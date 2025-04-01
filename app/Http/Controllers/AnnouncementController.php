<?php

namespace App\Http\Controllers;

use App\Http\Requests\AnnouncementRequest;
use App\Models\Announcement;
use App\Models\AnnouncementFile;
use App\Models\Lecture;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class AnnouncementController extends Controller
{
    /**
     * Index
     * 
     * Display a listing of the resource.
     */
    public function index(){
        // Fetch all announcements
        $announcements = Announcement::all();

        // Return the announcements as a JSON response
        return response()->json([
            'data' => $announcements
        ], 200);
    }


    /**
     * Store
     * 
     * Post a a resource
     */
    public function store(AnnouncementRequest $request) {
        $announcement = Announcement::create($request->validated());

        // Check if multiple files are uploaded
        if ($request->hasFile('announcement_files')) {
            
            $request->validate([
                'announcement_files.*' => 'file', // Validate each file
            ]);

            $destinationPath = '/var/www/html/educonnect/announcement';

            // Ensure the directory exists
            if (!file_exists($destinationPath)) {
                mkdir($destinationPath, 0755, true);
            }

            // Loop through each uploaded file
            foreach ($request->file('announcement_files') as $file) {
                $fileExtension = $file->getClientOriginalExtension();
                $fileName = time() . '-' . Str::random(10) . '.' . $fileExtension;

                $file->move($destinationPath, $fileName);

                AnnouncementFile::create([
                    'announcement_id' => $announcement->id,
                    'file_path' => 'educonnect/announcement/' . $fileName,
                ]);
            }

            return response()->json([
                "message" => "Announcement and files uploaded successfully"
            ], 201);
        }

        return response()->json([
            'message' => 'Announcement created successfully.',
        ], 201);
    }

    /**
     * Show
     * 
     * Display the specified announcement. Pass the announcement id
     */
    public function show(string $id)
    {
        // Fetch the announcement along with its related class and files
        $announcement = Announcement::with(['class', 'files'])->find($id);

        if (!$announcement) {
            return response()->json(["message" => "Announcement not found"], 404);
        }

        // Get the lecture associated with the class
        $lecture = Lecture::whereHas('classes', function ($query) use ($announcement) {
            $query->where('id', $announcement->class_id);
        })->first();

        return response()->json([
            "lecture_name" => $lecture ? $lecture->fullname : "Unknown",
            "title" => $announcement->title,
            "description" => $announcement->description,
            "posted"=> $announcement->created_at,
            "files" => $announcement->files->pluck("file_path")
        ]);
    }

    /**
     * Update
     * 
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Delete
     * 
     * Remove the specified resource from storage.
     */
    
    public function destroy(string $id){
        // Find the announcement by its ID
        $announcement = Announcement::findOrFail($id);
    
        // Check if there are any associated files
        $announcementFiles = AnnouncementFile::where('announcement_id', $announcement->id)->get();
    
        if ($announcementFiles->isNotEmpty()) {
            // If there are files, delete each file from the file system
            foreach ($announcementFiles as $file) {
                $filePath = public_path($file->file_path); // Get the full file path
                
                // Check if the file exists and delete it
                if (file_exists($filePath)) {
                    unlink($filePath); // Delete the file
                }
    
                // Delete the record from the 'announcement_files' table
                $file->delete();
            }
        }
    
        // Delete the announcement record
        $announcement->delete();
    
        return response()->json([
            'message' => 'Announcement and associated files deleted successfully, if any.'
        ], 200);
    }
}
