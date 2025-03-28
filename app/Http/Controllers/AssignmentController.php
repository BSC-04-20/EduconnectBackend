<?php

namespace App\Http\Controllers;

use App\Http\Requests\AssignmentRequest;
use App\Models\Assignment;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class AssignmentController extends Controller
{
    /**
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
     * Get all assignments.
     */
    public function index()
    {
        return response()->json(Assignment::with('class')->latest()->get());
    }

    /**
     * Get a specific assignment.
     */
    public function show($id)
    {
        $assignment = Assignment::with('class')->findOrFail($id);

        return response()->json($assignment);
    }

    /**
     * Update an assignment.
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
     * Delete an assignment.
     */
    public function destroy($id)
    {
        $assignment = Assignment::findOrFail($id);
        $assignment->delete();

        return response()->json(['message' => 'Assignment deleted successfully.']);
    }
}
