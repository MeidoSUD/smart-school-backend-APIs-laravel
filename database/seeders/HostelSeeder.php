<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class HostelSeeder extends Seeder
{
    public function run(): void
    {
        if (DB::table('hostel')->exists()) {
            return;
        }

        $hostelId = DB::table('hostel')->insertGetId([
            'hostel_name' => 'السكن الرئيسي',
            'type' => 'داخلي',
            'address' => 'الحرم الجامعي الرئيسي، الرياض',
            'intake' => 200,
            'description' => 'السكن الرئيسي للطلاب داخل الحرم الجامعي',
            'is_active' => 'yes',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $rooms = [
            ['hostel_id' => $hostelId, 'room_type_id' => 1, 'room_no' => '101', 'no_of_bed' => 1, 'cost_per_bed' => 500.00, 'title' => 'غرفة 101 - فردي'],
            ['hostel_id' => $hostelId, 'room_type_id' => 2, 'room_no' => '102', 'no_of_bed' => 2, 'cost_per_bed' => 300.00, 'title' => 'غرفة 102 - مزدوج'],
            ['hostel_id' => $hostelId, 'room_type_id' => 2, 'room_no' => '103', 'no_of_bed' => 2, 'cost_per_bed' => 300.00, 'title' => 'غرفة 103 - مزدوج'],
            ['hostel_id' => $hostelId, 'room_type_id' => 3, 'room_no' => '104', 'no_of_bed' => 3, 'cost_per_bed' => 200.00, 'title' => 'غرفة 104 - ثلاثي'],
            ['hostel_id' => $hostelId, 'room_type_id' => 4, 'room_no' => '105', 'no_of_bed' => 4, 'cost_per_bed' => 150.00, 'title' => 'غرفة 105 - رباعي'],
        ];

        DB::table('hostel_rooms')->insert($rooms);
    }
}
