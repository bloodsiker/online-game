<?php

namespace Database\Seeders;

use App\Modules\Monster\Infrastructure\Persistence\Models\Monster;
use App\Modules\Npc\Infrastructure\Persistence\Models\Npc;
use App\Modules\Npc\Infrastructure\Persistence\Models\NpcDialogueNode;
use App\Modules\Npc\Infrastructure\Persistence\Models\NpcDialogueOption;
use App\Modules\Quest\Infrastructure\Persistence\Models\Quest;
use App\Modules\Quest\Infrastructure\Persistence\Models\QuestObjective;
use App\Modules\Quest\Infrastructure\Persistence\Models\QuestReward;
use App\Modules\Reputation\Infrastructure\Persistence\Models\Reputation;
use App\Modules\Reputation\Infrastructure\Persistence\Models\ReputationShopItem;
use App\Modules\Reputation\Infrastructure\Persistence\Models\ReputationTier;
use App\Modules\Reputation\Infrastructure\Persistence\Models\ReputationTierQuest;
use App\Modules\Share\Infrastructure\Persistence\Models\ShareItem;
use App\Modules\Share\Infrastructure\Persistence\Models\ShareStructureCategory;
use Illuminate\Database\Seeder;

/**
 * Two reputations:
 *   1. "Городская стража"  — NPC: Глава города (id=1).
 *      Стартовая репутация: монстры 1-7 (Мышь … Властелин Нежити).
 *
 *   2. "Орден Охотников"   — NPC: Егерь Вульфгар (локация 305, карта 3).
 *      Эндгейм-репутация для персонажей ~50 уровня (до НПС ещё нужно дойти),
 *      поэтому цели стартуют с Мантикоры(34) и растут до боссов 50 уровня.
 *      Часть заданий — collect-трофеи (share_items type=quest, drop_chance).
 *      Каждая медаль требует ПОДВИГ (reputation_tiers.feat_quest_id) — особый
 *      квест или цепочку (through quests.after_quest_id, feat_quest_id
 *      указывает на финальный квест). Подвиги не участвуют в ежедневном
 *      random-пуле тира и не трогают 2-дневный кулдаун.
 *
 * Tiers: 0-500, 500-1000, 1000-2000, 2000-3000, 3000-4000, 4000-5000, 5000+
 */
class ReputationSeeder extends Seeder
{
    private const OKHOTA_NPC_LOCATION_ID = 305;

    public function run(): void
    {
        $this->seedStrazha();
        $this->seedOkhota();
    }

