<?php

declare(strict_types=1);

namespace App\Console\Seeders;

use App\Modules\Skill\Infrastructure\Persistence\Models\Skill;
use App\Modules\Item\Infrastructure\Persistence\Models\Item;
use App\Modules\Location\Infrastructure\Persistence\Models\Location;
use App\Modules\Monster\Infrastructure\Persistence\Models\Monster;
use App\Modules\Share\Domain\Enums\ItemEffectType;
use App\Modules\Share\Domain\Enums\ItemEffectValueType;
use App\Modules\Share\Domain\Enums\ItemRarity;
use App\Modules\Share\Domain\Enums\ShareItemSlot;
use App\Modules\Share\Domain\Enums\ShareItemStatType;
use App\Modules\Share\Domain\Enums\ShareItemType;
use App\Modules\Share\Infrastructure\Persistence\Models\ShareItem;
use App\Modules\Share\Infrastructure\Persistence\Models\ShareItemEffect;
use App\Modules\Share\Infrastructure\Persistence\Models\ShareRecipe;
use App\Modules\Structure\Blacksmith\Domain\Enums\RuneRarity;
use App\Modules\Structure\Blacksmith\Domain\Enums\UpgradeScrollType;
use App\Modules\Structure\Infrastructure\Persistence\Models\Structure;
use App\Modules\User\Infrastructure\Persistence\Models\User;
use Illuminate\Console\Command;

class ItemsSeeder
{
    private Location $location1;

    private Location $location2;

    private Monster $monster1;

    private Monster $monster2;

    private Skill $skill;

    private Skill $skill2;

    private User $user1;

    public function __construct(private readonly Command $command) {}

    public function run(
        Location $location1,
        Location $location2,
        Monster $monster1,
        Monster $monster2,
        Skill $skill,
        Skill $skill2,
        User $user1,
    ): void {
        $this->location1 = $location1;
        $this->location2 = $location2;
        $this->monster1 = $monster1;
        $this->monster2 = $monster2;
        $this->skill = $skill;
        $this->skill2 = $skill2;
        $this->user1 = $user1;

        $this->createItems();
        $this->createRecipeItems();
        $this->createBoxItems();
        $this->createRunes();
    }

    /**
     * По одной руне на каждую механическую редкость RuneRarity (см. её
     * градацию statCount/multiplier/passiveChance). Пул статов не ограничен
     * (rune_stat_pool = null) — все руны катают статы из полного
     * RuneService::STAT_POOL, а редкость определяет только сложность
     * добычи руны, количество статов и их силу. Картинки добавляются
     * вручную через админку.
     */
    private function createRunes(): void
    {
        $createRune = function (string $name, string $description, ItemRarity $rarity, RuneRarity $runeRarity, int $price): void {
            $sItem = new ShareItem;
            $sItem->type = ShareItemType::RUNE;
            $sItem->rarity = $rarity;
            $sItem->rune_rarity = $runeRarity;
            $sItem->price = $price;
            $sItem->name = $name;
            $sItem->description = $description;
            $sItem->save();
        };

        $createRune('Руна пробуждения', 'Простая руна, пробуждающая скрытый потенциал оружия или экипировки.', ItemRarity::COMMON, RuneRarity::COMMON, 500);
        $createRune('Руна закалки', 'Закалённая руна с более сильными статами, чем у руны пробуждения.', ItemRarity::UNCOMMON, RuneRarity::UNCOMMON, 1500);
        $createRune('Руна прозрения', 'Редкая руна, способная заметно усилить снаряжение.', ItemRarity::RARE, RuneRarity::RARE, 4000);
        $createRune('Руна возрождения', 'Эпическая руна с шансом пробудить в предмете пассивный навык.', ItemRarity::EPIC, RuneRarity::EPIC, 10000);
        $createRune('Руна вознесения', 'Легендарная руна с высоким шансом мощной пассивки.', ItemRarity::LEGENDARY, RuneRarity::LEGENDARY, 25000);
        $createRune('Руна вечности', 'Героическая руна — вершина рунного мастерства.', ItemRarity::HEROIC, RuneRarity::HEROIC, 60000);
    }

