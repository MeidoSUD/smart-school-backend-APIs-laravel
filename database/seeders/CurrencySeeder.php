<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CurrencySeeder extends Seeder
{
    // public function run(): void
    // {
    //     $currencies = [
    //         ['name' => 'United States Dollar', 'short_name' => 'USD', 'symbol' => '$', 'base_price' => '1', 'is_active' => 1],
    //         ['name' => 'Indian Rupee', 'short_name' => 'INR', 'symbol' => '₹', 'base_price' => '1', 'is_active' => 0],
    //         ['name' => 'Euro', 'short_name' => 'EUR', 'symbol' => '€', 'base_price' => '1', 'is_active' => 0],
    //         ['name' => 'British Pound', 'short_name' => 'GBP', 'symbol' => '£', 'base_price' => '1', 'is_active' => 0],
    //         ['name' => 'UAE Dirham', 'short_name' => 'AED', 'symbol' => 'AED', 'base_price' => '1', 'is_active' => 0],
    //     ];

    //     foreach ($currencies as $currency) {
    //         DB::table('currencies')->updateOrInsert(
    //             ['short_name' => $currency['short_name']],
    //             $currency
    //         );
    //     }
    // }
}