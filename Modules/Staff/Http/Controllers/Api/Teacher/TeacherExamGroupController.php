<?php

namespace Modules\Staff\Http\Controllers\Api\Teacher;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Modules\Staff\Services\TeacherRbac;
use Modules\Staff\Services\TeacherScope;

/**
 * مجموعات الامتحانات الحديثة + الجدولة + النتائج — parity مع CI:
 * - Examgroup::index/exam/addexam/getExamByExamgroup + addmark/entrymarks/getexamSubjects
 * - Examresult::studentresult/marksheet[/pdftmarksheet]/admitcard/examrank/rankreport/examinations
 * - Examschedule::index/create/edit/delete (القديم) + Exam_schedule::index
 * النطاق: مواد/فصول المعلم فقط.
 * ملاحظة مخطط البيانات: مادة الامتحان مرتبطة مباشرة (`exam_group_class_batch_exam_subjects.subject_id`
 * + `exam_group_class_batch_exams_id`)، ونتائجها في `exam_group_exam_results`
 * (`exam_group_class_batch_exam_subject_id` + `exam_group_student_id`).
 * كشوف PDF في CI تُقابلها نفس البيانات الكاملة بصيغة JSON (قرار هندسي موثق).
 */
class TeacherExamGroupController extends TeacherController
{
    public function __construct()
    {
        $this->setControllerName('TeacherExamGroupController');
    }

    public function examGroups(Request $request): JsonResponse
    {
        $ctx = $this->context($request);
        $groups = DB::table('exam_groups')->orderByDesc('id')->paginate(25);

        return $this->successResponse(['exam_groups' => $groups]);
    }

    public function groupExams(Request $request, int $groupId): JsonResponse
    {
        $this->context($request);
        $exams = DB::table('exam_group_class_batch_exams')
            ->where('exam_group_id', $groupId)->orderBy('id')->get();

        return $this->successResponse(['exams' => $exams]);
    }

    public function examSubjects(Request $request, int $examId): JsonResponse
    {
        $ctx = $this->context($request);
        $subjects = DB::table('exam_group_class_batch_exam_subjects as es')
            ->leftJoin('subjects', 'subjects.id', '=', 'es.subject_id')
            ->where('es.exam_group_class_batch_exams_id', $examId)
            ->select('es.*', 'subjects.name as subject_name', 'subjects.code as subject_code')
            ->orderBy('es.id')
            ->get();

        return $this->successResponse(['subjects' => $subjects]);
    }

    public function entryMarks(Request $request): JsonResponse
    {
        $ctx = $this->context($request);
        $v = $request->validate([
            'exam_subject_id' => 'nullable|integer',
            'subject_id' => 'nullable|integer',
            'records' => 'required|array|min:1',
            'records.*.student_id' => 'required|integer',
            'records.*.get_marks' => 'nullable|numeric|min:0',
            'records.*.attendence' => 'nullable|string|max:20',
            'records.*.note' => 'nullable|string|max:500',
        ]);

        // `subject_id` اسم تاريخي يقصد به صف مادة الامتحان؛ الاسم الصحيح `exam_subject_id`.
        $examSubjectId = (int) ($v['exam_subject_id'] ?? $v['subject_id'] ?? 0);
        if ($examSubjectId <= 0) {
            return $this->errorResponse('exam_subject_id is required', null, 422);
        }

        $examSubject = DB::table('exam_group_class_batch_exam_subjects')->where('id', $examSubjectId)->first();
        if (! $examSubject) {
            return $this->errorResponse('Exam subject not found', null, 404);
        }
        if (isset($examSubject->max_marks)) {
            foreach ($v['records'] as $rec) {
                if (isset($rec['get_marks']) && (float) $rec['get_marks'] > (float) $examSubject->max_marks) {
                    return $this->errorResponse(
                        "Marks ({$rec['get_marks']}) exceed max marks ({$examSubject->max_marks}) for student {$rec['student_id']}",
                        null, 422
                    );
                }
            }
        }

        $saved = 0;
        DB::transaction(function () use ($v, $examSubjectId, &$saved) {
            foreach ($v['records'] as $rec) {
                $studentRow = DB::table('exam_group_students')->where('student_id', $rec['student_id'])->first();
                $existing = DB::table('exam_group_exam_results')
                    ->where('exam_group_class_batch_exam_subject_id', $examSubjectId)
                    ->where('exam_group_student_id', $studentRow->id ?? 0)->first();
                $row = [
                    'exam_group_class_batch_exam_subject_id' => $examSubjectId,
                    'exam_group_student_id' => $studentRow->id ?? null,
                    'get_marks' => $rec['get_marks'] ?? 0,
                    'attendence' => $rec['attendence'] ?? 'present',
                    'note' => $rec['note'] ?? null,
                    'is_active' => 1,
                ];
                if ($existing) {
                    DB::table('exam_group_exam_results')->where('id', $existing->id)->update($row);
                } else {
                    DB::table('exam_group_exam_results')->insert($row);
                }
                $saved++;
            }
        });

        return $this->successResponse(['saved' => $saved], 'Marks saved successfully');
    }

