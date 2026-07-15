<?php

namespace Database\Seeders;

use App\Modules\Npc\Infrastructure\Persistence\Models\Npc;
use App\Modules\Quest\Infrastructure\Persistence\Models\Quest;
use App\Modules\Quest\Infrastructure\Persistence\Models\QuestObjective;
use App\Modules\Quest\Infrastructure\Persistence\Models\QuestReward;
use Illuminate\Database\Seeder;

/**
 * 5 clan quests (type=clan, repeatable via reset_period) assigned to NPC "Глава города" (id=1).
 *
 * Monsters:
 *   1 — Мышь
 *   2 — Летучая мышь
 *   3 — Древний дракон
 *   4 — Лич Некромант
 *   5 — Древний вампир
 *   6 — Демон Регенерации
 *   7 — Властелин Нежити
 *
 * Reward type clan_points goes to clan.points pool.
 * Reward type exp/money goes to the player who turns in the quest.
 */
class ClanQuestSeeder extends Seeder
{
    public function run(): void
    {
        $npc = Npc::findOrFail(1); // Глава города

        $quests = [
            // Quest 1: Easy — kill mice
            [
                'quest' => [
                    'title'            => 'Охота на грызунов',
                    'description'      => 'Мышиные стаи угрожают нашим амбарам. Кlan должен разобраться с этой угрозой — уничтожьте 30 мышей.',
                    'type'             => 'clan',
                    'start_npc_id'     => $npc->id,
                    'complete_npc_id'  => $npc->id,
                    'reset_period'     => 86400, // 1 day
                    'is_active'        => true,
                ],
                'objectives' => [
                    [
                        'type'            => 'kill',
                        'target_type'     => 'monster',
                        'target_id'       => 1, // Мышь
                        'required_amount' => 30,
                        'description'     => 'Убить 30 мышей',
                    ],
                ],
                'rewards' => [
                    ['type' => 'clan_points', 'amount' => 500],
                    ['type' => 'exp',         'amount' => 250],
                ],
            ],

            // Quest 2: Easy/Medium — kill bats
            [
                'quest' => [
                    'title'            => 'Налёт летучих мышей',
                    'description'      => 'Стаи летучих мышей атакуют ночные дозоры. Клан должен уничтожить 50 летучих мышей и восстановить порядок.',
                    'type'             => 'clan',
                    'start_npc_id'     => $npc->id,
                    'complete_npc_id'  => $npc->id,
                    'reset_period'     => 172800, // 2 days
                    'is_active'        => true,
                ],
                'objectives' => [
                    [
                        'type'            => 'kill',
                        'target_type'     => 'monster',
                        'target_id'       => 2, // Летучая мышь
                        'required_amount' => 50,
                        'description'     => 'Убить 50 летучих мышей',
                    ],
                ],
                'rewards' => [
                    ['type' => 'clan_points', 'amount' => 1000],
                    ['type' => 'exp',         'amount' => 500],
                    ['type' => 'money',       'amount' => 200],
                ],
            ],

            // Quest 3: Medium — kill ancient dragons
            [
                'quest' => [
                    'title'            => 'Угроза драконов',
                    'description'      => 'Древние драконы вырвались из подземелий и сеют хаос. Клану поручено уничтожить 20 Древних драконов.',
                    'type'             => 'clan',
                    'start_npc_id'     => $npc->id,
                    'complete_npc_id'  => $npc->id,
                    'reset_period'     => 604800, // 7 days
                    'is_active'        => true,
                ],
                'objectives' => [
                    [
                        'type'            => 'kill',
                        'target_type'     => 'monster',
                        'target_id'       => 3, // Древний дракон
                        'required_amount' => 20,
                        'description'     => 'Убить 20 Древних драконов',
                    ],
                ],
                'rewards' => [
                    ['type' => 'clan_points', 'amount' => 2000],
                    ['type' => 'exp',         'amount' => 1250],
                    ['type' => 'money',       'amount' => 500],
                ],
            ],

            // Quest 4: Hard — kill undead
            [
                'quest' => [
                    'title'            => 'Противостояние нежити',
                    'description'      => 'Лич Некромант поднимает легионы нежити. Клан обязан уничтожить 15 Личей Некромантов и 10 Властелинов Нежити.',
                    'type'             => 'clan',
                    'start_npc_id'     => $npc->id,
                    'complete_npc_id'  => $npc->id,
                    'reset_period'     => 604800, // 7 days
                    'is_active'        => true,
                ],
                'objectives' => [
                    [
                        'type'            => 'kill',
                        'target_type'     => 'monster',
                        'target_id'       => 4, // Лич Некромант
                        'required_amount' => 15,
                        'description'     => 'Убить 15 Личей Некромантов',
                    ],
                    [
                        'type'            => 'kill',
                        'target_type'     => 'monster',
                        'target_id'       => 7, // Властелин Нежити
                        'required_amount' => 10,
                        'description'     => 'Убить 10 Властелинов Нежити',
                    ],
                ],
                'rewards' => [
                    ['type' => 'clan_points', 'amount' => 3000],
                    ['type' => 'exp',         'amount' => 2500],
                    ['type' => 'money',       'amount' => 1000],
                ],
            ],

            // Quest 5: Hard — kill demons and vampires
            [
                'quest' => [
                    'title'            => 'Чёрная угроза',
                    'description'      => 'Демоны и вампиры вышли из тёмных глубин. Клану предстоит уничтожить 10 Демонов Регенерации и 10 Древних вампиров.',
                    'type'             => 'clan',
                    'start_npc_id'     => $npc->id,
                    'complete_npc_id'  => $npc->id,
                    'reset_period'     => 259200, // 3 days
                    'is_active'        => true,
                ],
                'objectives' => [
                    [
                        'type'            => 'kill',
                        'target_type'     => 'monster',
                        'target_id'       => 6, // Демон Регенерации
                        'required_amount' => 10,
                        'description'     => 'Убить 10 Демонов Регенерации',
                    ],
                    [
                        'type'            => 'kill',
                        'target_type'     => 'monster',
                        'target_id'       => 5, // Древний вампир
                        'required_amount' => 10,
                        'description'     => 'Убить 10 Древних вампиров',
                    ],
                ],
                'rewards' => [
                    ['type' => 'clan_points', 'amount' => 2500],
                    ['type' => 'exp',         'amount' => 2000],
                    ['type' => 'money',       'amount' => 800],
                ],
            ],
        ];

        foreach ($quests as $data) {
            $quest = Quest::firstOrCreate(
                ['title' => $data['quest']['title']],
                $data['quest']
            );

            if ($quest->objectives->isEmpty()) {
                foreach ($data['objectives'] as $obj) {
                    QuestObjective::create(array_merge(['quest_id' => $quest->id], $obj));
                }
            }

            if ($quest->rewards->isEmpty()) {
                foreach ($data['rewards'] as $reward) {
                    QuestReward::create(array_merge(['quest_id' => $quest->id], $reward));
                }
            }

            $this->command->info("ClanQuestSeeder: quest «{$quest->title}» — OK");
        }
    }
}
