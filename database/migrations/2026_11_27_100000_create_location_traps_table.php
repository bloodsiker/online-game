<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('location_traps', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('location_id')->constrained('locations')->cascadeOnDelete();
            // north|south|east|west — ловушка срабатывает при выходе в это направление; NULL — при любом выходе.
            $table->string('direction', 8)->nullable();
            // Ключ типа ловушки из TrapService::TYPES (например, crossing).
            $table->string('type', 32);
            $table->json('name');
            $table->json('config')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['location_id', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('location_traps');
    }
};
