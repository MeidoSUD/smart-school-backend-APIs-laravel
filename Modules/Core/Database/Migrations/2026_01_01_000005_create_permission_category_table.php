<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('permission_category', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('perm_group_id')->nullable();
            $table->string('name', 100)->nullable();
            $table->string('short_code', 100)->nullable();
            $table->integer('enable_view')->nullable()->default(0);
            $table->integer('enable_add')->nullable()->default(0);
            $table->integer('enable_edit')->nullable()->default(0);
            $table->integer('enable_delete')->nullable()->default(0);
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('permission_category');
    }
};
