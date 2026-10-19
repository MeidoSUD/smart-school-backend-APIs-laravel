<?php

namespace Modules\Staff\Http\Controllers\Api\Teacher;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Modules\Staff\Services\TeacherRbac;
use Modules\Staff\Services\TeacherScope;

/**
 * تقارير السلوك بمنظور المعلم — parity مع CodeIgniter (المصدر الوحيد للحقيقة):
 * - behaviour/Report.php: index (tabs) + studentincidentreport/search/dtstudentlist/assignstudent
 *   + studentbehaviorsrankreport/studentranksearch/dtstudentranklist/assignstudentrank
 *   + classwiserankreport/classwisepoint + classsectionwiserank/classsectionwisepoint
 *   + housewiserank/housewisepoint + incidentwisereport/studentdetails
 *   عبر Studentincidents_model (assignstudent/totalpointsbysession/studentrank/classwiserank/
 *   classwisepoint/classsectionwiserank/classsectionwisepoint/housewiserank/housewisepoint/
 *   pointbystudent/studentcount) + Student_model::searchdtByClassSection + views report/*.
 *
 * الصلاحيات (RBAC الدقيق مثل CI Rbac::hasPrivilege):
 * - student_incident_report / student_behaviour_rank_report / class_wise_rank_report /
 *   class_section_wise_rank_report / house_wise_rank_report / incident_wise_report : can_view.
 * Super Admin يتجاوز الكل (مثل CI).
 *
 * النطاق (تقوية موثقة فوق CI): كل القوائم مُقيّدة بفصول المعلم عبر TeacherScope
 * (CI يقيّد فقط عندما role_id=2 + علم class_teacher، وإلا بلا تقييد).
 * session=current تعني الجلسة الحالية فقط على student_incidents (مثل CI)،
 * و overall تعني كل الجلسات (student_session تبقى على الحالية مثل CI).
 * الخصوصية: إخفاء اسم المُسند SuperAdmin عندما superadmin_restriction=disabled (مثل CI).
 */
class TeacherBehaviourReportController extends TeacherController
{
    public function __construct()
    {
        $this->setControllerName('TeacherBehaviourReportController');
    }

    private function denyUnless(int $staffId, string $category): ?JsonResponse
    {
        if (! TeacherRbac::hasPrivilege($staffId, $category, 'can_view')) {
            return $this->errorResponse('Forbidden: missing '.$category.' can_view permission', null, 403);
        }

        return null;
    }

    /** current|current_session => current ، overall|all => overall (مثل CI search). */
    private function sessionValue(?string $raw): string
    {
        $raw = strtolower(trim((string) ($raw ?? 'current')));

        return in_array($raw, ['overall', 'all', 'all_session'], true) ? 'overall' : 'current_session';
    }

    /** أزواج فصول المعلم الحالية [[class_id, section_id]]. */
    private function myPairs(int $staffId, int $sessionId): array
    {
        return collect(TeacherScope::myClasses($staffId, $sessionId))
            ->map(fn ($r) => [(int) $r['class_id'], (int) $r['section_id']])
            ->values()->all();
    }

    /** تقييد استعلام student_session بأزواج المعلم. */
    private function scopePairs($query, array $pairs, string $table = 'student_session')
    {
        return $query->where(function ($q) use ($pairs, $table) {
            foreach ($pairs as [$c, $s]) {
                $q->orWhere(function ($qq) use ($c, $s, $table) {
                    $qq->where($table.'.class_id', $c)->where($table.'.section_id', $s);
                });
            }
        });
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
        $cs = (int) DB::table('class_sections')
            ->where('class_id', $ss->class_id)->where('section_id', $ss->section_id)->value('id');

        return $cs > 0 && TeacherScope::inScope($staffId, $sessionId, $cs);
    }

    /** parity مع Customlib::getFullName. */
    private function fullName(object $row, bool $useMiddle, bool $useLast): string
    {
        $parts = [$row->firstname ?? ''];
        if ($useMiddle && (($row->middlename ?? '') !== '')) {
            $parts[] = $row->middlename;
        }
        if ($useLast && (($row->lastname ?? '') !== '')) {
            $parts[] = $row->lastname;
        }

        return trim(implode(' ', array_filter($parts)));
    }

