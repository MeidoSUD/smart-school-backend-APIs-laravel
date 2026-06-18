<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contents', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('title', 100)->nullable();
            $table->string('type', 50)->nullable();
            $table->string('is_public', 10)->nullable()->default('No');
$table->unsignedBigInteger('class_id')->nullable();

$table->unsignedBigInteger('cls_sec_id')->nullable();

            $table->string('file', 250)->nullable();
            $table->date('date');
            $table->text('note')->nullable();
            $table->string('is_active', 255)->nullable()->default('no');
            $table->integer('created_by');
            $table->timestamp('created_at')->useCurrent();
            $table->date('updated_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contents');
    }
};
