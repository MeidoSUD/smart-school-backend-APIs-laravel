<?php

namespace Modules\Staff\Http\Controllers\Api\Teacher;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Modules\Staff\Services\TeacherRbac;
use Modules\Staff\Services\TeacherScope;

/**
 * سلوك الطلاب بمنظور المعلم — parity مع CodeIgniter (المصدر الوحيد للحقيقة):
 * - behaviour/Incidents.php: index (can_view) + create + dtincident + get + edit + delete
 *   عبر Studentbehaviour_model (get/add/incident/delete) + views incidentlist/_incidentedit.
 * - behaviour/Studentincidents.php: index/search/dtassignincidentlist/create/
 *   viewassignedincidentslist/delete عبر Studentincidents_model (studentbehaviour/
 *   totalpoints/add/delete) + view assignincidentlist.
 *
 * الصلاحيات (RBAC الدقيق مثل CI Rbac::hasPrivilege):
 * - behaviour_records_incident: can_view/add/edit/delete (الدور 2: 1/1/1/1).
 * - behaviour_records_assign_incident: can_view/add/delete (الدور 2: 1/1/0/1).
 * Super Admin يتجاوز الكل (مثل CI).
 *
 * النطاق: تعريفات الحوادث عامة (بلا فلترة فصل مثل CI)، أما إسناد/عرض حوادث
 * الطلاب فيُقيّد بفصول المعلم عبر TeacherScope (تقوية موثقة فوق CI).
 */
class TeacherBehaviourController extends TeacherController
{
    public function __construct()
    {
        $this->setControllerName('TeacherBehaviourController');
    }

    private function denyUnless(int $staffId, string $category, string $perm): ?JsonResponse
    {
        if (! TeacherRbac::hasPrivilege($staffId, $category, $perm)) {
            return $this->errorResponse('Forbidden: missing '.$category.' '.$perm.' permission', null, 403);
        }

        return null;
    }

    /** parity مع Incidents::get() — فكّ البادئة السالبة. */
    private function splitPoint(?string $raw): array
    {
        $raw = (string) ($raw ?? '');
        if (str_starts_with($raw, '-')) {
            return [ltrim($raw, '-'), 1];
        }

        return [$raw, 0];
    }

    private function toIncidentResource(object $row, bool $canEdit, bool $canDelete): array
    {
        [$absPoint, $negative] = $this->splitPoint($row->point ?? null);

        return [
            'id' => (int) $row->id,
            'title' => $row->title,
            'point' => $absPoint,
            'raw_point' => $row->point,
            'negative_incident' => $negative,
            'is_negative' => $negative === 1,
            'description' => $row->description,
            'can_edit' => $canEdit,
            'can_delete' => $canDelete,
        ];
    }

    // ---------- Incident definitions (behaviour/incidents) ----------

    /** parity مع Incidents::index + dtincident (قائمة + أزرار حسب can_edit/can_delete). */
    public function incidents(Request $request): JsonResponse
    {
        $ctx = $this->context($request);
        $staffId = (int) $ctx->staff->id;

        if ($deny = $this->denyUnless($staffId, 'behaviour_records_incident', 'can_view')) {
            return $deny;
        }

        $v = $request->validate([
            'search' => 'nullable|string|max:100',
            'page' => 'nullable|integer|min:1',
            'per_page' => 'nullable|integer|min:1|max:100',
        ]);

        $canEdit = TeacherRbac::hasPrivilege($staffId, 'behaviour_records_incident', 'can_edit');
        $canDelete = TeacherRbac::hasPrivilege($staffId, 'behaviour_records_incident', 'can_delete');
        $canAdd = TeacherRbac::hasPrivilege($staffId, 'behaviour_records_incident', 'can_add');

        $query = DB::table('student_behaviour')
            ->select('id', 'title', 'point', 'description')
            ->orderByDesc('id');

        if (! empty($v['search'])) {
            $s = '%'.$v['search'].'%';
            $query->where(fn ($q) => $q->where('title', 'like', $s)
                ->orWhere('point', 'like', $s)
                ->orWhere('description', 'like', $s));
        }

        $paginator = $query->paginate((int) ($v['per_page'] ?? 25));
        $items = collect($paginator->items())
            ->map(fn ($row) => $this->toIncidentResource($row, $canEdit, $canDelete));

        return $this->successResponse([
            'incidents' => $items,
            'permissions' => ['can_view' => true, 'can_add' => $canAdd, 'can_edit' => $canEdit, 'can_delete' => $canDelete],
            'pagination' => [
                'total' => $paginator->total(),
                'per_page' => $paginator->perPage(),
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
            ],
        ]);
    }

