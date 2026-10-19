<?php

namespace Tests\Feature;

use Tests\TestCase;

class TeacherSidebarCompletionTest extends TestCase
{
    private function teacherHeaders(): array
    {
        $login = $this->postJson('/api/teacher/auth/login', [
            'identifier' => 't',
            'password' => '22222222',
        ]);
        $login->assertStatus(200);

        return ['Authorization' => 'Bearer '.$login->json('data.token')];
    }

    public function test_multi_class_students_flow(): void
    {
        $h = $this->teacherHeaders();

        $this->getJson('/api/teacher/students/multi-class')->assertStatus(401);
        $this->getJson('/api/teacher/students/multi-class', $h)->assertStatus(422);
        $this->getJson('/api/teacher/students/multi-class?class_id=999999&section_id=999999', $h)->assertStatus(403);

        $classes = $this->getJson('/api/teacher/my-classes', $h)->assertStatus(200);
        $first = collect($classes->json('data.classes') ?? [])->first();

        if ($first) {
            $r = $this->getJson(
                "/api/teacher/students/multi-class?class_id={$first['class_id']}&section_id={$first['section_id']}",
                $h
            );
            $r->assertStatus(200);
            $this->assertArrayHasKey('students', $r->json('data'));
        }
    }

    public function test_subject_groups_endpoint(): void
    {
        $h = $this->teacherHeaders();

        $this->getJson('/api/teacher/academics/subject-groups')->assertStatus(401);

        $res = $this->getJson('/api/teacher/academics/subject-groups', $h);
        $res->assertStatus(200);
        $this->assertArrayHasKey('subject_groups', $res->json('data'));
    }

    public function test_admit_card_and_marksheet_templates(): void
    {
        $h = $this->teacherHeaders();

        $this->getJson('/api/teacher/exams/admit-card-templates')->assertStatus(401);
        $this->getJson('/api/teacher/exams/marksheet-templates')->assertStatus(401);

        $admit = $this->getJson('/api/teacher/exams/admit-card-templates', $h);
        $admit->assertStatus(200);
        $this->assertArrayHasKey('templates', $admit->json('data'));

        $mark = $this->getJson('/api/teacher/exams/marksheet-templates', $h);
        $mark->assertStatus(200);
        $this->assertArrayHasKey('templates', $mark->json('data'));
    }

    public function test_admit_cards_generation(): void
    {
        $h = $this->teacherHeaders();

        $this->getJson('/api/teacher/exams/admit-cards')->assertStatus(401);
        $this->getJson('/api/teacher/exams/admit-cards', $h)->assertStatus(422);

        $this->getJson(
            '/api/teacher/exams/admit-cards?exam_group_class_batch_exam_id=1&class_id=999999&section_id=999999',
            $h
        )->assertStatus(403);
    }

    public function test_staff_id_cards(): void
    {
        $h = $this->teacherHeaders();

        $this->getJson('/api/teacher/cards/staff-templates')->assertStatus(401);
        $this->getJson('/api/teacher/cards/my-card')->assertStatus(401);

        $tpl = $this->getJson('/api/teacher/cards/staff-templates', $h);
        $tpl->assertStatus(200);
        $this->assertArrayHasKey('templates', $tpl->json('data'));

        $card = $this->getJson('/api/teacher/cards/my-card', $h);
        $card->assertStatus(200);
        $this->assertArrayHasKey('staff', $card->json('data'));
        $this->assertArrayHasKey('template', $card->json('data'));
    }

    public function test_additional_reports(): void
    {
        $h = $this->teacherHeaders();

        $this->getJson('/api/teacher/reports/transport-students')->assertStatus(401);
        $this->getJson('/api/teacher/reports/hostel-students')->assertStatus(401);
        $this->getJson('/api/teacher/reports/alumni-students')->assertStatus(401);

        $trans = $this->getJson('/api/teacher/reports/transport-students', $h);
        $trans->assertStatus(200);
        $this->assertArrayHasKey('students', $trans->json('data'));

        $hostel = $this->getJson('/api/teacher/reports/hostel-students', $h);
        $hostel->assertStatus(200);
        $this->assertArrayHasKey('students', $hostel->json('data'));

        $alumni = $this->getJson('/api/teacher/reports/alumni-students', $h);
        $alumni->assertStatus(200);
        $this->assertArrayHasKey('students', $alumni->json('data'));
    }
}
