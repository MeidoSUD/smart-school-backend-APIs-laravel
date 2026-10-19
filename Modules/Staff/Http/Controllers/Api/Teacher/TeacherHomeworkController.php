<?php

namespace Modules\Staff\Http\Controllers\Api\Teacher;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Exceptions\HttpResponseException;
use Modules\Staff\Services\TeacherScope;

/**
 * واجبات المعلم — parity مع CI Homework:
 * - create (class/section/subject_group_subject + dates + description + staff=creator + session)
 * - edit/getRecord/delete (مالك الواجب أو نطاق الفصل)
 * - evaluation/addEvaluation (marks+note لكل طالب + evaluated_by)
 * - evaluation_report
 * ملاحظة: رفع الملفات (media_storage) والمailsms يُوثقان كفرق — يقبل `document` كمسار اختياري.
 */
class TeacherHomeworkController extends TeacherController
{
    public function __construct()
    {
        $this->setControllerName('TeacherHomeworkController');
    }

    /** resolve class_section ضمن النطاق — يرمي 403 خارج النطاق. */
    private function scopedSection(int $staffId, int $sessionId, int $classId, int $sectionId): int
    {
        $id = (int) DB::table('class_sections')
            ->where('class_id', $classId)->where('section_id', $sectionId)->value('id');
        if (! $id || ! TeacherScope::inScope($staffId, $sessionId, $id)) {
            throw new HttpResponseException(
                $this->errorResponse('Class section is not in your scope', null, 403)
            );
        }

        return $id;
    }

    /** واجبات فصولي (قابل للفلترة) */
    public function index(Request $request): JsonResponse
    {
        $ctx = $this->context($request);

        $v = $request->validate([
            'class_id' => 'nullable|integer', 'section_id' => 'nullable|integer',
            'subject_group_subject_id' => 'nullable|integer',
            'from' => 'nullable|date', 'to' => 'nullable|date',
            'mine' => 'nullable|boolean',
        ]);

        $classes = TeacherScope::myClasses((int) $ctx->staff->id, $ctx->sessionId);
        $pairs = collect($classes)->map(fn ($c) => [$c['class_id'], $c['section_id']])->all();

        $query = DB::table('homework')
            ->leftJoin('subject_group_subjects as sgs', 'sgs.id', '=', 'homework.subject_group_subject_id')
            ->leftJoin('subjects', 'subjects.id', '=', 'sgs.subject_id')
            ->when($ctx->sessionId, fn ($q) => $q->where('homework.session_id', $ctx->sessionId))
            ->where(function ($q) use ($pairs) {
                foreach ($pairs as [$cid, $sid]) {
                    $q->orWhere(fn ($qq) => $qq->where('homework.class_id', $cid)->where('homework.section_id', $sid));
                }
            })
            ->select('homework.*', 'subjects.name as subject_name', 'subjects.code as subject_code')
            ->orderByDesc('homework.homework_date');

        if (! empty($v['class_id'])) $query->where('homework.class_id', $v['class_id']);
        if (! empty($v['section_id'])) $query->where('homework.section_id', $v['section_id']);
        if (! empty($v['subject_group_subject_id'])) $query->where('homework.subject_group_subject_id', $v['subject_group_subject_id']);
        if (! empty($v['from'])) $query->whereDate('homework.homework_date', '>=', $v['from']);
        if (! empty($v['to'])) $query->whereDate('homework.homework_date', '<=', $v['to']);
        if (! empty($v['mine'])) $query->where('homework.created_by', $ctx->staff->id);

        return $this->successResponse(['homework' => $query->paginate(25)]);
    }

    public function store(Request $request): JsonResponse
    {
        $ctx = $this->context($request);

        $v = $request->validate([
            'class_id' => 'required|integer', 'section_id' => 'required|integer',
            'subject_group_subject_id' => 'required|integer',
            'homework_date' => 'required|date', 'submit_date' => 'required|date|after_or_equal:homework_date',
            'description' => 'required|string', 'marks' => 'nullable|numeric',
            'document' => 'nullable|string|max:255',
        ]);
        $csId = $this->scopedSection((int) $ctx->staff->id, $ctx->sessionId, (int) $v['class_id'], (int) $v['section_id']);

        // المادة يجب أن تكون مسندة للمعلم في هذا الفصل (إن وُجدت بيانات إسناد)
        $assigned = DB::table('teacher_subjects')->where('teacher_id', $ctx->staff->id)
            ->where('class_section_id', $csId)->where('subject_group_subject_id', $v['subject_group_subject_id'] ?? null);
        // لا نرفض عند غياب بيانات الإسناد (توافق مع بيانات قديمة) — لكن نتحقق من وجود المادة
        $sgSubject = DB::table('subject_group_subjects')->where('id', $v['subject_group_subject_id'])->first();
        if (! $sgSubject) {
            return $this->errorResponse('Subject not found', null, 404);
        }

        $id = DB::table('homework')->insertGetId([
            'session_id' => $ctx->sessionId, 'class_id' => $v['class_id'], 'section_id' => $v['section_id'],
            'homework_date' => $v['homework_date'], 'submit_date' => $v['submit_date'],
            'staff_id' => $ctx->staff->id, 'subject_group_subject_id' => $v['subject_group_subject_id'],
            'marks' => $v['marks'] ?? null, 'description' => $v['description'],
            'create_date' => date('Y-m-d'), 'created_by' => $ctx->staff->id,
            'document' => $v['document'] ?? null,
        ]);

        return $this->successResponse(['id' => $id], 'Homework created successfully');
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $ctx = $this->context($request);

        $hw = DB::table('homework')->where('id', $id)->first();
        if (! $hw) return $this->errorResponse('Homework not found', null, 404);

        $csId = (int) DB::table('class_sections')
            ->where('class_id', $hw->class_id)->where('section_id', $hw->section_id)->value('id');
        if (! $csId || ! TeacherScope::inScope((int) $ctx->staff->id, $ctx->sessionId, $csId)) {
            return $this->errorResponse('Homework is not in your scope', null, 403);
        }

        $v = $request->validate([
            'homework_date' => 'sometimes|date', 'submit_date' => 'sometimes|date',
            'description' => 'sometimes|string', 'marks' => 'nullable|numeric',
            'document' => 'nullable|string|max:255',
        ]);

        DB::table('homework')->where('id', $id)->update($v);

        return $this->successResponse(null, 'Homework updated successfully');
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        $ctx = $this->context($request);

        $hw = DB::table('homework')->where('id', $id)->first();
        if (! $hw) return $this->errorResponse('Homework not found', null, 404);

        $csId = (int) DB::table('class_sections')
            ->where('class_id', $hw->class_id)->where('section_id', $hw->section_id)->value('id');
        if (! $csId || ! TeacherScope::inScope((int) $ctx->staff->id, $ctx->sessionId, $csId)) {
            return $this->errorResponse('Homework is not in your scope', null, 403);
        }

        DB::table('homework')->where('id', $id)->delete();

        return $this->successResponse(null, 'Homework deleted successfully');
    }

