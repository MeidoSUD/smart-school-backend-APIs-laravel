<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('homework', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->charset = 'utf8mb4';

            $table->id();
            $table->integer('staff_id')->unsigned();
            $table->integer('class_id')->unsigned();
            $table->integer('section_id')->unsigned();
            $table->integer('subject_id')->unsigned();
            $table->integer('class_section_id')->unsigned();
            $table->date('homework_date');
            $table->date('submit_date');
            $table->string('marks', 100);
            $table->text('description');
            $table->enum('is_active', ['active', 'inactive'])->default('active');

            $table->timestamps();

            $table->index('staff_id');
            $table->index('class_id');
            $table->index('section_id');
            $table->index('subject_id');
            $table->index('class_section_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('homework');
    }
};
