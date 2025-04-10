<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LectureController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\ClassController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\AnnouncementController;
use App\Http\Controllers\AssignmentController;
use App\Http\Controllers\ResourceController;
use App\Http\Controllers\RatingsController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::get("/trial", [LectureController::class, 'show']);

Route::post("/lecture/signup", [LectureController::class, "signup"]);
Route::post("/lecture/login", [LectureController::class, "login"]);

Route::post("/student/signup", [StudentController::class, "signup"]);
Route::post("/student/login", [StudentController::class, "login"]);

Route::prefix("lecture")->controller(LectureController::class)->middleware('auth:sanctum')->group(function (){
    Route::post("/logout", "logout");
});

Route::prefix("student")->controller(StudentController::class)->middleware('auth:sanctum')->group(function (){
    Route::post("/logout", "logout");
    Route::get("/lecturers", "getStudentLecturers");
});

Route::prefix("classes")->controller(ClassController::class)->middleware('auth:sanctum')->group(function (){
    Route::post("/create", "create");
    Route::post("/join", "join");
    Route::get("/get", "lectureClasses");
    Route::get('/get/{id}', 'getClassById');
    Route::get('/get/{id}/students', 'getStudents');
    Route::get('/get/{id}/all/post', 'getCombinedAssignmentsAndAnnouncements');
    Route::get('/student-classes', 'studentClasses');
    Route::post('/{classId}/discussion', 'createDiscussion');
    Route::get('/{classId}/discussions', "getByClassId");
    Route::get("/discussions/student", "getStudentDiscussions");
    Route::get("/discussion/{discussionId}", "getDiscussionById");
});

Route::prefix("announcement")
    ->controller(AnnouncementController::class)
    ->middleware('auth:sanctum')
    ->group(function () {
        Route::post("/create", "store");
        Route::get("/get", "index");
        Route::get("/get/{id}", "show");
        Route::put("/update/{id}", "update");
        Route::delete("/delete/{announcementId}", "destroy");
    });

Route::prefix("assignment")
    ->controller(AssignmentController::class)
    ->middleware('auth:sanctum')
    ->group(function () {
        Route::post("/create", "store"); // Create an assignment
        Route::post("/submit/{assignmentId}", "submit");
        Route::get("/get", "index"); // Get all assignments
        Route::get("/get/{id}", "show"); // Get a specific assignment
        Route::put("/update/{id}", "update"); // Update an assignment
        Route::delete("/delete/{id}", "destroy"); // Delete an assignment
        Route::post("/mark/{submissionId}", "mark");
        Route::get("/mark/{submissionId}", "getMarks");
    });

Route::prefix("event")->controller(EventController::class)->middleware('auth:sanctum')->group(function (){
    Route::post("/create", "store");
    Route::get("/get", "get");
});

Route::prefix("resources")->controller(ResourceController::class)->middleware('auth:sanctum')->group(function () {
    Route::post("/create", "store");  // Route to create a resource
    Route::get("/get", "index");  // Route to get all resources
    Route::get('/get/{id}', 'show');  // Route to get a specific resource by ID
    Route::put("/update/{id}", "update");  // Route to update a resource
    Route::delete("/delete/{id}", "destroy");  // Route to delete a resource
    Route::post('/upload-files', 'uploadFiles');  // Route for handling file uploads (if necessary)
    Route::get("/lecture", "getAllResourcesForAuthenticatedLecture");
});

Route::prefix("ratings")->controller(RatingsController::class)->middleware('auth:sanctum')->group(function () {
    Route::post("/rate/{lectureId}", "rateLecture");
    Route::get("/get", "getUserAverageRating");
    Route::get("/get/{lectureId}", "getLectureRating");
});

