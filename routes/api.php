<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\AdmissionController;
use App\Http\Controllers\Api\AttendenceController;
use App\Http\Controllers\Api\BookController;
use App\Http\Controllers\Api\CalendarController;
use App\Http\Controllers\Api\ChatController;
use App\Http\Controllers\Api\ContentController;
use App\Http\Controllers\Api\ExamController;
use App\Http\Controllers\Api\ExamScheduleController;
use App\Http\Controllers\Api\HomeworkController;
use App\Http\Controllers\Api\HostelController;
use App\Http\Controllers\Api\HostelRoomController;
use App\Http\Controllers\Api\MarkController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\OfflinePaymentController;
use App\Http\Controllers\Api\OnlineExamController;
use App\Http\Controllers\Api\RouteController;
use App\Http\Controllers\Api\SubjectController;
use App\Http\Controllers\Api\SyllabusController;
use App\Http\Controllers\Api\TeacherController;
use App\Http\Controllers\Api\TimelineController;
use App\Http\Controllers\Api\TimetableController;
use App\Http\Controllers\Api\VideoTutorialController;
use App\Http\Controllers\Api\VisitorController;
use App\Http\Controllers\Api\ApplyLeaveController;

/*
|--------------------------------------------------------------------------
| API Routes - Smart School LMS Mobile API
|--------------------------------------------------------------------------
|
| Converted from CodeIgniter to Laravel
| All routes map to the existing mobile_api.dart endpoints
|
*/

// ═══════════════════════════════════════════════════════════════════════════
// PUBLIC ROUTES (No Authentication Required)
// ═══════════════════════════════════════════════════════════════════════════

Route::prefix('auth')->group(function () {
    // POST /api/auth/login - User login
    Route::post('/login', [AuthController::class, 'login'])->name('api.auth.login');
});

// Admission (public endpoints)
Route::prefix('admission')->group(function () {
    Route::get('/', [AdmissionController::class, 'index']);
    Route::get('/form_config', [AdmissionController::class, 'form_config']);
    Route::get('/classes', [AdmissionController::class, 'classes']);
    Route::get('/sections', [AdmissionController::class, 'sections']);
    Route::post('/submit', [AdmissionController::class, 'submit']);
    Route::get('/status', [AdmissionController::class, 'status']);
});

// ═══════════════════════════════════════════════════════════════════════════
// PROTECTED ROUTES (Authentication Required)
// ═══════════════════════════════════════════════════════════════════════════