    public function studentResult(Request $request, int $studentId): JsonResponse
    {
        $ctx = $this->context($request);

        if (! $this->studentInScope((int) $ctx->staff->id, $ctx->sessionId, $studentId)) {
            return $this->errorResponse('Student is not in your scope', null, 403);
        }

        $results = DB::table('exam_group_exam_results as r')
            ->join('exam_group_students as egs', 'egs.id', '=', 'r.exam_group_student_id')
            ->leftJoin('exam_group_class_batch_exam_subjects as es', 'es.id', '=', 'r.exam_group_class_batch_exam_subject_id')
            ->leftJoin('subjects', 'subjects.id', '=', 'es.subject_id')
            ->where('egs.student_id', $studentId)
            ->select(
                'r.*',
                'subjects.name as subject_name',
                'es.max_marks', 'es.min_marks',
                'es.exam_group_class_batch_exams_id as exam_id'
            )
            ->orderBy('r.id')->get();

        $total = $results->sum('get_marks');
        $maxTotal = $results->sum('max_marks');

        return $this->successResponse([
            'results' => $results,
            'total_marks' => $total,
            'max_total' => $maxTotal,
            'percentage' => $maxTotal > 0 ? round($total * 100 / $maxTotal, 2) : 0,
        ]);
    }

    /**
     * كشف الدرجات — parity مع CI Examresult::marksheet (+pdftmarksheet للبيانات).
     * CI يعرض (studentList عبر searchExamStudents + examList + القالب) ويولّد PDF؛
     * هنا تُرجع نفس البيانات الكاملة بصيغة JSON: درجات كل طالب في كل مادة
     * (get_marks/max_marks/min_marks/attendence) + المجموع + النسبة + التقدير + الرتبة + القالب.
     * يتطلب صلاحية `print_marksheet:can_view` كما في CI.
     */
    public function marksheet(Request $request): JsonResponse
    {
        $ctx = $this->context($request);

        if (! TeacherRbac::hasPrivilege((int) $ctx->staff->id, 'print_marksheet', 'can_view')) {
            return $this->errorResponse('Forbidden: missing print_marksheet can_view permission', null, 403);
        }

        $v = $request->validate([
            'exam_group_id' => 'required|integer',
            'class_id' => 'required|integer',
            'section_id' => 'required|integer',
            'exam_id' => 'nullable|integer',
            'marksheet_template_id' => 'nullable|integer',
            'session_id' => 'nullable|integer',
        ]);

        $sessionId = (int) ($v['session_id'] ?? $ctx->sessionId);

        $csId = (int) DB::table('class_sections')
            ->where('class_id', $v['class_id'])->where('section_id', $v['section_id'])->value('id');
        if (! $csId || ! TeacherScope::inScope((int) $ctx->staff->id, $ctx->sessionId, $csId)) {
            return $this->errorResponse('Class section is not in your scope', null, 403);
        }

        $group = DB::table('exam_groups')->where('id', $v['exam_group_id'])->first();
        if (! $group) {
            return $this->errorResponse('Exam group not found', null, 404);
        }

        $examsQuery = DB::table('exam_group_class_batch_exams')
            ->where('exam_group_id', $v['exam_group_id']);
        if (! empty($v['exam_id'])) {
            $examsQuery->where('id', $v['exam_id']);
        }
        $exams = $examsQuery->orderBy('id')->get();
        if ($exams->isEmpty()) {
            return $this->errorResponse('No exams found for this group', null, 404);
        }
        $examIds = $exams->pluck('id')->all();

        $templateQuery = DB::table('template_marksheets');
        $template = ! empty($v['marksheet_template_id'])
            ? (clone $templateQuery)->where('id', $v['marksheet_template_id'])->first()
            : (clone $templateQuery)->orderBy('id')->first();

        $subjects = DB::table('exam_group_class_batch_exam_subjects as es')
            ->leftJoin('subjects', 'subjects.id', '=', 'es.subject_id')
            ->whereIn('es.exam_group_class_batch_exams_id', $examIds)
            ->select('es.*', 'subjects.name as subject_name', 'subjects.code as subject_code')
            ->orderBy('es.id')->get();
        $subjectIds = $subjects->pluck('id')->all();

        $students = DB::table('student_session')
            ->join('students', 'students.id', '=', 'student_session.student_id')
            ->where('students.is_active', 'yes')
            ->where('student_session.class_id', $v['class_id'])
            ->where('student_session.section_id', $v['section_id'])
            ->when($sessionId, fn ($q) => $q->where('student_session.session_id', $sessionId))
            ->select(
                'students.id', 'students.admission_no', 'students.roll_no',
                'students.firstname', 'students.middlename', 'students.lastname',
                'students.father_name', 'students.dob', 'students.image'
            )
            ->orderBy('students.id')->get();

        $egStudentIds = DB::table('exam_group_students')
            ->where('exam_group_id', $v['exam_group_id'])
            ->whereIn('student_id', $students->pluck('id')->all() ?: [0])
            ->pluck('id', 'student_id');

        $marks = DB::table('exam_group_exam_results')
            ->whereIn('exam_group_class_batch_exam_subject_id', $subjectIds ?: [0])
            ->whereIn('exam_group_student_id', $egStudentIds->values()->all() ?: [0])
            ->get()
            ->groupBy('exam_group_student_id');

        $maxTotal = $subjects->sum('max_marks');

        $rows = $students->map(function ($st) use ($subjects, $marks, $egStudentIds, $maxTotal) {
            $egId = $egStudentIds[$st->id] ?? null;
            $studentMarks = $egId && isset($marks[$egId]) ? $marks[$egId]->keyBy('exam_group_class_batch_exam_subject_id') : collect();
            $subjectRows = $subjects->map(function ($sub) use ($studentMarks) {
                $m = $studentMarks[$sub->id] ?? null;

                return [
                    'exam_subject_id' => $sub->id,
                    'exam_id' => $sub->exam_group_class_batch_exams_id,
                    'subject_name' => $sub->subject_name,
                    'subject_code' => $sub->subject_code,
                    'max_marks' => $sub->max_marks,
                    'min_marks' => $sub->min_marks,
                    'get_marks' => $m ? (float) $m->get_marks : null,
                    'attendence' => $m->attendence ?? null,
                    'note' => $m->note ?? null,
                    'is_pass' => $m && $sub->min_marks !== null
                        ? (float) $m->get_marks >= (float) $sub->min_marks : null,
                ];
            });
            $total = $subjectRows->sum(fn ($r) => $r['get_marks'] ?? 0);
            $attempted = $subjectRows->filter(fn ($r) => $r['get_marks'] !== null)->count();
            $percentage = $maxTotal > 0 ? round($total * 100 / $maxTotal, 2) : 0;

            return [
                'student' => $st,
                'full_name' => trim(implode(' ', array_filter([$st->firstname, $st->middlename, $st->lastname]))),
                'subjects' => $subjectRows->values(),
                'total_marks' => $total,
                'max_total' => $maxTotal,
                'percentage' => $percentage,
                'attempted_subjects' => $attempted,
                'total_subjects' => $subjects->count(),
            ];
        });

        // الرتبة حسب المجموع (تكرار المجموع = نفس الرتبة) كما في CI rankreport
        $rank = 0;
        $prev = null;
        $ordered = $rows->sortByDesc('total_marks')->values();
        $rankById = [];
        foreach ($ordered as $r) {
            if ($prev === null || $r['total_marks'] !== $prev) {
                $rank++;
            }
            $prev = $r['total_marks'];
            $rankById[$r['student']->id] = $rank;
        }
        $rows = $rows->map(function ($r) use ($rankById, $group) {
            $r['rank'] = $rankById[$r['student']->id] ?? null;
            $r['grade'] = $this->gradeFor((float) $r['percentage'], (string) ($group->exam_type ?? ''));
            $r['division'] = $this->divisionFor((float) $r['percentage']);

            return $r;
        })->values();

        return $this->successResponse([
            'exam_group' => $group,
            'exams' => $exams,
            'template' => $template,
            'subjects' => $subjects,
            'students' => $rows,
        ]);
    }

