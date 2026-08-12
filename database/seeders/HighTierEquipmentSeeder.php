<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\ItemRarity;
use App\Modules\Item\Domain\Services\EquipmentStatFormulas;
use App\Modules\Player\Domain\Enums\PlayerStatKey;
use App\Modules\Share\Domain\Enums\ItemEffectValueType;
use App\Modules\Share\Domain\Enums\ShareItemRequirementType;
use App\Modules\Share\Domain\Enums\ShareItemSlot;
use App\Modules\Share\Domain\Enums\ShareItemStatType;
use App\Modules\Share\Domain\Enums\ShareItemType;
use App\Modules\Share\Infrastructure\Persistence\Models\ShareItem;
use App\Modules\Share\Infrastructure\Persistence\Models\ShareItemRequirement;
use App\Modules\Share\Infrastructure\Persistence\Models\ShareItemStat;
use App\Modules\Structure\Shop\Infrastructure\Persistence\Models\ShopItem;
use Illuminate\Database\Seeder;

/**
 * ТИР3 (55-90) — продолжение Тира2 из StarterEquipmentSeeder.
 *
 * Мотивация: предметы с требованием по уровню заканчивались на 50 (кольчуги
 * Тира2) при кап-левеле игрока 100 — выше 50 надевать было нечего.
 *
 * Три сета продолжают ровно те же три архетипа Тира2, только под новыми
 * именами, чтобы игрок не пересобирал билд: «Мамонт»→«Титан» (Танк,
 * выносливость, гейт по силе), «Сумеречный»→«Призрачный» (Уворот, гейт по
 * ловкости), «Палач»→«Жнец» (Крит, гейт по интуиции). Стили боя те же —
 * одноручное+щит / дуал-вилд / двуручное, с теми же множителями урона.
 *
 * Раскладка — ОДНА волна: 8 слотов на сет, по одному примерно на каждые
 * 5 уровней (55,60,65,70,75,80,85,90), итого 24 предмета. Альтернативы (две
 * ступени 50-70/70-90 или полные комплекты каждые 10 уровней) отклонены:
 * вещь устаревала бы быстрее, чем окупается заточка (+15 даёт ×1.75, а рост
 * брони за все 40 уровней — ×1.83), то есть заточка обесценивалась бы.
 *
 * ВАЖНО: статы НЕ вбиты руками и не считаются собственной кривой — берутся
 * из EquipmentStatFormulas, как в Тире2. Своя кривая (lvl^1.5, дававшая 12→29
 * брони на слот вместо 12→22) была отклонена: она подняла бы долю брони от
 * шмота с заложенных ARMOR_BUDGET_SHARE=0.5 до ~0.67 от брони чистой вкладки
 * в силу, то есть шмот начал бы перевешивать статы — ровно то, чего
 * EquipmentStatFormulas избегает по своему докблоку.
 *
 * Симуляцией Тир3 НЕ проверен — по той же причине, что и Тир2: мобов выше
 * 50 уровня пока не существует. Проверять, когда появятся.
 *
 * @see StarterEquipmentSeeder Тир1 (1-20) и Тир2 (20-50)
 * @see EquipmentStatFormulas Формулы брони и урона от игровых констант
 */
class HighTierEquipmentSeeder extends Seeder
{
    /** «Магазин снаряжения» — там же продаётся Тир2 */
    private const SHOP_STRUCTURE_ID = 2;

    private const SHOP_CATEGORY_WEAPON = 1;

    private const SHOP_CATEGORY_ARMOR = 5;

    /** Все три сета уровня «Необычный» — на ступень выше Тира2 («Обычный») */
    private const RARITY = ItemRarity::UNCOMMON;

    /** Оружие открывается первым и единственный раз за волну — как в Тире2 */
    private const WEAPON_LEVEL = 55;

    /** Всё оружие качает «Рубящее оружие» (id=3), 2 опыта за удар — как в Тире2 */
    private const WEAPON_SKILL_ID = 3;

    private const WEAPON_SKILL_EXP = 2;

