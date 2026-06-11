<?php

namespace Tests\Feature\Api;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Modules\Core\Entities\User;
use Modules\Core\Services\StudentSessionResolver;

class ParentMultiChildTest extends ApiTestCase
{
    public function test_parent_can_resolve_specific_child_via_student_id(): void
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

        $parentId = DB::table('users')->insertGetId([
            'user_id' => 0,
            'username' => 'parent_' . $suffix,
            'password' => Hash::make('password'),
            'childs' => '[]',
            'lang_id' => 1,
            'verification_code' => '',
            'role' => 'parent',
            'is_active' => 'yes',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $firstStudentId = DB::table('students')->insertGetId(array_merge($this->defaultStudentRow(), [
            'parent_id' => $parentId,
            'admission_no' => 'ADM1' . $suffix,
            'firstname' => 'First',
            'lastname' => 'Child',
        ]));

        $secondStudentId = DB::table('students')->insertGetId(array_merge($this->defaultStudentRow(), [
            'parent_id' => $parentId,
            'admission_no' => 'ADM2' . $suffix,
            'firstname' => 'Second',
            'lastname' => 'Child',
        ]));

        DB::table('student_session')->insert([
            [
                'student_id' => $firstStudentId,
                'class_id' => $classId,
                'section_id' => $sectionId,
                'session_id' => $sessionId,
                'default_login' => 1,
                'is_alumni' => 0,
            ],
            [
                'student_id' => $secondStudentId,
                'class_id' => $classId,
                'section_id' => $sectionId,
                'session_id' => $sessionId,
                'default_login' => 0,
                'is_alumni' => 0,
            ],
        ]);

        $parent = User::find($parentId);
        $resolver = app(StudentSessionResolver::class);

        $defaultChildSession = $resolver->resolveSession($parent);
        $specificChildSession = $resolver->resolveSession($parent, $secondStudentId);

        $this->assertNotNull($defaultChildSession);
        $this->assertNotNull($specificChildSession);
        $this->assertSame($firstStudentId, $defaultChildSession->student_id);
        $this->assertSame($secondStudentId, $specificChildSession->student_id);
    }
}
