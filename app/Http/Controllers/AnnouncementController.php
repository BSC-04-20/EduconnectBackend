<?php

namespace App\Http\Controllers;

use App\Http\Requests\AnnouncementRequest;
use App\Models\Announcement;
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

       /**
     * Store a new announcement.
     */
    public function store(AnnouncementRequest $request)
    {
        $announcement = Announcement::create($request->validated());

        return response()->json([
            'message' => 'Announcement created successfully.'
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
