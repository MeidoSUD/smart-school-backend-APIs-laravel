<?php

namespace Modules\Core\Http\Controllers\Api;

 
use Modules\Academic\Entities\Student;
use Modules\Academic\Entities\StudentSession;
use Modules\Core\Entities\Setting;
use Modules\Academic\Entities\AttendenceType;
use Modules\Academic\Entities\Classe;
use Modules\Academic\Entities\Section;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Carbon\Carbon;

class UserController extends \Modules\Core\Http\Controllers\Api\Controller
{
    public function __construct()
    {
        $this->setControllerName('UserController');
    }

    public function dashboard(Request $request): JsonResponse
    {
        $user = $request->user();

        if (!$user) {
            return $this->errorResponse('Unauthorized', 401);
        }

        $data = [];

        $studentSession = $this->getStudentSession($user);
        if (!$studentSession) {
            return $this->errorResponse('Student session not found');
        }

        $setting = $this->getSchoolSettings();

        $sessionDetails = $this->getSessionDates($setting);

        $data['attendence_percentage'] = $this->calculateAttendancePercentage(
            $studentSession->id,
            $sessionDetails['start'],
            $sessionDetails['end']
        );

        $class = Classe::find($studentSession->class_id);
        $section = Section::find($studentSession->section_id);

        $student = Student::find($studentSession->student_id);

        $data['studentsession_username'] = $user->username;
        $data['student_data'] = [
            'id' => $user->id,
            'username' => $user->username,
            'role' => $user->role,
            'student_id' => $studentSession->student_id,
            'class' => $class ? $class->class : null,
            'section' => $section ? $section->section : null,
        ];

        $settings = Setting::first();
        $data['low_attendance_limit'] = $settings ? ($settings->low_attendance_limit ?? 75) : 75;

        return $this->successResponse($data);
    }

    public function profile(Request $request): JsonResponse
    {
        $user = $request->user();

        if (!$user) {
            return $this->errorResponse('Unauthorized', 401);
        }

        $data = [];

        $setting = $this->getSchoolSettings();
        $data['sch_setting'] = $setting;

        $studentSession = $this->getStudentSession($user);
        if (!$studentSession) {
            return $this->errorResponse('Student session not found');
        }

        $student = Student::with(['category'])
            ->find($studentSession->student_id);

        if (!$student) {
            return $this->errorResponse('Student not found');
        }

        $class = Classe::find($studentSession->class_id);
        $section = Section::find($studentSession->section_id);

        $data['student'] = [
            'id' => $student->id,
            'admission_no' => $student->admission_no,
            'roll_no' => $student->roll_no,
            'firstname' => $student->firstname,
            'middlename' => $student->middlename,
            'lastname' => $student->lastname,
            'fullname' => $student->firstname . ' ' . $student->lastname,
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
            'current_address' => $student->local_address ?? $student->permanent_address ?? '',
            'category' => $student->category ? $student->category->category : null,
            'class' => $class ? $class->class : null,
            'section' => $section ? $section->section : null,
            'student_session_id' => $studentSession->id,
            'class_id' => $studentSession->class_id,
            'section_id' => $studentSession->section_id,
        ];

        $data['role'] = $user->role;

        return $this->successResponse($data);
    }

    public function fees(Request $request): JsonResponse
    {
        $user = $request->user();

        if (!$user) {
            return $this->errorResponse('Unauthorized', 401);
        }

        $data = [];

        $studentSession = $this->getStudentSession($user);
        if (!$studentSession) {
            return $this->errorResponse('Student session not found');
        }

        $setting = $this->getSchoolSettings();
        $data['sch_setting'] = $setting;

        $student = Student::find($studentSession->student_id);
        $class = Classe::find($studentSession->class_id);
        $section = Section::find($studentSession->section_id);

        $data['student'] = [
            'id' => $student->id,
            'firstname' => $student->firstname,
            'lastname' => $student->lastname,
            'class' => $class ? $class->class : null,
            'section' => $section ? $section->section : null,
            'student_session_id' => $studentSession->id,
        ];

        $data['payment_method'] = false;

        return $this->successResponse($data);
    }

    public function getfees(Request $request): JsonResponse
    {
        $user = $request->user();

        if (!$user) {
            return $this->errorResponse('Unauthorized', 401);
        }

        $data = [];

        $studentSession = $this->getStudentSession($user);
        if (!$studentSession) {
            return $this->errorResponse('Student session not found');
        }

        $setting = $this->getSchoolSettings();
        $data['sch_setting'] = $setting;
        $data['adm_auto_insert'] = $setting ? $setting->adm_auto_insert : false;

        $student = Student::find($studentSession->student_id);
        $class = Classe::find($studentSession->class_id);
        $section = Section::find($studentSession->section_id);

        $data['student'] = [
            'id' => $student->id,
            'firstname' => $student->firstname,
            'lastname' => $student->lastname,
            'class' => $class ? $class->class : null,
            'section' => $section ? $section->section : null,
            'student_session_id' => $studentSession->id,
            'class_id' => $studentSession->class_id,
            'section_id' => $studentSession->section_id,
        ];

        $data['payment_method'] = false;
        $data['student_due_fee'] = [];
        $data['transport_fees'] = [];
        $data['student_discount_fee'] = [];

        return $this->successResponse($data);
    }

    private function getStudentSession($user): ?StudentSession
    {
        $studentId = null;

        switch ($user->role) {
            case 'student':
                $studentId = $user->user_id;
                break;
            case 'parent':
                $firstChild = Student::where('parent_id', $user->id)->first();
                $studentId = $firstChild ? $firstChild->id : null;
                break;
            default:
                return null;
        }

        if (!$studentId) {
            return null;
        }

        $currentSession = $this->getCurrentSession();

        $studentSession = StudentSession::where('student_id', $studentId)
            ->where('session_id', $currentSession ? $currentSession->id : null)
            ->first();

        if (!$studentSession) {
            $studentSession = StudentSession::where('student_id', $studentId)
                ->where('default_login', 1)
                ->first();
        }

        if (!$studentSession) {
            $studentSession = StudentSession::where('student_id', $studentId)
                ->orderBy('id', 'desc')
                ->first();
        }

        return $studentSession;
    }

    private function getCurrentSession()
    {
        return \Modules\Core\Entities\Session::where('is_active', 1)->first();
    }

    private function getSchoolSettings(): ?Setting
    {
        return Setting::first();
    }

    private function getSessionDates(?Setting $setting): array
    {
        $startMonth = $setting ? ($setting->start_month ?? 4) : 4;

        $currentYear = date('Y');
        $start = Carbon::createFromDate($currentYear, $startMonth, 1)->startOfMonth();
        $end = Carbon::createFromDate($currentYear, $startMonth, 1)->addYear()->endOfMonth();

        if (date('n') < $startMonth) {
            $start = $start->subYear();
            $end = $end->subYear();
        }

        return [
            'start' => $start->toDateString(),
            'end' => min($end->toDateString(), date('Y-m-d')),
        ];
    }

    private function calculateAttendancePercentage(int $studentSessionId, string $start, string $end): float
    {
        return -1.0;
    }
}
