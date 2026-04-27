<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AttendanceTypeSeeder extends Seeder
{
    // public function run(): void
    // {
    //     $types = [
    //         ['type' => 'Present', 'key_value' => '<b class="text text-success">P</b>', 'long_lang_name' => 'present', 'long_name_style' => 'label label-success', 'is_active' => 'yes', 'for_qr_attendance' => 1],
    //         ['type' => 'Late With Excuse', 'key_value' => '<b class="text text-warning">E</b>', 'long_lang_name' => 'late_with_excuse', 'long_name_style' => 'label label-warning text-dark', 'is_active' => 'no', 'for_qr_attendance' => 0],
    //         ['type' => 'Late', 'key_value' => '<b class="text text-warning">L</b>', 'long_lang_name' => 'late', 'long_name_style' => 'label label-warning text-dark', 'is_active' => 'yes', 'for_qr_attendance' => 1],
    //         ['type' => 'Absent', 'key_value' => '<b class="text text-danger">A</b>', 'long_lang_name' => 'absent', 'long_name_style' => 'label label-danger', 'is_active' => 'yes', 'for_qr_attendance' => 0],
    //         ['type' => 'Holiday', 'key_value' => 'H', 'long_lang_name' => 'holiday', 'long_name_style' => 'label label-info', 'is_active' => 'yes', 'for_qr_attendance' => 0],
    //         ['type' => 'Half Day', 'key_value' => '<b class="text text-warning">F</b>', 'long_lang_name' => 'half_day', 'long_name_style' => 'label label-warning text-dark', 'is_active' => 'yes', 'for_qr_attendance' => 1],
    //     ];

    //     foreach ($types as $type) {
    //         DB::table('attendence_type')->updateOrInsert(
    //             ['type' => $type['type']],
    //             $type
    //         );
    //     }
    // }
}