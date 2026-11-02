<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('midtrans_settings', function (Blueprint $table) {
            $table->id();
            $table->boolean('is_active')->default(false);
            $table->boolean('is_production')->default(false); // false = Sandbox
            $table->string('merchant_id', 100)->nullable();   // kode merchant (MID)
            $table->string('client_key', 200)->nullable();
            $table->string('server_key', 200)->nullable();
            $table->string('last_test_status', 20)->nullable();
            $table->string('last_test_message', 500)->nullable();
            $table->timestamp('last_tested_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('midtrans_settings');
    }
};
