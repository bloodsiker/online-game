<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Modules\Share\Infrastructure\Localization\LocalizedContentSeederQuery;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Квестовая цепочка НПС «Гаральд Бессменный» (id78, локация 1164) на выдачу
 * «Ключа Снятого Караула» (share_item id1797) — уже существующего предмета,
 * уже привязанного к воротам location_gates id15 (1164→1165), но до этой
 * правки ни один квест его не выдавал.
 *
 * Продолжает сюжет квестов 208-212 (квест 210 «Пробуждённые стражи: ветер»
 * обещает устами Ольгерта шестерых стражей — земля/ветер уже усмирены
 * квестами 209/210, реально из шести отсюда пока доступны для убийства
 * только «Костяной Часовой» и «Дух Павшего Знаменосца» — остальные четыре
 * («Стальной»/«Пепельный»/«Теневой»/«Туманный») расставлены в полосах 2 и 3
 * ЗА этими самыми воротами, физически недостижимы до получения ключа, — это
 * и определило состав цепочки).
 *
 * 3 шага (по прямому указанию пользователя: раздельные, последовательные
 * kill-квесты по 100 монстров одного вида, не одновременно):
 *   1. «Последний обход: Часовой» — убить 100 Костяной Часовой (id243,244).
 *   2. «Последний обход: Знаменосец» — убить 100 Дух Павшего Знаменосца
 *      (id241,242), после первого.
 *   3. «Клочья Несменного Знамени» — собрать 12× новый предмет «Обрывок
 *      Знамени Несменной Стражи» (дроп с Дух Павшего Знаменосца), после
 *      второго. Награда за завершение — Ключ Снятого Караула (id1797).
 */
class HaraldWatchKeyQuestSeeder extends Seeder
{
    private const HARALD_NPC_ID = 78;

    private const KEY_SHARE_ITEM_ID = 1797;

    private const BANNER_ITEM_NAME = 'Обрывок Несменного Знамени';

    private const BONE_SENTINEL_IDS = [243, 244];

    private const FALLEN_STANDARD_BEARER_IDS = [241, 242];

    public function run(): void
    {
        $bannerItemId = $this->createBannerItem();
        $this->attachBannerDrop($bannerItemId);

        $quest1Id = $this->createQuest(
            title: 'Последний обход: Часовой',
            description: 'Ты рассказал Эйдрану, и это правильно — но мне легче не станет от одних слов. Земля и ветер уже унялись твоей рукой, Ольгерт говорил мне. Но их было шестеро, а угомонились двое. Начнём с тех, кто ближе, — Костяной Часовой всё ещё несёт службу, которую я так и не отменил. Пора заканчивать его обход.<br>Уложи сотню — и посмотрим, ясна ли уже картина.',
            afterQuestId: 212,
        );
        $this->createObjective(
            questId: $quest1Id,
            type: 'kill',
            targetId: self::BONE_SENTINEL_IDS[0],
            targetIds: self::BONE_SENTINEL_IDS,
            requiredAmount: 100,
            description: 'Убейте 100 стражей — Костяной Часовой, любого из двух уровней.',
        );
        $this->createRewards($quest1Id, exp: 2200, money: 900);

        $quest2Id = $this->createQuest(
            title: 'Последний обход: Знаменосец',
            description: 'Часовой унялся — теперь Дух Павшего Знаменосца. Тот, что всё ждёт смены, которая не придёт. Не прогони его снова — упокой как следует. Ещё сотню, воин.',
            afterQuestId: $quest1Id,
        );
        $this->createObjective(
            questId: $quest2Id,
            type: 'kill',
            targetId: self::FALLEN_STANDARD_BEARER_IDS[0],
            targetIds: self::FALLEN_STANDARD_BEARER_IDS,
            requiredAmount: 100,
            description: 'Убейте 100 стражей — Дух Павшего Знаменосца, любого из двух уровней.',
        );
        $this->createRewards($quest2Id, exp: 2200, money: 900);

        $quest3Id = $this->createQuest(
            title: 'Клочья Несменного Знамени',
            description: 'Пока ты разбирался со знаменосцем, кое-что попалось мне на глаза среди твоих трофеев — клочья ткани, слишком ветхой, чтобы быть случайной находкой. Я узнал их сразу, хоть и не видел десятилетиями. Это был стяг моего знаменосца — того, что полёг здесь же, прикрывая отход, когда стражи впервые проснулись при моей памяти. Мы его толком не похоронили. Не было времени, а после — не нашлось смелости вернуться.<br>Собери, сколько сумеешь найти. Духи не отдают такое просто так — придётся выбивать.',
            afterQuestId: $quest2Id,
        );
        $this->createObjective(
            questId: $quest3Id,
            type: 'collect',
            targetId: null,
            targetIds: null,
            requiredAmount: 12,
            description: 'Соберите 12 «Обрывков Несменного Знамени» — их роняет Дух Павшего Знаменосца.',
            shareItemId: $bannerItemId,
            dropChance: 9,
        );
        $this->createRewards($quest3Id, exp: 3200, money: 1500, keyShareItemId: self::KEY_SHARE_ITEM_ID);

        $this->command?->info("HaraldWatchKeyQuestSeeder: создано квестов — 3 (id {$quest1Id}, {$quest2Id}, {$quest3Id}), предмет-баннер id={$bannerItemId}");
    }

