<?php

namespace App\Http\Controllers\Api;

use App\Models\StaffPayroll;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class StaffPayrollController extends \App\Http\Controllers\Api\Controller
{
    public function __construct()
    {
        $this->setControllerName('StaffPayrollController');
    }

    public function index(Request $request): JsonResponse
    {
        $payroll = StaffPayroll::with('staff')->get();

        return $this->successResponse($payroll);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'staff_id' => 'required',
            'pay_scale' => 'required|string|max:255',
            'grade' => 'required|string|max:255',
        ]);

        $payroll = StaffPayroll::create($validated);

        return $this->successResponse($payroll, 'Payroll record created successfully');
    }

    public function show($id): JsonResponse
    {
        $payroll = StaffPayroll::with('staff')->find($id);

        if (!$payroll) {
            return $this->errorResponse('Payroll record not found', null, 404);
        }

        return $this->successResponse($payroll);
    }

    public function update(Request $request, $id): JsonResponse
    {
        $payroll = StaffPayroll::find($id);

        if (!$payroll) {
            return $this->errorResponse('Payroll record not found', null, 404);
        }

        $validated = $request->validate([
            'staff_id' => 'sometimes|required',
            'pay_scale' => 'sometimes|required|string|max:255',
            'grade' => 'sometimes|required|string|max:255',
        ]);

        $payroll->update($validated);

        return $this->successResponse($payroll, 'Payroll record updated successfully');
    }

    public function destroy($id): JsonResponse
    {
        $payroll = StaffPayroll::find($id);

        if (!$payroll) {
            return $this->errorResponse('Payroll record not found', null, 404);
        }

        $payroll->delete();

        return $this->successResponse(null, 'Payroll record deleted successfully');
    }
}
