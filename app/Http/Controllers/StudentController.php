<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Student;
use App\Http\Requests\StudentRequest;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Http\JsonResponse;


class StudentController extends Controller
{
    //
    
    /**
     * Login
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

    /** 
     * Signup
    */
    public function signup(Request $request)
    {
        // Validate request data
        $validated = $request->validate([
            'fullname'    => 'required|string|max:255',
            'email'       => 'required|email|unique:students,email',
            'phonenumber' => 'required|string|regex:/^[0-9]{10,15}$/',
            'password'    => 'required|string|min:8',
        ]);
    
        // Create a new student record
        $student = new Student();
        $student->fullname = $validated['fullname'];
        $student->email = $validated['email'];
        $student->phonenumber = $validated['phonenumber'];
        $student->password = Hash::make($validated['password']);
    
        $student->save();
    
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
