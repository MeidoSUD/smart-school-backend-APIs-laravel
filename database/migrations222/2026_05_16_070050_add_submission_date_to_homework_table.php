<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('homework', function (Blueprint $table) {
            $table->date('submission_date')->nullable()->after('homework_date');
        });
    }

    public function down(): void
    {
        Schema::table('homework', function (Blueprint $table) {
            $table->dropColumn('submission_date');
        });
    }
};