    // ─────────────────────────────────────────────────────────────
    // Городская стража
    // ─────────────────────────────────────────────────────────────
    private function seedStrazha(): void
    {
        $npc = Npc::findOrFail(1);

        $reputation = Reputation::firstOrCreate(
            ['name' => 'Городская стража'],
            [
                'description' => 'Помогайте городской страже защищать жителей от монстров и тёмных сил.',
                'npc_id' => $npc->id,
                'icon' => 'img/reputation/strazha.png',
            ]
        );

        $tiers = $this->createTiers($reputation, [
            [0,    500,  null,              null],
            [500,  1000, 'Новобранец',      'img/reputation/medal_s1.png'],
            [1000, 2000, 'Дружинник',       'img/reputation/medal_s2.png'],
            [2000, 3000, 'Сержант',         'img/reputation/medal_s3.png'],
            [3000, 4000, 'Лейтенант',       'img/reputation/medal_s4.png'],
            [4000, 5000, 'Капитан',         'img/reputation/medal_s5.png'],
            [5000, null, 'Командир стражи', 'img/reputation/medal_s6.png'],
        ]);

        // ── Tier 0-500: мыши и летучие мыши ──────────────────────
        $this->addTierQuests($tiers[0], $npc, $reputation, [
            [
                'title' => '[Стража] Мыши в амбарах',
                'desc' => 'Мыши атакуют городские амбары. Уничтожьте 10 мышей.',
                'objs' => [[1, 10, 'Убить 10 мышей']],
                'rep' => 50, 'exp' => 50, 'money' => 100,
            ],
            [
                'title' => '[Стража] Грызуны у стен',
                'desc' => 'Отряды мышей снуют у городских стен. Убейте 15 мышей.',
                'objs' => [[1, 15, 'Убить 15 мышей']],
                'rep' => 55, 'exp' => 62, 'money' => 120,
            ],
            [
                'title' => '[Стража] Летучие разведчики',
                'desc' => 'Летучие мыши кружат над стражей, мешая дозорам. Уничтожьте 8.',
                'objs' => [[2, 8, 'Убить 8 летучих мышей']],
                'rep' => 55, 'exp' => 55, 'money' => 110,
            ],
            [
                'title' => '[Стража] Ночной дозор',
                'desc' => 'Очистите ночные кварталы от мышей и летучих мышей.',
                'objs' => [[1, 5, 'Убить 5 мышей'], [2, 5, 'Убить 5 летучих мышей']],
                'rep' => 65, 'exp' => 70, 'money' => 130,
            ],
            [
                'title' => '[Стража] Ночные гости',
                'desc' => 'Стаи летучих мышей нарушают ночной покой города. Убейте 12.',
                'objs' => [[2, 12, 'Убить 12 летучих мышей']],
                'rep' => 70, 'exp' => 75, 'money' => 150,
            ],
        ]);

        // ── Tier 500-1000: летучие + драконы ─────────────────────
        $this->addTierQuests($tiers[500], $npc, $reputation, [
            [
                'title' => '[Стража] Вылазка нежити',
                'desc' => 'Летучие мыши и Древние драконы угрожают окрестностям. Уничтожьте обоих.',
                'objs' => [[2, 10, 'Убить 10 летучих мышей'], [3, 5, 'Убить 5 Древних драконов']],
                'rep' => 100, 'exp' => 125, 'money' => 300,
            ],
            [
                'title' => '[Стража] Дракон у ворот',
                'desc' => 'Древние драконы появились у ворот города. Уничтожьте 15.',
                'objs' => [[3, 15, 'Убить 15 Древних драконов']],
                'rep' => 110, 'exp' => 138, 'money' => 320,
            ],
            [
                'title' => '[Стража] Зачистка предместий',
                'desc' => 'Предместья кишат летучими мышами. Зачистите — убейте 20.',
                'objs' => [[2, 20, 'Убить 20 летучих мышей']],
                'rep' => 105, 'exp' => 130, 'money' => 310,
            ],
            [
                'title' => '[Стража] Двойной удар',
                'desc' => 'Атака с неба — драконы и летучие мыши действуют сообща.',
                'objs' => [[3, 10, 'Убить 10 Древних драконов'], [2, 10, 'Убить 10 летучих мышей']],
                'rep' => 115, 'exp' => 145, 'money' => 340,
            ],
            [
                'title' => '[Стража] Ночная вахта',
                'desc' => 'Долгая ночная вахта — уничтожьте 25 мышей и 5 драконов.',
                'objs' => [[1, 25, 'Убить 25 мышей'], [3, 5, 'Убить 5 Древних драконов']],
                'rep' => 120, 'exp' => 150, 'money' => 350,
            ],
        ]);

        // ── Tier 1000-2000: личи и вампиры ───────────────────────
        $this->addTierQuests($tiers[1000], $npc, $reputation, [
            [
                'title' => '[Стража] Некроманты наступают',
                'desc' => 'Армия личей надвигается на город. Остановите её — уничтожьте 10 Личей Некромантов.',
                'objs' => [[4, 10, 'Убить 10 Личей Некромантов']],
                'rep' => 200, 'exp' => 250, 'money' => 600,
            ],
            [
                'title' => '[Стража] Вампирский ковен',
                'desc' => 'Ковен вампиров обосновался в предместьях. Уничтожьте 15 Древних вампиров.',
                'objs' => [[5, 15, 'Убить 15 Древних вампиров']],
                'rep' => 220, 'exp' => 275, 'money' => 650,
            ],
            [
                'title' => '[Стража] Двойная угроза',
                'desc' => 'Личи и вампиры действуют сообща. Уничтожьте и тех, и других.',
                'objs' => [[4, 8, 'Убить 8 Личей Некромантов'], [5, 8, 'Убить 8 Древних вампиров']],
                'rep' => 240, 'exp' => 300, 'money' => 700,
            ],
            [
                'title' => '[Стража] Страж некрополя',
                'desc' => 'Некрополь поднимает всё больше личей. Уничтожьте 20.',
                'objs' => [[4, 20, 'Убить 20 Личей Некромантов']],
                'rep' => 260, 'exp' => 325, 'money' => 750,
            ],
            [
                'title' => '[Стража] Осада мертвецов',
                'desc' => 'Мертвецы осаждают городские стены. Проредите их ряды.',
                'objs' => [[5, 12, 'Убить 12 Древних вампиров'], [4, 8, 'Убить 8 Личей Некромантов']],
                'rep' => 280, 'exp' => 350, 'money' => 800,
            ],
        ]);

        // ── Tier 2000-3000: демоны и властелины ──────────────────
        $this->addTierQuests($tiers[2000], $npc, $reputation, [
            [
                'title' => '[Стража] Демоническое вторжение',
                'desc' => 'Демоны Регенерации ломятся в городские ворота. Остановите 10.',
                'objs' => [[6, 10, 'Убить 10 Демонов Регенерации']],
                'rep' => 350, 'exp' => 500, 'money' => 1000,
            ],
            [
                'title' => '[Стража] Властелины тьмы',
                'desc' => 'Властелины Нежити командуют ордами. Уничтожьте 8 лидеров.',
                'objs' => [[7, 8, 'Убить 8 Властелинов Нежити']],
                'rep' => 370, 'exp' => 525, 'money' => 1100,
            ],
            [
                'title' => '[Стража] Элитный карательный отряд',
                'desc' => 'Элитные демоны и властелины угрожают городу. Уничтожьте обоих.',
                'objs' => [[6, 5, 'Убить 5 Демонов Регенерации'], [7, 5, 'Убить 5 Властелинов Нежити']],
                'rep' => 400, 'exp' => 625, 'money' => 1500,
            ],
        ]);

        // ── Tier 3000-4000 ────────────────────────────────────────
        $this->addTierQuests($tiers[3000], $npc, $reputation, [
            [
                'title' => '[Стража] Волна тьмы',
                'desc' => 'Тёмная волна демонов захлёстывает окрестности. Уничтожьте 15.',
                'objs' => [[6, 15, 'Убить 15 Демонов Регенерации']],
                'rep' => 450, 'exp' => 750, 'money' => 1800,
            ],
            [
                'title' => '[Стража] Армия нежити',
                'desc' => 'Армия Властелинов Нежити надвигается. Уничтожьте 12.',
                'objs' => [[7, 12, 'Убить 12 Властелинов Нежити']],
                'rep' => 480, 'exp' => 800, 'money' => 2000,
            ],
            [
                'title' => '[Стража] Последний рубеж',
                'desc' => 'Оборонительный рубеж города. Уничтожьте демонов и властелинов.',
                'objs' => [[6, 10, 'Убить 10 Демонов Регенерации'], [7, 8, 'Убить 8 Властелинов Нежити']],
                'rep' => 520, 'exp' => 875, 'money' => 2200,
            ],
        ]);

        // ── Tier 4000-5000 ────────────────────────────────────────
        $this->addTierQuests($tiers[4000], $npc, $reputation, [
            [
                'title' => '[Стража] Бесконечная тьма',
                'desc' => 'Тьма не отступает. Уничтожьте 20 Демонов Регенерации.',
                'objs' => [[6, 20, 'Убить 20 Демонов Регенерации']],
                'rep' => 600, 'exp' => 1000, 'money' => 2500,
            ],
            [
                'title' => '[Стража] Хаос нежити',
                'desc' => 'Хаос нежити охватил регион. Уничтожьте 18 Властелинов.',
                'objs' => [[7, 18, 'Убить 18 Властелинов Нежити']],
                'rep' => 650, 'exp' => 1125, 'money' => 3000,
            ],
            [
                'title' => '[Стража] Финальный бой',
                'desc' => 'Финальное сражение за судьбу города. Уничтожьте всех.',
                'objs' => [[6, 15, 'Убить 15 Демонов Регенерации'], [7, 12, 'Убить 12 Властелинов Нежити']],
                'rep' => 700, 'exp' => 1250, 'money' => 3500,
            ],
        ]);

        // ── Tier 5000+ ────────────────────────────────────────────
        $this->addTierQuests($tiers[5000], $npc, $reputation, [
            [
                'title' => '[Стража] Вечный страж',
                'desc' => 'Задание для истинных защитников города. Уничтожьте 25 Демонов.',
                'objs' => [[6, 25, 'Убить 25 Демонов Регенерации']],
                'rep' => 800, 'exp' => 1250, 'money' => 4000,
            ],
            [
                'title' => '[Стража] Повелитель тьмы',
                'desc' => 'Только командир стражи способен справиться. Уничтожьте 25 Властелинов.',
                'objs' => [[7, 25, 'Убить 25 Властелинов Нежити']],
                'rep' => 850, 'exp' => 1375, 'money' => 4500,
            ],
            [
                'title' => '[Стража] Чистильщик мрака',
                'desc' => 'Окончательно очистите мир от тёмных сил.',
                'objs' => [[6, 15, 'Убить 15 Демонов Регенерации'], [7, 15, 'Убить 15 Властелинов Нежити']],
                'rep' => 900, 'exp' => 1500, 'money' => 5000,
            ],
        ]);

        // ── Магазин ───────────────────────────────────────────────
        foreach ([
            ['share_item_id' => 17, 'price' => 500,  'min_points' => 0,    'sort_order' => 1], // Эликсир жизни
            ['share_item_id' => 18, 'price' => 500,  'min_points' => 0,    'sort_order' => 2], // Эликсир маны
            ['share_item_id' => 20, 'price' => 3000, 'min_points' => 500,  'sort_order' => 3], // Рюкзак путешественника
            ['share_item_id' => 19, 'price' => 5000, 'min_points' => 1000, 'sort_order' => 4], // Пояс титана
        ] as $si) {
            ReputationShopItem::firstOrCreate(
                ['reputation_id' => $reputation->id, 'share_item_id' => $si['share_item_id']],
                array_merge($si, ['reputation_id' => $reputation->id])
            );
        }

        $this->command->info("ReputationSeeder: «{$reputation->name}» — OK");
    }