    public function seedTradingShopFood(Structure $tradingShop, int $foodCategoryId): void
    {
        $createFoodItem = function (string $name, string $description, ItemRarity $rarity, int $healPercent, int $price, int $sortOrder) use ($tradingShop, $foodCategoryId): void {
            $sItem = new ShareItem;
            $sItem->type = ShareItemType::EAT;
            $sItem->rarity = $rarity;
            $sItem->price = $price;
            $sItem->name = $name;
            $sItem->description = $description;
            $sItem->save();

            $sItem->effects()->create([
                'effect_type' => ItemEffectType::HEAL_HP,
                'value' => $healPercent,
                'value_type' => ItemEffectValueType::PERCENT,
            ]);

            $tradingShop->shopItems()->create([
                'share_item_id' => $sItem->id,
                'share_structure_category_id' => $foodCategoryId,
                'price' => $price,
                'sort_order' => $sortOrder,
            ]);
        };

        // Обычная еда — быстрый перекус, восстанавливает 15% HP
        $createFoodItem('Груша', 'Сочная спелая груша.', ItemRarity::COMMON, 15, 5, 0);
        $createFoodItem('Виноград', 'Гроздь сладкого винограда.', ItemRarity::COMMON, 15, 5, 1);
        $createFoodItem('Сдобные булочки', 'Свежие сдобные булочки с хрустящей корочкой.', ItemRarity::COMMON, 15, 5, 2);
        $createFoodItem('Хлеб', 'Простой ржаной хлеб — надежный спутник в дороге.', ItemRarity::COMMON, 15, 5, 3);
        $createFoodItem('Мясной пирог', 'Сытный пирог с мясной начинкой.', ItemRarity::COMMON, 15, 5, 4);
        $createFoodItem('Окорок', 'Копченый окорок с аппетитной корочкой.', ItemRarity::COMMON, 15, 5, 5);

        // Необычная еда — восстанавливает 25% HP
        $createFoodItem('Кусок сыра', 'Выдержанный сыр с насыщенным вкусом.', ItemRarity::UNCOMMON, 25, 20, 6);
        $createFoodItem('Шашлык из мяса кодрагов', 'Ароматный шашлык из мяса кодрагов, приготовленный на углях.', ItemRarity::UNCOMMON, 25, 20, 7);
        $createFoodItem('Ножка индейки', 'Жареная индюшачья ножка — блюдо для настоящего воина.', ItemRarity::UNCOMMON, 25, 20, 8);

        // Редкая еда — восстанавливает 40% HP
        $createFoodItem('Заморский фрукт', 'Экзотический фрукт, привезенный издалека. Пахнет странствиями.', ItemRarity::RARE, 40, 60, 9);
        $createFoodItem('Запеченная рыба', 'Свежая рыба, запеченная с травами.', ItemRarity::RARE, 40, 60, 10);
        $createFoodItem('Черника', 'Горсть спелой лесной черники.', ItemRarity::RARE, 40, 60, 11);
    }

    /**
     * @return array{ancientCoin: ShareItem, waterSeal: ShareItem, windSeal: ShareItem, fireSeal: ShareItem}
     */
    public function seedExchangeResourceItems(): array
    {
        $sItem1 = new ShareItem;
        $sItem1->type = ShareItemType::RESOURCE;
        $sItem1->price = 10;
        $sItem1->name = 'Монета древности';
        $sItem1->description = 'Монета древности';
        $sItem1->image = '/img/resource/ancient_coin.gif';
        $sItem1->is_weight = false;
        $sItem1->save();

        $sItem2 = new ShareItem;
        $sItem2->type = ShareItemType::RESOURCE;
        $sItem2->price = 3;
        $sItem2->name = 'Синий Камень Печати';
        $sItem2->description = 'Синий Камень Печати';
        $sItem2->image = '/img/resource/water_rune.png';
        $sItem2->save();

        $sItem3 = new ShareItem;
        $sItem3->type = ShareItemType::RESOURCE;
        $sItem3->price = 5;
        $sItem3->name = 'Зеленый Камень Печати';
        $sItem3->description = 'Зеленый Камень Печати';
        $sItem3->image = '/img/resource/wind_rune.png';
        $sItem3->save();

        $sItem4 = new ShareItem;
        $sItem4->type = ShareItemType::RESOURCE;
        $sItem4->price = 10;
        $sItem4->name = 'Красный Камень Печати';
        $sItem4->description = 'Красный Камень Печати';
        $sItem4->image = '/img/resource/fire_rune.png';
        $sItem4->save();

        return [
            'ancientCoin' => $sItem1,
            'waterSeal' => $sItem2,
            'windSeal' => $sItem3,
            'fireSeal' => $sItem4,
        ];
    }

