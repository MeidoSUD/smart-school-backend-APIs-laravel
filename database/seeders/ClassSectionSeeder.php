<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Academic\Entities\ClassSection;
use Modules\Academic\Entities\Classe;
use Modules\Academic\Entities\Section;

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
            'is_active' => 1,
        ]);

        $sectionB = Section::create([
            'section' => 'B',
            'is_active' => 1,
        ]);

        ClassSection::create([
            'class_id' => $class->id,
            'section_id' => $sectionB->id,
            'is_active' => 1,
        ]);
    }
}
