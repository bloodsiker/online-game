<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        foreach (['shop_items', 'reputation_shop_items', 'influence_shop_items'] as $name) {
            Schema::table($name, function (Blueprint $table): void {
                $table->unsignedInteger('purchase_limit_quantity')->default(0);
                $table->unsignedInteger('purchase_limit_period_seconds')->default(0);
            });
        }
        Schema::create('shop_purchase_limit_states', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('offer_type', 64);
            $table->unsignedBigInteger('offer_id');
            $table->timestamp('period_started_at');
            $table->unsignedInteger('quantity');
            $table->unique(['user_id', 'offer_type', 'offer_id'], 'shop_purchase_limit_user_offer');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shop_purchase_limit_states');
        foreach (['shop_items', 'reputation_shop_items', 'influence_shop_items'] as $name) {
            Schema::table($name, function (Blueprint $table): void {
                $table->dropColumn(['purchase_limit_quantity', 'purchase_limit_period_seconds']);
            });
        }
    }
};
