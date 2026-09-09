<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('player_injuries', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('player_id')->constrained('players')->cascadeOnDelete();
            $table->string('body_part', 32);
            $table->unsignedTinyInteger('severity');
            $table->timestamp('applied_at');
            $table->timestamp('expires_at');
            $table->timestamps();

            $table->unique(['player_id', 'body_part']);
            $table->index('expires_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('player_injuries');
    }
};