    // ─────────────────────────────────────────────────────────────
    // Орден Охотников (эндгейм, НПС на локации 305)
    // ─────────────────────────────────────────────────────────────
    private function seedOkhota(): void
    {
        $npc = Npc::updateOrCreate(
            ['name' => 'Егерь Вульфгар'],
            [
                'location_id' => self::OKHOTA_NPC_LOCATION_ID,
                'description' => 'Старый егерь Ордена Охотников. Ведёт учёт контрактов на чудовищ долины и выдаёт задания тем, кто сумел добраться так далеко.',
                'image' => '/img/npc/lesnik.jpg',
            ]
        );

        $reputation = Reputation::updateOrCreate(
            ['name' => 'Орден Охотников'],
            [
                'description' => 'Докажите своё мастерство охотника, выполняя контракты егеря Вульфгара и уничтожая сильнейших существ долины.',
                'npc_id' => $npc->id,
                'icon' => '/img/reputation/okhota.png',
            ]
        );

        // Все квесты Ордена всегда указывают на текущего НПС репутации
        Quest::where('title', 'like', '[Орден]%')
            ->update(['start_npc_id' => $npc->id, 'complete_npc_id' => $npc->id]);

        $this->seedOkhotaDialogue($npc);

        $tiers = $this->createTiers($reputation, [
            [0,    500,  null,           null],
            [500,  1000, 'Охотник',      'img/reputation/medal_o1.png'],
            [1000, 2000, 'Следопыт',     'img/reputation/medal_o2.png'],
            [2000, 3000, 'Ловчий',       'img/reputation/medal_o3.png'],
            [3000, 4000, 'Мастер охоты', 'img/reputation/medal_o4.png'],
            [4000, 5000, 'Элита',        'img/reputation/medal_o5.png'],
            [5000, null, 'Легенда',      'img/reputation/medal_o6.png'],
        ]);

        $m = $this->okhotaMonsters();
        $t = $this->createOkhotaTrophies();

        // ── Tier 0-500: Мантикора(34), Виверна(39) ───────────────────────
        $this->addOkhotaTierQuests($tiers[0], $npc, $reputation, [
            [
                'title' => '[Орден] Хвост скорпиона, морда льва',
                'desc' => 'Мантикоры расплодились у дороги в поселение. Убейте 8.',
                'objs' => [['kill', $m['Мантикора'], 8, 'Убить 8 Мантикор']],
                'rep' => 50, 'exp' => 400, 'money' => 800,
            ],
            [
                'title' => '[Орден] Шипы для алхимика',
                'desc' => 'Алхимикам Ордена нужны шипы мантикор. Добудьте 5 штук.',
                'objs' => [['collect', $m['Мантикора'], 5, 'Добыть 5 шипов мантикоры', $t['Шип мантикоры'], 45.0]],
                'rep' => 60, 'exp' => 450, 'money' => 900,
            ],
            [
                'title' => '[Орден] Небесные хищницы',
                'desc' => 'Виверны охотятся на путников. Собейте 6.',
                'objs' => [['kill', $m['Виверна'], 6, 'Убить 6 Виверн']],
                'rep' => 55, 'exp' => 420, 'money' => 850,
            ],
            [
                'title' => '[Орден] Двойная вылазка',
                'desc' => 'Проредите и мантикор, и виверн разом.',
                'objs' => [
                    ['kill', $m['Мантикора'], 5, 'Убить 5 Мантикор'],
                    ['kill', $m['Виверна'], 4, 'Убить 4 Виверны'],
                ],
                'rep' => 70, 'exp' => 500, 'money' => 1000,
            ],
        ]);

        // ── Tier 500-1000: Виверна(39), Ледяной великан(44) ──────────────
        $this->addOkhotaTierQuests($tiers[500], $npc, $reputation, [
            [
                'title' => '[Орден] Крылья на стену',
                'desc' => 'Охотничий зал Ордена украшают крылья виверн. Принесите 4.',
                'objs' => [['collect', $m['Виверна'], 4, 'Добыть 4 крыла виверны', $t['Крыло виверны'], 40.0]],
                'rep' => 100, 'exp' => 700, 'money' => 1400,
            ],
            [
                'title' => '[Орден] Холод с гор',
                'desc' => 'Ледяные великаны спустились с гор. Убейте 6.',
                'objs' => [['kill', $m['Ледяной великан'], 6, 'Убить 6 Ледяных великанов']],
                'rep' => 110, 'exp' => 750, 'money' => 1500,
            ],
            [
                'title' => '[Орден] Большая охота',
                'desc' => 'Виверны и великаны — добыча для опытного охотника.',
                'objs' => [
                    ['kill', $m['Виверна'], 8, 'Убить 8 Виверн'],
                    ['kill', $m['Ледяной великан'], 4, 'Убить 4 Ледяных великана'],
                ],
                'rep' => 120, 'exp' => 800, 'money' => 1600,
            ],
        ]);

        // ── Tier 1000-2000: Ледяной великан(44), Молодой дракон(50) ──────
        $this->addOkhotaTierQuests($tiers[1000], $npc, $reputation, [
            [
                'title' => '[Орден] Сердце стужи',
                'desc' => 'Добудьте 3 ледяных сердца — они не тают даже летом.',
                'objs' => [['collect', $m['Ледяной великан'], 3, 'Добыть 3 ледяных сердца', $t['Ледяное сердце'], 35.0]],
                'rep' => 200, 'exp' => 1100, 'money' => 2200,
            ],
            [
                'title' => '[Орден] Первый дракон',
                'desc' => 'Молодые драконы — серьёзная добыча. Убейте 5.',
                'objs' => [['kill', $m['Молодой дракон'], 5, 'Убить 5 Молодых драконов']],
                'rep' => 220, 'exp' => 1200, 'money' => 2400,
            ],
            [
                'title' => '[Орден] Лёд и пламя',
                'desc' => 'Великаны и драконы не поделили угодья. Помогите — уничтожьте обоих.',
                'objs' => [
                    ['kill', $m['Ледяной великан'], 6, 'Убить 6 Ледяных великанов'],
                    ['kill', $m['Молодой дракон'], 4, 'Убить 4 Молодых драконов'],
                ],
                'rep' => 240, 'exp' => 1300, 'money' => 2600,
            ],
        ]);

        // ── Tier 2000-3000: драконы + первые боссы ───────────────────────
        $this->addOkhotaTierQuests($tiers[2000], $npc, $reputation, [
            [
                'title' => '[Орден] Чешуйчатый доспех',
                'desc' => 'Кузнецам Ордена нужна драконья чешуя. Добудьте 4.',
                'objs' => [['collect', $m['Молодой дракон'], 4, 'Добыть 4 чешуи молодого дракона', $t['Чешуя молодого дракона'], 35.0]],
                'rep' => 350, 'exp' => 1800, 'money' => 3500,
            ],
            [
                'title' => '[Орден] Древний ужас',
                'desc' => 'Древний дракон снова поднялся. Одолейте его дважды.',
                'objs' => [['kill', $m['Древний дракон'], 2, 'Убить 2 Древних драконов']],
                'rep' => 370, 'exp' => 1900, 'money' => 3700,
            ],
            [
                'title' => '[Орден] Охота на некроманта',
                'desc' => 'Лич Некромант поднимает мёртвых охотников. Упокойте его.',
                'objs' => [['kill', $m['Лич Некромант'], 2, 'Убить 2 Личей Некромантов']],
                'rep' => 400, 'exp' => 2000, 'money' => 4000,
            ],
        ]);

        // ── Tier 3000-4000: Демон(45), вампиры ───────────────────────────
        $this->addOkhotaTierQuests($tiers[3000], $npc, $reputation, [
            [
                'title' => '[Орден] Кровь за кровь',
                'desc' => 'Древние вампиры пьют кровь путников. Уничтожьте 3.',
                'objs' => [['kill', $m['Древний вампир'], 3, 'Убить 3 Древних вампиров']],
                'rep' => 450, 'exp' => 2400, 'money' => 4800,
            ],
            [
                'title' => '[Орден] Неубиваемый',
                'desc' => 'Демон Регенерации восстаёт вновь и вновь. Убейте 3 — окончательно.',
                'objs' => [['kill', $m['Демон Регенерации'], 3, 'Убить 3 Демонов Регенерации']],
                'rep' => 480, 'exp' => 2500, 'money' => 5000,
            ],
            [
                'title' => '[Орден] Ночь мастера охоты',
                'desc' => 'Вампиры и драконы за одну ночь. Справитесь?',
                'objs' => [
                    ['kill', $m['Древний вампир'], 2, 'Убить 2 Древних вампиров'],
                    ['kill', $m['Молодой дракон'], 6, 'Убить 6 Молодых драконов'],
                ],
                'rep' => 520, 'exp' => 2700, 'money' => 5400,
            ],
        ]);

        // ── Tier 4000-5000: Властелин(50), Демон(45) ─────────────────────
        $this->addOkhotaTierQuests($tiers[4000], $npc, $reputation, [
            [
                'title' => '[Орден] Повелитель мёртвых',
                'desc' => 'Властелин Нежити — вершина охоты. Убейте 2.',
                'objs' => [['kill', $m['Властелин Нежити'], 2, 'Убить 2 Властелинов Нежити']],
                'rep' => 600, 'exp' => 3200, 'money' => 6500,
            ],
            [
                'title' => '[Орден] Элитный контракт',
                'desc' => 'Контракт на демонов и властелинов. Только для элиты Ордена.',
                'objs' => [
                    ['kill', $m['Демон Регенерации'], 3, 'Убить 3 Демонов Регенерации'],
                    ['kill', $m['Властелин Нежити'], 1, 'Убить Властелина Нежити'],
                ],
                'rep' => 650, 'exp' => 3400, 'money' => 7000,
            ],
        ]);

        // ── Tier 5000+: легендарные контракты ────────────────────────────
        $this->addOkhotaTierQuests($tiers[5000], $npc, $reputation, [
            [
                'title' => '[Орден] Легендарная охота',
                'desc' => 'Задание, о котором слагают песни. Убейте 4 Властелинов Нежити.',
                'objs' => [['kill', $m['Властелин Нежити'], 4, 'Убить 4 Властелинов Нежити']],
                'rep' => 800, 'exp' => 4000, 'money' => 8500,
            ],
            [
                'title' => '[Орден] Эссенция тьмы',
                'desc' => 'Добудьте тёмную эссенцию из самого сердца нежити.',
                'objs' => [['collect', $m['Властелин Нежити'], 2, 'Добыть 2 тёмных эссенции', $t['Тёмная эссенция'], 30.0]],
                'rep' => 850, 'exp' => 4200, 'money' => 9000,
            ],
            [
                'title' => '[Орден] Великое истребление',
                'desc' => 'Очистите долину от сильнейших тварей.',
                'objs' => [
                    ['kill', $m['Молодой дракон'], 10, 'Убить 10 Молодых драконов'],
                    ['kill', $m['Властелин Нежити'], 3, 'Убить 3 Властелинов Нежити'],
                ],
                'rep' => 900, 'exp' => 4500, 'money' => 10000,
            ],
        ]);

        $this->seedOkhotaFeats($tiers, $npc, $reputation, $m, $t);

        // ── Магазин (категории + товары) ──────────────────────────
        $catElixirs = ShareStructureCategory::firstOrCreate(['name' => 'Эликсиры']);
        $catArmor = ShareStructureCategory::firstOrCreate(['name' => 'Броня']);
        $catWeapons = ShareStructureCategory::firstOrCreate(['name' => 'Оружие']);

        // Привязываем категории к магазину этой репутации
        $reputation->categories()->syncWithoutDetaching([
            $catElixirs->id, $catArmor->id, $catWeapons->id,
        ]);

        foreach ([
            ['share_item_id' => 17, 'category' => $catElixirs->id, 'price' => 400,  'min_points' => 0,    'sort_order' => 1], // Эликсир жизни
            ['share_item_id' => 18, 'category' => $catElixirs->id, 'price' => 400,  'min_points' => 0,    'sort_order' => 2], // Эликсир маны
            ['share_item_id' => 9,  'category' => $catArmor->id,   'price' => 4000, 'min_points' => 500,  'sort_order' => 1], // Броня-нагрудник
            ['share_item_id' => 10, 'category' => $catArmor->id,   'price' => 2000, 'min_points' => 500,  'sort_order' => 2], // Шлем
            ['share_item_id' => 5,  'category' => $catWeapons->id, 'price' => 8000, 'min_points' => 2000, 'sort_order' => 1], // Оружие
        ] as $si) {
            ReputationShopItem::updateOrCreate(
                ['reputation_id' => $reputation->id, 'share_item_id' => $si['share_item_id']],
                [
                    'reputation_id' => $reputation->id,
                    'share_structure_category_id' => $si['category'],
                    'price' => $si['price'],
                    'min_points' => $si['min_points'],
                    'sort_order' => $si['sort_order'],
                ]
            );
        }

        $this->command->info("ReputationSeeder: «{$reputation->name}» — OK");
    }

