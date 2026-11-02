<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * SaaS MULTI-TENANT PENGATURAN:
 * - smtp_settings / midtrans_settings / whatsapp_settings:
 *     baris lama (id=1) menjadi PENGATURAN PLATFORM (company_id NULL, fallback global).
 *     Tiap perusahaan punya baris sendiri setelah admin-nya menyimpan pengaturan.
 * - business_profiles / message_templates:
 *     di-backfill otomatis mengikuti perusahaan pemilik user (user_id → company_id).
 */
return new class extends Migration
{
    public function up(): void
    {
        foreach (['smtp_settings', 'midtrans_settings', 'whatsapp_settings', 'business_profiles', 'message_templates'] as $table) {
            if (!Schema::hasColumn($table, 'company_id')) {
                Schema::table($table, function (Blueprint $t) use ($table) {
                    $t->foreignId('company_id')->nullable()->after('id')
                        ->constrained('companies')->nullOnDelete();
                    $t->index('company_id');
                });
            }
        }

        // Backfill: profil bisnis & template pesan ikut perusahaan pemiliknya
        if (Schema::hasTable('users')) {
            foreach (['business_profiles', 'message_templates'] as $table) {
                DB::statement("UPDATE {$table} SET company_id = (
                    SELECT u.company_id FROM users u WHERE u.id = {$table}.user_id
                ) WHERE company_id IS NULL AND user_id IS NOT NULL");
            }
        }
    }

    public function down(): void
    {
        foreach (['smtp_settings', 'midtrans_settings', 'whatsapp_settings', 'business_profiles', 'message_templates'] as $table) {
            if (Schema::hasColumn($table, 'company_id')) {
                Schema::table($table, function (Blueprint $t) {
                    $t->dropConstrainedForeignId('company_id');
                });
            }
        }
    }
};
