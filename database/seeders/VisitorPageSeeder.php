<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Modules\Operations\Entities\Visitor;
use Modules\Academic\Entities\StudentSession;
use Modules\Staff\Entities\Staff;

class VisitorPageSeeder extends Seeder
{
    public function run(): void
    {
        if (DB::table('visitors_book')->exists()) {
            return;
        }

        $studentSessions = StudentSession::where('is_active', 'yes')->get();
        if ($studentSessions->isEmpty()) {
            return;
        }

        $staffMembers = Staff::where('is_active', 'yes')->get();

        // ===== Visitors Purpose (lookup table) =====
        if (!DB::table('visitors_purpose')->exists()) {
            $purposes = [
                ['visitors_purpose' => 'زيارة ولي الأمر', 'description' => 'زيارة من ولي أمر الطالب'],
                ['visitors_purpose' => 'توصيل طرد', 'description' => 'توصيل طرد أو مستندات للطالب'],
                ['visitors_purpose' => 'مقابلة إدارية', 'description' => 'مقابلة مع الإدارة المدرسية'],
                ['visitors_purpose' => 'مقابلة أكاديمية', 'description' => 'مقابلة مع المعلمين بشأن الأداء الأكاديمي'],
                ['visitors_purpose' => 'استلام الطالب', 'description' => 'استلام الطالب في وقت مبكر'],
                ['visitors_purpose' => 'تسليم وثائق', 'description' => 'تسليم وثائق أو مستندات رسمية'],
                ['visitors_purpose' => 'زيارة مراجعة', 'description' => 'زيارة مراجعة أو متابعة'],
                ['visitors_purpose' => 'أخرى', 'description' => 'غرض آخر غير محدد'],
            ];
            foreach ($purposes as $purpose) {
                DB::table('visitors_purpose')->insert($purpose);
            }
        }

        $purposeOptions = [
            'زيارة ولي أمر',
            'توصيل طرد',
            'مقابلة إدارية',
            'مقابلة أكاديمية',
            'استلام الطالب',
            'تسليم وثائق',
            'زيارة مراجعة',
        ];

        $visitorNames = [
            ['name' => 'خالد السالم', 'email' => 'khalid.alsalem@email.com', 'contact' => '0555111100'],
            ['name' => 'ناصر القحطاني', 'email' => 'nasser.alqahtani@email.com', 'contact' => '0555222200'],
            ['name' => 'سعيد الزهراني', 'email' => 'saeed.alzahrani@email.com', 'contact' => '0555333300'],
            ['name' => 'عبدالله الشمراني', 'email' => 'abdullah.alshamrani@email.com', 'contact' => '0555444400'],
            ['name' => 'محمد الدوسري', 'email' => 'mohammed.aldosari@email.com', 'contact' => '0555555500'],
            ['name' => 'أحمد العتيبي', 'email' => 'ahmed.alotaibi@email.com', 'contact' => '0555666600'],
            ['name' => 'فهد المطيري', 'email' => 'fahd.mutairi@email.com', 'contact' => '0555777700'],
            ['name' => 'سلطان الحربي', 'email' => 'sultan.harbi@email.com', 'contact' => '0555888800'],
            ['name' => 'عمر الشمري', 'email' => 'omar.shamari@email.com', 'contact' => '0555999900'],
            ['name' => 'يوسف الزعبي', 'email' => 'yousuf.zaaabi@email.com', 'contact' => '0555000000'],
        ];

        $notes = [
            'تم التوقيع على الاستلام',
            'الطالب في حالة مرضية خفيفة',
            'تسليم الوثائق المطلوبة',
            'تمت المتابعة مع المعلمين',
            'زيارة مراجعة للأداء الدراسي',
            'استلام مبكر بسبب ظرف عائلي',
            'تسليم كتب مدرسية',
            'مقابلة مع مرشد الطلاب',
            'تسليم مستندات التسجيل',
            'زيارة تفقدية من ولي الأمر',
        ];

        foreach ($visitorNames as $index => $visitor) {
            $meetingWith = ($index % 3 === 0 && $staffMembers->isNotEmpty()) ? 'staff' : 'student';
            $staffId = null;
            $studentSessionId = null;

            if ($meetingWith === 'staff' && $staffMembers->isNotEmpty()) {
                $staffId = $staffMembers[$index % $staffMembers->count()]->id;
            } else {
                $studentSessionId = $studentSessions[$index % $studentSessions->count()]->id;
            }

            $date = now()->subDays(rand(0, 30))->format('Y-m-d');
            $inHour = rand(7, 13);
            $inMinute = rand(0, 59);
            $inTime = sprintf('%02d:%02d %s', $inHour > 12 ? $inHour - 12 : $inHour, $inMinute, $inHour >= 12 ? 'PM' : 'AM');

            $outHour = $inHour + rand(0, 2);
            $outMinute = rand(0, 59);
            $outTime = sprintf('%02d:%02d %s', $outHour > 12 ? $outHour - 12 : $outHour, $outMinute, $outHour >= 12 ? 'PM' : 'AM');

            Visitor::create([
                'staff_id' => $staffId,
                'student_session_id' => $studentSessionId,
                'source' => $index % 4 === 0 ? '(parent)' : '(visitor)',
                'purpose' => $purposeOptions[$index % count($purposeOptions)],
                'name' => $visitor['name'],
                'email' => $visitor['email'],
                'contact' => $visitor['contact'],
                'id_proof' => str_pad(rand(1000000000, 9999999999), 10, '0', STR_PAD_LEFT),
                'no_of_people' => rand(1, 3),
                'date' => $date,
                'in_time' => $inTime,
                'out_time' => $outTime,
                'note' => $notes[$index % count($notes)],
                'image' => '',
                'meeting_with' => $meetingWith,
            ]);
        }
    }
}
