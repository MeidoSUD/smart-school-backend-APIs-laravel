<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('follow_up', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->charset = 'utf8mb4';

            $table->id();
            $table->integer('enquiry_id')->unsigned();
            $table->date('followup_date');
            $table->text('note');
            $table->integer('staff_id')->unsigned();
            $table->enum('is_active', ['active', 'inactive'])->default('active');

            $table->timestamps();

            $table->index('enquiry_id');
            $table->index('staff_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('follow_up');
    }
};
