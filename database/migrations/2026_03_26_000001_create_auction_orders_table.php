<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('auction_orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('structure_id')->nullable()->constrained('structures')->nullOnDelete();
            $table->foreignId('share_item_id')->constrained('share_items')->cascadeOnDelete();
            $table->integer('count')->default(1);
            $table->integer('price')->default(0);
            $table->boolean('is_anonymous')->default(0);
            $table->timestamps();
        });

        Schema::create('auction_claims', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('structure_id')->nullable()->constrained('structures')->nullOnDelete();
            $table->foreignId('item_id')->constrained('items')->cascadeOnDelete();
            $table->integer('count')->default(1);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('auction_claims');
        Schema::dropIfExists('auction_orders');
    }
};
