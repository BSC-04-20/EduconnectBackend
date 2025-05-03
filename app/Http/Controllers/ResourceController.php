<?php

namespace App\Http\Controllers;

use App\Models\Resource;
use App\Models\ResourceFile;
use App\Models\ClassModel;
use App\Http\Requests\ResourceRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\DB;

class ResourceController extends Controller
{
    /**
     * Store
     * 
     * Store a newly created resource in storage.
     */
    public function store(ResourceRequest $request)
    {
        DB::beginTransaction(); // Start the transaction

        try {
            $resource = new Resource();
            $resource->class_id = $request->class_id;
            $resource->title = $request->title;
            $resource->description = $request->description;
            $resource->save();

            $files = $request->file('files');
            $destinationPath = "/var/www/html/educonnect/resources";

            // Check if the directory exists, if not, create it
            if (!file_exists($destinationPath)) {
                mkdir($destinationPath, 0755, true); // Create the directory with appropriate permissions
            }

            // Loop through each file and get the original filename
            foreach ($files as $file) {
                $fileExtension = $file->getClientOriginalExtension();
                $actualMame = $file->getClientOriginalName();
                $fileName = time() . '-' . Str::random(10) . '.' . $fileExtension;

                $file->move($destinationPath, $fileName);

                // Create an entry in the resource_files table
                ResourceFile::create([
                    'resource_id' => $resource->id,  // Associate the file with the resource
                    "resource_name" => $actualMame,
                    'file_path' => 'educonnect/resources/' . $fileName, // Store the relative file path
                ]);
            }

            DB::commit(); // Commit the transaction

            return response()->json([
                "created" => "Uploaded successfully"
            ])->header('Content-Type', 'application/json');
        } catch (\Exception $e) {
            DB::rollBack(); // Rollback the transaction if anything goes wrong
            return response()->json([
                'error' => 'An error occurred while uploading the resource: ' . $e->getMessage()
            ], 500)->header('Content-Type', 'application/json');
        }
    }

    /**
     * Show
     * 
     * Display the specified resource.
     */
    public function show(string $id)
    {
        // Check if the resource exists
        $resource = Resource::with('files')->find($id);

        if (!$resource) {
            return response()->json([
                'success' => false,
                'message' => 'Resource not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'resource' => $resource->only(['id', 'title', 'description', 'class_id']),
                'files' => $resource->files->pluck('file_path') // Return only file paths
            ]
        ], 200);
    }

    /**
     * Update
     * 
     * Update the specified resource in storage.
     */
    public function update(ResourceRequest $request, string $id)
    {
        DB::beginTransaction(); // Start the transaction

        try {
            $resource = Resource::findOrFail($id);
            $resource->update($request->validated());

            DB::commit(); // Commit the transaction

            return response()->json([
                'message' => 'Resource updated successfully.',
                'resource' => $resource
            ], 200)->header('Content-Type', 'application/json');
        } catch (\Exception $e) {
            DB::rollBack(); // Rollback the transaction if anything goes wrong
            return response()->json([
                'error' => 'An error occurred while updating the resource: ' . $e->getMessage()
            ], 500)->header('Content-Type', 'application/json');
        }
    }

    /**
     * Delete
     * 
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        DB::beginTransaction(); // Start the transaction

        try {
            // Find the resource or throw a 404 if not found
            $resource = Resource::findOrFail($id);

            // Retrieve associated files
            $resourceFiles = ResourceFile::where('resource_id', $id)->get();

            // Loop through each file and delete it from storage
            foreach ($resourceFiles as $file) {
                // Delete the file from the storage disk
                File::delete('/var/www/html/' . $file->file_path);

                // Delete the file record in the database
                $file->delete();
            }

            // Now, delete the resource itself
            $resource->delete();

            DB::commit(); // Commit the transaction

            return response()->json([
                'message' => 'Resource and its associated files deleted successfully.'
            ], 200)->header('Content-Type', 'application/json');
        } catch (\Exception $e) {
            DB::rollBack(); // Rollback the transaction if anything goes wrong
            return response()->json([
                'error' => 'An error occurred while deleting the resource: ' . $e->getMessage()
            ], 500)->header('Content-Type', 'application/json');
        }
    }

    /**
     * ClassResources
     * 
     * Get all resources for a specific class by class id.
     */
    public function getResourcesByClassId($classId)
    {
        // Find the class by id
        $class = ClassModel::find($classId);

        // If class not found, return error response
        if (!$class) {
            return response()->json([
                'error' => 'Class not found'
            ], 404)->header('Content-Type', 'application/json');
        }

        // Get resources associated with this class and eager load related files
        $resources = $class->resources()->with('files')->get();

        // Return the resources as a JSON response
        return response()->json([
            'resources' => $resources
        ], 200)->header('Content-Type', 'application/json');
    }

    /**
     * LectureResources
     * 
     * Return all resources for the authenticated lecture.
     */
    public function getAllResourcesForAuthenticatedLecture(Request $request)
    {
        // Get the authenticated lecture (assuming Sanctum is used)
        $lecture = $request->user();

        // Get all resources where the class belongs to this lecture
        $resources = Resource::whereHas('class', function ($query) use ($lecture) {
            $query->where('lecture_id', $lecture->id);
        })->with(['files', 'class'])->get();

        return response()->json([
            'resources_count' => $resources->count(),
            'resources' => $resources->pluck('files')
        ]);
    }
}
