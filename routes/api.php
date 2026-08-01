<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\RoleController;
use App\Http\Controllers\Api\SessionController;
use App\Http\Controllers\Api\ClassSectionController;
use App\Http\Controllers\Api\SubjectController;
use App\Http\Controllers\Api\SubjectGroupController;
use App\Http\Controllers\Api\LessonController;
use App\Http\Controllers\Api\TopicController;
use App\Http\Controllers\Api\GradeController;
use App\Http\Controllers\Api\HomeworkController;
use App\Http\Controllers\Api\ExamController;
use App\Http\Controllers\Api\ExamScheduleController;
use App\Http\Controllers\Api\MarkController;
use App\Http\Controllers\Api\OnlineExamController;
use App\Http\Controllers\Api\SyllabusController;
use App\Http\Controllers\Api\TimetableController;
use App\Http\Controllers\Api\AttendenceController;
use App\Http\Controllers\Api\ApplyLeaveController;
use App\Http\Controllers\Api\CalendarController;
use App\Http\Controllers\Api\TimelineController;
use App\Http\Controllers\Api\StaffController;
use App\Http\Controllers\Api\StaffAttendanceController;
use App\Http\Controllers\Api\StaffLeaveController;
use App\Http\Controllers\Api\StaffPayrollController;
use App\Http\Controllers\Api\StaffPayslipController;
use App\Http\Controllers\Api\StaffDesignationController;
use App\Http\Controllers\Api\TeacherController;
use App\Http\Controllers\Api\FeeGroupController;
use App\Http\Controllers\Api\FeeMasterController;
use App\Http\Controllers\Api\StudentFeeController;
use App\Http\Controllers\Api\StudentFeeMasterController;
use App\Http\Controllers\Api\ExpenseHeadController;
use App\Http\Controllers\Api\ExpenseController;
use App\Http\Controllers\Api\IncomeHeadController;
use App\Http\Controllers\Api\IncomeController;
use App\Http\Controllers\Api\FeeDiscountController;
use App\Http\Controllers\Api\OfflinePaymentController;
use App\Http\Controllers\Api\BookController;
use App\Http\Controllers\Api\TransportRouteController;
use App\Http\Controllers\Api\VehicleController;
use App\Http\Controllers\Api\HostelController;
use App\Http\Controllers\Api\HostelRoomController;
use App\Http\Controllers\Api\RoomTypeController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\OnlineAdmissionController;
use App\Http\Controllers\Api\VisitorController;
use App\Http\Controllers\Api\VideoTutorialController;
use App\Http\Controllers\Api\ContentController;
use App\Http\Controllers\Api\ChatController;
use App\Http\Controllers\Api\AdmissionController;
use App\Http\Controllers\Api\RouteController;

