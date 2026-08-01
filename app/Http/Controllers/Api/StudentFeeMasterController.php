<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Models\StudentFeeMaster;

class StudentFeeMasterController extends \App\Http\Controllers\Api\Controller
{
    public function __construct()
    {
        $this->setControllerName('StudentFeeMasterController');
    }

    public function index(Request $request): JsonResponse
    {
        $query = StudentFeeMaster::query();

        if ($request->filled('student_session_id')) {
            $query->where('student_session_id', $request->student_session_id);
        }

        if ($request->filled('fee_session_group_id')) {
            $query->where('fee_session_group_id', $request->fee_session_group_id);
        }

        $studentFeeMasters = $query->get();
        return $this->successResponse($studentFeeMasters);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'fee_session_group_id' => 'required|integer|exists:fee_session_groups,id',
            'student_session_id' => 'required|integer|exists:student_sessions,id',
            'amount' => 'nullable|numeric|min:0',
            'is_active' => 'nullable|string',
        ]);

        $studentFeeMaster = StudentFeeMaster::create($validated);
        return $this->successResponse($studentFeeMaster, 'Student fee master created successfully');
    }

    public function show(Request $request, int $id): JsonResponse
    {
        $studentFeeMaster = StudentFeeMaster::with(['feeSessionGroup', 'studentSession'])->find($id);

        if (!$studentFeeMaster) {
            return $this->errorResponse('Student fee master not found', statusCode: 404);
        }

        return $this->successResponse($studentFeeMaster);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $studentFeeMaster = StudentFeeMaster::find($id);

        if (!$studentFeeMaster) {
            return $this->errorResponse('Student fee master not found', statusCode: 404);
        }

        $validated = $request->validate([
            'fee_session_group_id' => 'sometimes|required|integer|exists:fee_session_groups,id',
            'student_session_id' => 'sometimes|required|integer|exists:student_sessions,id',
            'amount' => 'nullable|numeric|min:0',
            'is_active' => 'nullable|string',
        ]);

        $studentFeeMaster->update($validated);
        return $this->successResponse($studentFeeMaster, 'Student fee master updated successfully');
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        $studentFeeMaster = StudentFeeMaster::find($id);

        if (!$studentFeeMaster) {
            return $this->errorResponse('Student fee master not found', statusCode: 404);
        }

        $studentFeeMaster->delete();
        return $this->successResponse(message: 'Student fee master deleted successfully');
    }
}