    /**
     * Множители урона по стилю боя — те же константы, что в Тире2
     * (DUAL_WIELD_DAMAGE_SHARE / TWO_HAND_DAMAGE_MULTIPLIER). Дуал бьёт дважды
     * за раунд, поэтому урон каждого клинка ниже; двуручное бьёт раз, но
     * сильнее — взамен утраченной руки под щит.
     */
    private const DUAL_WIELD_DAMAGE_SHARE = 0.75;

    private const TWO_HAND_DAMAGE_MULTIPLIER = 1.6;

    /** Доля уровня, которую предмет требует по гейт-стате — как в Тире2 */
    private const STAT_REQUIREMENT_SHARE = 0.6;

    /** slotKey => [уровень открытия, слот, тип] */
    private const SLOTS = [
        'weapon' => [self::WEAPON_LEVEL, ShareItemSlot::HAND, ShareItemType::WEAPON],
        'armor' => [60, ShareItemSlot::ARMOR, ShareItemType::ARMOR],
        'shoes' => [65, ShareItemSlot::SHOES, ShareItemType::ARMOR],
        'forearm' => [70, ShareItemSlot::FOREARM, ShareItemType::ARMOR],
        'helmet' => [75, ShareItemSlot::HELMET, ShareItemType::ARMOR],
        'legging' => [80, ShareItemSlot::LEGGING, ShareItemType::ARMOR],
        'shoulder' => [85, ShareItemSlot::SHOULDER, ShareItemType::ARMOR],
        'chain_armor' => [90, ShareItemSlot::CHAIN_ARMOR, ShareItemType::ARMOR],
    ];

    /** setKey => [вторичная стата, гейт-стата, двуручное ли оружие, множитель урона] */
    private const SETS = [
        'titan' => [ShareItemStatType::ENDURANCE, PlayerStatKey::STRENGTH, false, 1.0],
        'phantom' => [ShareItemStatType::DODGE, PlayerStatKey::AGILITY, false, self::DUAL_WIELD_DAMAGE_SHARE],
        'reaper' => [ShareItemStatType::CRITICAL, PlayerStatKey::INTUITION, true, self::TWO_HAND_DAMAGE_MULTIPLIER],
    ];

    /**
     * Имена заданы явно, а не композицией «материал + существительное»:
     * у трёх сетов разный род и разная лексика, композиция дала бы уродливые
     * гибриды вроде «Призрачный кольчуга».
     */
    private const NAMES = [
        'titan' => [
            'weapon' => 'Молот «Титан»',
            'armor' => 'Нагрудник «Титан»',
            'shoes' => 'Сапоги «Титан»',
            'forearm' => 'Наручи «Титан»',
            'helmet' => 'Шлем «Титан»',
            'legging' => 'Поножи «Титан»',
            'shoulder' => 'Наплечники «Титан»',
            'chain_armor' => 'Кольчуга «Титан»',
        ],
        'phantom' => [
            'weapon' => 'Призрачный клинок',
            'armor' => 'Призрачная броня',
            'shoes' => 'Призрачные сапоги',
            'forearm' => 'Призрачные перчатки',
            'helmet' => 'Призрачная маска',
            'legging' => 'Призрачные поножи',
            'shoulder' => 'Призрачные наплечники',
            'chain_armor' => 'Призрачная кольчуга',
        ],
        'reaper' => [
            'weapon' => 'Коса жнеца',
            'armor' => 'Жилет жнеца',
            'shoes' => 'Сапоги жнеца',
            'forearm' => 'Наручи жнеца',
            'helmet' => 'Маска жнеца',
            'legging' => 'Поножи жнеца',
            'shoulder' => 'Наплечники жнеца',
            'chain_armor' => 'Рубаха жнеца',
        ],
    ];

