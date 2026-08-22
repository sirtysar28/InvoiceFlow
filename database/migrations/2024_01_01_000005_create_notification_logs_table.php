<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notification_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invoice_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('channel');
            $table->string('type')->default('due_reminder');
            $table->integer('days_before_due')->default(0);
            $table->timestamp('sent_at')->nullable();
            $table->string('status')->default('sent');
            $table->text('message')->nullable();
            $table->timestamps();

            $table->unique(['invoice_id', 'days_before_due', 'channel'], 'unique_notification_per_day');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notification_logs');
    }
};
