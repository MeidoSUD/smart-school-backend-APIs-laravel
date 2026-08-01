<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ClassTimetable;
use App\Models\Classe;
use App\Models\Section;
use App\Models\ClassSection;
use App\Models\Subject;
use App\Models\SubjectGroup;
use App\Models\SubjectGroupClassSection;
use App\Models\SubjectGroupSubject;
use App\Models\Session;
use App\Models\Staff;

class TimetableSeeder extends Seeder
{
    public function run(): void
    {
        if (ClassTimetable::exists()) {
            return;
        }

        $session = Session::first();
        $class = Classe::first();
        $sectionA = Section::first();

        if (!$session || !$class || !$sectionA) {
            return;
        }

        $classSectionA = ClassSection::where('class_id', $class->id)
            ->where('section_id', $sectionA->id)
            ->first();

        if (!$classSectionA) {
            return;
        }

        $teacher = Staff::first();

        $subjects = [
            ['name' => 'الرياضيات', 'code' => 'MATH', 'type' => 'theory'],
            ['name' => 'العلوم', 'code' => 'SCI', 'type' => 'theory'],
            ['name' => 'اللغة العربية', 'code' => 'ARB', 'type' => 'theory'],
            ['name' => 'اللغة الإنجليزية', 'code' => 'ENG', 'type' => 'theory'],
            ['name' => 'الدراسات الإسلامية', 'code' => 'ISL', 'type' => 'theory'],
            ['name' => 'التربية البدنية', 'code' => 'PE', 'type' => 'practical'],
            ['name' => 'الحاسب الآلي', 'code' => 'ICT', 'type' => 'practical'],
            ['name' => 'الدراسات الاجتماعية', 'code' => 'SOC', 'type' => 'theory'],
        ];

        $subjectModels = [];
        foreach ($subjects as $subjectData) {
            $subjectModels[$subjectData['code']] = Subject::create([
                'name' => $subjectData['name'],
                'code' => $subjectData['code'],
                'type' => $subjectData['type'],
                'is_active' => 'yes',
            ]);
        }

        $subjectGroup = SubjectGroup::create([
            'name' => 'المواد الأساسية',
            'description' => 'مجموعة المواد للصف السابع',
            'session_id' => $session->id,
        ]);

        SubjectGroupClassSection::create([
            'class_section_id' => $classSectionA->id,
            'subject_group_id' => $subjectGroup->id,
            'session_id' => $session->id,
            'is_active' => 1,
        ]);

        $subjectGroupSubjects = [];
        foreach ($subjectModels as $code => $subject) {
            $subjectGroupSubjects[$code] = SubjectGroupSubject::create([
                'subject_group_id' => $subjectGroup->id,
                'subject_id' => $subject->id,
                'session_id' => $session->id,
            ]);
        }

        $timetable = [
            'Sunday' => [
                ['subject' => 'ARB', 'from' => '07:30', 'to' => '08:15', 'room' => '101'],
                ['subject' => 'MATH', 'from' => '08:15', 'to' => '09:00', 'room' => '101'],
                ['subject' => 'ENG', 'from' => '09:00', 'to' => '09:45', 'room' => '101'],
                ['subject' => 'ISL', 'from' => '10:00', 'to' => '10:45', 'room' => '101'],
                ['subject' => 'SCI', 'from' => '10:45', 'to' => '11:30', 'room' => '102'],
                ['subject' => 'SOC', 'from' => '11:30', 'to' => '12:15', 'room' => '101'],
            ],
            'Monday' => [
                ['subject' => 'MATH', 'from' => '07:30', 'to' => '08:15', 'room' => '101'],
                ['subject' => 'ARB', 'from' => '08:15', 'to' => '09:00', 'room' => '101'],
                ['subject' => 'SCI', 'from' => '09:00', 'to' => '09:45', 'room' => '102'],
                ['subject' => 'ICT', 'from' => '10:00', 'to' => '10:45', 'room' => '201'],
                ['subject' => 'ENG', 'from' => '10:45', 'to' => '11:30', 'room' => '101'],
                ['subject' => 'PE', 'from' => '11:30', 'to' => '12:15', 'room' => 'ملعب'],
            ],
            'Tuesday' => [
                ['subject' => 'ENG', 'from' => '07:30', 'to' => '08:15', 'room' => '101'],
                ['subject' => 'ISL', 'from' => '08:15', 'to' => '09:00', 'room' => '101'],
                ['subject' => 'MATH', 'from' => '09:00', 'to' => '09:45', 'room' => '101'],
                ['subject' => 'ARB', 'from' => '10:00', 'to' => '10:45', 'room' => '101'],
                ['subject' => 'SOC', 'from' => '10:45', 'to' => '11:30', 'room' => '101'],
                ['subject' => 'SCI', 'from' => '11:30', 'to' => '12:15', 'room' => '102'],
            ],
            'Wednesday' => [
                ['subject' => 'SCI', 'from' => '07:30', 'to' => '08:15', 'room' => '102'],
                ['subject' => 'MATH', 'from' => '08:15', 'to' => '09:00', 'room' => '101'],
                ['subject' => 'ARB', 'from' => '09:00', 'to' => '09:45', 'room' => '101'],
                ['subject' => 'ENG', 'from' => '10:00', 'to' => '10:45', 'room' => '101'],
                ['subject' => 'ISL', 'from' => '10:45', 'to' => '11:30', 'room' => '101'],
                ['subject' => 'ICT', 'from' => '11:30', 'to' => '12:15', 'room' => '201'],
            ],
            'Thursday' => [
                ['subject' => 'ARB', 'from' => '07:30', 'to' => '08:15', 'room' => '101'],
                ['subject' => 'ENG', 'from' => '08:15', 'to' => '09:00', 'room' => '101'],
                ['subject' => 'MATH', 'from' => '09:00', 'to' => '09:45', 'room' => '101'],
                ['subject' => 'PE', 'from' => '10:00', 'to' => '10:45', 'room' => 'ملعب'],
                ['subject' => 'SOC', 'from' => '10:45', 'to' => '11:30', 'room' => '101'],
            ],
        ];

        foreach ($timetable as $day => $periods) {
            foreach ($periods as $period) {
                ClassTimetable::create([
                    'class_id' => $class->id,
                    'section_id' => $sectionA->id,
                    'subject_group_id' => $subjectGroup->id,
                    'subject_group_subject_id' => $subjectGroupSubjects[$period['subject']]->id,
                    'staff_id' => $teacher?->id,
                    'day' => $day,
                    'time_from' => $period['from'],
                    'time_to' => $period['to'],
                    'room_no' => $period['room'],
                    'session_id' => $session->id,
                ]);
            }
        }
    }
}
