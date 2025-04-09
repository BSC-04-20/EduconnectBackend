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
                // Create the assignment
                $assignment = Assignment::create($request->validated());

                // Check if files are uploaded
                if ($request->hasFile('files')) {

                    $destinationPath = '/var/www/html/educonnect/assignments';

                    // Ensure the directory exists
                    if (!file_exists($destinationPath)) {
                        mkdir($destinationPath, 0755, true);
                    }

                    // Loop through and store each file
                    foreach ($request->file('files') as $file) {
                        $fileExtension = $file->getClientOriginalExtension();
                        $fileName = time() . '-' . Str::random(10) . '.' . $fileExtension;

                        // Move the file
                        $file->move($destinationPath, $fileName);

                        // Store metadata in DB
                        AssignmentFile::create([
                            'assignment_id' => $assignment->id,
                            'file_name' => $file->getClientOriginalName(),
                            'file_path' => 'educonnect/assignments/' . $fileName,
                        ]);
                    }
                }

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
        $assignment = Assignment::with(['class', 'assignmentFiles'])->findOrFail($id);

        return response()->json($assignment);
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
        $assignment = Assignment::findOrFail($id);
        $assignment->delete();

        return response()->json(['message' => 'Assignment deleted successfully.']);
    }

    /**
     * Submit
     * 
     * Submit an assignment
     */
    public function submit(Request $request, $assignmentId){
        $request->validate([
            'files' => 'required|array',
            'files.*' => 'file', // Allow multiple file types
        ]);

        // Ensure the assignment exists
        $assignment = Assignment::find($assignmentId);
        if (!$assignment) {
            return response()->json(["message" => "Assignment not found"], 404);
        }

        // Get authenticated student ID
        $studentId = auth()->id();

        // Create a submission record
        $submission = Submission::create([
            'student_id' => $studentId,
            'assignment_id' => $assignmentId,
        ]);

        $destination = "/var/www/html/educonnect/submissions";

        // Ensure the directory exists
        if (!file_exists($destination)) {
            mkdir($destination, 0755, true);
        }

        // Store each file
        if ($request->hasFile('files')) {
            foreach ($request->file('files') as $file) {
                $filename = time() . Str::random(10) .".". $file->getClientOriginalExtension();
                $file->move($destination, $filename); // Move file to target directory

                // Save file path to database
                SubmissionFiles::create([
                    'submission_id' => $submission->id,
                    'file_path' => "educonnect/submissions/" . $filename,
                ]);
            }
        }

        return response()->json([
            "message" => "Assignment submitted successfully"
            ], 201);
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
}
