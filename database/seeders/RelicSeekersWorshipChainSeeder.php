<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Modules\Quest\Domain\Services\QuestDefinitionsCache;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use RuntimeException;

/**
 * Подвиг «Поклонение» Искателей реликтов (тир 3000+) — цепочка из 3 квестов у Хальдора, ≈ неделя.
 *
 * 1. «Шёпот обломков»     — принести находки (амулеты, монеты, таблички, идолы) → ⏳ 1 день расшифровки.
 * 2. «Сердца механизмов»  — 5 сердец древних механизмов и 3 осколка короны (15%) с боссов открытого мира.
 * 3. «Ритуал реликвария» (финал, feat_quest_id тира, даёт «Медаль Поклонения»)
 *                          — Азазель, Кровавый Хранитель Склепа, 12 ржавых деталей с големов
 *                          → ⏳ 3 дня реликварий напитывается → 3 искажённые эссенции с нежити кладбища.
 *
 * Квесты 1–2 создаются один раз; финальный «Квест поклонения» переписывается, только пока его никто не начал.
 */
class RelicSeekersWorshipChainSeeder extends Seeder
{
    private const REPUTATION_ID = 3;

    private const FEAT_MIN_POINTS = 3000;

    private const PREFIX = '[Искатели реликтов] ';

    private const FINAL_OLD_TITLE = '[Искатели реликтов] Квест поклонения';

    private const DAY = 86400;

    private int $npcId;

