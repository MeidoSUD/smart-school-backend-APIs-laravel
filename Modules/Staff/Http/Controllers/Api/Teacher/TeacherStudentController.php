<?php

namespace Modules\Staff\Http\Controllers\Api\Teacher;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Modules\Staff\Services\TeacherRbac;
use Modules\Staff\Services\TeacherScope;

/**
 * طلاب فصول المعلم — parity مع:
 * - CI Student::getByClassAndSection + Student_model::searchByClassSectionWithSession
 *   (الجلسة الحالية + is_active=yes + ترتيب id)
 * - CI Student::disablestudentslist + Student_model::disablestudentByClassSection
 *   (الجلسة الحالية + is_active=no + ترتيب id + dis_reason/dis_note/disable_at)
 * - نطاق المعلم: class_section ضمن myClasses وإلا 403 (يقابل فلترة role_id==2 في CI)
 */
class TeacherStudentController extends TeacherController
{
    public function __construct()
    {
        $this->setControllerName('TeacherStudentController');
    }

    public function index(Request $request): JsonResponse
    {
        $ctx = $this->context($request);

        $validated = $request->validate([
            'class_id' => 'required_without:search|nullable|integer',
            'section_id' => 'required_with:class_id|nullable|integer',
            'search' => 'required_without:class_id|nullable|string|max:100',
            'page' => 'nullable|integer|min:1',
            'per_page' => 'nullable|integer|min:1|max:100',
        ]);

        $sessionId = $ctx->sessionId;
        $perPage = (int) ($validated['per_page'] ?? 25);

        if (! empty($validated['class_id'])) {
            $classSectionId = (int) DB::table('class_sections')
                ->where('class_id', $validated['class_id'])
                ->where('section_id', $validated['section_id'])
                ->value('id');

            if (! $classSectionId || ! TeacherScope::inScope($ctx->staff->id, $sessionId, $classSectionId)) {
                return $this->errorResponse('Class section is not in your scope', null, 403);
            }

            $query = DB::table('students')
                ->join('student_session', 'student_session.student_id', '=', 'students.id')
                ->leftJoin('classes', 'student_session.class_id', '=', 'classes.id')
                ->leftJoin('sections', 'sections.id', '=', 'student_session.section_id')
                ->leftJoin('categories', 'students.category_id', '=', 'categories.id')
                ->where('students.is_active', 'yes')
                ->where('student_session.class_id', $validated['class_id'])
                ->where('student_session.section_id', $validated['section_id'])
                ->when($sessionId, fn ($q) => $q->where('student_session.session_id', $sessionId))
                ->select(
                    'students.id', 'students.admission_no', 'students.roll_no',
                    'students.admission_date', 'students.firstname', 'students.middlename',
                    'students.lastname', 'students.image', 'students.mobileno', 'students.email',
                    'students.dob', 'students.gender', 'students.guardian_name',
                    'students.guardian_relation', 'students.guardian_phone',
                    'student_session.id as student_session_id',
                    'classes.class as class_name', 'sections.section as section_name',
                    'categories.category as category_name'
                )
                ->orderBy('students.id');
        } else {
            $csIds = collect(TeacherScope::myClasses((int) $ctx->staff->id, $sessionId))->pluck('class_section_id')->all();

            $query = DB::table('students')
                ->join('student_session', 'student_session.student_id', '=', 'students.id')
                ->join('class_sections', function ($j) {
                    $j->on('class_sections.class_id', '=', 'student_session.class_id')
                        ->on('class_sections.section_id', '=', 'student_session.section_id');
                })
                ->leftJoin('classes', 'student_session.class_id', '=', 'classes.id')
                ->leftJoin('sections', 'sections.id', '=', 'student_session.section_id')
                ->leftJoin('categories', 'students.category_id', '=', 'categories.id')
                ->where('students.is_active', 'yes')
                ->whereIn('class_sections.id', $csIds ?: [0])
                ->when($sessionId, fn ($q) => $q->where('student_session.session_id', $sessionId))
                ->select(
                    'students.id', 'students.admission_no', 'students.roll_no',
                    'students.admission_date', 'students.firstname', 'students.middlename',
                    'students.lastname', 'students.image', 'students.mobileno', 'students.email',
                    'students.dob', 'students.gender', 'students.guardian_name',
                    'students.guardian_relation', 'students.guardian_phone',
                    'student_session.id as student_session_id',
                    'classes.class as class_name', 'sections.section as section_name',
                    'categories.category as category_name'
                )
                ->orderBy('students.id');
        }

        if (! empty($validated['search'])) {
            $s = '%' . $validated['search'] . '%';
            $query->where(function ($q) use ($s) {
                $q->where('students.firstname', 'like', $s)
                    ->orWhere('students.lastname', 'like', $s)
                    ->orWhere('students.admission_no', 'like', $s)
                    ->orWhere('students.roll_no', 'like', $s);
            });
        }

        $paginator = $query->paginate($perPage);
        $items = collect($paginator->items())->map(function ($row) {
            $row->full_name = trim(implode(' ', array_filter([$row->firstname, $row->middlename, $row->lastname])));
            return $row;
        });

        return $this->successResponse([
            'students' => $items,
            'pagination' => [
                'total' => $paginator->total(),
                'per_page' => $paginator->perPage(),
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
            ],
        ]);
    }

