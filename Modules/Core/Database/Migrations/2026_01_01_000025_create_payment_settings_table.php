<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payment_settings', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('payment_type', 200);
            $table->string('api_username', 200)->nullable();
            $table->string('api_secret_key', 200);
            $table->string('salt', 200);
            $table->string('api_publishable_key', 200);
            $table->string('api_password', 200)->nullable();
            $table->string('api_signature', 200)->nullable();
            $table->string('api_email', 200)->nullable();
            $table->string('paypal_demo', 100);
            $table->string('account_no', 200);
            $table->string('is_active', 255)->nullable()->default('no');
            $table->integer('gateway_mode')->comment('0 Testing, 1 live');
            $table->string('paytm_website', 255);
            $table->string('paytm_industrytype', 255);
            $table->timestamp('created_at')->useCurrent();
            $table->date('updated_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_settings');
    }
};
