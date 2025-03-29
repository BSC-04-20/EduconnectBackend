<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Student;
use App\Http\Requests\StudentRequest;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use App\Mail\RegisterMail;
use Illuminate\Http\JsonResponse;


class StudentController extends Controller
{
    //
    
        /**
     * Handle an authentication attempt.
     */
    public function login(Request $request){
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        // Find user by email
        $user = Student::where('email', $credentials['email'])->first();

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
            'studToken' => $token
        ]);
    }

    function signup(Request $request){
        // $validated = $request->validated();

        $student = new Student();

        $student->fullname = $request->fullname;
        $student->email = $request->email;
        $student->phonenumber = $request->phonenumber;
        $student->password = Hash::make($request->input("password"));

        $student->save();

        // Mail::to("wes@gmail.com")->send(new RegisterMail($student));

        return response()->json([
            "message" => "Created Successfully"
        ], 201);
    }

    public function logout(Request $request){
        // Revoke the current user's token
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Logged out successfully'
        ], 200);
    }
}
