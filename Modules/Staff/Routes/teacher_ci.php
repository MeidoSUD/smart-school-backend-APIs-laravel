<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Modules\Staff\Http\Controllers\Api\Teacher\TeacherAcademicController;
use Modules\Staff\Http\Controllers\Api\Teacher\TeacherAdmissionSettingController;
use Modules\Staff\Http\Controllers\Api\Teacher\TeacherAttendanceController;
use Modules\Staff\Http\Controllers\Api\Teacher\TeacherCalendarController;
use Modules\Staff\Http\Controllers\Api\Teacher\TeacherCommunicateController;
use Modules\Staff\Http\Controllers\Api\Teacher\TeacherContentController;
use Modules\Staff\Http\Controllers\Api\Teacher\TeacherDisableReasonController;
use Modules\Staff\Http\Controllers\Api\Teacher\TeacherExamController;
use Modules\Staff\Http\Controllers\Api\Teacher\TeacherExamGroupController;
use Modules\Staff\Http\Controllers\Api\Teacher\TeacherHomeworkController;
use Modules\Staff\Http\Controllers\Api\Teacher\TeacherHomeworkExtraController;
use Modules\Staff\Http\Controllers\Api\Teacher\TeacherLeaveController;
use Modules\Staff\Http\Controllers\Api\Teacher\TeacherMyLeaveController;
use Modules\Staff\Http\Controllers\Api\Teacher\TeacherOnlineAdmissionController;
use Modules\Staff\Http\Controllers\Api\Teacher\TeacherOnlineExamManageController;
use Modules\Staff\Http\Controllers\Api\Teacher\TeacherReferenceController;
use Modules\Staff\Http\Controllers\Api\Teacher\TeacherStudentController;
use Modules\Staff\Http\Controllers\Api\Teacher\TeacherStudentReportController;
use Modules\Staff\Http\Controllers\Api\Teacher\TeacherSyllabusController;
use Modules\Staff\Http\Controllers\Api\Teacher\TeacherSyllabusExtraController;
use Modules\Staff\Http\Controllers\Api\Teacher\TeacherTimetableController;

/*
 * Aliases بنفس مسارات CodeIgniter الأصلية تحت بادئة api/ و api/teacher/
 * للبنود الـ55 في plans/teacher_sidebar_actions.json
 * نفس الكنترولرات المستخدمة في teacher.php (لا منطق جديد) — للحفاظ على التوافق
 * القديم api/teacher/* يبقى كما هو، والجديد api/<ci-path> و api/teacher/<ci-path> يعملان جنباً إلى جنب.
 * السبب: Postman يستخدم {{baseUrl}}/teacher/... حيث baseUrl تنتهي بـ /api
 * فيلزم دعم الشكلين: api/admin/staff و api/teacher/admin/staff.
 */