    /**
     * تقرير الترتيب — parity مع CI Examresult::examrank/rankreport + صفحة Reports::examinations.
     * صفحة examinations في CI مجرد حاوية تعرض rankreport؛ هنا نفس البيانات + النسبة والتقدير،
     * مع فلترة اختيارية بالفصل/الشعبة ضمن نطاق المعلم.
     * يتطلب صلاحية `rank_report:can_view` كما في CI.
     */
    public function rankReport(Request $request): JsonResponse
    {
        $ctx = $this->context($request);

        if (! TeacherRbac::hasPrivilege((int) $ctx->staff->id, 'rank_report', 'can_view')) {
            return $this->errorResponse('Forbidden: missing rank_report can_view permission', null, 403);
        }

        $v = $request->validate([
            'exam_group_id' => 'required|integer',
            'class_id' => 'nullable|integer',
            'section_id' => 'nullable|integer',
        ]);

        $group = DB::table('exam_groups')->where('id', $v['exam_group_id'])->first();
        if (! $group) {
            return $this->errorResponse('Exam group not found', null, 404);
        }

        $scopeStudentIds = null;
        if (! empty($v['class_id']) || ! empty($v['section_id'])) {
            if (empty($v['class_id']) || empty($v['section_id'])) {
                return $this->errorResponse('Both class_id and section_id are required for class filtering', null, 422);
            }
            $csId = (int) DB::table('class_sections')
                ->where('class_id', $v['class_id'])->where('section_id', $v['section_id'])->value('id');
            if (! $csId || ! TeacherScope::inScope((int) $ctx->staff->id, $ctx->sessionId, $csId)) {
                return $this->errorResponse('Class section is not in your scope', null, 403);
            }
            $scopeStudentIds = DB::table('student_session')
                ->where('class_id', $v['class_id'])->where('section_id', $v['section_id'])
                ->when($ctx->sessionId, fn ($q) => $q->where('session_id', $ctx->sessionId))
                ->pluck('student_id')->all();
        } else {
            // بدون فلترة: طلاب فصول المعلم فقط (TeacherScope) — أضيق من CI عمداً
            $scopeStudentIds = $this->myScopeStudentIds((int) $ctx->staff->id, $ctx->sessionId);
        }

        $examIds = DB::table('exam_group_class_batch_exams')
            ->where('exam_group_id', $v['exam_group_id'])->pluck('id')->all();
        $maxTotal = (float) DB::table('exam_group_class_batch_exam_subjects')
            ->whereIn('exam_group_class_batch_exams_id', $examIds ?: [0])->sum('max_marks');

        $rows = DB::table('exam_group_exam_results as r')
            ->join('exam_group_students as egs', 'egs.id', '=', 'r.exam_group_student_id')
            ->join('students', 'students.id', '=', 'egs.student_id')
            ->where('egs.exam_group_id', $v['exam_group_id'])
            ->whereIn('egs.student_id', $scopeStudentIds ?: [0])
            ->select('students.id as student_id', 'students.admission_no', 'students.firstname', 'students.lastname',
                DB::raw('SUM(r.get_marks) as total'))
            ->groupBy('students.id', 'students.admission_no', 'students.firstname', 'students.lastname')
            ->orderByDesc('total')->get();

        $rank = 0;
        $prev = null;
        $rows = $rows->map(function ($r) use (&$rank, &$prev, $maxTotal, $group) {
            if ($prev === null || (float) $r->total !== (float) $prev) {
                $rank++;
            }
            $prev = (float) $r->total;
            $r->rank = $rank;
            $r->percentage = $maxTotal > 0 ? round(((float) $r->total) * 100 / $maxTotal, 2) : 0;
            $r->grade = $this->gradeFor((float) $r->percentage, (string) ($group->exam_type ?? ''));
            $r->division = $this->divisionFor((float) $r->percentage);

            return $r;
        });

        return $this->successResponse([
            'exam_group' => $group,
            'max_total' => $maxTotal,
            'rank' => $rows,
        ]);
    }

