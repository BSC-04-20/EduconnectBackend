<?php

namespace App\Http\Controllers;

use App\Http\Requests\AssignmentRequest;
use App\Models\Assignment;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Models\Submission;
use App\Models\SubmissionFiles;
use Illuminate\Support\Str;

class AssignmentController extends Controller
{
    /**
     * Store
     * 
     * Create a new assignment.
     */
    public function store(AssignmentRequest $request)
    {
        $assignment = Assignment::create($request->validated());

        return response()->json([
            'message' => 'Assignment created successfully.'
        ], 201);
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
     * Get a specific assignment specified bt assignment id.
     */
    public function show($id)
    {
        $assignment = Assignment::with('class')->findOrFail($id);

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
                $filename = time() . Str::random(10) . $file->getClientOriginalExtension();
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
}
