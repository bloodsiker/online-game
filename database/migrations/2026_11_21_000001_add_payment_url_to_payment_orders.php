<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payment_orders', function (Blueprint $table): void {
            $table->string('payment_url', 255)->nullable()->after('provider_payment_id');
            $table->string('provider_status', 32)->nullable()->after('payment_url');
        });
    }

    public function down(): void
    {
        Schema::table('payment_orders', function (Blueprint $table): void {
            $table->dropColumn(['payment_url', 'provider_status']);
        });
    }
};
