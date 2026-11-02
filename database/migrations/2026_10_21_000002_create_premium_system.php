<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('premium_settings', function (Blueprint $table): void {
            $table->id();
            $table->unsignedInteger('gold_per_diamond')->default(0);
        });
        DB::table('premium_settings')->insert(['id' => 1, 'gold_per_diamond' => 0]);
        Schema::create('premium_levels', function (Blueprint $table): void {
            $table->unsignedTinyInteger('level')->primary();
            $table->unsignedBigInteger('required_diamond_cents')->nullable();
            $table->json('bonuses');
            $table->timestamps();
        });
        foreach (range(1, 5) as $level) {
            DB::table('premium_levels')->insert([
                'level' => $level, 'bonuses' => '{}', 'created_at' => now(), 'updated_at' => now(),
            ]);
        }
        Schema::create('premium_periods', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained()->restrictOnDelete();
            $table->unsignedBigInteger('diamond_cents')->default(0);
            $table->timestamp('started_at');
            $table->timestamp('expires_at');
            $table->timestamps();
            $table->index(['user_id', 'expires_at']);
        });
        Schema::create('premium_exchanges', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained()->restrictOnDelete();
            $table->foreignId('premium_period_id')->constrained()->restrictOnDelete();
            $table->uuid('request_id');
            $table->unsignedBigInteger('diamond_cents');
            $table->unsignedInteger('gold_per_diamond');
            $table->unsignedBigInteger('gold_amount');
            $table->decimal('diamond_balance_after', 14, 2);
            $table->unsignedBigInteger('money_balance_after');
            $table->timestamps();
            $table->unique(['user_id', 'request_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('premium_exchanges');
        Schema::dropIfExists('premium_periods');
        Schema::dropIfExists('premium_levels');
        Schema::dropIfExists('premium_settings');
    }
};
