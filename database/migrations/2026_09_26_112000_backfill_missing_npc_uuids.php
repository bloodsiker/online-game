<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('npcs')
            ->whereNull('uuid')
            ->orderBy('id')
            ->lazyById()
            ->each(static function (object $npc): void {
                DB::table('npcs')
                    ->where('id', $npc->id)
                    ->update([
                        'uuid' => (string) Str::uuid(),
                        'updated_at' => now(),
                    ]);
            });
    }

    public function down(): void
    {
        // UUID нужны для публичных карточек NPC; обратная миграция их не удаляет.
    }
};
