<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Models\StudentFee;

class StudentFeeController extends \App\Http\Controllers\Api\Controller
{
    public function __construct()
    {
        $this->setControllerName('StudentFeeController');
    }

    public function index(Request $request): JsonResponse
    {
        $query = StudentFee::query();

        if ($request->filled('student_id')) {
            $query->where('student_session_id', $request->student_id);
        }

        if ($request->filled('student_fees_master_id')) {
            $query->where('student_fees_master_id', $request->student_fees_master_id);
        }

        $studentFees = $query->get();
        return $this->successResponse($studentFees);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'student_session_id' => 'required|integer|exists:student_sessions,id',
            'feemaster_id' => 'required|integer|exists:feemasters,id',
            'amount' => 'required|numeric|min:0',
            'amount_discount' => 'nullable|numeric|min:0',
            'amount_fine' => 'nullable|numeric|min:0',
            'description' => 'nullable|string',
            'date' => 'nullable|date',
            'payment_mode' => 'nullable|string',
            'is_active' => 'nullable|string',
        ]);

        $studentFee = StudentFee::create($validated);
        return $this->successResponse($studentFee, 'Student fee created successfully');
    }

    public function show(Request $request, int $id): JsonResponse
    {
        $studentFee = StudentFee::find($id);

        if (!$studentFee) {
            return $this->errorResponse('Student fee not found', statusCode: 404);
        }

        return $this->successResponse($studentFee);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $studentFee = StudentFee::find($id);

        if (!$studentFee) {
            return $this->errorResponse('Student fee not found', statusCode: 404);
        }

        $validated = $request->validate([
            'student_session_id' => 'sometimes|required|integer|exists:student_sessions,id',
            'feemaster_id' => 'sometimes|required|integer|exists:feemasters,id',
            'amount' => 'sometimes|required|numeric|min:0',
            'amount_discount' => 'nullable|numeric|min:0',
            'amount_fine' => 'nullable|numeric|min:0',
            'description' => 'nullable|string',
            'date' => 'nullable|date',
            'payment_mode' => 'nullable|string',
            'is_active' => 'nullable|string',
        ]);

        $studentFee->update($validated);
        return $this->successResponse($studentFee, 'Student fee updated successfully');
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        $studentFee = StudentFee::find($id);

        if (!$studentFee) {
            return $this->errorResponse('Student fee not found', statusCode: 404);
        }

        $studentFee->delete();
        return $this->successResponse(message: 'Student fee deleted successfully');
    }
}
