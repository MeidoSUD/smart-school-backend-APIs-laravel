<?php

use Illuminate\Support\Facades\Route;
use Modules\Staff\Http\Controllers\Api\Teacher\TeacherAcademicController;
use Modules\Staff\Http\Controllers\Api\Teacher\TeacherAttendanceController;
use Modules\Staff\Http\Controllers\Api\Teacher\TeacherAdmissionSettingController;
use Modules\Staff\Http\Controllers\Api\Teacher\TeacherStudentReportController;
use Modules\Staff\Http\Controllers\Api\Teacher\TeacherDisableReasonController;
use Modules\Staff\Http\Controllers\Api\Teacher\TeacherOnlineAdmissionController;
use Modules\Staff\Http\Controllers\Api\Teacher\TeacherAuthController;
use Modules\Staff\Http\Controllers\Api\Teacher\TeacherBehaviourController;
use Modules\Staff\Http\Controllers\Api\Teacher\TeacherBehaviourReportController;
use Modules\Staff\Http\Controllers\Api\Teacher\TeacherCalendarController;
use Modules\Staff\Http\Controllers\Api\Teacher\TeacherChatController;
use Modules\Staff\Http\Controllers\Api\Teacher\TeacherCommunicateController;
use Modules\Staff\Http\Controllers\Api\Teacher\TeacherComplaintController;
use Modules\Staff\Http\Controllers\Api\Teacher\TeacherContentController;
use Modules\Staff\Http\Controllers\Api\Teacher\TeacherDashboardController;
use Modules\Staff\Http\Controllers\Api\Teacher\TeacherExamController;
use Modules\Staff\Http\Controllers\Api\Teacher\TeacherExamGroupController;
use Modules\Staff\Http\Controllers\Api\Teacher\TeacherHomeworkController;
use Modules\Staff\Http\Controllers\Api\Teacher\TeacherHomeworkExtraController;
use Modules\Staff\Http\Controllers\Api\Teacher\TeacherLeaveController;
use Modules\Staff\Http\Controllers\Api\Teacher\TeacherMarkController;
use Modules\Staff\Http\Controllers\Api\Teacher\TeacherMyLeaveController;
use Modules\Staff\Http\Controllers\Api\Teacher\TeacherOnlineExamManageController;
use Modules\Staff\Http\Controllers\Api\Teacher\TeacherProfileController;
use Modules\Staff\Http\Controllers\Api\Teacher\TeacherReferenceController;
use Modules\Staff\Http\Controllers\Api\Teacher\TeacherStudentController;
use Modules\Staff\Http\Controllers\Api\Teacher\TeacherSyllabusController;
use Modules\Staff\Http\Controllers\Api\Teacher\TeacherSyllabusExtraController;
use Modules\Staff\Http\Controllers\Api\Teacher\TeacherTimetableController;

/*
 * مسارات المعلم بمنظوره الخاص (P0–P5).
 * ملاحظة: ملف api.php الحالي يبقى كما هو (منظور الطالب) ولا يُمس.
 */
Route::prefix('api/teacher')->group(function () {
    Route::post('/auth/login', [TeacherAuthController::class, 'login'])
        ->name('api.teacher.login')
        ->middleware('throttle:10,1');
});

