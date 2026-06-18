<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('certificates', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('certificate_name', 100);
            $table->text('certificate_text');
            $table->string('left_header', 100);
            $table->string('center_header', 100);
            $table->string('right_header', 100);
            $table->string('left_footer', 100);
            $table->string('right_footer', 100);
            $table->string('center_footer', 100);
            $table->string('background_image', 100)->nullable();
            $table->boolean('created_for')->comment('1 = staff, 2 = students');
            $table->boolean('status');
            $table->integer('header_height');
            $table->integer('content_height');
            $table->integer('footer_height');
            $table->integer('content_width');
            $table->boolean('enable_student_image')->comment('0=no,1=yes');
            $table->integer('enable_image_height');
            $table->timestamp('created_at')->nullable()->useCurrent();
            $table->date('updated_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('certificates');
    }
};
