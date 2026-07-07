<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Modules\Core\Entities\Session;
use Modules\Staff\Entities\Staff;
use Modules\Academic\Entities\StudentSession;
use Modules\Operations\Entities\LibraryMember;

class DashboardSupportSeeder extends Seeder
{
    public function run(): void
    {
        $session = Session::where('is_active', 'yes')->first() ?? Session::first();
        if (!$session) {
            return;
        }

        // Truncate all managed tables on re-run (except users, staff)
        if (DB::table('income_head')->exists()) {
            DB::statement('SET FOREIGN_KEY_CHECKS=0');
            DB::table('income_head')->truncate();
            DB::table('income')->truncate();
            DB::table('expense_head')->truncate();
            DB::table('expenses')->truncate();
            DB::table('enquiry')->truncate();
            DB::table('follow_up')->truncate();
            DB::table('book_issues')->truncate();
            DB::table('staff_attendance')->truncate();
            DB::table('send_notification')->truncate();
            DB::table('notification_roles')->truncate();
            DB::table('events')->truncate();
            DB::table('student_attendences')->where('date', now()->format('Y-m-d'))->delete();
            DB::table('student_fees_deposite')
                ->where('amount_detail', 'LIKE', '%"payment_mode":"Cash"%')->delete();
            DB::table('staff_roles')->where('is_active', 1)->delete();
            DB::statement('SET FOREIGN_KEY_CHECKS=1');
        }

        // Fix sch_settings.currency and currency_format
        DB::table('sch_settings')->where('currency', 'SAR')->update(['currency' => 124]);
        DB::table('sch_settings')->where('currency_format', '2')->update(['currency_format' => '#,###.##']);

        // Fix admin session stale values (currency_base_price, currency_format)
        $sessionDir = dirname(__DIR__, 3) . '/codeigniter/application/sessions';
        if (is_dir($sessionDir)) {
            $sessionFixes = [
                's:19:"currency_base_price";s:0:""' => 's:19:"currency_base_price";s:1:"1"',
                's:15:"currency_format";s:1:"2"' => 's:15:"currency_format";s:9:"#,###.##"',
            ];
            foreach (glob($sessionDir . '/ci_session*') as $sf) {
                if (!is_file($sf) || !is_writable($sf)) continue;
                $content = file_get_contents($sf);
                $dirty = false;
                foreach ($sessionFixes as $old => $new) {
                    if (str_contains($content, $old)) {
                        $content = str_replace($old, $new, $content);
                        $dirty = true;
                    }
                }
                if ($dirty) {
                    file_put_contents($sf, $content);
                }
            }
        }

        $sessionYears = explode('-', $session->session);
        $sessionStartYear = isset($sessionYears[0]) ? (int)$sessionYears[0] : (int)now()->format('Y');
        $ref = now()->copy()->setDate($sessionStartYear + 1, 4, 15);
        if ($ref->isFuture()) $ref = now();
        $refDate = $ref->format('Y-m-d');
        $now = now();
        $today = $now->format('Y-m-d');

        // ====================================================================
        // 1. ROLES
        // ====================================================================
        $roles = [
            ['id' => 1, 'name' => 'Admin', 'is_active' => 1, 'is_system' => 1, 'is_superadmin' => 0],
            ['id' => 2, 'name' => 'Teacher', 'is_active' => 1, 'is_system' => 1, 'is_superadmin' => 0],
            ['id' => 3, 'name' => 'Accountant', 'is_active' => 1, 'is_system' => 1, 'is_superadmin' => 0],
            ['id' => 4, 'name' => 'Librarian', 'is_active' => 1, 'is_system' => 1, 'is_superadmin' => 0],
            ['id' => 5, 'name' => 'Staff', 'is_active' => 1, 'is_system' => 1, 'is_superadmin' => 0],
            ['id' => 6, 'name' => 'Receptionist', 'is_active' => 1, 'is_system' => 1, 'is_superadmin' => 0],
            ['id' => 7, 'name' => 'Super Admin', 'is_active' => 1, 'is_system' => 1, 'is_superadmin' => 1],
        ];
        $existingRoleIds = DB::table('roles')->pluck('id')->toArray();
        foreach ($roles as $r) {
            if (!in_array($r['id'], $existingRoleIds)) {
                DB::table('roles')->insert($r + ['slug' => null, 'created_at' => $ref, 'updated_at' => $refDate]);
            }
        }

        // ====================================================================
        // 2. STAFF ROLES
        // ====================================================================
        $staff = Staff::all();
        $roleMap = [
            'TCH2024001' => 2, 'STF2024001' => 5,
            'ACC2024001' => 3, 'LIB2024001' => 4,
        ];
        $existingStaffRoles = DB::table('staff_roles')->get()->map(fn($sr) => $sr->role_id . '-' . $sr->staff_id)->toArray();
        foreach ($staff as $s) {
            $roleId = $roleMap[$s->employee_id] ?? 5;
            $key = $roleId . '-' . $s->id;
            if (!in_array($key, $existingStaffRoles)) {
                DB::table('staff_roles')->insert([
                    'role_id' => $roleId, 'staff_id' => $s->id,
                    'is_active' => 1, 'created_at' => $ref, 'updated_at' => $refDate,
                ]);
            }
        }
        if ($staff->isNotEmpty()) {
            $adminKey = '1-' . $staff->first()->id;
            if (!in_array($adminKey, $existingStaffRoles)) {
                DB::table('staff_roles')->insert([
                    'role_id' => 1, 'staff_id' => $staff->first()->id,
                    'is_active' => 1, 'created_at' => $ref, 'updated_at' => $refDate,
                ]);
            }
        }

        // ====================================================================
        // 3. FIX SESSIONS
        // ====================================================================
        $wrongSessionIds = DB::table('student_session')
            ->where('session_id', '!=', $session->id)
            ->distinct()->pluck('session_id')->toArray();
        if (!empty($wrongSessionIds)) {
            $tablesWithSession = [
                'student_session' => 'session_id', 'fee_session_groups' => 'session_id',
                'fee_groups_feetype' => 'session_id', 'transport_feemaster' => 'session_id', 'feemasters' => 'session_id',
                'fees_discounts' => 'session_id', 'class_teacher' => 'session_id',
                'homework' => 'session_id', 'exam_schedules' => 'session_id',
                'onlineexam' => 'session_id', 'subject_group_subjects' => 'session_id',
                'subject_group_class_sections' => 'session_id', 'subject_timetable' => 'session_id',
            ];
            foreach ($tablesWithSession as $table => $col) {
                if (Schema::hasColumn($table, $col)) {
                    DB::table($table)->whereIn($col, $wrongSessionIds)->update([$col => $session->id]);
                }
            }
            if (Schema::hasColumn('exams', 'sesion_id')) {
                DB::table('exams')->whereIn('sesion_id', $wrongSessionIds)->update(['sesion_id' => $session->id]);
            }
        }

        // Fix other tables that might have wrong session_id (even if wrongSessionIds is now empty)
        if (Schema::hasColumn('fee_session_groups', 'session_id')) {
            DB::table('fee_session_groups')
                ->where('session_id', '!=', $session->id)
                ->update(['session_id' => $session->id]);
        }

        DB::table('sch_settings')->where('id', 1)->update([
            'session_id' => $session->id, 'start_month' => '9',
        ]);
        DB::table('sessions')
            ->where('id', '!=', $session->id)->where('session', $session->session)
            ->update(['is_active' => 'no']);

        // ====================================================================
        // 4. ATTENDANCE TYPES (fallback)
        // ====================================================================
        if (!DB::table('attendence_type')->where('id', 1)->exists()) {
            DB::table('attendence_type')->insert([
                ['id' => 1, 'type' => 'Present', 'key_value' => 'P', 'long_lang_name' => 'present', 'long_name_style' => 'label label-success', 'is_active' => 'yes', 'for_qr_attendance' => 1, 'created_at' => $ref, 'updated_at' => $refDate],
                ['id' => 2, 'type' => 'Late With Excuse', 'key_value' => 'E', 'long_lang_name' => 'late_with_excuse', 'long_name_style' => 'label label-warning', 'is_active' => 'no', 'for_qr_attendance' => 0, 'created_at' => $ref, 'updated_at' => $refDate],
                ['id' => 3, 'type' => 'Late', 'key_value' => 'L', 'long_lang_name' => 'late', 'long_name_style' => 'label label-warning', 'is_active' => 'yes', 'for_qr_attendance' => 1, 'created_at' => $ref, 'updated_at' => $refDate],
                ['id' => 4, 'type' => 'Absent', 'key_value' => 'A', 'long_lang_name' => 'absent', 'long_name_style' => 'label label-danger', 'is_active' => 'yes', 'for_qr_attendance' => 0, 'created_at' => $ref, 'updated_at' => $refDate],
                ['id' => 5, 'type' => 'Holiday', 'key_value' => 'H', 'long_lang_name' => 'holiday', 'long_name_style' => 'label label-info', 'is_active' => 'yes', 'for_qr_attendance' => 0, 'created_at' => $ref, 'updated_at' => $refDate],
                ['id' => 6, 'type' => 'Half Day', 'key_value' => 'F', 'long_lang_name' => 'half_day', 'long_name_style' => 'label label-warning', 'is_active' => 'yes', 'for_qr_attendance' => 1, 'created_at' => $ref, 'updated_at' => $refDate],
            ]);
        }
        if (!DB::table('staff_attendance_type')->where('id', 1)->exists()) {
            DB::table('staff_attendance_type')->insert([
                ['id' => 1, 'type' => 'Present', 'key_value' => 'P', 'is_active' => 'yes', 'for_qr_attendance' => 1, 'long_lang_name' => 'present', 'long_name_style' => 'label label-success', 'created_at' => $ref, 'updated_at' => $refDate],
                ['id' => 2, 'type' => 'Late', 'key_value' => 'L', 'is_active' => 'yes', 'for_qr_attendance' => 1, 'long_lang_name' => 'late', 'long_name_style' => 'label label-warning', 'created_at' => $ref, 'updated_at' => $refDate],
                ['id' => 3, 'type' => 'Absent', 'key_value' => 'A', 'is_active' => 'yes', 'for_qr_attendance' => 0, 'long_lang_name' => 'absent', 'long_name_style' => 'label label-danger', 'created_at' => $ref, 'updated_at' => $refDate],
                ['id' => 4, 'type' => 'Half Day', 'key_value' => 'F', 'is_active' => 'yes', 'for_qr_attendance' => 1, 'long_lang_name' => 'half_day', 'long_name_style' => 'label label-info', 'created_at' => $ref, 'updated_at' => $refDate],
                ['id' => 5, 'type' => 'Holiday', 'key_value' => 'H', 'is_active' => 'yes', 'for_qr_attendance' => 0, 'long_lang_name' => 'holiday', 'long_name_style' => 'label label-warning', 'created_at' => $ref, 'updated_at' => $refDate],
            ]);
        }

        // ====================================================================
        // 5. INCOME HEADS + INCOME
        // ====================================================================
        $incomeCategories = ['الرسوم الدراسية', 'رسوم النقل', 'رسوم الأنشطة', 'التبرعات', 'إيرادات أخرى'];
        $incomeHeadIds = [];
        foreach ($incomeCategories as $cat) {
            $incomeHeadIds[] = DB::table('income_head')->insertGetId([
                'income_category' => $cat, 'description' => $cat,
                'is_active' => 'yes', 'is_deleted' => 'no',
                'created_at' => $ref, 'updated_at' => $refDate,
            ]);
        }

        $incomeRecords = [];
        // Monthly income across session (for yearly chart)
        for ($m = 0; $m < 9; $m++) {
            $dt = $ref->copy()->subMonths($m);
            $incomeRecords[] = [
                'income_head_id' => $incomeHeadIds[$m % 5],
                'name' => 'إيراد ' . $incomeCategories[$m % 5],
                'invoice_no' => 'INV-' . $dt->format('Ym') . '-' . str_pad($m + 1, 3, '0', STR_PAD_LEFT),
                'date' => $dt->format('Y-m-d'), 'amount' => rand(15000, 80000) + 0.00,
                'note' => '', 'is_active' => 'yes', 'is_deleted' => 'no',
                'created_at' => $dt, 'updated_at' => $refDate,
            ];
        }
        // Current month income per head (for current month chart)
        foreach ($incomeHeadIds as $i => $hid) {
            $incomeRecords[] = [
                'income_head_id' => $hid,
                'name' => 'إيراد ' . $incomeCategories[$i],
                'invoice_no' => 'INV-' . $now->format('Ym') . '-C' . ($i + 1),
                'date' => $today, 'amount' => rand(5000, 40000) + 0.00,
                'note' => '', 'is_active' => 'yes', 'is_deleted' => 'no',
                'created_at' => $now, 'updated_at' => $today,
            ];
        }
        foreach ($incomeRecords as $r) {
            DB::table('income')->insert($r);
        }

        // ====================================================================
        // 6. EXPENSE HEADS + EXPENSES
        // ====================================================================
        $expenseCategories = ['رواتب الموظفين', 'الصيانة', 'القرطاسية', 'الخدمات', 'مصروفات أخرى'];
        $expenseHeadIds = [];
        foreach ($expenseCategories as $cat) {
            $expenseHeadIds[] = DB::table('expense_head')->insertGetId([
                'exp_category' => $cat, 'description' => $cat,
                'is_active' => 'yes', 'is_deleted' => 'no',
                'created_at' => $ref, 'updated_at' => $refDate,
            ]);
        }

        $expenseRecords = [];
        // Monthly expenses across session
        for ($m = 0; $m < 9; $m++) {
            $dt = $ref->copy()->subMonths($m);
            $expenseRecords[] = [
                'exp_head_id' => $expenseHeadIds[$m % 5],
                'name' => 'مصروف ' . $expenseCategories[$m % 5],
                'invoice_no' => 'EXP-' . $dt->format('Ym') . '-' . str_pad($m + 1, 3, '0', STR_PAD_LEFT),
                'date' => $dt->copy()->endOfMonth()->format('Y-m-d'), 'amount' => rand(5000, 70000) + 0.00,
                'note' => '', 'is_active' => 'yes', 'is_deleted' => 'no',
                'created_at' => $dt->copy()->endOfMonth(), 'updated_at' => $refDate,
            ];
        }
        // Current month expenses per head
        foreach ($expenseHeadIds as $i => $hid) {
            $expenseRecords[] = [
                'exp_head_id' => $hid,
                'name' => 'مصروف ' . $expenseCategories[$i],
                'invoice_no' => 'EXP-' . $now->format('Ym') . '-C' . ($i + 1),
                'date' => $today, 'amount' => rand(2000, 20000) + 0.00,
                'note' => '', 'is_active' => 'yes', 'is_deleted' => 'no',
                'created_at' => $now, 'updated_at' => $today,
            ];
        }
        // Daily expenses for current month (for daily expense chart)
        $nowDaysInMonth = (int) $now->format('d');
        for ($d = 1; $d <= $nowDaysInMonth; $d++) {
            $expenseRecords[] = [
                'exp_head_id' => $expenseHeadIds[array_rand($expenseHeadIds)],
                'name' => 'مصروف يومي',
                'invoice_no' => 'EXP-DLY-' . str_pad($d, 2, '0', STR_PAD_LEFT),
                'date' => $now->copy()->day($d)->format('Y-m-d'),
                'amount' => rand(200, 5000) + 0.00,
                'note' => '', 'is_active' => 'yes', 'is_deleted' => 'no',
                'created_at' => $now->copy()->day($d), 'updated_at' => $today,
            ];
        }
        foreach ($expenseRecords as $r) {
            DB::table('expenses')->insert($r);
        }

        // ====================================================================
        // 7. ENQUIRY LOOKUP TABLES
        // ====================================================================
        if (!DB::table('source')->exists()) {
            DB::table('source')->insert([
                ['source' => 'موقع المدرسة', 'description' => ''],
                ['source' => 'فيسبوك', 'description' => ''],
                ['source' => 'توصية', 'description' => ''],
                ['source' => 'زيارة شخصية', 'description' => ''],
            ]);
        }
        if (!DB::table('reference')->exists()) {
            DB::table('reference')->insert([
                ['reference' => 'صديق', 'description' => ''],
                ['reference' => 'إعلان', 'description' => ''],
                ['reference' => 'موقع إلكتروني', 'description' => ''],
            ]);
        }
        if (!DB::table('enquiry_type')->exists()) {
            DB::table('enquiry_type')->insert([
                ['enquiry_type' => 'عام', 'description' => ''],
                ['enquiry_type' => 'تسجيل', 'description' => ''],
                ['enquiry_type' => 'شكوى', 'description' => ''],
            ]);
        }

        // ====================================================================
        // 8. ENQUIRIES
        // ====================================================================
        $statuses = ['won', 'active', 'passive', 'dead', 'lost'];
        $enquiryData = [
            ['name' => 'أحمد السالم', 'contact' => '0555123401'],
            ['name' => 'نورة القحطاني', 'contact' => '0555123402'],
            ['name' => 'فهد العتيبي', 'contact' => '0555123403'],
            ['name' => 'سعود الشمري', 'contact' => '0555123404'],
            ['name' => 'مريم الزهراني', 'contact' => '0555123405'],
            ['name' => 'بدر الدوسري', 'contact' => '0555123406'],
            ['name' => 'هند المطيري', 'contact' => '0555123407'],
            ['name' => 'ماجد العنزي', 'contact' => '0555123408'],
            ['name' => 'لطيفة الحربي', 'contact' => '0555123409'],
            ['name' => 'سلطان البقمي', 'contact' => '0555123410'],
            ['name' => 'خالد القصبي', 'contact' => '0555123411'],
            ['name' => 'سارة الدوسري', 'contact' => '0555123412'],
            ['name' => 'نايف الصيعري', 'contact' => '0555123413'],
            ['name' => 'دلال المالكي', 'contact' => '0555123414'],
            ['name' => 'تركي العجمي', 'contact' => '0555123415'],
            ['name' => 'عبدالله البدر', 'contact' => '0555123416'],
            ['name' => 'نورة العبدالله', 'contact' => '0555123417'],
            ['name' => 'مشعل السبيعي', 'contact' => '0555123418'],
            ['name' => 'ريم الحمدان', 'contact' => '0555123419'],
            ['name' => 'عزام الشهري', 'contact' => '0555123420'],
        ];
        $enquiryIds = [];
        foreach ($enquiryData as $i => $enq) {
            $enqDate = $i < 10
                ? $ref->copy()->subDays(rand(1, 60))
                : $now->copy()->day(rand(1, (int) $now->format('d')));
            $status = $statuses[$i % 5];
            $eid = DB::table('enquiry')->insertGetId([
                'name' => $enq['name'], 'contact' => $enq['contact'],
                'address' => 'الرياض',
                'reference' => 'REF-' . str_pad($i + 1, 3, '0', STR_PAD_LEFT),
                'date' => $enqDate->format('Y-m-d'),
                'description' => 'استفسار عن التسجيل',
                'follow_up_date' => $enqDate->copy()->addDays(7)->format('Y-m-d'),
                'note' => '', 'source' => 'موقع المدرسة',
                'email' => 'enquiry' . ($i + 1) . '@email.com',
                'assigned' => $staff->isNotEmpty() ? $staff->first()->id : null,
                'class_id' => null, 'no_of_child' => '1',
                'status' => $status, 'created_by' => 1,
                'created_at' => $enqDate,
            ]);
            $enquiryIds[] = $eid;
        }

        // ====================================================================
        // 9. FOLLOW UPS
        // ====================================================================
        foreach ($enquiryIds as $i => $eid) {
            if ($i % 2 == 0) {
                DB::table('follow_up')->insert([
                    'enquiry_id' => $eid,
                    'date' => $ref->copy()->subDays(rand(1, 15))->format('Y-m-d'),
                    'next_date' => $ref->copy()->addDays(rand(1, 30))->format('Y-m-d'),
                    'response' => 'تم التواصل', 'note' => 'بحاجة متابعة',
                    'followup_by' => $staff->isNotEmpty() ? $staff->first()->id : 0,
                    'created_at' => $ref,
                ]);
            }
        }

        // ====================================================================
        // 10. BOOK ISSUES
        // ====================================================================
        $books = DB::table('books')->get();
        $libraryMembers = LibraryMember::all();
        foreach ($books as $bi => $book) {
            if ($bi >= 6) break;
            $isReturned = $bi < 3 ? 1 : 0;
            $issueDate = $ref->copy()->subDays(rand(10, 40));
            $dueDate = $issueDate->copy()->addDays(14);
            DB::table('book_issues')->insert([
                'book_id' => $book->id,
                'member_id' => $libraryMembers->isNotEmpty() ? $libraryMembers->random()->id : null,
                'duereturn_date' => $dueDate->format('Y-m-d'),
                'return_date' => $isReturned ? $ref->copy()->subDays(rand(1, 5))->format('Y-m-d') : null,
                'issue_date' => $issueDate->format('Y-m-d'),
                'is_returned' => $isReturned,
                'is_active' => 'yes',
                'created_at' => $issueDate,
            ]);
        }

        // ====================================================================
        // 11. STAFF ATTENDANCE
        // ====================================================================
        $sat = DB::table('staff_attendance_type')->get()->keyBy('type');
        $pType = $sat->get('Present')->id ?? 1;
        $aType = $sat->get('Absent')->id ?? 3;
        $lType = $sat->get('Late')->id ?? 2;

        // Today's staff attendance (matches PHP date('Y-m-d'))
        foreach ($staff as $s) {
            $present = rand(1, 10) > 2;
            DB::table('staff_attendance')->insert([
                'date' => $today, 'staff_id' => $s->id,
                'staff_attendance_type_id' => $present ? $pType : $aType,
                'remark' => $present ? 'حاضر' : 'غائب',
                'is_active' => 1, 'created_at' => $now, 'updated_at' => $today,
            ]);
        }
        // Past 20 days staff attendance
        for ($d = 1; $d <= 20; $d++) {
            $ad = $now->copy()->subDays($d);
            if ($ad->isFriday()) continue;
            foreach ($staff as $s) {
                $r = rand(1, 10);
                $tid = $r <= 1 ? $aType : ($r <= 2 ? $lType : $pType);
                DB::table('staff_attendance')->insert([
                    'date' => $ad->format('Y-m-d'), 'staff_id' => $s->id,
                    'staff_attendance_type_id' => $tid,
                    'remark' => $tid === $aType ? 'غائب' : ($tid === $lType ? 'متأخر' : 'حاضر'),
                    'is_active' => 1, 'created_at' => $ad, 'updated_at' => $ad->format('Y-m-d'),
                ]);
            }
        }

        // ====================================================================
        // 12. STUDENT TODAY ATTENDANCE (matches PHP date('Y-m-d'))
        // ====================================================================
        $studentSessions = StudentSession::where('is_active', 'yes')->get();
        $ats = DB::table('attendence_type')->get()->keyBy('type');
        $pa = $ats->get('Present')->id ?? 1;
        $aa = $ats->get('Absent')->id ?? 4;
        $la = $ats->get('Late')->id ?? 3;
        $ha = $ats->get('Half Day')->id ?? 6;

        foreach ($studentSessions as $ss) {
            $r = rand(1, 10);
            if ($r <= 1) { $tid = $aa; $rem = 'غائب'; }
            elseif ($r <= 2) { $tid = $la; $rem = 'متأخر'; }
            elseif ($r <= 3) { $tid = $ha; $rem = 'نصف يوم'; }
            else { $tid = $pa; $rem = 'حاضر'; }
            DB::table('student_attendences')->insert([
                'student_session_id' => $ss->id, 'date' => $today,
                'attendence_type_id' => $tid, 'remark' => $rem,
                'is_active' => 'yes', 'created_at' => $now, 'updated_at' => $today,
            ]);
        }

        // ====================================================================
        // 13. NOTIFICATIONS
        // ====================================================================
        $notifTitles = [
            'اجتماع أولياء الأمور', 'بدء الفصل الدراسي الثاني',
            'موعد الاختبارات النهائية', 'إجازة منتصف العام', 'تحديث بيانات الطلاب',
        ];
        foreach ($notifTitles as $i => $title) {
            $nd = $ref->copy()->subDays(count($notifTitles) - $i);
            $nid = DB::table('send_notification')->insertGetId([
                'title' => $title, 'publish_date' => $nd->format('Y-m-d'),
                'date' => $nd->format('Y-m-d'), 'attachment' => '',
                'message' => 'نود إعلامكم بأن ' . $title,
                'visible_student' => 'yes', 'visible_staff' => 'yes', 'visible_parent' => 'yes',
                'created_by' => 'Admin', 'created_id' => 1,
                'is_active' => 'yes', 'created_at' => $nd, 'updated_at' => $nd->format('Y-m-d'),
            ]);
            DB::table('notification_roles')->insert([
                'send_notification_id' => $nid, 'role_id' => 1, 'is_active' => 1, 'created_at' => $nd,
            ]);
            DB::table('notification_roles')->insert([
                'send_notification_id' => $nid, 'role_id' => 2, 'is_active' => 1, 'created_at' => $nd,
            ]);
        }

        // ====================================================================
        // 14. EVENTS
        // ====================================================================
        $eventColors = ['#03a9f4', '#c53da9', '#757575', '#8e24aa', '#d81b60', '#7cb342', '#fb8c00', '#fb3b3b'];
        $eventsList = [
            'بداية الفصل الدراسي', 'اجتماع الهيئة التعليمية',
            'يوم التربية البدنية', 'اختبارات منتصف الفصل', 'رحلة مدرسية',
            'ورشة عمل تطوير المعلمين', 'يوم المهنة المفتوح',
            'مسابقة القراءة', 'حفل تكريم المتفوقين', 'معرض العلوم السنوي',
        ];
        foreach ($eventsList as $i => $ev) {
            $es = $now->copy()->startOfMonth()->addDays($i);
            DB::table('events')->insert([
                'event_title' => $ev, 'event_description' => $ev,
                'start_date' => $es->format('Y-m-d H:i:s'),
                'end_date' => $es->copy()->addDay()->format('Y-m-d H:i:s'),
                'event_type' => 'public', 'event_color' => $eventColors[$i % 8],
                'event_for' => '0', 'role_id' => null, 'is_active' => 'yes',
            ]);
        }

        // ====================================================================
        // 15. FEE COLLECTIONS - Session-wide (for yearly chart)
        // ====================================================================
        $feeGroupFeetypes = DB::table('fee_groups_feetype')->get();
        // Spread deposits across Sep 2024 - Aug 2025 (session range)
        for ($m = 0; $m < 10; $m++) {
            $depositDate = $ref->copy()->subMonths($m);
            foreach ($studentSessions as $ss) {
                $sfm = DB::table('student_fees_master')
                    ->where('student_session_id', $ss->id)->first();
                if (!$sfm) continue;
                DB::table('student_fees_deposite')->insert([
                    'student_fees_master_id' => $sfm->id,
                    'fee_groups_feetype_id' => $feeGroupFeetypes->random()->id,
                    'amount_detail' => json_encode([[
                        'amount' => rand(500, 2000),
                        'amount_discount' => 0.00, 'amount_fine' => 0.00,
                        'date' => $depositDate->format('Y-m-d'),
                        'payment_mode' => 'Cash',
                        'description' => '', 'inv_no' => '',
                    ]]),
                    'is_active' => 'yes', 'created_at' => $depositDate,
                ]);
            }
        }

        // ====================================================================
        // 16. FEE COLLECTIONS - Current month daily (for daily chart)
        // ====================================================================
        for ($d = 1; $d <= $nowDaysInMonth; $d++) {
            $dayDate = $now->copy()->day($d);
            foreach ($studentSessions as $ss) {
                if (rand(1, 3) == 1) continue;
                $sfm = DB::table('student_fees_master')
                    ->where('student_session_id', $ss->id)->first();
                if (!$sfm) continue;
                DB::table('student_fees_deposite')->insert([
                    'student_fees_master_id' => $sfm->id,
                    'fee_groups_feetype_id' => $feeGroupFeetypes->random()->id,
                    'amount_detail' => json_encode([[
                        'amount' => rand(200, 1500),
                        'amount_discount' => 0.00, 'amount_fine' => 0.00,
                        'date' => $dayDate->format('Y-m-d'),
                        'payment_mode' => 'Cash',
                        'description' => '', 'inv_no' => '',
                    ]]),
                    'is_active' => 'yes', 'created_at' => $dayDate,
                ]);
            }
        }

        // ====================================================================
        // 17. FIXES FOR DASHBOARD SECTIONS (Fees Overview, Library, Transport)
        // ====================================================================

        // --- Fees Overview: scatter fee_groups_feetype due_date across current month ---
        $currentMonthStart = $now->copy()->startOfMonth()->format('Y-m-d');
        $currentMonthEnd = $now->copy()->endOfMonth()->format('Y-m-d');
        $daysInMonth = (int) $now->format('d');
        $fgt = DB::table('fee_groups_feetype')->get();
        foreach ($fgt as $i => $fg) {
            $day = ($i % $daysInMonth) + 1;
            $dd = $now->copy()->day($day);
            DB::table('fee_groups_feetype')->where('id', $fg->id)->update([
                'due_date' => $dd->format('Y-m-d'),
            ]);
        }

        // --- Transport: fix route_pickup_point fees and transport_feemaster due_date ---
        DB::table('route_pickup_point')->update(['fees' => 500.00]);
        DB::table('transport_feemaster')->update([
            'due_date' => $now->copy()->day(15)->format('Y-m-d'),
        ]);

        // --- Books: update postdate to current month ---
        $allBooks = DB::table('books')->get();
        foreach ($allBooks as $i => $book) {
            $day = ($i % $daysInMonth) + 1;
            DB::table('books')->where('id', $book->id)->update([
                'postdate' => $now->copy()->day($day)->format('Y-m-d'),
            ]);
        }

        // --- Book Issues: scatter duereturn_date across current month ---
        $allIssues = DB::table('book_issues')->get();
        foreach ($allIssues as $i => $bi) {
            $day = ($i % $daysInMonth) + 1;
            $dd = $now->copy()->day($day);
            $updates = ['duereturn_date' => $dd->format('Y-m-d')];
            if ($bi->is_returned == 1) {
                $updates['return_date'] = $dd->copy()->subDays(rand(1, 3))->format('Y-m-d');
            }
            DB::table('book_issues')->where('id', $bi->id)->update($updates);
        }
    }
}