    /** parity مع Incidents::create (title/point/description مطلوبة + negative_incident → ‎-point). */
    public function storeIncident(Request $request): JsonResponse
    {
        $ctx = $this->context($request);
        $staffId = (int) $ctx->staff->id;

        if ($deny = $this->denyUnless($staffId, 'behaviour_records_incident', 'can_add')) {
            return $deny;
        }

        $v = $request->validate([
            'title' => 'required|string|max:255',
            'point' => 'required|numeric',
            'description' => 'required|string',
            'negative_incident' => 'nullable|boolean',
        ]);

        $point = (string) $v['point'];
        if (! empty($v['negative_incident'])) {
            $point = '-'.ltrim($point, '-');
        }

        $id = DB::table('student_behaviour')->insertGetId([
            'title' => $v['title'],
            'point' => $point,
            'description' => $v['description'],
        ]);

        return $this->successResponse(['id' => $id], 'Incident created successfully', 201);
    }

    /** parity مع Incidents::get (سجل واحد + فكّ السالب مثل _incidentedit). */
    public function showIncident(Request $request, int $id): JsonResponse
    {
        $ctx = $this->context($request);

        if ($deny = $this->denyUnless((int) $ctx->staff->id, 'behaviour_records_incident', 'can_view')) {
            return $deny;
        }

        $row = DB::table('student_behaviour')->where('id', $id)->first();
        if (! $row) {
            return $this->errorResponse('Incident not found', null, 404);
        }

        [$absPoint, $negative] = $this->splitPoint($row->point ?? null);

        return $this->successResponse(['incident' => [
            'id' => (int) $row->id,
            'title' => $row->title,
            'point' => $absPoint,
            'raw_point' => $row->point,
            'negative_incident' => $negative,
            'description' => $row->description,
        ]]);
    }

    /** parity مع Incidents::edit (نفس تحقق create + incident_id). */
    public function updateIncident(Request $request, int $id): JsonResponse
    {
        $ctx = $this->context($request);
        $staffId = (int) $ctx->staff->id;

        if ($deny = $this->denyUnless($staffId, 'behaviour_records_incident', 'can_edit')) {
            return $deny;
        }

        $exists = DB::table('student_behaviour')->where('id', $id)->exists();
        if (! $exists) {
            return $this->errorResponse('Incident not found', null, 404);
        }

        $v = $request->validate([
            'title' => 'required|string|max:255',
            'point' => 'required|numeric',
            'description' => 'required|string',
            'negative_incident' => 'nullable|boolean',
        ]);

        $point = (string) $v['point'];
        if (! empty($v['negative_incident'])) {
            $point = '-'.ltrim($point, '-');
        }

        DB::table('student_behaviour')->where('id', $id)->update([
            'title' => $v['title'],
            'point' => $point,
            'description' => $v['description'],
        ]);

        return $this->successResponse(['id' => $id], 'Incident updated successfully');
    }

    /**
     * parity مع Studentbehaviour_model::delete (حذف الإسنادات أولاً ثم التعريف — transaction).
     */
    public function destroyIncident(Request $request, int $id): JsonResponse
    {
        $ctx = $this->context($request);

        if ($deny = $this->denyUnless((int) $ctx->staff->id, 'behaviour_records_incident', 'can_delete')) {
            return $deny;
        }

        $exists = DB::table('student_behaviour')->where('id', $id)->exists();
        if (! $exists) {
            return $this->errorResponse('Incident not found', null, 404);
        }

        DB::transaction(function () use ($id) {
            DB::table('student_incidents')->where('incident_id', $id)->delete();
            DB::table('student_behaviour')->where('id', $id)->delete();
        });

        return $this->successResponse(null, 'Incident deleted successfully');
    }

    // ---------- Assign incidents to students (behaviour/studentincidents) ----------

