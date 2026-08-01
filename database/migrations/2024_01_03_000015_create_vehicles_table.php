<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vehicles', function (Blueprint $table) {
            $table->id();
            $table->string('vehicle_no', 100);
            $table->string('vehicle_model', 100);
            $table->string('vehicle_maker', 100);
            $table->integer('year_made');
            $table->string('vehicle_colour', 100);
            $table->string('chassis_no', 100);
            $table->string('insurance_no', 100);
            $table->date('insurance_expire');
            $table->date('tax_valid_upto');
            $table->date('fitness_valid_upto');
            $table->date('permit_valid_upto');
            $table->date('puc_valid_upto');
            $table->string('fuel_type', 20);
            $table->string('driver_name', 100);
            $table->string('driver_licence', 100);
            $table->date('driver_license_expire');
            $table->string('driver_mobile_no', 20);
            $table->enum('is_active', ['active', 'inactive'])->default('active');
            $table->timestamps();
        });

        DB::statement('ALTER TABLE `vehicles` ENGINE = InnoDB');
        DB::statement('ALTER TABLE `vehicles` DEFAULT CHARSET = utf8mb4');
    }

    public function down(): void
    {
        Schema::dropIfExists('vehicles');
    }
};
