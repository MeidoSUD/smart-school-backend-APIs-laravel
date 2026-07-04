<?php

namespace Modules\Core\Http\Controllers\Api;

use Modules\Academic\Entities\Student;
use Modules\Academic\Entities\StudentSession;
use Modules\Core\Services\SchoolSettingsService;
use Modules\Core\Services\StudentSessionService;
use Modules\Finance\Entities\FeeSessionGroup;
use Modules\Finance\Entities\FeeGroupsFeetype;
use Modules\Finance\Entities\StudentFeesDeposite;
use Modules\Finance\Entities\TransportFeemaster;
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

        $studentSession->load([
            'studentFeeMasters.feeSessionGroup.feeGroup',
            'studentTransportFees.transportFeemaster',
            'studentFeesDiscounts.feeDiscount',
        ]);

        $student_due_fee = $studentSession->studentFeeMasters->map(function ($feeMaster) {
            $amountDetail = StudentFeesDeposite::where('student_fees_master_id', $feeMaster->id)->get();
            $feeGroupsFeetype = collect();
            if ($feeMaster->feeSessionGroup) {
                $feeGroupsFeetype = FeeGroupsFeetype::where('fee_session_group_id', $feeMaster->fee_session_group_id)
                    ->with('feeType')
                    ->get();
            }
            return [
                'id' => $feeMaster->id,
                'is_system' => $feeMaster->is_system,
                'student_session_id' => $feeMaster->student_session_id,
                'fee_session_group_id' => $feeMaster->fee_session_group_id,
                'amount' => $feeMaster->amount,
                'fee_group' => $feeMaster->feeSessionGroup->feeGroup->name ?? null,
                'fee_group_id' => $feeMaster->feeSessionGroup->feeGroup->id ?? null,
                'fee_types' => $feeGroupsFeetype->map(fn($ft) => [
                    'id' => $ft->id,
                    'fee_type' => $ft->feeType->type ?? null,
                    'fee_type_code' => $ft->feeType->code ?? null,
                    'amount' => $ft->amount,
                    'due_date' => $ft->due_date,
                    'fine_type' => $ft->fine_type,
                    'fine_percentage' => $ft->fine_percentage,
                    'fine_amount' => $ft->fine_amount,
                ]),
                'amount_detail' => $amountDetail->map(fn($dep) => $dep->amount_detail),
                'amount_deposited' => $amountDetail->sum(function($dep) { $d = json_decode($dep->amount_detail, true); return (float)($d['amount'] ?? 0); }),
            ];
        })->values();

        $transport_fees = $studentSession->studentTransportFees->map(function ($transportFee) {
            $amountDetail = StudentFeesDeposite::where('student_transport_fee_id', $transportFee->id)->get();
            return [
                'id' => $transportFee->id,
                'transport_feemaster_id' => $transportFee->transport_feemaster_id,
                'route_pickup_point_id' => $transportFee->route_pickup_point_id,
                'month' => $transportFee->transportFeemaster->month ?? null,
                'due_date' => $transportFee->transportFeemaster->due_date ?? null,
                'fine_amount' => $transportFee->transportFeemaster->fine_amount ?? 0,
                'fine_type' => $transportFee->transportFeemaster->fine_type ?? null,
                'fine_percentage' => $transportFee->transportFeemaster->fine_percentage ?? 0,
                'amount_detail' => $amountDetail->map(fn($dep) => $dep->amount_detail),
            ];
        })->values();

        $student_discount_fee = $studentSession->studentFeesDiscounts->map(function ($discount) {
            return [
                'id' => $discount->id,
                'student_session_id' => $discount->student_session_id,
                'fees_discount_id' => $discount->fees_discount_id,
                'status' => $discount->status,
                'payment_id' => $discount->payment_id,
                'description' => $discount->description,
                'name' => $discount->feeDiscount->name ?? null,
                'code' => $discount->feeDiscount->code ?? null,
                'type' => $discount->feeDiscount->type ?? null,
                'percentage' => $discount->feeDiscount->percentage ?? null,
                'amount' => $discount->feeDiscount->amount ?? null,
            ];
        })->values();

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
            'student_due_fee' => $student_due_fee,
            'transport_fees' => $transport_fees,
            'student_discount_fee' => $student_discount_fee,
        ]);
    }
}
