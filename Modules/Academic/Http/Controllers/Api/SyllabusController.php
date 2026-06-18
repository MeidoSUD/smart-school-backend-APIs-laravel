<?php

namespace Modules\Academic\Http\Controllers\Api;

use Modules\Academic\Entities\Syllabus;
use Modules\Academic\Entities\SyllabusMessage;
use Modules\Academic\Entities\StudentSession;
use Modules\Academic\Entities\Student;
use Modules\Academic\Services\SyllabusService;
use Modules\Academic\Http\Requests\SyllabusMessageRequest;
use Modules\Core\Entities\Setting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class SyllabusController extends \Modules\Core\Http\Controllers\Api\Controller
{
    public function __construct(
        private readonly SyllabusService $syllabusService
    ) {
        $this->setControllerName('SyllabusController');
    }

    public function index(): JsonResponse
    {
        $startWeekday = Setting::first()->start_month ?? 4;
        $monday = Carbon::now()->startOfMonth()->startOfWeek();

        $data = [
            'this_week_start' => $monday->format('Y-m-d'),
            'this_week_end' => $monday->copy()->addDays(6)->format('Y-m-d'),
        ];

        return $this->successResponse($data);
    }

    public function status(Request $request): JsonResponse
    {
        $user = $request->user();
        $studentSession = $this->getStudentSession($user);

        if (!$studentSession) {
            return $this->errorResponse('Student session not found');
        }

        $subjects = Syllabus::getMySubjects(
            $studentSession->class_id,
            $studentSession->section_id,
            $studentSession->session_id
        );

        $subjectsData = $this->syllabusService->buildSubjectStatusData($subjects);

        return $this->successResponse([
            'subjects_data' => $subjectsData,
            'status' => ['1' => 'Complete', '0' => 'Incomplete'],
        ]);
    }

    public function download($id): JsonResponse
    {
        $result = Syllabus::find($id);

        if (!$result) {
            return $this->errorResponse('Syllabus not found', null, 404);
        }

        return $this->successResponse(['attachment' => $result->attachment]);
    }

    public function addmessage(SyllabusMessageRequest $request): JsonResponse
    {
        $user = $request->user();
        $studentId = $this->getStudentId($user);

        DB::transaction(function () use ($request, $studentId) {
            SyllabusMessage::create([
                'subject_syllabus_id' => $request->syllabus_id,
                'type' => 'student',
                'student_id' => $studentId,
                'message' => $request->message,
                'created_date' => now(),
            ]);
        });

        return $this->successResponse(null, 'Message added successfully');
    }

    public function getmessage(Request $request): JsonResponse
    {
        $subjectSyllabusId = $request->syllabus_id;

        $messageList = SyllabusMessage::where('subject_syllabus_id', $subjectSyllabusId)->get();

        return $this->successResponse(['messagelist' => $messageList]);
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
