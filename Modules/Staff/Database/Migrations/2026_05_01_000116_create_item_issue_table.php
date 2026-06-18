<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('item_issue', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('issue_type', 15)->nullable();
            $table->integer('issue_to');
            $table->integer('issue_by')->nullable();
            $table->date('issue_date')->nullable();
            $table->date('return_date')->nullable();
            $table->unsignedBigInteger('item_category_id')->nullable();
            $table->unsignedBigInteger('item_id')->nullable();
            $table->integer('quantity');
            $table->text('note');
            $table->integer('is_returned')->default(1);
            $table->timestamp('created_at')->useCurrent();
            $table->string('is_active', 10)->nullable()->default('no');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('item_issue');
    }
};
