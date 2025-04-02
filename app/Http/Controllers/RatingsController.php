<?php

namespace App\Http\Controllers;

use App\Models\Rating;
use Illuminate\Http\Request;

class RatingsController extends Controller{
    
    /**
     * Rate
     * 
     */
    public function rateLecture(Request $request,$lectureId)
    {
        $request->validate([
            'rating' => 'required|integer|min:1|max:5',
        ]);

        // Assuming the student is logged in
        $studentId = auth()->id();

        // Store the rating
        $rating = Rating::updateOrCreate(
            ['student_id' => $studentId, 'lecture_id' => $lectureId],
            ['rating' => $request->rating]
        );

        return response()->json(["message" => "Rating submitted successfully"]);
    }

}