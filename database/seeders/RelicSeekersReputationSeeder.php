<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Modules\Quest\Infrastructure\Persistence\Models\Quest;
use App\Modules\Quest\Infrastructure\Persistence\Models\QuestObjective;
use App\Modules\Reputation\Infrastructure\Persistence\Models\Reputation;
use App\Modules\Reputation\Infrastructure\Persistence\Models\ReputationTier;
use App\Modules\Share\Infrastructure\Persistence\Models\ShareItem;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class RelicSeekersReputationSeeder extends Seeder
{
    private const REPUTATION_NAME = 'Искатели реликтов';

    private const WORSHIP_QUEST_TITLE = '[Искатели реликтов] Квест поклонения';

    private const WORSHIP_ITEM_NAME = 'Сердце древнего механизма';

    public function run(): void
    {
        $reputation = Reputation::query()
            ->with('npc')
            ->where('name', self::REPUTATION_NAME)
            ->first();

        if (! $reputation || ! $reputation->npc) {
            throw new RuntimeException('Репутация «'.self::REPUTATION_NAME.'» или её НПС не найдены.');
        }

        $worshipItem = ShareItem::query()
            ->where('name', self::WORSHIP_ITEM_NAME)
            ->firstOrFail();

        $monsterIds = DB::table('monster_has_items')
            ->where('share_item_id', $worshipItem->id)
            ->orderBy('monster_id')
            ->pluck('monster_id')
            ->map(static fn ($id): int => (int) $id)
            ->unique()
            ->values();

        if ($monsterIds->isEmpty()) {
            throw new RuntimeException('Не найдены монстры, с которых выпадает «'.self::WORSHIP_ITEM_NAME.'».');
        }

        DB::transaction(function () use ($reputation, $worshipItem, $monsterIds): void {
            $quest = Quest::query()->updateOrCreate(
                ['title' => self::WORSHIP_QUEST_TITLE],
                [
                    'description' => 'Хальдор готов признать вас достойным поклонения Искателей. Добудьте 5 сердец древних механизмов и принесите их ему.',
                    'type' => 'reputation',
                    'start_npc_id' => $reputation->npc_id,
                    'complete_npc_id' => $reputation->npc_id,
                    'after_quest_id' => null,
                    'is_active' => true,
                ]
            );

            QuestObjective::query()->updateOrCreate(
                [
                    'quest_id' => $quest->id,
                    'type' => 'collect',
                    'share_item_id' => $worshipItem->id,
                ],
                [
                    'target_type' => 'monster',
                    'target_id' => $monsterIds->first(),
                    'target_ids' => $monsterIds->all(),
                    'required_amount' => 5,
                    'drop_chance' => 20.0,
                    'description' => 'Добыть 5 сердец древних механизмов',
                ]
            );

            $tiers = [
                [0, 500, null, null],
                [500, 1000, 'Медаль Признания', 'img/reputation/relic-seekers-recognition.png'],
                [1000, 2000, 'Медаль Дружбы', 'img/reputation/relic-seekers-friendship.png'],
                [2000, 3000, 'Медаль Уважения', 'img/reputation/relic-seekers-respect.png'],
                [3000, null, 'Медаль Почета', 'img/reputation/relic-seekers-honor.png'],
            ];

            foreach ($tiers as [$minPoints, $maxPoints, $medalName, $medalIcon]) {
                ReputationTier::query()->updateOrCreate(
                    [
                        'reputation_id' => $reputation->id,
                        'min_points' => $minPoints,
                    ],
                    [
                        'max_points' => $maxPoints,
                        'medal_name' => $medalName,
                        'medal_icon' => $medalIcon,
                        'feat_quest_id' => $minPoints === 3000 ? $quest->id : null,
                        'feat_description' => $minPoints === 3000
                            ? 'Добудьте 5 сердец древних механизмов и принесите их Хальдору.'
                            : null,
                        'feat_medal_name' => $minPoints === 3000 ? 'Медаль Поклонения' : null,
                        'feat_medal_icon' => $minPoints === 3000
                            ? 'main/images/data/artifacts/medaliskatelia_red.gif'
                            : null,
                    ]
                );
            }
        });

        $this->command?->info('RelicSeekersReputationSeeder: уровни и квест поклонения добавлены.');
    }
}
