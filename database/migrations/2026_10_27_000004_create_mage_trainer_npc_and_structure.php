<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

/**
 * НПС «Архимаг Орвендил» у Башни магов (локация 43) + структура
 * mage_trainer, через которую (а не просто «в этой локации») теперь
 * проверяется право изучать книги — см. правку LearnMagicSkillFromBook
 * (TYPE_MAGE_TOWER → TYPE_MAGE_TRAINER). Магазин (mage_tower, см.
 * 2026_10_27_000003) торгует книгами независимо, без этого гейта.
 */
return new class extends Migration
{
    private const TYPES = [
        'shop', 'auction', 'heal', 'warehouse', 'clan_warehouse', 'bank', 'clan_bank',
        'blacksmith', 'exchange', 'auction_exchange', 'clan_skill_hall', 'reputation_exchange',
        'barter_shop', 'workshop', 'tool_workshop', 'clan_boss', 'prison', 'mage_tower', 'mage_trainer',
    ];

    public function up(): void
    {
        if (Schema::getConnection()->getDriverName() === 'mysql') {
            $types = "'".implode("','", self::TYPES)."'";
            DB::statement("ALTER TABLE structures MODIFY type ENUM({$types}) NULL DEFAULT 'shop'");
        }

        $now = now();

        $npcId = DB::table('npcs')->where('name->ru', 'Архимаг Орвендил')->value('id');
        if ($npcId === null) {
            $npcId = DB::table('npcs')->insertGetId([
                'uuid' => (string) Str::uuid(),
                'location_id' => 43,
                'is_active' => 1,
                'description_legacy' => 'Старый архимаг, что присматривает за Башней магов.',
                'name' => json_encode(['ru' => 'Архимаг Орвендил', 'uk' => 'Архімаг Орвенділ'], JSON_UNESCAPED_UNICODE),
                'description' => json_encode([
                    'ru' => 'Старый архимаг, что присматривает за Башней магов — учит тех, кто принёс гримуар и достаточно созрел для его силы.',
                    'uk' => 'Старий архімаг, що приглядає за Башнею магів — навчає тих, хто приніс гримуар і достатньо визрів для його сили.',
                ], JSON_UNESCAPED_UNICODE),
                'hide_location' => 0,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        $structureId = DB::table('structures')->where('type', 'mage_trainer')->where('location_id', 43)->value('id');
        if ($structureId === null) {
            DB::table('structures')->insert([
                'type' => 'mage_trainer',
                'npc_id' => $npcId,
                'location_id' => 43,
                'name' => json_encode(['ru' => 'Наставник магии', 'uk' => 'Наставник магії'], JSON_UNESCAPED_UNICODE),
                'description' => json_encode([
                    'ru' => 'Архимаг Орвендил изучит с вами заклинание из гримуара, если вы готовы к его силе.',
                    'uk' => 'Архімаг Орвенділ вивчить з вами заклинання з гримуару, якщо ви готові до його сили.',
                ], JSON_UNESCAPED_UNICODE),
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }

    public function down(): void
    {
        DB::table('structures')->where('type', 'mage_trainer')->where('location_id', 43)->delete();
        DB::table('npcs')->where('name->ru', 'Архимаг Орвендил')->delete();

        if (Schema::getConnection()->getDriverName() === 'mysql') {
            $types = "'shop','auction','heal','warehouse','clan_warehouse','bank','clan_bank','blacksmith','exchange','auction_exchange','clan_skill_hall','reputation_exchange','barter_shop','workshop','tool_workshop','clan_boss','prison','mage_tower'";
            DB::statement("ALTER TABLE structures MODIFY type ENUM({$types}) NULL DEFAULT 'shop'");
        }
    }
};
