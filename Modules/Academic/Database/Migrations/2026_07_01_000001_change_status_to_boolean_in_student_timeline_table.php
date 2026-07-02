<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('student_timeline', function (Blueprint $table) {
            $table->boolean('status')->change();
        });
    }

    public function down(): void
    {
        Schema::table('student_timeline', function (Blueprint $table) {
            $table->string('status', 200)->change();
        });
    }
};
