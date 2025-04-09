<?php

namespace App\Http\Controllers;

use App\Models\Resource;
use App\Models\ResourceFile;
use App\Http\Requests\ResourceRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;

class ResourceController extends Controller
{
    /**
     * Store
     * 
     * Store a newly created resource in storage.
     */
    public function store(ResourceRequest $request){
        $resource = new Resource();
        $resource->class_id = $request->class_id;
        $resource->title = $request->title;
        $resource->description = $request->description;

        $resource->save();

        $files= $request->file('files');
        $destinationPath = "/var/www/html/educonnect/resources";

        // Check if the directory exists, if not, create it
        if (!file_exists($destinationPath)) {
            mkdir($destinationPath, 0755, true); // Create the directory with appropriate permissions
        }

        // Loop through each file and get the original filename
        foreach ($request->file("files") as $file) {
            $fileExtension = $file->getClientOriginalExtension();
            $actualMame= $file->getClientOriginalName();
            $fileName = time() . '-' . Str::random(10) . '.' . $fileExtension;

            $file->move($destinationPath, $fileName);

            
            // Create an entry in the resource_files table
            ResourceFile::create([
                'resource_id' => $resource->id,  // Associate the file with the resource
                "resource_name"=> $actualMame,
                'file_path' => 'educonnect/resources/' . $fileName, // Store the relative file path
            ]);
        }

    
        // Return an array of all uploaded filenames
        return response()->json([
            "created" => "Uploaded successfully"
        ]);
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
        $resource = Resource::findOrFail($id);
        $resource->update($request->validated());

        return response()->json([
            'message' => 'Resource updated successfully.',
            'resource' => $resource
        ], 200);
    }

    /**
     * Delete
     * 
     * Remove the specified resource from storage.
     */
    
    public function destroy(string $id)
    {
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
    
        // Return a success response
        return response()->json([
            'message' => 'Resource and its associated files deleted successfully.'
        ], 200);
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
            'resources' => $resources->pluck(["files"])
        ]);
    }
}
