<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Models\ClassModel;
use App\Models\ClassStudents;
use App\Models\Announcement;
use App\Models\Lecture;
use Illuminate\Http\JsonResponse;
use Carbon\Carbon;
use App\Models\Discussion;
use App\Models\Attendee;
use Illuminate\Support\Facades\Auth;  

class ClassController extends Controller
{
    /**
     * Create
     * 
     * Create a class
     */
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

    /**
     * getLecturerClasses
     * 
     * Return all classes for a lecturer
     */

    public function lectureClasses(Request $request){

        $classes = ClassModel::where('lecture_id', $request->user()->id)->get();

        return response()->json([
            'status' => 'success',
            'data' => $classes
        ], 200);
    }

    /**
     * getClassDetails
     * 
     * This will return details about a selected class. It will return the total number of enrolled students, an array of announcements and assignmnets combined
     * , the name of the class and its class code
     */

    public function getClassById($id){
        // Find the class by its ID
        $class = ClassModel::select('name', "class_code")->find($id);
        $enrolledStudents = ClassStudents::where('classe_id', $id)->count();
        $announcements = $this->getCombinedAssignmentsAndAnnouncements($id);

        // If the class is not found, return an error response
        if (!$class) {
            return response()->json([
                'message' => 'Class not found'
            ], 404);
        }

        // Return the class data
        return response()->json([
            'data' => $class,
            'total'=>$enrolledStudents,
            "announcements" => $announcements
        ], 200);
    }

    /**
     * Join
     * 
     * Joining a class by class code
     */
    public function join(Request $request){
        // Validate the request
        $request->validate([
            'code' => 'required|string|exists:classes,class_code',
        ]);

        // Find the class by code
        $class = ClassModel::where('class_code', $request->code)->first();

        // Check if the student is already in the class
        $alreadyJoined = ClassStudents::where('classe_id', $class->id)
            ->where('student_id', $request->user()->id)
            ->exists();

        if ($alreadyJoined) {
            return response()->json([
                "message" => "You have already joined this class"
            ], 409); 
        }

        // Add the student to the class
        $classStudent = new ClassStudents();
        $classStudent->classe_id = $class->id;
        $classStudent->student_id = $request->user()->id;
        $classStudent->save();

        return response()->json([
            "message" => "Joined successfully"
        ], 201);
    }

    /**
     * getStudentClasses
     * 
     * Return all classes joined by a student
     */

    public function studentClasses(Request $request){
        $classes = ClassStudents::where('student_id', $request->user()->id)
            ->join('classes', 'classstudents.classe_id', '=', 'classes.id') // Join with ClassModel
            ->join('lectures', 'classes.lecture_id', '=', 'lectures.id') // Join with Lecture
            ->get(['classes.id as class_id', 'classes.name as class_name', 'lectures.fullname as lecture_name']);
    
        return response()->json([
            'data' => $classes
        ], 200);
    }
    
    /**
     * getClassStudents
     * 
     * Return all students for a given class
     */
    public function getStudents($classId){
        $class = ClassModel::with('students')->findOrFail($classId);
        return response()->json([
            "name" => $class['name'],
            "students"=>$class->students]);
    }

    /**
     * announcementsAndResources
     * 
     * This will combine all announcements and resources for a given class
     */
    public function getCombinedAssignmentsAndAnnouncements($classId)
    {
    // Get announcements and assignments combined for a specific class
    $announcements = DB::table('announcements')
        ->select('id', 'title', 'description', 'created_at', DB::raw("'announcement' as type"))
        ->where('class_id', $classId)  // Filter by class_id
        ->union(
            DB::table('assignments')
                ->select('id', 'title', 'description', 'created_at', DB::raw("'assignment' as type"))
                ->where('class_id', $classId)  // Filter by class_id
        )
        ->orderBy('created_at', 'desc') // Sort by created_at in descending order
        ->get();

        return $announcements;
    }   

    /**
     * Create Discussion
     * 
     * This will create a discussion for students in a specific class only
     */
    public function createDiscussion(Request $request, $classId)
    {
        $request->validate([
            'meeting_name' => 'required|string|max:255',
            'start_time' => 'required|date',
        ]);
    
        $discussion = Discussion::create([
            'class_id' => $classId,
            'meeting_name' => $request->meeting_name,
            'start_time' => Carbon::parse($request->start_time),
            // meeting_identifier will be generated in the model if not provided
        ]);
    
        return response()->json([
            'message' => 'Discussion created successfully.',
            'data' => $discussion,
        ], 201);
    }

