<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notification_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->boolean('wa_enabled')->default(false);
            $table->boolean('email_enabled')->default(true);
            $table->string('wa_number')->nullable();
            $table->string('email')->nullable();
            $table->json('days_before_due')->nullable();
            $table->boolean('send_on_due_day')->default(true);
            $table->integer('send_at_hour')->default(9);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notification_settings');
    }
};