    /**
     * Диалог Вульфгара: история Ордена и суть репутации.
     * Дерево: Приветствие → История Ордена → Контракты → Медали и подвиги
     * (из приветствия можно перейти сразу к контрактам).
     */
    private function seedOkhotaDialogue(Npc $npc): void
    {
        $node = fn (string $title, string $text, bool $isStart = false, int $sort = 0) => NpcDialogueNode::firstOrCreate(
            ['npc_id' => $npc->id, 'title' => $title],
            ['description' => $text, 'is_start' => $isStart, 'is_active' => true, 'sort_order' => $sort]
        );

        $greeting = $node('Егерь Вульфгар', 'Стой, путник. Немногие доходят до этого тракта своими ногами — чаще их приносят. Я — Вульфгар, егерь Ордена Охотников. Сорок лет я читаю следы этой долины, и поверь: каждый след здесь ведёт либо к добыче, либо к смерти.', true);

        $history = $node('История Ордена', 'Когда-то за этим перевалом стояла застава Серых Клинков. Гарнизон пал за одну ночь: с гор спустились ледяные великаны, а из пепелища поднялась нежить. Уцелели лишь охотники — те, кто умел выследить зверя раньше, чем зверь выследит их. Так родился Орден: не армия и не гильдия, а братство тех, кто берёт контракты на чудовищ и доводит их до конца. Наш девиз прост: «След не лжёт».', false, 1);

        $contracts = $node('Контракты Ордена', 'Всё просто, как хороший капкан. Я выдаю контракты — на мантикор, виверн, великанов, а для бывалых — на драконов и владык нежити. Исполнил — и Орден узнаёт твоё имя чуть лучше. Чем выше твоя слава, тем опаснее контракты и щедрее награды. Но запомни: один контракт за раз, и после сдачи — два дня тишины. Хороший охотник не гонится за всей дичью разом.', false, 2);

        $medals = $node('Медали и подвиги', 'Хе… Медали Ордена не покупают очками славы. Каждая выкована из клыка первой добычи наших основателей, и каждая вручается только за подвиг. Наберёшь славу — приходи, и я назову твоё испытание: упокоить лича, вырвать сердце ледяного великана… А высшая награда, «Легенда», потребует трёх подвигов кряду. Таких охотников за мою жизнь было двое. Может, станешь третьим.', false, 3);

        $option = fn (NpcDialogueNode $from, NpcDialogueNode $to, string $text, int $sort = 0) => NpcDialogueOption::firstOrCreate(
            ['npc_dialogue_node_id' => $from->id, 'button_text' => $text],
            ['to_node_id' => $to->id, 'is_active' => true, 'sort_order' => $sort]
        );

        $option($greeting, $history, 'Что за Орден Охотников?');
        $option($greeting, $contracts, 'Какая работа найдётся для меня?', 1);
        $option($history, $contracts, 'Как мне заслужить доверие Ордена?');
        $option($contracts, $medals, 'А медали? Я слышал о них.');

        $this->command->info('  ├ диалог Вульфгара: 4 узла');
    }