    private function nameSettings(): array
    {
        $sch = DB::table('sch_settings')->first(['middlename', 'lastname', 'date_format', 'superadmin_restriction']);

        return [
            (bool) ($sch->middlename ?? true),
            (bool) ($sch->lastname ?? true),
            (string) ($sch->date_format ?? 'd/m/Y'),
            (string) ($sch->superadmin_restriction ?? 'no'),
        ];
    }

    /** parity مع Customlib::dateformat (مجموعة الصيغ المدعومة في CI). */
    private function dateFormat(?string $raw, string $format): string
    {
        if (! $raw || trim($raw) === '' || substr($raw, 0, 10) === '0000-00-00') {
            return '';
        }
        $ts = strtotime(substr($raw, 0, 10));
        if ($ts === false) {
            return '';
        }
        $map = [
            'd-m-Y' => 'd-m-Y', 'd/m/Y' => 'd/m/Y', 'd-M-Y' => 'd-M-Y',
            'd.m.Y' => 'd.m.Y', 'm-d-Y' => 'm-d-Y', 'm/d/Y' => 'm/d/Y',
            'Y-m-d' => 'Y-m-d', 'Y/m/d' => 'Y/m/d',
        ];

        return date($map[$format] ?? 'd/m/Y', $ts);
    }

    /** إخفاء اسم SuperAdmin حسب القيد (مثل views report). */
    private function staffName(?object $row, ?int $roleId, string $restriction): string
    {
        if ($restriction === 'disabled' && (int) $roleId === 7) {
            return '';
        }
        $name = trim((($row->staff_name ?? '') ?? '').' '.(($row->staff_surname ?? '') ?? ''));
        if (($row->staff_employee_id ?? '') !== '') {
            $name .= ' ('.$row->staff_employee_id.')';
        }

        return trim($name);
    }

    /** تفاصيل حوادث طالب — parity مع assignstudent() (يُستخدم لتقريرين). */
    private function incidentDetails(int $studentId, string $sessionValue, int $sessionId, string $dateFormat, string $restriction): array
    {
        $q = DB::table('student_incidents')
            ->join('students', 'students.id', '=', 'student_incidents.student_id')
            ->join('student_behaviour', 'student_behaviour.id', '=', 'student_incidents.incident_id')
            ->join('sessions', 'sessions.id', '=', 'student_incidents.session_id')
            ->join('staff', 'staff.id', '=', 'student_incidents.assign_by')
            ->leftJoin('staff_roles', 'staff_roles.staff_id', '=', 'staff.id')
            ->where('students.is_active', 'yes')
            ->where('student_incidents.student_id', $studentId)
            ->orderByDesc('student_incidents.id')
            ->select(
                'student_behaviour.title', 'student_behaviour.point', 'student_behaviour.description',
                'student_incidents.created_at', 'sessions.session',
                'staff.name as staff_name', 'staff.surname as staff_surname',
                'staff.employee_id as staff_employee_id', 'staff_roles.role_id'
            );
        if ($sessionValue === 'current_session') {
            $q->where('student_incidents.session_id', $sessionId);
        }

        return $q->get()->map(function ($r) use ($dateFormat, $restriction) {
            return [
                'title' => $r->title,
                'point' => $r->point,
                'session' => $r->session,
                'created_at' => $r->created_at,
                'date' => $this->dateFormat($r->created_at, $dateFormat),
                'description' => $r->description,
                'assign_by' => $this->staffName($r, isset($r->role_id) ? (int) $r->role_id : null, $restriction),
                'assigner_role_id' => isset($r->role_id) ? (int) $r->role_id : null,
            ];
        })->values()->all();
    }

    // ---------- لوحة التقارير (parity مع index + _behaviour_report) ----------

    public function index(Request $request): JsonResponse
    {
        $ctx = $this->context($request);
        $staffId = (int) $ctx->staff->id;
        $cats = [
            'student_incident_report' => 'student_incident_report',
            'student_behaviour_rank_report' => 'student_behaviour_rank_report',
            'class_wise_rank_report' => 'class_wise_rank_report',
            'class_section_wise_rank_report' => 'class_section_wise_rank_report',
            'house_wise_rank_report' => 'house_wise_rank_report',
            'incident_wise_report' => 'incident_wise_report',
        ];
        $reports = [];
        foreach ($cats as $key => $cat) {
            $reports[$key] = TeacherRbac::hasPrivilege($staffId, $cat, 'can_view');
        }

        return $this->successResponse(['reports' => $reports]);
    }

