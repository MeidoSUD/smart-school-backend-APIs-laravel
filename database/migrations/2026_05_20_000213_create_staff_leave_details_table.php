<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('staff_leave_details', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('staff_id');
            $table->integer('leave_type_id');
            $table->string('alloted_leave', 100);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('staff_leave_details');
    }
};
