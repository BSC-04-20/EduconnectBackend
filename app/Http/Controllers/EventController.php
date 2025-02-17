<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\EventRequest;
use App\Models\Event;

class EventController extends Controller
{
    //
/**
     * Store a newly created event.
     */
    public function store(EventRequest $request)
    {
        // Combine date and time into a single DateTime format
        $eventDateTime = "{$request->date} {$request->time}";

        // Create the event
        $event = Event::create([
            'user_id'=>$request->user()->id,
            'name' => $request->name,
            'location' => $request->location,
            'date' => $request->date,
            'time' => $request->time,
            "number_of_attendees" => 0
        ]);

        return response()->json([
            'message' => 'Event created successfully!'
        ], 201);
    }

    function get(Request $request){
            // Retrieve all events where the 'user_id' matches the provided one
        $events = Event::where('user_id', $request->user()->id)->get();

        // Return the events as a JSON response
        return response()->json($events);
    }
}
