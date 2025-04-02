<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\EventRequest;
use App\Models\Event;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;

class EventController extends Controller
{
    //
    /**
     * Store
     * 
     * Store a newly created event.
     */
    public function store(EventRequest $request)
    {

        // Create the event
        $event = Event::create([
            'user_id'=>$request->user()->id,
            'name' => $request->name,
            'location' => $request->location,
            'date' => $request->date,
            'time' => Carbon::parse($request->time)->format('H:i'),
            "number_of_attendees" => 0
        ]);

        return response()->json([
            'message' => 'Event created successfully!'
        ], 201);
    }

    /**
     * UserEvents
     * 
     * Get all events for current user
     * 
     */
    function get(Request $request){
        $events = Event::where('user_id', $request->user()->id)->get();

        // Return the events as a JSON response
        return response()->json($events);
    }
}
