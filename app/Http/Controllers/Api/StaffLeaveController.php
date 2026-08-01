<?php

namespace App\Http\Controllers\Api;

use App\Models\StaffLeaveRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class StaffLeaveController extends \App\Http\Controllers\Api\Controller
{
    public function __construct()
    {
        $this->setControllerName('StaffLeaveController');
    }

    public function index(Request $request): JsonResponse
    {
        $query = StaffLeaveRequest::with('staff');

        if ($request->staff_id) {
            $query->where('staff_id', $request->staff_id);
        }

        if ($request->status) {
            $query->where('status', $request->status);
        }

        $leaves = $query->get();

        return $this->successResponse($leaves);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'staff_id' => 'required',
            'leave_type_id' => 'required',
            'leave_from' => 'required|date',
            'leave_to' => 'required|date|after_or_equal:leave_from',
            'reason' => 'required|string',
        ]);

        $leave = StaffLeaveRequest::create([
            'staff_id' => $validated['staff_id'],
            'leave_type_id' => $validated['leave_type_id'],
            'date_from' => $validated['leave_from'],
            'date_to' => $validated['leave_to'],
            'reason' => $validated['reason'],
            'applied_on' => now(),
        ]);

        return $this->successResponse($leave, 'Leave request submitted successfully');
    }

    public function show($id): JsonResponse
    {
        $leave = StaffLeaveRequest::with('staff')->find($id);

        if (!$leave) {
            return $this->errorResponse('Leave request not found', null, 404);
        }

        return $this->successResponse($leave);
    }

    public function update(Request $request, $id): JsonResponse
    {
        $leave = StaffLeaveRequest::find($id);

        if (!$leave) {
            return $this->errorResponse('Leave request not found', null, 404);
        }

        $validated = $request->validate([
            'is_approved' => 'required|boolean',
        ]);

        $leave->update([
            'status' => $validated['is_approved'] ? 1 : 0,
            'approved_by' => $request->user()->id ?? null,
        ]);

        return $this->successResponse($leave, 'Leave request updated successfully');
    }

    public function destroy($id): JsonResponse
    {
        $leave = StaffLeaveRequest::find($id);

        if (!$leave) {
            return $this->errorResponse('Leave request not found', null, 404);
        }

        $leave->delete();

        return $this->successResponse(null, 'Leave request deleted successfully');
    }
}
