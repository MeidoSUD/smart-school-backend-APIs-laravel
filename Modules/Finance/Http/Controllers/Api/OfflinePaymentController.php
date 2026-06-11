<?php

namespace Modules\Finance\Http\Controllers\Api;

use Modules\Finance\Entities\OfflinePayment;
use Modules\Academic\Entities\StudentSession;
use Modules\Academic\Entities\Student;
use Modules\Core\Entities\Setting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Converted from CodeIgniter: codelgiterControllers/user/Offlinepayment.php
 */
class OfflinePaymentController extends \Modules\Core\Http\Controllers\Api\Controller
{
    public function __construct()
    {
        $this->setControllerName('OfflinePaymentController');
        }

    public function index(Request $request): JsonResponse
    {
        $studentSession = $this->studentSession($request);
        
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
        
        $studentSession = $this->studentSession($request);
        
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
