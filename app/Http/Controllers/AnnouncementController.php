<?php

namespace App\Http\Controllers;

use App\Http\Requests\AnnouncementRequest;
use App\Models\Announcement;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class AnnouncementController extends Controller
{
    /**
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
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }
    
    public function store(AnnouncementRequest $request)
    {
        //Creating the announcement 
        $announcement = Announcement::create($request->validated());
        
        //Checking if the file exist
        if ($request->hasFile('announcement_file')) {
    
            $file = $request->file('announcement_file');
            $destinationPath = '/var/www/html/educonnect/announcement'; // setting the destination path
            
            //Creating the announcement directory if it already exist or not
            if (!file_exists($destinationPath)) {
                mkdir($destinationPath, 0755, true);
            }
            
            //Setting the file name
            $fileName = time() . '-' . Str::random(10) . '-' . $file->getClientOriginalName();
            $file->move($destinationPath, $fileName); //moving the filename
    
            AnnouncementFile::create([
                'announcement_id' => $announcement->id,
                'file_path' => 'educonnect/announcement/' . $fileName,
            ]);
        }
    
        return response()->json([
            'message' => 'Announcement created successfully.',
            'announcement' => $announcement
        ], 201);
    }
    

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
