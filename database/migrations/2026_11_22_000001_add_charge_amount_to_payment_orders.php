<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Сумма фактического списания у провайдера (monobank списывает в гривне, ордер — в USD).
        Schema::table('payment_orders', function (Blueprint $table): void {
            $table->unsignedBigInteger('charge_amount_minor')->nullable()->after('provider_status');
            $table->string('charge_currency', 3)->nullable()->after('charge_amount_minor');
        });
    }

    public function down(): void
    {
        Schema::table('payment_orders', function (Blueprint $table): void {
            $table->dropColumn(['charge_amount_minor', 'charge_currency']);
        });
    }
};