    /** @return array<string, Monster> */
    private function okhotaMonsters(): array
    {
        $names = [
            'Мантикора', 'Виверна', 'Ледяной великан', 'Молодой дракон',
            'Древний дракон', 'Лич Некромант', 'Древний вампир',
            'Демон Регенерации', 'Властелин Нежити',
        ];

        $monsters = [];
        foreach ($names as $name) {
            $monsters[$name] = Monster::where('name', $name)->firstOrFail();
        }

        return $monsters;
    }

    /** @return array<string, ShareItem> */
    private function createOkhotaTrophies(): array
    {
        $defs = [
            'Шип мантикоры' => 'Ядовитый шип с хвоста мантикоры. Трофей Ордена Охотников.',
            'Крыло виверны' => 'Перепончатое крыло виверны. Трофей Ордена Охотников.',
            'Ледяное сердце' => 'Никогда не тающее сердце ледяного великана. Трофей Ордена Охотников.',
            'Чешуя молодого дракона' => 'Прочная драконья чешуя. Трофей Ордена Охотников.',
            'Тёмная эссенция' => 'Сгусток тёмной силы Властелина Нежити. Редчайший трофей Ордена.',
        ];

        $trophies = [];
        foreach ($defs as $name => $description) {
            $trophies[$name] = ShareItem::firstOrCreate(
                ['name' => $name],
                [
                    'type' => 'quest',
                    'description' => $description,
                    'is_sell' => 0,
                    'price' => 0,
                ]
            );
        }

        return $trophies;
    }

