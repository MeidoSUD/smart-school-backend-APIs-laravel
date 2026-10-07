<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Academic\Entities\Student;
use Modules\Core\Entities\Session;
use Modules\Operations\Entities\BehaviourSetting;
use Modules\Operations\Entities\StudentBehaviour;
use Modules\Operations\Entities\StudentIncident;
use Modules\Operations\Entities\StudentIncidentComment;
use Modules\Staff\Entities\Staff;

/**
 * Seeds the dataset required by the CodeIgniter page
 * `behaviour/studentincidents/index` (assignincidentlist).
 *
 * CI source of truth:
 * - Controller: backend/codelignter/student cotroller/behaviour/Studentincidents.php
 *   :: index(), dtassignincidentlist(), create(), viewassignedincidentslist(), delete()
 * - Models: Studentbehaviour_model::get() (student_behaviour.* ORDER BY id),
 *   Studentincidents_model::add() (insert student_incidents:
 *   student_id/session_id=current_session/incident_id/assign_by),
 *   ::totalpoints($student_id) (SUM(point) JOIN student_behaviour
 *   WHERE session_id=current_session AND student_id),
 *   ::studentbehaviour($student_id) (student_incidents JOIN students JOIN
 *   student_behaviour JOIN sessions JOIN staff LEFT staff_roles
 *   WHERE students.is_active='yes' AND session_id=current_session
 *   AND student_id ORDER BY id DESC + totalcomments per incident),
 *   ::addmessage()/getmessage()/totalcomments()/delete_comment()
 *   (student_incident_comments), Student_model::searchdtByClassSection()
 *   + Student_model::get($student_id), Class_model::get(),
 *   Setting_model::getCurrentSession()
 * - Views: application/views/behaviour/studentincidents/assignincidentlist.php
 *   (criteria class/section + table student_name/admission_no/class(gender)/
 *   phone/total_points/action + assign modal incident_id[] checkboxes from
 *   $incidentlist title/point/description),
 *   _viewassignedincidentslist.php (title/point/date/description/assign_by staff
 *   name+surname(employee_id) + delete_assign)
 *
 * Dependency order respected here (parents first):
 *   sessions (current is_active=yes) > students (is_active=yes) + staff
 *   > student_behaviour > student_incidents > student_incident_comments,
 *   behaviour_settings (independent lookup)
 *
 * Existing rows are reused, never recreated and never deleted.
 * Safe to run repeatedly (firstOrCreate / updateOrCreate, no truncate).
 */