    public function view(Request $request, int $id): JsonResponse
    {
        $ctx = $this->context($request);

        $sessionId = $ctx->sessionId;

        $student = DB::table('students')
            ->join('student_session', 'student_session.student_id', '=', 'students.id')
            ->leftJoin('classes', 'student_session.class_id', '=', 'classes.id')
            ->leftJoin('sections', 'sections.id', '=', 'student_session.section_id')
            ->where('students.id', $id)
            ->where('students.is_active', 'yes')
            ->when($sessionId, fn ($q) => $q->where('student_session.session_id', $sessionId))
            ->select(
                'students.*', 'student_session.id as student_session_id',
                'student_session.class_id', 'student_session.section_id',
                'classes.class as class_name', 'sections.section as section_name'
            )
            ->first();

        if (! $student) {
            return $this->errorResponse('Student not found', null, 404);
        }

        $classSectionId = (int) DB::table('class_sections')
            ->where('class_id', $student->class_id)
            ->where('section_id', $student->section_id)
            ->value('id');

        if (! $classSectionId || ! TeacherScope::inScope($ctx->staff->id, $sessionId, $classSectionId)) {
            return $this->errorResponse('Student is not in your scope', null, 403);
        }

        unset($student->password);
        $student->full_name = trim(implode(' ', array_filter([$student->firstname, $student->middlename, $student->lastname])));

        return $this->successResponse(['student' => $student]);
    }

    /**
     * طلاب معطلون (is_active = 'no') لفصول المعلم — read-only.
     * parity: CI Student::disablestudentslist (disable_student:can_view) → disablestudentByClassSection.
     */
    public function disabledStudents(Request $request): JsonResponse
    {
        $ctx = $this->context($request);

        if (! TeacherRbac::hasPrivilege((int) $ctx->staff->id, 'disable_student', 'can_view')) {
            return $this->errorResponse('Forbidden: missing disable_student can_view permission', null, 403);
        }

        $validated = $request->validate([
            'class_id' => 'required|integer',
            'section_id' => 'required|integer',
            'per_page' => 'nullable|integer|min:1|max:100',
        ]);

        $sessionId = $ctx->sessionId;
        $classSectionId = (int) DB::table('class_sections')
            ->where('class_id', $validated['class_id'])
            ->where('section_id', $validated['section_id'])
            ->value('id');

        if (! $classSectionId || ! TeacherScope::inScope($ctx->staff->id, $sessionId, $classSectionId)) {
            return $this->errorResponse('Class section is not in your scope', null, 403);
        }

        $paginator = DB::table('students')
            ->join('student_session', 'student_session.student_id', '=', 'students.id')
            ->join('classes', 'student_session.class_id', '=', 'classes.id')
            ->join('sections', 'sections.id', '=', 'student_session.section_id')
            ->leftJoin('categories', 'students.category_id', '=', 'categories.id')
            ->where('student_session.session_id', $sessionId)
            ->where('students.is_active', 'no')
            ->where('student_session.class_id', $validated['class_id'])
            ->where('student_session.section_id', $validated['section_id'])
            ->select(
                'students.id', 'students.admission_no', 'students.roll_no',
                'students.admission_date', 'students.firstname', 'students.middlename',
                'students.lastname', 'students.image', 'students.mobileno', 'students.email',
                'students.dob', 'students.gender', 'students.guardian_name',
                'students.guardian_phone', 'students.disable_at',
                'students.dis_reason', 'students.dis_note',
                'student_session.id as student_session_id',
                'classes.id as class_id', 'classes.class as class_name',
                'sections.id as section_id', 'sections.section as section_name',
                DB::raw("COALESCE(categories.category, '') as category_name")
            )
            ->orderBy('students.id')
            ->paginate((int) ($validated['per_page'] ?? 25));

        $items = collect($paginator->items())->map(function ($row) {
            $row->full_name = trim(implode(' ', array_filter([$row->firstname, $row->middlename, $row->lastname])));

            return $row;
        });

        return $this->successResponse([
            'students' => $items,
            'pagination' => [
                'total' => $paginator->total(),
                'per_page' => $paginator->perPage(),
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
            ],
        ]);
    }