Route::prefix('api')->group(function () {

    // ============ PUBLIC ROUTES ============

    // Auth (public)
    Route::prefix('auth')->group(function () {
        Route::post('/login', [AuthController::class, 'login'])
            ->middleware('throttle:5,1');
    });

    // Online Admissions (public)
    Route::prefix('admission')->group(function () {
        Route::get('/', [AdmissionController::class, 'index']);
        Route::get('/form_config', [AdmissionController::class, 'form_config']);
        Route::get('/classes', [AdmissionController::class, 'classes']);
        Route::get('/sections', [AdmissionController::class, 'sections']);
        Route::post('/submit', [AdmissionController::class, 'submit'])->middleware('throttle:10,1');
        Route::get('/status', [AdmissionController::class, 'status']);
    });

    // ============ PROTECTED ROUTES (auth:sanctum) ============
    Route::middleware('auth:sanctum')->group(function () {

        // --- Auth ---
        Route::prefix('auth')->group(function () {
            Route::post('/logout', [AuthController::class, 'logout']);
            Route::post('/changepass', [AuthController::class, 'changePassword'])
                ->middleware('throttle:5,1');
        });

        // --- User ---
        Route::prefix('user')->group(function () {
            Route::match(['get', 'post'], '/choose', [UserController::class, 'choose']);
            Route::get('/dashboard', [UserController::class, 'dashboard']);
            Route::get('/profile', [UserController::class, 'profile']);
            Route::get('/fees', [UserController::class, 'fees']);
            Route::get('/getfees', [UserController::class, 'getfees']);
        });

        // --- Admin Only ---
        Route::middleware('role:admin')->prefix('admin')->group(function () {
            Route::apiResource('roles', RoleController::class);
            Route::apiResource('sessions', SessionController::class);
        });

        // --- Academic ---
        Route::apiResource('class-sections', ClassSectionController::class);
        Route::apiResource('subject-groups', SubjectGroupController::class);
        Route::apiResource('lessons', LessonController::class);
        Route::apiResource('topics', TopicController::class);
        Route::apiResource('grades', GradeController::class);

        Route::get('/subject', [SubjectController::class, 'index']);
        Route::get('/subject/{id}', [SubjectController::class, 'view']);

        Route::get('/homework', [HomeworkController::class, 'index']);
        Route::get('/homework/homework_detail/{id}/{status}', [HomeworkController::class, 'homework_detail']);
        Route::post('/homework/upload_docs', [HomeworkController::class, 'upload_docs']);

        Route::get('/exam', [ExamController::class, 'index']);
        Route::get('/exam/{id}', [ExamController::class, 'view']);
        Route::post('/exam/examresult', [ExamController::class, 'examresult']);

        Route::get('/examschedule', [ExamScheduleController::class, 'index']);

        Route::get('/mark/marklist', [MarkController::class, 'marklist']);

        Route::get('/onlineexam', [OnlineExamController::class, 'index']);
        Route::get('/onlineexam/{id}', [OnlineExamController::class, 'exam_detail']);
        Route::post('/onlineexam/submit', [OnlineExamController::class, 'submit']);

        Route::get('/syllabus', [SyllabusController::class, 'index']);
        Route::get('/syllabus/status', [SyllabusController::class, 'status']);
        Route::get('/syllabus/download/{id}', [SyllabusController::class, 'download']);
        Route::post('/syllabus/addmessage', [SyllabusController::class, 'addmessage']);

        Route::get('/timetable', [TimetableController::class, 'index']);
        Route::post('/timetable', [TimetableController::class, 'store']);
        Route::put('/timetable/{id}', [TimetableController::class, 'update']);
        Route::delete('/timetable/{id}', [TimetableController::class, 'destroy']);

        Route::get('/attendence', [AttendenceController::class, 'index']);
        Route::get('/attendence/getAttendence', [AttendenceController::class, 'getAttendence']);
        Route::post('/attendence/getdaysubattendence', [AttendenceController::class, 'getdaysubattendence']);

        Route::get('/apply_leave', [ApplyLeaveController::class, 'index']);
        Route::get('/apply_leave/{id}', [ApplyLeaveController::class, 'get_details']);
        Route::post('/apply_leave/add', [ApplyLeaveController::class, 'add']);
        Route::delete('/apply_leave/{id}', [ApplyLeaveController::class, 'remove_leave']);

        Route::get('/calendar', [CalendarController::class, 'index']);
        Route::get('/calendar/getevents', [CalendarController::class, 'getevents']);
        Route::post('/calendar/addtodo', [CalendarController::class, 'addtodo']);
        Route::get('/calendar/{id}', [CalendarController::class, 'gettaskbyid']);
        Route::post('/calendar/markcomplete/{id}', [CalendarController::class, 'markcomplete']);
        Route::delete('/calendar/{id}', [CalendarController::class, 'delete_event']);

        Route::post('/timeline/add', [TimelineController::class, 'add']);

        // --- Staff ---
        Route::apiResource('staff', StaffController::class);
        Route::apiResource('staff-attendance', StaffAttendanceController::class);
        Route::apiResource('staff-leaves', StaffLeaveController::class);
        Route::apiResource('staff-payroll', StaffPayrollController::class);
        Route::apiResource('staff-payslips', StaffPayslipController::class);
        Route::apiResource('staff-designations', StaffDesignationController::class);
        Route::get('/teacher', [TeacherController::class, 'index']);
        Route::post('/teacher/rating', [TeacherController::class, 'rating']);

        // --- Finance ---
        Route::apiResource('fee-groups', FeeGroupController::class);
        Route::apiResource('fee-masters', FeeMasterController::class);
        Route::apiResource('student-fees', StudentFeeController::class);
        Route::apiResource('student-fee-masters', StudentFeeMasterController::class);
        Route::apiResource('expense-heads', ExpenseHeadController::class);
        Route::apiResource('expenses', ExpenseController::class);
        Route::apiResource('income-heads', IncomeHeadController::class);
        Route::apiResource('income', IncomeController::class);
        Route::apiResource('fee-discounts', FeeDiscountController::class);
        Route::get('/offlinepayment', [OfflinePaymentController::class, 'index']);
        Route::post('/offlinepayment/add', [OfflinePaymentController::class, 'add']);

        // --- Operations ---
        Route::apiResource('books', BookController::class);
        Route::get('/book/issue', [BookController::class, 'issue']);

        Route::apiResource('transport-routes', TransportRouteController::class);
        Route::apiResource('vehicles', VehicleController::class);
        Route::apiResource('hostels', HostelController::class);

        Route::get('/hostel/room', [HostelRoomController::class, 'index']);
        Route::post('/hostel/room', [HostelRoomController::class, 'store']);
        Route::get('/hostel/room/{id}', [HostelRoomController::class, 'show']);
        Route::put('/hostel/room/{id}', [HostelRoomController::class, 'update']);
        Route::delete('/hostel/room/{id}', [HostelRoomController::class, 'destroy']);

        Route::get('/room_type', [RoomTypeController::class, 'index']);
        Route::post('/room_type', [RoomTypeController::class, 'store']);
        Route::get('/room_type/{id}', [RoomTypeController::class, 'show']);
        Route::put('/room_type/{id}', [RoomTypeController::class, 'update']);
        Route::delete('/room_type/{id}', [RoomTypeController::class, 'destroy']);

        Route::apiResource('online-admissions', OnlineAdmissionController::class);

        Route::get('/visitors', [VisitorController::class, 'index']);

        Route::get('/video_tutorial', [VideoTutorialController::class, 'index']);
        Route::get('/video_tutorial/{id}', [VideoTutorialController::class, 'view']);

        Route::get('/content/list', [ContentController::class, 'list']);
        Route::get('/content/getsharelist', [ContentController::class, 'getsharelist']);
        Route::get('/content/assignment', [ContentController::class, 'assignment']);
        Route::get('/content/studymaterial', [ContentController::class, 'studymaterial']);
        Route::get('/content/syllabus', [ContentController::class, 'syllabus']);
        Route::get('/content/other', [ContentController::class, 'other']);
        Route::get('/content/download/{file}', [ContentController::class, 'download']);
        Route::get('/content/{id}', [ContentController::class, 'view']);

        // --- Notifications ---
        Route::get('/notification', [NotificationController::class, 'index']);
        Route::post('/notification', [NotificationController::class, 'store']);
        Route::get('/notification/{id}', [NotificationController::class, 'show']);
        Route::put('/notification/{id}', [NotificationController::class, 'update']);
        Route::delete('/notification/{id}', [NotificationController::class, 'destroy']);
        Route::post('/notification/updatestatus', [NotificationController::class, 'updatestatus']);
        Route::post('/notification/read', [NotificationController::class, 'read']);
        Route::get('/notification/download/{id}', [NotificationController::class, 'download']);
        Route::post('/notification/detail', [NotificationController::class, 'notification']);

        // --- Transport Routes ---
        Route::get('/route', [RouteController::class, 'index']);
        Route::post('/route/getbusdetail', [RouteController::class, 'getbusdetail']);

        // --- Chat ---
        Route::get('/chat/myuser', [ChatController::class, 'myuser']);
        Route::post('/chat/getChatRecord', [ChatController::class, 'getChatRecord']);
        Route::post('/chat/newMessage', [ChatController::class, 'newMessage'])
            ->middleware('throttle:30,1');
    });
});

// Health check
Route::get('api/ping', fn () => response()->json([
    'status' => 'success',
    'message' => 'API is working',
    'timestamp' => now()->toDateTimeString(),
]))->name('api.ping');
