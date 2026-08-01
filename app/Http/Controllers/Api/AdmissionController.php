<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\AdmissionSubmitRequest;
use App\Services\AdmissionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdmissionController extends \App\Http\Controllers\Api\Controller
{
    public function __construct(
        private readonly AdmissionService $admissionService
    ) {
        $this->setControllerName('AdmissionController');
    }

    public function index(): JsonResponse
    {
        $setting = \App\Models\Setting::first();

        $data = [
            'enabled' => (bool) ($setting->online_admission ?? false),
            'instructions' => $setting->online_admission_instruction ?? '',
            'conditions' => $setting->online_admission_conditions ?? '',
            'amount' => $setting->online_admission_amount ?? 0,
            'payment_enabled' => ($setting->online_admission_payment ?? 'no') === 'yes',
        ];

        return $this->successResponse($data);
    }

    public function form_config(): JsonResponse
    {
        return $this->successResponse($this->admissionService->getFormConfig());
    }

    public function classes(): JsonResponse
    {
        return $this->successResponse($this->admissionService->getActiveClasses());
    }

    public function sections(Request $request): JsonResponse
    {
        $classId = $request->get('class_id');

        if (!$classId) {
            return $this->errorResponse('class_id is required');
        }

        return $this->successResponse(
            $this->admissionService->getSectionsForClass((int) $classId)
        );
    }

    public function submit(AdmissionSubmitRequest $request): JsonResponse
    {
        $setting = \App\Models\Setting::first();

        if (!($setting->online_admission ?? false)) {
            return $this->errorResponse('Online admission is currently disabled');
        }

        $onlineStudent = $this->admissionService->submitAdmission($request);

        return $this->successResponse([
            'admission_id' => $onlineStudent->id,
            'reference_no' => $onlineStudent->reference_no,
            'message' => 'Registration successful. Please note your reference number for further communication.',
        ], 'Admission form submitted successfully');
    }

    public function status(Request $request): JsonResponse
    {
        $referenceNo = $request->get('reference_no');

        if (!$referenceNo) {
            return $this->errorResponse('reference_no is required');
        }

        $admission = $this->admissionService->getAdmissionStatus($referenceNo);

        if (!$admission) {
            return $this->errorResponse('No admission found with this reference number', null, 404);
        }

        return $this->successResponse([
            'reference_no' => $admission->reference_no,
            'firstname' => $admission->firstname,
            'lastname' => $admission->lastname,
            'form_status' => $admission->form_status,
            'paid_status' => $admission->paid_status,
            'submitted_date' => $admission->created_at,
        ]);
    }
}
