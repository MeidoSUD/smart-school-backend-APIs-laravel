<?php

namespace Modules\Staff\Http\Controllers\Api\Teacher;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Modules\Staff\Services\TeacherScope;

/**
 * درجات المعلم — parity مع CI admin/Mark:
 * - index: exam + class/section → جداول الامتحان لموااد المعلم (getTeacherSubjects) + قائمة الطلاب + النتائج الحالية
 * - save: examresult_model->add_exam_result (get_marks/attendence/note لكل طالب وجدول)
 * - myExams: جداول امتحانات مواد المعلم
 */
class TeacherMarkController extends TeacherController
{
    public function __construct()
    {
        $this->setControllerName('TeacherMarkController');
    }

    private function myTeacherSubjectIds(int $staffId, int $sessionId, ?int $classSectionId = null): array
    {
        return DB::table('teacher_subjects')->where('teacher_id', $staffId)
            ->where('session_id', $sessionId)
            ->when($classSectionId, fn ($q) => $q->where('class_section_id', $classSectionId))
            ->pluck('id')->map(fn ($x) => (int) $x)->all();
    }

    public function myExams(Request $request): JsonResponse
    {
        $ctx = $this->context($request);

        $tsIds = $this->myTeacherSubjectIds((int) $ctx->staff->id, $ctx->sessionId);
        $schedules = DB::table('exam_schedules')
            ->join('teacher_subjects', 'teacher_subjects.id', '=', 'exam_schedules.teacher_subject_id')
            ->leftJoin('subjects', 'subjects.id', '=', 'teacher_subjects.subject_id')
            ->leftJoin('exams', 'exams.id', '=', 'exam_schedules.exam_id')
            ->whereIn('exam_schedules.teacher_subject_id', $tsIds ?: [0])
            ->select('exam_schedules.*', 'subjects.name as subject_name',
                'exams.name as exam_name', 'teacher_subjects.class_section_id')
            ->orderBy('exam_schedules.date_of_exam')->get();

        return $this->successResponse(['exam_schedules' => $schedules]);
    }

    public function markSheet(Request $request): JsonResponse
    {
        $ctx = $this->context($request);

        $v = $request->validate([
            'exam_id' => 'required|integer',
            'class_id' => 'required|integer', 'section_id' => 'required|integer',
        ]);

        $csId = (int) DB::table('class_sections')
            ->where('class_id', $v['class_id'])->where('section_id', $v['section_id'])->value('id');
        if (! $csId || ! TeacherScope::inScope((int) $ctx->staff->id, $ctx->sessionId, $csId)) {
            return $this->errorResponse('Class section is not in your scope', null, 403);
        }

        $tsIds = $this->myTeacherSubjectIds((int) $ctx->staff->id, $ctx->sessionId, $csId);
        $schedules = DB::table('exam_schedules')
            ->join('teacher_subjects', 'teacher_subjects.id', '=', 'exam_schedules.teacher_subject_id')
            ->leftJoin('subjects', 'subjects.id', '=', 'teacher_subjects.subject_id')
            ->where('exam_schedules.exam_id', $v['exam_id'])
            ->whereIn('exam_schedules.teacher_subject_id', $tsIds ?: [0])
            ->select('exam_schedules.*', 'subjects.name as subject_name')->get();

        $students = DB::table('student_session')
            ->join('students', 'students.id', '=', 'student_session.student_id')
            ->where('students.is_active', 'yes')
            ->where('student_session.class_id', $v['class_id'])
            ->where('student_session.section_id', $v['section_id'])
            ->when($ctx->sessionId, fn ($q) => $q->where('student_session.session_id', $ctx->sessionId))
            ->select('students.id', 'students.admission_no', 'students.firstname',
                'students.lastname', 'student_session.id as student_session_id')
            ->orderBy('students.id')->get();

        $scheduleIds = $schedules->pluck('id')->all();
        // MISSING DEPENDENCY: جدول exam_results غير موجود في هذه القاعدة
        // (CI: exam_results بـ exam_schedule_id/student_id/get_marks/attendence/note).
        // تُعرض الكشوفات بدون نتائج حتى يُنشأ الجدول بـ migration معتمد.
        $resultsAvailable = Schema::hasTable('exam_results');
        $results = collect();
        if ($resultsAvailable) {
            $results = DB::table('exam_results')
                ->whereIn('exam_schedule_id', $scheduleIds ?: [0])->get()
                ->groupBy(fn ($r) => $r->student_id . '_' . $r->exam_schedule_id);
        }

        $students = $students->map(function ($st) use ($schedules, $results) {
            $st->marks = [];
            foreach ($schedules as $sch) {
                $key = $st->id . '_' . $sch->id;
                $st->marks[] = [
                    'exam_schedule_id' => $sch->id,
                    'result' => isset($results[$key]) ? $results[$key]->first() : null,
                ];
            }

            return $st;
        });

        return $this->successResponse(['exam_schedules' => $schedules, 'students' => $students, 'results_available' => $resultsAvailable]);
    }

    public function saveMarks(Request $request): JsonResponse
    {
        $ctx = $this->context($request);

        $v = $request->validate([
            'records' => 'required|array|min:1',
            'records.*.exam_schedule_id' => 'required|integer|exists:exam_schedules,id',
            'records.*.student_id' => 'required|integer|exists:students,id',
            'records.*.get_marks' => 'nullable|numeric|min:0',
            'records.*.attendence' => 'nullable|string|max:20',
            'records.*.note' => 'nullable|string|max:500',
        ]);

        $tsIds = $this->myTeacherSubjectIds((int) $ctx->staff->id, $ctx->sessionId);
        if (! Schema::hasTable('exam_results')) {
            return $this->errorResponse(
                'BLOCKED: exam_results table is missing. Required schema (CodeIgniter exam_results): exam_schedule_id, student_id, get_marks, attendence, note.',
                null, 503
            );
        }
        $saved = 0;
        DB::transaction(function () use ($v, $tsIds, &$saved) {
            foreach ($v['records'] as $rec) {
                $belongs = DB::table('exam_schedules')->where('id', $rec['exam_schedule_id'])
                    ->whereIn('teacher_subject_id', $tsIds ?: [0])->exists();
                if (! $belongs) continue;
                $existing = DB::table('exam_results')
                    ->where('exam_schedule_id', $rec['exam_schedule_id'])
                    ->where('student_id', $rec['student_id'])->first();
                // CI parity (Mark::create → Examresult_model::add_exam_result):
                // only exam_schedule_id/student_id/get_marks/attendence/note exist.
                $row = ['exam_schedule_id' => $rec['exam_schedule_id'], 'student_id' => $rec['student_id'],
                    'get_marks' => $rec['get_marks'] ?? 0,
                    'attendence' => $rec['attendence'] ?? 'present',
                    'note' => $rec['note'] ?? null];
                if ($existing) {
                    DB::table('exam_results')->where('id', $existing->id)->update($row);
                } else {
                    DB::table('exam_results')->insert($row);
                }
                $saved++;
            }
        });

        return $this->successResponse(['saved' => $saved], 'Marks saved successfully');
    }
}
