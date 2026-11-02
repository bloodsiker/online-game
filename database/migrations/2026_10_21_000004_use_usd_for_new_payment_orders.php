<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payment_settings', function (Blueprint $table): void {
            $table->char('currency', 3)->default('USD');
        });
        // Нельзя переименовать цену в гривнах в цену в долларах.
        // Администратор задаст новые значения; существующие ордера неизменны.
        DB::table('payment_settings')->where('id', 1)->update([
            'currency' => 'USD', 'diamond_price_minor' => 0, 'minimum_amount_minor' => 0, 'is_enabled' => false,
        ]);
    }

    public function down(): void
    {
        DB::table('payment_settings')->where('id', 1)->update(['is_enabled' => false]);
        Schema::table('payment_settings', function (Blueprint $table): void {
            $table->dropColumn('currency');
        });
    }
};
