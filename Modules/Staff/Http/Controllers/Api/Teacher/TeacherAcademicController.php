<?php

declare(strict_types=1);

namespace Modules\Staff\Http\Controllers\Api\Teacher;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Modules\Staff\Services\TeacherRbac;
use Modules\Staff\Services\TeacherScope;

/**
 * قوائم الأكاديميات للمعلم (قراءة فقط) — parity مع:
 * - Classes::index           (class:can_view)            → classes
 * - Sections::index / getByClass (section:can_view)       → sections
 * - Subject::index / view    (subject:can_view)          → subjects
 * - Disable_reason::index / get_details (disable_reason:can_view) → disable_reason
 *
 * النطاق: الفصول والأقسام مقيّدة بفصول المعلم (TeacherScope::myClasses)،
 * والمواد بمواد المعلم في teacher_subjects للجلسة الحالية.
 * أسباب التعطيل مرجع عام (CI يعرض الكل)، وتُعرض للقراءة فقط.
 */
class TeacherAcademicController extends TeacherController
{
    public function __construct()
    {
        $this->setControllerName('TeacherAcademicController');
    }

    public function classes(Request $request): JsonResponse
    {
        $ctx = $this->context($request);

        $rows = collect(TeacherScope::myClasses((int) $ctx->staff->id, $ctx->sessionId))
            ->unique('class_id')
            ->values()
            ->map(fn (array $r): array => ['id' => $r['class_id'], 'class' => $r['class_name']])
            ->all();

        return $this->successResponse(['classes' => $rows]);
    }

    public function sections(Request $request): JsonResponse
    {
        $ctx = $this->context($request);

        $v = $request->validate(['class_id' => 'nullable|integer']);

        $rows = collect(TeacherScope::myClasses((int) $ctx->staff->id, $ctx->sessionId))
            ->when(! empty($v['class_id']), fn ($c) => $c->where('class_id', (int) $v['class_id']))
            ->values()
            ->map(fn (array $r): array => [
                'class_section_id' => $r['class_section_id'],
                'class_id' => $r['class_id'],
                'section_id' => $r['section_id'],
                'section' => $r['section_name'],
                'is_class_teacher' => $r['is_class_teacher'],
            ])
            ->all();

        return $this->successResponse(['sections' => $rows]);
    }

