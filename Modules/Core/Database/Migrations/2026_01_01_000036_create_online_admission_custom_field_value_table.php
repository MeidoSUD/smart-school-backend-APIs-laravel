<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('online_admission_custom_field_value', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('belong_table_id')->nullable();
            $table->integer('custom_field_id')->nullable();
            $table->longText('field_value');
            $table->timestamp('created_at')->useCurrent();
            $table->date('updated_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('online_admission_custom_field_value');
    }
};
