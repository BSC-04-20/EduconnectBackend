<?php

namespace App\Http\Controllers;

use App\Models\Rating;
use Illuminate\Http\Request;

class RatingsController extends Controller
{
    /**
     * Submit a rating for a lecture.
     */
    public function rateLecture(Request $request, $lectureId)
    {
        $request->validate([
            'rating' => 'required|integer|min:1|max:5'
        ]);

        // Assuming the student is logged in
        $studentId = auth()->id();

        // Store or update the rating
        $rating = Rating::updateOrCreate(
            ['student_id' => $studentId, 'lecture_id' => $lectureId],
            ['rating' => $request->rating]
        );

        return response()->json(["message" => "Rating submitted successfully"]);
    }

    /**
     * Get average rating for the currently authenticated lecturer.
     */
    public function getUserAverageRating()
    {
        $lectureId = auth()->id();

        $averageRating = Rating::where('lecture_id', $lectureId)->avg('rating');
        $formattedAverage = number_format((float)$averageRating, 2, '.', '');

        return response()->json([
            'average_rating' => $formattedAverage,
            'total_ratings' => Rating::where('lecture_id', $lectureId)->count()
        ]);
    }

    /**
     * Get average rating for a specific lecture by ID.
     */
    public function getLectureRating($lectureId)
    {
        $averageRating = Rating::where('lecture_id', $lectureId)->avg('rating');
        $formattedAverage = number_format((float)$averageRating, 2, '.', '');

        return response()->json([
            'average_rating' => $formattedAverage,
        ]);
    }
}
