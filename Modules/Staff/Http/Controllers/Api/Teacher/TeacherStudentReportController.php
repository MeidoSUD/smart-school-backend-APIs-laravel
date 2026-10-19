<?php

declare(strict_types=1);

namespace Modules\Staff\Http\Controllers\Api\Teacher;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Modules\Staff\Services\TeacherRbac;
use Modules\Staff\Services\TeacherScope;

/**
 * تقرير الطلاب للمعلم (قراءة فقط) — parity مع CI Report::studentreport / dtstudentreportlist:
 * - الصلاحية: student_report:can_view (TeacherRbac)
 * - الاستعلام: Student_model::searchdatatableByClassSectionCategoryGenderRte
 *   (student_session للجلسة الحالية + students.is_active='yes')
 * - الفلاتر: class_id, section_id, category_id, gender, rte (كما في CI)
 *
 * النطاق: الطلاب ضمن فصول المعلم فقط (TeacherScope::myClasses) — تقييد أضيق من CI عمداً.
 */
class TeacherStudentReportController extends TeacherController
{
    public function __construct()
    {
        $this->setControllerName('TeacherStudentReportController');
    }

    public function index(Request $request): JsonResponse
    {
        $ctx = $this->context($request);

        if (! TeacherRbac::hasPrivilege((int) $ctx->staff->id, 'student_report', 'can_view')) {
            return $this->errorResponse('Forbidden: missing student_report can_view permission', null, 403);
        }

        $v = $request->validate([
            'class_id' => 'nullable|integer',
            'section_id' => 'nullable|integer',
            'category_id' => 'nullable|integer',
            'gender' => 'nullable|string|max:20',
            'rte' => 'nullable|string|max:10',
            'search' => 'nullable|string|max:100',
            'per_page' => 'nullable|integer|min:1|max:100',
        ]);

        $csIds = collect(TeacherScope::myClasses((int) $ctx->staff->id, $ctx->sessionId))
            ->pluck('class_section_id')
            ->map(fn ($id): int => (int) $id)
            ->all();

        $query = DB::table('students')
            ->join('student_session', 'student_session.student_id', '=', 'students.id')
            ->join('classes', 'classes.id', '=', 'student_session.class_id')
            ->join('sections', 'sections.id', '=', 'student_session.section_id')
            ->leftJoin('categories', 'categories.id', '=', 'students.category_id')
            ->join('class_sections', function ($join) {
                $join->on('class_sections.class_id', '=', 'student_session.class_id')
                    ->on('class_sections.section_id', '=', 'student_session.section_id');
            })
            ->where('student_session.session_id', $ctx->sessionId)
            ->where('students.is_active', 'yes')
            ->whereIn('class_sections.id', $csIds ?: [0])
            ->when(! empty($v['class_id']), fn ($q) => $q->where('student_session.class_id', $v['class_id']))
            ->when(! empty($v['section_id']), fn ($q) => $q->where('student_session.section_id', $v['section_id']))
            ->when(! empty($v['category_id']), fn ($q) => $q->where('students.category_id', $v['category_id']))
            ->when(! empty($v['gender']), fn ($q) => $q->where('students.gender', $v['gender']))
            ->when(! empty($v['rte']), fn ($q) => $q->where('students.rte', $v['rte']))
            ->when(! empty($v['search']), function ($q) use ($v) {
                $term = '%'.$v['search'].'%';
                $q->where(function ($w) use ($term) {
                    $w->where('students.admission_no', 'like', $term)
                        ->orWhere('students.firstname', 'like', $term)
                        ->orWhere('students.father_name', 'like', $term)
                        ->orWhere('students.mobileno', 'like', $term);
                });
            })
            ->orderBy('students.id')
            ->select(
                'students.id',
                'students.admission_no',
                'students.roll_no',
                'students.firstname',
                'students.middlename',
                'students.lastname',
                'students.mobileno',
                'students.email',
                'students.dob',
                'students.gender',
                'students.rte',
                'students.father_name',
                'students.guardian_name',
                'students.guardian_phone',
                'students.current_address',
                'students.permanent_address',
                'students.admission_date',
                'student_session.id as student_session_id',
                'classes.id as class_id',
                'classes.class',
                'sections.id as section_id',
                'sections.section',
                DB::raw('IFNULL(categories.category, "") as category'),
            );

        return $this->successResponse(['students' => $query->paginate((int) ($v['per_page'] ?? 25))]);
    }

