<?php

namespace Modules\Academic\Http\Controllers\Api;

use Modules\Academic\Entities\Homework;
use Modules\Academic\Entities\HomeworkEvaluation;
use Modules\Academic\Entities\SubmitAssignment;
use Modules\Academic\Entities\DailyAssignment;
use Modules\Academic\Entities\Student;
use Modules\Academic\Http\Requests\HomeworkRequest;
use Modules\Academic\Http\Requests\DailyAssignmentRequest;
use Modules\Core\Services\StudentSessionService;
use Modules\Core\Entities\Setting;
use Modules\Staff\Entities\Staff;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use DB;

class HomeworkController extends \Modules\Core\Http\Controllers\Api\Controller
{
    public function __construct(
        private readonly StudentSessionService $studentSessionService
    ) {
        $this->setControllerName('HomeworkController');
    }

    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $studentSession = $this->studentSessionService->getStudentSession($user);

        if (!$studentSession) {
            return $this->errorResponse('Student session not found');
        }

        $studentId = $studentSession->student_id;

        $mapHomework = function ($homework) {
            $evaluation = $homework->homeworkEvaluations->first();
            $homework->homework_evaluation_id = $evaluation ? $evaluation->id : 0;
            $homework->evaluation_marks = $evaluation ? $evaluation->marks : null;
            $homework->note = $evaluation ? $evaluation->note : '';
            
            $homework->status = $homework->submission_status > 0 ? 'submitted' : '';

            $homework->class = $homework->class->class ?? '';
            $homework->section = $homework->section->section ?? '';
            $homework->subject_name = $homework->subject->name ?? '';
            $homework->subject_code = $homework->subject->code ?? '';

            $homework->makeHidden(['class', 'section', 'subject', 'homeworkEvaluations']);
            
            return $homework;
        };

        $homeworklist = Homework::where('class_id', $studentSession->class_id)
            ->where('section_id', $studentSession->section_id)
            ->where('submit_date', '>=', now()->toDateString())
            ->with(['class', 'section', 'subject', 'homeworkEvaluations' => function ($q) use ($studentId) {
                $q->where('student_id', $studentId);
            }])
            ->withCount(['submitAssignments as submission_status' => function ($query) use ($studentId) {
                $query->where('student_id', $studentId);
            }])
            ->orderBy('homework_date', 'desc')
            ->get()
            ->map($mapHomework);

        $closedhomeworklist = Homework::where('class_id', $studentSession->class_id)
            ->where('section_id', $studentSession->section_id)
            ->where('submit_date', '<', now()->toDateString())
            ->with(['class', 'section', 'subject', 'homeworkEvaluations' => function ($q) use ($studentId) {
                $q->where('student_id', $studentId);
            }])
            ->withCount(['submitAssignments as submission_status' => function ($query) use ($studentId) {
                $query->where('student_id', $studentId);
            }])
            ->orderBy('homework_date', 'desc')
            ->get()
            ->map($mapHomework);

        $data = [
            'created_by' => '',
            'evaluated_by' => '',
            'homeworklist' => $homeworklist,
            'closedhomeworklist' => $closedhomeworklist,
        ];

