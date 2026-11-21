<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // История курсов: грн за 1 единицу валюты на дату (источник — НБУ).
        Schema::create('exchange_rates', function (Blueprint $table): void {
            $table->id();
            $table->date('rate_date');
            $table->string('currency', 3);
            $table->decimal('rate', 12, 4);
            $table->string('source', 16)->default('nbu');
            $table->timestamps();
            $table->unique(['rate_date', 'currency', 'source']);
        });
        Schema::table('payment_orders', function (Blueprint $table): void {
            $table->decimal('charge_rate', 12, 4)->nullable()->after('charge_currency');
        });
    }

    public function down(): void
    {
        Schema::table('payment_orders', function (Blueprint $table): void {
            $table->dropColumn('charge_rate');
        });
        Schema::dropIfExists('exchange_rates');
    }
};