    /**
     * Count Lecturer Classes
     * 
     * Return the total number of classes created by the authenticated lecturer.
     * 
     * @param Request $request
     * @return JsonResponse
     *
     * @authenticated
     *
     * @response 200 {
     *   "total_classes": 5
     * }
     */
    public function countLecturerClasses(Request $request): JsonResponse
    {
        // Get the authenticated lecturer's ID
        $lecturerId = $request->user()->id;

        // Count the number of classes created by this lecturer
        $classCount = ClassModel::where('lecture_id', $lecturerId)->count();

        // Return the count in JSON format
        return response()->json([
            'total_classes' => $classCount
        ]);
    }

    /**
     * Get Class Discussions
     * 
     * Return all discussions from a certain class
     */
    public function getByClassId($classId)
    {
        $class = ClassModel::with('discussions')->find($classId);

        if (!$class) {
            return response()->json([
                'message' => 'Class not found.'
            ], 404);
        }

        return response()->json([
            'class_name' => $class->name,
            'discussions' => $class->discussions()->orderBy('start_time', 'desc')->get(),
        ]);
    }

    /**
     * Fetch Student Disscussions
     * 
     * Fetch all discussions for the authenticated student.
     */
    public function getStudentDiscussions(Request $request)
    {
        $student = Auth::user(); 
        $classes = $student->classes; 

        $discussions = [];

        foreach ($classes as $class) {
            $discussions[] = $class->discussions;  
        }

        $discussions = collect($discussions)->flatten();

        if ($discussions->isEmpty()) {
            return response()->json([
                'message' => 'No discussions found for this student.',
            ], 404);
        }

        // Return discussions as a JSON response
        return response()->json([
            'discussions' => $discussions,
        ]);
    }

    /**
     * Get a single discussion
     * 
     * Get a single discussion by its ID.
     */
    public function getDiscussionById($discussionId)
    {
        // Fetch the discussion by its ID
        $discussion = Discussion::find($discussionId);

        // If the discussion is not found, return an error message
        if (!$discussion) {
            return response()->json([
                'message' => 'Discussion not found.',
            ], 404);
        }

        // Return the discussion data as a JSON response
        return response()->json([
            'discussion' => $discussion,
        ], 200);
    }


    /*
    * Capture the attended meeting
    *
    * Pass the discussion id for the current discussion
    */
    public function attendDiscussion($discussionId)
    {
        $userId = Auth::id();

        // Check if the discussion exists
        $discussion = Discussion::find($discussionId);
        if (!$discussion) {
            return response()->json(['message' => 'Discussion not found.'], 404);
        }

        // Check if the user already attended
        $alreadyAttended = Attendee::where('discussion_id', $discussionId)
            ->where('student_id', $userId)
            ->exists();

        if ($alreadyAttended) {
            return response()->json(['message' => 'You have already attended this discussion.'], 409);
        }

        // Create attendance
        Attendee::create([
            'discussion_id' => $discussionId,
            'student_id' => $userId
        ]);

        return response()->json(['message' => 'Attendance recorded successfully.'], 201);
    }

    /**
     * Get Discussion Attendance Summary
     * 
     * Returns students who attended and who did not attend a specific discussion.
     */
    public function getDiscussionAttendance($discussionId)
    {
        // Find the discussion and related class
        $discussion = Discussion::find($discussionId);

        if (!$discussion) {
            return response()->json(['message' => 'Discussion not found.'], 404);
        }

        $classId = $discussion->class_id;

        // Get all student IDs in the class
        $classStudents = ClassStudents::where('classe_id', $classId)->pluck('student_id');

        // Get student IDs who attended the discussion
        $attendedStudents = Attendee::where('discussion_id', $discussionId)->pluck('student_id');

        // Get student IDs who did not attend
        $notAttendedStudents = $classStudents->diff($attendedStudents);

        // Get student names and emails
        $attended = DB::table('students')
            ->whereIn('id', $attendedStudents)
            ->select('fullname', 'email')
            ->get();

        $notAttended = DB::table('students')
            ->whereIn('id', $notAttendedStudents)
            ->select('fullname', 'email')
            ->get();

        return response()->json([
            'discussion' => $discussion->meeting_name,
            'attended' => $attended,
            'not_attended' => $notAttended
        ]);
    }

    /**
     * Get Discussion Summary for Authenticated Student
     *
     * Returns total discussions related to the student's classes and how many they've attended.
     */
    public function getMyDiscussionSummary()
    {
        $student = auth()->user(); // or auth('student')->user() if using a custom guard

        if (!$student) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        // Get class IDs the student is enrolled in
        $classIds = ClassStudents::where('student_id', $student->id)->pluck('classe_id');

        // Get total discussions across those classes
        $totalDiscussions = Discussion::whereIn('class_id', $classIds)->count();

        // Count of discussions attended by the student
        $attendedDiscussions = Attendee::where('student_id', $student->id)->count();

        return response()->json([
            'student_name' => $student->fullname,
            'student_email' => $student->email,
            'total_discussions' => $totalDiscussions,
            'attended_discussions' => $attendedDiscussions
        ]);
    }

}
