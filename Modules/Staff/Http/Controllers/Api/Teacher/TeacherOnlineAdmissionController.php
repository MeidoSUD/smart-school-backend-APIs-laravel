<?php

declare(strict_types=1);

namespace Modules\Staff\Http\Controllers\Api\Teacher;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Modules\Staff\Services\TeacherRbac;
use Modules\Staff\Services\TeacherScope;

/**
 * القبول الإلكتروني للمعلم، parity مع CI admin/Onlinestudent:
 * - index  (online_admission:can_view) → getstudentlist (DataTables، مُفلتر بالفصول)
 * - show   → Onlinestudent_model::get($id) + أعلام الإجراءات (enroll للمدير فقط)
 * - update (online_admission:can_edit) → edit (تحديث الحقول، enroll محظور على المعلم)
 * - paymentStatus → checkpaymentstatus (form_status/paid_status + إرشاد القبول)
 * - destroy (online_admission:can_delete) → Onlinestudent_model::remove
 *   (يحذف قيم الحقول المخصصة المرتبطة ثم السجل)
 *
 * النطاق: الطلبات المرتبطة بـ class_section_id ضمن فصول المعلم (TeacherScope::myClasses)،
 * وهو تقييد أضيق من CI عمداً كما هو موثق في TeacherScope.
 * الصلاحيات تُفحص عبر TeacherRbac، و403 عند غياب الصلاحية.
 * تحويل الطلب إلى طالب مقيد (is_enroll) عملية إدارية عليا — محظورة على المعلم (403).
 */
class TeacherOnlineAdmissionController extends TeacherController
{
    public function __construct()
    {
        $this->setControllerName('TeacherOnlineAdmissionController');
    }

    public function index(Request $request): JsonResponse
    {
        $ctx = $this->context($request);

        if (! TeacherRbac::hasPrivilege((int) $ctx->staff->id, 'online_admission', 'can_view')) {
            return $this->errorResponse('Forbidden: missing online_admission can_view permission', null, 403);
        }

        $v = $request->validate([
            'class_id' => 'nullable|integer',
            'section_id' => 'nullable|integer',
            'search' => 'nullable|string|max:100',
            'per_page' => 'nullable|integer|min:1|max:100',
        ]);

        $csIds = $this->scopedClassSectionIds($ctx->staff->id, $ctx->sessionId);

        $query = $this->baseQuery()
            ->whereIn('online_admissions.class_section_id', $csIds ?: [0])
            ->when(! empty($v['class_id']), fn ($q) => $q->where('classes.id', $v['class_id']))
            ->when(! empty($v['section_id']), fn ($q) => $q->where('sections.id', $v['section_id']))
            ->when(! empty($v['search']), function ($q) use ($v) {
                $term = '%'.$v['search'].'%';
                $q->where(function ($w) use ($term) {
                    $w->where('online_admissions.reference_no', 'like', $term)
                        ->orWhere('online_admissions.firstname', 'like', $term)
                        ->orWhere('online_admissions.father_name', 'like', $term)
                        ->orWhere('online_admissions.mobileno', 'like', $term);
                });
            })
            ->orderByDesc('online_admissions.id');

        return $this->successResponse([
            'admissions' => $query->paginate((int) ($v['per_page'] ?? 25)),
        ]);
    }

    public function show(Request $request, int $id): JsonResponse
    {
        $ctx = $this->context($request);

        if (! TeacherRbac::hasPrivilege((int) $ctx->staff->id, 'online_admission', 'can_view')) {
            return $this->errorResponse('Forbidden: missing online_admission can_view permission', null, 403);
        }

        $row = $this->baseQuery()->where('online_admissions.id', $id)->first();

        if (! $row) {
            return $this->errorResponse('Online admission not found', null, 404);
        }

        if (! in_array((int) $row->class_section_id, $this->scopedClassSectionIds($ctx->staff->id, $ctx->sessionId), true)) {
            return $this->errorResponse('Admission is not in your scope', null, 403);
        }

        return $this->successResponse(['admission' => $row] + $this->actionFlags((array) $row));
    }

