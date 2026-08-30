<?php

namespace Modules\Core\Http\Controllers\Api;

use Modules\Academic\Entities\Student;
use Modules\Academic\Entities\StudentSession;
use Modules\Academic\Entities\StudentAttendence;
use Modules\Academic\Entities\AttendenceType;
use Modules\Academic\Entities\Homework;
use Modules\Academic\Entities\SubmitAssignment;
use Modules\Academic\Entities\Syllabus;
use Modules\Academic\Entities\ClassTimetable;
use Modules\Core\Entities\Category;
use Modules\Core\Services\SchoolSettingsService;
use Modules\Core\Services\StudentSessionService;
use Modules\Finance\Entities\FeeSessionGroup;
use Modules\Finance\Entities\FeeGroupsFeetype;
use Modules\Finance\Entities\StudentFeesDeposite;
use Modules\Operations\Entities\LibraryMember;
use Modules\Operations\Entities\Notification;
use Modules\Operations\Entities\Visitor;
use Modules\Staff\Entities\Staff;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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
        $checkIsMember = LibraryMember::where('member_type', $memberType)
            ->where('member_id', $studentId)
            ->first();
        $bookList = $checkIsMember ? true : false;

        $homeworklist = Homework::where('class_id', $classId)
            ->where('section_id', $sectionId)
            ->get()
            ->map(function ($hw) use ($studentId) {
                $checkstatus = SubmitAssignment::where('homework_id', $hw->id)
                    ->where('student_id', $studentId)
                    ->count();
                $hw->status = $checkstatus > 0 ? 'submitted' : '';
                return $hw;
            });

        $notifications = Notification::where('is_active', 'yes')
            ->where('publish_date', '<=', now()->toDateString())
            ->when($user->role === 'student', fn($q) => $q->where('visible_student', 'yes'))
            ->when($user->role === 'parent', fn($q) => $q->where('visible_parent', 'yes'))
            ->orderByDesc('publish_date')
            ->get()
            ->filter(fn($n) => strtotime(date('Y-m-d')) >= strtotime($n->publish_date))
            ->values();

        $setting = $this->schoolSettingsService->getSettings();
        $sessionId = $setting->session_id;

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
            $subjectsData[$value->subject_group_subjects_id] = [
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
                        'subject' => $subjectName,
                        'subject_code' => $subjectCode,
                        'teacher' => $row->staff ? $row->staff->name : 'N/A',
                        'time_from' => $row->time_from,
                        'time_to' => $row->time_to,
                        'room' => $row->room_no ?? '',
                        'day' => $row->day,
                    ];
                });
        }

        $visitors = Visitor::where('student_session_id', $studentSessionId)->get();

        $teachers = [];
        $studentTeacher = ClassTimetable::where('class_id', $classId)
            ->where('section_id', $sectionId)
            ->with('staff')
            ->get()
            ->pluck('staff')
            ->filter()
            ->unique('id')
            ->values();

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
            ],
            'low_attendance_limit' => $this->schoolSettingsService->lowAttendanceLimit(),
            'teacherlist' => $studentTeacher,
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

        $data = [
            'sch_setting' => $this->schoolSettingsService->getSettings(),
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
            ],
            'role' => $user->role,
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


}
