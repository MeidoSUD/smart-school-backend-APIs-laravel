<?php

declare(strict_types=1);

namespace Modules\Staff\Http\Controllers\Api\Teacher;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Modules\Staff\Services\TeacherRbac;
use Modules\Staff\Services\TeacherScope;

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

        $query = $this->baseQuery()
            ->when(! empty($v['class_id']), fn ($q) => $q->where('classes.id', $v['class_id']))
            ->when(! empty($v['section_id']), fn ($q) => $q->where('sections.id', $v['section_id']))
            ->when(! empty($v['search']), function ($q) use ($v) {
                $term = '%'.$v['search'].'%';
                $q->where(function ($w) use ($term) {
                    $w->where('online_admissions.reference_no', 'like', $term)
                        ->orWhere('online_admissions.firstname', 'like', $term)
                        ->orWhere('online_admissions.lastname', 'like', $term)
                        ->orWhere('online_admissions.father_name', 'like', $term)
                        ->orWhere('online_admissions.dob', 'like', $term)
                        ->orWhere('online_admissions.gender', 'like', $term)
                        ->orWhere('online_admissions.mobileno', 'like', $term)
                        ->orWhere('classes.class', 'like', $term)
                        ->orWhere('categories.category', 'like', $term);
                });
            })
            ->orderByDesc('online_admissions.id');

        $paginator = $query->paginate((int) ($v['per_page'] ?? 25));

        $items = collect($paginator->items())->map(function ($row) {
            $studentName = trim(implode(' ', array_filter([$row->firstname, $row->middlename, $row->lastname])));
            return [
                'id' => (int) $row->id,
                'reference_no' => $row->reference_no,
                'student_name' => $studentName,
                'firstname' => $row->firstname,
                'middlename' => $row->middlename,
                'lastname' => $row->lastname,
                'class' => $row->class,
                'section' => $row->section,
                'class_name' => $row->class_name,
                'section_name' => $row->section_name,
                'class_id' => $row->class_id ? (int) $row->class_id : null,
                'section_id' => $row->section_id ? (int) $row->section_id : null,
                'class_section_id' => $row->class_section_id ? (int) $row->class_section_id : null,
                'father_name' => $row->father_name,
                'father_phone' => $row->father_phone,
                'mother_name' => $row->mother_name,
                'guardian_name' => $row->guardian_name,
                'guardian_phone' => $row->guardian_phone,
                'guardian_relation' => $row->guardian_relation,
                'dob' => $row->dob,
                'gender' => $row->gender,
                'category' => $row->category,
                'category_id' => (int) $row->category_id,
                'mobileno' => $row->mobileno,
                'email' => $row->email,
                'form_status' => (int) $row->form_status,
                'submit_date' => $row->submit_date,
                'paid_status' => (int) $row->paid_status,
                'is_enroll' => (int) $row->is_enroll,
                'note' => $row->note ?? '',
                'document' => $row->document,
                'current_address' => $row->current_address,
                'permanent_address' => $row->permanent_address,
                'route_title' => $row->route_title,
                'vehicle_no' => $row->vehicle_no,
                'hostel_name' => $row->hostel_name,
                'room_no' => $row->room_no,
                'created_at' => $row->created_at,
            ];
        })->all();

        return $this->successResponse([
            'admissions' => $items,
            'pagination' => [
                'total' => $paginator->total(),
                'per_page' => $paginator->perPage(),
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
            ],
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

        return $this->successResponse([
            'admission' => $row,
        ] + $this->actionFlags((array) $row));
    }

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

        DB::transaction(function () use ($id) {
            DB::table('online_admission_custom_field_value')->where('belong_table_id', $id)->delete();
            DB::table('online_admissions')->where('id', $id)->delete();
        });

        return $this->successResponse(null, 'Online admission deleted successfully');
    }

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

    private function scopedClassSectionIds(int $staffId, int $sessionId): array
    {
        return collect(TeacherScope::myClasses($staffId, $sessionId))
            ->pluck('class_section_id')
            ->map(fn ($id): int => (int) $id)
            ->all();
    }

    private function baseQuery()
    {
        return DB::table('online_admissions')
            ->leftJoin('class_sections', 'class_sections.id', '=', 'online_admissions.class_section_id')
            ->leftJoin('classes', 'classes.id', '=', 'class_sections.class_id')
            ->leftJoin('sections', 'sections.id', '=', 'class_sections.section_id')
            ->leftJoin('categories', 'categories.id', '=', 'online_admissions.category_id')
            ->leftJoin('school_houses', 'school_houses.id', '=', 'online_admissions.school_house_id')
            ->leftJoin('hostel_rooms', 'hostel_rooms.id', '=', 'online_admissions.hostel_room_id')
            ->leftJoin('hostel', 'hostel.id', '=', 'hostel_rooms.hostel_id')
            ->leftJoin('room_types', 'room_types.id', '=', 'hostel_rooms.room_type_id')
            ->leftJoin('vehicle_routes', 'vehicle_routes.id', '=', 'online_admissions.vehroute_id')
            ->leftJoin('transport_route', 'vehicle_routes.route_id', '=', 'transport_route.id')
            ->leftJoin('vehicles', 'vehicles.id', '=', 'vehicle_routes.vehicle_id')
            ->select(
                'online_admissions.*',
                'class_sections.class_id',
                'class_sections.section_id',
                'classes.class',
                'classes.class as class_name',
                'sections.section',
                'sections.section as section_name',
                DB::raw('IFNULL(online_admissions.category_id, 0) as category_id'),
                DB::raw('IFNULL(categories.category, "") as category'),
                'school_houses.house_name',
                'transport_route.route_title',
                'vehicle_routes.route_id',
                'vehicle_routes.vehicle_id',
                'vehicles.vehicle_no',
                'vehicles.driver_name',
                'vehicles.driver_contact',
                'hostel.id as hostel_id',
                'hostel.hostel_name',
                'room_types.id as room_type_id',
                'room_types.room_type',
                'hostel_rooms.room_no'
            );
    }
}
