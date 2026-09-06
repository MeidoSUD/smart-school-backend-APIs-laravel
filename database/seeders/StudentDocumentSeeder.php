<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Modules\Academic\Entities\Student;

class StudentDocumentSeeder extends Seeder
{
    public function run(): void
    {
        $students = Student::take(10)->get();

        foreach ($students as $student) {
            for ($i = 1; $i <= 2; $i++) {
                DB::table('student_doc')->updateOrInsert(
                    ['student_id' => $student->id, 'title' => 'Document ' . $i],
                    ['doc' => 'sample_doc_' . $i . '.pdf']
                );
            }
        }
    }
}