    // ---------- تقرير حوادث الطلاب (parity مع dtstudentlist) ----------

    public function studentIncidents(Request $request): JsonResponse
    {
        $ctx = $this->context($request);
        $staffId = (int) $ctx->staff->id;
        if ($deny = $this->denyUnless($staffId, 'student_incident_report')) {
            return $deny;
        }
        $v = $request->validate([
            'class_id' => 'nullable|integer', 'section_id' => 'nullable|integer',
            'session' => 'nullable|string|max:30', 'search' => 'nullable|string|max:100',
            'page' => 'nullable|integer|min:1', 'per_page' => 'nullable|integer|min:1|max:100',
        ]);
        $sessionValue = $this->sessionValue($v['session'] ?? null);
        $pairs = $this->myPairs($staffId, $ctx->sessionId);
        if ($pairs === []) {
            return $this->successResponse(['students' => [], 'session' => $sessionValue,
                'pagination' => ['total' => 0, 'per_page' => (int) ($v['per_page'] ?? 25), 'current_page' => 1, 'last_page' => 1]]);
        }
        if (! empty($v['class_id']) && ! empty($v['section_id'])) {
            $cs = (int) DB::table('class_sections')->where('class_id', $v['class_id'])
                ->where('section_id', $v['section_id'])->value('id');
            if (! $cs || ! TeacherScope::inScope($staffId, $ctx->sessionId, $cs)) {
                return $this->errorResponse('Class section is not in your scope', null, 403);
            }
        }
        [$useMiddle, $useLast] = $this->nameSettings();
        $q = DB::table('students')
            ->join('student_session', 'student_session.student_id', '=', 'students.id')
            ->leftJoin('classes', 'classes.id', '=', 'student_session.class_id')
            ->leftJoin('sections', 'sections.id', '=', 'student_session.section_id')
            ->where('students.is_active', 'yes')
            ->where('student_session.session_id', $ctx->sessionId)
            ->select('students.id', 'students.admission_no', 'students.firstname', 'students.middlename',
                'students.lastname', 'students.gender', 'students.mobileno',
                'classes.class as class_name', 'sections.section as section_name');
        if (! empty($v['class_id'])) {
            $q->where('student_session.class_id', $v['class_id']);
        }
        if (! empty($v['section_id'])) {
            $q->where('student_session.section_id', $v['section_id']);
        }
        $this->scopePairs($q, $pairs);
        if (! empty($v['search'])) {
            $s = '%'.$v['search'].'%';
            $q->where(fn ($qq) => $qq->where('students.firstname', 'like', $s)
                ->orWhere('students.admission_no', 'like', $s)
                ->orWhere('students.mobileno', 'like', $s));
        }
        $q->orderBy('students.admission_no')->orderBy('students.id');
        $p = $q->paginate((int) ($v['per_page'] ?? 25));
        $items = collect($p->items())->map(function ($r) use ($sessionValue, $ctx, $useMiddle, $useLast) {
            $tq = DB::table('student_incidents')
                ->join('student_behaviour', 'student_behaviour.id', '=', 'student_incidents.incident_id')
                ->where('student_incidents.student_id', $r->id);
            if ($sessionValue === 'current_session') {
                $tq->where('student_incidents.session_id', $ctx->sessionId);
            }
            $agg = $tq->selectRaw('COALESCE(SUM(student_behaviour.point),0) as tp, COUNT(student_incidents.id) as ti')->first();

            $r->full_name = $this->fullName($r, $useMiddle, $useLast);
            $r->total_points = (int) ($agg->tp ?? 0);
            $r->total_incidents = (int) ($agg->ti ?? 0);

            return $r;
        });

        return $this->successResponse(['students' => $items, 'session' => $sessionValue,
            'pagination' => ['total' => $p->total(), 'per_page' => $p->perPage(),
                'current_page' => $p->currentPage(), 'last_page' => $p->lastPage()]]);
    }

    public function studentIncidentDetails(Request $request, int $studentId): JsonResponse
    {
        $ctx = $this->context($request);
        if ($deny = $this->denyUnless((int) $ctx->staff->id, 'student_incident_report')) {
            return $deny;
        }
        $v = $request->validate(['session' => 'nullable|string|max:30']);
        if (! $this->studentInScope((int) $ctx->staff->id, $ctx->sessionId, $studentId)) {
            return $this->errorResponse('Student is not in your scope', null, 403);
        }
        [$skipA, $skipB, $df, $restr] = $this->nameSettings();
        $sv = $this->sessionValue($v['session'] ?? null);

        return $this->successResponse(['student_id' => $studentId, 'session' => $sv,
            'incidents' => $this->incidentDetails($studentId, $sv, $ctx->sessionId, $df, $restr)]);
    }

