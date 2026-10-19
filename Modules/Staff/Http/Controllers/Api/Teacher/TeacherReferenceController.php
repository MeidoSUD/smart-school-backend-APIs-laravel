<?php

namespace Modules\Staff\Http\Controllers\Api\Teacher;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * بيانات مرجعية للقراءة — parity مع CI (عرض فقط للدور 2):
 * - admin/Grade::index (marks_grade:can_view)
 * - Category (student_categories:can_view) + Schoolhouse (student_houses:can_view)
 * لا إنشاء/تعديل/حذف — تلك وظائف إدارة خارج نطاق المعلم.
 */
class TeacherReferenceController extends TeacherController
{
    public function __construct()
    {
        $this->setControllerName('TeacherReferenceController');
    }

    public function grades(Request $request): JsonResponse
    {
        $this->context($request);
        $rows = DB::table('grades')->orderBy('mark_from')->get();

        return $this->successResponse(['grades' => $rows]);
    }

    public function studentCategories(Request $request): JsonResponse
    {
        $this->context($request);
        $rows = DB::table('categories')->select('id', 'category as name')->get();

        return $this->successResponse(['categories' => $rows]);
    }

    public function studentHouses(Request $request): JsonResponse
    {
        $this->context($request);
        $rows = DB::table('school_houses')->select('id', 'house_name as name', 'description')->get();

        return $this->successResponse(['houses' => $rows]);
    }

    public function staffCardTemplates(Request $request): JsonResponse
    {
        $this->context($request);

        $templates = DB::table('staff_id_card')->where('status', 1)->get();

        return $this->successResponse(['templates' => $templates]);
    }

    public function myStaffCard(Request $request): JsonResponse
    {
        $ctx = $this->context($request);

        $v = $request->validate([
            'template_id' => 'nullable|integer',
        ]);

        $templateQuery = DB::table('staff_id_card')->where('status', 1);
        if (! empty($v['template_id'])) {
            $template = (clone $templateQuery)->where('id', $v['template_id'])->first();
        } else {
            $template = (clone $templateQuery)->first();
        }

        $schSetting = DB::table('sch_settings')->first();

        $staff = DB::table('staff')
            ->leftJoin('staff_roles', 'staff_roles.staff_id', '=', 'staff.id')
            ->leftJoin('roles', 'roles.id', '=', 'staff_roles.role_id')
            ->leftJoin('department', 'department.id', '=', 'staff.department')
            ->leftJoin('staff_designation', 'staff_designation.id', '=', 'staff.designation')
            ->where('staff.id', $ctx->staff->id)
            ->select(
                'staff.id',
                'staff.employee_id',
                'staff.name',
                'staff.surname',
                'staff.father_name',
                'staff.mother_name',
                'staff.contact_no',
                'staff.emergency_contact_no',
                'staff.email',
                'staff.dob',
                'staff.date_of_joining',
                'staff.local_address',
                'staff.permanent_address',
                'staff.image',
                'roles.name as role_name',
                'department.department_name',
                'staff_designation.designation as designation_name'
            )
            ->first();

        return $this->successResponse([
            'staff' => $staff,
            'template' => $template,
            'school_setting' => $schSetting,
        ]);
    }
}

