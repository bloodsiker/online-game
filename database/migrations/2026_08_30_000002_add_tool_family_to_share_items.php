<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Заменяет жёсткую привязку ресурса к одному конкретному инструменту
     * (gathering_tool_share_item_id) на привязку по семейству инструмента:
     * инструменты получают tool_family + gathering_speed_bonus_percent,
     * ресурсы — только gathering_tool_family. Любой инструмент нужного
     * семейства подходит для добычи; bonus напрямую задаётся у инструмента
     * в админке и лишь ускоряет добычу (см. GatheringService::
     * effectiveGatheringSeconds), доступ к редким ресурсам по нему не
     * гейтится — решение подтверждено владельцем продукта.
     */
    public function up(): void
    {
        Schema::table('share_items', function (Blueprint $table): void {
            $table->string('tool_family')->nullable()->after('slot');
            $table->unsignedTinyInteger('gathering_speed_bonus_percent')->default(0)->after('tool_family');
            $table->string('gathering_tool_family')->nullable()->after('gathering_tool_share_item_id');
        });

        $tools = [
            'Серп' => 'sickle',
            'Удочка' => 'rod',
            'Кирка' => 'pickaxe',
            'Топор' => 'axe',
        ];

        foreach ($tools as $name => $family) {
            DB::table('share_items')
                ->where('type', 'tool')
                ->where('name', $name)
                ->update(['tool_family' => $family]);
        }

        foreach ($tools as $family) {
            $toolIds = DB::table('share_items')
                ->where('type', 'tool')
                ->where('tool_family', $family)
                ->pluck('id');

            if ($toolIds->isEmpty()) {
                continue;
            }

            DB::table('share_items')
                ->where('type', 'resource')
                ->whereIn('gathering_tool_share_item_id', $toolIds)
                ->update(['gathering_tool_family' => $family]);
        }

        Schema::table('share_items', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('gathering_tool_share_item_id');
        });
    }

    public function down(): void
    {
        Schema::table('share_items', function (Blueprint $table): void {
            $table->foreignId('gathering_tool_share_item_id')
                ->nullable()
                ->after('gathering_respawn_seconds')
                ->constrained('share_items')
                ->nullOnDelete();
        });

        Schema::table('share_items', function (Blueprint $table): void {
            $table->dropColumn(['tool_family', 'gathering_speed_bonus_percent', 'gathering_tool_family']);
        });
    }
};