    // ---------- ترتيب الطلاب (parity مع dtstudentranklist + dense rank) ----------

    public function rank(Request $request): JsonResponse
    {
        $ctx = $this->context($request);
        $staffId = (int) $ctx->staff->id;
        if ($deny = $this->denyUnless($staffId, 'student_behaviour_rank_report')) {
            return $deny;
        }
        $v = $request->validate([
            'class_id' => 'nullable|integer', 'section_id' => 'nullable|integer',
            'session' => 'nullable|string|max:30', 'type' => 'nullable|in:greaterthanequal,lessthanequal',
            'point' => 'nullable|numeric', 'search' => 'nullable|string|max:100',
            'page' => 'nullable|integer|min:1', 'per_page' => 'nullable|integer|min:1|max:100',
        ]);
        if (! empty($v['type']) && ! isset($v['point'])) {
            return $this->errorResponse('point is required when type is set', null, 422);
        }
        $sessionValue = $this->sessionValue($v['session'] ?? null);
        $pairs = $this->myPairs($staffId, $ctx->sessionId);
        if ($pairs === []) {
            return $this->successResponse(['ranks' => [], 'session' => $sessionValue,
                'pagination' => ['total' => 0, 'per_page' => (int) ($v['per_page'] ?? 25), 'current_page' => 1, 'last_page' => 1]]);
        }
        [$useMiddle, $useLast] = $this->nameSettings();
        $q = DB::table('student_incidents')
            ->join('student_behaviour', 'student_behaviour.id', '=', 'student_incidents.incident_id')
            ->join('students', 'students.id', '=', 'student_incidents.student_id')
            ->join('student_session', fn ($j) => $j->on('student_session.student_id', '=', 'students.id'))
            ->leftJoin('classes', 'classes.id', '=', 'student_session.class_id')
            ->leftJoin('sections', 'sections.id', '=', 'student_session.section_id')
            ->where('students.is_active', 'yes')
            ->where('student_session.session_id', $ctx->sessionId);
        if ($sessionValue === 'current_session') {
            $q->where('student_incidents.session_id', $ctx->sessionId);
        }
        if (! empty($v['class_id'])) {
            $q->where('student_session.class_id', $v['class_id']);
        }
        if (! empty($v['section_id'])) {
            $q->where('student_session.section_id', $v['section_id']);
        }
        $this->scopePairs($q, $pairs);
        if (! empty($v['search'])) {
            $s = '%'.$v['search'].'%';
            $q->where(fn ($qq) => $qq->where('students.firstname', 'like', $s)
                ->orWhere('students.admission_no', 'like', $s));
        }
        $q->select('students.id', 'students.admission_no', 'students.firstname', 'students.middlename',
                'students.lastname', 'students.gender', 'students.mobileno',
                'classes.class as class_name', 'sections.section as section_name')
            ->selectRaw('SUM(student_behaviour.point) as totalpoints')
            ->groupBy('students.id', 'students.admission_no', 'students.firstname', 'students.middlename',
                'students.lastname', 'students.gender', 'students.mobileno', 'classes.class', 'sections.section');
        if (! empty($v['type']) && isset($v['point'])) {
            $op = $v['type'] === 'greaterthanequal' ? '>=' : '<=';
            $q->havingRaw('SUM(student_behaviour.point) '.$op.' ?', [(float) $v['point']]);
        }
        $rows = $q->orderByDesc('totalpoints')->get();
        // dense rank مثل CI (نفس المجموع = نفس الرتبة).
        $rank = 0;
        $prev = null;
        $rows = $rows->map(function ($r) use (&$rank, &$prev, $useMiddle, $useLast) {
            $tp = (int) $r->totalpoints;
            if ($prev === null || $tp !== $prev) {
                $rank++;
                $prev = $tp;
            }
            $r->totalpoints = $tp;
            $r->rank = $rank;
            $r->full_name = $this->fullName($r, $useMiddle, $useLast);

            return $r;
        })->values();
        $per = (int) ($v['per_page'] ?? 25);
        $page = (int) ($v['page'] ?? 1);
        $total = $rows->count();
        $items = $rows->forPage($page, $per)->values();

        return $this->successResponse(['ranks' => $items, 'session' => $sessionValue,
            'pagination' => ['total' => $total, 'per_page' => $per,
                'current_page' => $page, 'last_page' => max(1, (int) ceil($total / $per))]]);
    }

