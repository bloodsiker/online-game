<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Modules\Event\Domain\Enums\ActivityBonusRewardType;
use App\Modules\Event\Domain\Enums\ActivityPeriod;
use App\Modules\Event\Infrastructure\Persistence\Models\EventActivity;
use App\Modules\Monster\Infrastructure\Persistence\Models\Monster;
use App\Modules\Share\Infrastructure\Persistence\Models\ShareItem;
use Illuminate\Database\Seeder;

class EventActivitySeeder extends Seeder
{
    public function run(): void
    {
        if (EventActivity::query()->exists()) {
            $this->command->info('EventActivitySeeder: активности уже существуют, пропускаем.');

            return;
        }

        $monsters = Monster::query()->pluck('id', 'name');
        $items = ShareItem::query()->pluck('id', 'name');

        $count = fn (int $n): string => '<strong><span style="color:green">'.$n.'</span></strong>';
        $target = fn (string $name): string => '<strong><span style="color:darkred">'.$name.'</span></strong>';

        $activities = [
            // ── Дневные ──────────────────────────────────────────────────────
            [
                'period' => ActivityPeriod::DAILY,
                'title' => 'Охота на мышей',
                'description' => 'Убейте '.$target('Мышь').' '.$count(10).' раз',
                'monster' => 'Мышь',
                'required_count' => 10,
                'reward_item' => 'Эликсир жизни',
                'reward_item_amount' => 2,
                'bonus_reward_type' => ActivityBonusRewardType::MONEY,
                'bonus_reward_amount' => 50,
            ],
            [
                'period' => ActivityPeriod::DAILY,
                'title' => 'Ночные твари',
                'description' => 'Убейте '.$target('Летучую мышь').' '.$count(5).' раз',
                'monster' => 'Летучая мышь',
                'required_count' => 5,
                'reward_item' => 'Эликсир маны',
                'reward_item_amount' => 2,
            ],
            [
                'period' => ActivityPeriod::DAILY,
                'title' => 'Лич Некромант',
                'description' => 'Одолейте '.$target('Лича Некроманта').' '.$count(1).' раз',
                'monster' => 'Лич Некромант',
                'required_count' => 1,
                'reward_item' => 'Кристалл',
                'reward_item_amount' => 1,
                'bonus_reward_type' => ActivityBonusRewardType::DIAMOND,
                'bonus_reward_amount' => 1,
            ],
            [
                'period' => ActivityPeriod::DAILY,
                'title' => 'Древний вампир',
                'description' => 'Уничтожьте '.$target('Древнего вампира').' '.$count(2).' раза',
                'monster' => 'Древний вампир',
                'required_count' => 2,
                'reward_item' => 'Монета древности',
                'reward_item_amount' => 3,
                'bonus_reward_type' => ActivityBonusRewardType::MONEY,
                'bonus_reward_amount' => 100,
            ],

            // ── Недельные ────────────────────────────────────────────────────
            [
                'period' => ActivityPeriod::WEEKLY,
                'title' => 'Древний дракон',
                'description' => 'Победите '.$target('Древнего дракона').' '.$count(3).' раза',
                'monster' => 'Древний дракон',
                'required_count' => 3,
                'reward_item' => 'Сундук',
                'reward_item_amount' => 1,
                'bonus_reward_type' => ActivityBonusRewardType::DIAMOND,
                'bonus_reward_amount' => 2,
            ],
            [
                'period' => ActivityPeriod::WEEKLY,
                'title' => 'Властелин Нежити',
                'description' => 'Сразите '.$target('Властелина Нежити').' '.$count(5).' раз',
                'monster' => 'Властелин Нежити',
                'required_count' => 5,
                'reward_item' => 'Свиток заточки',
                'reward_item_amount' => 1,
                'bonus_reward_type' => ActivityBonusRewardType::ITEM,
                'bonus_reward_amount' => 1,
                'bonus_item' => 'Изумрудный ключ',
            ],
            [
                'period' => ActivityPeriod::WEEKLY,
                'title' => 'Демон Регенерации',
                'description' => 'Уничтожьте '.$target('Демона Регенерации').' '.$count(10).' раз',
                'monster' => 'Демон Регенерации',
                'required_count' => 10,
                'reward_item' => 'Слиток',
                'reward_item_amount' => 5,
                'bonus_reward_type' => ActivityBonusRewardType::MONEY,
                'bonus_reward_amount' => 500,
            ],
        ];

        $sortByPeriod = [];

        foreach ($activities as $data) {
            $monsterId = $monsters[$data['monster']] ?? null;
            $rewardItemId = $items[$data['reward_item']] ?? null;

            if ($monsterId === null || $rewardItemId === null) {
                $this->command->warn("EventActivitySeeder: пропущена «{$data['title']}» — не найден монстр или предмет награды.");

                continue;
            }

            $periodValue = $data['period']->value;
            $sortByPeriod[$periodValue] = ($sortByPeriod[$periodValue] ?? 0) + 1;

            EventActivity::create([
                'period' => $data['period'],
                'title' => $data['title'],
                'description' => $data['description'],
                'monster_id' => $monsterId,
                'required_count' => $data['required_count'],
                'reward_share_item_id' => $rewardItemId,
                'reward_item_amount' => $data['reward_item_amount'],
                'bonus_reward_type' => $data['bonus_reward_type'] ?? null,
                'bonus_reward_amount' => $data['bonus_reward_amount'] ?? null,
                'bonus_reward_share_item_id' => isset($data['bonus_item']) ? ($items[$data['bonus_item']] ?? null) : null,
                'sort_order' => $sortByPeriod[$periodValue],
                'is_active' => true,
            ]);
        }

        $this->command->info('EventActivitySeeder: создано активностей — '.EventActivity::count());
    }
}
