<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('student_transport_fees', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('transport_feemaster_id');
            $table->unsignedBigInteger('student_session_id');
            $table->unsignedBigInteger('route_pickup_point_id');
            $table->integer('generated_by')->nullable();
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_transport_fees');
    }
};
