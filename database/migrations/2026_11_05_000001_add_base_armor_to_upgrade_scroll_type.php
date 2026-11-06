<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * UpgradeScrollType::BASE_ARMOR — отдельный свиток заточки брони и щитов.
 * 'base' остаётся свитком заточки оружия.
 */
return new class extends Migration
{
    private const OLD_TYPES = ['base', 'protection', 'stabilizer', 'lucky'];

    private const NEW_TYPES = [...self::OLD_TYPES, 'base_armor'];

    public function up(): void
    {
        if (Schema::getConnection()->getDriverName() !== 'mysql') {
            return;
        }

        $list = "'".implode("','", self::NEW_TYPES)."'";
        DB::statement("ALTER TABLE share_items MODIFY upgrade_scroll_type ENUM({$list}) NULL");
    }

    public function down(): void
    {
        if (Schema::getConnection()->getDriverName() !== 'mysql') {
            return;
        }

        DB::table('share_items')->where('upgrade_scroll_type', 'base_armor')->update(['upgrade_scroll_type' => null]);

        $list = "'".implode("','", self::OLD_TYPES)."'";
        DB::statement("ALTER TABLE share_items MODIFY upgrade_scroll_type ENUM({$list}) NULL");
    }
};
