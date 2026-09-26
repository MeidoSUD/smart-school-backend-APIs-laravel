<?php

declare(strict_types=1);

namespace Modules\Academic\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Modules\Academic\Entities\OnlineExamResult;
use Modules\Academic\Entities\Student;
use Modules\Academic\Entities\StudentSession;
use Modules\Academic\Http\Requests\StartOnlineExamRequest;
use Modules\Academic\Http\Requests\SubmitOnlineExamRequest;
use Modules\Academic\Http\Resources\OnlineExamDetailResource;
use Modules\Academic\Http\Resources\OnlineExamQuestionResource;
use Modules\Academic\Services\OnlineExamService;
use Modules\Core\Entities\Setting;
use Modules\Core\Http\Controllers\Api\Controller;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

final class OnlineExamController extends Controller
{
    public function __construct(private readonly OnlineExamService $exams)
    {
        $this->setControllerName('OnlineExamController');
    }

    public function index(Request $request): JsonResponse
    {
        $session = $this->getStudentSession($request->user());

        if (! $session instanceof StudentSession) {
            return $this->errorResponse('Student session not found', null, 404);
        }

        $exams = $this->exams->getExamsForSession($session);

        return $this->successResponse([
            'student' => Student::find($session->student_id),
            'examList' => $exams,
        ]);
    }

    public function closed(Request $request): JsonResponse
    {
        $session = $this->getStudentSession($request->user());

        if (! $session instanceof StudentSession) {
            return $this->errorResponse('Student session not found', null, 404);
        }

        $exams = $this->exams->getClosedExamsForSession($session);

        return $this->successResponse([
            'student' => Student::find($session->student_id),
            'examList' => $exams,
        ]);
    }

    public function downloadattachment(Request $request, $doc): JsonResponse|BinaryFileResponse
    {
        return $this->sendStoredFile($doc, 'uploads/onlinexam_images');
    }

    public function exam_detail(Request $request, int $id): JsonResponse
    {
        $session = $this->getStudentSession($request->user());

        if (! $session instanceof StudentSession) {
            return $this->errorResponse('Student session not found', null, 404);
        }

        $detail = $this->exams->getExamDetail($id, $session);

        $onlineExamStudent = $detail['onlineExamStudent'];

        $resource = new OnlineExamDetailResource([
            'exam' => $detail['exam'],
            'student' => $detail['student'],
            'questions' => $detail['questions'],
            'stats' => $detail['stats'],
            'publishResult' => $detail['publishResult'],
            'is_attempted' => (int) ($onlineExamStudent->is_attempted ?? 0),
            'rank' => (int) ($onlineExamStudent->rank ?? 0),
        ]);

        return $this->successResponse([
            'result' => $resource->toArray($request),
            // Keep legacy `questions` key but without answer-key leakage.
            'questions' => OnlineExamQuestionResource::collection($detail['questions'])
                ->each(fn (OnlineExamQuestionResource $r) => $r->showCorrect($detail['publishResult'])),
        ]);
    }

    public function startexam(StartOnlineExamRequest $request): JsonResponse
    {
        $session = $this->getStudentSession($request->user());

        if (! $session instanceof StudentSession) {
            return $this->errorResponse('Student session not found', null, 404);
        }

        // Mirrors CI getExamForm(): exam + questions + adjusted duration + question_status.
        $payload = $this->exams->startExam($request->examId(), $session);

        return $this->successResponse([
            'exam_id' => $request->examId(),
            'started' => true,
            'status' => 0,
            'exam' => $payload['exam'],
            'duration' => $payload['duration'],
            'question_status' => $payload['question_status'],
            'total_question' => $payload['total_question'],
            'onlineexam_student_id' => $payload['onlineexam_student_id'],
            'questions' => OnlineExamQuestionResource::collection($payload['questions']),
        ], 'Exam started successfully');
    }

    public function print(Request $request): JsonResponse
    {
        // Mirrors CI user/Onlineexam::print() (POST exam_id -> printable payload).
        $examId = (int) ($request->input('exam_id') ?? $request->input('onlineexam_id') ?? 0);
        if ($examId < 1) {
            return $this->errorResponse('Validation failed', ['exam_id' => ['The exam id field is required.']], 422);
        }
        $session = $this->getStudentSession($request->user());

        if (! $session instanceof StudentSession) {
            return $this->errorResponse('Student session not found', null, 404);
        }

        $detail = $this->exams->getExamDetail($examId, $session);

        return $this->successResponse([
            'exam' => $detail['exam'],
            'student' => $detail['student'],
            'questions' => OnlineExamQuestionResource::collection($detail['questions'])
                ->each(fn (OnlineExamQuestionResource $r) => $r->showCorrect($detail['publishResult'])),
            'stats' => $detail['stats'],
        ]);
    }

    public function submit(SubmitOnlineExamRequest $request): JsonResponse
    {
        $session = $this->getStudentSession($request->user());

        if (! $session instanceof StudentSession) {
            return $this->errorResponse('Student session not found', null, 404);
        }

        $examId = $request->examId();
        $this->exams->submitExam($examId, $request->answers(), $session);

        // Preserve legacy dual-write for the new-schema results table.
        $result = null;
        if (Schema::hasTable('online_exam_results')) {
            $result = OnlineExamResult::create([
                'online_exam_id' => $examId,
                'student_id' => $this->getStudentId($request->user()),
                'answers' => json_encode($request->answers()),
                'obtained_marks' => 0,
                'attended_on' => now(),
                'is_active' => 1,
            ]);
        }

        return $this->successResponse(
            ['result' => $result ?? ['exam_id' => $examId, 'submitted' => true]],
            'Exam submitted successfully'
        );
    }

    private function getStudentSession(mixed $user): ?StudentSession
    {
        $studentId = $this->getStudentId($user);

        if ($studentId === null) {
            return null;
        }

        $setting = Setting::where('is_active', 'yes')->first();

        return StudentSession::where('student_id', $studentId)
            ->when($setting, fn ($q) => $q->where('session_id', $setting->session_id))
            ->first();
    }

    private function getStudentId(mixed $user): ?int
    {
        if (! is_object($user) || ! isset($user->role)) {
            return null;
        }

        if ($user->role === 'student') {
            return isset($user->user_id) ? (int) $user->user_id : null;
        }

        if ($user->role === 'parent') {
            $student = Student::where('parent_id', $user->id)->first();

            return $student ? (int) $student->id : null;
        }

        return null;
    }
}