    /**
     * تحديث طلب قبول إلكتروني — parity مع CI Onlinestudent::edit (بدون enroll).
     * enroll (is_enroll=1) إداري حصري → 403 للمعلم.
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $ctx = $this->context($request);

        if (! TeacherRbac::hasPrivilege((int) $ctx->staff->id, 'online_admission', 'can_edit')) {
            return $this->errorResponse('Forbidden: missing online_admission can_edit permission', null, 403);
        }

        $existing = DB::table('online_admissions')->where('id', $id)->first();
        if (! $existing) {
            return $this->errorResponse('Online admission not found', null, 404);
        }
        if (! in_array((int) $existing->class_section_id, $this->scopedClassSectionIds($ctx->staff->id, $ctx->sessionId), true)) {
            return $this->errorResponse('Admission is not in your scope', null, 403);
        }

        $v = $request->validate([
            'firstname' => 'sometimes|required|string|max:100',
            'middlename' => 'nullable|string|max:100',
            'lastname' => 'nullable|string|max:100',
            'mobileno' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:100',
            'dob' => 'nullable|date',
            'gender' => 'nullable|string|max:10',
            'father_name' => 'nullable|string|max:100',
            'father_phone' => 'nullable|string|max:20',
            'mother_name' => 'nullable|string|max:100',
            'mother_phone' => 'nullable|string|max:20',
            'guardian_name' => 'nullable|string|max:100',
            'guardian_phone' => 'nullable|string|max:20',
            'guardian_email' => 'nullable|email|max:100',
            'current_address' => 'nullable|string',
            'permanent_address' => 'nullable|string',
            'note' => 'nullable|string',
            'class_section_id' => 'nullable|integer|exists:class_sections,id',
            'is_enroll' => 'nullable|integer|in:0,1',
        ]);

        if (array_key_exists('is_enroll', $v) && (int) $v['is_enroll'] === 1) {
            return $this->errorResponse('Enrolling an online admission is restricted to administrators', null, 403);
        }
        unset($v['is_enroll']);

        if (isset($v['class_section_id'])
            && ! in_array((int) $v['class_section_id'], $this->scopedClassSectionIds($ctx->staff->id, $ctx->sessionId), true)) {
            return $this->errorResponse('Target class section is not in your scope', null, 403);
        }

        if ($v === []) {
            return $this->errorResponse('No updatable fields provided', null, 422);
        }

        DB::table('online_admissions')->where('id', $id)->update($v);

        $row = $this->baseQuery()->where('online_admissions.id', $id)->first();

        return $this->successResponse(['admission' => $row], 'Online admission updated successfully');
    }

    /**
     * حالة الدفع والنموذج — parity مع CI Onlinestudent::checkpaymentstatus.
     * تُستخدم قبل أي محاولة قبول لتحديد (form submitted؟ paid؟ enroll مسموح؟).
     */
    public function paymentStatus(Request $request, int $id): JsonResponse
    {
        $ctx = $this->context($request);

        if (! TeacherRbac::hasPrivilege((int) $ctx->staff->id, 'online_admission', 'can_view')) {
            return $this->errorResponse('Forbidden: missing online_admission can_view permission', null, 403);
        }

        $row = DB::table('online_admissions')->where('id', $id)->first(
            ['id', 'class_section_id', 'form_status', 'paid_status', 'is_enroll', 'reference_no']
        );
        if (! $row) {
            return $this->errorResponse('Online admission not found', null, 404);
        }
        if (! in_array((int) $row->class_section_id, $this->scopedClassSectionIds($ctx->staff->id, $ctx->sessionId), true)) {
            return $this->errorResponse('Admission is not in your scope', null, 403);
        }

        $paymentRequired = DB::table('sch_settings')->value('online_admission_payment') === 'yes';
        $formSubmitted = (int) $row->form_status === 1;
        $paid = (int) $row->paid_status === 1;
        $enrolled = (int) $row->is_enroll === 1;

        return $this->successResponse([
            'payment_status' => [
                'id' => (int) $row->id,
                'reference_no' => $row->reference_no,
                'form_status' => (int) $row->form_status,
                'form_submitted' => $formSubmitted,
                'paid_status' => (int) $row->paid_status,
                'paid' => $paid,
                'is_enroll' => (int) $row->is_enroll,
                'enrolled' => $enrolled,
                'payment_required' => $paymentRequired,
                // enroll للمدير فقط؛ المعلم يرى الحالة دون تنفيذها
                'enroll_allowed_for_teacher' => false,
                'enroll_blocked_reason' => 'Enrolling online admissions is restricted to administrators',
                'can_proceed_to_enroll' => false,
                'guidance' => (! $formSubmitted || ($paymentRequired && ! $paid))
                    ? 'Form is not submitted or payment is pending — enrollment would require confirmation even for admins'
                    : 'Form is submitted and payment is settled — an administrator may enroll this admission',
            ],
        ]);
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        $ctx = $this->context($request);

        if (! TeacherRbac::hasPrivilege((int) $ctx->staff->id, 'online_admission', 'can_delete')) {
            return $this->errorResponse('Forbidden: missing online_admission can_delete permission', null, 403);
        }

        $row = DB::table('online_admissions')->where('id', $id)->first(['id', 'class_section_id']);

        if (! $row) {
            return $this->errorResponse('Online admission not found', null, 404);
        }

        if (! in_array((int) $row->class_section_id, $this->scopedClassSectionIds($ctx->staff->id, $ctx->sessionId), true)) {
            return $this->errorResponse('Admission is not in your scope', null, 403);
        }

        DB::transaction(function () use ($id) {
            DB::table('online_admission_custom_field_value')->where('belong_table_id', $id)->delete();
            DB::table('online_admissions')->where('id', $id)->delete();
        });

        return $this->successResponse(null, 'Online admission deleted successfully');
    }

