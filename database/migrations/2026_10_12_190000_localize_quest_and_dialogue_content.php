<?php

declare(strict_types=1);

use App\Modules\Share\Infrastructure\Localization\LocalizedContentMigration;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        $migration = new LocalizedContentMigration;
        $migration->convert(tableName: 'quests', fields: ['title', 'description']);
        $migration->convert(tableName: 'quest_dialogues', fields: ['description', 'reply_text']);
        $migration->convert(tableName: 'quest_stages', fields: ['title', 'description', 'waiting_text', 'ready_text']);
        $migration->convert(tableName: 'quest_objectives', fields: ['description']);
        $migration->convert(tableName: 'npc_dialogue_nodes', fields: ['title', 'description']);
        $migration->convert(tableName: 'npc_dialogue_options', fields: ['button_text']);
    }

    public function down(): void
    {
        throw new RuntimeException('Automatic rollback would discard translations. Restore a verified backup or prepare an explicit content rollback.');
    }
};
