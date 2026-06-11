<?php

namespace Modules\Core\Http\Controllers\Api;

use Modules\Academic\Entities\Student;
use Modules\Academic\Services\DashboardService;
use Modules\Core\Entities\Classe;
use Modules\Core\Entities\Section;
use Modules\Core\Entities\Setting;
use Modules\Finance\Services\StudentFeeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Carbon\Carbon;

class UserController extends \Modules\Core\Http\Controllers\Api\Controller
{
    public function __construct(
        private DashboardService $dashboardService,
        private StudentFeeService $feeService,
    ) {
        $this->setControllerName('UserController');
    }

    public function dashboard(Request $request): JsonResponse
    {
        $user = $request->user();

        if (!$user) {
            return $this->errorResponse('Unauthorized', null, 401);
        }

        $studentSession = $this->studentSession($request);
        if (!$studentSession) {
            return $this->errorResponse('Student session not found');
        }

        $data = $this->dashboardService->build($user, $studentSession);

        return $this->successResponse($data);
    }

    public function profile(Request $request): JsonResponse
    {
        $user = $request->user();

        if (!$user) {
            return $this->errorResponse('Unauthorized', null, 401);
        }

        $data = [];

        $setting = Setting::first();
        $data['sch_setting'] = $setting;

        $studentSession = $this->studentSession($request);
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
            return $this->errorResponse('Unauthorized', null, 401);
        }

        $studentSession = $this->studentSession($request);
        if (!$studentSession) {
            return $this->errorResponse('Student session not found');
        }

        $setting = Setting::first();
        $student = Student::find($studentSession->student_id);
        $class = Classe::find($studentSession->class_id);
        $section = Section::find($studentSession->section_id);

        $studentDueFee = $this->feeService->getStudentFees($studentSession->id);

        if ($setting && (int) ($setting->is_student_feature_lock ?? 0) === 1) {
            $lockGracePeriod = (int) ($setting->lock_grace_period ?? 0);
            $asOfDate = Carbon::today()->subDays($lockGracePeriod)->toDateString();
            $studentDueFee = $this->feeService->getDueFeesByStudent($studentSession->id, $asOfDate);
        }

        $data = [
            'sch_setting' => $setting,
            'adm_auto_insert' => $setting ? $setting->adm_auto_insert : false,
            'payment_method' => $this->feeService->isPaymentMethodAvailable(),
            'student' => [
                'id' => $student->id,
                'firstname' => $student->firstname,
                'lastname' => $student->lastname,
                'class' => $class ? $class->class : null,
                'section' => $section ? $section->section : null,
                'student_session_id' => $studentSession->id,
            ],
            'student_due_fee' => $studentDueFee,
        ];

        return $this->successResponse($data);
    }

    public function getfees(Request $request): JsonResponse
    {
        $user = $request->user();

        if (!$user) {
            return $this->errorResponse('Unauthorized', null, 401);
        }

        $studentSession = $this->studentSession($request);
        if (!$studentSession) {
            return $this->errorResponse('Student session not found');
        }

        $setting = Setting::first();
        $student = Student::find($studentSession->student_id);
        $class = Classe::find($studentSession->class_id);
        $section = Section::find($studentSession->section_id);

        $transportFees = [];
        if ($studentSession->route_pickup_point_id) {
            $transportFees = $this->feeService->getStudentTransportFees(
                $studentSession->id,
                (int) $studentSession->route_pickup_point_id
            );
        }

        $data = [
            'sch_setting' => $setting,
            'adm_auto_insert' => $setting ? $setting->adm_auto_insert : false,
            'payment_method' => $this->feeService->isPaymentMethodAvailable(),
            'student' => [
                'id' => $student->id,
                'firstname' => $student->firstname,
                'lastname' => $student->lastname,
                'class' => $class ? $class->class : null,
                'section' => $section ? $section->section : null,
                'student_session_id' => $studentSession->id,
                'class_id' => $studentSession->class_id,
                'section_id' => $studentSession->section_id,
            ],
            'student_due_fee' => $this->feeService->getStudentFees($studentSession->id),
            'transport_fees' => $transportFees,
            'student_discount_fee' => $this->feeService->getStudentFeesDiscount($studentSession->id),
            'student_processing_fee' => false,
        ];

        return $this->successResponse($data);
    }
}
