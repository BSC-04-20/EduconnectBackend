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
use Illuminate\Support\Facades\Storage;

class ResourceController extends Controller
{
    /**
     * Store a newly created resource in storage.
     */
    public function store(ResourceRequest $request)
    {
        DB::beginTransaction();

        try {
            $resource = new Resource();
            $resource->class_id = $request->class_id;
            $resource->title = $request->title;
            $resource->description = $request->description;
            $resource->save();

            $files = $request->file('files');

            foreach ($files as $file) {
                $fileExtension = $file->getClientOriginalExtension();
                $originalName = $file->getClientOriginalName();
                $fileName = time() . '-' . Str::random(10) . '.' . $fileExtension;

                // Store file in storage/app/public/resource
                $file->storeAs('public/resources', $fileName);

                ResourceFile::create([
                    'resource_id'   => $resource->id,
                    'resource_name' => $originalName,
                    'file_path'     => 'resource/' . $fileName, // relative to 'public'
                ]);
            }

            DB::commit();

            return response()->json([
                "created" => "Uploaded successfully"
            ])->header('Content-Type', 'application/json');
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'error' => 'An error occurred while uploading the resource: ' . $e->getMessage()
            ], 500)->header('Content-Type', 'application/json');
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
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
                'files'    => $resource->files->pluck('file_path')
            ]
        ], 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(ResourceRequest $request, string $id)
    {
        DB::beginTransaction();

        try {
            $resource = Resource::findOrFail($id);
            $resource->update($request->validated());

            DB::commit();

            return response()->json([
                'message' => 'Resource updated successfully.',
                'resource' => $resource
            ], 200)->header('Content-Type', 'application/json');
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'error' => 'An error occurred while updating the resource: ' . $e->getMessage()
            ], 500)->header('Content-Type', 'application/json');
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        DB::beginTransaction();

        try {
            $resource = Resource::findOrFail($id);
            $resourceFiles = ResourceFile::where('resource_id', $id)->get();

            foreach ($resourceFiles as $file) {
                Storage::delete('public/' . $file->file_path); // delete from storage
                $file->delete();
            }

            $resource->delete();

            DB::commit();

            return response()->json([
                'message' => 'Resource and its associated files deleted successfully.'
            ], 200)->header('Content-Type', 'application/json');
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'error' => 'An error occurred while deleting the resource: ' . $e->getMessage()
            ], 500)->header('Content-Type', 'application/json');
        }
    }

    /**
     * Get all resources for a specific class by class id.
     */
    public function getResourcesByClassId($classId)
    {
        $class = ClassModel::find($classId);

        if (!$class) {
            return response()->json([
                'error' => 'Class not found'
            ], 404)->header('Content-Type', 'application/json');
        }

        $resources = $class->resources()->with('files')->get();

        return response()->json([
            'resources' => $resources
        ], 200)->header('Content-Type', 'application/json');
    }

    /**
     * Return all resources for the authenticated lecture.
     */
    public function getAllResourcesForAuthenticatedLecture(Request $request)
    {
        $lecture = $request->user();

        $resources = Resource::whereHas('class', function ($query) use ($lecture) {
            $query->where('lecture_id', $lecture->id);
        })->with(['files', 'class'])->get();

        return response()->json([
            'resources_count' => $resources->count(),
            'resources' => $resources->pluck('files')
        ]);
    }

    /**
     * Download a specific resource file.
     */
    public function download(string $resourceId, string $fileId)
    {
        $resourceFile = ResourceFile::where('resource_id', $resourceId)
                                    ->where('id', $fileId)
                                    ->first();

        if (!$resourceFile) {
            return response()->json([
                'error' => 'File not found'
            ], 404);
        }

        $filePath = storage_path('app/private/public/' . $resourceFile->file_path);

        if (!File::exists($filePath)) {
            return response()->json([
                'error' => 'File does not exist',
                'path' => $filePath
            ], 404);
        }

        return response()->download($filePath, $resourceFile->resource_name);
    }
}
