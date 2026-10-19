<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * اختبارات إكمال البنود الجزئية الأخيرة من الشريط الجانبي للمعلم (55 بنداً):
 * - 1.3 القبول الإلكتروني: update + payment-status (enroll محظور 403)
 * - 2.7 كشف الدرجات: بنية غنية (مواد/مجموع/نسبة/تقدير/رتبة/تقسيم/قالب)
 * - 12.4 تقارير الامتحانات: rank بالنسبة والتقدير مع فلترة الفصل
 * - 12.6 تقرير الخطة الدراسية: per-subject + chart dataset
 */
class TeacherSidebarFinalCompletionTest extends TestCase
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

    private function firstScopeClass(array $h): ?array
    {
        $classes = $this->getJson('/api/teacher/my-classes', $h)->assertStatus(200);

        return collect($classes->json('data.classes') ?? [])->first();
    }

    public function test_online_admission_update_and_payment_status(): void
    {
        $h = $this->teacherHeaders();

        // بدون مصادقة → 401
        $this->putJson('/api/teacher/online-admissions/1', ['firstname' => 'X'])->assertStatus(401);
        $this->getJson('/api/teacher/online-admissions/1/payment-status')->assertStatus(401);

        // غير موجود → 404
        $this->putJson('/api/teacher/online-admissions/999999', ['firstname' => 'X'], $h)->assertStatus(404);
        $this->getJson('/api/teacher/online-admissions/999999/payment-status', $h)->assertStatus(404);

        $list = $this->getJson('/api/teacher/online-admissions?per_page=1', $h)->assertStatus(200);
        $first = collect($list->json('data.admissions.data') ?? [])->first();
        if (! $first) {
            $this->markTestSkipped('No scoped online admissions to exercise update flow');
        }

        $id = (int) $first['id'];

        // العرض يتضمن أعلام الإجراءات (enroll محظور على المعلم)
        $show = $this->getJson("/api/teacher/online-admissions/{$id}", $h)->assertStatus(200);
        $this->assertFalse((bool) $show->json('data.actions.enroll_allowed_for_teacher'));

        // حالة الدفع — parity مع checkpaymentstatus
        $ps = $this->getJson("/api/teacher/online-admissions/{$id}/payment-status", $h)->assertStatus(200);
        $ps->assertJsonStructure(['data' => ['payment_status' => [
            'form_status', 'paid_status', 'is_enroll', 'payment_required',
            'enroll_allowed_for_teacher', 'guidance',
        ]]]);
        $this->assertFalse((bool) $ps->json('data.payment_status.enroll_allowed_for_teacher'));

        // محاولة القبول (enroll) محظورة على المعلم → 403
        $this->putJson("/api/teacher/online-admissions/{$id}", ['is_enroll' => 1], $h)->assertStatus(403);

        // نقل الطلب خارج النطاق → 403 (أما القسم غير الموجود أصلاً فيُرفض بالتحقق 422)
        $this->putJson("/api/teacher/online-admissions/{$id}", ['class_section_id' => 999999], $h)->assertStatus(422);
        $scopedSections = $this->getJson('/api/teacher/academics/sections', $h)->assertStatus(200);
        $myCsIds = collect($scopedSections->json('data.sections') ?? [])->pluck('class_section_id')->all();
        $foreignCs = (int) DB::table('class_sections')->whereNotIn('id', $myCsIds ?: [0])->value('id');
        if ($foreignCs > 0) {
            $this->putJson("/api/teacher/online-admissions/{$id}", ['class_section_id' => $foreignCs], $h)->assertStatus(403);
        }

        // تحديث حقل عادي ثم إرجاعه
        $original = $first['note'] ?? null;
        $upd = $this->putJson("/api/teacher/online-admissions/{$id}", ['note' => 'teacher-final-completion-probe'], $h);
        $upd->assertStatus(200)->assertJsonFragment(['note' => 'teacher-final-completion-probe']);
        DB::table('online_admissions')->where('id', $id)->update(['note' => $original]);
    }

    public function test_marksheet_enriched_structure(): void
    {
        $h = $this->teacherHeaders();

        $this->getJson('/api/teacher/exam-marksheet')->assertStatus(401);
        $this->getJson('/api/teacher/exam-marksheet?exam_group_id=1', $h)->assertStatus(422);
        $this->getJson('/api/teacher/exam-marksheet?exam_group_id=1&class_id=999999&section_id=999999', $h)
            ->assertStatus(403);
        $this->getJson('/api/teacher/exam-marksheet?exam_group_id=999999&class_id=1&section_id=1', $h)
            ->assertStatus(404);

        $scope = $this->firstScopeClass($h);
        if (! $scope) {
            $this->markTestSkipped('Teacher has no scoped classes');
        }

        $groupId = (int) (DB::table('exam_groups')->orderBy('id')->value('id') ?? 1);
        $r = $this->getJson(
            "/api/teacher/exam-marksheet?exam_group_id={$groupId}&class_id={$scope['class_id']}&section_id={$scope['section_id']}",
            $h
        );
        $r->assertStatus(200)->assertJsonStructure(['data' => [
            'exam_group', 'exams', 'template', 'subjects', 'students',
        ]]);

        $student = collect($r->json('data.students') ?? [])->first();
        if ($student) {
            $this->assertArrayHasKey('subjects', $student);
            $this->assertArrayHasKey('total_marks', $student);
            $this->assertArrayHasKey('percentage', $student);
            $this->assertArrayHasKey('rank', $student);
            $this->assertArrayHasKey('grade', $student);
            $this->assertArrayHasKey('division', $student);
            $subject = collect($student['subjects'] ?? [])->first();
            if ($subject) {
                $this->assertArrayHasKey('max_marks', $subject);
                $this->assertArrayHasKey('get_marks', $subject);
            }
        }
    }

    public function test_rank_report_enriched_with_class_filter(): void
    {
        $h = $this->teacherHeaders();

        $this->getJson('/api/teacher/exam-rank')->assertStatus(401);
        $this->getJson('/api/teacher/exam-rank?exam_group_id=999999', $h)->assertStatus(404);

        $groupId = (int) (DB::table('exam_groups')->orderBy('id')->value('id') ?? 1);
        $r = $this->getJson("/api/teacher/exam-rank?exam_group_id={$groupId}", $h);
        $r->assertStatus(200)->assertJsonStructure(['data' => ['exam_group', 'max_total', 'rank']]);

        $row = collect($r->json('data.rank') ?? [])->first();
        if ($row) {
            $this->assertArrayHasKey('total', $row);
            $this->assertArrayHasKey('rank', $row);
            $this->assertArrayHasKey('percentage', $row);
            $this->assertArrayHasKey('grade', $row);
            $this->assertArrayHasKey('division', $row);
        }

        // فلترة الفصل خارج النطاق → 403
        $this->getJson("/api/teacher/exam-rank?exam_group_id={$groupId}&class_id=999999&section_id=999999", $h)
            ->assertStatus(403);

        // فلترة الفصل داخل النطاق → 200
        $scope = $this->firstScopeClass($h);
        if ($scope) {
            $f = $this->getJson(
                "/api/teacher/exam-rank?exam_group_id={$groupId}&class_id={$scope['class_id']}&section_id={$scope['section_id']}",
                $h
            );
            $f->assertStatus(200)->assertJsonStructure(['data' => ['rank']]);
        }
    }

    public function test_syllabus_report_per_subject_with_chart(): void
    {
        $h = $this->teacherHeaders();

        $this->getJson('/api/teacher/syllabus/report')->assertStatus(401);
        $this->getJson('/api/teacher/syllabus/report?class_id=1', $h)->assertStatus(422);
        $this->getJson('/api/teacher/syllabus/report?class_id=999999&section_id=999999&subject_group_id=1', $h)
            ->assertStatus(403);

        // التوافق الخلفي: status بدون فلاتر يبقى إجمالياً عاماً
        $this->getJson('/api/teacher/syllabus/status', $h)->assertStatus(200)
            ->assertJsonStructure(['data' => ['total', 'complete', 'incomplete', 'complete_percent']]);

        $scope = $this->firstScopeClass($h);
        if (! $scope) {
            $this->markTestSkipped('Teacher has no scoped classes');
        }

        $groups = $this->getJson(
            "/api/teacher/academics/subject-groups?class_id={$scope['class_id']}&section_id={$scope['section_id']}",
            $h
        )->assertStatus(200);
        $group = collect($groups->json('data.subject_groups') ?? [])->first();
        if (! $group) {
            $this->markTestSkipped('No subject groups for scoped class');
        }
        $groupId = $group['id'] ?? $group['subject_group_id'] ?? null;
        if (! $groupId) {
            $this->markTestSkipped('Subject group id not exposed');
        }

        $r = $this->getJson(
            "/api/teacher/syllabus/report?class_id={$scope['class_id']}&section_id={$scope['section_id']}&subject_group_id={$groupId}",
            $h
        );
        $r->assertStatus(200)->assertJsonStructure(['data' => [
            'subjects_data', 'summary', 'chart',
        ]]);

        $subject = collect($r->json('data.subjects_data') ?? [])->first();
        if ($subject) {
            $this->assertArrayHasKey('lebel', $subject);
            $this->assertArrayHasKey('complete', $subject);
            $this->assertArrayHasKey('incomplete', $subject);
            $this->assertArrayHasKey('lesson_summary', $subject);
        }
        $chart = $r->json('data.chart');
        $this->assertArrayHasKey('labels', $chart);
        $this->assertArrayHasKey('complete', $chart);
        $this->assertArrayHasKey('incomplete', $chart);

        // status مع نفس الفلاتر يعيد التقرير المفصّل (parity مع lesson_plan)
        $s = $this->getJson(
            "/api/teacher/syllabus/status?class_id={$scope['class_id']}&section_id={$scope['section_id']}&subject_group_id={$groupId}",
            $h
        );
        $s->assertStatus(200)->assertJsonStructure(['data' => ['subjects_data', 'summary', 'chart']]);
    }

    public function test_exam_subjects_fixed_columns(): void
    {        $h = $this->teacherHeaders();

        $examId = (int) (DB::table('exam_group_class_batch_exams')->orderBy('id')->value('id') ?? 1);
        $r = $this->getJson("/api/teacher/exam-subjects/{$examId}", $h)->assertStatus(200);
        $subjects = $r->json('data.subjects') ?? [];
        $this->assertNotEmpty($subjects, 'Exam subjects must be returned with fixed column mapping');
        $this->assertArrayHasKey('subject_name', $subjects[0]);
        $this->assertArrayHasKey('max_marks', $subjects[0]);
    }

    public function test_syllabus_extra_scope_enforced(): void
    {
        $h = $this->teacherHeaders();

        // بدون مصادقة → 401
        $this->deleteJson('/api/teacher/lessons/1', [], [])->assertStatus(401);
        $this->postJson('/api/teacher/topics/status', ['topic_id' => 1, 'status' => 1], [])->assertStatus(401);

        // درس غير موجود → 404
        $this->deleteJson('/api/teacher/lessons/999999', [], $h)->assertStatus(404);

        // موضوع خارج النطاق (أو غير موجود) → 403/404، وليس 200 أبداً
        $foreignTopic = (int) DB::table('topic')
            ->whereNotIn('id', function ($q) {
                $q->select('topic.id')->from('topic')
                    ->join('lesson', 'lesson.id', '=', 'topic.lesson_id')
                    ->join('subject_group_subjects as sgs', 'sgs.id', '=', 'lesson.subject_group_subject_id')
                    ->join('teacher_subjects as ts', 'ts.subject_id', '=', 'sgs.subject_id');
            })->value('id');
        if ($foreignTopic > 0) {
            $r = $this->postJson('/api/teacher/topics/status', ['topic_id' => $foreignTopic, 'status' => 1], $h);
            $this->assertContains($r->getStatusCode(), [403, 404]);
            $this->deleteJson("/api/teacher/lessons/{$foreignTopic}", [], $h);
        }

        // نسخ من مادة خارج النطاق → 403
        $this->postJson('/api/teacher/lessons/copy', [
            'from_subject_group_subject_id' => 999999,
            'to_subject_group_subject_id' => 999999,
        ], $h)->assertStatus(403);
    }

    public function test_send_email_sms_class_scope(): void
    {        $h = $this->teacherHeaders();

        // استهداف فصل خارج النطاق → 403
        $this->postJson('/api/teacher/send-email', [
            'title' => 't', 'message' => 'm', 'class_id' => 999999, 'section_id' => 999999,
        ], $h)->assertStatus(403);
        $this->postJson('/api/teacher/send-sms', [
            'title' => 't', 'message' => 'm', 'class_id' => 999999, 'section_id' => 999999,
        ], $h)->assertStatus(403);

        // class_id بدون section_id → 422
        $this->postJson('/api/teacher/send-email', [
            'title' => 't', 'message' => 'm', 'class_id' => 1,
        ], $h)->assertStatus(422);
    }

    public function test_finance_summary_scoped_and_private(): void
    {
        $h = $this->teacherHeaders();

        // بدون مصادقة → 401
        $this->getJson('/api/teacher/reports/finance-summary')->assertStatus(401);

        // فلترة ناقصة → 422، وخارج النطاق → 403
        $this->getJson('/api/teacher/reports/finance-summary?class_id=1', $h)->assertStatus(422);
        $this->getJson('/api/teacher/reports/finance-summary?class_id=999999&section_id=999999', $h)
            ->assertStatus(403);

        $scope = $this->firstScopeClass($h);
        if (! $scope) {
            $this->markTestSkipped('Teacher has no scoped classes');
        }

        $r = $this->getJson(
            "/api/teacher/reports/finance-summary?class_id={$scope['class_id']}&section_id={$scope['section_id']}",
            $h
        );
        $r->assertStatus(200)->assertJsonStructure(['data' => [
            'summary' => ['students_count', 'total_fee', 'total_paid', 'total_balance',
                'paid_count', 'partial_count', 'unpaid_count'],
            'students', 'pagination', 'privacy_note',
        ]]);

        // نموذج الخصوصية: لا بيانات تواصل ولا تفاصيل دفع خام
        $payload = $r->json('data');
        $this->assertArrayNotHasKey('mobileno', $payload['students'][0] ?? []);
        foreach ($payload['students'] as $row) {
            $this->assertArrayNotHasKey('mobileno', $row);
            $this->assertArrayNotHasKey('email', $row);
            $this->assertArrayNotHasKey('amount_detail', $row);
            $this->assertArrayHasKey('status', $row);
            $this->assertArrayHasKey('balance', $row);
            $this->assertContains($row['status'], ['paid', 'partial', 'unpaid']);
        }

        // فلتر الحالة يعمل
        $f = $this->getJson(
            "/api/teacher/reports/finance-summary?class_id={$scope['class_id']}&section_id={$scope['section_id']}&status=unpaid",
            $h
        );
        $f->assertStatus(200);
        foreach ($f->json('data.students') as $row) {
            $this->assertSame('unpaid', $row['status']);
        }
    }
}