    /** أعلام الإجراءات المتاحة للمعلم على الطلب (enroll إداري حصري). */
    private function actionFlags(array $row): array
    {
        $enrolled = (int) ($row['is_enroll'] ?? 0) === 1;

        return [
            'actions' => [
                'can_edit' => ! $enrolled,
                'can_delete' => true,
                'enroll_allowed_for_teacher' => false,
                'enroll_blocked_reason' => 'Enrolling online admissions is restricted to administrators',
            ],
        ];
    }

    /** @return list<int> */
    private function scopedClassSectionIds(int $staffId, int $sessionId): array    {
        return collect(TeacherScope::myClasses($staffId, $sessionId))
            ->pluck('class_section_id')
            ->map(fn ($id): int => (int) $id)
            ->all();
    }

    /** الاستعلام الأساسي مطابق لاختيار CI في Onlinestudent_model::get / getstudentlist */
    private function baseQuery()
    {
        return DB::table('online_admissions')
            ->leftJoin('class_sections', 'class_sections.id', '=', 'online_admissions.class_section_id')
            ->leftJoin('classes', 'classes.id', '=', 'class_sections.class_id')
            ->leftJoin('sections', 'sections.id', '=', 'class_sections.section_id')
            ->leftJoin('categories', 'categories.id', '=', 'online_admissions.category_id')
            ->leftJoin('school_houses', 'school_houses.id', '=', 'online_admissions.school_house_id')
            ->select(
                'online_admissions.id',
                'online_admissions.reference_no',
                'online_admissions.admission_no',
                'online_admissions.roll_no',
                'online_admissions.admission_date',
                'online_admissions.firstname',
                'online_admissions.middlename',
                'online_admissions.lastname',
                'online_admissions.image',
                'online_admissions.mobileno',
                'online_admissions.email',
                'online_admissions.dob',
                'online_admissions.gender',
                'online_admissions.religion',
                'online_admissions.cast',
                'online_admissions.current_address',
                'online_admissions.permanent_address',
                'online_admissions.state',
                'online_admissions.city',
                'online_admissions.pincode',
                'online_admissions.previous_school',
                'online_admissions.father_name',
                'online_admissions.father_phone',
                'online_admissions.father_occupation',
                'online_admissions.mother_name',
                'online_admissions.mother_phone',
                'online_admissions.mother_occupation',
                'online_admissions.guardian_is',
                'online_admissions.guardian_name',
                'online_admissions.guardian_relation',
                'online_admissions.guardian_phone',
                'online_admissions.guardian_email',
                'online_admissions.guardian_address',
                'online_admissions.rte',
                'online_admissions.note',
                'online_admissions.form_status',
                'online_admissions.paid_status',
                'online_admissions.is_enroll',
                'online_admissions.created_at',
                'online_admissions.class_section_id',
                'class_sections.class_id',
                'class_sections.section_id',
                'classes.class as class_name',
                'sections.section as section_name',
                DB::raw('IFNULL(categories.category, "") as category'),
                'school_houses.house_name',
            );
    }
}
