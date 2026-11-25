<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** Текущее изучение вороха у Финко: одно на игрока, таймер ведёт сервер. */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('secret_knowledge_studies', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained('users')->cascadeOnDelete();
            $table->foreignId('npc_id')->constrained('npcs')->cascadeOnDelete();
            $table->uuid('token');
            $table->timestamp('started_at');
            $table->timestamp('completes_at');
            $table->timestamp('expires_at');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('secret_knowledge_studies');
    }
};
