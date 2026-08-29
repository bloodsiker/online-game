<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const OLD_TYPES = ['resource', 'weapon', 'shield', 'armor', 'belt', 'bag', 'potion', 'eat', 'key',
        'quest', 'artifact', 'recipe', 'chest', 'scroll', 'stone', 'gem', 'mount', 'rune', 'rune_key', 'misc', 'book'];

    private const NEW_TYPES = [...self::OLD_TYPES, 'tool'];

    public function up(): void
    {
        if (Schema::getConnection()->getDriverName() === 'mysql') {
            $list = "'".implode("','", self::NEW_TYPES)."'";
            DB::statement("ALTER TABLE share_items MODIFY type ENUM({$list}) DEFAULT 'resource'");
        }

        $toolIds = DB::table('share_items')
            ->whereNotNull('gathering_tool_share_item_id')
            ->pluck('gathering_tool_share_item_id')
            ->unique()
            ->values();

        if ($toolIds->isNotEmpty()) {
            DB::table('share_items')->whereIn('id', $toolIds)->update([
                'type' => 'tool',
                'slot' => 'hand',
                'is_stackable' => false,
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        DB::table('share_items')->where('type', 'tool')->update([
            'type' => 'weapon',
            'updated_at' => now(),
        ]);

        if (Schema::getConnection()->getDriverName() === 'mysql') {
            $list = "'".implode("','", self::OLD_TYPES)."'";
            DB::statement("ALTER TABLE share_items MODIFY type ENUM({$list}) DEFAULT 'resource'");
        }
    }
};
