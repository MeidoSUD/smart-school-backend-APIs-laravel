<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Category;
use Illuminate\Support\Facades\DB;

class CategorySeeder extends Seeder
{
    // public function run(): void
    // {
    //     $categories = [
    //         ['category' => 'General', 'is_active' => 'yes'],
    //         ['category' => 'OBC', 'is_active' => 'yes'],
    //         ['category' => 'SC', 'is_active' => 'yes'],
    //         ['category' => 'ST', 'is_active' => 'yes'],
    //     ];

    //     foreach ($categories as $category) {
    //         Category::updateOrCreate(
    //             ['category' => $category['category']],
    //             ['is_active' => $category['is_active']]
    //         );
    //     }
    // }
}