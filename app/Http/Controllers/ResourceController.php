<?php

namespace App\Http\Controllers;

use App\Models\Resource;
use App\Models\ResourceFile;
use App\Http\Requests\ResourceRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class ResourceController extends Controller
{
    /**
     * Store a newly created resource in storage.
     */
    public function store(ResourceRequest $request)
    {
        $resource = new Resource();
        $resource->class_id = $request->class_id;
        $resource->title = $request->title;
        $resource->description = $request->description;

        $resource->save();

        if ($request->hasFile('files')) {
            $files= $request->file('files');
            $destinationPath = "C:/Users/Weston/Desktop/resources";
            
            // Check if the directory exists, if not, create it
            if (!file_exists($destinationPath)) {
                mkdir($destinationPath, 0755, true); // Create the directory with appropriate permissions
            }

            // Loop through each file and get the original filename
            foreach ($request->file("files") as $file) {
                $file->move($destinationPath, $file->getClientOriginalName());
            }

            // Create an entry in the resource_files table
            ResourceFile::create([
                'resource_id' => $resource->id,  // Associate the file with the resource
                'file_path' => 'educonnect/resources/' . $file->getClientOriginalName(), // Store the relative file path
            ]);
    
            // Return an array of all uploaded filenames
            return response()->json(["created" => "yes"]);
        }

        return response()->json([
            "Message"
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $resource = Resource::findOrFail($id);
        return response()->json($resource);
    }

    /**
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
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $resource = Resource::findOrFail($id);
        $resource->delete();

        return response()->json([
            'message' => 'Resource deleted successfully.'
        ], 200);
    }
}
