<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class LanguageSeeder extends Seeder
{
    // public function run(): void
    // {
    //     $languages = [
    //         ['language' => 'English', 'short_code' => 'en', 'country_code' => 'us', 'is_rtl' => 0, 'is_deleted' => 'no', 'is_active' => 'no'],
    //         ['language' => 'Hindi', 'short_code' => 'hi', 'country_code' => 'in', 'is_rtl' => 0, 'is_deleted' => 'no', 'is_active' => 'no'],
    //         ['language' => 'Arabic', 'short_code' => 'ar', 'country_code' => 'sa', 'is_rtl' => 1, 'is_deleted' => 'no', 'is_active' => 'no'],
    //         ['language' => 'Urdu', 'short_code' => 'ur', 'country_code' => 'pk', 'is_rtl' => 1, 'is_deleted' => 'no', 'is_active' => 'no'],
    //     ];

    //     foreach ($languages as $language) {
    //         DB::table('languages')->updateOrInsert(
    //             ['short_code' => $language['short_code']],
    //             $language
    //         );
    //     }
    // }
}