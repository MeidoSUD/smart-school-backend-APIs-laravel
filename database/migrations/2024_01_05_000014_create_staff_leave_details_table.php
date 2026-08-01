<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('staff_leave_details', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->charset = 'utf8mb4';

            $table->id();
            $table->integer('staff_id');
            $table->integer('leave_type_id');
            $table->integer('alloted_leave');
            $table->enum('is_active', ['active', 'inactive'])->default('active');

            $table->timestamps();

            $table->index('staff_id');
            $table->index('leave_type_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('staff_leave_details');
    }
};
