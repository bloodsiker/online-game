<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Modules\Item\Infrastructure\Persistence\Models\ShareItemLockConfig;
use App\Modules\Location\Infrastructure\Persistence\Models\Location;
use App\Modules\Reputation\Infrastructure\Persistence\Models\Reputation;
use App\Modules\Reputation\Infrastructure\Persistence\Models\ReputationTier;
use App\Modules\Share\Infrastructure\Persistence\Models\ShareItem;
use App\Modules\TreasureHunt\Infrastructure\Persistence\Models\TreasureMapConfig;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

final class TreasureHuntSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function (): void {
            $existing = TreasureMapConfig::first();
            $reputation = $existing?->reputation ?? Reputation::where('name->ru', 'Кладоискатель')->first();
            if ($reputation === null) {
                $reputation = new Reputation([
                    'name' => ['ru' => 'Кладоискатель', 'uk' => 'Шукач скарбів'],
                    'description' => [
                        'ru' => 'Находите место по подсказке карты, сверяйте её с местностью и ищите сокровище. За находку карта расходуется, в рюкзак выдаётся закрытая шкатулка, а репутация открывает более сложные карты.',
                        'uk' => 'Знаходьте місце за підказкою карти, звіряйте її з місцевістю та шукайте скарб. Після знахідки карта витрачається, до рюкзака видається закрита скринька, а репутація відкриває складніші карти.',
                    ],
                ]);
                $this->save($reputation, ['name', 'description']);
            }
            $tiers = [
                [0, 499, 'Искатель следов', 'Шукач слідів', 0, 0],
                [500, 1499, 'Знаток старых карт', 'Знавець старих карт', 5, 3],
                [1500, 2999, 'Кладоискатель', 'Шукач скарбів', 10, 6],
                [3000, 5999, 'Охотник за реликвиями', 'Мисливець за реліквіями', 15, 10],
                [6000, null, 'Хранитель древностей', 'Хранитель старожитностей', 20, 15],
            ];
            foreach ($tiers as [$min, $max, $ru, $uk, $chance, $speed]) {
                $tier = ReputationTier::where('reputation_id', $reputation->id)->where('min_points', $min)->first();
                if ($tier === null) {
                    $tier = new ReputationTier(['reputation_id' => $reputation->id, 'min_points' => $min, 'max_points' => $max, 'medal_name' => ['ru' => $ru, 'uk' => $uk]]);
                    $this->save($tier, ['medal_name']);
                }
                DB::table('treasure_reputation_bonuses')->insertOrIgnore([
                    'reputation_tier_id' => $tier->id, 'search_chance_bonus_percent' => $chance,
                    'search_speed_bonus_percent' => $speed, 'created_at' => now(), 'updated_at' => now(),
                ]);
            }

