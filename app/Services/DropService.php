<?php

namespace App\Services;

use App\DTO\AttackResultDTO;
use App\Models\Battle\Battle;
use App\Models\Battle\BattleDetail;
use App\Models\Item\Item;
use App\Models\Location;
use App\Models\Monster\Monster;
use App\Models\Monster\MonsterOnLocation;
use App\Models\User;

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
            $result->log(sprintf("<p><span style='background:#F2B092;'>Вы уничтожили %s. Обыскав его, вы нашли <b>%s</b> монет</span></p>", $locationMonster->monster->name, $money));
        } else {
            $result->log(sprintf("<p><span style='background:#F2B092;'>Вы уничтожили %s.</span></p>", $locationMonster->monster->name));
        }

        $locationMonster->is_drop_money = 1;
        $locationMonster->save();
    }

    public function dropItemsFromMonsters(Battle $battle, Location $location): void
    {
        if ($battle->isFinish()) {
            $detailsWithMonsters = BattleDetail::with('locationMonster.monster.items')
                ->whereNotNull('location_monster_id')
                ->where('battle_id', $battle->id)->get();

            $droppedItems = [];
            foreach ($detailsWithMonsters as $monster) {
                if ($monster->locationMonster->monster instanceof Monster) {
                    $monsterItems = $monster->locationMonster->monster->items;
                    foreach ($monsterItems as $item) {
                        $randomChance = mt_rand(0, 100000) / 1000;  // деление на 1000 для преобразования в проценты с тремя десятичными
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
                $item = new Item();
                $item->share_item_id = $dropItem['item']->id;
                $item->count_use = $dropItem['item']->count_use;
                $item->save();

                $location->itemsOnLocation()->attach($item->id, ['count' => $dropItem['count']]);
            }
        }
    }
}
