<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Student;
use App\Models\Lecture;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\DB;
use App\Mail\RegisterMail;
use Illuminate\Http\JsonResponse;

class StudentController extends Controller
{
    /**
     * Login
     */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        $user = Student::where('email', $credentials['email'])->first();

        if (!$user || !Hash::check($credentials['password'], $user->password)) {
            return response()->json(['error' => 'Invalid credentials'], 401);
        }

        $token = $user->createToken('API Token')->plainTextToken;

        return response()->json([
            'message' => 'Login successful',
            'studToken' => $token
        ]);
    }

    /**
     * Signup (with transaction)
     */
    public function signup(Request $request)
    {
        $validated = $request->validate([
            'fullname'    => 'required|string|max:255',
            'email'       => 'required|email|unique:students,email',
            'phonenumber' => 'required|string|regex:/^[0-9]{10,15}$/',
            'password'    => 'required|string|min:8',
        ]);

        DB::beginTransaction();

        try {
            $student = new Student();
            $student->fullname = $validated['fullname'];
            $student->email = $validated['email'];
            $student->phonenumber = $validated['phonenumber'];
            $student->password = Hash::make($validated['password']);
            $student->save();

            Mail::to($validated['email'])->send(new RegisterMail($student));

            DB::commit();

            return response()->json([
                "message" => "Created Successfully"
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                "error" => "Signup failed. " . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Logout
     */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Logged out successfully'
        ], 200);
    }

    /**
     * Get lecturers associated with the student
     */
    public function getStudentLecturers()
    {
        $student = Auth::user();

        $lecturers = Lecture::whereHas('classes.classstudents', function ($query) use ($student) {
            $query->where('student_id', $student->id);
        })->pluck('id', 'fullname');

        return response()->json([
            'lecturers' => $lecturers
        ]);
    }

    /**
     * Change Student profile
     * 
     * Update the authenticated student's profile (excluding password).
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateProfile(Request $request): JsonResponse
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
