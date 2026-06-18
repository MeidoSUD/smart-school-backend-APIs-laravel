<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fee_session_groups', function (Blueprint $table) {
            $table->bigIncrements('id');
$table->unsignedBigInteger('fee_groups_id')->nullable();

$table->unsignedBigInteger('session_id')->nullable();

            $table->boolean('is_active')->default(true);
            $table->timestamp('created_at')->useCurrent();
        });

        Schema::table('fee_session_groups', function (Blueprint $table) {
            $table->index('fee_groups_id');
            $table->index('session_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fee_session_groups');
    }
};
