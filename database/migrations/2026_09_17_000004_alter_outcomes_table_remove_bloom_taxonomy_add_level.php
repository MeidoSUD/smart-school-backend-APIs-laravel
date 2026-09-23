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
        Schema::table('outcomes', function (Blueprint $table) {
            if (!Schema::hasColumn('outcomes', 'level')) {
                $table->tinyInteger('level')->unsigned()->default(1)->after('code');
            }
        });

        if (Schema::hasColumn('outcomes', 'bloom_taxonomy_id')) {
            Schema::table('outcomes', function (Blueprint $table) {
                $table->dropColumn('bloom_taxonomy_id');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (!Schema::hasColumn('outcomes', 'bloom_taxonomy_id')) {
            Schema::table('outcomes', function (Blueprint $table) {
                $table->unsignedBigInteger('bloom_taxonomy_id')->nullable()->after('code');
            });
        }

        if (Schema::hasColumn('outcomes', 'level')) {
            Schema::table('outcomes', function (Blueprint $table) {
                $table->dropColumn('level');
            });
        }
    }
};
