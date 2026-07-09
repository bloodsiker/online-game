<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bank_stocks', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->timestamp('starts_at');
            $table->timestamp('ends_at');
            $table->boolean('is_active')->default(false);
            $table->timestamps();
        });

        Schema::create('bank_stock_tiers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('stock_id')->constrained('bank_stocks')->cascadeOnDelete();
            $table->decimal('diamond_threshold', 10, 2);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index(['stock_id', 'sort_order']);
        });

        Schema::create('bank_stock_tier_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tier_id')->constrained('bank_stock_tiers')->cascadeOnDelete();
            $table->foreignId('share_item_id')->constrained('share_items')->cascadeOnDelete();
            $table->unsignedInteger('count')->default(0);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index(['tier_id', 'sort_order']);
        });

        Schema::create('bank_stock_contributions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('stock_id')->constrained('bank_stocks')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->decimal('amount', 10, 2);
            $table->timestamps();

            $table->index(['stock_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bank_stock_contributions');
        Schema::dropIfExists('bank_stock_tier_items');
        Schema::dropIfExists('bank_stock_tiers');
        Schema::dropIfExists('bank_stocks');
    }
};