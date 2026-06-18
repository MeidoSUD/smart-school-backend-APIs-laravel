<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('staff_roles', function (Blueprint $table) {
            $table->bigIncrements('id');
$table->unsignedBigInteger('role_id')->nullable();

$table->unsignedBigInteger('staff_id')->nullable();

            $table->boolean('is_active')->default(true);
            $table->timestamp('created_at')->useCurrent();
            $table->date('updated_at')->nullable();
            $table->index('staff_id');
            $table->index('role_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('staff_roles');
    }
};