    private function createItems(): void
    {
        $sItem = new ShareItem;
        $sItem->type = ShareItemType::RESOURCE;
        $sItem->price = 10;
        $sItem->name = 'Коготь медведя';
        $sItem->description = 'Коготь медведя';
        $sItem->image = 'https://skazanie.com/img-item/e2cf53ab693662.jpg';
        $sItem->save();

        $sItem->monsters()->attach($this->monster1->id, ['drop_chance' => 20, 'min_count' => 1, 'max_count' => 1]);
        $sItem->monsters()->attach($this->monster2->id, ['drop_chance' => 20, 'min_count' => 1, 'max_count' => 1]);

        $item = new Item;
        $item->share_item_id = $sItem->id;
        $item->save();

        $this->location1->itemsOnLocation()->attach($item->id);

        $sItem2 = new ShareItem;
        $sItem2->type = ShareItemType::WEAPON;
        $sItem2->skill_id = $this->skill2->id;
        $sItem2->skill_lvl = 0;
        $sItem2->price = 10;
        $sItem2->name = 'Кинжал «Ночной бури»';
        $sItem2->description = 'Кинжал «Ночной бури»';
        $sItem2->slot = ShareItemSlot::HAND;
        $sItem2->image = '/img/resource/sword_1.jpg';
        $sItem2->save();

        $sItem2->stats()->createMany([
            ['stat_type' => ShareItemStatType::ATTACK_MIN, 'value' => 1, 'value_type' => ItemEffectValueType::FLAT],
            ['stat_type' => ShareItemStatType::ATTACK_MAX, 'value' => 3, 'value_type' => ItemEffectValueType::FLAT],
        ]);

        $sItem2->monsters()->attach($this->monster1->id, ['drop_chance' => 5, 'min_count' => 1, 'max_count' => 1]);
        $sItem2->monsters()->attach($this->monster2->id, ['drop_chance' => 5, 'min_count' => 1, 'max_count' => 1]);

        $item2 = new Item;
        $item2->share_item_id = $sItem2->id;
        $item2->save();

        $this->location2->itemsOnLocation()->attach($item2->id);

        $sItem3 = new ShareItem;
        $sItem3->type = ShareItemType::POTION;
        $sItem3->price = 150;
        $sItem3->name = 'Сухой паек';
        $sItem3->description = 'Сухой паек';
        $sItem3->count_use = 10;
        $sItem3->image = 'https://skazanie.com/img-item/53e82794f638.jpg';
        $sItem3->save();

        $item3 = new Item;
        $item3->share_item_id = $sItem3->id;
        $item3->count_use = $sItem3->count_use;
        $item3->save();

        $this->location2->itemsOnLocation()->attach($item3->id);

        $sItem4 = new ShareItem;
        $sItem4->type = ShareItemType::KEY;
        $sItem4->is_sell = 0;
        $sItem4->price = 0;
        $sItem4->name = 'Изумрудный ключ';
        $sItem4->description = 'Это большой ключ с изумрудным украшением.';
        $sItem4->image = '/img/resource/key.gif';
        $sItem4->is_weight = false;
        $sItem4->save();

        $item4 = new Item;
        $item4->share_item_id = $sItem4->id;
        $item4->count_use = $sItem4->count_use;
        $item4->save();

        $this->location1->itemsOnLocation()->attach($item4->id);

        $sItem5 = new ShareItem;
        $sItem5->type = ShareItemType::WEAPON;
        $sItem5->skill_id = $this->skill2->id;
        $sItem5->skill_lvl = 5;
        $sItem5->price = 300;
        $sItem5->name = 'Вакидзаси';
        $sItem5->description = 'Вакидзаси';
        $sItem5->slot = ShareItemSlot::HAND;
        $sItem5->image = '/img/resource/sword_3.gif';
        $sItem5->save();

        $sItem5->stats()->createMany([
            ['stat_type' => ShareItemStatType::ATTACK_MIN, 'value' => 2, 'value_type' => ItemEffectValueType::FLAT],
            ['stat_type' => ShareItemStatType::ATTACK_MAX, 'value' => 4, 'value_type' => ItemEffectValueType::FLAT],
        ]);

        $sItem5->monsters()->attach($this->monster1->id, ['drop_chance' => 5, 'min_count' => 1, 'max_count' => 1]);
        $sItem5->monsters()->attach($this->monster2->id, ['drop_chance' => 5, 'min_count' => 1, 'max_count' => 1]);

        $sItem6 = new ShareItem;
        $sItem6->type = ShareItemType::WEAPON;
        $sItem6->skill_id = $this->skill2->id;
        $sItem6->skill_lvl = 10;
        $sItem6->price = 1000;
        $sItem6->break_crystal = 1;
        $sItem6->name = 'Копеш';
        $sItem6->description = 'Копеш';
        $sItem6->slot = ShareItemSlot::HAND;
        $sItem6->image = 'https://skazanie.com/img-item/032cd678c770.jpg';
        $sItem6->save();

        $sItem6->stats()->createMany([
            ['stat_type' => ShareItemStatType::ATTACK_MIN, 'value' => 5, 'value_type' => ItemEffectValueType::FLAT],
            ['stat_type' => ShareItemStatType::ATTACK_MAX, 'value' => 8, 'value_type' => ItemEffectValueType::FLAT],
        ]);

        $item3 = new Item;
        $item3->share_item_id = $sItem6->id;
        $item3->count_use = $sItem6->count_use;
        $item3->save();

        $this->location1->itemsOnLocation()->attach($item3->id);
        $sItem6->monsters()->attach($this->monster1->id, ['drop_chance' => 20, 'min_count' => 1, 'max_count' => 1]);
        $sItem6->monsters()->attach($this->monster2->id, ['drop_chance' => 20, 'min_count' => 1, 'max_count' => 1]);

        $sItem7 = new ShareItem;
        $sItem7->type = ShareItemType::SHIELD;
        $sItem7->price = 100;
        $sItem7->name = 'Щит «Заступник небес»';
        $sItem7->description = 'Щит «Заступник небес»';
        $sItem7->slot = ShareItemSlot::HAND;
        $sItem7->image = '/img/resource/sheild_1.jpg';
        $sItem7->save();

        $sItem7->stats()->createMany([
            ['stat_type' => ShareItemStatType::ARMOR, 'value' => 14, 'value_type' => ItemEffectValueType::FLAT],
        ]);

        $sItem7->monsters()->attach($this->monster1->id, ['drop_chance' => 5, 'min_count' => 1, 'max_count' => 1]);
        $sItem7->monsters()->attach($this->monster2->id, ['drop_chance' => 5, 'min_count' => 1, 'max_count' => 1]);

        $sItem8 = new ShareItem;
        $sItem8->type = ShareItemType::ARMOR;
        $sItem8->price = 2000;
        $sItem8->break_crystal = 2;
        $sItem8->name = 'Кольчуга «Ночной бури»';
        $sItem8->description = 'Кольчуга «Ночной бури»';
        $sItem8->slot = ShareItemSlot::CHAIN_ARMOR;
        $sItem8->image = '/img/resource/chain_armor_1.jpg';
        $sItem8->save();

        $sItem8->stats()->createMany([
            ['stat_type' => ShareItemStatType::ARMOR, 'value' => 8, 'value_type' => ItemEffectValueType::FLAT],
        ]);

        $sItem8->monsters()->attach($this->monster1->id, ['drop_chance' => 5, 'min_count' => 1, 'max_count' => 1]);
        $sItem8->monsters()->attach($this->monster2->id, ['drop_chance' => 5, 'min_count' => 1, 'max_count' => 1]);

        $sItem9 = new ShareItem;
        $sItem9->type = ShareItemType::ARMOR;
        $sItem9->price = 24000;
        $sItem9->break_crystal = 24;
        $sItem9->name = 'Нагрудник «Ночной бури»';
        $sItem9->description = 'Нагрудник «Ночной бури»';
        $sItem9->slot = ShareItemSlot::ARMOR;
        $sItem9->image = '/img/resource/armor_1.jpg';
        $sItem9->save();

        $sItem9->stats()->createMany([
            ['stat_type' => ShareItemStatType::ARMOR, 'value' => 30, 'value_type' => ItemEffectValueType::FLAT],
        ]);

        $sItem9->monsters()->attach($this->monster1->id, ['drop_chance' => 5, 'min_count' => 1, 'max_count' => 1]);
        $sItem9->monsters()->attach($this->monster2->id, ['drop_chance' => 5, 'min_count' => 1, 'max_count' => 1]);

        $this->command->info('Create Items success');

        $sItem9 = new ShareItem;
        $sItem9->type = ShareItemType::ARMOR;
        $sItem9->price = 100;
        $sItem9->name = 'Шлем «Ночной бури»';
        $sItem9->description = 'Шлем «Ночной бури»';
        $sItem9->slot = ShareItemSlot::HELMET;
        $sItem9->image = '/img/resource/helm_1.jpg';
        $sItem9->save();

        $sItem9->stats()->createMany([
            ['stat_type' => ShareItemStatType::ARMOR, 'value' => 1, 'value_type' => ItemEffectValueType::FLAT],
        ]);

        $sItem9->monsters()->attach($this->monster1->id, ['drop_chance' => 5, 'min_count' => 1, 'max_count' => 1]);
        $sItem9->monsters()->attach($this->monster2->id, ['drop_chance' => 5, 'min_count' => 1, 'max_count' => 1]);

        $sItem10 = new ShareItem;
        $sItem10->type = ShareItemType::ARMOR;
        $sItem10->price = 90;
        $sItem10->name = 'Наручи «Ночной бури»';
        $sItem10->description = 'Наручи «Ночной бури»';
        $sItem10->slot = ShareItemSlot::GLOVES;
        $sItem10->image = '/img/resource/gloves_1.jpg';
        $sItem10->save();

        $sItem10->stats()->createMany([
            ['stat_type' => ShareItemStatType::ARMOR, 'value' => 2, 'value_type' => ItemEffectValueType::FLAT],
        ]);

        $sItem10->monsters()->attach($this->monster1->id, ['drop_chance' => 5, 'min_count' => 1, 'max_count' => 1]);
        $sItem10->monsters()->attach($this->monster2->id, ['drop_chance' => 5, 'min_count' => 1, 'max_count' => 1]);

        $sItem11 = new ShareItem;
        $sItem11->type = ShareItemType::ARMOR;
        $sItem11->price = 110;
        $sItem11->name = 'Сапоги «Ночной бури»';
        $sItem11->description = 'Сапоги «Ночной бури»';
        $sItem11->slot = ShareItemSlot::SHOES;
        $sItem11->image = '/img/resource/shoes.jpg';
        $sItem11->save();

        $sItem11->stats()->createMany([
            ['stat_type' => ShareItemStatType::ARMOR, 'value' => 1, 'value_type' => ItemEffectValueType::FLAT],
        ]);

        $sItem11->monsters()->attach($this->monster1->id, ['drop_chance' => 5, 'min_count' => 1, 'max_count' => 1]);
        $sItem11->monsters()->attach($this->monster2->id, ['drop_chance' => 5, 'min_count' => 1, 'max_count' => 1]);

        $sItem11 = new ShareItem;
        $sItem11->type = ShareItemType::ARMOR;
        $sItem11->price = 105;
        $sItem11->name = 'Легкий плащ';
        $sItem11->description = 'Легкий плащ';
        $sItem11->slot = ShareItemSlot::CLOAK;
        $sItem11->image = 'https://skazanie.com/img-item/13ed6f939925.jpg';
        $sItem11->save();

        $sItem11->stats()->createMany([
            ['stat_type' => ShareItemStatType::ARMOR, 'value' => 1, 'value_type' => ItemEffectValueType::FLAT],
        ]);

        $sItem11->monsters()->attach($this->monster1->id, ['drop_chance' => 5, 'min_count' => 1, 'max_count' => 1]);
        $sItem11->monsters()->attach($this->monster2->id, ['drop_chance' => 5, 'min_count' => 1, 'max_count' => 1]);

        $sIte12 = new ShareItem;
        $sIte12->type = ShareItemType::RESOURCE;
        $sIte12->price = 10;
        $sIte12->name = 'Слиток';
        $sIte12->description = 'Слиток';
        $sIte12->image = 'https://skazanie.com/img-item/b4b4a5eabf3578.jpg';
        $sIte12->save();

        $sIte12->monsters()->attach($this->monster1->id, ['drop_chance' => 30, 'min_count' => 1, 'max_count' => 5]);
        $sIte12->monsters()->attach($this->monster2->id, ['drop_chance' => 30, 'min_count' => 1, 'max_count' => 5]);

        $item12 = new Item;
        $item12->share_item_id = $sIte12->id;
        $item12->save();

        $this->location1->itemsOnLocation()->attach($item12->id, ['count' => 10]);

        $sIte13 = new ShareItem;
        $sIte13->type = ShareItemType::SCROLL;
        $sIte13->name = 'Сертификат «Новое имя»';
        $sIte13->description = 'Документ, подтверждающий ваше право воспользоваться услугой по смене игрового ника. Будьте внимательны! Новое имя должно быть уникально';
        $sIte13->image = '/img/resource/sert_rename.gif';
        $sIte13->is_weight = false;
        $sIte13->save();

        $sIte14 = new ShareItem;
        $sIte14->type = ShareItemType::SCROLL;
        $sIte14->name = 'Сертификат «Смена расы»';
        $sIte14->description = 'Позволяет один раз изменить расу.';
        $sIte14->image = '/img/resource/sert_obraz.gif';
        $sIte14->is_weight = false;
        $sIte14->save();

        $sIte15 = new ShareItem;
        $sIte15->type = ShareItemType::POTION;
        $sIte15->price = 1000;
        $sIte15->name = 'Эликсир жизни';
        $sIte15->description = 'После применения, этот эликсир восстанавливает 50% жизни';
        $sIte15->image = '/img/resource/life_red.gif';
        $sIte15->save();

        $sIteEffect15 = new ShareItemEffect;
        $sIteEffect15->share_item_id = $sIte15->id;
        $sIteEffect15->effect_type = ItemEffectType::HEAL_HP;
        $sIteEffect15->value = 50;
        $sIteEffect15->value_type = ItemEffectValueType::PERCENT;
        $sIteEffect15->save();

        $item15 = new Item;
        $item15->share_item_id = $sIte15->id;
        $item15->save();

        $this->user1->backpack()->attach($item15->id, ['equipped' => 0, 'count' => 200]);

        $sIte16 = new ShareItem;
        $sIte16->type = ShareItemType::POTION;
        $sIte16->price = 1500;
        $sIte16->name = 'Эликсир маны';
        $sIte16->description = 'После применения, этот эликсир восстанавливает 55% маны';
        $sIte16->image = '/img/resource/mp_red.gif';
        $sIte16->save();

        $sIteEffect16 = new ShareItemEffect;
        $sIteEffect16->share_item_id = $sIte16->id;
        $sIteEffect16->effect_type = ItemEffectType::HEAL_MP;
        $sIteEffect16->value = 55;
        $sIteEffect16->value_type = ItemEffectValueType::PERCENT;
        $sIteEffect16->save();

        $item16 = new Item;
        $item16->share_item_id = $sIte16->id;
        $item16->save();

        $this->user1->backpack()->attach($item16->id, ['equipped' => 0, 'count' => 200]);

        $sIte17 = new ShareItem;
        $sIte17->type = ShareItemType::BELT;
        $sIte17->price = 20000;
        $sIte17->name = 'Пояс титана';
        $sIte17->slot = ShareItemSlot::BELT;
        $sIte17->description = 'Пояс добавляет три слота для эликсиров, свитков и прочих вспомогательных эффектов';
        $sIte17->image = '/img/resource/bluebelt.gif';
        $sIte17->save();

        $sIte17->stats()->createMany([
            [
                'effect_type' => ShareItemStatType::BELT_SLOT,
                'value' => 3,
                'value_type' => ItemEffectValueType::FLAT,
            ],
        ]);

        $item17 = new Item;
        $item17->share_item_id = $sIte17->id;
        $item17->save();

        $this->user1->backpack()->attach($item17->id, ['equipped' => 0, 'count' => 1]);

        $sIte18 = new ShareItem;
        $sIte18->type = ShareItemType::BAG;
        $sIte18->price = 30000;
        $sIte18->name = 'Рюкзак путешественника';
        $sIte18->slot = ShareItemSlot::BAG;
        $sIte18->description = 'Кожаная сумка позволит вам взять с собой на 11 вещей больше';
        $sIte18->image = '/img/resource/bag2.gif';
        $sIte18->save();

        $sIte18->stats()->createMany([
            [
                'effect_type' => ShareItemStatType::BAG_SLOT,
                'value' => 11,
                'value_type' => ItemEffectValueType::FLAT,
            ],
        ]);

        $item18 = new Item;
        $item18->share_item_id = $sIte18->id;
        $item18->save();

        $this->user1->backpack()->attach($item18->id, ['equipped' => 0, 'count' => 1]);

        $sItem19 = new ShareItem;
        $sItem19->type = ShareItemType::SCROLL;
        $sItem19->price = 1000;
        $sItem19->name = 'Свиток заточки';
        $sItem19->description = 'Необходим для заточки предмета';
        $sItem19->image = '/img/resource/scroll_enchant.gif';
        $sItem19->upgrade_scroll_type = UpgradeScrollType::BASE;
        $sItem19->save();

        $item19 = new Item;
        $item19->share_item_id = $sItem19->id;
        $item19->save();

        $this->user1->backpack()->attach($item19->id, ['equipped' => 0, 'count' => 100]);

        $sItem19 = new ShareItem;
        $sItem19->type = ShareItemType::SCROLL;
        $sItem19->price = 5000;
        $sItem19->name = 'Свиток стабилизации';
        $sItem19->description = 'Предотвращает понижение уровня заточки при неудаче';
        $sItem19->image = '/img/resource/scroll_stabilizer.gif';
        $sItem19->upgrade_scroll_type = UpgradeScrollType::STABILIZER;
        $sItem19->save();

        $item19 = new Item;
        $item19->share_item_id = $sItem19->id;
        $item19->save();

        $this->user1->backpack()->attach($item19->id, ['equipped' => 0, 'count' => 100]);

        $sItem20 = new ShareItem;
        $sItem20->type = ShareItemType::SCROLL;
        $sItem20->price = 10000;
        $sItem20->name = 'Свиток защиты';
        $sItem20->description = 'Предотвращает уничтожение предмета при неудаче';
        $sItem20->image = '/img/resource/scroll_defense.gif';
        $sItem20->upgrade_scroll_type = UpgradeScrollType::PROTECTION;
        $sItem20->save();

        $item20 = new Item;
        $item20->share_item_id = $sItem20->id;
        $item20->save();

        $this->user1->backpack()->attach($item20->id, ['equipped' => 0, 'count' => 100]);

        $sItem21 = new ShareItem;
        $sItem21->type = ShareItemType::RUNE_KEY;
        $sItem21->rarity = ItemRarity::LEGENDARY;
        $sItem21->price = 15000;
        $sItem21->name = 'Рунный ключ';
        $sItem21->description = 'Редчайший ключ, открывающий дополнительный рунный слот в оружии или экипировке.';
        $sItem21->image = '/img/resource/key.gif';
        $sItem21->save();

        $item21 = new Item;
        $item21->share_item_id = $sItem21->id;
        $item21->save();

        $this->user1->backpack()->attach($item21->id, ['equipped' => 0, 'count' => 3]);

        $this->command->info('Create Items success');
    }

