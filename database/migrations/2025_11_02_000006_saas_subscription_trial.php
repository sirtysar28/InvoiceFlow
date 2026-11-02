<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * SaaS subscription:
     * - Paket: trial_days (Free Trial = 15 hari) & duration_days (masa aktif per perpanjangan)
     * - Perusahaan: activation_token (kode dari Super Admin untuk membuka kunci trial habis)
     */
    public function up(): void
    {
        Schema::table('subscription_packages', function (Blueprint $table) {
            $table->unsignedTinyInteger('trial_days')->default(0)->after('is_active');
            $table->unsignedSmallInteger('duration_days')->default(30)->after('trial_days');
        });

        Schema::table('companies', function (Blueprint $table) {
            $table->string('activation_token', 20)->nullable()->after('is_active');
        });

        // Paket trial: 15 hari. Paket berbayar: masa aktif 30 hari per aktivasi.
        \Illuminate\Support\Facades\DB::table('subscription_packages')->where('name', 'like', '%trial%')->update(['trial_days' => 15]);
        \Illuminate\Support\Facades\DB::table('subscription_packages')->where('price_monthly', '>', 0)->update(['duration_days' => 30]);
    }

    public function down(): void
    {
        Schema::table('subscription_packages', function (Blueprint $table) {
            $table->dropColumn(['trial_days', 'duration_days']);
        });
        Schema::table('companies', function (Blueprint $table) {
            $table->dropColumn('activation_token');
        });
    }
};
