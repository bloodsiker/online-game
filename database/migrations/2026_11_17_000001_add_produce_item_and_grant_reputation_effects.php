<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Новые эффекты предметов:
 *  - produce_item: выдаёт value шт. target-предмета, сам предмет не расходуется (механизм);
 *  - grant_reputation: начисляет value очков репутации reputation_id за 1 шт.
 *    (Eternum — можно использовать сразу несколько штук).
 */
return new class extends Migration
{
    private const OLD_TYPES = ['heal_hp', 'heal_mp', 'buff_attack', 'buff_armor', 'damage_hp',
        'attack_min', 'attack_max', 'armor', 'bag_slot', 'belt_slot', 'restore_lost_exp', 'respec_stats',
        'rename_name', 'change_race', 'change_avatar', 'grant_diamonds',
        'cure_poison', 'cure_bleed', 'cure_burn', 'cure_paralysis'];

    private const NEW_TYPES = [...self::OLD_TYPES, 'produce_item', 'grant_reputation'];

    public function up(): void
    {
        $this->setEnum(self::NEW_TYPES);

        Schema::table('share_item_effects', function (Blueprint $table): void {
            $table->foreignId('target_share_item_id')->nullable()->after('duration_seconds')
                ->constrained('share_items')->nullOnDelete();
            $table->foreignId('reputation_id')->nullable()->after('target_share_item_id')
                ->constrained('reputations')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('share_item_effects', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('reputation_id');
            $table->dropConstrainedForeignId('target_share_item_id');
        });

        DB::table('share_item_effects')->whereIn('effect_type', ['produce_item', 'grant_reputation'])->delete();
        $this->setEnum(self::OLD_TYPES);
    }

    /** @param  list<string>  $types */
    private function setEnum(array $types): void
    {
        if (Schema::getConnection()->getDriverName() !== 'mysql') {
            return;
        }

        $list = "'".implode("','", $types)."'";
        DB::statement("ALTER TABLE share_item_effects MODIFY effect_type ENUM({$list}) NULL");
    }
};