Route::middleware('auth:sanctum')->prefix('api/teacher')->group(function () {
    // P0 — الدخول واللوحة والبروفايل
    Route::post('/auth/logout', [TeacherAuthController::class, 'logout']);
    Route::get('/me', [TeacherAuthController::class, 'me']);
    Route::get('/profile', [TeacherProfileController::class, 'profile']);
    Route::put('/profile', [TeacherProfileController::class, 'update']);
    Route::post('/change-password', [TeacherProfileController::class, 'changePassword']);
    Route::get('/dashboard', [TeacherDashboardController::class, 'dashboard']);
    Route::get('/my-classes', [TeacherDashboardController::class, 'myClasses']);
    Route::match(['get', 'post'], '/my-subjects', [TeacherDashboardController::class, 'mySubjects']);

    // P1 — طلابي
    Route::get('/students', [TeacherStudentController::class, 'index']);
    Route::post('/students', [TeacherStudentController::class, 'store']);
    Route::get('/students/disabled', [TeacherStudentController::class, 'disabledStudents']);
    Route::get('/students/multi-class', [TeacherStudentController::class, 'multiClass']);
    Route::post('/students/bulk-delete', [TeacherStudentController::class, 'bulkDelete']);
    Route::get('/students/{id}', [TeacherStudentController::class, 'view'])->whereNumber('id');

    // P2 — التحضير
    Route::get('/attendance', [TeacherAttendanceController::class, 'index']);
    Route::post('/attendance', [TeacherAttendanceController::class, 'store']);
    Route::get('/attendance/report', [TeacherAttendanceController::class, 'report']);
    Route::get('/attendance/month', [TeacherAttendanceController::class, 'monthAttendance']);
    Route::get('/subject-attendance', [TeacherAttendanceController::class, 'subjectAttendance']);
    Route::post('/subject-attendance', [TeacherAttendanceController::class, 'storeSubjectAttendance']);

    // P2b — السلوك (parity مع behaviour/incidents + behaviour/studentincidents)
    Route::get('/behaviour/incidents', [TeacherBehaviourController::class, 'incidents']);
    Route::post('/behaviour/incidents', [TeacherBehaviourController::class, 'storeIncident']);
    Route::get('/behaviour/incidents/{id}', [TeacherBehaviourController::class, 'showIncident'])->whereNumber('id');
    Route::put('/behaviour/incidents/{id}', [TeacherBehaviourController::class, 'updateIncident'])->whereNumber('id');
    Route::delete('/behaviour/incidents/{id}', [TeacherBehaviourController::class, 'destroyIncident'])->whereNumber('id');
    Route::get('/behaviour/students', [TeacherBehaviourController::class, 'assignableStudents']);
    Route::post('/behaviour/assign', [TeacherBehaviourController::class, 'assignIncident']);
    Route::get('/behaviour/students/{id}/incidents', [TeacherBehaviourController::class, 'studentIncidents'])->whereNumber('id');
    Route::delete('/behaviour/assigned/{id}', [TeacherBehaviourController::class, 'unassignIncident'])->whereNumber('id');
    // P2c — تقارير السلوك (parity مع behaviour/report)
    Route::get('/behaviour/reports', [TeacherBehaviourReportController::class, 'index']);
    Route::get('/behaviour/reports/student-incidents', [TeacherBehaviourReportController::class, 'studentIncidents']);
    Route::get('/behaviour/reports/student-incidents/{id}/details', [TeacherBehaviourReportController::class, 'studentIncidentDetails'])->whereNumber('id');
    Route::get('/behaviour/reports/rank', [TeacherBehaviourReportController::class, 'rank']);
    Route::get('/behaviour/reports/rank/{id}/details', [TeacherBehaviourReportController::class, 'rankDetails'])->whereNumber('id');
    Route::get('/behaviour/reports/class-rank', [TeacherBehaviourReportController::class, 'classRank']);
    Route::get('/behaviour/reports/class-rank/{id}/points', [TeacherBehaviourReportController::class, 'classPoints'])->whereNumber('id');
    Route::get('/behaviour/reports/class-section-rank', [TeacherBehaviourReportController::class, 'classSectionRank']);
    Route::get('/behaviour/reports/class-section-rank/points', [TeacherBehaviourReportController::class, 'classSectionPoints']);
    Route::get('/behaviour/reports/house-rank', [TeacherBehaviourReportController::class, 'houseRank']);
    Route::get('/behaviour/reports/house-rank/{id}/points', [TeacherBehaviourReportController::class, 'housePoints'])->whereNumber('id');
    Route::get('/behaviour/reports/incident-wise', [TeacherBehaviourReportController::class, 'incidentWise']);
    Route::get('/behaviour/reports/incident-wise/{id}/students', [TeacherBehaviourReportController::class, 'incidentStudents'])->whereNumber('id');

    // P3 — الواجبات والمنهج
    Route::get('/homework', [TeacherHomeworkController::class, 'index']);
    Route::post('/homework', [TeacherHomeworkController::class, 'store']);
    Route::put('/homework/{id}', [TeacherHomeworkController::class, 'update'])->whereNumber('id');
    Route::delete('/homework/{id}', [TeacherHomeworkController::class, 'destroy'])->whereNumber('id');
    Route::get('/homework/{id}/evaluation', [TeacherHomeworkController::class, 'evaluation'])->whereNumber('id');
    Route::post('/homework/evaluate', [TeacherHomeworkController::class, 'saveEvaluation']);
    Route::get('/homework-closed', [TeacherHomeworkExtraController::class, 'closedList']);
    Route::get('/homework-report', [TeacherHomeworkExtraController::class, 'evaluationReport']);
    Route::get('/daily-assignments', [TeacherHomeworkExtraController::class, 'dailyAssignments']);
    Route::post('/daily-assignments', [TeacherHomeworkExtraController::class, 'storeDailyAssignment']);
    Route::get('/daily-assignments/{id}', [TeacherHomeworkExtraController::class, 'dailyAssignmentDetails'])->whereNumber('id');
    Route::post('/daily-assignments/remark', [TeacherHomeworkExtraController::class, 'submitDailyRemark']);
    Route::get('/lessons', [TeacherSyllabusController::class, 'lessons']);
    Route::post('/lessons', [TeacherSyllabusController::class, 'storeLesson']);
    Route::delete('/lessons/{id}', [TeacherSyllabusExtraController::class, 'deleteLesson'])->whereNumber('id');
    Route::post('/lessons/copy', [TeacherSyllabusExtraController::class, 'copyLesson']);
    Route::get('/topics', [TeacherSyllabusController::class, 'topics']);
    Route::post('/topics', [TeacherSyllabusController::class, 'storeTopic']);
    Route::post('/topics/delete-bulk', [TeacherSyllabusExtraController::class, 'deleteTopicBulk']);
    Route::post('/topics/status', [TeacherSyllabusExtraController::class, 'changeTopicStatus']);
    Route::get('/syllabus', [TeacherSyllabusController::class, 'syllabus']);
    Route::post('/syllabus', [TeacherSyllabusController::class, 'storeSyllabus']);
    Route::delete('/syllabus/{id}', [TeacherSyllabusExtraController::class, 'deleteSyllabus'])->whereNumber('id');
    Route::get('/syllabus/status', [TeacherSyllabusController::class, 'status']);
    Route::get('/syllabus/report', [TeacherSyllabusController::class, 'report']);
    Route::get('/syllabus/{id}/messages', [TeacherSyllabusExtraController::class, 'syllabusMessages'])->whereNumber('id');
    Route::post('/syllabus/messages', [TeacherSyllabusExtraController::class, 'addSyllabusMessage']);
    Route::delete('/syllabus/messages/{id}', [TeacherSyllabusExtraController::class, 'deleteSyllabusMessage'])->whereNumber('id');

    // P4 — الدرجات والامتحانات
    Route::get('/exams', [TeacherMarkController::class, 'myExams']);
    Route::get('/marks/sheet', [TeacherMarkController::class, 'markSheet']);
    Route::post('/marks', [TeacherMarkController::class, 'saveMarks']);
    Route::get('/exam-groups', [TeacherExamGroupController::class, 'examGroups']);
    Route::get('/exam-groups/{id}/exams', [TeacherExamGroupController::class, 'groupExams'])->whereNumber('id');
    Route::get('/exam-subjects/{id}', [TeacherExamGroupController::class, 'examSubjects'])->whereNumber('id');
    Route::post('/exam-marks/entry', [TeacherExamGroupController::class, 'entryMarks']);
    Route::get('/exam-results/student/{id}', [TeacherExamGroupController::class, 'studentResult'])->whereNumber('id');
    Route::get('/exam-marksheet', [TeacherExamGroupController::class, 'marksheet']);
    Route::get('/exam-rank', [TeacherExamGroupController::class, 'rankReport']);
    Route::get('/exam-schedules', [TeacherExamGroupController::class, 'examSchedules']);
    Route::get('/exams/admit-card-templates', [TeacherExamGroupController::class, 'admitCardTemplates']);
    Route::get('/exams/marksheet-templates', [TeacherExamGroupController::class, 'marksheetTemplates']);
    Route::get('/exams/admit-cards', [TeacherExamGroupController::class, 'admitCards']);
    Route::get('/online-exams', [TeacherExamController::class, 'onlineExams']);
    Route::get('/online-exams/upcoming', [TeacherExamController::class, 'getexamlist']);
    Route::get('/online-exams/closed', [TeacherExamController::class, 'getclosedexamlist']);
    Route::post('/online-exams', [TeacherOnlineExamManageController::class, 'store']);
    Route::delete('/online-exams/{id}', [TeacherOnlineExamManageController::class, 'destroy'])->whereNumber('id');
    Route::get('/online-exams/{id}/questions', [TeacherOnlineExamManageController::class, 'examQuestions'])->whereNumber('id');
    Route::post('/online-exams/questions/attach', [TeacherOnlineExamManageController::class, 'attachQuestions']);
    Route::post('/online-exams/questions/detach', [TeacherOnlineExamManageController::class, 'detachQuestion']);
    Route::post('/online-exams/assign', [TeacherOnlineExamManageController::class, 'assignStudents']);
    Route::get('/online-exams/{id}/report', [TeacherOnlineExamManageController::class, 'report'])->whereNumber('id');
    Route::post('/online-exams/marks', [TeacherOnlineExamManageController::class, 'saveDescriptiveMarks']);
    Route::get('/questions', [TeacherExamController::class, 'questions']);
    Route::post('/questions', [TeacherExamController::class, 'storeQuestion']);
    Route::get('/questions/{id}', [TeacherExamController::class, 'showQuestion'])->whereNumber('id');
    Route::put('/questions/{id}', [TeacherExamController::class, 'updateQuestion'])->whereNumber('id');
    Route::delete('/questions/{id}', [TeacherExamController::class, 'deleteQuestion'])->whereNumber('id');
    Route::post('/questions/delete-bulk', [TeacherExamController::class, 'deleteQuestionBulk']);
    Route::get('/grades', [TeacherReferenceController::class, 'grades']);
    Route::get('/student-categories', [TeacherReferenceController::class, 'studentCategories']);
    Route::get('/student-houses', [TeacherReferenceController::class, 'studentHouses']);
    Route::get('/cards/staff-templates', [TeacherReferenceController::class, 'staffCardTemplates']);
    Route::get('/cards/my-card', [TeacherReferenceController::class, 'myStaffCard']);

    // القبول الإلكتروني — قائمة وتفاصيل للقراءة فقط (مُفلتر بفصول المعلم)
    Route::get('/online-admissions', [TeacherOnlineAdmissionController::class, 'index']);
    Route::get('/online-admissions/{id}', [TeacherOnlineAdmissionController::class, 'show'])->whereNumber('id');
    Route::put('/online-admissions/{id}', [TeacherOnlineAdmissionController::class, 'update'])->whereNumber('id');
    Route::get('/online-admissions/{id}/payment-status', [TeacherOnlineAdmissionController::class, 'paymentStatus'])->whereNumber('id');
    Route::delete('/online-admissions/{id}', [TeacherOnlineAdmissionController::class, 'destroy'])->whereNumber('id');

    // تقرير الطلاب (report/studentinformation → studentreport) — قراءة فقط
    Route::get('/reports/students', [TeacherStudentReportController::class, 'index']);
    Route::get('/reports/transport-students', [TeacherStudentReportController::class, 'transportStudents']);
    Route::get('/reports/hostel-students', [TeacherStudentReportController::class, 'hostelStudents']);
    Route::get('/reports/alumni-students', [TeacherStudentReportController::class, 'alumniStudents']);
    // ملخص الرسوم لفصول المعلم — parity مُضيّقة مع financereports/reportduefees (البند 12.2)
    Route::get('/reports/finance-summary', [TeacherStudentReportController::class, 'financeSummary']);

    // إعدادات القبول الإلكتروني (admissionsetting) — عرض وتحديث مع فحص RBAC
    Route::get('/online-admission/settings', [TeacherAdmissionSettingController::class, 'show']);
    Route::put('/online-admission/settings', [TeacherAdmissionSettingController::class, 'update']);

    // أسباب التعطيل — كتابة (add/edit/delete) مع فحص RBAC
    Route::post('/disable-reasons', [TeacherDisableReasonController::class, 'store']);
    Route::put('/disable-reasons/{id}', [TeacherDisableReasonController::class, 'update'])->whereNumber('id');
    Route::delete('/disable-reasons/{id}', [TeacherDisableReasonController::class, 'destroy'])->whereNumber('id');

    // Academics (classes / sections / subjects) وأسباب التعطيل — قراءة فقط
    Route::get('/academics/classes', [TeacherAcademicController::class, 'classes']);
    Route::get('/academics/sections', [TeacherAcademicController::class, 'sections']);
    Route::get('/academics/subjects', [TeacherAcademicController::class, 'subjects']);
    Route::get('/academics/subject-groups', [TeacherAcademicController::class, 'subjectGroups']);
    Route::get('/disable-reasons', [TeacherAcademicController::class, 'disableReasons']);
    Route::get('/disable-reasons/{id}', [TeacherAcademicController::class, 'disableReasonDetails'])->whereNumber('id');

    // رائد الفصل (assign_class_teacher) — عرض وتعديل مع RBAC
    Route::get('/academics/class-teachers', [TeacherAcademicController::class, 'classTeachers']);
    Route::post('/academics/class-teachers/assign', [TeacherAcademicController::class, 'assignClassTeacher']);

    // P5 — الجدول والإجازات والتواصل والمحتوى
    Route::get('/timetable', [TeacherTimetableController::class, 'myTimetable']);
    Route::get('/timetable/class', [TeacherTimetableController::class, 'classReport']);
    Route::get('/leaves', [TeacherLeaveController::class, 'index']);
    Route::post('/leaves/status', [TeacherLeaveController::class, 'setStatus']);
    Route::get('/my-leaves/types', [TeacherMyLeaveController::class, 'leaveTypes']);
    Route::get('/my-leaves', [TeacherMyLeaveController::class, 'myLeaves']);
    Route::get('/my-leaves/balance', [TeacherMyLeaveController::class, 'balance']);
    Route::post('/my-leaves', [TeacherMyLeaveController::class, 'apply']);
    Route::delete('/my-leaves/{id}', [TeacherMyLeaveController::class, 'cancel'])->whereNumber('id');
    Route::get('/notice-board', [TeacherCommunicateController::class, 'noticeBoard']);
    Route::post('/notice-board/{id}/read', [TeacherCommunicateController::class, 'markRead'])->whereNumber('id');
    Route::get('/staff-directory', [TeacherCommunicateController::class, 'staffDirectory']);
    Route::post('/notices', [TeacherCommunicateController::class, 'sendNotice']);
    Route::get('/notices/sent', [TeacherCommunicateController::class, 'sentLog']);
    Route::get('/email-templates', [TeacherCommunicateController::class, 'emailTemplates']);
    Route::get('/sms-templates', [TeacherCommunicateController::class, 'smsTemplates']);
    Route::post('/send-email', [TeacherCommunicateController::class, 'sendEmail']);
    Route::post('/send-sms', [TeacherCommunicateController::class, 'sendSms']);
    Route::get('/contents', [TeacherContentController::class, 'index']);
    Route::post('/contents', [TeacherContentController::class, 'store']);
    Route::get('/contents/{id}', [TeacherContentController::class, 'show'])->whereNumber('id');
    Route::put('/contents/{id}', [TeacherContentController::class, 'update'])->whereNumber('id');
    Route::delete('/contents/{id}', [TeacherContentController::class, 'destroy'])->whereNumber('id');
    Route::get('/chat/conversations', [TeacherChatController::class, 'myConversations']);
    Route::get('/chat/search-user', [TeacherChatController::class, 'searchUser']);
    Route::post('/chat/start', [TeacherChatController::class, 'startConversation']);
    Route::get('/chat/{id}/messages', [TeacherChatController::class, 'messages'])->whereNumber('id');
    Route::post('/chat/send', [TeacherChatController::class, 'send']);
    Route::get('/calendar/events', [TeacherCalendarController::class, 'events']);
    Route::get('/calendar/events/feed', [TeacherCalendarController::class, 'feed']);
    Route::post('/calendar/events', [TeacherCalendarController::class, 'saveEvent']);
    Route::post('/calendar/events/update', [TeacherCalendarController::class, 'updateEventByBody']);
    Route::get('/calendar/events/{id}', [TeacherCalendarController::class, 'viewEvent'])->whereNumber('id');
    Route::put('/calendar/events/{id}', [TeacherCalendarController::class, 'updateEvent'])->whereNumber('id');
    Route::post('/calendar/events/{id}', [TeacherCalendarController::class, 'updateEvent'])->whereNumber('id');
    Route::delete('/calendar/events/{id}', [TeacherCalendarController::class, 'deleteEvent'])->whereNumber('id');
    Route::get('/calendar/todos', [TeacherCalendarController::class, 'todos']);
    Route::post('/calendar/todos', [TeacherCalendarController::class, 'addTodo']);
    Route::get('/calendar/todos/{id}', [TeacherCalendarController::class, 'getTaskById'])->whereNumber('id');
    Route::post('/calendar/todos/{id}/complete', [TeacherCalendarController::class, 'markTodoComplete'])->whereNumber('id');
    Route::get('/complaints', [TeacherComplaintController::class, 'index']);
    Route::post('/complaints', [TeacherComplaintController::class, 'store']);
    Route::get('/complaints/{id}', [TeacherComplaintController::class, 'details'])->whereNumber('id');
    Route::put('/complaints/{id}', [TeacherComplaintController::class, 'update'])->whereNumber('id');
    Route::delete('/complaints/{id}', [TeacherComplaintController::class, 'destroy'])->whereNumber('id');
});
