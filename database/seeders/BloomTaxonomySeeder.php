<?php

namespace Database\Seeders;

use App\Models\BloomTaxonomy;
use Illuminate\Database\Seeder;

class BloomTaxonomySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $levels = [
            ['name' => 'Knowledge', 'code' => 'K', 'sort_order' => 1],
            ['name' => 'Comprehension', 'code' => 'C', 'sort_order' => 2],
            ['name' => 'Application', 'code' => 'A', 'sort_order' => 3],
            ['name' => 'Analysis', 'code' => 'AN', 'sort_order' => 4],
            ['name' => 'Synthesis', 'code' => 'S', 'sort_order' => 5],
            ['name' => 'Evaluation', 'code' => 'E', 'sort_order' => 6],
            ['name' => 'Creation', 'code' => 'CR', 'sort_order' => 7],
        ];

        foreach ($levels as $level) {
            BloomTaxonomy::firstOrCreate(
                ['name' => $level['name']],
                $level
            );
        }
    }
}