    /**
     * parity مع Studentincidents::dtassignincidentlist (طلاب فصل + total_points للجلسة الحالية).
     * class_id + section_id مطلوبان ويجب أن يكونا ضمن نطاق المعلم (403 خارجه).
     */
    public function assignableStudents(Request $request): JsonResponse
    {
        $ctx = $this->context($request);
        $staffId = (int) $ctx->staff->id;

        if ($deny = $this->denyUnless($staffId, 'behaviour_records_assign_incident', 'can_view')) {
            return $deny;
        }

        $v = $request->validate([
            'class_id' => 'required|integer',
            'section_id' => 'required|integer',
            'search' => 'nullable|string|max:100',
            'page' => 'nullable|integer|min:1',
            'per_page' => 'nullable|integer|min:1|max:100',
        ]);

        $classSectionId = (int) DB::table('class_sections')
            ->where('class_id', $v['class_id'])
            ->where('section_id', $v['section_id'])
            ->value('id');

        if (! $classSectionId || ! TeacherScope::inScope($staffId, $ctx->sessionId, $classSectionId)) {
            return $this->errorResponse('Class section is not in your scope', null, 403);
        }

        // parity مع Customlib::getFullName: احترام إعدادات sch_settings (middlename/lastname).
        $sch = DB::table('sch_settings')->first(['middlename', 'lastname']);
        $useMiddle = $sch === null ? true : (bool) $sch->middlename;
        $useLast = $sch === null ? true : (bool) $sch->lastname;

        $canAdd = TeacherRbac::hasPrivilege($staffId, 'behaviour_records_assign_incident', 'can_add');

        $query = DB::table('students')
            ->join('student_session', 'student_session.student_id', '=', 'students.id')
            ->leftJoin('classes', 'classes.id', '=', 'student_session.class_id')
            ->leftJoin('sections', 'sections.id', '=', 'student_session.section_id')
            ->where('students.is_active', 'yes')
            ->where('student_session.session_id', $ctx->sessionId)
            ->where('student_session.class_id', $v['class_id'])
            ->where('student_session.section_id', $v['section_id'])
            ->select(
                'students.id', 'students.admission_no', 'students.firstname',
                'students.middlename', 'students.lastname', 'students.gender',
                'students.mobileno', 'classes.class as class_name',
                'sections.section as section_name'
            )
            // parity مع Student_model::searchdtByClassSection sort(admission_no asc).
            ->orderBy('students.admission_no')
            ->orderBy('students.id');

        if (! empty($v['search'])) {
            $s = '%'.$v['search'].'%';
            $query->where(fn ($q) => $q->where('students.firstname', 'like', $s)
                ->orWhere('students.lastname', 'like', $s)
                ->orWhere('students.admission_no', 'like', $s));
        }

        $paginator = $query->paginate((int) ($v['per_page'] ?? 25));
        $items = collect($paginator->items())->map(function ($row) use ($ctx, $useMiddle, $useLast) {
            $total = DB::table('student_incidents')
                ->join('student_behaviour', 'student_behaviour.id', '=', 'student_incidents.incident_id')
                ->where('student_incidents.student_id', $row->id)
                ->where('student_incidents.session_id', $ctx->sessionId)
                ->sum('student_behaviour.point');

            // parity مع Customlib::getFullName(first, middle, last, is_middle, is_last).
            $parts = [$row->firstname];
            if ($useMiddle && ($row->middlename ?? '') !== '') {
                $parts[] = $row->middlename;
            }
            if ($useLast && ($row->lastname ?? '') !== '') {
                $parts[] = $row->lastname;
            }
            $row->full_name = trim(implode(' ', array_filter($parts)));
            $row->total_points = (int) $total;

            return $row;
        });

        return $this->successResponse([
            'students' => $items,
            // parity مع CI أزرار can_add/can_view في dtassignincidentlist + incidents().
            'permissions' => ['can_view' => true, 'can_add' => $canAdd],
            'pagination' => [
                'total' => $paginator->total(),
                'per_page' => $paginator->perPage(),
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
            ],
        ]);
    }

    /**
     * parity مع Studentincidents::create (incident_id[] مطلوب + session الحالية + assign_by).
     * الطالب يجب أن يكون ضمن نطاق المعلم.
     */
    public function assignIncident(Request $request): JsonResponse
    {
        $ctx = $this->context($request);
        $staffId = (int) $ctx->staff->id;

        if ($deny = $this->denyUnless($staffId, 'behaviour_records_assign_incident', 'can_add')) {
            return $deny;
        }

        $v = $request->validate([
            'student_id' => 'required|integer|exists:students,id',
            'incident_ids' => 'required|array|min:1',
            'incident_ids.*' => 'integer|exists:student_behaviour,id',
            // قبول الاسم القديم incident_id[] من CI أيضاً
            'incident_id' => 'nullable|array|min:1',
            'incident_id.*' => 'integer|exists:student_behaviour,id',
        ]);

        $incidentIds = $v['incident_ids'] ?? $v['incident_id'] ?? [];
        if ($incidentIds === []) {
            return $this->errorResponse('At least one incident must be selected', null, 422);
        }

        if (! $this->studentInScope($staffId, $ctx->sessionId, (int) $v['student_id'])) {
            return $this->errorResponse('Student is not in your scope', null, 403);
        }

        $ids = [];
        foreach ($incidentIds as $incidentId) {
            $ids[] = DB::table('student_incidents')->insertGetId([
                'student_id' => $v['student_id'],
                'session_id' => $ctx->sessionId,
                'incident_id' => $incidentId,
                'assign_by' => $staffId,
                'created_at' => now(),
            ]);
        }

        return $this->successResponse(['ids' => $ids, 'student_id' => (int) $v['student_id']], 'Incident assigned successfully', 201);
    }