    public function run(): void
    {
        $this->npcId = (int) DB::table('reputations')->where('id', self::REPUTATION_ID)->value('npc_id');
        if ($this->npcId === 0) {
            throw new RuntimeException('Репутация «Искатели реликтов» или её НПС не найдены.');
        }

        DB::transaction(function (): void {
            $amulet = $this->item('Треснувший амулет');
            $coin = $this->item('Позеленевшая монета');
            $tablet = $this->item('Обломок таблички');
            $idol = $this->item('Резной идол');
            $heart = $this->item('Сердце древнего механизма');
            $crown = $this->item('Осколок короны');
            $gear = $this->item('Ржавая Деталь Механизма');
            $essence = $this->item('Искажённая эссенция');

            $bosses = $this->monsters(['Костяной Скорпион', 'Азазель', 'Кровавый Хранитель Склепа']);
            $azazel = $this->monsters(['Азазель']);
            $keeper = $this->monsters(['Кровавый Хранитель Склепа']);
            $golems = $this->monsters(['Глиняный Голем', 'Каменный Голем', 'Гранитный Голем']);
            $undead = $this->monsters(['Могильный Упырь', 'Кровавый Оракул', 'Тень Приговорённого']);

            $q1 = $this->createQuestOnce(
                titles: ['Поклонение: Шёпот обломков', 'Поклоніння: Шепіт уламків'],
                desc: [
                    'Хальдор больше не принимает находки в обмен на признание — теперь они нужны для дела. По обломкам древних святынь он хочет прочесть, где покоились сердца механизмов. Принесите ему всё, что удалось собрать в странствиях.',
                    'Хальдор більше не приймає знахідки в обмін на визнання — тепер вони потрібні для справи. З уламків давніх святинь він хоче прочитати, де спочивали серця механізмів. Принесіть йому все, що вдалося зібрати в мандрах.',
                ],
                afterQuestId: null,
                stages: [
                    [
                        'title' => ['Находки для Хальдора', 'Знахідки для Хальдора'],
                        'desc' => ['Принесите Хальдору находки. Взять задание можно, только имея их при себе.', 'Принесіть Хальдору знахідки. Взяти завдання можна, лише маючи їх при собі.'],
                        'objs' => [
                            $this->bring($amulet, 15, ['Принести 15 «Треснувший амулет»', 'Принести 15 «Тріснутий амулет»']),
                            $this->bring($coin, 15, ['Принести 15 «Позеленевшая монета»', 'Принести 15 «Позеленіла монета»']),
                            $this->bring($tablet, 10, ['Принести 10 «Обломок таблички»', 'Принести 10 «Уламок таблички»']),
                            $this->bring($idol, 5, ['Принести 5 «Резной идол»', 'Принести 5 «Різьблений ідол»']),
                        ],
                    ],
                    [
                        'title' => ['Расшифровка', 'Розшифрування'],
                        'desc' => ['Хальдор сверяет надписи на табличках. Возвращайтесь через сутки.', 'Хальдор звіряє написи на табличках. Повертайтеся за добу.'],
                        'wait' => self::DAY,
                        'waiting' => ['Хальдор не отрывается от табличек. Знаки стёрты, но он почти у цели — приходи позже.', 'Хальдор не відривається від табличок. Знаки стерті, але він майже біля мети — приходь пізніше.'],
                        'ready' => ['Надписи прочтены: сердца механизмов хранят те, кто сторожит древние места силы. Ищи их у самых опасных хозяев этих земель.', 'Написи прочитано: серця механізмів зберігають ті, хто стереже давні місця сили. Шукай їх у найнебезпечніших володарів цих земель.'],
                        'objs' => [],
                    ],
                ],
                rewards: [15000, 20000],
            );

            $q2 = $this->createQuestOnce(
                titles: ['Поклонение: Сердца механизмов', 'Поклоніння: Серця механізмів'],
                desc: [
                    'Таблички указали путь: сердца древних механизмов достались сильнейшим хозяевам этих земель. Отберите их у Костяного Скорпиона, Азазеля и Кровавого Хранителя Склепа — и не забудьте осколки короны, без них сердца не пробудить.',
                    'Таблички вказали шлях: серця давніх механізмів дісталися наймогутнішим володарям цих земель. Відберіть їх у Кістяного Скорпіона, Азазеля та Кривавого Хранителя Склепу — і не забудьте уламки корони, без них серця не пробудити.',
                ],
                afterQuestId: $q1,
                stages: [[
                    'title' => ['Сердца механизмов', 'Серця механізмів'],
                    'desc' => ['Добудьте сердца древних механизмов и осколки короны с боссов.', 'Добудьте серця давніх механізмів та уламки корони з босів.'],
                    'objs' => [
                        $this->drop($bosses, $heart, 5, 15.0, ['Добыть 5 сердец древних механизмов (Костяной Скорпион, Азазель, Кровавый Хранитель Склепа)', 'Добути 5 сердець давніх механізмів (Кістяний Скорпіон, Азазель, Кривавий Хранитель Склепу)']),
                        $this->drop($bosses, $crown, 3, 15.0, ['Добыть 3 осколка короны с тех же боссов', 'Добути 3 уламки корони з тих самих босів']),
                    ],
                ]],
                rewards: [25000, 40000],
            );

            $finalId = $this->rewriteFinalQuest(
                titles: ['Поклонение: Ритуал реликвария', 'Поклоніння: Ритуал релікварію'],
                desc: [
                    'Сердца собраны, но спят. Хальдор готовит древний реликварий: его нужно окропить кровью сильнейших стражей, собрать механизм заново и напитать силой. Пройдите ритуал — и Искатели признают вас достойным поклонения.',
                    'Серця зібрано, але вони сплять. Хальдор готує давній реліквар: його треба окропити кров’ю наймогутніших вартових, зібрати механізм наново й напоїти силою. Пройдіть ритуал — і Шукачі визнають вас гідним поклоніння.',
                ],
                afterQuestId: $q2,
                stages: [
                    [
                        'title' => ['Кровь стражей', 'Кров вартових'],
                        'desc' => ['Победите Азазеля и Кровавого Хранителя Склепа и принесите 12 ржавых деталей механизма с големов Гранитного Перевала.', 'Переможіть Азазеля та Кривавого Хранителя Склепу й принесіть 12 іржавих деталей механізму з големів Гранітного Перевалу.'],
                        'objs' => [
                            $this->kill($azazel, 1, ['Победить Азазеля', 'Перемогти Азазеля']),
                            $this->kill($keeper, 1, ['Победить Кровавого Хранителя Склепа', 'Перемогти Кривавого Хранителя Склепу']),
                            $this->drop($golems, $gear, 12, 25.0, ['Добыть 12 ржавых деталей механизма с големов Гранитного Перевала', 'Добути 12 іржавих деталей механізму з големів Гранітного Перевалу']),
                        ],
                    ],
                    [
                        'title' => ['Реликварий напитывается', 'Реліквар напувається'],
                        'desc' => ['Хальдор собирает механизм в реликварии. Возвращайтесь через 3 дня.', 'Хальдор збирає механізм у реліквари. Повертайтеся за 3 дні.'],
                        'wait' => 3 * self::DAY,
                        'waiting' => ['Реликварий ещё тёплый и тихо гудит. Сердца просыпаются медленно — не торопи их.', 'Реліквар ще теплий і тихо гуде. Серця прокидаються повільно — не квапи їх.'],
                        'ready' => ['Механизм ожил, но ему не хватает силы. Нужна искажённая эссенция — её носит в себе нежить старого кладбища.', 'Механізм ожив, але йому бракує сили. Потрібна спотворена есенція — її носить у собі нежить старого кладовища.'],
                        'objs' => [],
                    ],
                    [
                        'title' => ['Последняя искра', 'Остання іскра'],
                        'desc' => ['Добудьте 3 искажённые эссенции с нежити Заброшенного кладбища и вернитесь к Хальдору.', 'Добудьте 3 спотворені есенції з нежиті Покинутого кладовища й поверніться до Хальдора.'],
                        'objs' => [
                            $this->drop($undead, $essence, 3, 10.0, ['Добыть 3 искажённые эссенции (Могильный Упырь, Кровавый Оракул, Тень Приговорённого)', 'Добути 3 спотворені есенції (Могильний Упир, Кривавий Оракул, Тінь Засудженого)']),
                        ],
                    ],
                ],
                rewards: [40000, 60000],
            );

            DB::table('reputation_tiers')
                ->where('reputation_id', self::REPUTATION_ID)
                ->where('min_points', self::FEAT_MIN_POINTS)
                ->update([
                    'feat_quest_id' => $finalId,
                    'feat_description_legacy' => 'Пройдите у Хальдора цепочку «Поклонение»: находки, сердца механизмов с боссов и ритуал реликвария.',
                    'feat_description' => $this->json([
                        'Пройдите у Хальдора цепочку «Поклонение»: находки, сердца механизмов с боссов и ритуал реликвария.',
                        'Пройдіть у Хальдора ланцюжок «Поклоніння»: знахідки, серця механізмів із босів і ритуал релікварію.',
                    ]),
                    'updated_at' => now(),
                ]);
        });

        QuestDefinitionsCache::flush();

        $this->command?->info('RelicSeekersWorshipChainSeeder: цепочка «Поклонение» готова.');
    }

