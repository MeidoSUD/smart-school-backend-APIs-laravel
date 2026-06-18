<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('read_notification', function (Blueprint $table) {
            $table->bigIncrements('id');
$table->unsignedBigInteger('student_id')->nullable();

$table->unsignedBigInteger('parent_id')->nullable();

$table->unsignedBigInteger('staff_id')->nullable();

$table->unsignedBigInteger('notification_id')->nullable();

            $table->string('is_active', 255)->nullable()->default('no');
            $table->timestamp('created_at')->useCurrent();
            $table->date('updated_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('read_notification');
    }
};
