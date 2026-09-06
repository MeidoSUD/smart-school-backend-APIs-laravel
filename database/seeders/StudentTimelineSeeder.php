<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Modules\Academic\Entities\Student;
use Carbon\Carbon;

class StudentTimelineSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $students = Student::take(10)->get();

        foreach ($students as $student) {
            for ($i = 0; $i < 3; $i++) {
                $date = Carbon::now()->subDays(rand(1, 30))->format('Y-m-d');
                DB::table('student_timeline')->updateOrInsert([
                    'student_id' => $student->id,
                    'title' => 'Sample Timeline ' . ($i + 1),
                ], [
                    'timeline_date' => $date,
                    'description' => 'This is a sample description for timeline ' . ($i + 1),
                    'document' => null,
                    'status' => 'yes',
                    'created_student_id' => $student->id,
                    'date' => $date,
                ]);
            }
        }
    }
}