Route::middleware('api.token')->group(function () {

    // ───────────────────────────────────────────────────────────────────────
    // AUTH ROUTES
    // ───────────────────────────────────────────────────────────────────────
    Route::prefix('auth')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::post('/changepass', [AuthController::class, 'changePassword']);
    });

    // ───────────────────────────────────────────────────────────────────────
    // USER ROUTES
    // ───────────────────────────────────────────────────────────────────────
    Route::prefix('user')->group(function () {
        Route::get('/dashboard', [UserController::class, 'dashboard']);
        Route::get('/profile', [UserController::class, 'profile']);
        Route::get('/fees', [UserController::class, 'fees']);
        Route::get('/getfees', [UserController::class, 'getfees']);
    });

    // ───────────────────────────────────────────────────────────────────────
    // ATTENDANCE ROUTES
    // ───────────────────────────────────────────────────────────────────────
    Route::get('/attendence', [AttendenceController::class, 'index']);
    Route::get('/attendence/getAttendence', [AttendenceController::class, 'getAttendence']);
    Route::post('/attendence/getdaysubattendence', [AttendenceController::class, 'getdaysubattendence']);

    // ───────────────────────────────────────────────────────────────────────
    // BOOK ROUTES
    // ───────────────────────────────────────────────────────────────────────
    Route::get('/book', [BookController::class, 'index']);
    Route::get('/book/issue', [BookController::class, 'issue']);

    // ───────────────────────────────────────────────────────────────────────
    // CALENDAR ROUTES
    // ───────────────────────────────────────────────────────────────────────
    Route::get('/calendar', [CalendarController::class, 'index']);
    Route::get('/calendar/getevents', [CalendarController::class, 'getevents']);
    Route::post('/calendar/addtodo', [CalendarController::class, 'addtodo']);
    Route::get('/calendar/{id}', [CalendarController::class, 'gettaskbyid']);
    Route::post('/calendar/markcomplete/{id}', [CalendarController::class, 'markcomplete']);
    Route::delete('/calendar/{id}', [CalendarController::class, 'delete_event']);

    // ───────────────────────────────────────────────────────────────────────
    // CHAT ROUTES
    // ───────────────────────────────────────────────────────────────────────
    Route::get('/chat/myuser', [ChatController::class, 'myuser']);
    Route::post('/chat/getChatRecord', [ChatController::class, 'getChatRecord']);
    Route::post('/chat/newMessage', [ChatController::class, 'newMessage']);

    // ───────────────────────────────────────────────────────────────────────
    // CONTENT ROUTES
    // ───────────────────────────────────────────────────────────────────────
    Route::get('/content/list', [ContentController::class, 'list']);
    Route::get('/content/getsharelist', [ContentController::class, 'getsharelist']);
    Route::get('/content/{id}', [ContentController::class, 'view']);
    Route::get('/content/assignment', [ContentController::class, 'assignment']);
    Route::get('/content/studymaterial', [ContentController::class, 'studymaterial']);

    // ───────────────────────────────────────────────────────────────────────
    // EXAM ROUTES
    // ───────────────────────────────────────────────────────────────────────
    Route::get('/exam', [ExamController::class, 'index']);
    Route::get('/exam/{id}', [ExamController::class, 'view']);
    Route::post('/exam/examresult', [ExamController::class, 'examresult']);
    Route::get('/examschedule', [ExamScheduleController::class, 'index']);

    // ───────────────────────────────────────────────────────────────────────
    // HOMEWORK ROUTES
    // ───────────────────────────────────────────────────────────────────────
    Route::get('/homework', [HomeworkController::class, 'index']);
    Route::get('/homework/homework_detail/{id}/{status}', [HomeworkController::class, 'homework_detail']);
    Route::post('/homework/upload_docs', [HomeworkController::class, 'upload_docs']);

    // ───────────────────────────────────────────────────────────────────────
    // HOSTEL ROUTES
    // ───────────────────────────────────────────────────────────────────────
    Route::get('/hostel', [HostelController::class, 'index']);
    Route::get('/hostel/room', [HostelRoomController::class, 'index']);

    // ───────────────────────────────────────────────────────────────────────
    // MARK ROUTES
    // ───────────────────────────────────────────────────────────────────────
    Route::get('/mark/marklist', [MarkController::class, 'marklist']);

    // ───────────────────────────────────────────────────────────────────────
    // NOTIFICATION ROUTES
    // ───────────────────────────────────────────────────────────────────────
    Route::get('/notification', [NotificationController::class, 'index']);
    Route::post('/notification/updatestatus', [NotificationController::class, 'updatestatus']);

    // ───────────────────────────────────────────────────────────────────────
    // OFFLINE PAYMENT ROUTES
    // ───────────────────────────────────────────────────────────────────────
    Route::get('/offlinepayment', [OfflinePaymentController::class, 'index']);
    Route::post('/offlinepayment/add', [OfflinePaymentController::class, 'add']);

    // ───────────────────────���───────────────────────────────────────────────
    // ONLINE EXAM ROUTES
    // ───────────────────────────────────────────────────────────────────────
    Route::get('/onlineexam', [OnlineExamController::class, 'index']);
    Route::get('/onlineexam/{id}', [OnlineExamController::class, 'exam_detail']);
    Route::post('/onlineexam/submit', [OnlineExamController::class, 'submit']);

    // ───────────────────────────────────────────────────────────────────────
    // TRANSPORT ROUTES
    // ───────────────────────────────────────────────────────────────────────
    Route::get('/route', [RouteController::class, 'index']);
    Route::post('/route/getbusdetail', [RouteController::class, 'getbusdetail']);

    // ───────────────────────────────────────────────────────────────────────
    // SUBJECT ROUTES
    // ───────────────────────────────────────────────────────────────────────
    Route::get('/subject', [SubjectController::class, 'index']);
    Route::get('/subject/{id}', [SubjectController::class, 'view']);

    // ───────────────────────────────────────────────────────────────────────
    // SYLLABUS ROUTES
    // ───────────────────────────────────────────────────────────────────────
    Route::get('/syllabus', [SyllabusController::class, 'index']);
    Route::get('/syllabus/status', [SyllabusController::class, 'status']);
    Route::get('/syllabus/download/{id}', [SyllabusController::class, 'download']);
    Route::post('/syllabus/addmessage', [SyllabusController::class, 'addmessage']);

    // ───────────────────────────────────────────────────────────────────────
    // TEACHER ROUTES
    // ───────────────────────────────────────────────────────────────────────
    Route::get('/teacher', [TeacherController::class, 'index']);
    Route::post('/teacher/rating', [TeacherController::class, 'rating']);

    // ───────────────────────────────────────────────────────────────────────
    // TIMELINE ROUTES
    // ─────────────────────────────────────────────────��─────────────────────
    Route::post('/timeline/add', [TimelineController::class, 'add']);

    // ───────────────────────────────────────────────────────────────────────
    // TIMETABLE ROUTES
    // ───────────────────────────────────────────────────────────────────────
    Route::get('/timetable', [TimetableController::class, 'index']);

    // ───────────────────────────────────────────────────────────────────────
    // VIDEO TUTORIAL ROUTES
    // ───────────────────────────────────────────────────────────────────────
    Route::get('/video_tutorial', [VideoTutorialController::class, 'index']);
    Route::get('/video_tutorial/{id}', [VideoTutorialController::class, 'view']);

    // ───────────────────────────────────────────────────────────────────────
    // VISITORS ROUTES
    // ───────────────────────────────────────────────────────────────────────
    Route::get('/visitors', [VisitorController::class, 'index']);

    // ───────────────────────────────────────────────────────────────────────
    // APPLY LEAVE ROUTES
    // ───────────────────────────────────────────────────────────────────────
    Route::get('/apply_leave', [ApplyLeaveController::class, 'index']);
    Route::get('/apply_leave/{id}', [ApplyLeaveController::class, 'get_details']);
    Route::post('/apply_leave/add', [ApplyLeaveController::class, 'add']);
    Route::delete('/apply_leave/{id}', [ApplyLeaveController::class, 'remove_leave']);
});

// ═══════════════════════════════════════════════════════════════════════════
// HEALTH CHECK ROUTE
// ═══════════════════════════════════════════════════════════════════════════
Route::get('/ping', function () {
    return response()->json([
        'status' => 'success',
        'message' => 'API is working',
        'timestamp' => now()->toDateTimeString(),
        'php_version' => PHP_VERSION,
    ]);
})->name('api.ping');