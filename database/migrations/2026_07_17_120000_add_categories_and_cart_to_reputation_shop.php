<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Категория товара (переиспользуем общий каталог share_structure_categories)
        Schema::table('reputation_shop_items', function (Blueprint $table) {
            $table->foreignId('share_structure_category_id')->nullable()->after('reputation_id')
                ->constrained('share_structure_categories')->nullOnDelete();
        });

        // Какие категории показывает магазин данной репутации
        Schema::create('reputation_categories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('reputation_id')->constrained('reputations')->cascadeOnDelete();
            $table->foreignId('share_structure_category_id')->constrained('share_structure_categories')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['reputation_id', 'share_structure_category_id'], 'rep_cat_unique');
        });

        // Корзина магазина репутации
        Schema::create('reputation_shop_carts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('reputation_shop_item_id')->constrained('reputation_shop_items')->cascadeOnDelete();
            $table->integer('quantity')->default(1);
            $table->timestamps();

            $table->unique(['user_id', 'reputation_shop_item_id'], 'rep_cart_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reputation_shop_carts');
        Schema::dropIfExists('reputation_categories');
        Schema::table('reputation_shop_items', function (Blueprint $table) {
            $table->dropForeign(['share_structure_category_id']);
            $table->dropColumn('share_structure_category_id');
        });
    }
};