    /**
     * Подвиги Ордена: по одному на каждый тир с медалью. Квест(ы) типа
     * reputation, НЕ входят в random-пул тира; feat_quest_id тира =
     * финальный квест цепочки.
     */
    private function seedOkhotaFeats(array $tiers, Npc $npc, Reputation $reputation, array $m, array $t): void
    {
        $feats = [
            500 => [
                'featDesc' => 'Упокойте Лича Некроманта — докажите, что достойны звания Охотника.',
                'quests' => [[
                    'title' => '[Орден] Подвиг: Первое упокоение',
                    'desc' => 'Медаль «Охотник» получает лишь тот, кто в одиночку упокоил Лича Некроманта.',
                    'objs' => [['kill', $m['Лич Некромант'], 1, 'Убить Лича Некроманта']],
                    'rep' => 150, 'exp' => 1500, 'money' => 3000,
                ]],
            ],
            1000 => [
                'featDesc' => 'Добудьте 5 крыльев виверны — Следопыт умеет выслеживать крылатую дичь.',
                'quests' => [[
                    'title' => '[Орден] Подвиг: Крылатый след',
                    'desc' => 'Следопыт читает небо как книгу. Добудьте 5 крыльев виверны.',
                    'objs' => [['collect', $m['Виверна'], 5, 'Добыть 5 крыльев виверны', $t['Крыло виверны'], 40.0]],
                    'rep' => 250, 'exp' => 2000, 'money' => 4000,
                ]],
            ],
            2000 => [
                'featDesc' => 'Уничтожьте 3 Древних вампиров — Ловчий не боится ночных тварей.',
                'quests' => [[
                    'title' => '[Орден] Подвиг: Ночной ловчий',
                    'desc' => 'Вампиры веками ускользали от Ордена. Убейте 3 Древних вампиров.',
                    'objs' => [['kill', $m['Древний вампир'], 3, 'Убить 3 Древних вампиров']],
                    'rep' => 400, 'exp' => 3000, 'money' => 6000,
                ]],
            ],
            3000 => [
                'featDesc' => 'Добудьте 3 ледяных сердца — Мастер охоты добывает невозможное.',
                'quests' => [[
                    'title' => '[Орден] Подвиг: Сердце зимы',
                    'desc' => 'Ледяное сердце не должно растаять в пути. Принесите 3.',
                    'objs' => [['collect', $m['Ледяной великан'], 3, 'Добыть 3 ледяных сердца', $t['Ледяное сердце'], 35.0]],
                    'rep' => 550, 'exp' => 3500, 'money' => 7000,
                ]],
            ],
            4000 => [
                'featDesc' => 'Убейте 5 Демонов Регенерации — Элита Ордена завершает начатое.',
                'quests' => [[
                    'title' => '[Орден] Подвиг: Окончательная смерть',
                    'desc' => 'Демоны Регенерации не умирают… обычно. Убейте 5 — навсегда.',
                    'objs' => [['kill', $m['Демон Регенерации'], 5, 'Убить 5 Демонов Регенерации']],
                    'rep' => 700, 'exp' => 4500, 'money' => 9000,
                ]],
            ],
            5000 => [
                'featDesc' => 'Пройдите тройное испытание Легенды: чешуя дракона, истребление властелинов и тёмная эссенция.',
                'quests' => [
                    [
                        'title' => '[Орден] Подвиг: Испытание чешуёй',
                        'desc' => 'Первое испытание Легенды: добудьте 5 чешуй молодого дракона.',
                        'objs' => [['collect', $m['Молодой дракон'], 5, 'Добыть 5 чешуй молодого дракона', $t['Чешуя молодого дракона'], 35.0]],
                        'rep' => 300, 'exp' => 3000, 'money' => 6000,
                    ],
                    [
                        'title' => '[Орден] Подвиг: Истребитель властелинов',
                        'desc' => 'Второе испытание Легенды: уничтожьте 10 Властелинов Нежити.',
                        'objs' => [['kill', $m['Властелин Нежити'], 10, 'Убить 10 Властелинов Нежити']],
                        'rep' => 500, 'exp' => 5000, 'money' => 10000,
                    ],
                    [
                        'title' => '[Орден] Подвиг: Тёмная эссенция',
                        'desc' => 'Последнее испытание: вырвите тёмную эссенцию из Властелина Нежити.',
                        'objs' => [['collect', $m['Властелин Нежити'], 1, 'Добыть тёмную эссенцию', $t['Тёмная эссенция'], 25.0]],
                        'rep' => 1000, 'exp' => 8000, 'money' => 20000,
                    ],
                ],
            ],
        ];

        foreach ($feats as $minPoints => $feat) {
            $tier = $tiers[$minPoints];
            $prevQuestId = null;
            $lastQuest = null;

            foreach ($feat['quests'] as $qd) {
                $lastQuest = $this->createOkhotaQuest($qd, $npc, $reputation, afterQuestId: $prevQuestId);
                $prevQuestId = $lastQuest->id;
            }

            $tier->update([
                'feat_quest_id' => $lastQuest->id,
                'feat_description' => $feat['featDesc'],
            ]);

            $this->command->info("  ├ подвиг для медали «{$tier->medal_name}» ({$minPoints}+): «{$lastQuest->title}»");
        }
    }

