<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const OLD_TYPES = ['resource', 'weapon', 'shield', 'armor', 'belt', 'bag', 'potion', 'eat', 'key',
        'quest', 'artifact', 'recipe', 'chest', 'scroll', 'stone', 'gem', 'mount', 'rune', 'rune_key'];

    private const NEW_TYPES = ['resource', 'weapon', 'shield', 'armor', 'belt', 'bag', 'potion', 'eat', 'key',
        'quest', 'artifact', 'recipe', 'chest', 'scroll', 'stone', 'gem', 'mount', 'rune', 'rune_key', 'misc'];

    public function up(): void
    {
        if (Schema::getConnection()->getDriverName() === 'mysql') {
            $list = "'".implode("','", self::NEW_TYPES)."'";
            DB::statement("ALTER TABLE share_items MODIFY type ENUM({$list}) DEFAULT 'resource'");
        }
    }

    public function down(): void
    {
        DB::table('share_items')->where('type', 'misc')->delete();

        if (Schema::getConnection()->getDriverName() === 'mysql') {
            $list = "'".implode("','", self::OLD_TYPES)."'";
            DB::statement("ALTER TABLE share_items MODIFY type ENUM({$list}) DEFAULT 'resource'");
        }
    }
};
