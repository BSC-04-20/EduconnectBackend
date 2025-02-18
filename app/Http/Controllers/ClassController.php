<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Models\ClassModel;
use App\Models\ClassStudents;  

class ClassController extends Controller
{
    //

    function create(Request $request){

        $class = new ClassModel();

        $request->validate([
            'name' => 'required|min:3'
        ]);

        $classCode = $this->generateUniqueClassCode();
        
        $class->name = $request->name;
        $class->class_code = $classCode;
        $class->lecture_id = $request->user()->id;
        $class->number_of_students = 0;

        $class->save();

        return response()->json([
            "message" => "class created successfully",
            "code" => $classCode
        ]);
    }

    //This is the function to generate a unique class code
    private function generateUniqueClassCode(){
        do {
            // Generate a random class code (e.g., "CLS-ABC123")
            $classCode = strtoupper(Str::random(6));
        } while (ClassModel::where('class_code', $classCode)->exists());

        return $classCode;
    }

    public function lectureClasses(Request $request){

        $classes = ClassModel::where('lecture_id', $request->user()->id)->get();

        return response()->json([
            'status' => 'success',
            'data' => $classes
        ], 200);
    }

    public function join(Request $request){
        // Validate the request
        $request->validate([
            'code' => 'required|string|exists:classes,class_code',
        ]);

        // Find the class by code
        $class = ClassModel::where('class_code', $request->code)->first();
        $classStudent = new ClassStudents();

        $classStudent->classe_id = $class->id;
        $classStudent->student_id = $request->user()->id;

        $classStudent->save();

        return response()->json([
            "message" => "joined successfully"
        ], 201);
    }
}
