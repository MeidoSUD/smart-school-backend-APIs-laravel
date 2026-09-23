<?php

namespace Modules\Core\Http\Controllers\Api;

use Modules\Academic\Entities\Student;
use Modules\Academic\Entities\StudentSession;
use Modules\Academic\Entities\StudentAttendence;
use Modules\Academic\Entities\AttendenceType;
use Modules\Academic\Entities\Homework;
use Modules\Academic\Entities\HomeworkEvaluation;
use Modules\Academic\Entities\SubmitAssignment;
use Modules\Academic\Entities\Syllabus;
use Modules\Academic\Entities\ClassTimetable;
use Modules\Academic\Entities\StudentDoc;
use Modules\Core\Entities\Category;
use Modules\Core\Services\SchoolSettingsService;
use Modules\Core\Services\StudentSessionService;
use Modules\Finance\Entities\FeeSessionGroup;
use Modules\Finance\Entities\FeeGroupsFeetype;
use Modules\Finance\Entities\StudentFeesDeposite;
use Modules\Operations\Entities\LibraryMember;
use Modules\Operations\Entities\Notification;
use Modules\Operations\Entities\ReadNotification;
use Modules\Operations\Entities\Visitor;
use Modules\Staff\Entities\Staff;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class UserController extends \Modules\Core\Http\Controllers\Api\Controller
{
    public function __construct(
        private readonly StudentSessionService $studentSessionService,
        private readonly SchoolSettingsService $schoolSettingsService
    ) {
        $this->setControllerName('UserController');
    }

    public function dashboard(Request $request): JsonResponse
    {
        $user = $request->user();

        if (!$user) {
            return $this->errorResponse('Unauthorized', 401);
        }

        $studentSession = $this->studentSessionService->getStudentSession($user);
        if (!$studentSession) {
            return $this->errorResponse('Student session not found');
        }

        $studentId = $studentSession->student_id;
        $classId = $studentSession->class_id;
        $sectionId = $studentSession->section_id;
        $studentSessionId = $studentSession->id;

        $sessionDates = $this->schoolSettingsService->sessionDates();

        $attendencePercentage = -1;
        $attendances = StudentAttendence::where('student_session_id', $studentSessionId)
            ->whereBetween('date', [$sessionDates['start'], $sessionDates['end']])
            ->get();

        if ($attendances->isNotEmpty()) {
            $total = $attendances->count();
            $absents = $attendances->filter(fn($a) => $a->attendence_type_id == 4)->count();
            $presents = $total - $absents;
            $attendencePercentage = round(($presents * 100) / $total, 2);
        }

        $memberType = 'student';
        $libraryMember = LibraryMember::where('member_type', $memberType)
            ->where('member_id', $studentId)
            ->first();
        $bookList = false;
        if ($libraryMember) {
            $bookList = DB::table('book_issues')
                ->leftJoin('libarary_members', 'libarary_members.id', '=', 'book_issues.member_id')
                ->leftJoin('books', 'books.id', '=', 'book_issues.book_id')
                ->where('libarary_members.id', $libraryMember->id)
                ->select(
                    'book_issues.return_date',
                    'books.book_no',
                    'book_issues.issue_date',
                    'book_issues.is_returned',
                    'books.book_title',
                    'books.author',
                    'book_issues.duereturn_date'
                )
                ->orderBy('book_issues.is_returned', 'asc')
                ->get();
        }

        $sessionId = $this->schoolSettingsService->getSettings()->session_id;
        $homeworklist = Homework::where('homework.class_id', $classId)
            ->where('homework.section_id', $sectionId)
            ->where('homework.session_id', $sessionId)
            ->where('homework.submit_date', '>=', now()->toDateString())
            ->leftJoin('homework_evaluation', function ($join) use ($studentSessionId) {
                $join->on('homework_evaluation.homework_id', '=', 'homework.id')
                    ->where('homework_evaluation.student_session_id', '=', $studentSessionId);
            })
            ->leftJoin('classes', 'classes.id', '=', 'homework.class_id')
            ->leftJoin('sections', 'sections.id', '=', 'homework.section_id')
            ->leftJoin('subject_group_subjects', 'subject_group_subjects.id', '=', 'homework.subject_group_subject_id')
            ->leftJoin('subjects', 'subjects.id', '=', 'subject_group_subjects.subject_id')
            ->leftJoin('subject_groups', 'subject_groups.id', '=', 'subject_group_subjects.subject_group_id')
            ->select(
                'homework.*',
                'homework_evaluation.id as homework_evaluation_id',
                'homework_evaluation.note',
                'homework_evaluation.marks as evaluation_marks',
                'classes.class',
                'sections.section',
                'subject_group_subjects.subject_id',
                'subject_group_subjects.id as subject_group_subject_id',
                'subjects.name as subject_name',
                'subjects.code as subject_code',
                'subject_groups.id as subject_groups_id',
                'subject_groups.name'
            )
            ->orderBy('homework.homework_date', 'desc')
            ->get()
            ->map(function ($hw) use ($studentId) {
                $checkstatus = SubmitAssignment::where('homework_id', $hw->id)
                    ->where('student_id', $studentId)
                    ->count();
                $hw->status = $checkstatus > 0 ? 'submitted' : '';
                return $hw;
            });

        $notifications = DB::table('send_notification')
            ->leftJoin('staff', 'staff.id', '=', 'send_notification.created_id')
            ->leftJoin('read_notification', function ($join) use ($user) {
                $join->on('read_notification.notification_id', '=', 'send_notification.id');
                if ($user->role === 'student') {
                    $join->where('read_notification.student_id', '=', $user->id);
                } elseif ($user->role === 'parent') {
                    $join->where('read_notification.parent_id', '=', $user->id);
                }
            })
            ->where($user->role === 'student' ? 'visible_student' : 'visible_parent', 'Yes')
            ->orderByDesc('send_notification.publish_date')
            ->select(
                'send_notification.id',
                'send_notification.title',
                'send_notification.publish_date',
                'send_notification.date',
                'send_notification.message',
                'send_notification.attachment',
                'staff.employee_id',
                'staff.name',
                'staff.surname',
                DB::raw("IF(read_notification.id IS NULL, 'unread', 'read') as notification_id")
            )
            ->get()
            ->filter(fn($n) => strtotime(date('Y-m-d')) >= strtotime($n->publish_date))
            ->values();

        $setting = $this->schoolSettingsService->getSettings();

        $subjects = Syllabus::getMySubjects($classId, $sectionId, $sessionId);
        $subjectsData = [];
        foreach ($subjects as $value) {
            $subjectDetails = Syllabus::getSubjectStatus($value->subject_group_subjects_id, $value->subject_group_class_sections_id);
            $complete = 0;
            $incomplete = 0;
            if ($subjectDetails && $subjectDetails->total > 0) {
                $complete = round(($subjectDetails->complete / $subjectDetails->total) * 100);
                $incomplete = round(($subjectDetails->incomplete / $subjectDetails->total) * 100);
            }
            $lebel = $value->name . ($value->code ? ' (' . $value->code . ')' : '');
            $subjectsData[] = [
                'lebel' => $lebel,
                'complete' => $complete,
                'incomplete' => $incomplete,
                'id' => $value->subject_group_subjects_id . '_' . $value->code,
                'total' => $subjectDetails->total ?? 0,
                'name' => $value->name,
                'graph_id' => $value->subject_group_subjects_id . time(),
            ];
        }

        $days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
        $daysRecord = [];
        foreach ($days as $day) {
            $daysRecord[$day] = ClassTimetable::where('class_id', $classId)
                ->where('section_id', $sectionId)
                ->where('day', $day)
                ->with('subjectGroupSubject.subjectGroup.subjects')
                ->with('staff')
                ->orderBy('time_from')
                ->get()
                ->map(function ($row) {
                    $subjectName = 'N/A';
                    $subjectCode = '';
                    if ($row->subjectGroupSubject && $row->subjectGroupSubject->subjectGroup && $row->subjectGroupSubject->subjectGroup->subjects) {
                        $subject = $row->subjectGroupSubject->subjectGroup->subjects->first();
                        if ($subject) {
                            $subjectName = $subject->name;
                            $subjectCode = $subject->code;
                        }
                    }
                    return [
                        'id' => $row->id,
                        'subject_name' => $subjectName,
                        'code' => $subjectCode,
                        'name' => $row->staff ? $row->staff->name : 'N/A',
                        'surname' => $row->staff ? $row->staff->surname : '',
                        'employee_id' => $row->staff ? $row->staff->employee_id : '',
                        'image' => $row->staff ? $row->staff->image : '',
                        'gender' => $row->staff ? $row->staff->gender : '',
                        'time_from' => $row->time_from,
                        'time_to' => $row->time_to,
                        'room_no' => $row->room_no ?? '',
                        'day' => $row->day,
                    ];
                });
        }

        $visitors = Visitor::where('student_session_id', $studentSessionId)->get();

        $teachers = [];
        $sessionId = $this->schoolSettingsService->getSettings()->session_id;

        $subjectTeachers = DB::table('subject_timetable')
            ->join('subject_group_subjects', 'subject_group_subjects.id', '=', 'subject_timetable.subject_group_subject_id')
            ->leftJoin('subjects', 'subjects.id', '=', 'subject_group_subjects.subject_id')
            ->join('staff', 'staff.id', '=', 'subject_timetable.staff_id')
            ->leftJoin('classes', 'classes.id', '=', 'subject_timetable.class_id')
            ->leftJoin('sections', 'sections.id', '=', 'subject_timetable.section_id')
            ->leftJoin('class_teacher', function ($join) {
                $join->on('class_teacher.class_id', '=', 'classes.id')
                    ->on('class_teacher.staff_id', '=', 'staff.id')
                    ->on('class_teacher.section_id', '=', 'sections.id');
            })
            ->where('staff.is_active', '1')
            ->where('subject_timetable.class_id', $classId)
            ->where('subject_timetable.section_id', $sectionId)
            ->where('subject_timetable.session_id', $sessionId)
            ->select(
                DB::raw("'subject' as type"),
                'class_teacher.staff_id as class_teacher',
                'subjects.id as subject_id',
                'subjects.name as subject_name',
                'subjects.code',
                'subjects.type',
                'staff.name',
                'staff.surname',
                'staff.email',
                'staff.contact_no',
                'staff.employee_id',
                'subject_timetable.staff_id as staff_id',
                'staff.image',
                'staff.gender',
                'subject_timetable.time_from',
                'subject_timetable.day',
                'subject_timetable.room_no',
                'subject_timetable.time_to',
                'sections.section as section_name',
                'classes.class as class_name'
            )
            ->get();

        $classTeachers = DB::table('class_teacher')
            ->join('staff', 'staff.id', '=', 'class_teacher.staff_id')
            ->join('classes', 'classes.id', '=', 'class_teacher.class_id')
            ->join('sections', 'sections.id', '=', 'class_teacher.section_id')
            ->where('staff.is_active', '1')
            ->where('class_teacher.class_id', $classId)
            ->where('class_teacher.section_id', $sectionId)
            ->where('class_teacher.session_id', $sessionId)
            ->select(
                DB::raw("'class' as type"),
                'class_teacher.staff_id as class_teacher',
                DB::raw("'' as subject_id"),
                DB::raw("'' as subject_name"),
                DB::raw("'' as code"),
                DB::raw("'' as type_col"),
                'staff.name',
                'staff.surname',
                'staff.email',
                'staff.contact_no',
                'staff.employee_id',
                'staff.id as staff_id',
                'staff.image',
                'staff.gender',
                DB::raw("'' as time_from"),
                DB::raw("'' as time_to"),
                DB::raw("'' as day"),
                DB::raw("'' as room_no"),
                'sections.section as section_name',
                'classes.class as class_name'
            )
            ->get();

        $allTeachers = $subjectTeachers->merge($classTeachers);

        $seenStaff = [];
        foreach ($allTeachers as $teacher) {
            if (!in_array($teacher->staff_id, $seenStaff)) {
                $seenStaff[] = $teacher->staff_id;
                $teachers[] = $teacher;
            }
        }

        $student = Student::find($studentId);

        $data = [
            'attendence_percentage' => $attendencePercentage,
            'bookList' => $bookList,
            'homeworklist' => $homeworklist,
            'notificationlist' => $notifications,
            'subjects_data' => $subjectsData,
            'timetable' => $daysRecord,
            'visitor_list' => $visitors,
            'studentsession_username' => $user->username,
            'student_data' => [
                'id' => $user->id,
                'username' => $user->username,
                'role' => $user->role,
                'student_id' => $studentId,
                'class' => $studentSession->class->class ?? null,
                'section' => $studentSession->section->section ?? null,
                'image' => $student ? $student->image : '',
                'gender' => $student ? $student->gender : '',
            ],
            'low_attendance_limit' => $this->schoolSettingsService->lowAttendanceLimit(),
            'teachers' => $teachers,
            'teacherlist' => $teachers,
        ];

        return $this->successResponse($data);
    }

    public function choose(Request $request): JsonResponse
    {
        $user = $request->user();

        if (!$user) {
            return $this->errorResponse('Unauthorized', 401);
        }

        if ($request->isMethod('post')) {
            $studentSessionId = $request->input('clschg');
            if (!$studentSessionId) {
                return $this->errorResponse('Student session ID is required');
            }

            $session = StudentSession::find($studentSessionId);
            if (!$session) {
                return $this->errorResponse('Student session not found');
            }

            StudentSession::where('student_id', $session->student_id)
                ->where('default_login', 1)
                ->update(['default_login' => 0]);

            $session->update(['default_login' => 1]);

            return $this->successResponse(['redirect' => 'user/user/dashboard'], 'Class selected successfully');
        }

        $studentLists = [];
        if ($user->role === 'student') {
            $studentId = $user->user_id;
            $studentLists = StudentSession::where('student_id', $studentId)
                ->with('class', 'section', 'session')
                ->get()
                ->map(fn($ss) => [
                    'id' => $ss->id,
                    'student_session_id' => $ss->id,
                    'student_id' => $ss->student_id,
                    'class_id' => $ss->class_id,
                    'section_id' => $ss->section_id,
                    'session_id' => $ss->session_id,
                    'class' => $ss->class->class ?? null,
                    'section' => $ss->section->section ?? null,
                    'session' => $ss->session->session ?? null,
                    'default_login' => $ss->default_login,
                ]);
        } elseif ($user->role === 'parent') {
            $studentLists = Student::where('parent_id', $user->id)
                ->with(['studentSessions.class', 'studentSessions.section', 'studentSessions.session'])
                ->get()
                ->flatMap(fn($s) => $s->studentSessions->map(fn($ss) => [
                    'id' => $ss->id,
                    'student_session_id' => $ss->id,
                    'student_id' => $ss->student_id,
                    'class_id' => $ss->class_id,
                    'section_id' => $ss->section_id,
                    'session_id' => $ss->session_id,
                    'class' => $ss->class->class ?? null,
                    'section' => $ss->section->section ?? null,
                    'session' => $ss->session->session ?? null,
                    'default_login' => $ss->default_login,
                    'student_name' => $s->fullname,
                ]));
        }

        return $this->successResponse([
            'student_lists' => $studentLists,
            'sch_setting' => $this->schoolSettingsService->getSettings(),
            'role' => $user->role,
        ]);
    }

    public function profile(Request $request): JsonResponse
    {
        $user = $request->user();

        if (!$user) {
            return $this->errorResponse('Unauthorized', 401);
        }

        $studentSession = $this->studentSessionService->getStudentSession($user);
        if (!$studentSession) {
            return $this->errorResponse('Student session not found');
        }

        $student = Student::with(['category', 'studentSessions.class', 'studentSessions.section'])
            ->find($studentSession->student_id);

        if (!$student) {
            return $this->errorResponse('Student not found');
        }

        $setting = $this->schoolSettingsService->getSettings();
        $feeData = $this->buildStudentFeeData($studentSession);
        $transport_active = DB::table('permission_group')->where('short_code', 'transport')->value('is_active');
        $transport_fees = $transport_active ? $feeData['transport_fees'] : [];

        $timeline = DB::table('student_timeline')->where('student_id', $student->id)->where('status', 'yes')->get();
        
        $student_doc = DB::table('student_doc')->where('student_id', $student->id)->get();

        $sessionDates = $this->schoolSettingsService->sessionDates();
        $attendances = StudentAttendence::with('attendenceType')
            ->where('student_session_id', $studentSession->id)
            ->whereBetween('date', [$sessionDates['start'], $sessionDates['end']])
            ->get();
            
        $countAttendance = $attendances->count();
        $attendanceByDate = $attendances->keyBy('date');

        $resultlist = [];
        $start = \Carbon\Carbon::parse($sessionDates['start']);
        $end = \Carbon\Carbon::parse($sessionDates['end']);
        $period = \Carbon\CarbonPeriod::create($start, $end);
        foreach ($period as $date) {
            $dateKey = $date->format('Y-m-d');
            if (isset($attendanceByDate[$dateKey])) {
                $att = $attendanceByDate[$dateKey];
                $resultlist[$dateKey] = [
                    'att_type' => $att->attendenceType->type ?? '',
                    'key' => $att->attendenceType->key_value ?? '',
                ];
            } else {
                $resultlist[$dateKey] = [];
            }
        }

        $startMonth = $setting->start_month ?? 1;
        if ($startMonth == 1) {
            $endMonth = 12;
        } else {
            $endMonth = $startMonth - 1;
        }

        $sessionName = $setting->session ?? '';
        $parts = explode('-', $sessionName);
        $startYear = $parts[0] ?? date('Y');
        if (isset($parts[1]) && strlen($parts[1]) == 2) {
            $nextYear = substr($startYear, 0, 2) . $parts[1];
        } else {
            $nextYear = $parts[1] ?? $startYear;
        }

        $monthlist = [];
        for ($x = $startMonth; $x < $startMonth + 12; $x++) {
            $month = date('m', mktime(0, 0, 0, $x, 10));
            $monthlist[$month] = date('F', mktime(0, 0, 0, $x, 10));
        }

        $exam_result = DB::table('exam_group_class_batch_exam_students')
            ->join('exam_group_class_batch_exams', 'exam_group_class_batch_exams.id', '=', 'exam_group_class_batch_exam_students.exam_group_class_batch_exam_id')
            ->where('exam_group_class_batch_exam_students.student_session_id', $studentSession->id)
            ->get();

        $unread_notifications = DB::table('send_notification')
            ->where('visible_student', 'Yes')
            ->where('publish_date', '<=', date('Y-m-d'))
            ->whereNotIn('id', function ($query) use ($user) {
                $query->select('notification_id')
                    ->from('read_notification')
                    ->where('student_id', $user->user_id);
            })->get();

        $marks_division = DB::table('mark_divisions')->get();
        $category_list = DB::table('categories')->get();
        $gradeList = DB::table('grades')->get();
        $attendencetypeslist = DB::table('attendence_type')->orderBy('id')->get();

        $gradeTypes = config('app.exam_type', [
            'basic_system' => 'Basic System',
            'school_grade_system' => 'School Grade System',
            'coll_grade_system' => 'College Grade System',
            'gpa' => 'GPA Grading System',
            'average_passing' => 'Average Passing',
        ]);
        $exam_grade = [];
        foreach ($gradeTypes as $key => $label) {
            $exam_grade[] = [
                'exam_key' => $key,
                'exm_type_value' => $label,
                'exam_grade_values' => DB::table('grades')->where('exam_type', $key)->get(),
            ];
        }

        $data = [
            'sch_setting' => $setting,
            'superadmin_restriction' => $setting->superadmin_restriction ?? 0,
            'marks_division' => $marks_division,
            'student' => [
                'id' => $student->id,
                'admission_no' => $student->admission_no,
                'roll_no' => $student->roll_no,
                'firstname' => $student->firstname,
                'middlename' => $student->middlename,
                'lastname' => $student->lastname,
                'fullname' => $student->fullname,
                'gender' => $student->gender,
                'dob' => $student->dob,
                'religion' => $student->religion,
                'email' => $student->email,
                'mobileno' => $student->mobileno,
                'admission_date' => $student->admission_date,
                'image' => $student->image,
                'father_name' => $student->father_name,
                'father_phone' => $student->father_phone,
                'mother_name' => $student->mother_name,
                'mother_phone' => $student->mother_phone,
                'guardian_name' => $student->guardian_name,
                'guardian_phone' => $student->guardian_phone,
                'guardian_relation' => $student->guardian_relation,
                'guardian_address' => $student->guardian_address,
                'current_address' => $student->current_address ?? $student->permanent_address ?? '',
                'category' => $student->category?->category,
                'class' => $studentSession->class->class ?? null,
                'section' => $studentSession->section->section ?? null,
                'student_session_id' => $studentSession->id,
                'class_id' => $studentSession->class_id,
                'section_id' => $studentSession->section_id,
                'total_points' => 0,
            ],
            'role' => $user->role,
            'student_due_fee' => $feeData['student_due_fee'],
            'student_discount_fee' => $feeData['student_discount_fee'],
            'transport_fees' => $transport_fees,
            'timeline_list' => $timeline,
            'student_doc' => $student_doc,
            'student_doc_id' => $student->id,
            'category_list' => $category_list,
            'gradeList' => $gradeList,
            'examSchedule' => [],
            'exam_result' => $exam_result,
            'exam_grade' => $exam_grade,
            'countAttendance' => $countAttendance,
            'resultlist' => $resultlist,
            'monthlist' => $monthlist,
            'attendencetypeslist' => $attendencetypeslist,
            'session_year_start' => $sessionDates['start'],
            'session_year_end' => $sessionDates['end'],
            'start_year' => $startYear,
            'Next_year' => $nextYear,
            'student_timeline' => $setting->student_timeline ?? 0,
            'unread_notifications' => $unread_notifications,
        ];

        return $this->successResponse($data);
    }

    public function fees(Request $request): JsonResponse
    {
        $user = $request->user();

        if (!$user) {
            return $this->errorResponse('Unauthorized', 401);
        }

        $studentSession = $this->studentSessionService->getStudentSession($user);
        if (!$studentSession) {
            return $this->errorResponse('Student session not found');
        }

        $student = Student::find($studentSession->student_id);
        $setting = $this->schoolSettingsService->getSettings();
        $feeData = $this->buildStudentFeeData($studentSession);

        $student_due_fee = $feeData['student_due_fee'];

        $transport_active = DB::table('permission_group')->where('short_code', 'transport')->value('is_active');
        $transport_fees = $transport_active ? $feeData['transport_fees'] : [];

        $student_processing_fee = false;
        $processingRecords = DB::table('student_fees_processing')
            ->whereIn('student_fees_master_id', collect($student_due_fee)->pluck('id'))
            ->get();
        foreach ($processingRecords as $processing_value) {
            if (!empty($processing_value->fees)) {
                $student_processing_fee = true;
            }
        }

        $categorylist = Category::query()->get()->map(fn($cat) => [
            'id' => $cat->id,
            'category' => $cat->category,
        ])->values();

        return $this->successResponse([
            'categorylist' => $categorylist,
            'sch_setting' => $setting,
            'adm_auto_insert' => $setting ? $setting->adm_auto_insert : false,
            'paymentoption' => false,
            'payment_method' => !empty($this->payment_method ?? false),
            'student_discount_fee' => $feeData['student_discount_fee'],
            'student_due_fee' => $student_due_fee,
            'student' => [
                'id' => $student->id,
                'admission_no' => $student->admission_no,
                'roll_no' => $student->roll_no,
                'firstname' => $student->firstname,
                'middlename' => $student->middlename,
                'lastname' => $student->lastname,
                'image' => $student->image,
                'mobileno' => $student->mobileno,
                'category_id' => $student->category_id,
                'rte' => $student->rte,
                'father_name' => $student->father_name,
                'guardian_phone' => $student->guardian_phone,
                'guardian_email' => $student->guardian_email,
                'parent_app_key' => $student->parent_app_key,
                'class' => $studentSession->class->class ?? null,
                'section' => $studentSession->section->section ?? null,
                'student_session_id' => $studentSession->id,
            ],
            'transport_fees' => $transport_fees,
            'student_processing_fee' => $student_processing_fee,
        ]);
    }

    public function getfees(Request $request): JsonResponse
    {
        $user = $request->user();

        if (!$user) {
            return $this->errorResponse('Unauthorized', 401);
        }

        $studentSession = $this->studentSessionService->getStudentSession($user);
        if (!$studentSession) {
            return $this->errorResponse('Student session not found');
        }

        $student = Student::find($studentSession->student_id);
        $setting = $this->schoolSettingsService->getSettings();
        $feeData = $this->buildStudentFeeData($studentSession);

        $student_due_fee = $feeData['student_due_fee'];

        $transport_active = DB::table('permission_group')->where('short_code', 'transport')->value('is_active');
        $transport_fees = $transport_active ? $feeData['transport_fees'] : [];

        $student_processing_fee = false;
        $processingRecords = DB::table('student_fees_processing')
            ->whereIn('student_fees_master_id', collect($student_due_fee)->pluck('id'))
            ->get();
        foreach ($processingRecords as $processing_value) {
            if (!empty($processing_value->fees)) {
                $student_processing_fee = true;
            }
        }

        $categorylist = Category::query()->get()->map(fn($cat) => [
            'id' => $cat->id,
            'category' => $cat->category,
        ])->values();

        return $this->successResponse([
            'categorylist' => $categorylist,
            'sch_setting' => $setting,
            'adm_auto_insert' => $setting ? $setting->adm_auto_insert : false,
            'paymentoption' => false,
            'payment_method' => !empty($this->payment_method ?? false),
            'student_discount_fee' => $feeData['student_discount_fee'],
            'student_due_fee' => $student_due_fee,
            'student' => [
                'id' => $student->id,
                'admission_no' => $student->admission_no,
                'roll_no' => $student->roll_no,
                'firstname' => $student->firstname,
                'middlename' => $student->middlename,
                'lastname' => $student->lastname,
                'image' => $student->image,
                'mobileno' => $student->mobileno,
                'category_id' => $student->category_id,
                'rte' => $student->rte,
                'father_name' => $student->father_name,
                'guardian_phone' => $student->guardian_phone,
                'guardian_email' => $student->guardian_email,
                'parent_app_key' => $student->parent_app_key,
                'class' => $studentSession->class->class ?? null,
                'section' => $studentSession->section->section ?? null,
                'student_session_id' => $studentSession->id,
            ],
            'transport_fees' => $transport_fees,
            'student_processing_fee' => $student_processing_fee,
        ]);
    }

    private function buildStudentFeeData(StudentSession $studentSession): array
    {
        $studentSession->load([
            'studentFeeMasters.feeSessionGroup.feeGroup',
            'studentTransportFees.transportFeemaster',
            'studentFeesDiscounts.feeDiscount',
        ]);

        $student_due_fee = [];

        foreach ($studentSession->studentFeeMasters as $feeMaster) {
            $feeGroupsFeetype = FeeGroupsFeetype::where('fee_session_group_id', $feeMaster->fee_session_group_id)
                ->with('feeType')
                ->get();

            $fees = [];
            foreach ($feeGroupsFeetype as $ft) {
                $deposit = StudentFeesDeposite::where('student_fees_master_id', $feeMaster->id)
                    ->where('fee_groups_feetype_id', $ft->id)
                    ->first();

                $fees[] = (object) [
                    'id' => $feeMaster->id,
                    'is_system' => $feeMaster->is_system,
                    'student_session_id' => $feeMaster->student_session_id,
                    'fee_session_group_id' => $feeMaster->fee_session_group_id,
                    'amount' => $ft->amount,
                    'fee_groups_feetype_id' => $ft->id,
                    'due_date' => $ft->due_date,
                    'fine_amount' => $ft->fine_amount,
                    'name' => $feeMaster->feeSessionGroup->feeGroup->name ?? null,
                    'code' => $ft->feeType->code ?? null,
                    'type' => $ft->feeType->type ?? null,
                    'student_fees_deposite_id' => $deposit ? $deposit->id : 0,
                    'amount_detail' => $deposit ? $deposit->amount_detail : 0,
                ];
            }

            if ($feeMaster->is_system != 0 && !empty($fees)) {
                $fees[0]->amount = $feeMaster->amount;
            }

            $student_due_fee[] = (object) [
                'id' => $feeMaster->id,
                'fees' => $fees,
            ];
        }

        $transport_fees = [];
        foreach ($studentSession->studentTransportFees as $transportFee) {
            $deposit = StudentFeesDeposite::where('student_transport_fee_id', $transportFee->id)->first();

            $transport_fees[] = (object) [
                'id' => $transportFee->id,
                'month' => $transportFee->transportFeemaster->month ?? null,
                'due_date' => $transportFee->transportFeemaster->due_date ?? null,
                'fees' => (float) (DB::table('route_pickup_point')->where('id', $transportFee->route_pickup_point_id)->value('fees') ?? 0),
                'fine_amount' => $transportFee->transportFeemaster->fine_amount ?? 0,
                'fine_type' => $transportFee->transportFeemaster->fine_type ?? null,
                'fine_percentage' => $transportFee->transportFeemaster->fine_percentage ?? 0,
                'student_fees_deposite_id' => $deposit ? $deposit->id : 0,
                'amount_detail' => $deposit ? $deposit->amount_detail : 0,
            ];
        }

        $student_discount_fee = [];
        foreach ($studentSession->studentFeesDiscounts as $discount) {
            $student_discount_fee[] = [
                'code' => $discount->feeDiscount->code ?? null,
                'status' => $discount->status,
                'payment_id' => $discount->payment_id,
                'student_fees_discount_description' => $discount->description ?? null,
                'type' => $discount->feeDiscount->type ?? null,
                'amount' => $discount->feeDiscount->amount ?? null,
                'percentage' => $discount->feeDiscount->percentage ?? null,
            ];
        }

        return [
            'student_due_fee' => $student_due_fee,
            'transport_fees' => $transport_fees,
            'student_discount_fee' => $student_discount_fee,
        ];
    }

    public function documents(Request $request): JsonResponse
    {
        $user = $request->user();
        $studentId = $this->getStudentId($user);

        if (! $studentId) {
            return $this->errorResponse('Student not found', null, 404);
        }

        $documents = StudentDoc::where('student_id', $studentId)
            ->orderByDesc('id')
            ->get();

        return $this->successResponse(['documents' => $documents]);
    }

    public function adddoc(Request $request): JsonResponse
    {
        $user = $request->user();
        $studentId = $this->getStudentId($user);

        if (! $studentId) {
            return $this->errorResponse('Student not found', null, 404);
        }

        $validated = $request->validate([
            'first_title' => 'required|string|max:255',
            'first_doc' => 'required|file',
        ]);

        $file = $request->file('first_doc');
        $filename = time() . '_' . bin2hex(random_bytes(12)) . '.' . $file->getClientOriginalExtension();
        $file->storeAs('uploads/student_documents/' . $studentId, $filename, 'local');

        $document = StudentDoc::create([
            'student_id' => $studentId,
            'title' => $validated['first_title'],
            'doc' => $filename,
        ]);

        return $this->successResponse(['document' => $document], 'Document uploaded successfully');
    }

    public function downloaddoc(Request $request, $id): JsonResponse|BinaryFileResponse
    {
        $user = $request->user();
        $studentId = $this->getStudentId($user);

        $document = StudentDoc::where('id', $id)
            ->where('student_id', $studentId)
            ->first();

        if (! $document) {
            return $this->errorResponse('Document not found', null, 404);
        }

        return $this->sendStoredFile($document->doc, 'uploads/student_documents/' . $studentId);
    }

    public function changeusername(Request $request): JsonResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'current_username' => 'required|string',
            'new_username' => 'required|string|max:191',
            'confirm_username' => 'required|string|same:new_username',
        ]);

        if ($user->username !== $validated['current_username']) {
            return $this->errorResponse('Invalid current username');
        }

        $exists = DB::table('users')
            ->where('username', $validated['new_username'])
            ->where('id', '!=', $user->id)
            ->exists();

        if ($exists) {
            return $this->errorResponse('Username already exists, please choose another');
        }

        DB::table('users')->where('id', $user->id)->update(['username' => $validated['new_username']]);

        return $this->successResponse(null, 'Username changed successfully');
    }

    public function language(Request $request): JsonResponse
    {
        $user = $request->user();

        $langId = (int) $request->input('lang_id');

        if (! $langId) {
            return $this->errorResponse('lang_id is required');
        }

        $language = DB::table('languages')->where('id', $langId)->first();

        if (! $language) {
            return $this->errorResponse('Invalid language');
        }

        DB::table('users')->where('id', $user->id)->update(['lang_id' => $langId]);

        return $this->successResponse([
            'lang_id' => $langId,
            'language' => $language->language,
            'is_rtl' => (int) $language->is_rtl,
        ], 'Language changed successfully');
    }

    public function currency(Request $request): JsonResponse
    {
        $user = $request->user();

        $currencyId = (int) $request->input('currency_id');

        if (! $currencyId) {
            return $this->errorResponse('currency_id is required');
        }

        $currency = DB::table('currencies')->where('id', $currencyId)->first();

        if (! $currency) {
            return $this->errorResponse('Invalid currency');
        }

        DB::table('users')->where('id', $user->id)->update(['currency_id' => $currencyId]);

        return $this->successResponse([
            'currency_id' => $currencyId,
            'currency' => $currency->short_name ?? null,
            'symbol' => $currency->symbol ?? null,
        ], 'Currency changed successfully');
    }

    private function getStudentId($user): ?int
    {
        if ($user->role === 'student') {
            return isset($user->user_id) ? (int) $user->user_id : null;
        }

        if ($user->role === 'parent') {
            $student = Student::where('parent_id', $user->id)->first();

            return $student ? (int) $student->id : null;
        }

        return null;
    }


}
