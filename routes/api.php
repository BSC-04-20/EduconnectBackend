<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LectureController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\ClassController;
use App\Http\Controllers\EventController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::get("/trial", [LectureController::class, 'show']);

Route::post("/lecture/signup", [LectureController::class, "signup"]);
Route::post("/lecture/login", [LectureController::class, "login"]);

Route::post("/student/signup", [StudentController::class, "signup"]);
Route::post("/student/login", [StudentController::class, "login"]);

Route::prefix("classes")->controller(ClassController::class)->middleware('auth:sanctum')->group(function (){
    Route::post("/create", "create");
    Route::post("/join", "join");
    Route::get("/get", "lectureClasses");
});

Route::prefix("event")->controller(EventController::class)->middleware('auth:sanctum')->group(function (){
    Route::post("/create", "store");
    Route::get("/get", "get");
});

