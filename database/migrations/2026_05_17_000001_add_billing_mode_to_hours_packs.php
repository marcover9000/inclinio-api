<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('hours_packs', function (Blueprint $table) {
            $table->string('billing_mode', 10)->default('fixed')->after('reason');
            $table->integer('hourly_rate_cents')->nullable()->after('billing_mode');
            $table->string('hourly_rate_currency', 3)->nullable()->after('hourly_rate_cents');
            $table->integer('hours')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('hours_packs', function (Blueprint $table) {
            $table->dropColumn(['billing_mode', 'hourly_rate_cents', 'hourly_rate_currency']);
            $table->integer('hours')->nullable(false)->change();
        });
    }
};
