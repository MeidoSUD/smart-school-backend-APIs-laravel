<?php

namespace Modules\Core\Http\Controllers\Api;

use Modules\Academic\Entities\Student;
use Modules\Academic\Entities\StudentSession;
use Modules\Core\Services\SchoolSettingsService;
use Modules\Core\Services\StudentSessionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

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

        $sessionDetails = $this->schoolSettingsService->sessionDates();

        $data = [
            'attendence_percentage' => -1.0,
            'studentsession_username' => $user->username,
            'student_data' => [
                'id' => $user->id,
                'username' => $user->username,
                'role' => $user->role,
                'student_id' => $studentSession->student_id,
                'class' => $studentSession->class->class ?? null,
                'section' => $studentSession->section->section ?? null,
            ],
            'low_attendance_limit' => $this->schoolSettingsService->lowAttendanceLimit(),
        ];

        return $this->successResponse($data);
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
                'current_address' => $student->local_address ?? $student->permanent_address ?? '',
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

        return $this->successResponse([
            'sch_setting' => $this->schoolSettingsService->getSettings(),
            'student' => [
                'id' => $student->id,
                'firstname' => $student->firstname,
                'lastname' => $student->lastname,
                'class' => $studentSession->class->class ?? null,
                'section' => $studentSession->section->section ?? null,
                'student_session_id' => $studentSession->id,
            ],
            'payment_method' => false,
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

        $setting = $this->schoolSettingsService->getSettings();
        $student = Student::find($studentSession->student_id);

        return $this->successResponse([
            'sch_setting' => $setting,
            'adm_auto_insert' => $setting ? $setting->adm_auto_insert : false,
            'student' => [
                'id' => $student->id,
                'firstname' => $student->firstname,
                'lastname' => $student->lastname,
                'class' => $studentSession->class->class ?? null,
                'section' => $studentSession->section->section ?? null,
                'student_session_id' => $studentSession->id,
                'class_id' => $studentSession->class_id,
                'section_id' => $studentSession->section_id,
            ],
            'payment_method' => false,
            'student_due_fee' => [],
            'transport_fees' => [],
            'student_discount_fee' => [],
        ]);
    }
}
