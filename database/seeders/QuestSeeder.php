<?php

namespace Database\Seeders;

use App\Modules\Npc\Infrastructure\Persistence\Models\Npc;
use App\Modules\Location\Infrastructure\Persistence\Models\Location;
use App\Modules\Quest\Infrastructure\Persistence\Models\Quest;
use App\Modules\Quest\Infrastructure\Persistence\Models\QuestObjective;
use App\Modules\Quest\Infrastructure\Persistence\Models\QuestReward;
use App\Modules\Quest\Infrastructure\Persistence\Models\QuestStage;
use Illuminate\Database\Seeder;

/**
 * Quest chain for NPC "Глава города" (id=1, location_id=3):
 *
 * 1. "Познание мира" (one_time) — already exists in DB (id=1), skip if exists
 *    Objective: Kill 5 mice (monster_id=1)
 *    Reward: 200 exp
 *
 * 2. "Первая вылазка" (one_time, after=1)
 *    Objective: Kill 10 bats on map 2 (monster_id=2, map_id=2)
 *    Reward: 500 exp + 50 money
 *
 * 3. "Испытание воли" (one_time, after=2)
 *    Multi-objective: Kill 5 mice + Kill 5 bats on map 2
 *    Reward: exp + item (Коготь медведя, share_item_id=1)
 *
 * 4. "Путь к катакомбам" (one_time, after=3)
 *    Multi-objective: Kill 3 dragons (monster_id=3) + deliver share_item_id=23 (Кристалл)
 *    Complete at: Вестник (NPC 2, at location 4)
 *    Reward: exp + location_access to locked location 102 (Тропа Заблудших, map 2)
 *
 * 5. "Дозор у ворот" (repeatable, reset_period=86400, after=1)
 *    Objective: Kill 20 mice (monster_id=1)
 *    Reward: 300 exp + 100 money
 *
 * 6. "Три испытания" (one_time, after=3) — STAGED QUEST (3 stages)
 *    Stage 1 — "Зачистка" (complete at NPC1 "Глава города"):
 *      Kill 5 dragons (monster_id=3)
 *    Stage 2 — "Охота за трофеями" (complete at NPC2 "Вестник"):
 *      Collect 5 claws from bats (monster_id=2, type=collect, drop_chance=60%)
 *    Stage 3 — "Доставка реликвии" (complete at NPC1 "Глава города"):
 *      Deliver Кристалл x1 (share_item_id=23) — given on stage start, taken on complete
 *    Reward: 2000 exp + 300 money + 2x Коготь медведя (share_item_id=1)
 */