    /**
     * parity مع Studentincidents::viewassignedincidentslist +
     * Studentincidents_model::studentbehaviour (الجلسة الحالية + totalcomments).
     */
    public function studentIncidents(Request $request, int $studentId): JsonResponse
    {
        $ctx = $this->context($request);

        if ($deny = $this->denyUnless((int) $ctx->staff->id, 'behaviour_records_assign_incident', 'can_view')) {
            return $deny;
        }

        if (! $this->studentInScope((int) $ctx->staff->id, $ctx->sessionId, $studentId)) {
            return $this->errorResponse('Student is not in your scope', null, 403);
        }

        $rows = DB::table('student_incidents')
            ->join('students', 'students.id', '=', 'student_incidents.student_id')
            ->join('student_behaviour', 'student_behaviour.id', '=', 'student_incidents.incident_id')
            ->join('sessions', 'sessions.id', '=', 'student_incidents.session_id')
            ->leftJoin('staff', 'staff.id', '=', 'student_incidents.assign_by')
            ->where('students.is_active', 'yes')
            ->where('student_incidents.session_id', $ctx->sessionId)
            ->where('student_incidents.student_id', $studentId)
            ->orderByDesc('student_incidents.id')
            ->select(
                'student_incidents.id', 'student_incidents.created_at',
                'student_behaviour.id as incident_id', 'student_behaviour.title',
                'student_behaviour.point', 'student_behaviour.description',
                'sessions.session as session_name',
                'staff.name as staff_name', 'staff.surname as staff_surname',
                'staff.employee_id as staff_employee_id'
            )
            ->get();

        $items = $rows->map(function ($row) {
            [$absPoint, $negative] = $this->splitPoint($row->point ?? null);
            $row->point_abs = $absPoint;
            $row->negative_incident = $negative;
            $row->totalcomments = (int) DB::table('student_incident_comments')
                ->where('student_incident_id', $row->id)->count();

            return $row;
        })->values();

        $totalPoints = DB::table('student_incidents')
            ->join('student_behaviour', 'student_behaviour.id', '=', 'student_incidents.incident_id')
            ->where('student_incidents.student_id', $studentId)
            ->where('student_incidents.session_id', $ctx->sessionId)
            ->sum('student_behaviour.point');

        return $this->successResponse([
            'incidents' => $items,
            'total_points' => (int) $totalPoints,
            'student_id' => $studentId,
        ]);
    }

    /**
     * parity مع Studentincidents::delete. ملاحظة أمنية: CI بلا فحص RBAC هنا،
     * لكن Laravel يفرض can_delete (تقوية — لا نُضعف الحماية لتقليد المصدر).
     */
    public function unassignIncident(Request $request, int $assignedId): JsonResponse
    {
        $ctx = $this->context($request);

        if ($deny = $this->denyUnless((int) $ctx->staff->id, 'behaviour_records_assign_incident', 'can_delete')) {
            return $deny;
        }

        $row = DB::table('student_incidents')->where('id', $assignedId)->first();
        if (! $row) {
            return $this->errorResponse('Assigned incident not found', null, 404);
        }

        if (! $this->studentInScope((int) $ctx->staff->id, $ctx->sessionId, (int) $row->student_id)) {
            return $this->errorResponse('Student is not in your scope', null, 403);
        }

        DB::table('student_incidents')->where('id', $assignedId)->delete();

        return $this->successResponse(['student_id' => (int) $row->student_id], 'Assigned incident deleted successfully');
    }

    private function studentInScope(int $staffId, int $sessionId, int $studentId): bool
    {
        $ss = DB::table('student_session')
            ->where('student_id', $studentId)
            ->where('session_id', $sessionId)
            ->first(['class_id', 'section_id']);

        if (! $ss) {
            return false;
        }

        $classSectionId = (int) DB::table('class_sections')
            ->where('class_id', $ss->class_id)
            ->where('section_id', $ss->section_id)
            ->value('id');

        return $classSectionId > 0 && TeacherScope::inScope($staffId, $sessionId, $classSectionId);
    }
}