    public function run(): void
    {
        $created = 0;

        foreach (self::SETS as $setKey => [$secondaryStat, $gateStat, $isTwoHand, $damageMultiplier]) {
            foreach (self::SLOTS as $slotKey => [$level, $slot, $type]) {
                $isWeapon = $type === ShareItemType::WEAPON;

                $created += (int) $this->createItem(
                    name: self::NAMES[$setKey][$slotKey],
                    level: $level,
                    slot: $slot,
                    type: $type,
                    secondaryStat: $secondaryStat,
                    gateStat: $gateStat,
                    isTwoHand: $isWeapon && $isTwoHand,
                    damageMultiplier: $isWeapon ? $damageMultiplier : 1.0,
                );
            }
        }

        $this->command?->info("HighTierEquipmentSeeder: создано предметов — {$created}");
    }

    private function createItem(
        string $name,
        int $level,
        ShareItemSlot $slot,
        ShareItemType $type,
        ShareItemStatType $secondaryStat,
        PlayerStatKey $gateStat,
        bool $isTwoHand,
        float $damageMultiplier,
    ): bool {
        $isWeapon = $type === ShareItemType::WEAPON;

        $item = ShareItem::firstOrCreate(
            ['name' => $name],
            [
                'type' => $type,
                'slot' => $slot,
                'rarity' => self::RARITY,
                'price' => $this->price($level),
                'is_two_hand' => $isTwoHand ? 1 : 0,
                // Картинки не задаются: ассетов для Тира3 нет, проставляются отдельно
                'image' => null,
                'skill_id' => $isWeapon ? self::WEAPON_SKILL_ID : null,
                'skill_exp' => $isWeapon ? self::WEAPON_SKILL_EXP : null,
            ]
        );

        if (! $item->wasRecentlyCreated) {
            return false;
        }

        $statValue = EquipmentStatFormulas::armorPerSlot($level);

        if ($isWeapon) {
            $this->addWeaponDamage(itemId: $item->id, level: $level, multiplier: $damageMultiplier);
        } else {
            $this->addStat(itemId: $item->id, statType: ShareItemStatType::ARMOR, value: $statValue);
        }

        // Вторичная стата под архетип — той же величины, что броня слота (как в Тире2)
        $this->addStat(itemId: $item->id, statType: $secondaryStat, value: $statValue);

        ShareItemRequirement::create([
            'share_item_id' => $item->id,
            'type' => ShareItemRequirementType::LEVEL,
            'min_value' => $level,
        ]);

        ShareItemRequirement::create([
            'share_item_id' => $item->id,
            'type' => ShareItemRequirementType::STAT,
            'stat_key' => $gateStat->value,
            'min_value' => max(1, (int) round($level * self::STAT_REQUIREMENT_SHARE)),
        ]);

        $this->addToShop(itemId: $item->id, level: $level, isWeapon: $isWeapon);

        return true;
    }

    private function addWeaponDamage(int $itemId, int $level, float $multiplier): void
    {
        [$min, $max] = EquipmentStatFormulas::weaponDamage($level);

        $min = max(1, (int) round($min * $multiplier));
        $max = max($min + 1, (int) round($max * $multiplier));

        $this->addStat(itemId: $itemId, statType: ShareItemStatType::ATTACK_MIN, value: $min);
        $this->addStat(itemId: $itemId, statType: ShareItemStatType::ATTACK_MAX, value: $max);
    }

    private function addStat(int $itemId, ShareItemStatType $statType, int $value): void
    {
        ShareItemStat::create([
            'share_item_id' => $itemId,
            'stat_type' => $statType,
            'value' => $value,
            'value_type' => ItemEffectValueType::FLAT,
        ]);
    }

    private function addToShop(int $itemId, int $level, bool $isWeapon): void
    {
        ShopItem::firstOrCreate(
            [
                'structure_id' => self::SHOP_STRUCTURE_ID,
                'share_item_id' => $itemId,
            ],
            [
                'share_structure_category_id' => $isWeapon ? self::SHOP_CATEGORY_WEAPON : self::SHOP_CATEGORY_ARMOR,
                'price' => $this->price($level),
                'sort_order' => $level,
            ]
        );
    }

    /** Та же ценовая формула, что у Тира2 — сходится с БД до единицы на 20 и 50 уровне */
    private function price(int $level): int
    {
        return (int) round(80 * $level ** 1.5);
    }
}
