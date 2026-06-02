<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('book_issues', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('book_id');
            $table->integer('member_id')->nullable();
            $table->date('duereturn_date')->nullable();
            $table->date('return_date')->nullable();
            $table->date('issue_date')->nullable();
            $table->integer('is_returned')->nullable()->default(0);
            $table->string('is_active', 10)->default('no');
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('book_issues');
    }
};
