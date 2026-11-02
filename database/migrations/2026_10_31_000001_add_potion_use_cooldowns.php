<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Зелья лечения/маны не имели никакого ограничения на использование в бою:
 * ItemController::useItem() — отдельный эндпоинт, не завязанный на раунд
 * боя (не проходит через FightOrchestrator/AttackService), а
 * ItemUsagePolicyService::reserveUse() просто не находил запись в
 * share_item_use_limits ни для одного зелья (во всей таблице была только
 * 1 строка — «Кровавый эликсир Братства», 2 раза/24ч). Это позволяло
 * бесплатно спамить, например, «Героическое зелье лечения» (+70% HP) без
 * всякого отката, что обесценивало сложность любого тяжёлого боя — не
 * только нового босса «Кровавый Хранитель Склепа», но и уже существующих
 * (Азазель и т.п.).
 *
 * Добавляет реальный кулдаун (через уже готовый механизм max_uses=1 за
 * period_seconds) по 6 ступеням редкости, чем выше качество — тем больше
 * кулдаун. Применяется к обеим линейкам (HP и MP), т.к. они зеркальны и
 * подвержены той же проблеме.
 */
return new class extends Migration
{
    private const COOLDOWN_BY_RARITY = [
        'common' => 15,
        'uncommon' => 25,
        'rare' => 40,
        'epic' => 60,
        'legendary' => 90,
        'heroic' => 120,
    ];

    /** @var list<int> Зелья лечения (heal_hp) + зелья/эликсиры маны (heal_mp). */
    private const POTION_SHARE_ITEM_IDS = [729, 757, 759, 761, 763, 17, 765, 767, 769, 771, 774, 18];

    public function up(): void
    {
        $now = now();

        $items = DB::table('share_items')
            ->whereIn('id', self::POTION_SHARE_ITEM_IDS)
            ->get(['id', 'rarity']);

        foreach ($items as $item) {
            $periodSeconds = self::COOLDOWN_BY_RARITY[$item->rarity] ?? null;
            if ($periodSeconds === null) {
                continue;
            }

            $exists = DB::table('share_item_use_limits')->where('share_item_id', $item->id)->exists();
            if ($exists) {
                continue;
            }

            DB::table('share_item_use_limits')->insert([
                'share_item_id' => $item->id,
                'max_uses' => 1,
                'period_seconds' => $periodSeconds,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }

    public function down(): void
    {
        DB::table('share_item_use_limits')->whereIn('share_item_id', self::POTION_SHARE_ITEM_IDS)->delete();
    }
};
