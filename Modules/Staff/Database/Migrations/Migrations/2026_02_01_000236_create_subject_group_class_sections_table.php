<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('subject_group_class_sections', function (Blueprint $table) {
            $table->bigIncrements('id');
$table->unsignedBigInteger('subject_group_id')->nullable();

$table->unsignedBigInteger('class_section_id')->nullable();

            $table->index('class_section_id');
$table->unsignedBigInteger('session_id')->nullable();

            $table->text('description')->nullable();
            $table->integer('is_active')->nullable()->default(0);
            $table->timestamp('created_at')->useCurrent();
            $table->date('updated_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subject_group_class_sections');
    }
};
