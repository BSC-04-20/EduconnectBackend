<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LectureController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::get("/trial", [LectureController::class, 'show']);

Route::post("/lecture/signup", [LectureController::class, "signup"]);

Route::post("/lecture/login", [LectureController::class, "login"]);