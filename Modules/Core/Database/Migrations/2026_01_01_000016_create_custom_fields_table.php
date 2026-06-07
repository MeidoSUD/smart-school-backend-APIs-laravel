<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('custom_fields', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name', 100)->nullable();
            $table->string('belong_to', 100)->nullable();
            $table->string('type', 100)->nullable();
            $table->integer('bs_column')->nullable();
            $table->integer('validation')->nullable()->default(0);
            $table->text('field_values')->nullable();
            $table->string('show_table', 100)->nullable();
            $table->integer('visible_on_table');
            $table->integer('weight')->nullable();
            $table->integer('is_active')->nullable()->default(0);
            $table->timestamp('created_at')->useCurrent();
            $table->date('updated_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('custom_fields');
    }
};
