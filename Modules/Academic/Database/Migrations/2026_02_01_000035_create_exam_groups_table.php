<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('exam_groups', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name', 250)->nullable();
            $table->string('exam_type', 250)->nullable();
            $table->text('description')->nullable();
            $table->integer('is_active')->nullable()->default(1);
            $table->timestamp('created_at')->useCurrent();
            $table->date('updated_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('exam_groups');
    }
};
