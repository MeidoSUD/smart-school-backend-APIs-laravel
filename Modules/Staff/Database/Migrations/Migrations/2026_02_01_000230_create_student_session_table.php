<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('student_session', function (Blueprint $table) {
            $table->bigIncrements('id');
$table->unsignedBigInteger('session_id')->nullable();

$table->unsignedBigInteger('student_id')->nullable();

$table->unsignedBigInteger('class_id')->nullable();

$table->unsignedBigInteger('section_id')->nullable();

$table->unsignedBigInteger('hostel_room_id')->nullable();

$table->unsignedBigInteger('vehroute_id')->nullable();

$table->unsignedBigInteger('route_pickup_point_id')->nullable();

            $table->float('transport_fees', 10, 2)->default(0.00);
            $table->float('fees_discount', 10, 2)->default(0.00);
            $table->integer('is_leave')->default(0);
            $table->boolean('is_active')->default(true);
            $table->integer('is_alumni');
            $table->integer('default_login')->default(0);
            $table->index('default_login');
            $table->timestamp('created_at')->useCurrent();
            $table->date('updated_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_session');
    }
};
