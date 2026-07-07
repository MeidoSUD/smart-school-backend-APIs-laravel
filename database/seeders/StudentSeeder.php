<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Modules\Academic\Entities\Student;
use Modules\Academic\Entities\StudentSession;
use Modules\Academic\Entities\StudentTimeline;
use Modules\Academic\Entities\ApplyLeave;
use Modules\Academic\Entities\StudentAttendence;
use Modules\Academic\Entities\AttendenceType;
use Modules\Academic\Entities\Classe;
use Modules\Academic\Entities\Section;
use Modules\Core\Entities\Category;
use Modules\Core\Entities\House;
use Modules\Core\Entities\Session;

class StudentSeeder extends Seeder
{
    public function run(): void
    {
        if (DB::table('categories')->exists()) {
            return;
        }

        $session = Session::first();
        if (!$session) {
            return;
        }

        $class = Classe::first();
        $section = Section::first();
        if (!$class || !$section) {
            return;
        }

        // ===== Categories =====
        $categories = [
            ['category' => 'عام', 'is_active' => 'yes'],
            ['category' => 'منحة دراسية', 'is_active' => 'yes'],
            ['category' => 'أيتام', 'is_active' => 'yes'],
            ['category' => 'ذوي احتياجات خاصة', 'is_active' => 'yes'],
        ];
        foreach ($categories as $cat) {
            Category::create($cat);
        }

        // ===== School Houses =====
        $houses = [
            ['house_name' => 'النيل', 'description' => 'بيت النيل', 'is_active' => 'yes'],
            ['house_name' => 'الفرات', 'description' => 'بيت الفرات', 'is_active' => 'yes'],
            ['house_name' => 'دجلة', 'description' => 'بيت دجلة', 'is_active' => 'yes'],
            ['house_name' => 'النخيل', 'description' => 'بيت النخيل', 'is_active' => 'yes'],
        ];
        foreach ($houses as $house) {
            House::create($house);
        }

        // ===== Students =====
        $studentsData = [
            [
                'firstname' => 'يوسف', 'lastname' => 'السالم',
                'admission_no' => 'ADM2025001', 'roll_no' => '101',
                'mobileno' => '0555111101', 'email' => 'yousuf.alsalem@student.com',
                'father_name' => 'خالد السالم', 'father_phone' => '0555111100',
                'mother_name' => 'هند السالم', 'mother_phone' => '0555111102',
                'guardian_name' => 'خالد السالم', 'guardian_relation' => 'والد',
                'guardian_phone' => '0555111100', 'guardian_email' => 'khalid.alsalem@email.com',
                'dob' => '2011-06-15',
            ],
            [
                'firstname' => 'سارة', 'lastname' => 'القحطاني',
                'admission_no' => 'ADM2025002', 'roll_no' => '102',
                'mobileno' => '0555222201', 'email' => 'sara.alqahtani@student.com',
                'father_name' => 'ناصر القحطاني', 'father_phone' => '0555222200',
                'mother_name' => 'نورة القحطاني', 'mother_phone' => '0555222202',
                'guardian_name' => 'ناصر القحطاني', 'guardian_relation' => 'والد',
                'guardian_phone' => '0555222200', 'guardian_email' => 'nasser.alqahtani@email.com',
                'dob' => '2012-02-20',
            ],
            [
                'firstname' => 'عمر', 'lastname' => 'الزهراني',
                'admission_no' => 'ADM2025003', 'roll_no' => '103',
                'mobileno' => '0555333301', 'email' => 'omar.alzahrani@student.com',
                'father_name' => 'سعيد الزهراني', 'father_phone' => '0555333300',
                'mother_name' => 'مريم الزهراني', 'mother_phone' => '0555333302',
                'guardian_name' => 'سعيد الزهراني', 'guardian_relation' => 'والد',
                'guardian_phone' => '0555333300', 'guardian_email' => 'saeed.alzahrani@email.com',
                'dob' => '2011-11-08',
            ],
            [
                'firstname' => 'ليلى', 'lastname' => 'الشمراني',
                'admission_no' => 'ADM2025004', 'roll_no' => '104',
                'mobileno' => '0555444401', 'email' => 'layla.alshamrani@student.com',
                'father_name' => 'عبدالله الشمراني', 'father_phone' => '0555444400',
                'mother_name' => 'فاطمة الشمراني', 'mother_phone' => '0555444402',
                'guardian_name' => 'عبدالله الشمراني', 'guardian_relation' => 'والد',
                'guardian_phone' => '0555444400', 'guardian_email' => 'abdullah.alshamrani@email.com',
                'dob' => '2012-07-22',
            ],
            [
                'firstname' => 'فيصل', 'lastname' => 'الدوسري',
                'admission_no' => 'ADM2025005', 'roll_no' => '105',
                'mobileno' => '0555555501', 'email' => 'faisal.aldosari@student.com',
                'father_name' => 'محمد الدوسري', 'father_phone' => '0555555500',
                'mother_name' => 'حصة الدوسري', 'mother_phone' => '0555555502',
                'guardian_name' => 'محمد الدوسري', 'guardian_relation' => 'والد',
                'guardian_phone' => '0555555500', 'guardian_email' => 'mohammed.aldosari@email.com',
                'dob' => '2011-04-10',
            ],
        ];

        $categoryIds = Category::pluck('id')->toArray();
        $houseIds = House::pluck('id')->toArray();

        $createdStudents = [];
        foreach ($studentsData as $data) {
            $student = Student::create([
                'parent_id' => 0,
                'admission_no' => $data['admission_no'],
                'roll_no' => $data['roll_no'],
                'admission_date' => '2025-09-01',
                'firstname' => $data['firstname'],
                'middlename' => '',
                'lastname' => $data['lastname'],
                'image' => '',
                'mobileno' => $data['mobileno'],
                'email' => $data['email'],
                'state' => 'الرياض',
                'city' => 'الرياض',
                'pincode' => '12345',
                'religion' => 'الإسلام',
                'cast' => '',
                'dob' => $data['dob'],
                'gender' => in_array($data['firstname'], ['سارة', 'ليلى']) ? 'Female' : 'Male',
                'current_address' => 'الرياض، المملكة العربية السعودية',
                'permanent_address' => 'الرياض، المملكة العربية السعودية',
                'category_id' => (string) ($categoryIds[array_rand($categoryIds)] ?? '1'),
                'school_house_id' => $houseIds[array_rand($houseIds)] ?? null,
                'blood_group' => ['A+', 'B+', 'O+', 'AB+'][array_rand(['A+', 'B+', 'O+', 'AB+'])],
                'guardian_is' => 'father',
                'father_name' => $data['father_name'],
                'father_phone' => $data['father_phone'],
                'father_occupation' => 'موظف',
                'mother_name' => $data['mother_name'],
                'mother_phone' => $data['mother_phone'],
                'mother_occupation' => 'ربة منزل',
                'guardian_name' => $data['guardian_name'],
                'guardian_relation' => $data['guardian_relation'],
                'guardian_phone' => $data['guardian_phone'],
                'guardian_occupation' => 'موظف',
                'guardian_address' => 'الرياض، المملكة العربية السعودية',
                'guardian_email' => $data['guardian_email'],
                'father_pic' => '',
                'mother_pic' => '',
                'guardian_pic' => '',
                'is_active' => 'yes',
                'previous_school' => '',
                'height' => strval(rand(140, 165)),
                'weight' => strval(rand(30, 55)),
                'measurement_date' => '2025-09-01',
                'dis_reason' => 0,
                'note' => '',
                'dis_note' => '',
            ]);
            $createdStudents[] = $student;

            // Student Session
            StudentSession::create([
                'session_id' => $session->id,
                'student_id' => $student->id,
                'class_id' => $class->id,
                'section_id' => $section->id,
                'is_alumni' => 0,
                'default_login' => 1,
                'is_active' => 'yes',
            ]);
        }

        // ===== Student Attendences =====
        $attendenceTypes = AttendenceType::all();
        $presentType = $attendenceTypes->firstWhere('type', 'Present');
        $absentType = $attendenceTypes->firstWhere('type', 'Absent');
        $lateType = $attendenceTypes->firstWhere('type', 'Late');

        $studentSessions = StudentSession::all();

        $date = now()->subMonth();
        for ($day = 0; $day < 20; $day++) {
            $currentDate = $date->copy()->addWeekdays($day);
            if ($currentDate->isSaturday()) continue;

            foreach ($studentSessions as $ss) {
                $rand = rand(1, 10);
                $typeId = $presentType->id;
                if ($rand <= 1) $typeId = $absentType?->id ?? $presentType->id;
                elseif ($rand <= 2) $typeId = $lateType?->id ?? $presentType->id;

                StudentAttendence::create([
                    'student_session_id' => $ss->id,
                    'date' => $currentDate->format('Y-m-d'),
                    'attendence_type_id' => $typeId,
                    'remark' => $typeId === $absentType?->id ? 'غياب' : ($typeId === $lateType?->id ? 'تأخر' : 'حاضر'),
                    'is_active' => 'yes',
                ]);
            }
        }

        // ===== Student Apply Leave =====
        foreach ($studentSessions as $ss) {
            DB::table('student_applyleave')->insert([
                'student_session_id' => $ss->id,
                'from_date' => now()->addMonth()->format('Y-m-d'),
                'to_date' => now()->addMonth()->addDays(2)->format('Y-m-d'),
                'apply_date' => now()->format('Y-m-d'),
                'status' => 1,
                'reason' => 'ظرف عائلي',
                'request_type' => 0,
            ]);
        }

        // ===== Student Documents =====
        foreach ($createdStudents as $student) {
            DB::table('student_doc')->insert([
                'student_id' => $student->id,
                'title' => 'شهادة ميلاد',
                'doc' => '',
            ]);
        }

        // ===== Student Timeline =====
        foreach ($createdStudents as $student) {
            StudentTimeline::create([
                'student_id' => $student->id,
                'title' => 'تم تسجيل الطالب',
                'timeline_date' => '2025-09-01',
                'description' => 'تم تسجيل الطالب في المدرسة',
                'document' => '',
                'status' => 'yes',
                'created_student_id' => 1,
                'date' => '2025-09-01',
            ]);
        }

        // ===== Alumni Students =====
        $alumniStudent = Student::create([
            'parent_id' => 0,
            'admission_no' => 'ADM2020001',
            'roll_no' => '001',
            'admission_date' => '2020-09-01',
            'firstname' => 'نوف',
            'lastname' => 'العتيبي',
            'mobileno' => '0555777701',
            'email' => 'nouf.alotaibi@alumni.com',
            'state' => 'الرياض',
            'city' => 'الرياض',
            'dob' => '2008-03-25',
            'gender' => 'Female',
            'guardian_is' => 'father',
            'father_name' => 'عبدالعزيز العتيبي',
            'father_phone' => '0555777700',
            'mother_name' => 'منيرة العتيبي',
            'mother_phone' => '0555777702',
            'guardian_name' => 'عبدالعزيز العتيبي',
            'guardian_relation' => 'والد',
            'guardian_phone' => '0555777700',
            'guardian_email' => 'abdualaziz.alotaibi@email.com',
            'is_active' => 'yes',
            'height' => '160',
            'weight' => '50',
            'dis_reason' => 0,
            'note' => '',
            'dis_note' => '',
        ]);

        $alumniSession = StudentSession::create([
            'session_id' => $session->id,
            'student_id' => $alumniStudent->id,
            'class_id' => $class->id,
            'section_id' => $section->id,
            'is_alumni' => 1,
            'default_login' => 0,
            'is_active' => 'yes',
        ]);

        DB::table('alumni_students')->insert([
            'student_id' => $alumniStudent->id,
            'current_email' => 'nouf.alotaibi@alumni.com',
            'current_phone' => '0555777701',
            'occupation' => 'طالبة جامعية',
            'address' => 'الرياض، المملكة العربية السعودية',
            'created_at' => now(),
        ]);

        // ===== School Houses =====
        DB::table('school_houses')->insert([
            ['house_name' => 'الأمل', 'description' => 'بيت الأمل', 'is_active' => 'yes'],
            ['house_name' => 'النجاح', 'description' => 'بيت النجاح', 'is_active' => 'yes'],
        ]);

        // ===== Fee Reminder =====
        if (!DB::table('fees_reminder')->where('id', 5)->exists()) {
            DB::table('fees_reminder')->insert([
                ['reminder_type' => 'before', 'day' => 7, 'is_active' => 1, 'created_at' => now()],
                ['reminder_type' => 'after', 'day' => 1, 'is_active' => 1, 'created_at' => now()],
                ['reminder_type' => 'after', 'day' => 7, 'is_active' => 1, 'created_at' => now()],
            ]);
        }
    }
}
