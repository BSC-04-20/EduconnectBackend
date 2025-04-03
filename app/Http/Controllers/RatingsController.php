<?php

namespace App\Http\Controllers;

use App\Models\Rating;
use Illuminate\Http\Request;

class RatingsController extends Controller{
    
    /**
     * Rate
     * 
     * Rate lecturer
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

    /**
     * Rate
     * 
     * Get the average rating for the current authenticated lecture
     */
    public function getUserAverageRating()
    {
        $lectureId = auth()->id();
        
        // Calculate average rating
        $averageRating = Rating::where('lecture_id', $lectureId)
            ->avg('rating');
            
        // Format the average to 2 decimal places
        $formattedAverage = number_format((float)$averageRating, 2, '.', '');

        return response()->json([
            'average_rating' => $formattedAverage,
            'total_ratings' => Rating::where('lecture_id', $lectureId)->count()
        ]);
    }
}