<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class StaffAttendanceTypeSeeder extends Seeder
{
    public function run(): void
    {
        $types = [
            ['type' => 'Present', 'key_value' => '<b class="text text-success">P</b>', 'is_active' => 'yes', 'for_qr_attendance' => 1, 'long_lang_name' => 'present', 'long_name_style' => 'label label-success'],
            ['type' => 'Late', 'key_value' => '<b class="text text-warning">L</b>', 'is_active' => 'yes', 'for_qr_attendance' => 1, 'long_lang_name' => 'late', 'long_name_style' => 'label label-warning'],
            ['type' => 'Absent', 'key_value' => '<b class="text text-danger">A</b>', 'is_active' => 'yes', 'for_qr_attendance' => 0, 'long_lang_name' => 'absent', 'long_name_style' => 'label label-danger'],
            ['type' => 'Half Day', 'key_value' => '<b class="text text-warning">F</b>', 'is_active' => 'yes', 'for_qr_attendance' => 1, 'long_lang_name' => 'half_day', 'long_name_style' => 'label label-info'],
            ['type' => 'Holiday', 'key_value' => 'H', 'is_active' => 'yes', 'for_qr_attendance' => 0, 'long_lang_name' => 'holiday', 'long_name_style' => 'label label-warning text-dark'],
        ];

        foreach ($types as $type) {
            DB::table('staff_attendance_type')->updateOrInsert(
                ['type' => $type['type']],
                $type
            );
        }
    }
}