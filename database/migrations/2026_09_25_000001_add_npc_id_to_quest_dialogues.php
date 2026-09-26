<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('quest_dialogues', function (Blueprint $table) {
            // NULL — реплика общая для любого NPC (старое поведение, многостраничная
            // речь одного персонажа). Заполненное значение — реплика видна только
            // на странице конкретного NPC (нужно для квестов, где начало и сдача
            // происходят у разных персонажей).
            $table->foreignId('npc_id')->nullable()->after('quest_id')->constrained('npcs')->nullOnDelete();
            $table->index(['quest_id', 'npc_id', 'order']);
        });
    }

    public function down(): void
    {
        Schema::table('quest_dialogues', function (Blueprint $table) {
            $table->dropForeign(['npc_id']);
            $table->dropIndex(['quest_id', 'npc_id', 'order']);
            $table->dropColumn('npc_id');
        });
    }
};