foreach (['api', 'api/teacher'] as $ciPrefix) {
Route::middleware('auth:sanctum')->prefix($ciPrefix)->group(function () {

    // 1.1 student/search — تفاصيل الطلاب
    Route::get('/student/search', [TeacherStudentController::class, 'index']);
    Route::get('/student/search/{id}', [TeacherStudentController::class, 'view'])->whereNumber('id');

    // 1.2 student/create — قبول الطالب
    Route::get('/student/create', [TeacherStudentController::class, 'index']);
    Route::post('/student/create', [TeacherStudentController::class, 'store']);

    // 1.3 admin/onlinestudent — القبول الإلكتروني
    Route::get('/admin/onlinestudent', [TeacherOnlineAdmissionController::class, 'index']);
    Route::get('/admin/onlinestudent/{id}', [TeacherOnlineAdmissionController::class, 'show'])->whereNumber('id');
    Route::put('/admin/onlinestudent/{id}', [TeacherOnlineAdmissionController::class, 'update'])->whereNumber('id');
    Route::delete('/admin/onlinestudent/{id}', [TeacherOnlineAdmissionController::class, 'destroy'])->whereNumber('id');
    Route::get('/admin/onlinestudent/{id}/payment-status', [TeacherOnlineAdmissionController::class, 'paymentStatus'])->whereNumber('id');

    // 1.4 student/disablestudentslist — الطلاب المعطلون
    Route::get('/student/disablestudentslist', [TeacherStudentController::class, 'disabledStudents']);

    // 1.5 student/multiclass — طلاب الفصول المتعددة
    Route::get('/student/multiclass', [TeacherStudentController::class, 'multiClass']);

    // 1.6 student/bulkdelete — الحذف الجماعي
    Route::post('/student/bulkdelete', [TeacherStudentController::class, 'bulkDelete']);
    Route::delete('/student/bulkdelete', [TeacherStudentController::class, 'bulkDelete']);

    // 1.7 category — فئات الطلاب
    Route::get('/category', [TeacherReferenceController::class, 'studentCategories']);

    // 1.8 admin/schoolhouse — بيوت الطلاب
    Route::get('/admin/schoolhouse', [TeacherReferenceController::class, 'studentHouses']);

    // 1.9 admin/disable_reason — أسباب التعطيل
    Route::get('/admin/disable_reason', [TeacherAcademicController::class, 'disableReasons']);
    Route::get('/admin/disable_reason/{id}', [TeacherAcademicController::class, 'disableReasonDetails'])->whereNumber('id');
    Route::post('/admin/disable_reason', [TeacherDisableReasonController::class, 'store']);
    Route::put('/admin/disable_reason/{id}', [TeacherDisableReasonController::class, 'update'])->whereNumber('id');
    Route::delete('/admin/disable_reason/{id}', [TeacherDisableReasonController::class, 'destroy'])->whereNumber('id');

    // 2.1 admin/examgroup — مجموعات الامتحانات
    Route::get('/admin/examgroup', [TeacherExamGroupController::class, 'examGroups']);
    Route::get('/admin/examgroup/{id}/exams', [TeacherExamGroupController::class, 'groupExams'])->whereNumber('id');
    Route::get('/admin/examgroup/subjects/{id}', [TeacherExamGroupController::class, 'examSubjects'])->whereNumber('id');
    Route::post('/admin/examgroup/marks/entry', [TeacherExamGroupController::class, 'entryMarks']);

    // 2.2 admin/exam_schedule — جدول الامتحانات
    Route::get('/admin/exam_schedule', [TeacherExamGroupController::class, 'examSchedules']);

    // 2.3 admin/examresult — نتائج الامتحانات
    Route::get('/admin/examresult', [TeacherExamGroupController::class, 'rankReport']);
    Route::get('/admin/examresult/student/{id}', [TeacherExamGroupController::class, 'studentResult'])->whereNumber('id');
    Route::get('/admin/examresult/rank', [TeacherExamGroupController::class, 'rankReport']);

    // 2.4 admin/admitcard — تصميم بطاقة الدخول
    Route::get('/admin/admitcard', [TeacherExamGroupController::class, 'admitCardTemplates']);

    // 2.5 admin/examresult/admitcard — طباعة بطاقة الدخول
    Route::get('/admin/examresult/admitcard', [TeacherExamGroupController::class, 'admitCards']);

    // 2.6 admin/marksheet — تصميم كشف الدرجات
    Route::get('/admin/marksheet', [TeacherExamGroupController::class, 'marksheetTemplates']);

    // 2.7 admin/examresult/marksheet — طباعة كشف الدرجات
    Route::get('/admin/examresult/marksheet', [TeacherExamGroupController::class, 'marksheet']);

    // 2.8 admin/grade — سلالم التقديرات
    Route::get('/admin/grade', [TeacherReferenceController::class, 'grades']);

    // 3.1 admin/stuattendence — تحضير الطلاب
    Route::get('/admin/stuattendence', [TeacherAttendanceController::class, 'index']);
    Route::post('/admin/stuattendence', [TeacherAttendanceController::class, 'store']);
    Route::get('/admin/stuattendence/month', [TeacherAttendanceController::class, 'monthAttendance']);

    // 3.2 admin/approve_leave — اعتماد الإجازات
    Route::get('/admin/approve_leave', [TeacherLeaveController::class, 'index']);
    Route::post('/admin/approve_leave/status', [TeacherLeaveController::class, 'setStatus']);

    // 3.3 admin/stuattendence/attendencereport — الحضور حسب التاريخ
    Route::get('/admin/stuattendence/attendencereport', [TeacherAttendanceController::class, 'report']);

    Route::get('/admin/onlineexam', [TeacherExamController::class, 'onlineExams']);
    Route::get('/admin/onlineexam/getexamlist', [TeacherExamController::class, 'getexamlist']);
    Route::get('/admin/onlineexam/getclosedexamlist', [TeacherExamController::class, 'getclosedexamlist']);
    Route::post('/admin/onlineexam', [TeacherOnlineExamManageController::class, 'store']);
    Route::delete('/admin/onlineexam/{id}', [TeacherOnlineExamManageController::class, 'destroy'])->whereNumber('id');
    Route::get('/admin/onlineexam/{id}/questions', [TeacherOnlineExamManageController::class, 'examQuestions'])->whereNumber('id');
    Route::post('/admin/onlineexam/questions/attach', [TeacherOnlineExamManageController::class, 'attachQuestions']);
    Route::post('/admin/onlineexam/questions/detach', [TeacherOnlineExamManageController::class, 'detachQuestion']);
    Route::post('/admin/onlineexam/assign', [TeacherOnlineExamManageController::class, 'assignStudents']);
    Route::post('/admin/onlineexam/marks', [TeacherOnlineExamManageController::class, 'saveDescriptiveMarks']);

    // 4.2 admin/question — بنك الأسئلة
    Route::get('/admin/question', [TeacherExamController::class, 'questions']);
    Route::post('/admin/question', [TeacherExamController::class, 'storeQuestion']);
    Route::get('/admin/question/{id}', [TeacherExamController::class, 'showQuestion'])->whereNumber('id');
    Route::put('/admin/question/{id}', [TeacherExamController::class, 'updateQuestion'])->whereNumber('id');
    Route::delete('/admin/question/{id}', [TeacherExamController::class, 'deleteQuestion'])->whereNumber('id');
    Route::post('/admin/question/delete-bulk', [TeacherExamController::class, 'deleteQuestionBulk']);

    // 5.1 admin/timetable/classreport — جدول حصص الفصول
    Route::get('/admin/timetable/classreport', [TeacherTimetableController::class, 'classReport']);

    // 5.2 admin/timetable/mytimetable — جدول حصص المعلم
    Route::get('/admin/timetable/mytimetable', [TeacherTimetableController::class, 'myTimetable']);

    // 5.3 admin/teacher/assign_class_teacher — تعيين رائد الفصل
    Route::get('/admin/teacher/assign_class_teacher', [TeacherAcademicController::class, 'classTeachers']);
    Route::post('/admin/teacher/assign_class_teacher/assign', [TeacherAcademicController::class, 'assignClassTeacher']);

    // 5.4 admin/subjectgroup — مجموعات المواد
    Route::get('/admin/subjectgroup', [TeacherAcademicController::class, 'subjectGroups']);

    // 5.5 admin/subject — المواد الدراسية
    Route::get('/admin/subject', [TeacherAcademicController::class, 'subjects']);

    // 5.6 classes — الفصول
    Route::get('/classes', [TeacherAcademicController::class, 'classes']);

    // 5.7 sections — الشعب
    Route::get('/sections', [TeacherAcademicController::class, 'sections']);

    // 6.1 admin/syllabus — إدارة الخطة الدراسية
    Route::get('/admin/syllabus', [TeacherSyllabusController::class, 'syllabus']);
    Route::post('/admin/syllabus', [TeacherSyllabusController::class, 'storeSyllabus']);
    Route::delete('/admin/syllabus/{id}', [TeacherSyllabusExtraController::class, 'deleteSyllabus'])->whereNumber('id');
    Route::get('/admin/syllabus/{id}/messages', [TeacherSyllabusExtraController::class, 'syllabusMessages'])->whereNumber('id');
    Route::post('/admin/syllabus/messages', [TeacherSyllabusExtraController::class, 'addSyllabusMessage']);
    Route::delete('/admin/syllabus/messages/{id}', [TeacherSyllabusExtraController::class, 'deleteSyllabusMessage'])->whereNumber('id');

    // 6.2 admin/syllabus/status — حالة إنجاز المنهج
    Route::get('/admin/syllabus/status', [TeacherSyllabusController::class, 'status']);

    // 6.3 admin/lessonplan/lesson — الدروس
    Route::get('/admin/lessonplan/lesson', [TeacherSyllabusController::class, 'lessons']);
    Route::post('/admin/lessonplan/lesson', [TeacherSyllabusController::class, 'storeLesson']);
    Route::delete('/admin/lessonplan/lesson/{id}', [TeacherSyllabusExtraController::class, 'deleteLesson'])->whereNumber('id');
    Route::post('/admin/lessonplan/lesson/copy', [TeacherSyllabusExtraController::class, 'copyLesson']);

    // 6.4 admin/lessonplan/topic — الموضوعات
    Route::get('/admin/lessonplan/topic', [TeacherSyllabusController::class, 'topics']);
    Route::post('/admin/lessonplan/topic', [TeacherSyllabusController::class, 'storeTopic']);
    Route::post('/admin/lessonplan/topic/status', [TeacherSyllabusExtraController::class, 'changeTopicStatus']);
    Route::post('/admin/lessonplan/topic/delete-bulk', [TeacherSyllabusExtraController::class, 'deleteTopicBulk']);

    // 7.1 admin/staff — دليل الموظفين
    Route::get('/admin/staff', [TeacherCommunicateController::class, 'staffDirectory']);

    // 7.2 admin/staff/leaverequest — طلب إجازة المعلم
    Route::get('/admin/staff/leaverequest', [TeacherMyLeaveController::class, 'myLeaves']);
    Route::get('/admin/staff/leaverequest/types', [TeacherMyLeaveController::class, 'leaveTypes']);
    Route::get('/admin/staff/leaverequest/balance', [TeacherMyLeaveController::class, 'balance']);
    Route::post('/admin/staff/leaverequest', [TeacherMyLeaveController::class, 'apply']);
    Route::delete('/admin/staff/leaverequest/{id}', [TeacherMyLeaveController::class, 'cancel'])->whereNumber('id');

    // 8.1 admin/notification — لوحة الإعلانات
    Route::get('/admin/notification', [TeacherCommunicateController::class, 'noticeBoard']);
    Route::post('/admin/notification/{id}/read', [TeacherCommunicateController::class, 'markRead'])->whereNumber('id');

    // 8.2 admin/mailsms/compose — إرسال بريد
    Route::get('/admin/mailsms/compose', [TeacherCommunicateController::class, 'emailTemplates']);
    Route::post('/admin/mailsms/compose', [TeacherCommunicateController::class, 'sendEmail']);

    // 8.3 admin/mailsms/compose_sms — إرسال رسائل قصيرة
    Route::get('/admin/mailsms/compose_sms', [TeacherCommunicateController::class, 'smsTemplates']);
    Route::post('/admin/mailsms/compose_sms', [TeacherCommunicateController::class, 'sendSms']);

    // 8.4 admin/mailsms/index — سجل المراسلات
    Route::get('/admin/mailsms/index', [TeacherCommunicateController::class, 'sentLog']);

    // 9.1 admin/content/list — قائمة المحتويات المشتركة
    Route::get('/admin/content/list', [TeacherContentController::class, 'index']);
    Route::get('/admin/content/list/{id}', [TeacherContentController::class, 'show'])->whereNumber('id');

    // 9.2 admin/content/upload — رفع المحتوى
    Route::get('/admin/content/upload', [TeacherContentController::class, 'index']);
    Route::post('/admin/content/upload', [TeacherContentController::class, 'store']);
    Route::put('/admin/content/upload/{id}', [TeacherContentController::class, 'update'])->whereNumber('id');
    Route::delete('/admin/content/upload/{id}', [TeacherContentController::class, 'destroy'])->whereNumber('id');

    // 10.1 homework — الواجبات المدرسية
    Route::get('/homework', [TeacherHomeworkController::class, 'index']);
    Route::post('/homework', [TeacherHomeworkController::class, 'store']);
    Route::put('/homework/{id}', [TeacherHomeworkController::class, 'update'])->whereNumber('id');
    Route::delete('/homework/{id}', [TeacherHomeworkController::class, 'destroy'])->whereNumber('id');
    Route::get('/homework/{id}/evaluation', [TeacherHomeworkController::class, 'evaluation'])->whereNumber('id');
    Route::post('/homework/evaluate', [TeacherHomeworkController::class, 'saveEvaluation']);

    // 11.1 admin/staffidcard — بطاقات هوية الموظفين
    Route::get('/admin/staffidcard', [TeacherReferenceController::class, 'staffCardTemplates']);

    // 11.2 admin/generatestaffidcard — توليد بطاقات الموظفين
    Route::get('/admin/generatestaffidcard', [TeacherReferenceController::class, 'myStaffCard']);

    // 12.1 report/studentinformation — تقرير معلومات الطلاب
    Route::get('/report/studentinformation', [TeacherStudentReportController::class, 'index']);

    // 12.2 financereports/finance — تقارير المالية
    Route::get('/financereports/finance', [TeacherStudentReportController::class, 'financeSummary']);

    // 12.3 attendencereports/attendance — تقارير الحضور
    Route::get('/attendencereports/attendance', [TeacherAttendanceController::class, 'report']);

    // 12.4 admin/examresult/examinations — تقارير الامتحانات
    Route::get('/admin/examresult/examinations', [TeacherExamGroupController::class, 'rankReport']);
    Route::get('/admin/examresult/examinations/marksheet', [TeacherExamGroupController::class, 'marksheet']);

    // 12.5 admin/onlineexam/report — تقارير الامتحانات الإلكترونية
    Route::get('/admin/onlineexam/report', function (Request $request) {
        return response()->json(['message' => 'exam_id required, use /api/admin/onlineexam/report/{id}'], 422);
    });
    Route::get('/admin/onlineexam/report/{id}', [TeacherOnlineExamManageController::class, 'report'])->whereNumber('id');

    // 12.6 report/lesson_plan — تقرير الخطة الدراسية
    Route::get('/report/lesson_plan', [TeacherSyllabusController::class, 'status']);
    Route::get('/report/lesson_plan/report', [TeacherSyllabusController::class, 'report']);

    // 12.7 homework/homeworkordailyassignmentreport — تقرير الواجبات
    Route::get('/homework/homeworkordailyassignmentreport', [TeacherHomeworkExtraController::class, 'evaluationReport']);

    // 12.8 admin/route/studenttransportdetails — تقرير النقل
    Route::get('/admin/route/studenttransportdetails', [TeacherStudentReportController::class, 'transportStudents']);

    // 12.9 admin/hostelroom/studenthosteldetails — تقرير السكن الداخلي
    Route::get('/admin/hostelroom/studenthosteldetails', [TeacherStudentReportController::class, 'hostelStudents']);

    // 12.10 report/alumnireport — تقرير الخريجين
    Route::get('/report/alumnireport', [TeacherStudentReportController::class, 'alumniStudents']);

    // 13.1 admin/onlineadmission/admissionsetting — إعدادات القبول الإلكتروني
    Route::get('/admin/onlineadmission/admissionsetting', [TeacherAdmissionSettingController::class, 'show']);
    Route::put('/admin/onlineadmission/admissionsetting', [TeacherAdmissionSettingController::class, 'update']);
    Route::post('/admin/onlineadmission/admissionsetting', [TeacherAdmissionSettingController::class, 'update']);

    // Calendar — parity مع CI admin/calendar/* (صفحة events + AJAX)
    // ملاحظة: admin/calendar/events في CI صفحة HTML؛ هنا تُرجع JSON المفلتر (events + todos)
    Route::get('/admin/calendar/events', [TeacherCalendarController::class, 'events']);
    Route::match(['get', 'post'], '/admin/calendar/getevents', [TeacherCalendarController::class, 'feed']);
    Route::post('/admin/calendar/saveevent', [TeacherCalendarController::class, 'saveEvent']);
    Route::post('/admin/calendar/updateevent', [TeacherCalendarController::class, 'updateEventByBody']);
    Route::match(['get', 'post'], '/admin/calendar/view_event/{id}', [TeacherCalendarController::class, 'viewEvent'])->whereNumber('id');
    Route::match(['post', 'delete'], '/admin/calendar/delete_event/{id}', [TeacherCalendarController::class, 'deleteEvent'])->whereNumber('id');
    Route::post('/admin/calendar/addtodo', [TeacherCalendarController::class, 'addTodo']);
    Route::match(['get', 'post'], '/admin/calendar/gettaskbyid/{id}', [TeacherCalendarController::class, 'getTaskById'])->whereNumber('id');
    Route::post('/admin/calendar/markcomplete/{id}', [TeacherCalendarController::class, 'markTodoComplete'])->whereNumber('id');
});
} // end foreach ciPrefix
