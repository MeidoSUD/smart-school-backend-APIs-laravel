<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Modules\Core\Entities\User;
use Modules\Academic\Entities\Student;
use Modules\Core\Entities\Session;
use Modules\Academic\Entities\Classe;
use Modules\Academic\Entities\Section;
use Modules\Staff\Entities\Staff;

class UserSeeder extends Seeder
{
    /**
     * مصدر الحقيقة لكل الحسابات الفعالة.
     *
     * مهم: قاعدة البيانات مشتركة بين Laravel (backend على :8000)
     * و CodeIgniter (الموقع على :8080 /site/login للموظفين
     * و /site/userlogin للطلاب/أولياء الأمور).
     *
     * قواعد التوافق:
     * - جدول users: كلمة المرور نص صريح (plain) في عمودي password و hash_password
     *   لأن CI User_model::checkLogin يقارن نصياً، و Laravel AuthController يقارن نصياً.
     * - جدول staff: كلمة المرور bcrypt لأن CI Staff_model::checkLogin
     *   يستخدم Enc_lib::passHashDyc = password_verify.
     * - لا تستخدم md5 أبداً هنا، فهي تكسر الدخول في النظامين.
     */
    public function run(): void
    {
        // بيانات أساسية (لا تحذف الموجود)
        $session = Session::firstOrCreate(
            ['session' => '2024-2025'],
            ['is_active' => 'yes']
        );
        $classe = Classe::firstOrCreate(
            ['class' => 'الصف السابع'],
            ['is_active' => 'yes']
        );
        $section = Section::firstOrCreate(
            ['section' => 'A'],
            ['is_active' => 'yes']
        );

        // -------------------------------------------------
        // 1) الموظفون الفعالون (site/login في CodeIgniter)
        // -------------------------------------------------
        // email => [employee_id, name, surname, plain_password, role_ids[]]
        $staffAccounts = [
            'sara.abdullah@teacher.school.com' => [
                'employee_id' => 'TCH2024001', 'name' => 'سارة', 'surname' => 'العبد الله',
                'password' => 'password', 'roles' => [2, 1], // Teacher + Admin
            ],
            'mohammed.alomar@staff.school.com' => [
                'employee_id' => 'STF2024001', 'name' => 'محمد', 'surname' => 'العمر',
                'password' => 'password', 'roles' => [5], // Staff
            ],
            'fahad.almutairi@accountant.school.com' => [
                'employee_id' => 'ACC2024001', 'name' => 'فهد', 'surname' => 'المطيري',
                'password' => 'password', 'roles' => [3], // Accountant
            ],
            'noura.alharbi@librarian.school.com' => [
                'employee_id' => 'LIB2024001', 'name' => 'نورة', 'surname' => 'الحربي',
                'password' => 'password', 'roles' => [4], // Librarian
            ],
            'admin@school.com' => [
                'employee_id' => 'ADM001', 'name' => 'Super Admin', 'surname' => '',
                'password' => 'admin123', 'roles' => [5], // Staff (دور فعال)
            ],
            'superadmin@gmail.com' => [
                'employee_id' => 'DEMO-SADMIN', 'name' => 'Super Admin', 'surname' => '',
                'password' => 'password', 'roles' => [5],
            ],
            'william@gmail.com' => [
                'employee_id' => 'DEMO-ADMIN', 'name' => 'William', 'surname' => '',
                'password' => 'password', 'roles' => [5],
            ],
            'jason@gmail.com' => [
                'employee_id' => 'DEMO-TEACHER', 'name' => 'Jason', 'surname' => '',
                'password' => 'password', 'roles' => [5],
            ],
            'james.deckar@gmail.com' => [
                'employee_id' => 'DEMO-ACCOUNTANT', 'name' => 'James Deckar', 'surname' => '',
                'password' => 'password', 'roles' => [5],
            ],
            'maria.ford@gmail.com' => [
                'employee_id' => 'DEMO-RECEPTION', 'name' => 'Maria Ford', 'surname' => '',
                'password' => 'password', 'roles' => [5],
            ],
            'brandon@gmail.com' => [
                'employee_id' => 'DEMO-LIBRARIAN', 'name' => 'Brandon', 'surname' => '',
                'password' => 'password', 'roles' => [5],
            ],
        ];

        foreach ($staffAccounts as $email => $s) {
            $staff = Staff::where('email', $email)
                ->orWhere('employee_id', $s['employee_id'])
                ->first();

            $hashed = Hash::make($s['password']);

            if (!$staff) {
                $staff = new Staff();
                $staff->employee_id = $s['employee_id'];
                $staff->email = $email;
                $staff->fill([
                    'lang_id' => 4,
                    'name' => $s['name'],
                    'surname' => $s['surname'],
                    'is_active' => 1,
                    'user_id' => 0,
                ]);
                $staff->password = $hashed;
                $staff->save();
            } else {
                // حدّث البيانات الأساسية + أصلح كلمة المرور لو كانت md5 أو لا تطابق bcrypt
                $needsFix = true;
                try {
                    $needsFix = !password_verify($s['password'], $staff->password);
                } catch (\Throwable $e) {
                    $needsFix = true;
                }
                $staff->email = $email;
                $staff->employee_id = $s['employee_id'];
                $staff->is_active = 1;
                if (empty($staff->name)) {
                    $staff->name = $s['name'];
                }
                if ($needsFix) {
                    $staff->password = $hashed;
                }
                $staff->save();
            }

            // أدوار الموظف (staff_roles) - idempotent
            foreach ($s['roles'] as $roleId) {
                DB::table('staff_roles')->updateOrInsert(
                    ['staff_id' => $staff->id, 'role_id' => $roleId],
                    ['is_active' => 1, 'updated_at' => now()]
                );
            }
        }

        // -------------------------------------------------
        // 2) مستخدمو الطلاب/الأولياء الفعالون (site/userlogin)
        // -------------------------------------------------
        // username => [role, user_id(student.id أو 0 للأب), plain_password]
        // user_id يشير إلى students.id للطالب، و parent_id للأب حسب منطق CI.
        $userAccounts = [
            // الموجود فعلياً في القاعدة
            's'                 => ['role' => 'student', 'user_id' => 1,  'password' => '22222222'],
            't'                 => ['role' => 'teacher', 'user_id' => 1,  'password' => '22222222'],
            'parent'            => ['role' => 'parent',  'user_id' => 1,  'password' => '22222222', 'childs' => [1]],
            'std9'              => ['role' => 'student', 'user_id' => 9,  'password' => '22222222'],
            'parent9'           => ['role' => 'parent',  'user_id' => 0,  'password' => '22222222'],
            'khalid@parent.com' => ['role' => 'parent',  'user_id' => 0,  'password' => '22222222'],
            'ahmed.student'     => ['role' => 'student', 'user_id' => 10, 'password' => '22222222'],
            'fatima@parent.com' => ['role' => 'parent',  'user_id' => 0,  'password' => '22222222'],
            'sara.student'      => ['role' => 'student', 'user_id' => 11, 'password' => '22222222'],
            'hassan@parent.com' => ['role' => 'parent',  'user_id' => 0,  'password' => '22222222'],
            'omar.student'      => ['role' => 'student', 'user_id' => 12, 'password' => '22222222'],
            // حسابات Laravel الأصلية (كانت md5 ومفقودة من DB) - نضيفها بنص صريح
            'ahmed.ansari'      => ['role' => 'student', 'user_id' => null, 'password' => '22222222'],
            'khalid.ansari'     => ['role' => 'parent',  'user_id' => null, 'password' => '22222222'],
            'sara.abdullah'     => ['role' => 'teacher', 'user_id' => null, 'password' => '22222222', 'staff_email' => 'sara.abdullah@teacher.school.com'],
            'mohammed.alomar'   => ['role' => 'staff',   'user_id' => null, 'password' => '22222222', 'staff_email' => 'mohammed.alomar@staff.school.com'],
            'fahad.almutairi'   => ['role' => 'accountant', 'user_id' => null, 'password' => '22222222', 'staff_email' => 'fahad.almutairi@accountant.school.com'],
            'noura.alharbi'     => ['role' => 'librarian',  'user_id' => null, 'password' => '22222222', 'staff_email' => 'noura.alharbi@librarian.school.com'],
        ];

        // تأكد من وجود طالب افتراضي لحسابات Laravel الجديدة إن لزم
        $defaultStudent = Student::first();
        if (!$defaultStudent) {
            $defaultStudent = Student::create([
                'parent_id' => 0,
                'admission_no' => 'ADM2024001',
                'roll_no' => '101',
                'admission_date' => '2024-09-01',
                'firstname' => 'أحمد',
                'lastname' => 'الأنصاري',
                'email' => 'ahmed.ansari@student.school.com',
                'mobileno' => '0555000001',
                'gender' => 'Male',
                'is_active' => 'yes',
            ]);
            DB::table('student_session')->insert([
                'session_id' => $session->id,
                'student_id' => $defaultStudent->id,
                'class_id' => $classe->id,
                'section_id' => $section->id,
                'is_alumni' => 0,
                'default_login' => 1,
                'is_active' => 'yes',
            ]);
        }

        foreach ($userAccounts as $username => $u) {
            $userId = $u['user_id'];
            if ($userId === null) {
                if (isset($u['staff_email'])) {
                    $st = Staff::where('email', $u['staff_email'])->first();
                    $userId = $st ? $st->id : 0;
                } else {
                    $userId = $defaultStudent->id;
                }
            }

            $childs = '';
            if (isset($u['childs'])) {
                $childs = json_encode($u['childs']);
            } elseif ($u['role'] === 'parent' && $userId) {
                $childs = json_encode([$userId]);
            }

            $existing = User::where('username', $username)->first();
            $payload = [
                'user_id' => $userId,
                'password' => $u['password'],       // نص صريح لتوافق CI + Laravel
                'hash_password' => $u['password'],  // نص صريح (HashPlaintextPasswords يتكفل بالباقي إن لزم)
                'childs' => $childs,
                'role' => $u['role'],
                'lang_id' => 4,
                'currency_id' => 0,
                'verification_code' => '',
                'is_active' => 'yes',
            ];

            if (!$existing) {
                $existing = User::create(array_merge(['username' => $username], $payload));
            } else {
                // أصلح md5 القديم (32 حرف hex) إلى نص صريح
                if (preg_match('/^[a-f0-9]{32}$/', (string) $existing->password)) {
                    $existing->password = $u['password'];
                }
                if (empty($existing->hash_password) || preg_match('/^[a-f0-9]{32}$/', (string) $existing->hash_password)) {
                    $existing->hash_password = $u['password'];
                }
                $existing->role = $u['role'];
                $existing->is_active = 'yes';
                if (empty($existing->childs) && $childs !== '') {
                    $existing->childs = $childs;
                }
                $existing->save();
            }

            // اربط الموظف بحسابه (staff.user_id -> users.id)
            if (isset($u['staff_email'])) {
                $st = Staff::where('email', $u['staff_email'])->first();
                if ($st && empty($st->user_id)) {
                    $st->user_id = $existing->id;
                    $st->save();
                }
            }
        }

        // إصلاح أي باسورد md5 متبقٍ في users (32 hex) إلى 22222222 كنص صريح
        // حتى لا ينكسر دخول CodeIgniter القديم.
        $md5users = User::whereRaw('CHAR_LENGTH(password)=32 AND password REGEXP "^[a-f0-9]{32}$"')->get();
        foreach ($md5users as $mu) {
            $mu->password = '22222222';
            if (empty($mu->hash_password) || preg_match('/^[a-f0-9]{32}$/', (string) $mu->hash_password)) {
                $mu->hash_password = '22222222';
            }
            $mu->save();
        }
    }
}