    private function addOkhotaTierQuests(ReputationTier $tier, Npc $npc, Reputation $reputation, array $quests): void
    {
        foreach ($quests as $qd) {
            $quest = $this->createOkhotaQuest($qd, $npc, $reputation);

            ReputationTierQuest::firstOrCreate([
                'tier_id' => $tier->id,
                'quest_id' => $quest->id,
            ]);

            $this->command->info("  ├ «{$quest->title}» → тир {$tier->min_points}+");
        }
    }

    /** Квест Ордена: objs = [type, Monster, amount, desc, ?ShareItem trophy, ?dropChance] */
    private function createOkhotaQuest(array $qd, Npc $npc, Reputation $reputation, ?int $afterQuestId = null): Quest
    {
        $quest = Quest::firstOrCreate(
            ['title' => $qd['title']],
            [
                'description' => $qd['desc'],
                'type' => 'reputation',
                'start_npc_id' => $npc->id,
                'complete_npc_id' => $npc->id,
                'after_quest_id' => $afterQuestId,
                'is_active' => true,
            ]
        );

        if ($quest->objectives->isEmpty()) {
            foreach ($qd['objs'] as $obj) {
                [$type, $monster, $amount, $description] = $obj;
                QuestObjective::create([
                    'quest_id' => $quest->id,
                    'type' => $type,
                    'target_type' => 'monster',
                    'target_id' => $monster->id,
                    'required_amount' => $amount,
                    'description' => $description,
                    'share_item_id' => $type === 'collect' ? $obj[4]->id : null,
                    'drop_chance' => $type === 'collect' ? $obj[5] : null,
                ]);
            }
        }

        if ($quest->rewards->isEmpty()) {
            QuestReward::create([
                'quest_id' => $quest->id,
                'type' => 'reputation_points',
                'amount' => $qd['rep'],
                'reputation_id' => $reputation->id,
            ]);
            QuestReward::create(['quest_id' => $quest->id, 'type' => 'exp', 'amount' => $qd['exp']]);
            QuestReward::create(['quest_id' => $quest->id, 'type' => 'money', 'amount' => $qd['money']]);
        }

        return $quest;
    }