    public function multiClass(Request $request): JsonResponse
    {
        $ctx = $this->context($request);

        if (! TeacherRbac::hasPrivilege((int) $ctx->staff->id, 'multi_class_student', 'can_view')) {
            return $this->errorResponse('Forbidden: missing multi_class_student can_view permission', null, 403);
        }

        $validated = $request->validate([
            'class_id' => 'required|integer',
            'section_id' => 'required|integer',
            'per_page' => 'nullable|integer|min:1|max:100',
        ]);

        $sessionId = $ctx->sessionId;
        $classSectionId = (int) DB::table('class_sections')
            ->where('class_id', $validated['class_id'])
            ->where('section_id', $validated['section_id'])
            ->value('id');

        if (! $classSectionId || ! TeacherScope::inScope($ctx->staff->id, $sessionId, $classSectionId)) {
            return $this->errorResponse('Class section is not in your scope', null, 403);
        }

        $studentIds = DB::table('student_session')
            ->where('class_id', $validated['class_id'])
            ->where('section_id', $validated['section_id'])
            ->where('session_id', $sessionId)
            ->pluck('student_id');

        $paginator = DB::table('students')
            ->join('student_session', 'student_session.student_id', '=', 'students.id')
            ->join('classes', 'student_session.class_id', '=', 'classes.id')
            ->join('sections', 'sections.id', '=', 'student_session.section_id')
            ->leftJoin('categories', 'students.category_id', '=', 'categories.id')
            ->where('student_session.session_id', $sessionId)
            ->where('students.is_active', 'yes')
            ->whereIn('students.id', $studentIds)
            ->where('student_session.class_id', $validated['class_id'])
            ->where('student_session.section_id', $validated['section_id'])
            ->select(
                'students.id', 'students.admission_no', 'students.roll_no',
                'students.admission_date', 'students.firstname', 'students.middlename',
                'students.lastname', 'students.image', 'students.mobileno', 'students.email',
                'students.dob', 'students.gender',
                'student_session.id as student_session_id',
                'classes.id as class_id', 'classes.class as class_name',
                'sections.id as section_id', 'sections.section as section_name',
                DB::raw("COALESCE(categories.category, '') as category_name")
            )
            ->orderBy('students.id')
            ->paginate((int) ($validated['per_page'] ?? 25));

        $studentList = $paginator->items();
        $itemStudentIds = collect($studentList)->pluck('id')->all();

        $allSessions = DB::table('student_session')
            ->join('classes', 'classes.id', '=', 'student_session.class_id')
            ->join('sections', 'sections.id', '=', 'student_session.section_id')
            ->whereIn('student_session.student_id', $itemStudentIds ?: [0])
            ->where('student_session.session_id', $sessionId)
            ->select(
                'student_session.id',
                'student_session.student_id',
                'student_session.class_id',
                'student_session.section_id',
                'classes.class as class_name',
                'sections.section as section_name',
                'student_session.default_login'
            )
            ->get()
            ->groupBy('student_id');

        $items = collect($studentList)->map(function ($row) use ($allSessions) {
            $row->full_name = trim(implode(' ', array_filter([$row->firstname, $row->middlename, $row->lastname])));
            $row->student_sessions = $allSessions->get($row->id, collect())->values();
            return $row;
        });

        return $this->successResponse([
            'students' => $items,
            'pagination' => [
                'total' => $paginator->total(),
                'per_page' => $paginator->perPage(),
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
            ],
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $ctx = $this->context($request);

        if (! TeacherRbac::hasPrivilege((int) $ctx->staff->id, 'student', 'can_add')) {
            return $this->errorResponse('Forbidden: missing student can_add permission', null, 403);
        }

        $v = $request->validate([
            'class_id' => 'required|integer',
            'section_id' => 'required|integer',
            'firstname' => 'required|string|max:100',
            'lastname' => 'nullable|string|max:100',
            'admission_no' => 'required|string|max:100|unique:students,admission_no',
            'gender' => 'required|string|max:20',
            'dob' => 'nullable|date',
            'mobileno' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:100',
            'guardian_name' => 'nullable|string|max:100',
            'guardian_phone' => 'nullable|string|max:50',
        ]);

        $csId = (int) DB::table('class_sections')
            ->where('class_id', $v['class_id'])
            ->where('section_id', $v['section_id'])
            ->value('id');

        if (! $csId || ! TeacherScope::inScope((int) $ctx->staff->id, $ctx->sessionId, $csId)) {
            return $this->errorResponse('Class section is not in your scope', null, 403);
        }

        $now = now();
        $studentId = DB::transaction(function () use ($v, $ctx, $now) {
            $sid = DB::table('students')->insertGetId([
                'admission_no' => $v['admission_no'],
                // CI parity: Student::create omits parent_id on initial insert
                // (NOT NULL col, no default → MySQL non-strict stores 0),
                // then links the parent user id via a follow-up update.
                'parent_id' => 0,
                // CI parity: the remaining NOT NULL-no-default columns are also
                // omitted by CI (non-strict MySQL stores ''/0 implicitly);
                // reproduced here explicitly since the dev DB runs strict mode.
                'blood_group' => '',
                'guardian_is' => '',
                'guardian_occupation' => '',
                'father_pic' => '',
                'mother_pic' => '',
                'guardian_pic' => '',
                'height' => '',
                'weight' => '',
                'dis_reason' => 0,
                'dis_note' => '',
                'firstname' => $v['firstname'],
                'lastname' => $v['lastname'] ?? null,
                'gender' => $v['gender'],
                'dob' => $v['dob'] ?? null,
                'mobileno' => $v['mobileno'] ?? null,
                'email' => $v['email'] ?? null,
                'guardian_name' => $v['guardian_name'] ?? null,
                'guardian_phone' => $v['guardian_phone'] ?? null,
                'is_active' => 'yes',
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            DB::table('student_session')->insert([
                'session_id' => $ctx->sessionId,
                'student_id' => $sid,
                'class_id' => $v['class_id'],
                'section_id' => $v['section_id'],
                'is_active' => 'yes',
                // CI parity: add_student_session omits is_alumni (implicit 0).
                'is_alumni' => 0,
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            return $sid;
        });

        return $this->successResponse(['student_id' => $studentId], 'Student admitted successfully');
    }

    public function bulkDelete(Request $request): JsonResponse
    {
        $ctx = $this->context($request);

        if (! TeacherRbac::hasPrivilege((int) $ctx->staff->id, 'student', 'can_delete')) {
            return $this->errorResponse('Forbidden: missing student can_delete permission', null, 403);
        }

        $v = $request->validate([
            'student_ids' => 'required|array|min:1',
            'student_ids.*' => 'required|integer',
        ]);

        $deleted = DB::table('students')
            ->whereIn('id', $v['student_ids'])
            ->update(['is_active' => 'no', 'updated_at' => now()]);

        return $this->successResponse(['deleted_count' => $deleted], 'Students deactivated successfully');
    }
}


