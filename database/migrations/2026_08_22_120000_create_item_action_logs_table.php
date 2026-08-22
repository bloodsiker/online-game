<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('item_action_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            // share_item_id + item_name — снимок на момент действия: сам предмет
            // (Item) часто удаляется тем же действием (продажа, выброс квестового
            // предмета), поэтому жёсткая FK на items здесь не подходит.
            $table->foreignId('share_item_id')->nullable()->constrained('share_items')->nullOnDelete();
            $table->string('item_name');
            $table->unsignedInteger('upgrade_lvl')->default(0);
            $table->enum('action', ['drop', 'sell', 'give']);
            $table->unsignedInteger('count')->default(1);
            $table->unsignedInteger('money')->nullable();
            $table->foreignId('target_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['user_id', 'created_at']);
            $table->index('action');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('item_action_logs');
    }
};
