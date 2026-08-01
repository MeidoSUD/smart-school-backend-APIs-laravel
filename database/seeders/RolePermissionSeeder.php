<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Role;
use App\Models\PermissionGroup;
use App\Models\PermissionCategory;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        $permissionGroups = [
            'Students' => [
                'student_list', 'student_create', 'student_edit', 'student_delete',
                'student_view', 'student_import', 'student_export', 'student_send_email',
                'student_send_sms', 'student_change_status',
            ],
            'Staff' => [
                'staff_list', 'staff_create', 'staff_edit', 'staff_delete',
                'staff_view', 'staff_import', 'staff_export', 'staff_send_email',
                'staff_send_sms', 'staff_change_status', 'staff_salary',
            ],
            'Fees' => [
                'fee_list', 'fee_create', 'fee_edit', 'fee_delete',
                'fee_collect', 'fee_discount', 'fee_export', 'fee_receipt',
                'fee_invoice', 'fee_search', 'offline_fee_payment',
            ],
            'Examination' => [
                'exam_list', 'exam_create', 'exam_edit', 'exam_delete',
                'exam_publish', 'exam_schedule', 'exam_result',
                'marks_entry', 'marks_edit', 'marks_delete', 'marks_publish',
            ],
            'Attendance' => [
                'attendance_list', 'attendance_create', 'attendance_edit',
                'attendance_delete', 'attendance_report', 'attendance_sms',
            ],
            'Homework' => [
                'homework_list', 'homework_create', 'homework_edit', 'homework_delete',
                'homework_evaluate', 'homework_export', 'homework_sms',
            ],
            'Library' => [
                'book_list', 'book_create', 'book_edit', 'book_delete',
                'book_issue', 'book_return', 'book_fine', 'member_list',
                'member_create', 'member_delete',
            ],
            'Hostel' => [
                'hostel_list', 'hostel_create', 'hostel_edit', 'hostel_delete',
                'room_list', 'room_create', 'room_edit', 'room_delete',
                'hostel_allocate', 'hostel_deallocate',
            ],
            'Transport' => [
                'vehicle_list', 'vehicle_create', 'vehicle_edit', 'vehicle_delete',
                'route_list', 'route_create', 'route_edit', 'route_delete',
                'vehicle_allocate', 'vehicle_deallocate',
            ],
            'Income' => [
                'income_list', 'income_create', 'income_edit', 'income_delete',
                'income_head_list', 'income_head_create', 'income_head_edit',
                'income_head_delete', 'income_report',
            ],
            'Expense' => [
                'expense_list', 'expense_create', 'expense_edit', 'expense_delete',
                'expense_head_list', 'expense_head_create', 'expense_head_edit',
                'expense_head_delete', 'expense_report',
            ],
            'Communication' => [
                'notice_list', 'notice_create', 'notice_edit', 'notice_delete',
                'notice_send_email', 'notice_send_sms',
                'event_list', 'event_create', 'event_edit', 'event_delete',
                'email_send', 'sms_send', 'chat',
            ],
            'Academics' => [
                'class_list', 'class_create', 'class_edit', 'class_delete',
                'section_list', 'section_create', 'section_edit', 'section_delete',
                'subject_list', 'subject_create', 'subject_edit', 'subject_delete',
                'timetable_list', 'timetable_create', 'timetable_edit', 'timetable_delete',
                'syllabus_list', 'syllabus_create', 'syllabus_edit', 'syllabus_delete',
                'lesson_plan_list', 'lesson_plan_create', 'lesson_plan_edit', 'lesson_plan_delete',
            ],
            'Reports' => [
                'student_report', 'staff_report', 'fee_report', 'exam_report',
                'attendance_report', 'homework_report', 'income_report', 'expense_report',
                'payroll_report', 'library_report', 'hostel_report', 'transport_report',
            ],
            'System' => [
                'role_list', 'role_create', 'role_edit', 'role_delete',
                'permission_manage', 'language_manage', 'currency_manage',
                'notification_manage', 'backup_database', 'system_settings',
                'online_admission', 'custom_field', 'custom_field_value',
                'mail_settings', 'sms_settings', 'payment_settings',
                'general_settings', 'frontend_settings',
            ],
        ];

        $roles = [
            'admin' => [
                'is_active' => 1, 'is_superadmin' => 1, 'is_staff' => 1,
                'is_student' => 0, 'is_parent' => 0, 'is_admin' => 1,
            ],
            'teacher' => [
                'is_active' => 1, 'is_superadmin' => 0, 'is_staff' => 1,
                'is_student' => 0, 'is_parent' => 0, 'is_admin' => 0,
            ],
            'accountant' => [
                'is_active' => 1, 'is_superadmin' => 0, 'is_staff' => 1,
                'is_student' => 0, 'is_parent' => 0, 'is_admin' => 0,
            ],
            'librarian' => [
                'is_active' => 1, 'is_superadmin' => 0, 'is_staff' => 1,
                'is_student' => 0, 'is_parent' => 0, 'is_admin' => 0,
            ],
            'student' => [
                'is_active' => 1, 'is_superadmin' => 0, 'is_staff' => 0,
                'is_student' => 1, 'is_parent' => 0, 'is_admin' => 0,
            ],
            'parent' => [
                'is_active' => 1, 'is_superadmin' => 0, 'is_staff' => 0,
                'is_student' => 0, 'is_parent' => 1, 'is_admin' => 0,
            ],
            'staff' => [
                'is_active' => 1, 'is_superadmin' => 0, 'is_staff' => 1,
                'is_student' => 0, 'is_parent' => 0, 'is_admin' => 0,
            ],
        ];

        foreach ($permissionGroups as $groupName => $categories) {
            $group = PermissionGroup::firstOrCreate(
                ['permission_group' => $groupName],
                ['is_active' => 1]
            );

            foreach ($categories as $categoryName) {
                PermissionCategory::firstOrCreate(
                    ['name' => $categoryName],
                    ['permission_group_id' => $group->id, 'is_active' => 1]
                );
            }
        }

        foreach ($roles as $roleName => $roleData) {
            $role = Role::firstOrCreate(
                ['name' => $roleName],
                $roleData
            );

            if ($roleName === 'admin') {
                $allPermissions = PermissionCategory::pluck('id')->toArray();
                foreach ($allPermissions as $permId) {
                    DB::table('roles_permissions')->updateOrInsert(
                        ['role_id' => $role->id, 'permission_category_id' => $permId],
                        ['is_active' => 1]
                    );
                }
            } elseif ($roleName === 'teacher') {
                $teacherPerms = ['student_list', 'student_view', 'homework_list', 'homework_create',
                    'homework_edit', 'homework_evaluate', 'exam_list', 'exam_schedule', 'exam_result',
                    'marks_entry', 'marks_publish', 'attendance_list', 'attendance_create', 'attendance_edit',
                    'class_list', 'section_list', 'subject_list', 'timetable_list', 'syllabus_list',
                    'syllabus_create', 'lesson_plan_list', 'lesson_plan_create', 'book_list', 'book_issue',
                    'notice_list', 'event_list', 'chat', 'student_report', 'exam_report',
                    'attendance_report', 'homework_report'];
                foreach ($teacherPerms as $permName) {
                    $perm = PermissionCategory::where('name', $permName)->first();
                    if ($perm) {
                        DB::table('roles_permissions')->updateOrInsert(
                            ['role_id' => $role->id, 'permission_category_id' => $perm->id],
                            ['is_active' => 1]
                        );
                    }
                }
            } elseif ($roleName === 'accountant') {
                $accountantPerms = ['fee_list', 'fee_collect', 'fee_discount', 'fee_export', 'fee_receipt',
                    'fee_invoice', 'fee_search', 'offline_fee_payment', 'income_list', 'income_create',
                    'income_edit', 'income_head_list', 'income_head_create', 'expense_list', 'expense_create',
                    'expense_edit', 'expense_head_list', 'expense_head_create', 'student_list', 'student_view',
                    'staff_list', 'staff_view', 'staff_salary', 'fee_report', 'income_report', 'expense_report',
                    'payroll_report'];
                foreach ($accountantPerms as $permName) {
                    $perm = PermissionCategory::where('name', $permName)->first();
                    if ($perm) {
                        DB::table('roles_permissions')->updateOrInsert(
                            ['role_id' => $role->id, 'permission_category_id' => $perm->id],
                            ['is_active' => 1]
                        );
                    }
                }
            } elseif ($roleName === 'librarian') {
                $librarianPerms = ['book_list', 'book_create', 'book_edit', 'book_delete',
                    'book_issue', 'book_return', 'book_fine', 'member_list', 'member_create', 'member_delete',
                    'library_report'];
                foreach ($librarianPerms as $permName) {
                    $perm = PermissionCategory::where('name', $permName)->first();
                    if ($perm) {
                        DB::table('roles_permissions')->updateOrInsert(
                            ['role_id' => $role->id, 'permission_category_id' => $perm->id],
                            ['is_active' => 1]
                        );
                    }
                }
            }
        }
    }
}