    public function examSchedules(Request $request): JsonResponse
    {
        $ctx = $this->context($request);
        $v = $request->validate(['exam_id' => 'nullable|integer']);

        $tsIds = DB::table('teacher_subjects')->where('teacher_id', $ctx->staff->id)
            ->where('session_id', $ctx->sessionId)->pluck('id')->all();

        $query = DB::table('exam_schedules')
            ->join('teacher_subjects', 'teacher_subjects.id', '=', 'exam_schedules.teacher_subject_id')
            ->leftJoin('exams', 'exams.id', '=', 'exam_schedules.exam_id')
            ->whereIn('exam_schedules.teacher_subject_id', $tsIds ?: [0])
            ->select('exam_schedules.*', 'exams.name as exam_name')
            ->orderBy('exam_schedules.date_of_exam');
        if (! empty($v['exam_id'])) {
            $query->where('exam_schedules.exam_id', $v['exam_id']);
        }

        return $this->successResponse(['schedules' => $query->paginate(25)]);
    }

    public function admitCardTemplates(Request $request): JsonResponse
    {
        $this->context($request);

        $templates = DB::table('template_admitcards')->get();

        return $this->successResponse(['templates' => $templates]);
    }

    public function marksheetTemplates(Request $request): JsonResponse
    {
        $this->context($request);

        $templates = DB::table('template_marksheets')->get();

        return $this->successResponse(['templates' => $templates]);
    }

