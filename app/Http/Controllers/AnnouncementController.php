<?php

namespace App\Http\Controllers;

use App\Http\Requests\AnnouncementRequest;
use App\Models\Announcement;
use App\Models\AnnouncementFile;
use App\Models\Lecture;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\DB;
use App\Models\ClassModel;
use Illuminate\Support\Facades\Mail;
use App\Mail\AnnouncementNotification;
use Illuminate\Support\Facades\Auth;

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
     * Create
     * 
     * Post a a resource
     */
    public function store(AnnouncementRequest $request) {
        DB::beginTransaction();
    
        try {
            $announcement = Announcement::create($request->validated());
            
            if ($request->hasFile('announcement_files')) {
                $destinationPath = '/var/www/html/educonnect/announcement';
    
                if (!file_exists($destinationPath)) {
                    mkdir($destinationPath, 0755, true);
                }
    
                foreach ($request->file('announcement_files') as $file) {
                    $fileExtension = $file->getClientOriginalExtension();
                    $fileName = time() . '-' . Str::random(10) . '.' . $fileExtension;
    
                    $file->move($destinationPath, $fileName);
    
                    AnnouncementFile::create([
                        'announcement_id' => $announcement->id,
                        'file_path' => 'educonnect/announcement/' . $fileName,
                    ]);
                }
            }
            
            $classStudents = ClassModel::with('students')->findOrFail($announcement->class_id);
            $studentEmails = $classStudents->students->pluck('email');

            // Send email to each student
            // foreach ($studentEmails as $email) {
            //     Mail::to($email)->queue(new AnnouncementNotification($announcement));
            // }

            DB::commit();
    
            return response()->json([
                "message" => "Announcement and files uploaded successfully",
                'students'=>$studentEmails
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
    
            return response()->json([
                'message' => 'Failed to create announcement.',
                'error' => $e->getMessage()
            ], 500);
        }
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
     * Remove the specified announcement from storage.
     */
    public function destroy(string $announcementId){
        // Find the announcement by its ID
        $announcement = Announcement::findOrFail($announcementId);
    
        // Check if there are any associated files
        $announcementFiles = AnnouncementFile::where('announcement_id', $announcement->id)->get();
    
        if ($announcementFiles->isNotEmpty()) {
            
            // If there are files, delete each file from the file system
            foreach ($announcementFiles as $file) {
                $filePath = $file->file_path; // Get the full file path
                
                //Delete the file
                File::delete('/var/www/html/' . $filePath);
                
                // Delete the record from the 'announcement_files' table
                $file->delete();
            }
        }
    
        // Delete the announcement record
        $announcement->delete();
    
        return response()->json([
            'message' => 'Announcement deleted successfully'
        ], 200);
    }

    /*
    * Student Announcement
    * Get all announcements for an authenticated student
    */
    public function getMyAnnouncements()
    {
        try {
            $student = Auth::user(); // Get the currently authenticated user

            if (!$student) {
                return response()->json([
                    'message' => 'Unauthenticated.'
                ], 401);
            }

            // Get class IDs that this student is part of
            $classIds = DB::table('classstudents')
                ->where('student_id', $student->id)
                ->pluck('classe_id');

            if ($classIds->isEmpty()) {
                return response()->json([
                    'message' => 'You are not enrolled in any classes.',
                    'announcements' => []
                ], 200);
            }

            // Fetch announcements for these classes
            $announcements = Announcement::with('files')
                ->whereIn('class_id', $classIds)
                ->latest()
                ->get();

            return response()->json([
                'message' => 'Announcements retrieved successfully.',
                'announcements' => $announcements
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error retrieving announcements.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
