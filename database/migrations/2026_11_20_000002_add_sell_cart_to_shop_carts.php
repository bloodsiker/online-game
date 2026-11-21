<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Одна корзина магазина на покупку и продажу.
 * buy  — товар магазина (shop_item_id);
 * sell — предмет игрока из сумки (item_id) для конкретного магазина (structure_id).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('shop_carts', function (Blueprint $table): void {
            $table->string('type', 8)->default('buy')->after('user_id');
            $table->foreignId('structure_id')->nullable()->after('type')
                ->constrained('structures')->cascadeOnDelete();
            $table->foreignId('item_id')->nullable()->after('shop_item_id')
                ->constrained('items')->cascadeOnDelete();
            $table->unsignedBigInteger('shop_item_id')->nullable()->change();

            $table->unique(['user_id', 'structure_id', 'item_id'], 'shop_cart_user_sell_unique');
        });
    }

    public function down(): void
    {
        DB::table('shop_carts')->where('type', 'sell')->orWhereNull('shop_item_id')->delete();

        Schema::table('shop_carts', function (Blueprint $table): void {
            $table->dropUnique('shop_cart_user_sell_unique');
            $table->dropConstrainedForeignId('item_id');
            $table->dropConstrainedForeignId('structure_id');
            $table->dropColumn('type');
        });

        Schema::table('shop_carts', function (Blueprint $table): void {
            $table->unsignedBigInteger('shop_item_id')->nullable(false)->change();
        });
    }
};