            $ranks = [
                ['worn', 'Потёртая', 'Потерта', 0, 50, 35, 10, 25, 6, 'У целительного фонтана, где сходятся городские улицы, под старой мостовой скрыт тайник.', 'Біля цілющого фонтана, де сходяться міські вулиці, під старою бруківкою сховано скарб.'],
                ['ancient', 'Старинная', 'Старовинна', 500, 40, 52, 10, 95, 28, 'Ищи у каменных стел с именами братств, под колышущимися клановыми флагами.', 'Шукай біля кам’яних стел з іменами братств, під клановими прапорами.'],
                ['mysterious', 'Загадочная', 'Загадкова', 1000, 30, 77, 10, 145, 1558, 'В подземной выработке найди площадку, где упавший камень не отзывается эхом.', 'У підземній виробці знайди майданчик, де впалий камінь не відлунює.'],
                ['forgotten', 'Забытая', 'Забута', 2000, 20, 103, 10, 225, 1630, 'Там, где окраина уступает пепельным склонам и тропа впервые уходит вверх, ищи под серой землёй.', 'Там, де околиця поступається попелястим схилам і стежка вперше йде вгору, шукай під сірою землею.'],
                ['legendary', 'Легендарная', 'Легендарна', 3000, 10, 136, 10, 300, 29, 'Под высокими сводами, где архивариус хранит летописи братств и принимает новые кланы, покоится старая шкатулка.', 'Під високими склепіннями, де архіваріус зберігає літописи братств і приймає нові клани, лежить давня скринька.'],
            ];
            $rarities = ['common', 'uncommon', 'rare', 'epic', 'legendary'];
            foreach ($ranks as $index => [$code, $ru, $uk, $required, $chance, $seconds, $rewardPoints, $difficulty, $locationId, $clueRu, $clueUk]) {
                $existingConfig = TreasureMapConfig::where('code', $code)->first();
                $territoryClue = ['ru' => 'Клад скрыт на территории «'.Location::findOrFail($locationId)->map->getTranslation('name', 'ru').'». Сверяйте карту с местностью, чтобы найти подходящее место.',
                    'uk' => 'Скарб сховано на території «'.Location::findOrFail($locationId)->map->getTranslation('name', 'uk').'». Звіряйте карту з місцевістю, щоб знайти потрібне місце.'];
                $genericClue = [
                    'ru' => 'Территория определяется при сборке карты. Отправляйтесь в указанную на готовой карте область и сверяйте карту с местностью, чтобы найти место клада.',
                    'uk' => 'Територія визначається під час збирання карти. Вирушайте до зазначеної на готовій карті області та звіряйте карту з місцевістю, щоб знайти місце скарбу.',
                ];
                if ($existingConfig !== null) {
                    // Replace only the original fixed-location clue; preserve admin edits.
                    if (in_array($existingConfig->getTranslation('clue', 'ru'), [$clueRu, $territoryClue['ru']], true)) {
                        $existingConfig->setTranslations('clue', $genericClue)->save();
                        $existingMap = $existingConfig->item;
                        $originalDescription = 'Территория: '.Location::findOrFail($locationId)->map->getTranslation('name', 'ru').'. '.$clueRu;
                        if (in_array($existingMap->getTranslation('description', 'ru'), [$originalDescription, $territoryClue['ru']], true)) {
                            $existingMap->setTranslations('description', $genericClue);
                            $this->save($existingMap, ['description']);
                        }
                    }

                    continue;
                }
                [$clueRu, $clueUk] = [$genericClue['ru'], $genericClue['uk']];
                $source = ShareItem::where('type', 'chest')->whereHas('lockConfig')->whereHas('itemHasItems')
                    ->with(['lockConfig', 'itemHasItems'])->get()->sortBy(fn ($item) => abs($item->lockConfig->lock_required_skill - $difficulty))->firstOrFail();
                $chest = new ShareItem([
                    'name' => ['ru' => $ru.' шкатулка сокровищ', 'uk' => $uk.' скринька скарбів'],
                    'description' => ['ru' => 'Закрытая находка кладоискателя. Откройте замок отмычкой, чтобы забрать содержимое.', 'uk' => 'Закрита знахідка шукача скарбів. Зламайте замок відмичкою, щоб забрати вміст.'],
                    'type' => 'chest', 'rarity' => $rarities[$index], 'image' => $source->getRawOriginal('image'),
                    'is_active' => true, 'is_sell' => false, 'is_give' => true, 'is_auction_sellable' => false,
                    'is_stackable' => false, 'is_weight' => true, 'is_use' => true, 'is_droppable' => true, 'price' => 0,
                ]);
                $this->save($chest, ['name', 'description']);
                ShareItemLockConfig::create([
                    'share_item_id' => $chest->id, 'lock_required_skill' => $difficulty, 'lock_duration_seconds' => 12 + $index * 4,
                    'experience_reward' => max(3, (int) ceil($difficulty / 10)), 'minimum_success_chance_percent' => 5,
                    'trap_chance_penalty_percent' => $index * 3, 'trap_effect_id' => $source->lockConfig->trap_effect_id,
                    'trap_effect_duration_seconds' => 60 + $index * 30, 'trap_damage_percent' => $index * 2,
                ]);
                $loot = $source->itemHasItems
                    ->filter(fn ($item) => $item->type->isGatheringResource() || in_array($item->type->value, ['potion', 'lockpick'], true))
                    ->unique(fn ($item) => $item->type->isGatheringResource() ? 'resource' : $item->type->value)
                    ->mapWithKeys(fn ($item) => [$item->id => [
                        'min_count' => $item->pivot->min_count, 'max_count' => $item->pivot->max_count,
                        'drop_chance' => $item->pivot->drop_chance,
                    ]])->all();
                $moneyItemId = DB::table('share_item_instant_rewards')->where('reward_type', 'money')->value('share_item_id');
                if ($moneyItemId) {
                    $loot[$moneyItemId] = ['min_count' => ($index + 1) * 75, 'max_count' => ($index + 1) * 150, 'drop_chance' => 100];
                }
                $chest->itemHasItems()->sync($loot);
                $mapImage = ShareItem::where('type', 'scroll')->whereNotNull('image')->first()?->getRawOriginal('image');
                $map = new ShareItem([
                    'name' => ['ru' => $ru.' карта сокровищ', 'uk' => $uk.' карта скарбів'],
                    'description' => ['ru' => $clueRu, 'uk' => $clueUk],
                    'type' => 'treasure_map', 'rarity' => $rarities[$index], 'image' => $mapImage,
                    'is_active' => true, 'is_sell' => false, 'is_give' => false, 'is_auction_sellable' => false,
                    'is_clan_warehouse_allowed' => false, 'is_stackable' => false, 'is_weight' => true, 'is_use' => false, 'is_droppable' => true, 'is_slot_usable' => true, 'price' => 0,
                ]);
                $this->save($map, ['name', 'description']);
                $config = new TreasureMapConfig([
                    'code' => $code, 'share_item_id' => $map->id, 'reward_share_item_id' => $chest->id, 'reputation_id' => $reputation->id,
                    'map_id' => null, 'required_reputation' => $required, 'reputation_reward' => $rewardPoints,
                    'search_duration_seconds' => $seconds, 'success_chance_percent' => $chance,
                    'clue' => ['ru' => $clueRu, 'uk' => $clueUk], 'is_active' => true,
                ]);
                $config->save();
                $this->command?->info("Карта #{$map->id}; шкатулка #{$chest->id}; репутация #{$reputation->id}.");
            }
        });
        $this->call(TreasureMapFragmentsSeeder::class);
    }

    private function save(Model $model, array $fields): void
    {
        foreach ($fields as $field) {
            if (Schema::hasColumn($model->getTable(), $field.'_legacy')) {
                $model->setAttribute($field.'_legacy', $model->getTranslation($field, 'ru'));
            }
        }
        $model->save();
    }
}
