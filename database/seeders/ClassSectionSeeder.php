<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ClassSection;
use App\Models\Classe;
use App\Models\Section;

class ClassSectionSeeder extends Seeder
{
    public function run(): void
    {
        if (ClassSection::exists()) {
            return;
        }

        $class = Classe::first();
        $section = Section::first();

        if (!$class || !$section) {
            return;
        }

        ClassSection::create([
            'class_id' => $class->id,
            'section_id' => $section->id,
            'is_active' => 'yes',
        ]);

        $sectionB = Section::create([
            'section' => 'B',
            'is_active' => 'yes',
        ]);

        ClassSection::create([
            'class_id' => $class->id,
            'section_id' => $sectionB->id,
            'is_active' => 'yes',
        ]);
    }
}
