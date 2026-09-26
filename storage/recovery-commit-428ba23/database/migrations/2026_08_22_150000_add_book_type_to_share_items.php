<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * ShareItemType::BOOK уже существует в PHP-enum'е (для книг заклинаний,
 * см. MagicSkillBook), но ни одна миграция ранее не добавляла 'book' в MySQL
 * ENUM-колонку share_items.type — без этого MagicBookStarterSeeder не может
 * создать ни одной книги (SQLSTATE[01000]: Data truncated for column 'type').
 * Зеркалит паттерн 2026_07_27_000000_add_misc_type_to_share_items.php.
 */
return new class extends Migration
{
    private const OLD_TYPES = ['resource', 'weapon', 'shield', 'armor', 'belt', 'bag', 'potion', 'eat', 'key',
        'quest', 'artifact', 'recipe', 'chest', 'scroll', 'stone', 'gem', 'mount', 'rune', 'rune_key', 'misc'];

    private const NEW_TYPES = ['resource', 'weapon', 'shield', 'armor', 'belt', 'bag', 'potion', 'eat', 'key',
        'quest', 'artifact', 'recipe', 'chest', 'scroll', 'stone', 'gem', 'mount', 'rune', 'rune_key', 'misc', 'book'];

    public function up(): void
    {
        if (Schema::getConnection()->getDriverName() === 'mysql') {
            $list = "'".implode("','", self::NEW_TYPES)."'";
            DB::statement("ALTER TABLE share_items MODIFY type ENUM({$list}) DEFAULT 'resource'");
        }
    }

    public function down(): void
    {
        DB::table('share_items')->where('type', 'book')->delete();

        if (Schema::getConnection()->getDriverName() === 'mysql') {
            $list = "'".implode("','", self::OLD_TYPES)."'";
            DB::statement("ALTER TABLE share_items MODIFY type ENUM({$list}) DEFAULT 'resource'");
        }
    }
};
