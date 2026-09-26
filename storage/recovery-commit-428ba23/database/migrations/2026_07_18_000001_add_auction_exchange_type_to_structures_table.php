<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const OLD_TYPES = ['shop', 'auction', 'heal', 'warehouse', 'clan_warehouse', 'bank', 'clan_bank', 'blacksmith', 'exchange'];

    private const NEW_TYPES = [...self::OLD_TYPES, 'auction_exchange'];

    public function up(): void
    {
        if (Schema::getConnection()->getDriverName() !== 'mysql') {
            return;
        }

        $list = "'".implode("','", self::NEW_TYPES)."'";
        DB::statement("ALTER TABLE structures MODIFY type ENUM({$list}) DEFAULT 'shop'");
    }

    public function down(): void
    {
        if (Schema::getConnection()->getDriverName() !== 'mysql') {
            return;
        }

        $list = "'".implode("','", self::OLD_TYPES)."'";
        DB::statement("ALTER TABLE structures MODIFY type ENUM({$list}) DEFAULT 'shop'");
    }
};
