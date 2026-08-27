<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const OLD_TYPES = [
        'shop', 'auction', 'heal', 'warehouse', 'clan_warehouse', 'bank',
        'clan_bank', 'blacksmith', 'exchange', 'auction_exchange',
    ];

    private const NEW_TYPES = [...self::OLD_TYPES, 'clan_skill_hall'];

    public function up(): void
    {
        $this->changeEnum(self::NEW_TYPES);
    }

    public function down(): void
    {
        $this->changeEnum(self::OLD_TYPES);
    }

    private function changeEnum(array $types): void
    {
        if (Schema::getConnection()->getDriverName() !== 'mysql') {
            return;
        }

        $list = "'".implode("','", $types)."'";
        DB::statement("ALTER TABLE structures MODIFY type ENUM({$list}) DEFAULT 'shop'");
    }
};
