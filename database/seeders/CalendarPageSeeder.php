<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Modules\Academic\Entities\CalendarEvent;
use Modules\Core\Entities\User;

class CalendarPageSeeder extends Seeder
{
    public function run(): void
    {
        // Resolve target student (mirrors CI: $this->customlib->getLoggedInUserData()['id'] = users.id)
        $studentUser = User::where('role', 'student')->where('is_active', 'yes')->orderBy('id')->first()
            ?? User::where('role', 'student')->orderBy('id')->first()
            ?? User::first();

        if (!$studentUser) {
            return;
        }

        $studentUserId = (string) $studentUser->id;

        $palette = ['#03a9f4', '#c53da9', '#757575', '#8e24aa', '#d81b60', '#7cb342', '#fb8c00', '#fb3b3b'];

        // 8 public events: visible in CI getStudentEvents() + Laravel getevents()
        // CI: (event_type='public' or event_type='task') and role_id IS NULL, event_for='0' for public
        $publicEvents = [
            ['title' => 'اجتماع أولياء الأمور', 'desc' => 'اجتماع أولياء الأمور لمناقشة مستوى الطلاب (القاعة الكبرى).', 'start' => '2026-09-14 10:00:00', 'end' => '2026-09-14 12:00:00'],
            ['title' => 'احتفال اليوم الوطني', 'desc' => 'احتفال المدرسة باليوم الوطني مع الأنشطة الطلابية.', 'start' => '2026-09-18 09:00:00', 'end' => '2026-09-18 11:00:00'],
            ['title' => 'نهائيات معرض العلوم', 'desc' => 'عرض مشاريع الطلاب النهائية في معرض العلوم السنوي.', 'start' => '2026-09-22 09:00:00', 'end' => '2026-09-22 13:00:00'],
            ['title' => 'مسابقة اللغة العربية', 'desc' => 'مسابقة الإلقاء والتعبير لطلاب المرحلة المتوسطة.', 'start' => '2026-09-25 10:00:00', 'end' => '2026-09-25 12:00:00'],
            ['title' => 'نهائيات اليوم الرياضي', 'desc' => 'نهائيات كرة القدم وألعاب القوى في الملعب المدرسي.', 'start' => '2026-09-29 08:00:00', 'end' => '2026-09-29 12:00:00'],
            ['title' => 'معرض الفنون', 'desc' => 'معرض أعمال الطلاب الفنية في قاعة الأنشطة.', 'start' => '2026-10-03 10:00:00', 'end' => '2026-10-03 14:00:00'],
            ['title' => 'إعلان إجازة الشتاء', 'desc' => 'إعلان مواعيد إجازة الشتاء وتعليمات العودة.', 'start' => '2026-10-07 09:00:00', 'end' => '2026-10-07 10:00:00'],
            ['title' => 'حفل نهاية الفصل', 'desc' => 'حفل تكريم المتفوقين في نهاية الفصل الدراسي.', 'start' => '2026-10-12 10:00:00', 'end' => '2026-10-12 12:00:00'],
        ];

        // 12 personal tasks for the student user.
        // CI user/addtodo: event_type='task', event_color='#000', start=end, is_active='no', event_for=user_id, role_id=NULL
        // Laravel getevents() only returns is_active='yes', so mix yes/no:
        //   - 'yes' => visible in calendar grid + task list (completed)
        //   - 'no'  => visible in task list only (pending, matches CI default)
        $tasks = [
            ['title' => 'حل تمارين الرياضيات ص 45', 'desc' => 'حل التمارين من 1 إلى 10 صفحة 45.', 'date' => '2026-09-13 16:00:00', 'active' => 'no'],
            ['title' => 'حفظ السورة المقررة', 'desc' => 'حفظ ومراجعة السورة المقررة للحصة القادمة.', 'date' => '2026-09-14 17:00:00', 'active' => 'no'],
            ['title' => 'مراجعة مفردات الإنجليزية', 'desc' => 'مراجعة مفردات الوحدة الثالثة.', 'date' => '2026-09-15 16:30:00', 'active' => 'yes'],
            ['title' => 'مسودة مشروع العلوم', 'desc' => 'تجهيز مسودة مشروع العلوم عن الطاقة الشمسية.', 'date' => '2026-09-16 18:00:00', 'active' => 'no'],
            ['title' => 'كتابة موضوع التعبير', 'desc' => 'كتابة موضوع التعبير عن العمل التطوعي.', 'date' => '2026-09-17 17:00:00', 'active' => 'no'],
            ['title' => 'تجهيز عرض التاريخ', 'desc' => 'تجهيز عرض تقديمي عن الدولة السعودية.', 'date' => '2026-09-19 16:00:00', 'active' => 'yes'],
            ['title' => 'إكمال واجب الحاسب', 'desc' => 'إكمال التطبيق العملي على برنامج العروض.', 'date' => '2026-09-21 18:30:00', 'active' => 'no'],
            ['title' => 'مراجعة قوانين الفيزياء', 'desc' => 'مراجعة قوانين الحركة وحل أمثلة.', 'date' => '2026-09-23 17:00:00', 'active' => 'yes'],
            ['title' => 'رسم خريطة الجغرافيا', 'desc' => 'رسم خريطة شبه الجزيرة العربية وتحديد التضاريس.', 'date' => '2026-09-26 16:00:00', 'active' => 'no'],
            ['title' => 'التدرب على جدول الضرب', 'desc' => 'التدرب على جدول الضرب من 6 إلى 9.', 'date' => '2026-09-28 15:30:00', 'active' => 'yes'],
            ['title' => 'قراءة الفصل الثالث من القصة', 'desc' => 'قراءة الفصل الثالث وتلخيص أهم الأحداث.', 'date' => '2026-10-01 17:00:00', 'active' => 'no'],
            ['title' => 'الاستعداد لاختبار الإملاء', 'desc' => 'مراجعة الكلمات المطلوبة لاختبار الإملاء.', 'date' => '2026-10-05 16:00:00', 'active' => 'no'],
        ];

        foreach ($publicEvents as $i => $ev) {
            CalendarEvent::updateOrCreate(
                [
                    'event_title' => $ev['title'],
                    'start_date' => $ev['start'],
                    'event_for' => '0',
                    'event_type' => 'public',
                ],
                [
                    'event_description' => $ev['desc'],
                    'end_date' => $ev['end'],
                    'event_color' => $palette[$i % count($palette)],
                    'role_id' => null,
                    'status' => 'no',
                    'is_active' => 'yes',
                ]
            );
        }

        foreach ($tasks as $t) {
            CalendarEvent::updateOrCreate(
                [
                    'event_title' => $t['title'],
                    'start_date' => $t['date'],
                    'event_for' => $studentUserId,
                    'event_type' => 'task',
                ],
                [
                    'event_description' => $t['desc'],
                    'end_date' => $t['date'],
                    'event_color' => '#000',
                    'role_id' => null,
                    'status' => 'no',
                    'is_active' => $t['active'],
                ]
            );
        }
    }
}
