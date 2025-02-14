<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Lecture;
use App\Http\Requests\LectureRequest;
use Illuminate\Support\Facades\Hash;

class LectureController extends Controller
{
    //
    function show(){
        return("Heelo");
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