    public function transportStudents(Request $request): JsonResponse
    {
        $ctx = $this->context($request);

        $v = $request->validate([
            'class_id' => 'nullable|integer',
            'section_id' => 'nullable|integer',
            'route_id' => 'nullable|integer',
            'vehicle_id' => 'nullable|integer',
            'per_page' => 'nullable|integer|min:1|max:100',
        ]);

        $csIds = collect(TeacherScope::myClasses((int) $ctx->staff->id, $ctx->sessionId))
            ->pluck('class_section_id')
            ->map(fn ($id): int => (int) $id)
            ->all();

        $query = DB::table('students')
            ->join('student_session', 'student_session.student_id', '=', 'students.id')
            ->join('classes', 'classes.id', '=', 'student_session.class_id')
            ->join('sections', 'sections.id', '=', 'student_session.section_id')
            ->join('class_sections', function ($join) {
                $join->on('class_sections.class_id', '=', 'student_session.class_id')
                    ->on('class_sections.section_id', '=', 'student_session.section_id');
            })
            ->leftJoin('vehicle_routes', 'vehicle_routes.id', '=', 'student_session.vehroute_id')
            ->leftJoin('vehicles', 'vehicles.id', '=', 'vehicle_routes.vehicle_id')
            ->leftJoin('transport_route', 'transport_route.id', '=', 'vehicle_routes.route_id')
            ->where('student_session.session_id', $ctx->sessionId)
            ->where('students.is_active', 'yes')
            ->whereIn('class_sections.id', $csIds ?: [0])
            ->when(! empty($v['class_id']), fn ($q) => $q->where('student_session.class_id', $v['class_id']))
            ->when(! empty($v['section_id']), fn ($q) => $q->where('student_session.section_id', $v['section_id']))
            ->when(! empty($v['route_id']), fn ($q) => $q->where('vehicle_routes.route_id', $v['route_id']))
            ->when(! empty($v['vehicle_id']), fn ($q) => $q->where('vehicle_routes.vehicle_id', $v['vehicle_id']))
            ->select(
                'students.id',
                'students.admission_no',
                'students.roll_no',
                'students.firstname',
                'students.middlename',
                'students.lastname',
                'students.father_name',
                'students.father_phone',
                'classes.class as class_name',
                'sections.section as section_name',
                'transport_route.route_title',
                'vehicles.vehicle_no',
                'vehicles.driver_name',
                'vehicles.driver_contact',
                'student_session.transport_fees'
            )
            ->orderBy('students.id');

        return $this->successResponse(['students' => $query->paginate((int) ($v['per_page'] ?? 25))]);
    }

