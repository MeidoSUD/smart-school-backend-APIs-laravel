<?php

namespace Modules\Academic\Http\Controllers\Api;

use Modules\Academic\Entities\ApplyLeave;
use Modules\Academic\Entities\StudentSession;
use Modules\Academic\Entities\Student;
use Modules\Core\Entities\Setting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Carbon\Carbon;
use DB;

class ApplyLeaveController extends \Modules\Core\Http\Controllers\Api\Controller
{
    public function __construct()
    {
        $this->setControllerName('ApplyLeaveController');
    }

    public function index(Request $request): JsonResponse
    {
        $studentSession = $this->studentSession($request);

        if (!$studentSession) {
            return $this->errorResponse('Student session not found');
        }

        $studentId = $this->resolvedStudentId($request);
        $student = Student::find($studentId);

        $results = ApplyLeave::where('student_session_id', $studentSession->id)
            ->orderBy('apply_date', 'desc')
            ->get();

        $studentClasses = StudentSession::where('student_id', $studentId)->with(['class', 'section'])->get();

        $data = [
            'results' => $results,
            'studentclasses' => $studentClasses,
        ];

        return $this->successResponse($data);
    }

    public function get_details($id): JsonResponse
    {
        $data = ApplyLeave::find($id);

        if (!$data) {
            return $this->errorResponse('Leave not found', null, 404);
        }

        $data->from_date = Carbon::parse($data->from_date)->format('d-m-Y');
        $data->to_date = Carbon::parse($data->to_date)->format('d-m-Y');
        $data->apply_date = Carbon::parse($data->apply_date)->format('d-m-Y');

        return $this->successResponse($data);
    }

    public function add(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'apply_date' => 'required',
            'from_date' => 'required',
            'to_date' => 'required',
            'message' => 'required|string',
        ]);

        $studentSession = $this->studentSession($request);

        if (!$studentSession) {
            return $this->errorResponse('Student session not found');
        }

        $data = [
            'apply_date' => Carbon::parse($request->apply_date)->format('Y-m-d'),
            'from_date' => Carbon::parse($request->from_date)->format('Y-m-d'),
            'to_date' => Carbon::parse($request->to_date)->format('Y-m-d'),
            'student_session_id' => $studentSession->id,
            'reason' => $request->message,
        ];

        $leaveId = $request->leave_id;

        if ($leaveId) {
            $data['id'] = $leaveId;
            ApplyLeave::where('id', $leaveId)->update($data);
        } else {
            $leave = ApplyLeave::create($data);
            $leaveId = $leave->id;
        }

        $document = null;
        if ($request->hasFile('files')) {
            $file = $request->file('files')[0];
            $document = time() . '_' . $file->getClientOriginalName();
            $file->move(public_path('uploads/student_leavedocuments'), $document);
            ApplyLeave::where('id', $leaveId)->update(['docs' => $document]);
        }

        return $this->successResponse(['leave_id' => $leaveId], 'Leave application submitted successfully');
    }

    public function remove_leave($id): JsonResponse
    {
        $row = ApplyLeave::find($id);

        if ($row && $row->docs) {
            $filePath = public_path('uploads/student_leavedocuments/' . $row->docs);
            if (file_exists($filePath)) {
                unlink($filePath);
            }
        }

        ApplyLeave::destroy($id);

        return $this->successResponse(null, 'Leave removed successfully');
    }


}