    // ─────────────────────────────────────────────────────────────
    // Helpers
    // ─────────────────────────────────────────────────────────────

    /** Create tiers for a reputation; returns array keyed by min_points. */
    private function createTiers(Reputation $reputation, array $tiersData): array
    {
        $tiers = [];
        foreach ($tiersData as [$min, $max, $medal, $icon]) {
            $tiers[$min] = ReputationTier::firstOrCreate(
                ['reputation_id' => $reputation->id, 'min_points' => $min],
                ['max_points' => $max, 'medal_name' => $medal, 'medal_icon' => $icon]
            );
        }

        return $tiers;
    }

    /** Create kill-only quests and attach them to the given tier (Стража). */
    private function addTierQuests(ReputationTier $tier, Npc $npc, Reputation $reputation, array $quests): void
    {
        foreach ($quests as $qd) {
            $quest = Quest::firstOrCreate(
                ['title' => $qd['title']],
                [
                    'description' => $qd['desc'],
                    'type' => 'reputation',
                    'start_npc_id' => $npc->id,
                    'complete_npc_id' => $npc->id,
                    'is_active' => true,
                ]
            );

            if ($quest->objectives->isEmpty()) {
                foreach ($qd['objs'] as [$monsterId, $amount, $description]) {
                    QuestObjective::create([
                        'quest_id' => $quest->id,
                        'type' => 'kill',
                        'target_type' => 'monster',
                        'target_id' => $monsterId,
                        'required_amount' => $amount,
                        'description' => $description,
                    ]);
                }
            }

            if ($quest->rewards->isEmpty()) {
                QuestReward::create([
                    'quest_id' => $quest->id,
                    'type' => 'reputation_points',
                    'amount' => $qd['rep'],
                    'reputation_id' => $reputation->id,
                ]);
                QuestReward::create(['quest_id' => $quest->id, 'type' => 'exp', 'amount' => $qd['exp']]);
                QuestReward::create(['quest_id' => $quest->id, 'type' => 'money', 'amount' => $qd['money']]);
            }

            ReputationTierQuest::firstOrCreate([
                'tier_id' => $tier->id,
                'quest_id' => $quest->id,
            ]);

            $this->command->info("  ├ «{$quest->title}» → тир {$tier->min_points}+");
        }
    }
}
