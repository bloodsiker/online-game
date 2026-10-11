<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Квестовая цепочка НПС «Дозорный Ольгерт» (id77, локация 1183) на выдачу
 * «Печати Дозорного Ольгерта» (share_item id1798) — уже существующего
 * предмета, уже привязанного к воротам location_gates id17/18 (1183→1232,
 * открывает полосу 3 Сторожевых Холмов), но до этой правки его выдавал не
 * Ольгерт, а квест 216 «Голос гранита» (финал совсем другой, Гранитного
 * Перевала, сюжетной связи с Ольгертом не было) — см. миграцию ниже.
 *
 * Продолжает квест 210 «Пробуждённые стражи: ветер», где Ольгерт обещает
 * шестерых стражей — земля/ветер усмирены квестами 209/210, сталь/пепел
 * реально расставлены в полосе 2 (1165-1231, та же полоса, где сидит
 * Ольгерт) — тень/туман расставлены в полосе 3, за воротами, физически
 * недостижимы до получения печати (тот же паттерн, что и у Гаральда).
 *
 * Идёт ПОСЛЕ цепочки Гаральда (223-225, см. HaraldWatchKeyQuestSeeder) —
 * печать логична только после того, как открыта полоса 2 его ключом.
 *
 * 3 шага (по прямому указанию пользователя):
 *   1. «Эхо прежней тревоги: сталь» — убить 150 Стальной Страж (id235,236).
 *   2. «Эхо прежней тревоги: пепел» — убить 150 Пепельный Страж (id237,238),
 *      после первого.
 *   3. «Весть для Гаральда» — поговорить с Гаральдом Бессменным (id78) о
 *      усмирённых стражах; завершается У Гаральда (как квест 211→212).
 *   4. «Печать для гряды» — Гаральд отправляет обратно к Ольгерту;
 *      завершается у Ольгерта — тот выдаёт печать в диалоге завершения.
 */
class OlgertWatchSealQuestSeeder extends Seeder
{
    private const OLGERT_NPC_ID = 77;

    private const HARALD_NPC_ID = 78;

    private const SEAL_SHARE_ITEM_ID = 1798;

    private const STEEL_WARD_IDS = [235, 236];

    private const ASH_WARD_IDS = [237, 238];

    public function run(): void
    {
        $this->detachStraySealReward();

        $questAId = $this->createQuest(
            title: 'Эхо прежней тревоги: сталь',
            description: 'Ты прошёл дальше, чем я смел надеяться — а я всё ещё сижу у этого завала и считаю дни. Гаральд говорил о шестерых стражах: землю и ветер мы усмирили вместе, помнишь? Но дальше по гряде зашевелились ещё двое — стальной да пепельный. Пока они не замолчат, я не опущу руки от этих камней ни на шаг.<br>Разберись со стальным стражем. Полторы сотни — и я наконец выдохну.',
            startNpcId: self::OLGERT_NPC_ID,
            completeNpcId: self::OLGERT_NPC_ID,
            afterQuestId: 225,
        );
        $this->createKillObjective(
            questId: $questAId,
            targetIds: self::STEEL_WARD_IDS,
            requiredAmount: 150,
            description: 'Убейте 150 стражей — Стальной Страж, любого из двух уровней.',
        );
        $this->createRewards($questAId, exp: 2200, money: 900);

        $questBId = $this->createQuest(
            title: 'Эхо прежней тревоги: пепел',
            description: 'Сталь утихла — и впервые за три дня я сплю, не прислушиваясь к гряде. Остался пепельный — жжёт не хуже открытого огня. Уйми и его — и, может, я наконец отойду от этого завала сам.',
            startNpcId: self::OLGERT_NPC_ID,
            completeNpcId: self::OLGERT_NPC_ID,
            afterQuestId: $questAId,
        );
        $this->createKillObjective(
            questId: $questBId,
            targetIds: self::ASH_WARD_IDS,
            requiredAmount: 150,
            description: 'Убейте 150 стражей — Пепельный Страж, любого из двух уровней.',
        );
        $this->createRewards($questBId, exp: 2200, money: 900);

        $questCId = $this->createQuest(
            title: 'Весть для Гаральда',
            description: 'Сталь и пепел молчат — твоими стараниями, не моими. Но прежде чем я решусь снять печать с этих ворот, я должен услышать от Гаральда, что дальше по гряде не повторится то же самое. Он дольше меня здесь стоял — ему я поверю. Передай ему, что стражи усмирены, и узнай, что он скажет.',
            startNpcId: self::OLGERT_NPC_ID,
            completeNpcId: self::HARALD_NPC_ID,
            afterQuestId: $questBId,
        );
        $this->createTalkObjective(
            questId: $questCId,
            targetNpcId: self::HARALD_NPC_ID,
            description: 'Расскажите Гаральду Бессменному, что стальной и пепельный стражи усмирены.',
        );
        $this->createRewards($questCId, exp: 2000, money: 850);

        $questDId = $this->createQuest(
            title: 'Печать для гряды',
            description: 'Сталь и пепел молчат — что ж, ты не солгал. Я не вижу в этом повторения прежнего, но пост на завале — его, не мой, и решать там ему. Возвращайся к Ольгерту и передай: я не держу его больше за трусость.',
            startNpcId: self::HARALD_NPC_ID,
            completeNpcId: self::OLGERT_NPC_ID,
            afterQuestId: $questCId,
        );
        $this->createTalkObjective(
            questId: $questDId,
            targetNpcId: self::OLGERT_NPC_ID,
            description: 'Вернитесь к Дозорному Ольгерту и передайте слова Гаральда.',
        );
        $this->createRewards($questDId, exp: 3600, money: 1800, keyShareItemId: self::SEAL_SHARE_ITEM_ID);

        $this->command?->info("OlgertWatchSealQuestSeeder: создано квестов — 4 (id {$questAId}, {$questBId}, {$questCId}, {$questDId})");
    }

