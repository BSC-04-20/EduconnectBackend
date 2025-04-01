<?php

namespace App\Http\Controllers;

use App\Http\Requests\AssignmentRequest;
use App\Models\Assignment;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

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
    public function submit($id){
        
    }
}
