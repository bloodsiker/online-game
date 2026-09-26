<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shop_purchase_logs', function (Blueprint $table): void {
            $table->id();
            $table->uuid('purchase_uuid')->index();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('user_name');
            $table->foreignId('structure_id')->nullable()->constrained('structures')->nullOnDelete();
            $table->string('structure_name')->nullable();
            $table->string('source_type', 40)->index();
            $table->unsignedBigInteger('source_id')->nullable();
            $table->foreignId('share_item_id')->nullable()->constrained('share_items')->nullOnDelete();
            $table->string('item_name');
            $table->unsignedInteger('quantity');
            $table->unsignedBigInteger('unit_price')->default(0);
            $table->unsignedBigInteger('unit_diamond')->default(0);
            $table->unsignedBigInteger('total_price')->default(0);
            $table->unsignedBigInteger('total_diamond')->default(0);
            $table->unsignedBigInteger('money_balance_after')->default(0);
            $table->unsignedBigInteger('diamond_balance_after')->default(0);
            $table->json('requirements')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'created_at']);
            $table->index(['structure_id', 'created_at']);
            $table->index(['share_item_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shop_purchase_logs');
    }
};