    public function rankDetails(Request $request, int $studentId): JsonResponse
    {
        $ctx = $this->context($request);
        if ($deny = $this->denyUnless((int) $ctx->staff->id, 'student_behaviour_rank_report')) {
            return $deny;
        }
        $v = $request->validate(['session' => 'nullable|string|max:30']);
        if (! $this->studentInScope((int) $ctx->staff->id, $ctx->sessionId, $studentId)) {
            return $this->errorResponse('Student is not in your scope', null, 403);
        }
        [$skipC, $skipD, $df, $restr] = $this->nameSettings();

        return $this->successResponse(['student_id' => $studentId,
            'session' => $this->sessionValue($v['session'] ?? null),
            'incidents' => $this->incidentDetails($studentId, $this->sessionValue($v['session'] ?? null), $ctx->sessionId, $df, $restr)]);
    }

    // ---------- ترتيب الفصول (parity مع classwiserank/classwisepoint) ----------

    public function classRank(Request $request): JsonResponse
    {
        $ctx = $this->context($request);
        $staffId = (int) $ctx->staff->id;
        if ($deny = $this->denyUnless($staffId, 'class_wise_rank_report')) {
            return $deny;
        }
        $pairs = $this->myPairs($staffId, $ctx->sessionId);
        $rows = DB::table('student_incidents')
            ->join('student_behaviour', 'student_behaviour.id', '=', 'student_incidents.incident_id')
            ->join('students', 'students.id', '=', 'student_incidents.student_id')
            ->join('student_session', fn ($j) => $j->on('student_session.student_id', '=', 'students.id'))
            ->join('classes', 'classes.id', '=', 'student_session.class_id')
            ->where('students.is_active', 'yes')
            ->where('student_incidents.session_id', $ctx->sessionId)
            ->where('student_session.session_id', $ctx->sessionId)
            ->when($pairs !== [], fn ($q) => $this->scopePairs($q, $pairs))
            ->when($pairs === [], fn ($q) => $q->whereRaw('1=0'))
            ->select('classes.id as class_id', 'classes.class as class_name')
            ->selectRaw('SUM(student_behaviour.point) as totalpoints')
            ->selectRaw('COUNT(DISTINCT students.id) as total_students')
            ->groupBy('classes.id', 'classes.class')
            ->orderByDesc('totalpoints')->get()
            ->map(fn ($r) => ['class_id' => (int) $r->class_id, 'class_name' => $r->class_name,
                'totalpoints' => (int) $r->totalpoints, 'total_students' => (int) $r->total_students])->values();

        return $this->successResponse(['ranks' => $rows]);
    }

    public function classPoints(Request $request, int $classId): JsonResponse
    {
        $ctx = $this->context($request);
        $staffId = (int) $ctx->staff->id;
        if ($deny = $this->denyUnless($staffId, 'class_wise_rank_report')) {
            return $deny;
        }
        $pairs = $this->myPairs($staffId, $ctx->sessionId);
        if (! collect($pairs)->contains(fn ($p) => $p[0] === $classId)) {
            return $this->errorResponse('Class is not in your scope', null, 403);
        }
        [$useMiddle, $useLast] = $this->nameSettings();
        $students = DB::table('student_incidents')
            ->join('student_behaviour', 'student_behaviour.id', '=', 'student_incidents.incident_id')
            ->join('students', 'students.id', '=', 'student_incidents.student_id')
            ->join('student_session', fn ($j) => $j->on('student_session.student_id', '=', 'students.id'))
            ->where('students.is_active', 'yes')
            ->where('student_incidents.session_id', $ctx->sessionId)
            ->where('student_session.session_id', $ctx->sessionId)
            ->where('student_session.class_id', $classId)
            ->select('students.id', 'students.admission_no', 'students.firstname', 'students.middlename', 'students.lastname')
            ->groupBy('students.id', 'students.admission_no', 'students.firstname', 'students.middlename', 'students.lastname')
            ->get()->map(function ($s) use ($ctx, $useMiddle, $useLast) {
                $s->full_name = $this->fullName($s, $useMiddle, $useLast);
                $s->incidents = DB::table('student_incidents')
                    ->join('student_behaviour', 'student_behaviour.id', '=', 'student_incidents.incident_id')
                    ->where('student_incidents.student_id', $s->id)
                    ->where('student_incidents.session_id', $ctx->sessionId)
                    ->select('student_behaviour.point', 'student_behaviour.title', 'student_behaviour.description')->get();

                return $s;
            })->values();

        return $this->successResponse(['class_id' => $classId, 'students' => $students]);
    }

