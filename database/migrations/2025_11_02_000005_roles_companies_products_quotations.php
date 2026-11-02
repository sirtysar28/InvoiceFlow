<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ===== Paket Subscription =====
        Schema::create('subscription_packages', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->string('slug', 100)->unique();
            $table->decimal('price_monthly', 12, 2)->default(0);
            $table->decimal('price_yearly', 12, 2)->default(0);
            $table->integer('max_users')->default(5);
            $table->integer('max_invoices')->default(100);
            $table->integer('max_clients')->default(50);
            $table->json('features')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // ===== Perusahaan (Tenant) =====
        Schema::create('companies', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->nullable();
            $table->string('phone', 30)->nullable();
            $table->text('address')->nullable();
            $table->foreignId('package_id')->nullable()->constrained('subscription_packages')->nullOnDelete();
            $table->date('subscription_expires_at')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Users → milik perusahaan
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('company_id')->nullable()->after('role')
                ->constrained('companies')->nullOnDelete();
        });

        // ===== Produk/Jasa =====
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('unit', 30)->default('pcs');
            $table->decimal('price', 15, 2)->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // ===== Quotation (Penawaran) =====
        Schema::create('quotations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('quote_number', 50)->unique();
            $table->unsignedBigInteger('client_id')->nullable();
            $table->string('client_name');
            $table->string('client_email')->nullable();
            $table->string('client_phone', 30)->nullable();
            $table->json('items');                    // [{description, quantity, unit_price}]
            $table->decimal('subtotal', 15, 2)->default(0);
            $table->decimal('discount', 5, 2)->default(0);
            $table->decimal('discount_amount', 15, 2)->default(0);
            $table->decimal('tax', 5, 2)->default(0);
            $table->decimal('tax_amount', 15, 2)->default(0);
            $table->decimal('total', 15, 2)->default(0);
            $table->date('valid_until')->nullable();
            $table->string('status', 20)->default('draft'); // draft|sent|accepted|rejected|expired|converted
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        // ===== Sales Order =====
        Schema::create('sales_orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('order_number', 50)->unique();
            $table->unsignedBigInteger('quotation_id')->nullable();
            $table->unsignedBigInteger('client_id')->nullable();
            $table->string('client_name');
            $table->string('client_email')->nullable();
            $table->string('client_phone', 30)->nullable();
            $table->json('items');
            $table->decimal('subtotal', 15, 2)->default(0);
            $table->decimal('discount_amount', 15, 2)->default(0);
            $table->decimal('tax_amount', 15, 2)->default(0);
            $table->decimal('total', 15, 2)->default(0);
            $table->date('order_date')->nullable();
            $table->string('status', 20)->default('new'); // new|processing|completed|cancelled|invoiced
            $table->unsignedBigInteger('invoice_id')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        // ===== Pengaturan Sistem (Super Admin) =====
        Schema::create('system_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key', 100)->unique();
            $table->text('value')->nullable();
            $table->timestamps();
        });

        // ===== Paket default =====
        $now = now();
        DB::table('subscription_packages')->insert([
            ['name' => 'Free Trial', 'slug' => 'free-trial', 'price_monthly' => 0, 'price_yearly' => 0,
             'max_users' => 2, 'max_invoices' => 20, 'max_clients' => 5,
             'features' => json_encode(['Invoice dasar', 'WhatsApp share', '1 user']),
             'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Starter', 'slug' => 'starter', 'price_monthly' => 99000, 'price_yearly' => 990000,
             'max_users' => 5, 'max_invoices' => 200, 'max_clients' => 50,
             'features' => json_encode(['Invoice & quotation', 'Notifikasi WA & Email', 'Laporan dasar', 'Payment gateway']),
             'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Business', 'slug' => 'business', 'price_monthly' => 249000, 'price_yearly' => 2490000,
             'max_users' => 15, 'max_invoices' => 1000, 'max_clients' => 300,
             'features' => json_encode(['Semua fitur Starter', 'Midtrans payment', 'Laporan lengkap', 'Multi user (finance & sales)']),
             'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Enterprise', 'slug' => 'enterprise', 'price_monthly' => 599000, 'price_yearly' => 5990000,
             'max_users' => 999, 'max_invoices' => 99999, 'max_clients' => 9999,
             'features' => json_encode(['Semua fitur Business', 'User tak terbatas', 'Prioritas support', 'Custom integrasi']),
             'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
        ]);
    }

    public function down(): void
    {
        Schema::table('users', fn (Blueprint $t) => $t->dropForeign(['company_id']));
        Schema::table('users', fn (Blueprint $t) => $t->dropColumn('company_id'));
        Schema::dropIfExists('sales_orders');
        Schema::dropIfExists('quotations');
        Schema::dropIfExists('products');
        Schema::dropIfExists('companies');
        Schema::dropIfExists('subscription_packages');
        Schema::dropIfExists('system_settings');
    }
};