class QuestSeeder extends Seeder
{
    public function run(): void
    {
        $npc1 = Npc::find(1); // Глава города

        // Create second NPC "Вестник" at location_id=4 if not exists
        $npc2 = Npc::firstOrCreate(
            ['name' => 'Воевода Гидвер'],
            ['location_id' => 4, 'description' => 'Дока военного искусства, тактик и стратег, он посвятил свою жизнь развитию военного дела и обучению молодых солдат его премудростям.', 'image' => '/img/npc/voevoda.jpg']
        );

        // Lock the "Тропа Заблудших" location (first one, id=102)
        Location::where('id', 102)->update(['is_locked' => true]);

        // -------------------------------------------------------
        // Quest 1: "Познание мира" — may already exist
        // -------------------------------------------------------
        $quest1 = Quest::firstOrCreate(
            ['title' => 'Познание мира'],
            [
                'description' => 'Добро пожаловать! Чтобы освоиться в этом мире, тебе нужно победить нескольких мышей.',
                'type' => 'one_time',
                'start_npc_id' => $npc1->id,
                'complete_npc_id' => $npc1->id,
                'is_active' => true,
            ]
        );

        if ($quest1->objectives->isEmpty()) {
            QuestObjective::create([
                'quest_id' => $quest1->id,
                'type' => 'kill',
                'target_type' => 'monster',
                'target_id' => 1, // Мышь
                'required_amount' => 5,
                'description' => 'Убить 5 мышей',
            ]);
        }

        if ($quest1->rewards->isEmpty()) {
            QuestReward::create([
                'quest_id' => $quest1->id,
                'type' => 'exp',
                'amount' => 200,
            ]);
        }

        // -------------------------------------------------------
        // Quest 2: "Первая вылазка" — after quest1, kill bats on map 2
        // -------------------------------------------------------
        $quest2 = Quest::firstOrCreate(
            ['title' => 'Первая вылазка'],
            [
                'description' => 'Ты показал себя неплохим бойцом. Теперь иди в Шепчущий лес и разберись с летучими мышами.',
                'type' => 'one_time',
                'start_npc_id' => $npc1->id,
                'complete_npc_id' => $npc1->id,
                'after_quest_id' => $quest1->id,
                'is_active' => true,
            ]
        );

        if ($quest2->objectives->isEmpty()) {
            QuestObjective::create([
                'quest_id' => $quest2->id,
                'type' => 'kill',
                'target_type' => 'monster',
                'target_id' => 2, // Летучая мышь
                'map_id' => 2, // Шепчущий Лес
                'required_amount' => 10,
                'description' => 'Убить 10 летучих мышей в Шепчущем лесу',
            ]);
        }

        if ($quest2->rewards->isEmpty()) {
            QuestReward::create(['quest_id' => $quest2->id, 'type' => 'exp',   'amount' => 500]);
            QuestReward::create(['quest_id' => $quest2->id, 'type' => 'money', 'amount' => 50]);
        }

        // -------------------------------------------------------
        // Quest 3: "Испытание воли" — multi-objective, after quest2
        // -------------------------------------------------------
        $quest3 = Quest::firstOrCreate(
            ['title' => 'Испытание воли'],
            [
                'description' => 'Настоящий воин сражается на нескольких фронтах одновременно. Уничтожь мышей и летучих мышей.',
                'type' => 'one_time',
                'start_npc_id' => $npc1->id,
                'complete_npc_id' => $npc1->id,
                'after_quest_id' => $quest2->id,
                'is_active' => true,
            ]
        );

        if ($quest3->objectives->isEmpty()) {
            QuestObjective::create([
                'quest_id' => $quest3->id,
                'type' => 'kill',
                'target_type' => 'monster',
                'target_id' => 1, // Мышь
                'required_amount' => 5,
                'description' => 'Убить 5 мышей',
            ]);
            QuestObjective::create([
                'quest_id' => $quest3->id,
                'type' => 'kill',
                'target_type' => 'monster',
                'target_id' => 2, // Летучая мышь
                'map_id' => 2,
                'required_amount' => 5,
                'description' => 'Убить 5 летучих мышей в Шепчущем лесу',
            ]);
        }

        if ($quest3->rewards->isEmpty()) {
            QuestReward::create(['quest_id' => $quest3->id, 'type' => 'exp',  'amount' => 800]);
            QuestReward::create(['quest_id' => $quest3->id, 'type' => 'item', 'amount' => 2, 'share_item_id' => 1]); // 2x Коготь медведя
        }

        // -------------------------------------------------------
        // Quest 4: "Путь к катакомбам" — deliver quest, after quest3
        // Start at NPC1 (Глава города), complete at NPC2 (Вестник)
        // -------------------------------------------------------
        $quest4 = Quest::firstOrCreate(
            ['title' => 'Путь к катакомбам'],
            [
                'description' => 'Мне нужно, чтобы ты передал Кристалл моему посланнику Вестнику у восточных ворот. Он откроет тебе путь к Тропе Заблудших.',
                'type' => 'one_time',
                'start_npc_id' => $npc1->id,
                'complete_npc_id' => $npc2->id,
                'after_quest_id' => $quest3->id,
                'is_active' => true,
            ]
        );

        if ($quest4->objectives->isEmpty()) {
            // Deliver a Кристалл (share_item_id=23) — given to player on quest accept, taken on complete
            QuestObjective::create([
                'quest_id' => $quest4->id,
                'type' => 'deliver',
                'target_type' => 'item',
                'target_id' => 23, // Кристалл
                'required_amount' => 1,
                'description' => 'Доставить Кристалл Вестнику',
            ]);
        }

        if ($quest4->rewards->isEmpty()) {
            QuestReward::create(['quest_id' => $quest4->id, 'type' => 'exp',   'amount' => 1000]);
            QuestReward::create([
                'quest_id' => $quest4->id,
                'type' => 'location_access',
                'amount' => 0,
                'location_id' => 102, // Тропа Заблудших (locked)
            ]);
        }

        // -------------------------------------------------------
        // Quest 5: "Дозор у ворот" (repeatable, cooldown 1 day, after quest1)
        // -------------------------------------------------------
        $quest5 = Quest::firstOrCreate(
            ['title' => 'Дозор у ворот'],
            [
                'description' => 'Мышиные стаи снова атакуют ворота! Убей 20 мышей. Это задание можно выполнять каждый день.',
                'type' => 'repeatable',
                'start_npc_id' => $npc1->id,
                'complete_npc_id' => $npc1->id,
                'after_quest_id' => $quest1->id,
                'reset_period' => 86400, // 1 day
                'is_active' => true,
            ]
        );

        if ($quest5->objectives->isEmpty()) {
            QuestObjective::create([
                'quest_id' => $quest5->id,
                'type' => 'kill',
                'target_type' => 'monster',
                'target_id' => 1, // Мышь
                'required_amount' => 20,
                'description' => 'Убить 20 мышей',
            ]);
        }

        if ($quest5->rewards->isEmpty()) {
            QuestReward::create(['quest_id' => $quest5->id, 'type' => 'exp',   'amount' => 300]);
            QuestReward::create(['quest_id' => $quest5->id, 'type' => 'money', 'amount' => 100]);
        }

        // -------------------------------------------------------
        // Quest 6: "Три испытания" — staged quest (3 stages), after quest3
        // -------------------------------------------------------
        $quest6 = Quest::firstOrCreate(
            ['title' => 'Три испытания'],
            [
                'description' => 'Докажи свою доблесть, пройдя три испытания. Тебе предстоит сражаться, охотиться и выполнить особое поручение.',
                'type' => 'one_time',
                'start_npc_id' => $npc1->id,
                'complete_npc_id' => $npc1->id, // fallback for non-staged path
                'after_quest_id' => $quest3->id,
                'is_active' => true,
            ]
        );

        if ($quest6->stages()->doesntExist()) {
            // Stage 1: Kill 5 dragons — turn in at Глава города
            $stage1 = QuestStage::create([
                'quest_id' => $quest6->id,
                'complete_npc_id' => $npc1->id,
                'order' => 1,
                'title' => 'Зачистка',
                'description' => 'Сразись с драконами, обитающими в катакомбах.',
            ]);

            // Stage 2: Collect 5 claws from bats (drop_chance=60%) — turn in at Вестник
            $stage2 = QuestStage::create([
                'quest_id' => $quest6->id,
                'complete_npc_id' => $npc2->id,
                'order' => 2,
                'title' => 'Охота за трофеями',
                'description' => 'Принеси когти летучих мышей Вестнику.',
            ]);

            // Stage 3: Deliver Кристалл x1 — turn in at Глава города
            $stage3 = QuestStage::create([
                'quest_id' => $quest6->id,
                'complete_npc_id' => $npc1->id,
                'order' => 3,
                'title' => 'Доставка реликвии',
                'description' => 'Доставь Кристалл Главе города.',
            ]);

            QuestObjective::create([
                'quest_id' => $quest6->id,
                'stage_id' => $stage1->id,
                'type' => 'kill',
                'target_type' => 'monster',
                'target_id' => 3, // Дракон
                'required_amount' => 5,
                'description' => 'Убить 5 драконов',
            ]);

            QuestObjective::create([
                'quest_id' => $quest6->id,
                'stage_id' => $stage2->id,
                'type' => 'collect',
                'target_type' => 'monster',
                'target_id' => 2,    // Летучая мышь (monster)
                'share_item_id' => 1,    // Коготь медведя (ShareItem that drops into backpack)
                'required_amount' => 5,
                'drop_chance' => 60.00,
                'description' => 'Собрать 5 когтей летучих мышей (шанс 60%)',
            ]);

            QuestObjective::create([
                'quest_id' => $quest6->id,
                'stage_id' => $stage3->id,
                'type' => 'deliver',
                'target_type' => 'item',
                'target_id' => 23, // Кристалл
                'required_amount' => 1,
                'description' => 'Доставить Кристалл',
            ]);
        }

        if ($quest6->rewards()->doesntExist()) {
            QuestReward::create(['quest_id' => $quest6->id, 'type' => 'exp',   'amount' => 2000]);
            QuestReward::create(['quest_id' => $quest6->id, 'type' => 'money', 'amount' => 300]);
            QuestReward::create(['quest_id' => $quest6->id, 'type' => 'item',  'amount' => 2, 'share_item_id' => 1]); // 2x Коготь медведя
        }

        $this->command->info('QuestSeeder: created/updated quests 1-6 and NPC "Вестник".');
    }
}