    public function hostelStudents(Request $request): JsonResponse
    {
        $ctx = $this->context($request);

        $v = $request->validate([
            'class_id' => 'nullable|integer',
            'section_id' => 'nullable|integer',
            'hostel_id' => 'nullable|integer',
            'per_page' => 'nullable|integer|min:1|max:100',
        ]);

        $csIds = collect(TeacherScope::myClasses((int) $ctx->staff->id, $ctx->sessionId))
            ->pluck('class_section_id')
            ->map(fn ($id): int => (int) $id)
            ->all();

        $query = DB::table('students')
            ->join('student_session', 'student_session.student_id', '=', 'students.id')
            ->join('classes', 'classes.id', '=', 'student_session.class_id')
            ->join('sections', 'sections.id', '=', 'student_session.section_id')
            ->join('class_sections', function ($join) {
                $join->on('class_sections.class_id', '=', 'student_session.class_id')
                    ->on('class_sections.section_id', '=', 'student_session.section_id');
            })
            ->leftJoin('hostel_rooms', 'hostel_rooms.id', '=', 'student_session.hostel_room_id')
            ->leftJoin('hostel', 'hostel.id', '=', 'hostel_rooms.hostel_id')
            ->leftJoin('room_types', 'room_types.id', '=', 'hostel_rooms.room_type_id')
            ->where('student_session.session_id', $ctx->sessionId)
            ->where('students.is_active', 'yes')
            ->whereIn('class_sections.id', $csIds ?: [0])
            ->when(! empty($v['class_id']), fn ($q) => $q->where('student_session.class_id', $v['class_id']))
            ->when(! empty($v['section_id']), fn ($q) => $q->where('student_session.section_id', $v['section_id']))
            ->when(! empty($v['hostel_id']), fn ($q) => $q->where('hostel.id', $v['hostel_id']))
            ->select(
                'students.id',
                'students.admission_no',
                'students.roll_no',
                'students.firstname',
                'students.middlename',
                'students.lastname',
                'students.guardian_phone',
                'classes.class as class_name',
                'sections.section as section_name',
                'hostel.hostel_name',
                'hostel_rooms.room_no',
                'room_types.room_type',
                'hostel_rooms.cost_per_bed'
            )
            ->orderBy('students.id');

        return $this->successResponse(['students' => $query->paginate((int) ($v['per_page'] ?? 25))]);
    }

    public function alumniStudents(Request $request): JsonResponse
    {        $ctx = $this->context($request);

        $v = $request->validate([
            'class_id' => 'nullable|integer',
            'section_id' => 'nullable|integer',
            'search' => 'nullable|string|max:100',
            'per_page' => 'nullable|integer|min:1|max:100',
        ]);

        $csIds = collect(TeacherScope::myClasses((int) $ctx->staff->id, $ctx->sessionId))
            ->pluck('class_section_id')
            ->map(fn ($id): int => (int) $id)
            ->all();

        $query = DB::table('students')
            ->join('student_session', 'student_session.student_id', '=', 'students.id')
            ->join('classes', 'classes.id', '=', 'student_session.class_id')
            ->join('sections', 'sections.id', '=', 'student_session.section_id')
            ->join('class_sections', function ($join) {
                $join->on('class_sections.class_id', '=', 'student_session.class_id')
                    ->on('class_sections.section_id', '=', 'student_session.section_id');
            })
            ->leftJoin('alumni_students', 'alumni_students.student_id', '=', 'students.id')
            ->where(function ($w) {
                $w->where('student_session.is_alumni', 1)
                    ->orWhereNotNull('alumni_students.id');
            })
            ->whereIn('class_sections.id', $csIds ?: [0])
            ->when(! empty($v['class_id']), fn ($q) => $q->where('student_session.class_id', $v['class_id']))
            ->when(! empty($v['section_id']), fn ($q) => $q->where('student_session.section_id', $v['section_id']))
            ->when(! empty($v['search']), function ($q) use ($v) {
                $term = '%'.$v['search'].'%';
                $q->where(function ($sq) use ($term) {
                    $sq->where('students.firstname', 'like', $term)
                        ->orWhere('students.lastname', 'like', $term)
                        ->orWhere('students.admission_no', 'like', $term);
                });
            })
            ->select(
                'students.id',
                'students.admission_no',
                'students.firstname',
                'students.middlename',
                'students.lastname',
                'students.email',
                'students.mobileno',
                'classes.class as class_name',
                'sections.section as section_name',
                'alumni_students.current_email',
                'alumni_students.current_phone',
                'alumni_students.occupation',
                'alumni_students.address'
            )
            ->orderBy('students.id');

        return $this->successResponse(['students' => $query->paginate((int) ($v['per_page'] ?? 25))]);
    }

