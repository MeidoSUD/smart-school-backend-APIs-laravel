<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('class_sections', function (Blueprint $table) {
            $table->bigIncrements('id');
$table->unsignedBigInteger('class_id')->nullable();

$table->unsignedBigInteger('section_id')->nullable();

            $table->boolean('is_active')->default(true);
            $table->index('is_active');
            $table->timestamp('created_at')->useCurrent();
            $table->date('updated_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('class_sections');
    }
};
