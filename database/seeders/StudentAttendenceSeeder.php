<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class StudentAttendenceSeeder extends Seeder
{
    public function run(): void
    {
        $types = [
            ['type' => 'Present', 'key_value' => 'P', 'is_active' => 1],
            ['type' => 'Absent', 'key_value' => 'A', 'is_active' => 1],
            ['type' => 'Late', 'key_value' => 'L', 'is_active' => 1],
            ['type' => 'Half Day', 'key_value' => 'F', 'is_active' => 1],
        ];
        
        foreach ($types as $type) {
            DB::table('attendence_type')->updateOrInsert(
                ['key_value' => $type['key_value']],
                ['type' => $type['type'], 'is_active' => $type['is_active']]
            );
        }

        $presentType = DB::table('attendence_type')->where('key_value', 'P')->first();
        if (!$presentType) return;

        $studentSessions = DB::table('student_session')->take(10)->get();

        foreach ($studentSessions as $session) {
            // Seed attendance for different months in 2025 and 2026
            $datesToSeed = [
                '2025-09-10', '2025-10-15', '2025-11-20', '2025-12-05',
                '2026-01-10', '2026-02-14', '2026-03-20', '2026-04-25',
                '2026-05-10', '2026-06-15', '2026-07-20', '2026-08-25',
                '2026-09-01', '2026-09-02'
            ];
            foreach ($datesToSeed as $date) {
                DB::table('student_attendences')->updateOrInsert(
                    [
                        'student_session_id' => $session->id,
                        'date' => $date,
                    ],
                    [
                        'attendence_type_id' => $presentType->id,
                        'remark' => 'On time',
                        'is_active' => 1,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]
                );
            }
        }
    }
}
