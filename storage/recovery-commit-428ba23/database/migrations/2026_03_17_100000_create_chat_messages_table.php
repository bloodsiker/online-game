<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('chat_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('channel', 20)->default('main'); // main|location|trade|clan|private|system
            $table->foreignId('target_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('clan_id')->nullable()->constrained('clans')->nullOnDelete();
            $table->foreignId('map_id')->nullable()->constrained('maps')->nullOnDelete();
            $table->text('message');
            $table->string('type', 20)->default('message'); // message|private|system|mention
            $table->timestamps();

            $table->index(['channel', 'created_at']);
            $table->index(['target_user_id', 'channel']);
            $table->index(['map_id', 'channel']);
        });

        Schema::create('chat_ignores', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('ignored_user_id')->constrained('users')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['user_id', 'ignored_user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('chat_ignores');
        Schema::dropIfExists('chat_messages');
    }
};
