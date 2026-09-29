<?php

namespace Modules\Academic\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Modules\Academic\Entities\Student;
use Modules\Academic\Entities\StudentSession;
use Modules\Core\Entities\Setting;

/**
 * Mirrors CodeIgniter: application/controllers/user/cbse/Exam.php::timetable()
 * -> Cbseexam_exam_model::getStudentExamTimetable($student_session_id)
 * -> application/views/user/cbse/timetable.php
 *
 * View contract per exam: exam name header + rows:
 * subject_name (+ " (code)" when code non-empty), date (CI dateformat),
 * time_from, duration, room_no. Empty exams => no_record_found.
 */
class CbseExamController extends \Modules\Core\Http\Controllers\Api\Controller
{
    public function __construct()
    {
        $this->setControllerName('CbseExamController');
    }

    public function timetable(Request $request): JsonResponse
    {
        $user = $request->user();
        $studentSession = $this->getStudentSession($user);

        if (!$studentSession) {
            return $this->errorResponse('Student session not found', null, 404);
        }

        $setting = Setting::where('is_active', 'yes')->first();
        $currentSessionId = $setting ? (int) $setting->session_id : (int) $studentSession->session_id;

        // CI: cbse_exam_students JOIN cbse_exams WHERE student_session_id
        // AND cbse_exams.session_id = current_session AND is_active = 1 ORDER BY id desc.
        // Select: cbse_exam_students.*, cbse_exams.name, exam_code.
        $exams = DB::table('cbse_exam_students')
            ->join('cbse_exams', 'cbse_exams.id', '=', 'cbse_exam_students.cbse_exam_id')
            ->where('cbse_exam_students.student_session_id', $studentSession->id)
            ->where('cbse_exams.session_id', $currentSessionId)
            ->where('cbse_exams.is_active', 1)
            ->orderBy('cbse_exams.id', 'desc')
            ->select('cbse_exam_students.*', 'cbse_exams.name', 'cbse_exams.exam_code')
            ->get();

        $result = [];
        foreach ($exams as $exam) {
            // CI: cbse_exam_timetable JOIN subjects WHERE cbse_exam_id.
            // Select: timetable.*, subjects.name AS subject_name, subjects.code AS subject_code.
            $timeTable = DB::table('cbse_exam_timetable')
                ->join('subjects', 'subjects.id', '=', 'cbse_exam_timetable.subject_id')
                ->where('cbse_exam_timetable.cbse_exam_id', $exam->cbse_exam_id)
                ->orderBy('cbse_exam_timetable.date', 'asc')
                ->orderBy('cbse_exam_timetable.time_from', 'asc')
                ->select(
                    'cbse_exam_timetable.id',
                    'cbse_exam_timetable.cbse_exam_id',
                    'cbse_exam_timetable.subject_id',
                    'cbse_exam_timetable.date',
                    'cbse_exam_timetable.time_from',
                    'cbse_exam_timetable.time_to',
                    'cbse_exam_timetable.duration',
                    'cbse_exam_timetable.room_no',
                    'subjects.name as subject_name',
                    'subjects.code as subject_code'
                )
                ->get();

            $result[] = [
                'id' => $exam->id,
                'cbse_exam_id' => $exam->cbse_exam_id,
                'student_session_id' => $exam->student_session_id,
                'name' => $exam->name,
                'exam' => $exam->name,
                'exam_code' => $exam->exam_code,
                'time_table' => $timeTable,
            ];
        }

        return $this->successResponse(['exams' => $result]);
    }

    private function getStudentSession($user)
    {
        $studentId = null;

        if ($user->role === 'student') {
            $studentId = $user->user_id;
        } elseif ($user->role === 'parent') {
            $student = Student::where('parent_id', $user->id)->first();
            $studentId = $student ? $student->id : null;
        }

        if (!$studentId) {
            return null;
        }

        $setting = Setting::where('is_active', 'yes')->first();

        return StudentSession::where('student_id', $studentId)
            ->when($setting, fn ($q) => $q->where('session_id', $setting->session_id))
            ->first();
    }
}
