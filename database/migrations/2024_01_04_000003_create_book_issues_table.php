<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('book_issues', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->charset = 'utf8mb4';

            $table->id();
            $table->integer('book_id')->unsigned();
            $table->integer('libarary_members_id')->unsigned();
            $table->date('issue_date');
            $table->date('due_date');
            $table->date('return_date');
            $table->enum('is_active', ['active', 'inactive'])->default('active');

            $table->timestamps();

            $table->index('book_id');
            $table->index('libarary_members_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('book_issues');
    }
};
