<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transport_route', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('route_title', 100)->nullable();
            $table->integer('no_of_vehicle')->nullable();
            $table->text('note')->nullable();
            $table->string('is_active', 255)->nullable()->default('no');
            $table->timestamp('created_at')->useCurrent();
            $table->date('updated_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transport_route');
    }
};
