<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('item_issue', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->charset = 'utf8mb4';

            $table->id();
            $table->integer('item_id')->unsigned();
            $table->integer('item_category_id')->unsigned();
            $table->integer('staff_id')->unsigned();
            $table->integer('issue_quantity');
            $table->date('issue_date');
            $table->date('return_date');
            $table->enum('is_active', ['active', 'inactive'])->default('active');

            $table->timestamps();

            $table->index('item_id');
            $table->index('item_category_id');
            $table->index('staff_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('item_issue');
    }
};
