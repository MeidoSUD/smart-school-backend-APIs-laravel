<?php

namespace Modules\Academic\Http\Controllers\Api;

use Modules\Academic\Entities\Homework;
use Modules\Academic\Entities\SubmitAssignment;
use Modules\Academic\Entities\DailyAssignment;
use Modules\Academic\Entities\StudentSession;
use Modules\Academic\Entities\Student;
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
        $studentSession = $this->studentSession($request);

        if (!$studentSession) {
            return $this->errorResponse('Student session not found');
        }

        $homeworklist = Homework::where('class_id', $studentSession->class_id)
            ->where('section_id', $studentSession->section_id)
            ->where('submit_date', '>=', now()->toDateString())
            ->get();

        foreach ($homeworklist as $key => $homework) {
            $checkstatus = SubmitAssignment::where('homework_id', $homework->id)
                ->where('student_id', $studentSession->student_id)
                ->count();

            $homeworklist[$key]['status'] = $checkstatus > 0 ? 'submitted' : '';
        }

        $closedhomeworklist = Homework::where('class_id', $studentSession->class_id)
            ->where('section_id', $studentSession->section_id)
            ->where('submit_date', '<', now()->toDateString())
            ->get();

        foreach ($closedhomeworklist as $key => $homework) {
            $checkstatus = SubmitAssignment::where('homework_id', $homework->id)
                ->where('student_id', $studentSession->student_id)
                ->count();

            $closedhomeworklist[$key]['status'] = $checkstatus > 0 ? 'submitted' : '';
        }

        $data = [
            'created_by' => '',
            'evaluated_by' => '',
            'homeworklist' => $homeworklist,
            'closedhomeworklist' => $closedhomeworklist,
        ];

        return $this->successResponse($data);
    }

    public function upload_docs(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'homework_id' => 'required',
            'message' => 'required|string',
            'file' => 'nullable|file|max:10240',
        ]);

        $studentId = $this->resolvedStudentId($request);

        $homeworkId = $request->homework_id;

        $isRequired = SubmitAssignment::where('homework_id', $homeworkId)
            ->where('student_id', $studentId)
            ->count();

        if ($isRequired == 0 && !$request->hasFile('file')) {
            return $this->errorResponse('File is required');
        }

        $data = [
            'homework_id' => $homeworkId,
            'student_id' => $studentId,
            'message' => $request->message,
            'docs' => '',
            'file_name' => null,
        ];

        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $filename = time() . '_' . $file->getClientOriginalName();
            $file->move(public_path('uploads/homework/assignment'), $filename);
            $data['docs'] = $filename;
            $data['file_name'] = $file->getClientOriginalName();
        }

        SubmitAssignment::create($data);

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
        $studentSession = $this->studentSession($request);

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


}
