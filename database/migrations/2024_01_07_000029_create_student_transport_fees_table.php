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
            $table->integer('student_session_id');
            $table->integer('route_pickup_point_id');
            $table->integer('transport_feemaster_id');
            $table->decimal('amount', 15, 2);
            $table->enum('is_active', ['active', 'inactive'])->default('active');
            $table->timestamps();
            $table->index('student_session_id', 'student_transport_fees_student_session_id_index');
            $table->index('route_pickup_point_id', 'student_transport_fees_route_pickup_point_id_index');
            $table->index('transport_feemaster_id', 'student_transport_fees_transport_feemaster_id_index');
            $table->engine = 'InnoDB';
            $table->charset = 'utf8mb4';
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_transport_fees');
    }
};
