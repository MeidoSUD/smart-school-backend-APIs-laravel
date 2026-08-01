<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('student_fees_deposite', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->charset = 'utf8mb4';

            $table->id();
            $table->integer('student_transport_fees_id');
            $table->integer('student_fees_master_id');
            $table->integer('fee_groups_feetype_id');
            $table->decimal('amount', 15, 2);
            $table->enum('is_active', ['active', 'inactive'])->default('active');

            $table->timestamps();

            $table->index('student_transport_fees_id');
            $table->index('student_fees_master_id');
            $table->index('fee_groups_feetype_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_fees_deposite');
    }
};
