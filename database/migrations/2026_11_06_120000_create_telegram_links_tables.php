<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('telegram_links', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->unsignedBigInteger('telegram_user_id')->unique();
            $table->string('username', 32)->nullable();
            $table->timestamp('linked_at');
        });
        Schema::create('telegram_link_tokens', function (Blueprint $table): void {
            $table->foreignId('user_id')->primary()->constrained()->cascadeOnDelete();
            $table->char('token_hash', 64)->unique();
            $table->timestamp('expires_at')->index();
        });
        Schema::create('telegram_webhook_updates', function (Blueprint $table): void {
            $table->unsignedBigInteger('update_id')->primary();
            $table->json('response')->nullable();
            $table->timestamp('created_at')->index();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('telegram_webhook_updates');
        Schema::dropIfExists('telegram_link_tokens');
        Schema::dropIfExists('telegram_links');
    }
};
