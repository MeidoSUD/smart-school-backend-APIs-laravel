<?php

use Illuminate\Support\Facades\Route;
use Modules\Academic\Http\Controllers\Api\ApplyLeaveController;
use Modules\Academic\Http\Controllers\Api\AttendenceController;
use Modules\Academic\Http\Controllers\Api\CalendarController;
use Modules\Academic\Http\Controllers\Api\ExamController;
use Modules\Academic\Http\Controllers\Api\ExamScheduleController;
use Modules\Academic\Http\Controllers\Api\HomeworkController;
use Modules\Academic\Http\Controllers\Api\MarkController;
use Modules\Academic\Http\Controllers\Api\OnlineExamController;
use Modules\Academic\Http\Controllers\Api\SubjectController;
use Modules\Academic\Http\Controllers\Api\SyllabusController;
use Modules\Academic\Http\Controllers\Api\TimelineController;
use Modules\Academic\Http\Controllers\Api\TimetableController;

Route::middleware('auth:sanctum')->prefix('api')->group(function () {
    Route::get('/attendence', [AttendenceController::class, 'index']);
    Route::get('/attendence/getAttendence', [AttendenceController::class, 'getAttendence']);
    Route::get('/attendence/getevents', [AttendenceController::class, 'getevents']);
    Route::post('/attendence/getdaysubattendence', [AttendenceController::class, 'getdaysubattendence']);

    // G-1.4/1.5/1.6: must precede /exam/{id} or the wildcard swallows them.
    Route::get('/exam/getByFeecategory', [ExamController::class, 'getByFeecategory']);
    Route::match(['get', 'post'], '/exam/examSearch', [ExamController::class, 'examSearch']);
    Route::post('/exam/getStudentCategoryFee', [ExamController::class, 'getStudentCategoryFee']);
    Route::get('/exam', [ExamController::class, 'index']);
    Route::post('/exam/examresult', [ExamController::class, 'examresult']);
    Route::get('/exam/{id}', [ExamController::class, 'view']);
    Route::get('/examschedule', [ExamScheduleController::class, 'index']);
    Route::post('/examschedule/getexamscheduledetail', [ExamScheduleController::class, 'getexamscheduledetail']);

    Route::get('/homework', [HomeworkController::class, 'index']);
    Route::get('/homework/homework_detail/{id}/{status}', [HomeworkController::class, 'homework_detail']);
    Route::post('/homework/upload_docs', [HomeworkController::class, 'upload_docs']);
    Route::get('/homework/download/{id}', [HomeworkController::class, 'download']);
    Route::get('/homework/assigmnetDownload/{id}', [HomeworkController::class, 'assigmnetDownload']);

    Route::get('/homework/dailyassignment', [HomeworkController::class, 'dailyassignment']);
    Route::get('/homework/getsingle_dailyassignment/{id}', [HomeworkController::class, 'getsingledailyassignment']);
    Route::post('/homework/createdailyassignment', [HomeworkController::class, 'createdailyassignment']);
    Route::post('/homework/updatedailyassignment', [HomeworkController::class, 'updatedailyassignment']);
    Route::delete('/homework/deletedailyassignment/{id}', [HomeworkController::class, 'deletedailyassignment']);
    Route::get('/homework/dailyassigmnetdownload/{id}', [HomeworkController::class, 'dailyassigmnetdownload']);

    Route::get('/mark/marklist', [MarkController::class, 'marklist']);
    Route::get('/mark', [MarkController::class, 'index']);
    Route::get('/mark/{id}', [MarkController::class, 'view']);

    Route::get('/onlineexam', [OnlineExamController::class, 'index']);
    Route::get('/onlineexam/closed', [OnlineExamController::class, 'closed']);
    Route::get('/onlineexam/{id}', [OnlineExamController::class, 'exam_detail']);
    Route::post('/onlineexam/startexam', [OnlineExamController::class, 'startexam']);
    Route::post('/onlineexam/submit', [OnlineExamController::class, 'submit']);
    Route::get('/onlineexam/downloadattachment/{doc}', [OnlineExamController::class, 'downloadattachment']);

    Route::get('/subject', [SubjectController::class, 'index']);
    Route::get('/subject/{id}', [SubjectController::class, 'view']);

    Route::get('/syllabus', [SyllabusController::class, 'index']);
    Route::post('/syllabus/get_weekdates', [SyllabusController::class, 'getWeekdates']);
    Route::get('/syllabus/status', [SyllabusController::class, 'status']);
    Route::get('/syllabus/download/{id}', [SyllabusController::class, 'download']);
    Route::get('/syllabus/lacture_video_download/{id}', [SyllabusController::class, 'lactureVideoDownload']);
    Route::post('/syllabus/get_subject_syllabus', [SyllabusController::class, 'subjectSyllabus']);
    Route::get('/syllabus/get_subject_syllabus/{id}', [SyllabusController::class, 'subjectSyllabus']);
    Route::post('/syllabus/check_subject_syllabus', [SyllabusController::class, 'checkSubjectSyllabus']);
    Route::post('/syllabus/addmessage', [SyllabusController::class, 'addmessage']);
    Route::post('/syllabus/getmessage', [SyllabusController::class, 'getmessage']);
    Route::post('/syllabus/deletemessage', [SyllabusController::class, 'deletemessage']);

    Route::get('/timeline', [TimelineController::class, 'list']);
    Route::post('/timeline', [TimelineController::class, 'add']);
    Route::post('/timeline/add', [TimelineController::class, 'add']);
    Route::get('/timeline/getstudentsingletimeline', [TimelineController::class, 'getstudentsingletimeline']);
    Route::get('/timeline/{id}', [TimelineController::class, 'getstudentsingletimeline']);
    Route::put('/timeline/{id}', [TimelineController::class, 'edit']);
    Route::delete('/timeline/{id}', [TimelineController::class, 'delete_timeline']);
    Route::get('/timeline/download/{id}', [TimelineController::class, 'download']);
    // G-4.8 alias: CI api/user/User.php timeline_download($timeline_id,$doc)
    Route::get('/user/timeline_download/{id}/{doc?}', [TimelineController::class, 'download']);
    Route::get('/timetable', [TimetableController::class, 'index']);
    Route::post('/timetable', [TimetableController::class, 'store']);
    Route::put('/timetable/{id}', [TimetableController::class, 'update']);
    Route::delete('/timetable/{id}', [TimetableController::class, 'destroy']);

    Route::get('/apply_leave', [ApplyLeaveController::class, 'index']);
    Route::match(['get', 'post'], '/apply_leave/get_details/{id}', [ApplyLeaveController::class, 'get_details']);
    Route::get('/apply_leave/download/{id}', [ApplyLeaveController::class, 'download']);
    Route::post('/apply_leave/add', [ApplyLeaveController::class, 'add']);
    Route::match(['get', 'post', 'delete'], '/apply_leave/remove_leave/{id}', [ApplyLeaveController::class, 'remove_leave']);
    Route::get('/apply_leave/{id}', [ApplyLeaveController::class, 'get_details']);
    Route::delete('/apply_leave/{id}', [ApplyLeaveController::class, 'remove_leave']);

    Route::get('/calendar', [CalendarController::class, 'index']);
    Route::get('/calendar/getevents', [CalendarController::class, 'getevents']);
    Route::post('/calendar/addtodo', [CalendarController::class, 'addtodo']);
    Route::get('/calendar/{id}', [CalendarController::class, 'gettaskbyid']);
    Route::post('/calendar/markcomplete/{id}', [CalendarController::class, 'markcomplete']);
    Route::delete('/calendar/{id}', [CalendarController::class, 'delete_event']);
});
