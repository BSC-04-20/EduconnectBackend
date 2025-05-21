<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\EventRequest;
use App\Models\Event;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
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

    /**
     * Count My Events
     * 
     * Get the total number of events created by the authenticated lecture.
     * 
     * @return JsonResponse
     *
     * @authenticated
     *
     * @response 200 {
     *   "message": "Event count retrieved successfully.",
     *   "data": {
     *     "total_events": 5
     *   }
     * }
     *
     * @response 401 {
     *   "message": "Unauthenticated."
     * }
     */
    public function countMyEvents()
    {
        // Get the currently authenticated user
        $user = Auth::user();

        // Check if the user is authenticated
        if (!$user) {
            return response()->json([
                'message' => 'Unauthenticated.'
            ], 401);
        }

        // Count all events created by the authenticated lecturer
        $totalEvents = Event::where('user_id', $user->id)->count();

        // Return the total count as a JSON response
        return response()->json([
            'message' => 'Event count retrieved successfully.',
            'data' => [
                'total_events' => $totalEvents
            ]
        ], 200);
    }
}
