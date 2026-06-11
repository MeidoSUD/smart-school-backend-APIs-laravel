<?php

namespace Modules\Academic\Http\Controllers\Api;

use Modules\Academic\Entities\Syllabus;
use Modules\Academic\Entities\SyllabusMessage;
use Modules\Academic\Entities\StudentSession;
use Modules\Academic\Entities\Student;
use Modules\Core\Entities\Setting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Carbon\Carbon;

class SyllabusController extends \Modules\Core\Http\Controllers\Api\Controller
{
    public function __construct()
    {
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
        $studentSession = $this->studentSession($request);

        if (!$studentSession) {
            return $this->errorResponse('Student session not found');
        }

        $subjects = Syllabus::getMySubjects(
            $studentSession->class_id,
            $studentSession->section_id,
            $studentSession->session_id
        );

        $subjectsData = [];
        foreach ($subjects as $subject) {
            $subjectDetails = Syllabus::getSubjectStatus(
                $subject->subject_group_subjects_id,
                $subject->subject_group_class_sections_id
            );

            $label = $subject->code ? ' (' . $subject->code . ')' : '';
            $total = $subjectDetails->total ?? 0;
            $completePercent = 0;
            $incompletePercent = 0;

            if ($total > 0) {
                $completePercent = round(($subjectDetails->complete / $total) * 100);
                $incompletePercent = round(($subjectDetails->incomplete / $total) * 100);
            }

            $lessonSummary = [];
            $syllabusReport = Syllabus::getSubjectSyllabusReport(
                $subject->subject_group_subjects_id,
                $subject->subject_group_class_sections_id
            );

            foreach ($syllabusReport as $lesson) {
                $topics = Syllabus::getTopicsByLessonId($lesson->id);
                $topicComplete = $topics->where('status', 1)->count();
                $totalTopics = $topics->count();

                $lessonSummary[] = [
                    'name' => $lesson->name,
                    'topics' => $topics->map(fn ($topic) => [
                        'name' => $topic->name,
                        'status' => $topic->status,
                        'complete_date' => $topic->complete_date,
                    ])->values(),
                    'incomplete_percent' => $totalTopics > 0 ? round((($totalTopics - $topicComplete) / $totalTopics) * 100) : 0,
                    'complete_percent' => $totalTopics > 0 ? round(($topicComplete / $totalTopics) * 100) : 0,
                ];
            }

            $subjectsData[$subject->subject_group_subjects_id] = [
                'lebel' => $subject->name . $label,
                'complete' => $completePercent,
                'incomplete' => $incompletePercent,
                'id' => $subject->subject_group_subjects_id . '_' . $subject->code,
                'total' => $total,
                'name' => $subject->name,
                'graph_id' => $subject->subject_group_subjects_id . time(),
                'lesson_summary' => $lessonSummary,
            ];
        }

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

    public function addmessage(Request $request): JsonResponse
    {
        $request->validate([
            'syllabus_id' => 'required',
            'message' => 'required|string',
        ]);

        $studentId = $this->resolvedStudentId($request);

        SyllabusMessage::create([
            'subject_syllabus_id' => $request->syllabus_id,
            'type' => 'student',
            'student_id' => $studentId,
            'message' => $request->message,
            'created_date' => now(),
        ]);

        return $this->successResponse(null, 'Message added successfully');
    }

    public function getmessage(Request $request): JsonResponse
    {
        $subjectSyllabusId = $request->syllabus_id;

        $messageList = SyllabusMessage::where('subject_syllabus_id', $subjectSyllabusId)->get();

        return $this->successResponse(['messagelist' => $messageList]);
    }


}
