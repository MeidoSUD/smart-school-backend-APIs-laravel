<?php
use Illuminate\Support\Facades\Route;
use Modules\Academic\Http\Controllers\Api\AttendenceController;
use Modules\Academic\Http\Controllers\Api\ExamController;
use Modules\Academic\Http\Controllers\Api\ExamScheduleController;
use Modules\Academic\Http\Controllers\Api\HomeworkController;
use Modules\Academic\Http\Controllers\Api\MarkController;
use Modules\Academic\Http\Controllers\Api\OnlineExamController;
use Modules\Academic\Http\Controllers\Api\SubjectController;
use Modules\Academic\Http\Controllers\Api\SyllabusController;
use Modules\Academic\Http\Controllers\Api\TimelineController;
use Modules\Academic\Http\Controllers\Api\TimetableController;
use Modules\Academic\Http\Controllers\Api\ApplyLeaveController;
use Modules\Academic\Http\Controllers\Api\CalendarController;

Route::middleware('auth:sanctum')->prefix('api')->group(function () {
    Route::get('/attendence', [AttendenceController::class, 'index']);
    Route::get('/attendence/getAttendence', [AttendenceController::class, 'getAttendence']);
    Route::post('/attendence/getdaysubattendence', [AttendenceController::class, 'getdaysubattendence']);

    Route::get('/exam', [ExamController::class, 'index']);
    Route::get('/exam/{id}', [ExamController::class, 'view']);
    Route::post('/exam/examresult', [ExamController::class, 'examresult']);
    Route::get('/examschedule', [ExamScheduleController::class, 'index']);

    Route::get('/homework', [HomeworkController::class, 'index']);
    Route::get('/homework/homework_detail/{id}/{status}', [HomeworkController::class, 'homework_detail']);
    Route::post('/homework/upload_docs', [HomeworkController::class, 'upload_docs']);

    Route::get('/mark/marklist', [MarkController::class, 'marklist']);

    Route::get('/onlineexam', [OnlineExamController::class, 'index']);
    Route::get('/onlineexam/{id}', [OnlineExamController::class, 'exam_detail']);
    Route::post('/onlineexam/submit', [OnlineExamController::class, 'submit']);

    Route::get('/subject', [SubjectController::class, 'index']);
    Route::get('/subject/{id}', [SubjectController::class, 'view']);

    Route::get('/syllabus', [SyllabusController::class, 'index']);
    Route::get('/syllabus/status', [SyllabusController::class, 'status']);
    Route::get('/syllabus/download/{id}', [SyllabusController::class, 'download']);
    Route::post('/syllabus/addmessage', [SyllabusController::class, 'addmessage']);

    Route::post('/timeline/add', [TimelineController::class, 'add']);
    Route::get('/timetable', [TimetableController::class, 'index']);
    Route::post('/timetable', [TimetableController::class, 'store']);
    Route::put('/timetable/{id}', [TimetableController::class, 'update']);
    Route::delete('/timetable/{id}', [TimetableController::class, 'destroy']);

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
});
