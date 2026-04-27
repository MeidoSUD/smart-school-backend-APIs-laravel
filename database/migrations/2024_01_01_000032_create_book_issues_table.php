<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('book_issues', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('book_id');
            $table->unsignedBigInteger('member_id')->nullable();
            $table->date('duereturn_date')->nullable();
            $table->date('return_date')->nullable();
            $table->date('issue_date')->nullable();
            $table->tinyInteger('is_returned')->default(0);
            $table->string('is_active', 10)->default('no');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('book_issues');
    }
};