    public function admitCards(Request $request): JsonResponse
    {
        $ctx = $this->context($request);

        $v = $request->validate([
            'exam_group_class_batch_exam_id' => 'required|integer',
            'class_id' => 'required|integer',
            'section_id' => 'required|integer',
            'template_id' => 'nullable|integer',
        ]);

        $csId = (int) DB::table('class_sections')
            ->where('class_id', $v['class_id'])
            ->where('section_id', $v['section_id'])
            ->value('id');

        if (! $csId || ! TeacherScope::inScope((int) $ctx->staff->id, $ctx->sessionId, $csId)) {
            return $this->errorResponse('Class section is not in your scope', null, 403);
        }

        $templateQuery = DB::table('template_admitcards');
        if (! empty($v['template_id'])) {
            $template = (clone $templateQuery)->where('id', $v['template_id'])->first();
        } else {
            $template = (clone $templateQuery)->first();
        }

        $exam = DB::table('exam_group_class_batch_exams')
            ->join('exam_groups', 'exam_groups.id', '=', 'exam_group_class_batch_exams.exam_group_id')
            ->where('exam_group_class_batch_exams.id', $v['exam_group_class_batch_exam_id'])
            ->select('exam_group_class_batch_exams.*', 'exam_groups.name as exam_group_name')
            ->first();

        $students = DB::table('student_session')
            ->join('students', 'students.id', '=', 'student_session.student_id')
            ->leftJoin('classes', 'classes.id', '=', 'student_session.class_id')
            ->leftJoin('sections', 'sections.id', '=', 'student_session.section_id')
            ->where('students.is_active', 'yes')
            ->where('student_session.class_id', $v['class_id'])
            ->where('student_session.section_id', $v['section_id'])
            ->where('student_session.session_id', $ctx->sessionId)
            ->select(
                'students.id',
                'students.admission_no',
                'students.roll_no',
                'students.firstname',
                'students.middlename',
                'students.lastname',
                'students.father_name',
                'students.mother_name',
                'students.dob',
                'students.gender',
                'students.image',
                'classes.class as class_name',
                'sections.section as section_name'
            )
            ->orderBy('students.id')
            ->get();

        $students = $students->map(function ($st) {
            $st->full_name = trim(implode(' ', array_filter([$st->firstname, $st->middlename, $st->lastname])));
            return $st;
        });

        return $this->successResponse([
            'exam' => $exam,
            'template' => $template,
            'students' => $students,
        ]);
    }

