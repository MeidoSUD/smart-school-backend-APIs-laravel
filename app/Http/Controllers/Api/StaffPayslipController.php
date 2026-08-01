<?php

namespace App\Http\Controllers\Api;

use App\Models\StaffPayslip;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class StaffPayslipController extends \App\Http\Controllers\Api\Controller
{
    public function __construct()
    {
        $this->setControllerName('StaffPayslipController');
    }

    public function index(Request $request): JsonResponse
    {
        $query = StaffPayslip::with('staff');

        if ($request->staff_id) {
            $query->where('staff_id', $request->staff_id);
        }

        if ($request->month) {
            $query->where('month', $request->month);
        }

        if ($request->year) {
            $query->where('year', $request->year);
        }

        $payslips = $query->get();

        return $this->successResponse($payslips);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'staff_id' => 'required',
            'month' => 'required|string|max:255',
            'year' => 'required|integer',
            'basic_salary' => 'required|numeric',
            'total_allowance' => 'required|numeric',
            'total_deduction' => 'required|numeric',
            'net_salary' => 'required|numeric',
        ]);

        $payslip = StaffPayslip::create($validated);

        return $this->successResponse($payslip, 'Payslip created successfully');
    }

    public function show($id): JsonResponse
    {
        $payslip = StaffPayslip::with('staff')->find($id);

        if (!$payslip) {
            return $this->errorResponse('Payslip not found', null, 404);
        }

        return $this->successResponse($payslip);
    }

    public function update(Request $request, $id): JsonResponse
    {
        $payslip = StaffPayslip::find($id);

        if (!$payslip) {
            return $this->errorResponse('Payslip not found', null, 404);
        }

        $validated = $request->validate([
            'staff_id' => 'sometimes|required',
            'month' => 'sometimes|required|string|max:255',
            'year' => 'sometimes|required|integer',
            'basic_salary' => 'sometimes|required|numeric',
            'total_allowance' => 'sometimes|required|numeric',
            'total_deduction' => 'sometimes|required|numeric',
            'net_salary' => 'sometimes|required|numeric',
        ]);

        $payslip->update($validated);

        return $this->successResponse($payslip, 'Payslip updated successfully');
    }

    public function destroy($id): JsonResponse
    {
        $payslip = StaffPayslip::find($id);

        if (!$payslip) {
            return $this->errorResponse('Payslip not found', null, 404);
        }

        $payslip->delete();

        return $this->successResponse(null, 'Payslip deleted successfully');
    }
}