    // ---------- ترتيب الفصول والأقسام (parity مع classsectionwiserank) ----------

    public function classSectionRank(Request $request): JsonResponse
    {
        $ctx = $this->context($request);
        $staffId = (int) $ctx->staff->id;
        if ($deny = $this->denyUnless($staffId, 'class_section_wise_rank_report')) {
            return $deny;
        }
        $pairs = $this->myPairs($staffId, $ctx->sessionId);
        $rows = DB::table('student_incidents')
            ->join('student_behaviour', 'student_behaviour.id', '=', 'student_incidents.incident_id')
            ->join('students', 'students.id', '=', 'student_incidents.student_id')
            ->join('student_session', fn ($j) => $j->on('student_session.student_id', '=', 'students.id'))
            ->join('classes', 'classes.id', '=', 'student_session.class_id')
            ->join('sections', 'sections.id', '=', 'student_session.section_id')
            ->where('students.is_active', 'yes')
            ->where('student_incidents.session_id', $ctx->sessionId)
            ->where('student_session.session_id', $ctx->sessionId)
            ->when($pairs !== [], fn ($q) => $this->scopePairs($q, $pairs))
            ->when($pairs === [], fn ($q) => $q->whereRaw('1=0'))
            ->select('classes.id as class_id', 'classes.class as class_name', 'sections.id as section_id', 'sections.section as section_name')
            ->selectRaw('SUM(student_behaviour.point) as totalpoints')
            ->selectRaw('COUNT(DISTINCT students.id) as total_students')
            ->groupBy('classes.id', 'classes.class', 'sections.id', 'sections.section')
            ->orderByDesc('totalpoints')->get()
            ->map(fn ($r) => ['class_id' => (int) $r->class_id, 'class_name' => $r->class_name,
                'section_id' => (int) $r->section_id, 'section_name' => $r->section_name,
                'totalpoints' => (int) $r->totalpoints, 'total_students' => (int) $r->total_students])->values();

        return $this->successResponse(['ranks' => $rows]);
    }

    public function classSectionPoints(Request $request): JsonResponse
    {
        $ctx = $this->context($request);
        $staffId = (int) $ctx->staff->id;
        if ($deny = $this->denyUnless($staffId, 'class_section_wise_rank_report')) {
            return $deny;
        }
        $v = $request->validate(['class_id' => 'required|integer', 'section_id' => 'required|integer']);
        $cs = (int) DB::table('class_sections')->where('class_id', $v['class_id'])
            ->where('section_id', $v['section_id'])->value('id');
        if (! $cs || ! TeacherScope::inScope($staffId, $ctx->sessionId, $cs)) {
            return $this->errorResponse('Class section is not in your scope', null, 403);
        }
        [$useMiddle, $useLast] = $this->nameSettings();
        $students = DB::table('student_incidents')
            ->join('student_behaviour', 'student_behaviour.id', '=', 'student_incidents.incident_id')
            ->join('students', 'students.id', '=', 'student_incidents.student_id')
            ->join('student_session', fn ($j) => $j->on('student_session.student_id', '=', 'students.id'))
            ->where('students.is_active', 'yes')
            ->where('student_incidents.session_id', $ctx->sessionId)
            ->where('student_session.session_id', $ctx->sessionId)
            ->where('student_session.class_id', $v['class_id'])
            ->where('student_session.section_id', $v['section_id'])
            ->select('students.id', 'students.admission_no', 'students.firstname', 'students.middlename', 'students.lastname')
            ->groupBy('students.id', 'students.admission_no', 'students.firstname', 'students.middlename', 'students.lastname')
            ->get()->map(function ($s) use ($ctx, $useMiddle, $useLast) {
                $s->full_name = $this->fullName($s, $useMiddle, $useLast);
                $s->incidents = DB::table('student_incidents')
                    ->join('student_behaviour', 'student_behaviour.id', '=', 'student_incidents.incident_id')
                    ->where('student_incidents.student_id', $s->id)
                    ->where('student_incidents.session_id', $ctx->sessionId)
                    ->select('student_behaviour.point', 'student_behaviour.title', 'student_behaviour.description')->get();

                return $s;
            })->values();

        return $this->successResponse(['class_id' => (int) $v['class_id'],
            'section_id' => (int) $v['section_id'], 'students' => $students]);
    }

