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
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Storage;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::get("/trial", [LectureController::class, 'show']);

Route::post("/lecture/signup", [LectureController::class, "signup"]);
Route::post("/lecture/login", [LectureController::class, "login"]);
Route::prefix("lecture")->controller(LectureController::class)->middleware('auth:sanctum')->group(function () {
    Route::post("/updateProfile", "updateProfile");
    Route::post("/logout", "logout");
});

Route::middleware(['auth:sanctum'])->group(function () {
    Route::post('/lecture/profile-picture', [LectureController::class, 'uploadProfilePicture']);
    Route::get('/lecture/profile-picture', [LectureController::class, 'getProfilePicture']);
    Route::put('/lecture/profile-picture', [LectureController::class, 'updateProfilePicture']);
    Route::delete('/lecture/profile-picture', [LectureController::class, 'deleteProfilePicture']);
});

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/student/profile-picture', [StudentController::class, 'upload']);
    Route::get('/student/profile-picture', [StudentController::class, 'show']);
    Route::put('/student/profile-picture', [StudentController::class, 'update']);
    Route::delete('/student/profile-picture', [StudentController::class, 'delete']);
});

Route::post("/student/signup", [StudentController::class, "signup"]);
Route::post("/student/login", [StudentController::class, "login"]);

Route::prefix("student")->controller(StudentController::class)->middleware('auth:sanctum')->group(function () {
    Route::post("/updateProfile", "updateProfile");
    Route::post("/logout", "logout");
    Route::get("/lecturers", "getStudentLecturers");
});

Route::prefix("classes")->controller(ClassController::class)->middleware('auth:sanctum')->group(function () {
    Route::get("/get", "lectureClasses");
    Route::get("/count", "countLecturerClasses");
    Route::get('/get/{id}', 'getClassById');
    Route::get('/get/{id}/students', 'getStudents');
    Route::get('/get/{id}/all/post', 'getCombinedAssignmentsAndAnnouncements');
    Route::get('/student-classes', 'studentClasses');
    Route::get('/{classId}/discussions', "getByClassId");
    Route::get("/discussions/student", "getStudentDiscussions");
    Route::get("/discussion/summary", "getMyDiscussionSummary");
    Route::get("/discussion/{discussionId}", "getDiscussionById");
    Route::get("/discussion/summary/{discussionId}", "getDiscussionAttendance");

    Route::post('/discussion/{discussionId}/attend', "attendDiscussion");
    Route::post("/create", "create");
    Route::post("/join", "join");
    Route::post('/{classId}/discussion', 'createDiscussion');
});

Route::prefix("announcement")
    ->controller(AnnouncementController::class)
    ->middleware('auth:sanctum')
    ->group(function () {
        Route::get("/get", "index");
        Route::get("/get/{id}", "show");
        Route::get("/student", "getMyAnnouncements");
        Route::post("/create", "store");

        Route::put("/update/{id}", "update");
        Route::delete("/delete/{announcementId}", "destroy");
    });

Route::prefix("assignment")
    ->controller(AssignmentController::class)
    ->middleware('auth:sanctum')
    ->group(function () {
        Route::get("/get", "index"); // Get all assignments
        Route::get("/get/{id}", "show"); // Get a specific assignment
        Route::get("/submissions/{id}", 'getSubmissionsForAssignment');
        Route::get("/submission/{submissionId}", "showSubmission"); // Getting a single submission
        Route::get("/average", "getStudentAverageScore");
        Route::get("/student", "studentAssignmentsWithStatus");
        Route::get('/student/early', 'early');
        Route::get("/stats", 'getAssignmentStatsForAuthenticatedUser');
        Route::get("/marks", 'getAssignmentsWithMarksForAuthenticatedUser');
        Route::get("/group", 'groupedAssignments');
        Route::get("/average/{classId}", "getStudentAveragesForClass");
        Route::post("/create", "store"); // Create an assignment
        Route::post("/submit/{assignmentId}", "submit");
        Route::post("/mark/{submissionId}", "mark");

        Route::put("/update/{id}", "update"); // Update an assignment

        Route::delete("/delete/{id}", "destroy"); // Delete an assignment
        Route::get("/mark/{submissionId}", "getMarks");
    });

Route::prefix("event")->controller(EventController::class)->middleware('auth:sanctum')->group(function () {
    Route::get("/get", "get");
    Route::get("/count", "countMyEvents");
    Route::post("/create", "store");
});

// Downloading resources, assignments
Route::get('/download/{resourceId}/{fileId}', [ResourceController::class, 'download']);
Route::get('/download/{fileId}', [AssignmentController::class, 'download']);


Route::prefix("resources")->controller(ResourceController::class)->middleware('auth:sanctum')->group(function () {
    Route::get("/get", "index");  // Route to get all resources
    Route::get("/count", "countAllLecturerResources");
    Route::get('/get/{id}', 'show');  // Route to get a specific resource by ID
    Route::get("/lecture", "getAllResourcesForAuthenticatedLecture");
    Route::get("/class/{classId}", "getResourcesByClassId");

    Route::post("/create", "store");  // Route to create a resource
    Route::post('/upload-files', 'uploadFiles');  // Route for handling file uploads (if necessary)

    Route::put("/update/{id}", "update");  // Route to update a resource
    Route::delete("/delete/{id}", "destroy");  // Route to delete a resource
    Route::delete('/resource/file/{fileId}', "deleteFile");
});

Route::prefix("ratings")->controller(RatingsController::class)->middleware('auth:sanctum')->group(function () {
    Route::get("/get", "getUserAverageRating");
    Route::get("/get/{lectureId}", "getLectureRating");

    Route::post("/rate/{lectureId}", "rateLecture");
});


Route::get('/storage/{filename}', function ($filename) {
    $path = 'submissions/' . $filename;

    if (!Storage::disk('public')->exists($path)) {
        abort(404);
    }

    $file = Storage::disk('public')->get($path);
    $type = Storage::disk('public')->mimeType($path);

    return Response::make($file, 200, [
        'Content-Type' => $type,
        'Access-Control-Allow-Origin' => '*',
    ]);
});