        return $this->successResponse($data);
    }

    public function upload_docs(HomeworkRequest $request): JsonResponse
    {
        $user = $request->user();
        $studentId = $this->studentSessionService->getStudentId($user);
        $homeworkId = $request->homework_id;

        $submissionExists = SubmitAssignment::where('homework_id', $homeworkId)
            ->where('student_id', $studentId)
            ->exists();

        if (!$submissionExists && !$request->hasFile('file')) {
            return $this->errorResponse('File is required');
        }

        $data = [
            'homework_id' => $homeworkId,
            'student_id' => $studentId,
            'message' => $request->message,
            'docs' => '',
            'file_name' => null,
        ];

        DB::transaction(function () use ($request, &$data) {
            if ($request->hasFile('file')) {
                $file = $request->file('file');
                $filename = time() . '_' . bin2hex(random_bytes(16)) . '.' . $file->getClientOriginalExtension();
                $file->storeAs('uploads/homework/assignment', $filename, 'local');
                $data['docs'] = $filename;
                $data['file_name'] = $file->getClientOriginalName();
            }

            SubmitAssignment::create($data);
        });

        return $this->successResponse(null, 'Homework submitted successfully');
    }

    public function homework_detail($id, $status, Request $request): JsonResponse
    {
        $result = Homework::find($id);

        if (!$result) {
            return $this->errorResponse('Homework not found', null, 404);
        }

        $user = $request->user();
        $studentId = $this->studentSessionService->getStudentId($user);

        $setting = Setting::first();
        $superadminRestriction = $setting ? ($setting->superadmin_restriction ?? false) : false;

        $classId = $result->class_id;
        $sectionId = $result->section_id;

        $studentlist = Student::whereHas('studentSessions', function ($q) use ($classId, $sectionId) {
            $q->where('class_id', $classId)->where('section_id', $sectionId);
        })->get();

        $report = HomeworkEvaluation::where('homework_id', $id)
            ->where('student_id', $studentId)
            ->first();

        $homeworkdocs = SubmitAssignment::where('homework_id', $id)
            ->where('student_id', $studentId)
            ->get();

        $created_by = '';
        $evaluated_by = '';

        $createData = Staff::find($result->created_by);
        if ($createData && ($superadminRestriction != 'disabled' || $createData->role_id != 7)) {
            $created_by = ($createData->surname ? $createData->name . ' ' . $createData->surname : $createData->name) . ' (' . $createData->employee_id . ')';
        }

        if ($result->evaluated_by) {
            $evalData = Staff::find($result->evaluated_by);
            if ($evalData && ($superadminRestriction != 'disabled' || $evalData->role_id != 7)) {
                $evaluated_by = ($evalData->surname ? $evalData->name . ' ' . $evalData->surname : $evalData->name) . ' (' . $evalData->employee_id . ')';
            }
        }

        $checkstatus = SubmitAssignment::where('homework_id', $id)
            ->where('student_id', $studentId)
            ->count();
        $homeworkStatus = $checkstatus > 0 ? 'submitted' : '';

        $data = [
            'homework_status' => $status,
            'homework_id' => $id,
            'title' => 'Homework Evaluation',
            'result' => $result,
            'studentlist' => $studentlist,
            'report' => $report,
            'homeworkdocs' => $homeworkdocs,
            'created_by' => $created_by,
            'evaluated_by' => $evaluated_by,
            'status' => $homeworkStatus,
        ];

        return $this->successResponse($data);
    }

    public function download(Request $request, $id): JsonResponse|BinaryFileResponse
    {
        $homework = Homework::find($id);

        if (! $homework) {
            return $this->errorResponse('Homework not found', null, 404);
        }

        return $this->sendStoredFile($homework->document, 'uploads/homework/document', 'uploads/homework');
    }

    public function assigmnetDownload(Request $request, $id): JsonResponse|BinaryFileResponse
    {
        $assignment = SubmitAssignment::find($id);

        if (! $assignment) {
            return $this->errorResponse('Assignment not found', null, 404);
        }

        $user = $request->user();
        $studentId = $this->studentSessionService->getStudentId($user);

        if (! $studentId || (int) $assignment->student_id !== (int) $studentId) {
            return $this->errorResponse('Unauthorized', null, 403);
        }

        return $this->sendStoredFile($assignment->docs, 'uploads/homework/assignment');
    }

    public function dailyassignment(Request $request): JsonResponse
    {
        $user = $request->user();
        $studentSession = $this->studentSessionService->getStudentSession($user);

        if (!$studentSession) {
            return $this->errorResponse('Student session not found');
        }

        $studentId = $this->studentSessionService->getStudentId($user);

        // Fetching data with joins matching CodeIgniter behavior
        $dailyassignmentlist = DB::table('daily_assignment')
            ->select('daily_assignment.*', 'subjects.name as subject_name', 'subjects.code as subject_code')
            ->leftJoin('student_session', 'student_session.id', '=', 'daily_assignment.student_session_id')
            ->leftJoin('subject_group_subjects', 'subject_group_subjects.id', '=', 'daily_assignment.subject_group_subject_id')
            ->join('subjects', 'subjects.id', '=', 'subject_group_subjects.subject_id')
            ->where('daily_assignment.student_session_id', $studentSession->id)
            ->orWhere('student_session.student_id', $studentId)
            ->orderBy('daily_assignment.id', 'desc')
            ->get();

        $data = [
            'dailyassignmentlist' => $dailyassignmentlist,
        ];

        return $this->successResponse($data);
    }

    public function getsingledailyassignment($id, Request $request): JsonResponse
    {
        $user = $request->user();
        $studentSession = $this->studentSessionService->getStudentSession($user);

        if (!$studentSession) {
            return $this->errorResponse('Student session not found');
        }

        $assignment = DB::table('daily_assignment')
            ->select('daily_assignment.*', 'subjects.name as subject_name', 'subjects.code as subject_code')
            ->leftJoin('subject_group_subjects', 'subject_group_subjects.id', '=', 'daily_assignment.subject_group_subject_id')
            ->join('subjects', 'subjects.id', '=', 'subject_group_subjects.subject_id')
            ->where('daily_assignment.id', $id)
            ->where('daily_assignment.student_session_id', $studentSession->id)
            ->first();

        if (!$assignment) {
            return $this->errorResponse('Assignment not found', null, 404);
        }

        return $this->successResponse(['singleassignmentlist' => $assignment]);
    }

    public function createdailyassignment(DailyAssignmentRequest $request): JsonResponse
    {
        $user = $request->user();
        $studentSession = $this->studentSessionService->getStudentSession($user);

        if (!$studentSession) {
            return $this->errorResponse('Student session not found');
        }

        $data = [
            'title' => $request->title,
            'student_session_id' => $studentSession->id,
            'description' => $request->description,
            'subject_group_subject_id' => $request->subject,
            'date' => date('Y-m-d'),
            'evaluated_by' => null,
            'attachment' => null,
            'remark' => ''
        ];

        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $filename = time() . '_' . bin2hex(random_bytes(16)) . '.' . $file->getClientOriginalExtension();
            $file->storeAs('uploads/homework/daily_assignment', $filename, 'local');
            $data['attachment'] = $filename;
        }

        DailyAssignment::create($data);

        return $this->successResponse(null, 'Record Saved Successfully');
    }

    public function updatedailyassignment(DailyAssignmentRequest $request): JsonResponse
    {
        $user = $request->user();
        $studentSession = $this->studentSessionService->getStudentSession($user);

        if (!$studentSession) {
            return $this->errorResponse('Student session not found');
        }

        $assignment = DailyAssignment::find($request->assigment_id);
        if (!$assignment) {
            return $this->errorResponse('Assignment not found', null, 404);
        }

        $data = [
            'title' => $request->title,
            'student_session_id' => $studentSession->id,
            'description' => $request->description,
            'subject_group_subject_id' => $request->subject,
            'date' => date('Y-m-d'),
        ];

        if ($request->hasFile('file')) {
            if ($assignment->attachment) {
                Storage::disk('local')->delete('uploads/homework/daily_assignment/' . $assignment->attachment);
            }
            $file = $request->file('file');
            $filename = time() . '_' . bin2hex(random_bytes(16)) . '.' . $file->getClientOriginalExtension();
            $file->storeAs('uploads/homework/daily_assignment', $filename, 'local');
            $data['attachment'] = $filename;
        }

        $assignment->update($data);

        return $this->successResponse(null, 'Record Updated Successfully');
    }

    public function deletedailyassignment($id): JsonResponse
    {
        $assignment = DailyAssignment::find($id);

        if (!$assignment) {
            return $this->errorResponse('Assignment not found', null, 404);
        }

        if ($assignment->attachment) {
            Storage::disk('local')->delete('uploads/homework/daily_assignment/' . $assignment->attachment);
        }

        $assignment->delete();

        return $this->successResponse(null, 'Record Deleted Successfully');
    }

    public function dailyassigmnetdownload($id)
    {
        $assignment = DailyAssignment::find($id);

        if (!$assignment || !$assignment->attachment) {
            return $this->errorResponse('File not found', null, 404);
        }

        $path = storage_path('app/uploads/homework/daily_assignment/' . $assignment->attachment);
        if (!file_exists($path)) {
            return $this->errorResponse('File not found', null, 404);
        }

        return response()->download($path);
    }
}