class StudentIncidentPageSeeder extends Seeder
{
    public function run(): void
    {
        // ---- 0. Current session (CI: setting_model->getCurrentSession()) ----
        $session = Session::where('is_active', 'yes')->orderByDesc('id')->first()
            ?? Session::orderByDesc('id')->first();

        if (! $session) {
            return;
        }

        // ---- 1. behaviour_settings lookup (CI: studentbehaviour_model->getsettings()) ----
        BehaviourSetting::firstOrCreate(
            ['id' => 1],
            ['comment_option' => '["student","parent"]', 'created_at' => now()]
        );

        // ---- 2. student_behaviour lookup (CI: Incidents::create/edit validation
        // title/point/description required; negative point stored as '-point') ----
        $behaviours = [
            ['title' => 'التفوق الدراسي', 'point' => 10, 'description' => 'تفوق في الاختبارات الشهرية وحصول على أعلى الدرجات'],
            ['title' => 'مساعدة الزملاء', 'point' => 5, 'description' => 'مساعدة الزملاء في الدروس والواجبات'],
            ['title' => 'المشاركة الصفية', 'point' => 5, 'description' => 'مشاركة فعالة في الأنشطة الصفية'],
            ['title' => 'الالتزام والنظافة', 'point' => 3, 'description' => 'الالتزام بالزي المدرسي والنظافة العامة'],
            ['title' => 'التأخر عن الطابور', 'point' => -2, 'description' => 'التأخر عن الطابور الصباحي بدون عذر'],
            ['title' => 'الغياب بدون عذر', 'point' => -5, 'description' => 'الغياب عن الدروس بدون عذر مقبول'],
            ['title' => 'إزعاج داخل الفصل', 'point' => -3, 'description' => 'إحداث إزعاج متكرر داخل الفصل أثناء الشرح'],
            ['title' => 'سوء السلوك', 'point' => -10, 'description' => 'سلوك غير لائق يستوجب التنبيه'],
        ];

        $behaviourModels = [];
        foreach ($behaviours as $row) {
            $behaviourModels[] = StudentBehaviour::updateOrCreate(
                ['title' => $row['title']],
                ['point' => $row['point'], 'description' => $row['description'], 'created_at' => now()]
            );
        }

        // ---- 3. Parents: active students + staff (assign_by = logged staff id) ----
        $students = Student::where('is_active', 'yes')->orderBy('id')->get();
        if ($students->isEmpty()) {
            return;
        }

        $staffIds = Staff::where('is_active', 1)->orderBy('id')->pluck('id')->all();
        if (empty($staffIds)) {
            $firstStaff = Staff::orderBy('id')->first();
            if (! $firstStaff) {
                return;
            }
            $staffIds = [$firstStaff->id];
        }

        // Map title => model for deterministic assignment (positive + negative mix
        // so totalpoints()/rank/pointbystudent() return meaningful data).
        $byTitle = [];
        foreach ($behaviourModels as $m) {
            $byTitle[$m->title] = $m;
        }

        // student index => [behaviour titles] — several incidents per student.
        $plan = [
            0 => ['التفوق الدراسي', 'المشاركة الصفية', 'التأخر عن الطابور'],
            1 => ['مساعدة الزملاء', 'الغياب بدون عذر'],
            2 => ['الالتزام والنظافة', 'إزعاج داخل الفصل', 'المشاركة الصفية'],
            3 => ['سوء السلوك'],
            4 => ['التفوق الدراسي', 'مساعدة الزملاء'],
            5 => ['الغياب بدون عذر', 'التأخر عن الطابور'],
        ];

        $createdIncidents = [];
        $i = 0;
        foreach ($students as $index => $student) {
            $titles = $plan[$index % count($plan)];
            foreach ($titles as $t) {
                if (! isset($byTitle[$t])) {
                    continue;
                }
                $behaviour = $byTitle[$t];
                // Idempotency: same student + incident + session must not duplicate.
                $incident = StudentIncident::firstOrCreate(
                    [
                        'session_id' => $session->id,
                        'student_id' => $student->id,
                        'incident_id' => $behaviour->id,
                    ],
                    [
                        'assign_by' => $staffIds[$i % count($staffIds)],
                        'created_at' => now()->subDays(20 - ($i % 20))->format('Y-m-d H:i:s'),
                    ]
                );
                $createdIncidents[] = $incident;
                $i++;
            }
        }

        // ---- 4. Comments (CI: getmessage/addmessage/totalcomments) ----
        // Types mirror CI: staff / student / parent.
        $commentSeeds = [
            ['type' => 'staff', 'comment' => 'تم التنبيه على الطالب وتحسن سلوكه.'],
            ['type' => 'student', 'comment' => 'أعد بالالتزام مستقبلاً.'],
            ['type' => 'parent', 'comment' => 'تم الاطلاع وسيتم المتابعة من البيت.'],
        ];

        $targets = StudentIncident::where('session_id', $session->id)
            ->orderBy('id')->limit(3)->get();

        foreach ($targets as $tIndex => $incident) {
            $seed = $commentSeeds[$tIndex % count($commentSeeds)];
            $isStaff = $seed['type'] === 'staff';

            StudentIncidentComment::firstOrCreate(
                [
                    'student_incident_id' => $incident->id,
                    'comment' => $seed['comment'],
                    'type' => $seed['type'],
                ],
                [
                    'staff_id' => $isStaff ? $incident->assign_by : 0,
                    'student_id' => $incident->student_id,
                    'created_date' => now()->subDays(5 - $tIndex)->format('Y-m-d H:i:s'),
                ]
            );
        }
    }
}
