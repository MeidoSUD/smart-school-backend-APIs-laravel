<?php

namespace Modules\Academic\Http\Controllers\Api;

use Modules\Academic\Entities\Homework;
use Modules\Academic\Entities\SubmitAssignment;
use Modules\Academic\Entities\DailyAssignment;
use Modules\Academic\Entities\StudentSession;
use Modules\Academic\Entities\Student;
use Modules\Academic\Http\Requests\HomeworkRequest;
use Modules\Staff\Entities\Staff;
use Modules\Core\Entities\Setting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use DB;

class HomeworkController extends \Modules\Core\Http\Controllers\Api\Controller
{
    public function __construct()
    {
        $this->setControllerName('HomeworkController');
    }

    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $studentSession = $this->getStudentSession($user);

        if (!$studentSession) {
            return $this->errorResponse('Student session not found');
        }

        $studentId = $studentSession->student_id;

        $homeworklist = Homework::where('class_id', $studentSession->class_id)
            ->where('section_id', $studentSession->section_id)
            ->where('submit_date', '>=', now()->toDateString())
            ->withCount(['submitAssignments as submission_status' => function ($query) use ($studentId) {
                $query->where('student_id', $studentId);
            }])
            ->get()
            ->map(function ($homework) {
                $homework->status = $homework->submission_status > 0 ? 'submitted' : '';
                return $homework;
            });

        $closedhomeworklist = Homework::where('class_id', $studentSession->class_id)
            ->where('section_id', $studentSession->section_id)
            ->where('submit_date', '<', now()->toDateString())
            ->withCount(['submitAssignments as submission_status' => function ($query) use ($studentId) {
                $query->where('student_id', $studentId);
            }])
            ->get()
            ->map(function ($homework) {
                $homework->status = $homework->submission_status > 0 ? 'submitted' : '';
                return $homework;
            });

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
        $studentId = $this->getStudentId($user);
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
                $filename = time() . '_' . $file->getClientOriginalName();
                $file->move(public_path('uploads/homework/assignment'), $filename);
                $data['docs'] = $filename;
                $data['file_name'] = $file->getClientOriginalName();
            }

            SubmitAssignment::create($data);
        });

        return $this->successResponse(null, 'Homework submitted successfully');
    }

    public function homework_detail($id, $status): JsonResponse
    {
        $result = Homework::find($id);

        if (!$result) {
            return $this->errorResponse('Homework not found', null, 404);
        }

        $setting = Setting::first();
        $superadminRestriction = $setting ? ($setting->superadmin_restriction ?? false) : false;

        $classId = $result->class_id;
        $sectionId = $result->section_id;

        $data = [
            'homework_status' => $status,
            'homework_id' => $id,
            'title' => 'Homework Evaluation',
            'result' => $result,
        ];

        return $this->successResponse($data);
    }

    public function download($id): JsonResponse
    {
        $homework = Homework::find($id);

        if (!$homework) {
            return $this->errorResponse('Homework not found', null, 404);
        }

        return $this->successResponse(['document' => $homework->document]);
    }

    public function dailyassignment(Request $request): JsonResponse
    {
        $user = $request->user();
        $studentSession = $this->getStudentSession($user);

        if (!$studentSession) {
            return $this->errorResponse('Student session not found');
        }

        $dailyassignmentlist = DailyAssignment::where('student_session_id', $studentSession->id)
            ->orderBy('date', 'desc')
            ->get();

        $data = [
            'dailyassignmentlist' => $dailyassignmentlist,
        ];

        return $this->successResponse($data);
    }

    private function getStudentSession($user)
    {
        $studentId = $this->getStudentId($user);

        if (!$studentId) {
            return null;
        }

        $setting = Setting::where('is_active', 1)->first();

        return StudentSession::where('student_id', $studentId)
            ->when($setting, fn($q) => $q->where('session_id', $setting->id))
            ->first();
    }

    private function getStudentId($user)
    {
        if ($user->role === 'student') {
            return $user->user_id;
        } elseif ($user->role === 'parent') {
            $student = Student::where('parent_id', $user->id)->first();
            return $student ? $student->id : null;
        }

        return null;
    }
}