    private function createBannerItem(): int
    {
        $existing = LocalizedItemSeederQuery::items()->where('name', self::BANNER_ITEM_NAME)->first();
        if ($existing !== null) {
            return $existing->id;
        }

        return LocalizedItemSeederQuery::items()->insertGetId([
            'type' => 'quest',
            'name' => self::BANNER_ITEM_NAME,
            'description' => 'Клочок ткани, истлевшей почти до нитей — но на свету ещё различим узор старой заставы. Достаточно, чтобы узнать, чьё это знамя, и слишком мало, чтобы сшить его заново.',
            'image' => '',
            'is_two_hand' => 0,
            'count_use' => 0,
            'is_active' => 1,
            'is_sell' => 0,
            'is_auction_sellable' => 0,
            'is_give' => 0,
            'is_clan_warehouse_allowed' => 0,
            'is_slot_usable' => 0,
            'is_droppable' => 1,
            'is_stackable' => 1,
            'is_use' => 1,
            'is_weight' => 0,
            'price' => 0,
            'break_crystal' => 0,
            'gathering_speed_bonus_percent' => 0,
            'gathering_double_chance_percent' => 0,
            'rarity' => 'uncommon',
            'upgrade_gold_cost' => 0,
            'drop_direct_to_backpack' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function attachBannerDrop(int $bannerItemId): void
    {
        foreach (self::FALLEN_STANDARD_BEARER_IDS as $monsterId) {
            $exists = DB::table('monster_has_items')
                ->where('monster_id', $monsterId)
                ->where('share_item_id', $bannerItemId)
                ->exists();
            if ($exists) {
                continue;
            }
            DB::table('monster_has_items')->insert([
                'monster_id' => $monsterId,
                'share_item_id' => $bannerItemId,
                'drop_chance' => 9,
                'min_count' => 1,
                'max_count' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    private function createQuest(string $title, string $description, int $afterQuestId): int
    {
        $quests = LocalizedContentSeederQuery::forTable('quests');
        $existing = (clone $quests)->where('title', $title)->first();
        if ($existing !== null) {
            return $existing->id;
        }

        return $quests->insertGetId([
            'title' => $title,
            'description' => $description,
            'type' => 'main',
            'start_npc_id' => self::HARALD_NPC_ID,
            'complete_npc_id' => self::HARALD_NPC_ID,
            'after_quest_id' => $afterQuestId,
            'is_active' => 1,
            'is_finish' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /** @param  array<int, int>|null  $targetIds */
    private function createObjective(
        int $questId,
        string $type,
        ?int $targetId,
        ?array $targetIds,
        int $requiredAmount,
        string $description,
        ?int $shareItemId = null,
        ?int $dropChance = null,
    ): void {
        DB::table('quest_objectives')->insert([
            'quest_id' => $questId,
            'type' => $type,
            'target_type' => $type === 'kill' ? 'monster' : 'item',
            'target_id' => $targetId,
            'target_ids' => $targetIds !== null ? json_encode($targetIds) : null,
            'share_item_id' => $shareItemId,
            'consume_item' => 1,
            'required_amount' => $requiredAmount,
            'drop_chance' => $dropChance,
            'description' => $description,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function createRewards(int $questId, int $exp, int $money, ?int $keyShareItemId = null): void
    {
        // Раздельные insert() — batch-insert требует одинакового набора колонок
        // у всех строк, а награда-предмет добавляет share_item_id, которого
        // нет у exp/money.
        DB::table('quest_rewards')->insert([
            'quest_id' => $questId,
            'type' => 'exp',
            'amount' => $exp,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        DB::table('quest_rewards')->insert([
            'quest_id' => $questId,
            'type' => 'money',
            'amount' => $money,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        if ($keyShareItemId !== null) {
            DB::table('quest_rewards')->insert([
                'quest_id' => $questId,
                'type' => 'item',
                'amount' => 1,
                'share_item_id' => $keyShareItemId,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