    private function createRecipeItems(): void
    {
        $user = User::find(1);

        $sItem1 = new ShareItem;
        $sItem1->type = ShareItemType::WEAPON;
        $sItem1->skill_id = $this->skill->id;
        $sItem1->skill_lvl = 50;
        $sItem1->price = 7000000;
        $sItem1->break_crystal = 70000;
        $sItem1->name = 'Кнут Архангела';
        $sItem1->description = 'Кнут Архангела';
        $sItem1->slot = ShareItemSlot::HAND;
        $sItem1->image = 'https://skazanie.com/img-item/8f5d6477954920.jpg';
        $sItem1->save();

        $sItem1->stats()->createMany([
            ['stat_type' => ShareItemStatType::ATTACK_MIN, 'value' => 35, 'value_type' => ItemEffectValueType::FLAT],
            ['stat_type' => ShareItemStatType::ATTACK_MAX, 'value' => 80, 'value_type' => ItemEffectValueType::FLAT],
        ]);

        $item1 = new Item;
        $item1->share_item_id = $sItem1->id;
        $item1->save();

        $sItem2 = new ShareItem;
        $sItem2->type = ShareItemType::RECIPE;
        $sItem2->price = 1000;
        $sItem2->name = 'Рецепт "Кнут Архангела"';
        $sItem2->description = 'Рецепт "Кнут Архангела"';
        $sItem2->image = '/img/resource/scroll_weapon.gif';
        $sItem2->save();

        $item2 = new Item;
        $item2->share_item_id = $sItem2->id;
        $item2->save();

        $shareRecipe = new ShareRecipe;
        $shareRecipe->share_item_id = $sItem2->id;
        $shareRecipe->kraft_item_id = $sItem1->id;
        $shareRecipe->percent = 95;
        $shareRecipe->save();

        $sItem3 = new ShareItem;
        $sItem3->type = ShareItemType::RESOURCE;
        $sItem3->price = 1000;
        $sItem3->name = 'Кристалл';
        $sItem3->description = 'Кристалл';
        $sItem3->image = '/img/resource/crystal_gold.gif';
        $sItem3->save();

        $item3 = new Item;
        $item3->share_item_id = $sItem3->id;
        $item3->save();

        $details1 = ShareItem::where('name', 'Коготь медведя')->first();
        $details2 = ShareItem::where('name', 'Слиток')->first();
        $shareRecipe->items()->attach($details1->id, ['count' => 2]);
        $shareRecipe->items()->attach($details2->id, ['count' => 1]);
        $shareRecipe->items()->attach($sItem3->id, ['count' => 100]);

        $user->backpack()->attach($item2->id, ['equipped' => 0]);
        $user->backpack()->attach($details1->id, ['equipped' => 0, 'count' => 2]);
        $user->backpack()->attach($item3->id, ['equipped' => 0, 'count' => 200]);
    }

    private function createBoxItems(): void
    {
        $sItem1 = new ShareItem;
        $sItem1->type = ShareItemType::CHEST;
        $sItem1->price = 0;
        $sItem1->break_crystal = 0;
        $sItem1->name = 'Сундук';
        $sItem1->description = 'Сундук';
        $sItem1->image = '/img/resource/chest.jpg';
        $sItem1->save();

        $details1 = ShareItem::find(3);
        $details2 = ShareItem::find(11);
        $details3 = ShareItem::find(14);

        $sItem1->itemHasItems()->attach($details1->id, ['min_count' => 1, 'max_count' => 1, 'drop_chance' => 70]);
        $sItem1->itemHasItems()->attach($details2->id, ['min_count' => 1, 'max_count' => 1, 'drop_chance' => 70]);
        $sItem1->itemHasItems()->attach($details3->id, ['min_count' => 1, 'max_count' => 3, 'drop_chance' => 70]);

        $item = new Item;
        $item->share_item_id = $sItem1->id;
        $item->save();

        $this->location1->itemsOnLocation()->attach($item->id);
    }
}