    private function item(string $name): int
    {
        $id = DB::table('share_items')->where('name->ru', $name)->value('id')
            ?? DB::table('share_items')->where('name_legacy', $name)->value('id');

        if ($id === null) {
            throw new RuntimeException("Предмет «{$name}» не найден.");
        }

        return (int) $id;
    }

    /**
     * @param  list<string>  $names
     * @return list<int>
     */
    private function monsters(array $names): array
    {
        $ids = DB::table('monsters')
            ->where(fn ($query) => $query->whereIn('name->ru', $names)->orWhereIn('name_legacy', $names))
            ->orderBy('lvl')
            ->pluck('id')
            ->map(static fn ($id): int => (int) $id)
            ->unique()
            ->values()
            ->all();

        if ($ids === []) {
            throw new RuntimeException('Монстры не найдены: '.implode(', ', $names));
        }

        return $ids;
    }

    /** @param array{0: string, 1: string} $desc */
    private function bring(int $itemId, int $amount, array $desc): array
    {
        return ['type' => 'collect', 'target_type' => 'item', 'targets' => [], 'item' => $itemId, 'amount' => $amount, 'chance' => null, 'desc' => $desc];
    }

    /**
     * @param  list<int>  $monsterIds
     * @param  array{0: string, 1: string}  $desc
     */
    private function drop(array $monsterIds, int $itemId, int $amount, float $chance, array $desc): array
    {
        return ['type' => 'collect', 'target_type' => 'monster', 'targets' => $monsterIds, 'item' => $itemId, 'amount' => $amount, 'chance' => $chance, 'desc' => $desc];
    }

    /**
     * @param  list<int>  $monsterIds
     * @param  array{0: string, 1: string}  $desc
     */
    private function kill(array $monsterIds, int $amount, array $desc): array
    {
        return ['type' => 'kill', 'target_type' => 'monster', 'targets' => $monsterIds, 'item' => null, 'amount' => $amount, 'chance' => null, 'desc' => $desc];
    }

    /**
     * Квест создаётся один раз; уже существующий не трогаем (правки — в админке).
     *
     * @param  array{0: string, 1: string}  $titles
     * @param  array{0: string, 1: string}  $desc
     * @param  list<array<string, mixed>>  $stages
     * @param  array{0: int, 1: int}  $rewards
     */
    private function createQuestOnce(array $titles, array $desc, ?int $afterQuestId, array $stages, array $rewards): int
    {
        $existing = DB::table('quests')->where('title->ru', self::PREFIX.$titles[0])->value('id');
        if ($existing !== null) {
            return (int) $existing;
        }

        $questId = (int) DB::table('quests')->insertGetId($this->questRow($titles, $desc, $afterQuestId) + ['created_at' => now()]);
        $this->writeStages($questId, $stages);
        $this->writeRewards($questId, $rewards);

        return $questId;
    }

