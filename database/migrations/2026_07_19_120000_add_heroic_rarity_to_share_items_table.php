<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const OLD_RARITIES = ['common', 'uncommon', 'rare', 'epic', 'legendary'];

    private const NEW_RARITIES = [...self::OLD_RARITIES, 'heroic'];

    public function up(): void
    {
        if (Schema::getConnection()->getDriverName() !== 'mysql') {
            return;
        }

        $list = "'".implode("','", self::NEW_RARITIES)."'";
        DB::statement("ALTER TABLE share_items MODIFY rarity ENUM({$list}) DEFAULT 'common'");
    }

    public function down(): void
    {
        if (Schema::getConnection()->getDriverName() !== 'mysql') {
            return;
        }

        DB::table('share_items')->where('rarity', 'heroic')->update(['rarity' => 'legendary']);

        $list = "'".implode("','", self::OLD_RARITIES)."'";
        DB::statement("ALTER TABLE share_items MODIFY rarity ENUM({$list}) DEFAULT 'common'");
    }
};