    /**
     * ملخص الرسوم لفصول المعلم — parity مُضيّقة مع CI Financereports::reportduefees.
     *
     * منطق الاستحقاق مطابق لـ CI Studentfeemaster_model::getStudentDueFeeTypesByDate +
     * حلقة reportduefees: أنواع الرسوم المستحقة (due_date <= اليوم) لطلاب الجلسة،
     * والمدفوع = مجموع (amount + amount_discount) من amount_detail (JSON)،
     * والمستحق إذا كان المدفوع < fee_amount (أو < مبلغ السجل عند is_system).
     *
     * نموذج الخصوصية (انحراف مقصود وموثق، أضيق من CI):
     * - النطاق: فصول المعلم فقط (TeacherScope) — class_id/section_id اختيارية للفلترة.
     * - يُعرض: رقم القبول والاسم والمجاميع (المستحق/المدفوع/المتبقي) والحالة فقط.
     * - يُحجب عمداً: بيانات التواصل (mobileno/email)، تفاصيل الدفع الخام (amount_detail)،
     *   رسوم النقل، وسجل الإيداعات — وهي بيانات مالية حساسة تبقى للإدارة المالية.
     * - ملاحظة: دور المعلم (2) لا يملك أي صلاحية رسوم في CI (كان سيُرفض access_denied)،
     *   وهذا الملخص المُجمّع هو البديل الآمن المعتمد لإكمال البند 12.2.
     */
    public function financeSummary(Request $request): JsonResponse
    {
        $ctx = $this->context($request);

        $v = $request->validate([
            'class_id' => 'nullable|integer',
            'section_id' => 'nullable|integer',
            'status' => 'nullable|string|in:paid,partial,unpaid',
            'per_page' => 'nullable|integer|min:1|max:100',
        ]);

        $csIds = collect(TeacherScope::myClasses((int) $ctx->staff->id, $ctx->sessionId))
            ->pluck('class_section_id')
            ->map(fn ($id): int => (int) $id)
            ->all();

        if (! empty($v['class_id']) || ! empty($v['section_id'])) {
            if (empty($v['class_id']) || empty($v['section_id'])) {
                return $this->errorResponse('Both class_id and section_id are required for class filtering', null, 422);
            }
            $csId = (int) DB::table('class_sections')
                ->where('class_id', $v['class_id'])->where('section_id', $v['section_id'])->value('id');
            if (! $csId || ! in_array($csId, $csIds, true)) {
                return $this->errorResponse('Class section is not in your scope', null, 403);
            }
            $csIds = [$csId];
        }

        $today = date('Y-m-d');

        // أنواع الرسوم المستحقة حتى اليوم — مرآة استعلام CI (الجلسة الحالية فقط)
        $dueLines = DB::table('student_fees_master as sfm')
            ->join('fee_session_groups as fsg', 'fsg.id', '=', 'sfm.fee_session_group_id')
            ->join('fee_groups as fg', 'fg.id', '=', 'fsg.fee_groups_id')
            ->join('fee_groups_feetype as fgft', 'fgft.fee_groups_id', '=', 'fg.id')
            ->join('student_session as ss', 'ss.id', '=', 'sfm.student_session_id')
            ->join('students', 'students.id', '=', 'ss.student_id')
            ->join('class_sections as cs', function ($join) {
                $join->on('cs.class_id', '=', 'ss.class_id')
                    ->on('cs.section_id', '=', 'ss.section_id');
            })
            ->leftJoin('student_fees_deposite as sfd', function ($join) {
                $join->on('sfd.student_fees_master_id', '=', 'sfm.id')
                    ->on('sfd.fee_groups_feetype_id', '=', 'fgft.id');
            })
            ->where('ss.session_id', $ctx->sessionId)
            ->where('fsg.session_id', $ctx->sessionId)
            ->where('fgft.session_id', $ctx->sessionId)
            ->where('students.is_active', 'yes')
            ->where('sfm.is_active', 'yes')
            ->where('fgft.due_date', '<=', $today)
            ->whereIn('cs.id', $csIds ?: [0])
            ->select(
                'ss.id as student_session_id',
                'students.id as student_id',
                'students.admission_no',
                'students.roll_no',
                'students.firstname',
                'students.middlename',
                'students.lastname',
                'ss.class_id',
                'ss.section_id',
                'sfm.id as fees_master_id',
                'sfm.is_system',
                'sfm.amount as master_amount',
                'fgft.id as fee_type_id',
                'fgft.amount as fee_amount',
                'sfd.amount_detail'
            )
            ->orderBy('students.id')
            ->get();

        // تجميع per-student بنفس حساب CI (amount + discount من JSON)
        $byStudent = [];
        foreach ($dueLines as $line) {
            $paid = 0.0;
            if (! empty($line->amount_detail)) {
                $decoded = json_decode((string) $line->amount_detail, true);
                if (is_array($decoded)) {
                    foreach ($decoded as $entry) {
                        $paid += (float) ($entry['amount'] ?? 0) + (float) ($entry['amount_discount'] ?? 0);
                    }
                }
            }
            // عتبة الاستحقاق كما في CI: مبلغ السجل عند is_system وإلا مبلغ النوع
            $threshold = ((int) $line->is_system === 1) ? (float) $line->master_amount : (float) $line->fee_amount;

            $key = (int) $line->student_session_id;
            if (! isset($byStudent[$key])) {
                $byStudent[$key] = [
                    'student_session_id' => $key,
                    'student_id' => (int) $line->student_id,
                    'admission_no' => $line->admission_no,
                    'roll_no' => $line->roll_no,
                    'full_name' => trim(implode(' ', array_filter([$line->firstname, $line->middlename, $line->lastname]))),
                    'class_id' => (int) $line->class_id,
                    'section_id' => (int) $line->section_id,
                    'total_fee' => 0.0,
                    'total_paid' => 0.0,
                ];
            }
            $byStudent[$key]['total_fee'] += $threshold;
            $byStudent[$key]['total_paid'] += min($paid, $threshold);
        }

        $rows = [];
        foreach ($byStudent as $row) {
            $balance = round($row['total_fee'] - $row['total_paid'], 2);
            $row['total_fee'] = round($row['total_fee'], 2);
            $row['total_paid'] = round($row['total_paid'], 2);
            $row['balance'] = $balance;
            $row['status'] = $balance <= 0 ? 'paid' : ($row['total_paid'] > 0 ? 'partial' : 'unpaid');
            if (! empty($v['status']) && $row['status'] !== $v['status']) {
                continue;
            }
            // يُدرج فقط من لديه مستحقات (balance > 0) أو بفلتر paid صراحةً — كما في CI (قائمة المتأخرين)
            if ($balance <= 0 && empty($v['status'])) {
                continue;
            }
            $rows[] = $row;
        }

        $summary = [
            'students_count' => count($rows),
            'total_fee' => round(array_sum(array_column($rows, 'total_fee')), 2),
            'total_paid' => round(array_sum(array_column($rows, 'total_paid')), 2),
            'total_balance' => round(array_sum(array_column($rows, 'balance')), 2),
            'paid_count' => count(array_filter($rows, fn ($r) => $r['status'] === 'paid')),
            'partial_count' => count(array_filter($rows, fn ($r) => $r['status'] === 'partial')),
            'unpaid_count' => count(array_filter($rows, fn ($r) => $r['status'] === 'unpaid')),
        ];

        $perPage = (int) ($v['per_page'] ?? 25);
        $page = max(1, (int) $request->integer('page', 1));
        $paged = array_slice($rows, ($page - 1) * $perPage, $perPage);

        return $this->successResponse([
            'summary' => $summary,
            'students' => $paged,
            'pagination' => [
                'total' => count($rows),
                'per_page' => $perPage,
                'current_page' => $page,
                'last_page' => max(1, (int) ceil(count($rows) / $perPage)),
            ],
            'privacy_note' => 'Scoped fee-status summary only. Contact details, payment transactions and transport fees are withheld for the finance office.',
        ]);
    }
}

