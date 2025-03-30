<?php

namespace App\Http\Controllers;

use App\Models\Resource;
use App\Models\ResourceFile;
use App\Http\Requests\ResourceRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ResourceController extends Controller
{
    /**
     * Store a newly created resource in storage.
     */
    public function store(ResourceRequest $request)
    {
        // Create the resource record using validated data
        $resource = Resource::create($request->validated());

        // Check if the user has uploaded files
        if ($request->hasFile('files')) {
            // Loop through the uploaded files
            foreach ($request->file('files') as $file) {
                // Define the directory to store the file
                $destinationPath = '/var/www/html/educonnect/resources';

                // Check if the directory exists, if not, create it
                if (!file_exists($destinationPath)) {
                    mkdir($destinationPath, 0755, true); // Create the directory with appropriate permissions
                }

                // Set the file name (you can modify the name as needed)
                $fileName = time() . '-' . Str::random(10) . '.' . $file->getClientOriginalExtension();

                // Move the file to the destination directory
                $file->move($destinationPath, $fileName);

                // Create an entry in the resource_files table
                ResourceFile::create([
                    'resource_id' => $resource->id,  // Associate the file with the resource
                    'file_path' => 'resources/' . $fileName, // Store the relative file path
                ]);
            }
        }

        // Return a success response
        return response()->json([
            'message' => 'Resource created successfully.',
            'resource' => $resource
        ], 201);
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
