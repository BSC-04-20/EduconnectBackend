<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Lecture;
use App\Http\Requests\LectureRequest;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class LectureController extends Controller
{
    //
    function show(){
        return("Heelo");
    }
    /**
     * Handle an authentication attempt.
     */
    public function login(Request $request){
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        // Find user by email
        $user = Lecture::where('email', $credentials['email'])->first();

        // Check if user exists and password is correct
        if (!$user || !Hash::check($credentials['password'], $user->password)) {
            return response()->json(['error' => 'Invalid credentials'], 401);
        }


        //Deleting all previous tokes
        $user->tokens()->delete();

        // Create a new API token
        $token = $user->createToken('API Token')->plainTextToken;

        return response()->json([
            'message' => 'Login successful',
            'token' => $token
        ]);
    }

    public function logout(Request $request)
    {
        // Revoke the current user's token
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Logged out successfully'
        ], 200);
    }

    function signup(LectureRequest $request):JsonResponse {
            $validated = $request->validated();

            $lecture = new Lecture();

            $lecture->fullname = $request->fullname;
            $lecture->email = $request->email;
            $lecture->phonenumber = $request->phonenumber;
            $lecture->password = Hash::make($request->input("password"));

            $lecture->save();

            return response()->json([
                "message" => "Created Successfully"
            ], 201);
    }
}