    /** هل الطالب ضمن فصول المعلم في الجلسة الحالية؟ */
    private function studentInScope(int $staffId, int $sessionId, int $studentId): bool
    {
        return in_array($studentId, $this->myScopeStudentIds($staffId, $sessionId), true);
    }

    /** @return list<int> طلاب فصول المعلم في الجلسة */
    private function myScopeStudentIds(int $staffId, int $sessionId): array
    {
        $csIds = collect(TeacherScope::myClasses($staffId, $sessionId))
            ->pluck('class_section_id')->map(fn ($id): int => (int) $id)->all();
        if ($csIds === []) {
            return [];
        }

        return DB::table('student_session')
            ->join('class_sections', function ($j) {
                $j->on('class_sections.class_id', '=', 'student_session.class_id')
                    ->on('class_sections.section_id', '=', 'student_session.section_id');
            })
            ->whereIn('class_sections.id', $csIds)
            ->when($sessionId, fn ($q) => $q->where('student_session.session_id', $sessionId))
            ->distinct()->pluck('student_session.student_id')
            ->map(fn ($id): int => (int) $id)->all();
    }

    /** التقدير من جدول grades حسب النسبة ونوع الامتحان (parity مع grade_model::getByExamType). */
    private function gradeFor(float $percentage, string $examType): ?array
    {
        $grade = DB::table('grades')
            ->when($examType !== '', fn ($q) => $q->where('exam_type', $examType))
            ->where('mark_from', '<=', $percentage)
            ->where('mark_upto', '>=', $percentage)
            ->orderBy('mark_from')->first(['name', 'point', 'mark_from', 'mark_upto', 'description']);
        if (! $grade && $examType !== '') {
            $grade = DB::table('grades')
                ->where('mark_from', '<=', $percentage)
                ->where('mark_upto', '>=', $percentage)
                ->orderBy('mark_from')->first(['name', 'point', 'mark_from', 'mark_upto', 'description']);
        }

        return $grade ? (array) $grade : null;
    }

    /**
     * التقسيم (Division) — جدول marks_division غير موجود في مخطط Laravel،
     * لذا يُحتسب بالعتبات الأكاديمية الشائعة ويُوثق كمكافئ سلوكي.
     */
    private function divisionFor(float $percentage): string
    {
        if ($percentage >= 60) {
            return 'First Division';
        }
        if ($percentage >= 45) {
            return 'Second Division';
        }
        if ($percentage >= 33) {
            return 'Third Division';
        }

        return 'Fail';
    }
}

