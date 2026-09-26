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
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class SyllabusController extends Controller
{
    public function __construct(
        private readonly SyllabusService $syllabusService
    ) {
        $this->setControllerName('SyllabusController');
    }

    public function index(): JsonResponse
    {
        // CI: Syllabus::index() — "last {start_week}" relative to today.
        // $monday = strtotime("last ".$start_weekday);
        // $monday = date('w',$monday)==date('w') ? $monday+7*86400 : $monday;
        $setting = Setting::first();
        $startWeekday = strtolower($setting->start_week ?? 'monday');

        $monday = strtotime('last ' . $startWeekday);
        if (date('w', $monday) == date('w')) {
            $monday += 7 * 86400;
        }
        $sunday = strtotime(date('Y-m-d', $monday) . ' +6 days');

        $data = [
            'this_week_start' => date('Y-m-d', $monday),
            'this_week_end' => date('Y-m-d', $sunday),
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

        // CI accepts the school-formatted date (d-m-Y or d/m/Y) via
        // customlib->dateFormatToYYYYMMDD(); accept Y-m-d as well for API clients.
        $thisWeekStart = $this->toYYYYMMDD($date);
        if (! $thisWeekStart) {
            return $this->errorResponse('Invalid date format');
        }

        // CI: prev/next = last/next {start_weekday} relative to week start,
        // end = week start + 6 days.
        $prevWeekStart = date('Y-m-d', strtotime('last ' . $startWeekday, strtotime($thisWeekStart)));
        $nextWeekStart = date('Y-m-d', strtotime('next ' . $startWeekday, strtotime($thisWeekStart)));
        $thisWeekEnd = date('Y-m-d', strtotime($thisWeekStart . ' +6 day'));

        $dateCarbon = Carbon::parse($thisWeekStart);

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
            'this_week_start' => $thisWeekStart,
            'this_week_end' => $thisWeekEnd,
            'prev_week_start' => $prevWeekStart,
            'next_week_start' => $nextWeekStart,
            'timetable' => $timetable,
            'syllabus_by_date' => $syllabusByDate,
        ];

        return $this->successResponse($data);
    }

    /**
     * Mirror CI customlib->dateFormatToYYYYMMDD(): school format (d-m-Y/d/m/Y)
     * or plain Y-m-d → Y-m-d. Returns null when unparseable.
     */
    private function toYYYYMMDD(string $date): ?string
    {
        $date = trim($date);
        if (preg_match('/^\d{4}-\d{2}-\d{2}/', $date)) {
            try {
                return Carbon::parse(substr($date, 0, 10))->format('Y-m-d');
            } catch (\Throwable) {
                return null;
            }
        }
        foreach (['d-m-Y', 'd/m/Y', 'm-d-Y', 'Y/m/d'] as $format) {
            try {
                $parsed = Carbon::createFromFormat($format, substr($date, 0, 10));

                return $parsed->format('Y-m-d');
            } catch (\Throwable) {
                continue;
            }
        }

        return null;
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

    public function download(Request $request, $id): JsonResponse|BinaryFileResponse
    {
        $result = Syllabus::find($id);

        if (! $result) {
            return $this->errorResponse('Syllabus not found', null, 404);
        }

        return $this->sendStoredFile($result->attachment, 'uploads/syllabus_attachment');
    }

    public function lactureVideoDownload(Request $request, $id): JsonResponse|BinaryFileResponse
    {
        $result = Syllabus::find($id);

        if (! $result) {
            return $this->errorResponse('Syllabus not found', null, 404);
        }

        return $this->sendStoredFile($result->lacture_video, 'uploads/syllabus_attachment/lacture_video');
    }

    public function subjectSyllabus(Request $request, $id = null): JsonResponse
    {
        // CI canonical param is subject_syllabus_id; accept legacy aliases.
        $subjectSyllabusId = $id
            ?? $request->input('subject_syllabus_id')
            ?? $request->input('syllabus_id')
            ?? $request->input('lesson_plan_id');

        if (! $subjectSyllabusId) {
            return $this->errorResponse('subject_syllabus_id is required');
        }

        $result = $this->getSubjectSyllabusDetail($subjectSyllabusId);

        if (! $result) {
            return $this->errorResponse('Syllabus not found', null, 404);
        }

        $messageList = $this->getEnrichedMessages($subjectSyllabusId);

        return $this->successResponse([
            'subject_syllabus_id' => (int) $subjectSyllabusId,
            'result' => $result,
            'messagelist' => $messageList,
        ]);
    }

    public function checkSubjectSyllabus(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'subject_group_subject_id' => 'required',
            'date' => 'required',
            'time_from' => 'required',
            'time_to' => 'required',
            'subject_group_class_section_id' => 'required',
        ]);

        $record = DB::table('subject_syllabus')
            ->join('topic', 'topic.id', '=', 'subject_syllabus.topic_id')
            ->join('lesson', 'lesson.id', '=', 'topic.lesson_id')
            ->where('lesson.subject_group_subject_id', $validated['subject_group_subject_id'])
            ->where('lesson.subject_group_class_sections_id', $validated['subject_group_class_section_id'])
            ->where('subject_syllabus.date', $validated['date'])
            ->where('subject_syllabus.time_from', $validated['time_from'])
            ->where('subject_syllabus.time_to', $validated['time_to'])
            ->first();

        if (! $record) {
            return $this->errorResponse('Syllabus not found', null, 404);
        }

        $result = $this->getSubjectSyllabusDetail($record->id);

        return $this->successResponse([
            'subject_syllabus_id' => $record->id,
            'result' => $result,
        ]);
    }

    private function getSubjectSyllabusDetail($subjectSyllabusId): ?object
    {
        // CI filters by current session; resolve it the same way getStudentSession() does.
        $setting = Setting::where('is_active', 'yes')->first();
        $sessionId = $setting?->session_id;

        $query = DB::table('subject_syllabus')
            ->join('topic', 'topic.id', '=', 'subject_syllabus.topic_id')
            ->join('lesson', 'lesson.id', '=', 'topic.lesson_id')
            ->join('subject_group_subjects', 'subject_group_subjects.id', '=', 'lesson.subject_group_subject_id')
            ->join('subject_groups', 'subject_groups.id', '=', 'subject_group_subjects.subject_group_id')
            ->join('subjects', 'subjects.id', '=', 'subject_group_subjects.subject_id')
            ->join('subject_group_class_sections', 'subject_group_class_sections.id', '=', 'lesson.subject_group_class_sections_id')
            ->join('class_sections', 'class_sections.id', '=', 'subject_group_class_sections.class_section_id')
            ->join('sections', 'sections.id', '=', 'class_sections.section_id')
            ->join('classes', 'classes.id', '=', 'class_sections.class_id')
            ->where('subject_syllabus.id', $subjectSyllabusId);
        if ($sessionId) {
            $query->where('subject_syllabus.session_id', $sessionId);
        }

        return $query->select(
                'subject_syllabus.*',
                'subject_groups.name as sgname',
                'subjects.name as subname',
                'subjects.code as scode',
                'sections.section as sname',
                'classes.class as cname',
                'lesson.name as lessonname',
                'topic.name as topic_name',
                'topic.status as topic_status'
            )
            ->first();
    }

    public function addmessage(SyllabusMessageRequest $request): JsonResponse
    {
        $user = $request->user();
        $studentId = $this->getStudentId($user);

        // Canonical CI field is subject_syllabus_id (aliases normalised in FormRequest).
        $subjectSyllabusId = $request->subject_syllabus_id;

        DB::transaction(function () use ($subjectSyllabusId, $studentId, $request) {
            SyllabusMessage::create([
                'subject_syllabus_id' => $subjectSyllabusId,
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
        $subjectSyllabusId = $request->input('subject_syllabus_id')
            ?? $request->input('syllabus_id')
            ?? $request->input('lesson_plan_id');

        if (! $subjectSyllabusId) {
            return $this->errorResponse('subject_syllabus_id is required');
        }

        return $this->successResponse(['messagelist' => $this->getEnrichedMessages($subjectSyllabusId)]);
    }

    /**
     * Mirror CI syllabus_model->getstudentmessage(): forum rows joined with
     * staff + students so clients get display names / images without extra calls.
     */
    private function getEnrichedMessages($subjectSyllabusId)
    {
        return DB::table('lesson_plan_forum')
            ->leftJoin('staff', 'staff.id', '=', 'lesson_plan_forum.staff_id')
            ->leftJoin('students', 'students.id', '=', 'lesson_plan_forum.student_id')
            ->where('lesson_plan_forum.subject_syllabus_id', $subjectSyllabusId)
            ->orderByDesc('lesson_plan_forum.id')
            ->select(
                'lesson_plan_forum.id as fourm_id',
                'lesson_plan_forum.message',
                'lesson_plan_forum.created_date',
                'lesson_plan_forum.type',
                'staff.name as staff_name',
                'staff.surname as staff_surname',
                'staff.employee_id as staff_employee_id',
                'staff.image as staff_image',
                'staff.gender',
                'students.firstname',
                'students.middlename',
                'students.lastname',
                'students.image as student_image',
                'students.admission_no',
                'staff.id as staff_id',
                'students.id as student_id',
                'students.gender as students_gender'
            )
            ->get();
    }

    public function deletemessage(Request $request): JsonResponse
    {
        $fourmId = $request->input('fourm_id') ?? $request->input('id');

        if (! $fourmId) {
            return $this->errorResponse('fourm_id is required');
        }

        $user = $request->user();
        $studentId = $this->getStudentId($user);

        $message = SyllabusMessage::where('id', $fourmId)
            ->where('type', 'student')
            ->where('student_id', $studentId)
            ->first();

        if (! $message) {
            return $this->errorResponse('Message not found', null, 404);
        }

        $message->delete();

        return $this->successResponse(null, 'Message deleted successfully');
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
