<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payment_settings', function (Blueprint $table): void {
            $table->id();
            $table->unsignedInteger('diamond_price_minor')->default(650);
            $table->unsignedInteger('minimum_amount_minor')->default(50000);
            $table->boolean('is_enabled')->default(true);
            $table->timestamps();
        });
        DB::table('payment_settings')->insert([
            'id' => 1, 'diamond_price_minor' => 650, 'minimum_amount_minor' => 50000,
            'is_enabled' => true, 'created_at' => now(), 'updated_at' => now(),
        ]);
        Schema::create('payment_orders', function (Blueprint $table): void {
            $table->id();
            $table->uuid('public_id')->unique();
            $table->foreignId('user_id')->constrained()->restrictOnDelete();
            $table->uuid('request_id');
            $table->string('status', 16)->default('pending')->index();
            $table->string('provider', 32)->default('manual');
            $table->string('provider_payment_id')->nullable();
            $table->char('currency', 3)->default('UAH');
            $table->unsignedBigInteger('amount_minor');
            $table->unsignedInteger('diamond_price_minor');
            $table->unsignedInteger('diamonds');
            $table->unsignedBigInteger('diamond_balance_after')->nullable();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->restrictOnDelete();
            $table->text('review_note')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamp('credited_at')->nullable();
            $table->timestamps();
            $table->unique(['user_id', 'request_id']);
            $table->unique(['provider', 'provider_payment_id']);
            $table->index(['user_id', 'created_at']);
        });
        Schema::create('payment_order_events', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('payment_order_id')->constrained()->restrictOnDelete();
            $table->foreignId('actor_id')->nullable()->constrained('users')->restrictOnDelete();
            $table->string('from_status', 16)->nullable();
            $table->string('to_status', 16);
            $table->text('note')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_order_events');
        Schema::dropIfExists('payment_orders');
        Schema::dropIfExists('payment_settings');
    }
};
