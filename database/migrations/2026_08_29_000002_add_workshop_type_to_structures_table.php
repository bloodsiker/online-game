<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const OLD_TYPES = [
        'shop', 'auction', 'heal', 'warehouse', 'clan_warehouse', 'bank',
        'clan_bank', 'blacksmith', 'exchange', 'auction_exchange', 'clan_skill_hall',
        'reputation_exchange', 'barter_shop',
    ];

    private const NEW_TYPES = [...self::OLD_TYPES, 'workshop'];

    public function up(): void
    {
        $this->changeEnum(self::NEW_TYPES);
    }

    public function down(): void
    {
        if (Schema::getConnection()->getDriverName() === 'mysql') {
            DB::table('structures')->where('type', 'workshop')->update(['type' => 'shop']);
        }

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
