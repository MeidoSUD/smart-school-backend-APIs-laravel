<?php

namespace Modules\Academic\Http\Controllers\Api;

use Modules\Academic\Entities\ApplyLeave;
use Modules\Academic\Entities\StudentSession;
use Modules\Academic\Entities\Student;
use Modules\Academic\Http\Requests\ApplyLeaveRequest;
use Modules\Core\Services\StudentSessionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ApplyLeaveController extends \Modules\Core\Http\Controllers\Api\Controller
{
    public function __construct(
        private readonly StudentSessionService $studentSessionService
    ) {
        $this->setControllerName('ApplyLeaveController');
    }

    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $studentSession = $this->studentSessionService->getStudentSession($user);

        if (!$studentSession) {
            return $this->errorResponse('Student session not found');
        }

        $studentId = $this->studentSessionService->getStudentId($user);

        $results = ApplyLeave::where('student_session_id', $studentSession->id)
            ->with(['studentSession.class', 'studentSession.section', 'staff'])
            ->orderBy('id', 'desc')
            ->get()
            ->map(function ($item) {
                $array = $item->toArray();
                $array['class'] = $item->studentSession->class->class ?? '';
                $array['section'] = $item->studentSession->section->section ?? '';
                $array['staff_name'] = $item->staff->name ?? '';
                $array['surname'] = $item->staff->surname ?? '';
                return $array;
            });

        $studentClasses = StudentSession::where('student_id', $studentId)->with(['class', 'section'])->get();

        $data = [
            'results' => $results,
            'studentclasses' => $studentClasses,
        ];

        return $this->successResponse($data);
    }

    public function get_details($id, Request $request): JsonResponse
    {
        $user = $request->user();
        $studentSession = $this->studentSessionService->getStudentSession($user);

        if (!$studentSession) {
            return $this->errorResponse('Student session not found');
        }

        $leave = ApplyLeave::where('id', $id)
            ->where('student_session_id', $studentSession->id)
            ->first();

        if (!$leave) {
            return $this->errorResponse('Leave not found', null, 404);
        }

        $data = $leave->toArray();
        $data['from_date'] = $leave->from_date ? Carbon::parse($leave->from_date)->format('d-m-Y') : '';
        $data['to_date'] = $leave->to_date ? Carbon::parse($leave->to_date)->format('d-m-Y') : '';
        $data['apply_date'] = $leave->apply_date ? Carbon::parse($leave->apply_date)->format('d-m-Y') : '';

        return $this->successResponse($data);
    }

    public function add(ApplyLeaveRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $studentSession = $this->studentSessionService->getStudentSession($request->user());

        if (!$studentSession) {
            return $this->errorResponse('Student session not found');
        }

        $leaveId = $this->storeLeave($validated, $studentSession->id);

        $this->uploadLeaveDocument($request, $leaveId);

        return response()->json([
            'status' => 'success',
            'error' => '',
            'message' => 'Leave application submitted successfully',
            'leave_id' => $leaveId,
        ]);
    }

    private function storeLeave(array $input, int $studentSessionId): mixed
    {
        $data = [
            'apply_date' => Carbon::parse($input['apply_date'])->format('Y-m-d'),
            'from_date' => Carbon::parse($input['from_date'])->format('Y-m-d'),
            'to_date' => Carbon::parse($input['to_date'])->format('Y-m-d'),
            'student_session_id' => $studentSessionId,
            'reason' => $input['reason'],
        ];

        if (!empty($input['leave_id'])) {
            ApplyLeave::where('id', $input['leave_id'])->update($data);

            return $input['leave_id'];
        }

        $data['status'] = 0;
        $data['request_type'] = 0;

        return ApplyLeave::create($data)->id;
    }

    private function uploadLeaveDocument(Request $request, mixed $leaveId): void
    {
        $file = $request->file('docs')
            ?? $request->file('files')
            ?? $request->file('file')
            ?? $request->file('document');

        if (!$file) {
            return;
        }
        $fileToUpload = is_array($file) ? $file[0] : $file;

        if (!$fileToUpload || !$fileToUpload->isValid()) {
            return;
        }

        $originalName = $fileToUpload->getClientOriginalName();
        $document = time() . '-' . uniqid(rand(), true) . '!' . $originalName;
        $destinationPath = public_path('uploads/student_leavedocuments');

        if (!file_exists($destinationPath)) {
            mkdir($destinationPath, 0755, true);
        }

        $fileToUpload->move($destinationPath, $document);

        ApplyLeave::where('id', $leaveId)->update(['docs' => $document]);
    }

    public function remove_leave($id): JsonResponse
    {
        $row = ApplyLeave::find($id);

        if ($row && $row->docs) {
            $filePath = public_path('uploads/student_leavedocuments/' . $row->docs);
            if (file_exists($filePath)) {
                @unlink($filePath);
            }
        }

        if ($row) {
            $row->delete();
        }

        return $this->successResponse(null, 'Leave removed successfully');
    }

    public function download($id): JsonResponse|BinaryFileResponse
    {
        $row = ApplyLeave::find($id);

        if (!$row || !$row->docs) {
            return $this->errorResponse('Document not found', null, 404);
        }

        $filePath = public_path('uploads/student_leavedocuments/' . $row->docs);

        if (!file_exists($filePath)) {
            return $this->errorResponse('File does not exist on server', null, 404);
        }

        return response()->download($filePath, $row->docs);
    }
}
