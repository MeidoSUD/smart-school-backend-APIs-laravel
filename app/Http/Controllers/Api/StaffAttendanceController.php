<?php

namespace App\Http\Controllers\Api;

use App\Models\StaffAttendance;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class StaffAttendanceController extends \App\Http\Controllers\Api\Controller
{
    public function __construct()
    {
        $this->setControllerName('StaffAttendanceController');
    }

    public function index(Request $request): JsonResponse
    {
        $query = StaffAttendance::query();

        if ($request->staff_id) {
            $query->where('staff_id', $request->staff_id);
        }

        if ($request->date) {
            $query->where('date', $request->date);
        }

        if ($request->month && $request->year) {
            $query->whereMonth('date', $request->month)
                ->whereYear('date', $request->year);
        }

        $attendance = $query->get();

        return $this->successResponse($attendance);
    }

    public function store(Request $request): JsonResponse
    {
        if ($request->has('records') && is_array($request->records)) {
            $validated = $request->validate([
                'records' => 'required|array',
                'records.*.staff_id' => 'required',
                'records.*.staff_attendance_type_id' => 'required',
                'records.*.attendance_date' => 'required|date',
            ]);

            $created = [];
            foreach ($request->records as $record) {
                $created[] = StaffAttendance::create([
                    'staff_id' => $record['staff_id'],
                    'attendence_type' => $record['staff_attendance_type_id'],
                    'date' => $record['attendance_date'],
                ]);
            }

            return $this->successResponse($created, 'Attendance records created successfully');
        }

        $validated = $request->validate([
            'staff_id' => 'required',
            'staff_attendance_type_id' => 'required',
            'attendance_date' => 'required|date',
        ]);

        $attendance = StaffAttendance::create([
            'staff_id' => $validated['staff_id'],
            'attendence_type' => $validated['staff_attendance_type_id'],
            'date' => $validated['attendance_date'],
        ]);

        return $this->successResponse($attendance, 'Attendance recorded successfully');
    }

    public function show($id): JsonResponse
    {
        $attendance = StaffAttendance::find($id);

        if (!$attendance) {
            return $this->errorResponse('Attendance record not found', null, 404);
        }

        return $this->successResponse($attendance);
    }

    public function update(Request $request, $id): JsonResponse
    {
        $attendance = StaffAttendance::find($id);

        if (!$attendance) {
            return $this->errorResponse('Attendance record not found', null, 404);
        }

        $validated = $request->validate([
            'staff_id' => 'sometimes|required',
            'staff_attendance_type_id' => 'sometimes|required',
            'attendance_date' => 'sometimes|required|date',
        ]);

        if (isset($validated['staff_attendance_type_id'])) {
            $validated['attendence_type'] = $validated['staff_attendance_type_id'];
            unset($validated['staff_attendance_type_id']);
        }
        if (isset($validated['attendance_date'])) {
            $validated['date'] = $validated['attendance_date'];
            unset($validated['attendance_date']);
        }

        $attendance->update($validated);

        return $this->successResponse($attendance, 'Attendance updated successfully');
    }

    public function destroy($id): JsonResponse
    {
        $attendance = StaffAttendance::find($id);

        if (!$attendance) {
            return $this->errorResponse('Attendance record not found', null, 404);
        }

        $attendance->delete();

        return $this->successResponse(null, 'Attendance record deleted successfully');
    }
}
