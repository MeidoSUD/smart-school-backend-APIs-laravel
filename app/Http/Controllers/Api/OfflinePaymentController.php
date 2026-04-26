<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\OfflinePayment;
use App\Models\StudentSession;
use App\Models\Student;
use App\Models\Setting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Converted from CodeIgniter: codelgiterControllers/user/Offlinepayment.php
 */
class OfflinePaymentController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $studentSession = $this->getStudentSession($user);
        
        if (!$studentSession) {
            return $this->errorResponse('Student session not found');
        }
        
        $student = Student::find($studentSession->student_id);
        $paymentList = OfflinePayment::where('student_session_id', $studentSession->id)
            ->orderBy('payment_date', 'desc')
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
        $studentSession = $this->getStudentSession($user);
        
        if (!$studentSession) {
            return $this->errorResponse('Student session not found');
        }
        
        $offlinePayment = OfflinePayment::create([
            'student_session_id' => $studentSession->id,
            'amount' => $request->amount,
            'payment_mode' => $request->payment_mode,
            'payment_date' => now(),
            'status' => 'pending',
        ]);
        
        return $this->successResponse(['id' => $offlinePayment->id], 'Payment request submitted successfully');
    }

    private function getStudentSession($user)
    {
        $studentId = null;
        
        if ($user->role === 'student') {
            $studentId = $user->user_id;
        } elseif ($user->role === 'parent') {
            $student = Student::where('parent_id', $user->id)->first();
            $studentId = $student ? $student->id : null;
        }
        
        if (!$studentId) {
            return null;
        }
        
        $setting = Setting::where('is_active', 1)->first();
        
        return StudentSession::where('student_id', $studentId)
            ->when($setting, fn($q) => $q->where('session_id', $setting->id))
            ->first();
    }
}