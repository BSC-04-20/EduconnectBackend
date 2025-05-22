<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Lecture;
use App\Http\Requests\LectureRequest;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use App\Mail\RegisterMail;
use Illuminate\Http\JsonResponse;

class LectureController extends Controller
{
    /**
     * Trial
     */
    public function show()
    {
        return "Hello from educonnect api.";
    }

    /**
     * Login
     */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        $user = Lecture::where('email', $credentials['email'])->first();

        if (!$user || !Hash::check($credentials['password'], $user->password)) {
            return response()->json(['error' => 'Invalid credentials'], 401);
        }

        $token = $user->createToken('API Token')->plainTextToken;

        return response()->json([
            'message' => 'Login successful',
            'token' => $token
        ]);
    }

    /**
     * Logout
     */
    public function logout(Request $request)
    {
        DB::beginTransaction();
        try {
            $request->user()->currentAccessToken()->delete();

            DB::commit();
            return response()->json([
                'message' => 'Logged out successfully'
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'error' => 'Logout failed',
                'details' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Signup
     */
    public function signup(LectureRequest $request): JsonResponse
    {
        $validated = $request->validated();

        DB::beginTransaction();
        try {
            $lecture = new Lecture();
            $lecture->fullname = $validated['fullname'];
            $lecture->email = $validated['email'];
            $lecture->phonenumber = $validated['phonenumber'];
            $lecture->password = Hash::make($validated['password']);
            $lecture->save();

            Mail::to($validated['email'])->send(new RegisterMail($lecture));

            DB::commit();
            return response()->json([
                "message" => "Created Successfully"
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                "error" => "Signup failed",
                "details" => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Change profile
     * 
     * Update the authenticated lecture's profile (excluding password).
     * 
     * @OA\Put(
     *     path="/lecture/profile",
     *     summary="Update authenticated lecture profile",
     *     tags={"Lecturer"},
     *     security={{"sanctum": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="fullname", type="string", maxLength=255, example="Dr. Jane Smith"),
     *             @OA\Property(property="email", type="string", format="email", example="jane.smith@example.com"),
     *             @OA\Property(property="phonenumber", type="string", maxLength=20, example="+265991234567")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Profile updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Profile updated successfully."),
     *             @OA\Property(property="user", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Failed to update profile",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string", example="Failed to update profile."),
     *             @OA\Property(property="details", type="string", example="SQL error or validation failure.")
     *         )
     *     )
     * )
     */
    public function updateProfile(Request $request)
    {
    
        $validated = $request->validate([
            'fullname'     => 'sometimes|string|max:255',
            'email'        => 'sometimes|email|unique:lectures,email,',
            'phonenumber'  => 'sometimes|string|max:20',
        ]);

        $user = $request->user();

        try {
            $user->update($validated);

            return response()->json([
                'message' => 'Profile updated successfully.',
                'user' => $user
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to update profile.',
                'details' => $e->getMessage()
            ], 500);
        }
    }
}