    // ---------- ترتيب البيوت (parity مع housewiserank/housewisepoint) ----------

    public function houseRank(Request $request): JsonResponse
    {
        $ctx = $this->context($request);
        $staffId = (int) $ctx->staff->id;
        if ($deny = $this->denyUnless($staffId, 'house_wise_rank_report')) {
            return $deny;
        }
        $pairs = $this->myPairs($staffId, $ctx->sessionId);
        $rows = DB::table('student_incidents')
            ->join('student_behaviour', 'student_behaviour.id', '=', 'student_incidents.incident_id')
            ->join('students', 'students.id', '=', 'student_incidents.student_id')
            ->join('student_session', fn ($j) => $j->on('student_session.student_id', '=', 'students.id'))
            ->join('school_houses', 'school_houses.id', '=', 'students.school_house_id')
            ->where('students.is_active', 'yes')
            ->where('student_incidents.session_id', $ctx->sessionId)
            ->where('student_session.session_id', $ctx->sessionId)
            ->when($pairs !== [], fn ($q) => $this->scopePairs($q, $pairs))
            ->when($pairs === [], fn ($q) => $q->whereRaw('1=0'))
            ->select('school_houses.id as house_id', 'school_houses.house_name')
            ->selectRaw('SUM(student_behaviour.point) as totalpoints')
            ->selectRaw('COUNT(DISTINCT students.id) as total_students')
            ->groupBy('school_houses.id', 'school_houses.house_name')
            ->orderByDesc('totalpoints')->get()
            ->map(fn ($r) => ['house_id' => (int) $r->house_id, 'house_name' => $r->house_name,
                'totalpoints' => (int) $r->totalpoints, 'total_students' => (int) $r->total_students])->values();

        return $this->successResponse(['ranks' => $rows]);
    }

    public function housePoints(Request $request, int $houseId): JsonResponse
    {
        $ctx = $this->context($request);
        $staffId = (int) $ctx->staff->id;
        if ($deny = $this->denyUnless($staffId, 'house_wise_rank_report')) {
            return $deny;
        }
        [$useMiddle, $useLast] = $this->nameSettings();
        $pairs = $this->myPairs($staffId, $ctx->sessionId);
        $students = DB::table('student_incidents')
            ->join('student_behaviour', 'student_behaviour.id', '=', 'student_incidents.incident_id')
            ->join('students', 'students.id', '=', 'student_incidents.student_id')
            ->join('student_session', fn ($j) => $j->on('student_session.student_id', '=', 'students.id'))
            ->leftJoin('classes', 'classes.id', '=', 'student_session.class_id')
            ->leftJoin('sections', 'sections.id', '=', 'student_session.section_id')
            ->where('students.is_active', 'yes')
            ->where('student_incidents.session_id', $ctx->sessionId)
            ->where('student_session.session_id', $ctx->sessionId)
            ->where('students.school_house_id', $houseId)
            ->when($pairs !== [], fn ($q) => $this->scopePairs($q, $pairs))
            ->when($pairs === [], fn ($q) => $q->whereRaw('1=0'))
            ->select('students.id', 'students.admission_no', 'students.firstname', 'students.middlename', 'students.lastname',
                'classes.class as class_name', 'sections.section as section_name')
            ->groupBy('students.id', 'students.admission_no', 'students.firstname', 'students.middlename',
                'students.lastname', 'classes.class', 'sections.section')
            ->get()->map(function ($s) use ($ctx, $useMiddle, $useLast) {
                $s->full_name = $this->fullName($s, $useMiddle, $useLast);
                $s->incidents = DB::table('student_incidents')
                    ->join('student_behaviour', 'student_behaviour.id', '=', 'student_incidents.incident_id')
                    ->where('student_incidents.student_id', $s->id)
                    ->where('student_incidents.session_id', $ctx->sessionId)
                    ->select('student_behaviour.point', 'student_behaviour.title', 'student_behaviour.description')->get();

                return $s;
            })->values();

        return $this->successResponse(['house_id' => $houseId, 'students' => $students]);
    }