    public function subjects(Request $request): JsonResponse
    {
        $ctx = $this->context($request);

        $perPage = (int) $request->input('per_page', 25);
        if ($perPage <= 0 || $perPage > 100) {
            $perPage = 25;
        }

        $subjectIds = TeacherScope::subjectIds((int) $ctx->staff->id, $ctx->sessionId);

        $query = DB::table('subjects')
            ->select('id', 'name', 'code', 'type')
            ->orderBy('id');

        if (! empty($subjectIds)) {
            $query->whereIn('id', $subjectIds);
        } else {
            $isRestricted = DB::table('sch_settings')
                ->where(function ($q) {
                    $q->where('class_teacher', 'yes')
                        ->orWhere('class_teacher', '1');
                })
                ->exists();

            if ($isRestricted) {
                $query->whereRaw('1 = 0');
            }
        }

        $search = $request->input('search');
        if (! empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%");
            });
        }

        $rows = $query->paginate($perPage);

        return $this->successResponse(['subjects' => $rows]);
    }

    public function disableReasons(Request $request): JsonResponse
    {
        $this->context($request);

        $rows = DB::table('disable_reason')->select('id', 'reason')->orderBy('id')->get();

        return $this->successResponse(['disable_reasons' => $rows]);
    }

    public function disableReasonDetails(Request $request, int $id): JsonResponse
    {
        $this->context($request);

        $row = DB::table('disable_reason')->select('id', 'reason')->where('id', $id)->first();

        if (! $row) {
            return $this->errorResponse('Disable reason not found', null, 404);
        }

        return $this->successResponse(['disable_reason' => $row]);
    }

    public function subjectGroups(Request $request): JsonResponse
    {
        $ctx = $this->context($request);

        $v = $request->validate([
            'class_id' => 'nullable|integer',
            'section_id' => 'nullable|integer',
        ]);

        $myClassSectionIds = collect(TeacherScope::myClasses((int) $ctx->staff->id, $ctx->sessionId))
            ->pluck('class_section_id')
            ->map(fn ($id) => (int) $id)
            ->all();

        $targetCsIds = $myClassSectionIds;
        if (! empty($v['class_id']) && ! empty($v['section_id'])) {
            $csId = (int) DB::table('class_sections')
                ->where('class_id', $v['class_id'])
                ->where('section_id', $v['section_id'])
                ->value('id');
            if (! $csId || ! in_array($csId, $myClassSectionIds, true)) {
                return $this->errorResponse('Class section is not in your scope', null, 403);
            }
            $targetCsIds = [$csId];
        }

        $groupIds = DB::table('subject_group_class_sections')
            ->whereIn('class_section_id', $targetCsIds ?: [0])
            ->where('session_id', $ctx->sessionId)
            ->pluck('subject_group_id')
            ->unique()
            ->values()
            ->all();

        $groups = DB::table('subject_groups')
            ->whereIn('id', $groupIds ?: [0])
            ->get();

        $groupSubjects = DB::table('subject_group_subjects')
            ->join('subjects', 'subjects.id', '=', 'subject_group_subjects.subject_id')
            ->whereIn('subject_group_subjects.subject_group_id', $groupIds ?: [0])
            ->where('subject_group_subjects.session_id', $ctx->sessionId)
            ->select('subject_group_subjects.*', 'subjects.name as subject_name', 'subjects.code as subject_code', 'subjects.type as subject_type')
            ->get()
            ->groupBy('subject_group_id');

        $result = $groups->map(function ($g) use ($groupSubjects) {
            $g->subjects = $groupSubjects->get($g->id, collect())->values();
            return $g;
        });

        return $this->successResponse(['subject_groups' => $result]);
    }

    public function classTeachers(Request $request): JsonResponse
    {
        $ctx = $this->context($request);

        $v = $request->validate([
            'class_id' => 'nullable|integer',
            'section_id' => 'nullable|integer',
        ]);

        $query = DB::table('class_teacher')
            ->join('classes', 'classes.id', '=', 'class_teacher.class_id')
            ->join('sections', 'sections.id', '=', 'class_teacher.section_id')
            ->join('staff', 'staff.id', '=', 'class_teacher.staff_id')
            ->where('class_teacher.session_id', $ctx->sessionId)
            ->when(! empty($v['class_id']), fn ($q) => $q->where('class_teacher.class_id', $v['class_id']))
            ->when(! empty($v['section_id']), fn ($q) => $q->where('class_teacher.section_id', $v['section_id']))
            ->select(
                'class_teacher.id',
                'class_teacher.class_id',
                'class_teacher.section_id',
                'class_teacher.staff_id',
                'classes.class as class_name',
                'sections.section as section_name',
                'staff.name as staff_name',
                'staff.surname as staff_surname',
                'staff.employee_id'
            )
            ->orderBy('classes.id')
            ->orderBy('sections.id');

        $rows = $query->get()->map(function ($r) {
            $r->teacher_name = trim($r->staff_name . ' ' . $r->staff_surname);
            return $r;
        });

        return $this->successResponse(['class_teachers' => $rows]);
    }

    public function assignClassTeacher(Request $request): JsonResponse
    {
        $ctx = $this->context($request);

        if (! TeacherRbac::hasPrivilege((int) $ctx->staff->id, 'assign_class_teacher', 'can_edit')) {
            return $this->errorResponse('Forbidden: missing assign_class_teacher can_edit permission', null, 403);
        }

        $v = $request->validate([
            'class_id' => 'required|integer',
            'section_id' => 'required|integer',
            'teachers' => 'required|array|min:1',
            'teachers.*' => 'required|integer',
        ]);

        DB::table('class_teacher')
            ->where('session_id', $ctx->sessionId)
            ->where('class_id', $v['class_id'])
            ->where('section_id', $v['section_id'])
            ->delete();

        foreach ($v['teachers'] as $staffId) {
            DB::table('class_teacher')->insert([
                'session_id' => $ctx->sessionId,
                'class_id' => $v['class_id'],
                'section_id' => $v['section_id'],
                'staff_id' => $staffId,
            ]);
        }

        return $this->successResponse(null, 'Class teacher assigned successfully');
    }
}


