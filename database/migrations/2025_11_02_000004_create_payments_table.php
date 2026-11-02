<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invoice_id')->constrained('invoices')->cascadeOnDelete();
            $table->unsignedBigInteger('user_id')->nullable(); // pemilik invoice (denormalisasi)
            $table->string('method', 20)->index();             // midtrans | manual
            $table->decimal('amount', 15, 2)->default(0);
            $table->string('status', 20)->default('pending')->index(); // pending | success | rejected | failed

            // Konfirmasi manual
            $table->string('bank_name', 100)->nullable();
            $table->string('sender_name', 150)->nullable();
            $table->date('transfer_date')->nullable();
            $table->string('proof_path', 300)->nullable();
            $table->text('notes')->nullable();

            // Midtrans
            $table->string('order_id', 100)->nullable()->unique();
            $table->string('transaction_id', 100)->nullable();
            $table->string('payment_type', 50)->nullable();
            $table->string('snap_url', 500)->nullable();
            $table->json('payload')->nullable();

            // Verifikasi admin (manual)
            $table->foreignId('confirmed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('confirmed_at')->nullable();
            $table->text('admin_note')->nullable();

            $table->timestamp('paid_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
