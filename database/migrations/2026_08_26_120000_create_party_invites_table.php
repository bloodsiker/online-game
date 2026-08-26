<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Приглашения в группу: лидер не добавляет игрока напрямую,
 * а отправляет приглашение, видимое только приглашённому в чате.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('party_invites', function (Blueprint $table) {
            $table->id();
            $table->foreignId('party_id')->constrained('parties')->cascadeOnDelete();
            $table->foreignId('inviter_user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('invited_user_id')->constrained('users')->cascadeOnDelete();
            $table->enum('status', ['pending', 'accepted', 'declined', 'cancelled'])->default('pending');
            $table->timestamps();

            $table->index(['invited_user_id', 'status']);
            $table->index(['party_id', 'invited_user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('party_invites');
    }
};
