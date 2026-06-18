<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('custom_field_values', function (Blueprint $table) {
            $table->bigIncrements('id');
$table->unsignedBigInteger('belong_table_id')->nullable();

$table->unsignedBigInteger('custom_field_id')->nullable();

            $table->string('field_value', 500)->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->date('updated_at')->nullable();
            $table->index('belong_table_id');
            $table->index('custom_field_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('custom_field_values');
    }
};
