<?php

namespace App\Modules\Battle\Application\Services;

use App\Modules\Battle\Application\DTOs\AttackResultDTO;
use App\Modules\Battle\Infrastructure\Persistence\Models\Battle;
use App\Modules\Battle\Infrastructure\Persistence\Models\BattleDetail;
use App\Modules\Item\Infrastructure\Persistence\Models\Item;
use App\Modules\Location\Infrastructure\Persistence\Models\Location;
use App\Modules\Monster\Infrastructure\Persistence\Models\Monster;
use App\Modules\Monster\Infrastructure\Persistence\Models\MonsterOnLocation;
use App\Modules\Quest\Domain\Events\QuestItemDropped;
use App\Modules\Share\Domain\Enums\ShareItemType;
use App\Modules\User\Infrastructure\Persistence\Models\User;

class DropService
{
    public function dropMoney(User $user, MonsterOnLocation $locationMonster, AttackResultDTO $result): void
    {
        if ($locationMonster->is_drop_money) {
            return;
        }

        $monster = $locationMonster->monster;
        if ($monster->min_money > 0 && $monster->max_money > 0) {
            $money = mt_rand($monster->min_money, $monster->max_money);
            $user->money += $money;
            $user->save();
            $result->log(sprintf("<p style='margin:2px 0;'><span style='background:#fde8e8; border-left:3px solid #c0392b; padding:2px 6px; display:inline-block;'>⚔️ <b style='color:#7b1a1a;'>%s</b> уничтожен. Найдено <b>%s</b> монет</span></p>", $locationMonster->monster->name, $money));
        } else {
            $result->log(sprintf("<p style='margin:2px 0;'><span style='background:#fde8e8; border-left:3px solid #c0392b; padding:2px 6px; display:inline-block;'>⚔️ <b style='color:#7b1a1a;'>%s</b> уничтожен</span></p>", $locationMonster->monster->name));
        }

        $locationMonster->is_drop_money = 1;
        $locationMonster->save();
    }

    public function dropItemsFromMonsters(Battle $battle, Location $location, User $dropRecipient, ?int $dungeonSessionId = null): void
    {
        if ($battle->status->isFinish()) {
            $detailsWithMonsters = BattleDetail::with('locationMonster.monster.items')
                ->whereNotNull('location_monster_id')
                ->where('battle_id', $battle->id)->get();

            $droppedItems = [];
            foreach ($detailsWithMonsters as $monster) {
                if ($monster->locationMonster->monster instanceof Monster) {
                    $monsterItems = $monster->locationMonster->monster->items;
                    foreach ($monsterItems as $item) {
                        if (
                            $item->max_drop_level_difference !== null
                            && $dropRecipient->lvl - $monster->locationMonster->monster->lvl > $item->max_drop_level_difference
                        ) {
                            continue;
                        }

                        // 100 000 равновероятных значений: одно значение = 0.001%.
                        // Диапазон начинается с 1, чтобы шанс 0% никогда не срабатывал.
                        $randomChance = random_int(1, 100000) / 1000;
                        if ($randomChance <= $item->pivot->drop_chance) {
                            $droppedItems[] = [
                                'item' => $item,
                                'count' => mt_rand($item->pivot->min_count, $item->pivot->max_count),
                            ];
                        }
                    }
                }
            }

            foreach ($droppedItems as $dropItem) {
                $item = new Item;
                $item->share_item_id = $dropItem['item']->id;
                $item->count_use = $dropItem['item']->count_use;
                $item->save();

                $pivotData = [
                    'count' => $dropItem['count'],
                    'expires_at' => $dropItem['item']->groundExpiresAt(),
                ];
                if ($dungeonSessionId !== null) {
                    $pivotData['dungeon_session_id'] = $dungeonSessionId;
                }

                $location->itemsOnLocation()->attach($item->id, $pivotData);

                if ($dropItem['item']->type === ShareItemType::QUEST) {
                    QuestItemDropped::dispatch($dropRecipient, (int) $dropItem['item']->id);
                }
            }
        }
    }
}
