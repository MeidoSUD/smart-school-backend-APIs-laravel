<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class HostelRoomTypeSeeder extends Seeder
{
    public function run(): void
    {
        if (DB::table('room_types')->exists()) {
            return;
        }

        $roomTypes = [
            ['room_type' => 'غرفة فردية', 'description' => 'غرفة خاصة لطالب واحد'],
            ['room_type' => 'غرفة مزدوجة', 'description' => 'غرفة مشتركة لطالبين'],
            ['room_type' => 'غرفة ثلاثية', 'description' => 'غرفة مشتركة لثلاثة طلاب'],
            ['room_type' => 'غرفة رباعية', 'description' => 'غرفة مشتركة لأربعة طلاب'],
            ['room_type' => 'جناح', 'description' => 'جناح خاص بمرافق متكاملة'],
        ];

        DB::table('room_types')->insert($roomTypes);
    }
}
