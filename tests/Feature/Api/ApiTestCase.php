<?php

namespace Tests\Feature\Api;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\Sanctum;
use Modules\Core\Entities\User;
use Tests\TestCase;

abstract class ApiTestCase extends TestCase
{
    use DatabaseTransactions;

    protected function seedSchoolFixtures(): array
    {
        $suffix = (string) random_int(10000, 99999);

        $sessionId = DB::table('sessions')->where('is_active', 'yes')->value('id');
        if (!$sessionId) {
            $sessionId = DB::table('sessions')->insertGetId([
                'session' => '2025-26-' . $suffix,
                'is_active' => 'yes',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $classId = DB::table('classes')->insertGetId(['class' => 'Grade 1', 'is_active' => 'yes']);
        $sectionId = DB::table('sections')->insertGetId(['section' => 'A', 'is_active' => 'yes']);
        $classSectionId = DB::table('class_sections')->insertGetId([
            'class_id' => $classId,
            'section_id' => $sectionId,
            'is_active' => 'yes',
        ]);

        $studentId = DB::table('students')->insertGetId(array_merge($this->defaultStudentRow(), [
            'admission_no' => 'ADM' . $suffix,
            'firstname' => 'Test',
            'lastname' => 'Student',
        ]));

        $studentSessionId = DB::table('student_session')->insertGetId([
            'student_id' => $studentId,
            'class_id' => $classId,
            'section_id' => $sectionId,
            'session_id' => $sessionId,
            'default_login' => 1,
            'is_alumni' => 0,
        ]);

        $username = 'student1_' . $suffix;
        $userId = DB::table('users')->insertGetId([
            'user_id' => $studentId,
            'username' => $username,
            'password' => Hash::make('password'),
            'childs' => '[]',
            'lang_id' => 1,
            'verification_code' => '',
            'role' => 'student',
            'is_active' => 'yes',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        if (!DB::table('attendence_type')->where('id', 1)->exists()) {
            DB::table('attendence_type')->insert([
                ['id' => 1, 'type' => 'Present', 'key_value' => 'P', 'long_lang_name' => 'present', 'long_name_style' => 'label', 'is_active' => 'yes', 'for_qr_attendance' => 1, 'created_at' => now(), 'updated_at' => now()],
                ['id' => 4, 'type' => 'Absent', 'key_value' => 'A', 'long_lang_name' => 'absent', 'long_name_style' => 'label', 'is_active' => 'yes', 'for_qr_attendance' => 0, 'created_at' => now(), 'updated_at' => now()],
            ]);
        }

        DB::table('student_attendences')->insert([
            'student_session_id' => $studentSessionId,
            'attendence_type_id' => 1,
            'date' => now()->toDateString(),
            'remark' => '',
            'is_active' => 'yes',
        ]);

        $staffId = DB::table('staff')->insertGetId(array_merge($this->defaultStaffRow(), [
            'employee_id' => 'EMP' . $suffix,
            'email' => 'teacher' . $suffix . '@test.com',
        ]));

        $subjectId = DB::table('subjects')->insertGetId([
            'name' => 'Math',
            'code' => 'M' . $suffix,
            'type' => 'theory',
            'is_active' => 'yes',
        ]);

        DB::table('teacher_subjects')->insert([
            'teacher_id' => $staffId,
            'subject_id' => $subjectId,
            'class_section_id' => $classSectionId,
            'session_id' => $sessionId,
        ]);

        return [
            'user' => User::find($userId),
            'student_session_id' => $studentSessionId,
            'username' => $username,
        ];
    }

    protected function actingAsStudent(): User
    {
        $fixtures = $this->seedSchoolFixtures();
        Sanctum::actingAs($fixtures['user']);

        return $fixtures['user'];
    }

    protected function defaultStudentRow(): array
    {
        return [
            'parent_id' => 0,
            'roll_no' => '1',
            'admission_date' => now()->toDateString(),
            'gender' => 'Male',
            'dob' => '2010-01-01',
            'category_id' => '0',
            'blood_group' => 'O+',
            'guardian_is' => 'father',
            'guardian_occupation' => 'N/A',
            'father_pic' => '',
            'mother_pic' => '',
            'guardian_pic' => '',
            'height' => '150',
            'weight' => '50',
            'dis_reason' => 0,
            'dis_note' => '',
        ];
    }

    private function defaultStaffRow(): array
    {
        return [
            'lang_id' => 1,
            'qualification' => 'B.Ed',
            'work_exp' => '5 years',
            'name' => 'Jane',
            'surname' => 'Teacher',
            'father_name' => 'John',
            'mother_name' => 'Mary',
            'contact_no' => '1234567890',
            'emergency_contact_no' => '1234567890',
            'dob' => '1990-01-01',
            'marital_status' => 'single',
            'local_address' => 'Local',
            'permanent_address' => 'Permanent',
            'note' => '',
            'image' => '',
            'password' => Hash::make('password'),
            'gender' => 'Female',
            'account_title' => '',
            'bank_account_no' => '',
            'bank_name' => '',
            'ifsc_code' => '',
            'bank_branch' => '',
            'payscale' => '',
            'epf_no' => '',
            'contract_type' => 'permanent',
            'shift' => 'day',
            'location' => 'campus',
            'facebook' => '',
            'twitter' => '',
            'linkedin' => '',
            'instagram' => '',
            'resume' => '',
            'joining_letter' => '',
            'resignation_letter' => '',
            'other_document_name' => '',
            'other_document_file' => '',
            'user_id' => 0,
            'is_active' => 1,
            'verification_code' => '',
        ];
    }

}