    /**
     * Квест 216 «Голос гранита» (финал Гранитного Перевала) ошибочно выдавал
     * печать Ольгерта — сюжетной связи нет, убираем оттуда.
     */
    private function detachStraySealReward(): void
    {
        $deleted = DB::table('quest_rewards')
            ->where('quest_id', 216)
            ->where('share_item_id', self::SEAL_SHARE_ITEM_ID)
            ->delete();

        if ($deleted > 0) {
            $this->command?->info("убрана ошибочная награда печати из квеста 216 ({$deleted} строка)");
        }
    }

    private function createQuest(string $title, string $description, int $startNpcId, int $completeNpcId, int $afterQuestId): int
    {
        $existing = DB::table('quests')->where('title', $title)->first();
        if ($existing !== null) {
            return $existing->id;
        }

        return DB::table('quests')->insertGetId([
            'title' => $title,
            'description' => $description,
            'type' => 'main',
            'start_npc_id' => $startNpcId,
            'complete_npc_id' => $completeNpcId,
            'after_quest_id' => $afterQuestId,
            'is_active' => 1,
            'is_finish' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /** @param  array<int, int>  $targetIds */
    private function createKillObjective(int $questId, array $targetIds, int $requiredAmount, string $description): void
    {
        if (DB::table('quest_objectives')->where('quest_id', $questId)->exists()) {
            return;
        }

        DB::table('quest_objectives')->insert([
            'quest_id' => $questId,
            'type' => 'kill',
            'target_type' => 'monster',
            'target_id' => $targetIds[0],
            'target_ids' => json_encode($targetIds),
            'consume_item' => 1,
            'required_amount' => $requiredAmount,
            'description' => $description,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function createTalkObjective(int $questId, int $targetNpcId, string $description): void
    {
        if (DB::table('quest_objectives')->where('quest_id', $questId)->exists()) {
            return;
        }

        DB::table('quest_objectives')->insert([
            'quest_id' => $questId,
            'type' => 'talk',
            'target_type' => 'npc',
            'target_id' => $targetNpcId,
            'consume_item' => 1,
            'required_amount' => 1,
            'description' => $description,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function createRewards(int $questId, int $exp, int $money, ?int $keyShareItemId = null): void
    {
        if (DB::table('quest_rewards')->where('quest_id', $questId)->exists()) {
            return;
        }

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
