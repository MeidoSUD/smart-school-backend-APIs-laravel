<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('student_transport_fees', function (Blueprint $table) {
            $table->id();
            $table->integer('transport_feemaster_id');
            $table->integer('student_session_id');
            $table->integer('route_pickup_point_id');
            $table->integer('generated_by')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_transport_fees');
    }
};