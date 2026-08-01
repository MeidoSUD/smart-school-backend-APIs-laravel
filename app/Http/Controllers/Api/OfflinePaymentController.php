<?php

namespace App\Http\Controllers\Api;

use App\Models\OfflinePayment;
use App\Models\Student;
use App\Services\StudentSessionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OfflinePaymentController extends \App\Http\Controllers\Api\Controller
{
    public function __construct(
        private readonly StudentSessionService $studentSessionService
    ) {
        $this->setControllerName('OfflinePaymentController');
    }

    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $studentSession = $this->studentSessionService->getStudentSession($user);
        
        if (!$studentSession) {
            return $this->errorResponse('Student session not found');
            }


        
        $student = Student::find($studentSession->student_id);
        $paymentList = OfflinePayment::where('student_session_id', $studentSession->id)
            ->orderByDesc('submit_date')
            ->orderByDesc('payment_date')
            ->get();
        
        $data = [
            'student' => $student,
            'payment_list' => $paymentList,
        ];
        
        return $this->successResponse($data);
        }



    public function add(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|min:0',
            'payment_mode' => 'required|string',
        ]);
        
        $user = $request->user();
        $studentSession = $this->studentSessionService->getStudentSession($user);
        
        if (!$studentSession) {
            return $this->errorResponse('Student session not found');
            }


        
        $offlinePayment = OfflinePayment::create([
            'student_session_id' => $studentSession->id,
            'amount' => $request->amount,
            'reference' => $request->payment_mode,
            'payment_date' => now()->toDateString(),
            'submit_date' => now(),
            'is_active' => '0',
        ]);
        
        return $this->successResponse(['id' => $offlinePayment->id], 'Payment request submitted successfully');
    }
}