    // ---------- تقرير الحوادث (parity مع incidentwisereport/studentdetails) ----------

    public function incidentWise(Request $request): JsonResponse
    {
        $ctx = $this->context($request);
        $staffId = (int) $ctx->staff->id;
        if ($deny = $this->denyUnless($staffId, 'incident_wise_report')) {
            return $deny;
        }
        $v = $request->validate(['session' => 'nullable|string|max:30']);
        $sessionValue = $this->sessionValue($v['session'] ?? null);
        $pairs = $this->myPairs($staffId, $ctx->sessionId);
        $isRestricted = (string) (DB::table('sch_settings')->value('class_teacher') ?? 'no') === 'yes';
        $incidents = DB::table('student_behaviour')->orderBy('id')->select('id', 'title', 'point', 'description')->get();
        $items = $incidents->map(function ($inc) use ($sessionValue, $ctx, $pairs, $isRestricted) {
            $q = DB::table('student_incidents')
                ->join('students', 'students.id', '=', 'student_incidents.student_id')
                ->join('student_session', fn ($j) => $j->on('student_session.student_id', '=', 'students.id'))
                ->join('classes', 'classes.id', '=', 'student_session.class_id')
                ->join('sections', 'sections.id', '=', 'student_session.section_id')
                ->where('students.is_active', 'yes')
                ->where('student_incidents.incident_id', $inc->id);
            if ($sessionValue === 'current_session') {
                $q->where('student_incidents.session_id', $ctx->sessionId)
                    ->where('student_session.session_id', $ctx->sessionId);
            }
            if ($isRestricted && $pairs !== []) {
                $this->scopePairs($q, $pairs);
            }
            $inc->total_students = (int) $q->distinct()->count('student_incidents.student_id');

            return $inc;
        })->values();

        return $this->successResponse(['incidents' => $items, 'session' => $sessionValue]);
    }

    public function incidentStudents(Request $request, int $incidentId): JsonResponse
    {
        $ctx = $this->context($request);
        $staffId = (int) $ctx->staff->id;
        if ($deny = $this->denyUnless($staffId, 'incident_wise_report')) {
            return $deny;
        }
        $v = $request->validate(['session' => 'nullable|string|max:30']);
        if (! DB::table('student_behaviour')->where('id', $incidentId)->exists()) {
            return $this->errorResponse('Incident not found', null, 404);
        }
        $sessionValue = $this->sessionValue($v['session'] ?? null);
        $pairs = $this->myPairs($staffId, $ctx->sessionId);
        $isRestricted = (string) (DB::table('sch_settings')->value('class_teacher') ?? 'no') === 'yes';
        [$useMiddle, $useLast] = $this->nameSettings();
        $q = DB::table('student_incidents')
            ->join('students', 'students.id', '=', 'student_incidents.student_id')
            ->join('student_session', fn ($j) => $j->on('student_session.student_id', '=', 'students.id'))
            ->join('classes', 'classes.id', '=', 'student_session.class_id')
            ->join('sections', 'sections.id', '=', 'student_session.section_id')
            ->where('students.is_active', 'yes')
            ->where('student_incidents.incident_id', $incidentId);
        if ($sessionValue === 'current_session') {
            $q->where('student_incidents.session_id', $ctx->sessionId)
                ->where('student_session.session_id', $ctx->sessionId);
        }
        if ($isRestricted && $pairs !== []) {
            $this->scopePairs($q, $pairs);
        }
        $students = $q->select('students.id', 'students.admission_no', 'students.firstname',
                'students.middlename', 'students.lastname', 'classes.class as class_name',
                'sections.section as section_name', 'student_session.session_id')
            ->groupBy('students.id', 'students.admission_no', 'students.firstname', 'students.middlename',
                'students.lastname', 'classes.class', 'sections.section', 'student_session.session_id')
            ->get()->map(fn ($s) => array_merge((array) $s, ['full_name' => $this->fullName($s, $useMiddle, $useLast)]))->values();

        return $this->successResponse(['incident_id' => $incidentId, 'session' => $sessionValue, 'students' => $students]);
    }
}
