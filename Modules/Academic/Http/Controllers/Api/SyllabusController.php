<?php

namespace Modules\Academic\Http\Controllers\Api;

use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Modules\Academic\Entities\Student;
use Modules\Academic\Entities\StudentSession;
use Modules\Academic\Entities\Syllabus;
use Modules\Academic\Entities\SyllabusMessage;
use Modules\Academic\Http\Requests\SyllabusMessageRequest;
use Modules\Academic\Services\SyllabusService;
use Modules\Core\Entities\Setting;
use Modules\Core\Http\Controllers\Api\Controller;

class SyllabusController extends Controller
{
    public function __construct(
        private readonly SyllabusService $syllabusService
    ) {
        $this->setControllerName('SyllabusController');
    }

    public function index(): JsonResponse
    {
        $setting = Setting::first();
        $startWeekday = strtolower($setting->start_week ?? 'monday');

        $monday = Carbon::now()->startOfWeek();
        $thisWeekStart = $monday->format('Y-m-d');
        $thisWeekEnd = $monday->copy()->addDays(6)->format('Y-m-d');

        $data = [
            'this_week_start' => $thisWeekStart,
            'this_week_end' => $thisWeekEnd,
        ];

        return $this->successResponse($data);
    }

    public function getWeekdates(Request $request): JsonResponse
    {
        $user = $request->user();
        $studentSession = $this->getStudentSession($user);

        if (! $studentSession) {
            return $this->errorResponse('Student session not found');
        }

        $setting = Setting::first();
        $startWeekday = strtolower($setting->start_week ?? 'monday');

        $date = $request->input('date');
        if (! $date) {
            return $this->errorResponse('Date is required');
        }

        $dateCarbon = Carbon::parse($date);
        $prevWeekStart = $dateCarbon->copy()->subWeek()->startOfWeek($startWeekday)->format('Y-m-d');
        $nextWeekStart = $dateCarbon->copy()->addWeek()->startOfWeek($startWeekday)->format('Y-m-d');
        $thisWeekEnd = $dateCarbon->copy()->addDays(6)->format('Y-m-d');

        $studentData = Syllabus::getStudentSyllabus(
            $studentSession->class_id,
            $studentSession->section_id,
            $studentSession->session_id
        );

        $timetable = $this->getDaysName();
        $syllabusByDate = [];

        if ($studentData->isNotEmpty()) {
            $subjectGroupClassSectionId = $studentData->first()->subject_group_class_section_id;

            foreach ($timetable as $dayKey => $dayValue) {
                $dayIndex = array_search($dayKey, array_keys($timetable));
                $currentDate = $dateCarbon->copy()->addDays($dayIndex)->format('Y-m-d');

                $syllabusData = Syllabus::getSubjectSyllabusByDate(
                    $subjectGroupClassSectionId,
                    $currentDate,
                    $studentSession->session_id
                );

                $syllabusByDate[$dayKey] = $syllabusData->map(fn ($item) => [
                    'id' => $item->id,
                    'subname' => $item->subname,
                    'scode' => $item->scode,
                    'time_from' => $item->time_from,
                    'time_to' => $item->time_to,
                    'lessonname' => $item->lessonname,
                    'topic_name' => $item->topic_name,
                ])->toArray();
            }
        }

        $data = [
            'this_week_start' => $dateCarbon->format('Y-m-d'),
            'this_week_end' => $thisWeekEnd,
            'prev_week_start' => $prevWeekStart,
            'next_week_start' => $nextWeekStart,
            'timetable' => $timetable,
            'syllabus_by_date' => $syllabusByDate,
        ];

        return $this->successResponse($data);
    }

    private function getDaysName(): array
    {
        $setting = Setting::first();
        $startWeekday = strtolower($setting->start_week ?? 'monday');

        $days = [
            'Monday' => 'Monday',
            'Tuesday' => 'Tuesday',
            'Wednesday' => 'Wednesday',
            'Thursday' => 'Thursday',
            'Friday' => 'Friday',
            'Saturday' => 'Saturday',
            'Sunday' => 'Sunday',
        ];

        $startDayIndex = array_search(ucfirst($startWeekday), array_keys($days));
        if ($startDayIndex === false) {
            $startDayIndex = 0;
        }

        $reorderedDays = array_slice($days, $startDayIndex, null, true) + array_slice($days, 0, $startDayIndex, true);

        return $reorderedDays;
    }

    public function status(Request $request): JsonResponse
    {
        $user = $request->user();
        $studentSession = $this->getStudentSession($user);

        if (! $studentSession) {
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

        if (! $result) {
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

        if (! $studentId) {
            return null;
        }

        $setting = Setting::where('is_active', 'yes')->first();

        return StudentSession::where('student_id', $studentId)
            ->when($setting, fn ($q) => $q->where('session_id', $setting->session_id))
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
