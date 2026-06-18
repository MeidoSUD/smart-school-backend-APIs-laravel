<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('subjects', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name', 100)->nullable();
            $table->string('code', 100);
            $table->string('type', 100);
$table->unsignedBigInteger('teacher_id')->nullable();

            $table->index('teacher_id');
            $table->boolean('is_active')->default(true);
            $table->index('is_active');
            $table->index('name');
            $table->timestamp('created_at')->useCurrent();
            $table->date('updated_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subjects');
    }
};