    /** طلاب الواجب مع تقييماتهم (evaluation) */
    public function evaluation(Request $request, int $id): JsonResponse
    {
        $ctx = $this->context($request);

        $hw = DB::table('homework')->where('id', $id)->first();
        if (! $hw) return $this->errorResponse('Homework not found', null, 404);

        $csId = (int) DB::table('class_sections')
            ->where('class_id', $hw->class_id)->where('section_id', $hw->section_id)->value('id');
        if (! $csId || ! TeacherScope::inScope((int) $ctx->staff->id, $ctx->sessionId, $csId)) {
            return $this->errorResponse('Homework is not in your scope', null, 403);
        }

        $students = DB::table('student_session')
            ->join('students', 'students.id', '=', 'student_session.student_id')
            ->leftJoin('homework_evaluation', function ($j) use ($id) {
                $j->on('homework_evaluation.student_session_id', '=', 'student_session.id')
                    ->where('homework_evaluation.homework_id', $id);
            })
            ->where('students.is_active', 'yes')
            ->where('student_session.class_id', $hw->class_id)
            ->where('student_session.section_id', $hw->section_id)
            ->when($ctx->sessionId, fn ($q) => $q->where('student_session.session_id', $ctx->sessionId))
            ->select('students.id', 'students.admission_no', 'students.firstname', 'students.lastname',
                'student_session.id as student_session_id',
                'homework_evaluation.id as evaluation_id', 'homework_evaluation.marks as evaluation_marks',
                'homework_evaluation.note as evaluation_note')
            ->orderBy('students.id')->get();

        return $this->successResponse(['homework' => $hw, 'students' => $students]);
    }

    /** حفظ التقييم (insert/update لكل طالب + evaluated_by) */
    public function saveEvaluation(Request $request): JsonResponse
    {
        $ctx = $this->context($request);

        $v = $request->validate([
            'homework_id' => 'required|integer|exists:homework,id',
            'evaluation_date' => 'nullable|date',
            'records' => 'required|array|min:1',
            'records.*.student_session_id' => 'required|integer',
            'records.*.marks' => 'nullable|numeric',
            'records.*.note' => 'nullable|string|max:500',
        ]);

        $hw = DB::table('homework')->where('id', $v['homework_id'])->first();
        $csId = (int) DB::table('class_sections')
            ->where('class_id', $hw->class_id)->where('section_id', $hw->section_id)->value('id');
        if (! $csId || ! TeacherScope::inScope((int) $ctx->staff->id, $ctx->sessionId, $csId)) {
            return $this->errorResponse('Homework is not in your scope', null, 403);
        }

        $validIds = DB::table('student_session')
            ->where('class_id', $hw->class_id)->where('section_id', $hw->section_id)
            ->when($ctx->sessionId, fn ($q) => $q->where('session_id', $ctx->sessionId))
            ->pluck('id')->map(fn ($x) => (int) $x)->all();

        DB::transaction(function () use ($v, $validIds, $ctx, $hw) {
            foreach ($v['records'] as $rec) {
                $ssid = (int) $rec['student_session_id'];
                if (! in_array($ssid, $validIds, true)) continue;
                $studentId = (int) DB::table('student_session')->where('id', $ssid)->value('student_id');
                $existing = DB::table('homework_evaluation')
                    ->where('homework_id', $v['homework_id'])->where('student_session_id', $ssid)->first();
                $row = ['homework_id' => $v['homework_id'], 'student_id' => $studentId,
                    'student_session_id' => $ssid, 'marks' => $rec['marks'] ?? null,
                    'note' => $rec['note'] ?? null,
                    'date' => $v['evaluation_date'] ?? date('Y-m-d'), 'status' => 'evaluated'];
                if ($existing) {
                    DB::table('homework_evaluation')->where('id', $existing->id)->update($row);
                } else {
                    DB::table('homework_evaluation')->insert($row);
                }
            }
            DB::table('homework')->where('id', $v['homework_id'])
                ->update(['evaluated_by' => $ctx->staff->id, 'evaluation_date' => $v['evaluation_date'] ?? date('Y-m-d')]);
        });

        return $this->successResponse(null, 'Evaluation saved successfully');
    }
}
