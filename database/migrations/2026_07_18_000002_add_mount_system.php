<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const OLD_TYPES = ['resource', 'weapon', 'shield', 'armor', 'belt', 'bag', 'potion', 'eat', 'key',
        'quest', 'artifact', 'recipe', 'chest', 'scroll', 'stone', 'gem', 'socket_kit', 'rune', 'rune_key'];

    private const NEW_TYPES = ['resource', 'weapon', 'shield', 'armor', 'belt', 'bag', 'potion', 'eat', 'key',
        'quest', 'artifact', 'recipe', 'chest', 'scroll', 'stone', 'gem', 'mount', 'rune', 'rune_key'];

    public function up(): void
    {
        // «Оправа» полностью заменяет старый «Набор для сокета» — вычищаем
        // прежние экземпляры до смены enum, иначе они останутся с недопустимым type.
        $socketKitIds = DB::table('share_items')->where('type', 'socket_kit')->pluck('id');
        if ($socketKitIds->isNotEmpty()) {
            $itemIds = DB::table('items')->whereIn('share_item_id', $socketKitIds)->pluck('id');
            DB::table('backpacks')->whereIn('item_id', $itemIds)->delete();
            DB::table('items')->whereIn('id', $itemIds)->delete();
            DB::table('share_items')->whereIn('id', $socketKitIds)->delete();
        }

        if (Schema::getConnection()->getDriverName() === 'mysql') {
            $list = "'".implode("','", self::NEW_TYPES)."'";
            DB::statement("ALTER TABLE share_items MODIFY type ENUM({$list}) DEFAULT 'resource'");
        }

        // Редкость оправы хранится в уже существующей share_items.rarity —
        // отдельная колонка не нужна, диапазон сокетов/цена завязаны на неё
        // через MountRarityConfig (common/uncommon/rare/epic).
    }

    public function down(): void
    {
        DB::table('share_items')->where('type', 'mount')->delete();

        if (Schema::getConnection()->getDriverName() === 'mysql') {
            $list = "'".implode("','", self::OLD_TYPES)."'";
            DB::statement("ALTER TABLE share_items MODIFY type ENUM({$list}) DEFAULT 'resource'");
        }
    }
};