    /**
     * Финальный квест (тот, что привязан к тиру). Переписывается, только если его ещё никто не брал.
     *
     * @param  array{0: string, 1: string}  $titles
     * @param  array{0: string, 1: string}  $desc
     * @param  list<array<string, mixed>>  $stages
     * @param  array{0: int, 1: int}  $rewards
     */
    private function rewriteFinalQuest(array $titles, array $desc, int $afterQuestId, array $stages, array $rewards): int
    {
        $questId = DB::table('quests')->where('title->ru', self::PREFIX.$titles[0])->value('id')
            ?? DB::table('quests')->where('title->ru', self::FINAL_OLD_TITLE)->value('id')
            ?? DB::table('quests')->where('title_legacy', self::FINAL_OLD_TITLE)->value('id');

        if ($questId === null) {
            $questId = (int) DB::table('quests')->insertGetId($this->questRow($titles, $desc, $afterQuestId) + ['created_at' => now()]);
        } else {
            $questId = (int) $questId;
            if (DB::table('quest_players')->where('quest_id', $questId)->exists()) {
                $this->command?->warn("Квест #{$questId} уже начат игроками — оставлен без изменений.");

                return $questId;
            }
            DB::table('quests')->where('id', $questId)->update($this->questRow($titles, $desc, $afterQuestId));
            DB::table('quest_objectives')->where('quest_id', $questId)->delete();
            DB::table('quest_stages')->where('quest_id', $questId)->delete();
            DB::table('quest_rewards')->where('quest_id', $questId)->delete();
        }

        $this->writeStages($questId, $stages);
        $this->writeRewards($questId, $rewards);

        return $questId;
    }

    /**
     * @param  array{0: string, 1: string}  $titles
     * @param  array{0: string, 1: string}  $desc
     * @return array<string, mixed>
     */
    private function questRow(array $titles, array $desc, ?int $afterQuestId): array
    {
        return [
            'title_legacy' => self::PREFIX.$titles[0],
            'title' => $this->json([self::PREFIX.$titles[0], '[Шукачі реліктів] '.$titles[1]]),
            'description_legacy' => $desc[0],
            'description' => $this->json($desc),
            'type' => 'reputation',
            'start_npc_id' => $this->npcId,
            'complete_npc_id' => $this->npcId,
            'after_quest_id' => $afterQuestId,
            'is_active' => 1,
            'updated_at' => now(),
        ];
    }

    /** @param list<array<string, mixed>> $stages */
    private function writeStages(int $questId, array $stages): void
    {
        $useStages = count($stages) > 1;

        foreach (array_values($stages) as $index => $stage) {
            $stageId = null;
            if ($useStages) {
                $stageId = (int) DB::table('quest_stages')->insertGetId([
                    'quest_id' => $questId,
                    'complete_npc_id' => $this->npcId,
                    'order' => $index + 1,
                    'title_legacy' => $stage['title'][0],
                    'title' => $this->json($stage['title']),
                    'description_legacy' => $stage['desc'][0],
                    'description' => $this->json($stage['desc']),
                    'stage_type' => isset($stage['wait']) ? 'wait' : 'action',
                    'wait_duration_seconds' => $stage['wait'] ?? null,
                    'waiting_text_legacy' => $stage['waiting'][0] ?? null,
                    'waiting_text' => isset($stage['waiting']) ? $this->json($stage['waiting']) : null,
                    'ready_text_legacy' => $stage['ready'][0] ?? null,
                    'ready_text' => isset($stage['ready']) ? $this->json($stage['ready']) : null,
                    'grant_share_item_id' => null,
                    'grant_amount' => 1,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            foreach ($stage['objs'] as $obj) {
                $isItemGate = $obj['target_type'] === 'item';
                DB::table('quest_objectives')->insert([
                    'quest_id' => $questId,
                    'stage_id' => $stageId,
                    'type' => $obj['type'],
                    'target_type' => $obj['target_type'],
                    'target_id' => $isItemGate ? $obj['item'] : $obj['targets'][0],
                    'target_ids' => $isItemGate ? null : json_encode($obj['targets']),
                    'share_item_id' => $obj['item'],
                    'consume_item' => 1,
                    'required_amount' => $obj['amount'],
                    'drop_chance' => $obj['chance'],
                    'required_active_effect_id' => null,
                    'description_legacy' => $obj['desc'][0],
                    'description' => $this->json($obj['desc']),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    /** @param array{0: int, 1: int} $rewards */
    private function writeRewards(int $questId, array $rewards): void
    {
        [$exp, $money] = $rewards;
        DB::table('quest_rewards')->insert([
            ['quest_id' => $questId, 'type' => 'exp', 'amount' => $exp, 'reputation_id' => null, 'created_at' => now(), 'updated_at' => now()],
            ['quest_id' => $questId, 'type' => 'money', 'amount' => $money, 'reputation_id' => null, 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    /** @param array{0: string, 1: string} $pair */
    private function json(array $pair): string
    {
        return json_encode(['ru' => $pair[0], 'uk' => $pair[1]], JSON_UNESCAPED_UNICODE);
    }
}
