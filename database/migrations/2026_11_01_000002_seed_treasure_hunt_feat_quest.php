<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Квест-подвиг верхней ступени репутации «Кладоискатель» (id=35, тир
 * id=50, порог 3000+) — по образцу «Искателей реликтов» (quest id=136,
 * tier id=19): без quest_objectives/quest_stages (сам паттерн работает
 * без них — QuestController::complete() у квеста без объективов считает
 * их выполненными на пустой коллекции, реальный гейт — очки репутации,
 * уже проверенные в ReputationService::getAvailableFeatQuest()). Условие
 * в описании чисто нарративное, как и у «Ордена» ("в одиночку упокоил
 * Лича Некроманта", quest id=116) — не инвентарная проверка.
 *
 * NPC — Ренвар (id=85), тот же, что ведёт саму репутацию/лавку.
 */
return new class extends Migration
{
    public function up(): void
    {
        $now = now();
        $title = ['ru' => '[Кладоискатель] Квест поклонения', 'uk' => '[Шукач скарбів] Квест поклоніння'];
        $description = [
            'ru' => 'Ренвар готов признать вас достойным звания Хранителя древностей. Принесите ему легендарную находку — доказательство того, что для вас не осталось непройденных троп.',
            'uk' => 'Ренвар готовий визнати вас гідним звання Хранителя старожитностей. Принесіть йому легендарну знахідку — доказ того, що для вас не лишилося непройдених стежок.',
        ];

        $questId = DB::table('quests')->insertGetId([
            'type' => 'reputation', 'start_npc_id' => 85, 'complete_npc_id' => 85,
            'parent_quest_id' => null, 'after_quest_id' => null, 'reset_period' => null,
            'is_active' => 1, 'is_finish' => 0,
            'title_legacy' => $title['ru'], 'description_legacy' => $description['ru'],
            'title' => json_encode($title, JSON_UNESCAPED_UNICODE),
            'description' => json_encode($description, JSON_UNESCAPED_UNICODE),
            'created_at' => $now, 'updated_at' => $now,
        ]);

        foreach ([
            ['type' => 'reputation_points', 'amount' => 200, 'reputation_id' => 35],
            ['type' => 'exp', 'amount' => 2000],
            ['type' => 'money', 'amount' => 5000],
        ] as $reward) {
            DB::table('quest_rewards')->insert(array_merge([
                'quest_id' => $questId, 'share_item_id' => null, 'location_id' => null, 'reputation_id' => null,
                'created_at' => $now, 'updated_at' => $now,
            ], $reward));
        }

        DB::table('reputation_tiers')->where('id', 50)->update([
            'feat_quest_id' => $questId,
            'feat_description' => json_encode([
                'ru' => 'Принесите Ренвару легендарную находку — доказательство того, что для вас не осталось непройденных троп.',
                'uk' => 'Принесіть Ренвару легендарну знахідку — доказ того, що для вас не лишилося непройдених стежок.',
            ], JSON_UNESCAPED_UNICODE),
            'feat_description_legacy' => $description['ru'],
            'updated_at' => $now,
        ]);
    }

    public function down(): void
    {
        $questId = DB::table('reputation_tiers')->where('id', 50)->value('feat_quest_id');
        if ($questId === null) {
            return;
        }
        DB::table('reputation_tiers')->where('id', 50)->update([
            'feat_quest_id' => null, 'feat_description' => null, 'feat_description_legacy' => null,
        ]);
        DB::table('quest_rewards')->where('quest_id', $questId)->delete();
        DB::table('quests')->where('id', $questId)->delete();
    }
};
