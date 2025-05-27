<?php

namespace App\Http\Controllers;

use App\Http\Requests\AssignmentRequest;
use App\Models\Assignment;
use App\Models\AssignmentFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Models\Submission;
use App\Models\SubmissionFiles;
use Illuminate\Support\Str;
use App\Models\Marking;
use App\Models\ClassModel;
use Illuminate\Support\Facades\Mail;
use App\Mail\NewAssignmentNotification;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Response;
use App\Models\Student;
use App\Models\Lecture;

class AssignmentController extends Controller
{
    /**
     * Store
     * 
     * Create a new assignment.
     */
    public function store(AssignmentRequest $request){
        DB::beginTransaction();
    
        try {
            $assignment = Assignment::create($request->validated());
    
            if ($request->hasFile('files')) {
                $destinationPath = storage_path('app/public/assignments');
    
                if (!file_exists($destinationPath)) {
                    mkdir($destinationPath, 0755, true);
                }
    
                foreach ($request->file('files') as $file) {
                    $fileExtension = $file->getClientOriginalExtension();
                    $fileName = time() . '-' . Str::random(10) . '.' . $fileExtension;
    
                    $file->move($destinationPath, $fileName);
    
                    AssignmentFile::create([
                        'assignment_id' => $assignment->id,
                        'file_name' => $file->getClientOriginalName(),
                        'file_path' => 'assignments/' . $fileName, // Relative to storage/app/public
                    ]);
                }
            }
    
            $classStudents = ClassModel::with('students')->findOrFail($assignment->class_id);
            $studentEmails = $classStudents->students->pluck('email');
    
            // foreach ($studentEmails as $email) {
            //     Mail::to($email)->queue(new NewAssignmentNotification($assignment));
            // }
    
            DB::commit();
    
            return response()->json([
                'message' => 'Assignment and files uploaded successfully.'
            ], 201);
    
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Failed to create assignment.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    

    /**
     * Index
     * 
     * Get all assignments.
     */
    public function index()
    {
        return response()->json(Assignment::with('class')->latest()->get());
    }

    /**
     * Show
     * 
     * Get a specific assignment specified by assignment id, including its files.
     */
    public function show($id)
    {
        $assignment = Assignment::with(['class', 'files'])->findOrFail($id);
        $user = auth()->user();
        $userId = $user->id;

        // Determine if the user is a student
        $isStudent = Student::where('id', $userId)->exists();

        // Prepare base response
        $response = [
            'assignment' => $assignment
        ];

        if ($isStudent) {
            $status = 'not submitted';
            $submittedFiles = [];
            $mark = null;

            // Eager load marking and files for the submission
            $submission = \App\Models\Submission::with(['marking', 'files'])
                ->where('assignment_id', $id)
                ->where('student_id', $userId)
                ->first();

            if ($submission) {
                $status = 'submitted';
                $submittedFiles = $submission->files;
                $mark = $submission->marking?->marks; // Safely access marks using null-safe operator
            } elseif (now()->greaterThan($assignment->due_date)) {
                $status = 'missed';
            }

            $response['status'] = $status;

            if ($status === 'submitted') {
                $response['submitted_files'] = $submittedFiles;
                $response['mark'] = $mark;
            }
        }

        return response()->json($response);
    }
    /**
     * Update
     * 
     * Update an assignment specified by an assignment id.
     */
    public function update(AssignmentRequest $request, $id)
    {
        $assignment = Assignment::findOrFail($id);
        $assignment->update($request->validated());

        return response()->json([
            'message' => 'Assignment updated successfully.'
        ]);
    }

    /**
     * Delete
     * 
     * Delete an assignment by an assignment id.
    */
    public function destroy($id)
    {
        DB::beginTransaction();

        try {
            $assignment = Assignment::with('files')->findOrFail($id);

            // Delete physical files
            foreach ($assignment->files as $file) {
                $filePath = public_path($file->file_path);
                if (file_exists($filePath)) {
                    unlink($filePath);
                }
            }

            // Delete assignment files from DB
            AssignmentFile::where('assignment_id', $assignment->id)->delete();

            // Delete the assignment
            $assignment->delete();

            DB::commit();

            return response()->json(['message' => 'Assignment and its files deleted successfully.']);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'message' => 'Failed to delete assignment.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Submit
     * 
     * Submit an assignment
     */
    public function submit(Request $request, $assignmentId)
    {
        $request->validate([
            'files' => 'required|array',
            'files.*' => 'file|max:10240', // Optional: Limit file size (10MB)
        ]);

        DB::beginTransaction();

        try {
            $assignment = Assignment::find($assignmentId);
            if (!$assignment) {
                return response()->json(["message" => "Assignment not found"], 404);
            }

            $studentId = auth()->id();

            $submission = Submission::create([
                'student_id' => $studentId,
                'assignment_id' => $assignmentId,
            ]);

            if ($request->hasFile('files')) {
                foreach ($request->file('files') as $file) {
                    $filename = time() . '_' . Str::random(10) . '.' . $file->getClientOriginalExtension();
                    
                    // Store in storage/app/public/submissions
                    $filePath = $file->storeAs('submissions', $filename, 'public');

                    SubmissionFiles::create([
                        'submission_id' => $submission->id,
                        'file_path' => $filePath, // This will be like 'submissions/filename.ext'
                    ]);
                }
            }

            DB::commit();

            return response()->json([
                "message" => "Assignment submitted successfully"
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'message' => 'Failed to submit assignment.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Mark Assignment
     * 
     * Grade a student's submission for a given assignment.
     */
    public function mark(Request $request, $submissionId)
    {
        // Validate input
        $request->validate([
            'marks' => 'required|numeric|min:0|max:100',  // Marks should be a number between 0 and 100
            'feedback' => 'nullable|string',  // Optional feedback
        ]);

        // Start a transaction
        DB::beginTransaction();

        try {
            // Find the submission
            $submission = Submission::find($submissionId);
            if (!$submission) {
                return response()->json(["message" => "Submission not found."], 404);
            }

            // Ensure that the submission corresponds to an existing assignment
            $assignment = $submission->assignment;
            if (!$assignment) {
                return response()->json(["message" => "Assignment not found."], 404);
            }

            // Check if the submission already has a marking
            $existingMarking = $submission->marking;
            if ($existingMarking) {
                return response()->json(["message" => "This submission has already been marked."], 400);
            }

            // Create a new marking record
            $marking = Marking::create([
                'submission_id' => $submission->id,
                'marks' => $request->marks,
                'feedback' => $request->feedback,
            ]);

            // Commit the transaction
            DB::commit();

            return response()->json([
                'message' => 'Submission marked successfully.',
                'marking' => $marking, // Optionally return the marking details
            ], 201);

        } catch (\Exception $e) {
            // Rollback the transaction if anything goes wrong
            DB::rollBack();

            return response()->json([
                'message' => 'Failed to mark submission.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
    * Student Mark
    * 
    * Get Marks for a specific submission.
    */
    public function getMarks(Request $request, $submissionId)
    {
        // Get the authenticated student's ID
        $studentId = auth()->id();

        // Retrieve the submission for the authenticated student
        $submission = Submission::where('id', $submissionId)
                                ->where('student_id', $studentId)
                                ->first();

        // Check if submission exists
        if (!$submission) {
            return response()->json([
                'message' => 'Submission not found or does not belong to the authenticated student.',
            ], 404);
        }

        // Retrieve the marking for the submission
        $marking = $submission->marking;  // This will return the related marking if it exists

        // If no marking exists for this submission, return a message
        if (!$marking) {
            return response()->json([
                'message' => 'No marking found for this submission.',
            ], 404);
        }

        // Return the marks and feedback
        return response()->json([
            'marks' => $marking->marks,
            'feedback' => $marking->feedback,
        ]);
    }

    public function download($fileId)
    {
        // Find the file record by ID
        $assignmentFile = AssigmentFile::findOrFail($fileId);

        // Get the file path from the model
        $filePath = $assignmentFile->file_path;

        // Ensure the file exists in the public disk
        if (!Storage::disk('public')->exists($filePath)) {
            return response()->json(['error' => 'File not found.'], 404);
        }

        // Return a download response
        return Storage::disk('public')->download($filePath, $assignmentFile->file_name);
    }

    /**
     * Get all submissions for a specific assignment
     * 
     * @param  int  $assignmentId
     * @return JsonResponse
     */
    public function getSubmissionsForAssignment($assignmentId)
    {
        $assignment = Assignment::with('class')->findOrFail($assignmentId);

        // Get total number of students in the class
        $totalStudents = $assignment->class->students()->count();

        // Get all submissions including marking
        $submissions = Submission::with([
                'student:id,fullname,email,phonenumber', 
                'files:id,submission_id,file_path',
                'marking'  // Load related marking
            ])
            ->where('assignment_id', $assignmentId)
            ->get()
            ->map(function ($submission) {
                return [
                    'student' => $submission->student,
                    'submission_id' => $submission->id,
                    'files' => $submission->files->map(function ($file) {
                        return [
                            'id' => $file->id,
                            'file_path' => $file->file_path,
                        ];
                    }),
                    'marks' => $submission->marking ? $submission->marking->marks : null,
                    'feedback' => $submission->marking ? $submission->marking->feedback : null,
                ];
            });

        // Get all students in the class
        $students = $assignment->class->students()
            ->select('id', 'fullname', 'email', 'phonenumber')
            ->get()
            ->map(function ($student) {
                return [
                    'id' => $student->id,
                    'fullname' => $student->fullname,
                    'email' => $student->email,
                    'phonenumber' => $student->phonenumber,
                ];
            });

        return response()->json([
            'assignment' => $assignment->title,
            'total_students' => $totalStudents,
            'students' => $students,
            'number_of_submissions' => count($submissions),
            'submissions' => $submissions,
        ]);
    }

    public function showSubmission($submissionId)
    {
        $submission = Submission::with('files', 'student', 'assignment')->find($submissionId);

        if (!$submission) {
            return response()->json(['message' => 'Submission not found.'], 404);
        }

        return response()->json([
            'files' => $submission->files,
            'student' => $submission->student,
            'assignment' => $submission->assignment,
        ]);
    }
    

    /*
    * Calculate Student average scores
    *
    * Return the average score for all student work
    */
    public function getStudentAverageScore()
    {
        $studentId = auth()->id();
        $average = Marking::whereHas('submission', function ($query) use ($studentId) {
            $query->where('student_id', $studentId);
        })->avg('marks');

        return response()->json([
            'average_score' => $average !== null ? round($average, 2) : null,
        ]);
    }

    /*
    * Student Average per class
    *
    * Calculate the average score per course
    */
    public function getStudentAveragesForClass($classId)
    {
        $class = ClassModel::with('students')->findOrFail($classId);

        $averages = $class->students->map(function ($student) use ($classId) {
            $submissions = Submission::where('student_id', $student->id)
                ->whereHas('assignment', function ($query) use ($classId) {
                    $query->where('class_id', $classId);
                })
                ->with('marking')
                ->get();

            $totalMarks = 0;
            $markedCount = 0;

            foreach ($submissions as $submission) {
                if ($submission->marking) {
                    $totalMarks += $submission->marking->marks;
                    $markedCount++;
                }
            }

            return [
                'average_score' => $markedCount > 0 ? round($totalMarks / $markedCount, 2) : null
            ];
        });

        return response()->json($averages);
    }

    /*
    * Student scores
    *
    * Get  all scores of marked assignmnents
    */
    public function getStudentScoresWithDetails($studentId)
    {
        // Find all submissions by the student, including assignment, class, and marking
        $submissions = Submission::where('student_id', $studentId)
            ->with(['assignment.class', 'marking'])
            ->get();

        // Map the response to include desired data
        $scores = $submissions->map(function ($submission) {
            return [
                'assignment_name' => $submission->assignment->name,
                'class_name' => $submission->assignment->class->name,
                'score' => optional($submission->marking)->marks,
            ];
        });

        return response()->json($scores);
    }

    /**
     * All student assignments
     * 
     * Get all assignments for the authenticated student with submission status.
     */
    public function studentAssignmentsWithStatus()
    {
        $user = auth()->user();

        // Confirm user is a student
        $student = Student::find($user->id);
        if (!$student) {
            return response()->json(['message' => 'Authenticated user is not a student.'], 403);
        }

        // Get all classes the student is enrolled in
        $classes = $student->classes()->pluck('classe_id');

        // Get assignments for those classes
        $assignments = Assignment::with('class')
            ->whereIn('class_id', $classes)
            ->orderBy('due_date', 'desc')
            ->get();

        // Build response with status
        $assignmentsWithStatus = $assignments->map(function ($assignment) use ($student) {
            $submission = Submission::where('assignment_id', $assignment->id)
                ->where('student_id', $student->id)
                ->first();

            $status = 'not submitted';

            if ($submission) {
                $status = 'submitted';
            } elseif (now()->greaterThan($assignment->due_date)) {
                $status = 'missed';
            }

            return [
                'id' => $assignment->id,
                'title' => $assignment->title,
                'description' => $assignment->description,
                'due_date' => $assignment->due_date,
                'class' => $assignment->class->name ?? null,
                'status' => $status,
                'submitted_at' => $submission ? $submission->created_at : null,
            ];
        });

        return response()->json($assignmentsWithStatus);
    }
}
