<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Skill;
use App\Modules\Item\Domain\Services\EquipmentStatFormulas;
use App\Modules\Player\Domain\Enums\PlayerStatKey;
use App\Modules\Share\Domain\Enums\ItemEffectValueType;
use App\Modules\Share\Domain\Enums\ItemRarity;
use App\Modules\Share\Domain\Enums\ShareItemRequirementType;
use App\Modules\Share\Domain\Enums\ShareItemSlot;
use App\Modules\Share\Domain\Enums\ShareItemStatType;
use App\Modules\Share\Domain\Enums\ShareItemType;
use App\Modules\Share\Infrastructure\Persistence\Models\ShareItem;
use App\Modules\Share\Infrastructure\Persistence\Models\ShareItemRequirement;
use App\Modules\Share\Infrastructure\Persistence\Models\ShareItemStat;
use Illuminate\Database\Seeder;

/**
 * Экипировка по тирам:
 *
 * ТИР1 (1-20): 9 предметов (по одному на слот) + 1 доп. апгрейд меча на 10
 * уровне («Полуторный меч», см. TIER1_WEAPON_UPGRADE_LEVEL) — итого 10.
 * ОДИНАКОВЫЕ для всех игроков (без вариантов по классу, это появляется
 * только в Тире2), все одного материала «Кожаный» (см. TIER1_MATERIAL_INDEX).
 * Слоты по-прежнему открываются постепенно на тех же чекпоинтах, что и мобы
 * (1,2,4,7,10,13,16,20 — см. RecalibrateLeveling и /admin/docs/battle):
 * каждый предмет требует свой уровень открытия, статы берутся из его же
 * чекпоинта в WEAPON_DAMAGE/ARMOR_PER_SLOT, без повторного пере-скина той же
 * вещи на более поздних чекпоинтах (было 44 — по 1 на слот × чекпоинт, стало
 * 9 — по 1 на слот). Единственное исключение — меч: он статичен на всех
 * 20 уровнях ломал баланс (см. TIER1_WEAPON_UPGRADE_LEVEL), поэтому получил
 * один доп. чекпоинт.
 *
 * ТИР2 (20-50): все 9 слотов уже открыты, дальше они по очереди (по одному
 * слоту на чекпоинт — те же чекпоинты, пропорционально растянутые на 20-50:
 * 20,22,25,29,34,39,44,50) заменяются на более сильную версию, и с этого
 * момента у КАЖДОГО защитного слота 3 варианта — Танк/Уворот/Крит: одинаковая
 * базовая броня, плюс вторичная стата под класс (Танк — ещё столько же брони,
 * Уворот — уворот, Крит — крит), плюс требование по стате гейтится под класс
 * варианта (Танк→сила, Уворот→ловкость, Крит→интуиция). Игрок сам выбирает
 * вариант под свой билд вместо одной вещи на всех.
 *
 * Оружие/щит — три РАЗНЫХ стиля боя, не просто три стата одной вещи:
 * Танк — меч + щит (OneHandWeaponStrategy, 1 удар, броня щита); Уворот — два
 * одинаковых меча (DualWieldStrategy, 2 удара за раунд, урон каждого меча
 * вдвое меньше — см. DUAL_WIELD_DAMAGE_SHARE); Крит — двуручный топор
 * (is_two_hand, блокирует вторую руку, 1 удар, но сильнее — см.
 * TWO_HAND_DAMAGE_MULTIPLIER). Щит поэтому существует только в Танк-варианте.
 *
 * Урон даёт ТОЛЬКО оружие — кулаки фиксированы на 1-2 (см. PlayerFactory) и
 * не растут, это осознанно. Броня Тира1 распределена поровну между всеми
 * слотами, открытыми на момент чекпоинта, от бюджета = 50% брони, которую
 * даёт чистая вкладка в силу у Танк-билда (см. SimFighter, battle:simulate).
 * Тир2 использует ту же формулу через EquipmentStatFormulas (там же
 * проверено, что она один-в-один воспроизводит уже проверенные симуляцией
 * числа Тира1 на уровне 10-20) — то есть шмот ощутимо усиливает, но не
 * отменяет важность статы.
 *
 * ВАЖНО: Тир1 проверен реальной симуляцией (battle:simulate-pve) на 100%
 * винрейт Танк-билда против Огра (20 lvl) — см. /admin/docs/battle. Тир2 —
 * первая формула-прикидка, симуляцией НЕ проверена: мобов выше 20 уровня
 * пока не существует, проверить можно будет только когда появятся мобы
 * 20-50 уровня. Голый персонаж (без предметов) остаётся на уроне кулаков 1-2
 * и не растёт без оружия — это уже верно само по себе.
 */
class StarterEquipmentSeeder extends Seeder
{
    private const LEVEL_CHECKPOINTS = [1, 2, 4, 7, 10, 13, 16, 20];

    /** Материал по индексу чекпоинта — мужской род (меч, доспех, шлем, щит) */
    private const MATERIAL_M = ['Тренировочный', 'Кожаный', 'Бронзовый', 'Железный', 'Стальной', 'Закалённый', 'Мифриловый', 'Драконий'];

    /** Женский род (кольчуга) */
    private const MATERIAL_F = ['Тренировочная', 'Кожаная', 'Бронзовая', 'Железная', 'Стальная', 'Закалённая', 'Мифриловая', 'Драконья'];

    /** Множественное число (сапоги, наручи, поножи, наплечники) */
    private const MATERIAL_PL = ['Тренировочные', 'Кожаные', 'Бронзовые', 'Железные', 'Стальные', 'Закалённые', 'Мифриловые', 'Драконьи'];

    /** Мин/макс урон оружия (бонус СВЕРХ кулаков 1-2) по чекпоинтам — см. класс-докблок */
    private const WEAPON_DAMAGE = [
        1 => [3, 5], 2 => [3, 6], 4 => [4, 7], 7 => [6, 9],
        10 => [9, 13], 13 => [10, 14], 16 => [12, 18], 20 => [16, 23],
    ];

    /** Броня на слот (одинаковая для всех открытых на этот момент слотов) по чекпоинтам */
    private const ARMOR_PER_SLOT = [
        1 => 2, 2 => 1, 4 => 2, 7 => 3, 10 => 4, 13 => 4, 16 => 5, 20 => 5,
    ];

    /**
     * slotKey => [уровень открытия, существительное, род (m/f/pl — для выбора материала),
     * ShareItemSlot, ShareItemType, гейт-стата требования].
     */
    private const SLOTS = [
        'weapon' => [1, 'меч', 'm', ShareItemSlot::HAND, ShareItemType::WEAPON, PlayerStatKey::STRENGTH],
        'armor' => [1, 'доспех', 'm', ShareItemSlot::ARMOR, ShareItemType::ARMOR, PlayerStatKey::STRENGTH],
        'shoes' => [2, 'сапоги', 'pl', ShareItemSlot::SHOES, ShareItemType::ARMOR, PlayerStatKey::AGILITY],
        'forearm' => [4, 'наручи', 'pl', ShareItemSlot::FOREARM, ShareItemType::ARMOR, PlayerStatKey::STRENGTH],
        'helmet' => [7, 'шлем', 'm', ShareItemSlot::HELMET, ShareItemType::ARMOR, PlayerStatKey::INTUITION],
        'legging' => [10, 'поножи', 'pl', ShareItemSlot::LEGGING, ShareItemType::ARMOR, PlayerStatKey::AGILITY],
        'shoulder' => [13, 'наплечники', 'pl', ShareItemSlot::SHOULDER, ShareItemType::ARMOR, PlayerStatKey::INTUITION],
        'shield' => [16, 'щит', 'm', ShareItemSlot::HAND, ShareItemType::SHIELD, PlayerStatKey::STRENGTH],
        'chain_armor' => [20, 'кольчуга', 'f', ShareItemSlot::CHAIN_ARMOR, ShareItemType::ARMOR, PlayerStatKey::STRENGTH],
    ];

    /**
     * Явные имена вместо композиции «материал + существительное» — только для
     * стартового меча (без этого был бы «Кожаный меч», как у остальных
     * Тир1-слотов).
     */
    private const NAME_OVERRIDES = [
        'weapon' => 'Тесак Головореза',
    ];

    /** Чекпоинты Тира2 — паттерн Тира1 (1,2,4,7,10,13,16,20), пропорционально растянутый на 20-50 */
    private const TIER2_LEVEL_CHECKPOINTS = [20, 22, 25, 29, 34, 39, 44, 50];

    /** slotKey => уровень чекпоинта Тира2, на котором этот слот получает апгрейд (один раз, не на каждом чекпоинте) */
    private const TIER2_SLOT_LEVEL = [
        'weapon' => 20,
        'armor' => 20,
        'shoes' => 22,
        'forearm' => 25,
        'helmet' => 29,
        'legging' => 34,
        'shoulder' => 39,
        'shield' => 44,
        'chain_armor' => 50,
    ];

    private const TIER2_MATERIAL_M = ['Гвардейский', 'Рыцарский', 'Латный', 'Осквернённый', 'Огненный', 'Ледяной', 'Штормовой', 'Императорский'];

    private const TIER2_MATERIAL_F = ['Гвардейская', 'Рыцарская', 'Латная', 'Осквернённая', 'Огненная', 'Ледяная', 'Штормовая', 'Императорская'];

    private const TIER2_MATERIAL_PL = ['Гвардейские', 'Рыцарские', 'Латные', 'Осквернённые', 'Огненные', 'Ледяные', 'Штормовые', 'Императорские'];

    /**
     * variantKey => [суффикс имени, стата-гейт требования (под класс), вторичная стата предмета].
     *
     * У Танка вторичная стата — ВЫНОСЛИВОСТЬ (не броня): раньше секонд-стата
     * тоже была ARMOR, из-за чего предмет показывал два ОТДЕЛЬНЫХ ряда
     * «Броня» в статах — неинформативно и выглядит как баг. Выносливость даёт
     * ту же величину бюджета (см. armorPerSlot), но отдельной, осмысленной
     * статой (+HP — живучесть, а не удвоение брони) — и уже полностью
     * подключена в PlayerStatService (primary-стат, HP_PER_ENDURANCE), в
     * отличие от Силы, которой потребовалась бы новая проводка (ShareItemStatType
     * не имеет кейса STRENGTH).
     */
    private const TIER2_VARIANTS = [
        'tank' => ['воина', PlayerStatKey::STRENGTH, ShareItemStatType::ENDURANCE],
        'dodge' => ['ловкости', PlayerStatKey::AGILITY, ShareItemStatType::DODGE],
        'crit' => ['охотника', PlayerStatKey::INTUITION, ShareItemStatType::CRITICAL],
    ];

    /**
     * Явные имена для брони Уворот-варианта (вместо композиции
     * «материал + существительное + ловкости») — тематический сет «Сумеречный».
     * Оружие (меч) сюда не входит — остаётся «{материал} меч ловкости».
     */
    private const DODGE_ARMOR_NAME_OVERRIDES = [
        'helmet' => 'Сумеречная маска',
        'shoulder' => 'Сумеречные наплечники',
        'forearm' => 'Сумеречные перчатки',
        'armor' => 'Сумеречная броня',
        'legging' => 'Сумеречные поножи',
        'shoes' => 'Сумеречные сапоги',
        'chain_armor' => 'Сумеречная кольчуга',
    ];

    /** Картинки для брони Уворот-варианта (тот же сет «Сумеречный») */
    private const DODGE_ARMOR_IMAGE_OVERRIDES = [
        'helmet' => '/img/resource/sumerechniy/start_lovk_9.gif',
        'shoulder' => '/img/resource/sumerechniy/start_lovk_5.gif',
        'forearm' => '/img/resource/sumerechniy/start_lovk_8.gif',
        'armor' => '/img/resource/sumerechniy/start_lovk_1.gif',
        'legging' => '/img/resource/sumerechniy/start_lovk_2.gif',
        'shoes' => '/img/resource/sumerechniy/start_lovk_3.gif',
        'chain_armor' => '/img/resource/sumerechniy/start_lovk_7.gif',
    ];

    /**
     * Явные имена для брони Крит-варианта (вместо композиции
     * «материал + существительное + охотника») — тематический сет «Палач».
     * Оружие (двуручный топор) сюда не входит — см. TIER2_AXE_NAME.
     */
    private const CRIT_ARMOR_NAME_OVERRIDES = [
        'helmet' => 'Маска палача',
        'shoulder' => 'Наплечники палача',
        'forearm' => 'Наручи палача',
        'armor' => 'Жилет палача',
        'legging' => 'Поножи палача',
        'shoes' => 'Сапоги палача',
        'chain_armor' => 'Рубаха палача',
    ];

    /** Картинки для брони Крит-варианта (тот же сет «Палач») */
    private const CRIT_ARMOR_IMAGE_OVERRIDES = [
        'helmet' => '/img/resource/palach/start_krit_8.gif',
        'shoulder' => '/img/resource/palach/start_krit_6.gif',
        'forearm' => '/img/resource/palach/start_krit_7.gif',
        'armor' => '/img/resource/palach/start_krit_1.gif',
        'legging' => '/img/resource/palach/start_krit_2.gif',
        'shoes' => '/img/resource/palach/start_krit_5.gif',
        'chain_armor' => '/img/resource/palach/start_krit_4.gif',
    ];

    /**
     * Явные имена для брони и щита Танк-варианта (вместо композиции
     * «материал + существительное + воина») — тематический сет «Мамонт».
     * Оружие (кастет) сюда не входит — см. TIER2_TANK_WEAPON_NAME.
     */
    private const TANK_ARMOR_NAME_OVERRIDES = [
        'helmet' => 'Шлем «Мамонт»',
        'shoulder' => 'Наплечники «Мамонт»',
        'forearm' => 'Рукавицы «Мамонт»',
        'armor' => 'Нагрудник «Мамонт»',
        'legging' => 'Поножи «Мамонт»',
        'shoes' => 'Сапоги «Мамонт»',
        'chain_armor' => 'Кольчуга «Мамонт»',
        'shield' => 'Щит «Мамонт»',
    ];

    /** Картинки для брони и щита Танк-варианта (тот же сет «Мамонт») */
    private const TANK_ARMOR_IMAGE_OVERRIDES = [
        'helmet' => '/img/resource/mamont/start_tank_9.gif',
        'shoulder' => '/img/resource/mamont/start_tank_7.gif',
        'forearm' => '/img/resource/mamont/start_tank_8.gif',
        'armor' => '/img/resource/mamont/start_tank_1.gif',
        'legging' => '/img/resource/mamont/start_tank_2.gif',
        'shoes' => '/img/resource/mamont/start_tank_6.gif',
        'chain_armor' => '/img/resource/mamont/start_tank_5.gif',
        'shield' => '/img/resource/mamont/start_tank_4.gif',
    ];

    /**
     * Оружие/щит различаются не только статой, но и самим стилем боя:
     * Танк — меч+щит (1 удар, броня щита), Уворот — два меча (дуал-вилд,
     * 2 удара за раунд — см. DualWieldStrategy), Крит — двуручный топор
     * (1 удар, но сильнее, взамен утраченной руки под щит).
     *
     * Было 0.5 (простое «поделить урон пополам») — при реальной проверке на
     * новых мобах 20-50 (см. game:create мобов 20-50) вскрылась системная
     * проблема: Уворот не вкладывает в силу → броня в разы ниже Танка
     * (~121 против ~269 на 50 lvl), и шанс уворота (~34%) не компенсирует
     * настолько бОльшую долю урона, что проходит по оставшимся попаданиям —
     * винрейт против Ледяного великана/Молодого дракона падал до 21-46% даже
     * с каноничным распределением стат (0.2/0.5/0.1/0.2). 0.75 закрывает это
     * на всех 7 новых мобах (96-100%) — проверено реальным шмотом+статами,
     * не только формулой.
     */
    private const DUAL_WIELD_DAMAGE_SHARE = 0.75;

    /** Двуручное оружие бьёт заметно сильнее одноручного — компенсирует потерю щита/второй руки */
    private const TWO_HAND_DAMAGE_MULTIPLIER = 1.6;

    /** Тир1 — единый материал «Кожаный» для всех 9 предметов (индекс в MATERIAL_M/F/PL) */
    private const TIER1_MATERIAL_INDEX = 1;

    /** slotKey => путь к картинке (подготовлены в public/img/resource/set_leather/) */
    private const TIER1_IMAGES = [
        'weapon' => '/img/resource/set_leather/leather_sword.gif',
        'armor' => '/img/resource/set_leather/leather_armor.gif',
        'shoes' => '/img/resource/set_leather/leather_shoes.gif',
        'forearm' => '/img/resource/set_leather/leather_forearm.gif',
        'helmet' => '/img/resource/set_leather/leather_helmet.gif',
        'legging' => '/img/resource/set_leather/leather_legging.gif',
        'shoulder' => '/img/resource/set_leather/leather_shoulder.gif',
        'shield' => '/img/resource/set_leather/leather_shield.gif',
        'chain_armor' => '/img/resource/set_leather/leather_chain_armor.png',
    ];

    /** Картинка для двуручного топора Тир2-Крит («Топор палача») */
    private const TIER2_AXE_IMAGE = '/img/resource/palach/start_krit_3.gif';

    /** Оружие Тир2-Танк («Кастет «Мамонт»») и Тир2-Уворот («Сумеречный Дайто») */
    private const TIER2_TANK_WEAPON_NAME = 'Кастет «Мамонт»';

    private const TIER2_TANK_WEAPON_IMAGE = '/img/resource/mamont/start_tank_3.gif';

    private const TIER2_DODGE_SWORD_NAME = 'Сумеречный Дайто';

    private const TIER2_DODGE_SWORD_IMAGE = '/img/resource/sumerechniy/start_lovk_6.gif';

    /**
     * Явное имя вместо композиции «материал + существительное + суффикс» —
     * единственный двуручный топор Тир2-Крит (открывается один раз на
     * чекпоинте 20, других чекпоинтов у оружия в Тир2 нет).
     */
    private const TIER2_AXE_NAME = 'Топор палача';

    /**
     * Блок щитом: часть входящего урона (flat + percent от урона) гасится
     * ПОЛНОСТЬЮ и в том же объёме отражается атакующему — см. HitCalculator::
     * applyShieldBlock. Числа откалиброваны симуляцией полного левелинга 1-20
     * (3 архетипа × 3 прогона): при щите, доступном на всём диапазоне, дают
     * ~10-15% смертей — тот же порядок риска, что и у дуал-вилда (см. обсуждение
     * баланса щита). ВАЖНО: порог шанса очень резкий (20%→30-40% смертей,
     * 33%→~5-8%) — при переносе на Тир2+/другую редкость менять шанс только
     * через повторную симуляцию, не экстраполяцией.
     */
    private const SHIELD_BLOCK_CHANCE = 27;

    private const SHIELD_BLOCK_FLAT = 10;

    private const SHIELD_BLOCK_PERCENT = 50;

    /**
     * Апгрейд оружия внутри Тира1 (только меч, остальные 8 предметов Тира1
     * не трогаются): урон меча в Тире1 был статичным на всех 20 уровнях, из-за
     * чего симуляция левелинга 1-20 показала ~75% смертей на бой начиная с
     * 4 уровня (Волк) — броня/HP/блок щита не лечат эту проблему в принципе,
     * т.к. дело не во входящем уроне, а в слишком долгом времени убийства
     * моба. Чекпоинт на 10 уровне (совпадает с открытием поножей и мобом
     * Кабан) с формульным уроном EquipmentStatFormulas::weaponDamage(10)=9-13
     * закрывает диапазон 10-19 полностью (0% смертей в симуляции) — осталась
     * только дыра на 4-9 уровне, где нет ни улучшенного оружия, ни щита (щит
     * открывается на 16). Общая смертность левелинга 1-20 упала с ~75% до ~21%.
     */
    private const TIER1_WEAPON_UPGRADE_LEVEL = 10;

    private const TIER1_WEAPON_UPGRADE_NAME = 'Полуторный меч';

    private const TIER1_WEAPON_UPGRADE_IMAGE = '/img/resource/set_leather/leather_sword_universal.gif';

    /** Оба меча Тира1 качают навык «Рубящее оружие» (id=3, см. Skill::all()) */
    private const TIER1_WEAPON_SKILL_ID = 3;

    /**
     * Щиты качают «Владение щитом» — отдельный навык, который начисляется
     * ЗА УСПЕШНЫЙ БЛОК, а не за каждый удар (см. MonsterAttackService::
     * gainShieldSkill). Ищется по имени, а не по id: навык создаётся либо
     * GenerateSeed::createSkills() на пустой базе, либо миграцией
     * add_shield_mastery_skill на живой — id в этих двух путях может разойтись.
     */
    private const SHIELD_SKILL_NAME = 'Владение щитом';

    /**
     * Опыт за блок выше оружейного (2), потому что блок срабатывает лишь в
     * block_chance случаев: 7 × 0.27 ≈ 1.9 за раунд — паритет с оружием.
     */
    private const SHIELD_SKILL_EXP = 7;

    /** Гейт по навыку только у Тир2-щита; Тир1-щит (16 lvl) свободен, иначе навык негде начать качать */
    private const TIER2_SHIELD_SKILL_REQUIREMENT = 79;

    /** Стартовое оружие («Тесак Головореза») даёт 1 опыт навыка за удар */
    private const TIER1_SKILL_EXP_STARTER = 1;

    /** Всё, что дальше стартового меча (Полуторный меч, Тир2), даёт 2 опыта навыка за удар */
    private const TIER1_SKILL_EXP_ADVANCED = 2;

    /**
     * Требование навыка для «Полуторный меч»: специально ВЫШЕ, чем навык,
     * который естественно накапливается к 10 уровню персонажа (~14 при
     * BASE_EXP=18 навыка, на СТАРТОВОМ мече — 1 опыт/удар, см.
     * SkillLevelRequirementSeeder) — гонка «докачай навык на мобах послабее»
     * вместо автоматического открытия вместе с уровнем. 18 даёт те же ~65%
     * доп. ударов сверх естественного темпа (BASE_EXP=18 учитывает, что
     * почти весь путь ПОСЛЕ этого гейта идёт на оружии с 2 опыта/удар).
     */
    private const TIER1_WEAPON_UPGRADE_SKILL_REQUIREMENT = 18;

    public function run(): void
    {
        $created = 0;

        foreach (self::SLOTS as $slotKey => [$unlockLevel, $noun, $gender, $slot, $type, $stat]) {
            $tierIndex = self::TIER1_MATERIAL_INDEX;

            if ($this->createItem($noun, $gender, $tierIndex, $unlockLevel, $slot, $type, $stat, self::TIER1_IMAGES[$slotKey], self::NAME_OVERRIDES[$slotKey] ?? null)) {
                $created++;
            }
        }

        if ($this->createTier1WeaponUpgrade()) {
            $created++;
        }

        $created += $this->runTier2();

        $this->command?->info("StarterEquipmentSeeder: создано предметов — {$created}");
    }

    private function createTier1WeaponUpgrade(): bool
    {
        $level = self::TIER1_WEAPON_UPGRADE_LEVEL;

        $item = ShareItem::firstOrCreate(
            ['name' => self::TIER1_WEAPON_UPGRADE_NAME],
            [
                'type' => ShareItemType::WEAPON,
                'slot' => ShareItemSlot::HAND,
                'rarity' => ItemRarity::COMMON,
                'price' => (int) round(50 * $level ** 1.5),
                'is_two_hand' => 0,
                'image' => self::TIER1_WEAPON_UPGRADE_IMAGE,
                'skill_id' => self::TIER1_WEAPON_SKILL_ID,
                'skill_exp' => self::TIER1_SKILL_EXP_ADVANCED,
            ]
        );

        if (! $item->wasRecentlyCreated) {
            return false;
        }

        [$min, $max] = EquipmentStatFormulas::weaponDamage($level);
        ShareItemStat::create(['share_item_id' => $item->id, 'stat_type' => ShareItemStatType::ATTACK_MIN, 'value' => $min, 'value_type' => ItemEffectValueType::FLAT]);
        ShareItemStat::create(['share_item_id' => $item->id, 'stat_type' => ShareItemStatType::ATTACK_MAX, 'value' => $max, 'value_type' => ItemEffectValueType::FLAT]);

        ShareItemRequirement::create(['share_item_id' => $item->id, 'type' => ShareItemRequirementType::LEVEL, 'min_value' => $level]);
        ShareItemRequirement::create([
            'share_item_id' => $item->id,
            'type' => ShareItemRequirementType::STAT,
            'stat_key' => PlayerStatKey::STRENGTH->value,
            'min_value' => max(1, (int) round($level * 0.6)),
        ]);
        ShareItemRequirement::create([
            'share_item_id' => $item->id,
            'type' => ShareItemRequirementType::SKILL,
            'skill_id' => self::TIER1_WEAPON_SKILL_ID,
            'min_value' => self::TIER1_WEAPON_UPGRADE_SKILL_REQUIREMENT,
        ]);

        return true;
    }

    private function createItem(
        string $noun,
        string $gender,
        int $tierIndex,
        int $level,
        ShareItemSlot $slot,
        ShareItemType $type,
        PlayerStatKey $stat,
        ?string $image = null,
        ?string $nameOverride = null,
    ): bool {
        $material = match ($gender) {
            'm' => self::MATERIAL_M[$tierIndex],
            'f' => self::MATERIAL_F[$tierIndex],
            'pl' => self::MATERIAL_PL[$tierIndex],
        };
        $name = $nameOverride ?? $material.' '.$noun;

        // Стартовый набор весь «Обычный» — редкость выше появится позже через дроп
        // (Необычный) и крафт/апгрейд (остальные), не как отдельный сид.
        $rarity = ItemRarity::COMMON;

        $item = ShareItem::firstOrCreate(
            ['name' => $name],
            [
                'type' => $type,
                'slot' => $slot,
                'rarity' => $rarity,
                'price' => (int) round(50 * $level ** 1.5),
                'is_two_hand' => 0,
                'image' => $image,
                'skill_id' => $this->skillIdFor($type),
                'skill_exp' => match ($type) {
                    ShareItemType::WEAPON => self::TIER1_SKILL_EXP_STARTER,
                    ShareItemType::SHIELD => self::SHIELD_SKILL_EXP,
                    default => null,
                },
            ]
        );

        if (! $item->wasRecentlyCreated) {
            return false;
        }

        if ($type === ShareItemType::WEAPON) {
            [$min, $max] = self::WEAPON_DAMAGE[$level];
            ShareItemStat::create(['share_item_id' => $item->id, 'stat_type' => ShareItemStatType::ATTACK_MIN, 'value' => $min, 'value_type' => ItemEffectValueType::FLAT]);
            ShareItemStat::create(['share_item_id' => $item->id, 'stat_type' => ShareItemStatType::ATTACK_MAX, 'value' => $max, 'value_type' => ItemEffectValueType::FLAT]);
        } else {
            ShareItemStat::create(['share_item_id' => $item->id, 'stat_type' => ShareItemStatType::ARMOR, 'value' => self::ARMOR_PER_SLOT[$level], 'value_type' => ItemEffectValueType::FLAT]);
        }

        if ($type === ShareItemType::SHIELD) {
            $this->addShieldBlockStats($item->id);
        }

        ShareItemRequirement::create(['share_item_id' => $item->id, 'type' => ShareItemRequirementType::LEVEL, 'min_value' => $level]);
        ShareItemRequirement::create([
            'share_item_id' => $item->id,
            'type' => ShareItemRequirementType::STAT,
            'stat_key' => $stat->value,
            'min_value' => max(1, (int) round($level * 0.6)),
        ]);

        return true;
    }

    /**
     * Навык предмета по его типу. Щит ищется по имени навыка, а не по константе-id:
     * «Владение щитом» создаётся либо GenerateSeed на пустой базе, либо миграцией
     * add_shield_mastery_skill на живой, и id в этих путях может разойтись.
     */
    private function skillIdFor(ShareItemType $type): ?int
    {
        return match ($type) {
            ShareItemType::WEAPON => self::TIER1_WEAPON_SKILL_ID,
            ShareItemType::SHIELD => Skill::where('name', self::SHIELD_SKILL_NAME)->value('id'),
            default => null,
        };
    }

    private function addShieldBlockStats(int $itemId): void
    {
        ShareItemStat::create(['share_item_id' => $itemId, 'stat_type' => ShareItemStatType::BLOCK_CHANCE, 'value' => self::SHIELD_BLOCK_CHANCE, 'value_type' => ItemEffectValueType::FLAT]);
        ShareItemStat::create(['share_item_id' => $itemId, 'stat_type' => ShareItemStatType::BLOCK_FLAT, 'value' => self::SHIELD_BLOCK_FLAT, 'value_type' => ItemEffectValueType::FLAT]);
        ShareItemStat::create(['share_item_id' => $itemId, 'stat_type' => ShareItemStatType::BLOCK_PERCENT, 'value' => self::SHIELD_BLOCK_PERCENT, 'value_type' => ItemEffectValueType::FLAT]);
    }

    private function runTier2(): int
    {
        $created = 0;
        $checkpointIndex = array_flip(self::TIER2_LEVEL_CHECKPOINTS);

        foreach (self::SLOTS as $slotKey => [, $noun, $gender, $slot, $type]) {
            $level = self::TIER2_SLOT_LEVEL[$slotKey];
            $tierIndex = $checkpointIndex[$level];

            foreach (self::TIER2_VARIANTS as $variantKey => [$suffix, $gateStat, $secondaryStat]) {
                // Щит — только для Танка: Уворот дерётся двумя мечами, Крит — двуручным топором
                if ($slotKey === 'shield' && $variantKey !== 'tank') {
                    continue;
                }

                [$variantNoun, $isTwoHand, $damageMultiplier, $image, $nameOverride] = $this->weaponVariantOverride($slotKey, $variantKey, $noun);

                if ($variantKey === 'dodge' && isset(self::DODGE_ARMOR_NAME_OVERRIDES[$slotKey])) {
                    $nameOverride = self::DODGE_ARMOR_NAME_OVERRIDES[$slotKey];
                    $image = self::DODGE_ARMOR_IMAGE_OVERRIDES[$slotKey];
                }

                if ($variantKey === 'crit' && isset(self::CRIT_ARMOR_NAME_OVERRIDES[$slotKey])) {
                    $nameOverride = self::CRIT_ARMOR_NAME_OVERRIDES[$slotKey];
                    $image = self::CRIT_ARMOR_IMAGE_OVERRIDES[$slotKey];
                }

                if ($variantKey === 'tank' && isset(self::TANK_ARMOR_NAME_OVERRIDES[$slotKey])) {
                    $nameOverride = self::TANK_ARMOR_NAME_OVERRIDES[$slotKey];
                    $image = self::TANK_ARMOR_IMAGE_OVERRIDES[$slotKey];
                }

                if ($this->createTier2Item($variantNoun, $gender, $tierIndex, $level, $slot, $type, $suffix, $gateStat, $secondaryStat, $isTwoHand, $damageMultiplier, $image, $nameOverride)) {
                    $created++;
                }
            }
        }

        return $created;
    }

    /** @return array{0: string, 1: bool, 2: float, 3: ?string, 4: ?string} */
    private function weaponVariantOverride(string $slotKey, string $variantKey, string $defaultNoun): array
    {
        if ($slotKey !== 'weapon') {
            return [$defaultNoun, false, 1.0, null, null];
        }

        return match ($variantKey) {
            'tank' => [$defaultNoun, false, 1.0, self::TIER2_TANK_WEAPON_IMAGE, self::TIER2_TANK_WEAPON_NAME],
            'dodge' => [$defaultNoun, false, self::DUAL_WIELD_DAMAGE_SHARE, self::TIER2_DODGE_SWORD_IMAGE, self::TIER2_DODGE_SWORD_NAME],
            'crit' => ['топор двуручный', true, self::TWO_HAND_DAMAGE_MULTIPLIER, self::TIER2_AXE_IMAGE, self::TIER2_AXE_NAME],
            default => [$defaultNoun, false, 1.0, null, null],
        };
    }

    private function createTier2Item(
        string $noun,
        string $gender,
        int $tierIndex,
        int $level,
        ShareItemSlot $slot,
        ShareItemType $type,
        string $variantSuffix,
        PlayerStatKey $gateStat,
        ShareItemStatType $secondaryStat,
        bool $isTwoHand = false,
        float $damageMultiplier = 1.0,
        ?string $image = null,
        ?string $nameOverride = null,
    ): bool {
        $material = match ($gender) {
            'm' => self::TIER2_MATERIAL_M[$tierIndex],
            'f' => self::TIER2_MATERIAL_F[$tierIndex],
            'pl' => self::TIER2_MATERIAL_PL[$tierIndex],
        };
        $name = $nameOverride ?? "{$material} {$noun} {$variantSuffix}";

        $item = ShareItem::firstOrCreate(
            ['name' => $name],
            [
                'type' => $type,
                'slot' => $slot,
                'rarity' => ItemRarity::COMMON,
                'price' => (int) round(80 * $level ** 1.5),
                'is_two_hand' => $isTwoHand ? 1 : 0,
                'image' => $image,
                'skill_id' => $this->skillIdFor($type),
                'skill_exp' => match ($type) {
                    ShareItemType::WEAPON => self::TIER1_SKILL_EXP_ADVANCED,
                    ShareItemType::SHIELD => self::SHIELD_SKILL_EXP,
                    default => null,
                },
            ]
        );

        if (! $item->wasRecentlyCreated) {
            return false;
        }

        $armorPerSlot = EquipmentStatFormulas::armorPerSlot($level);

        if ($type === ShareItemType::WEAPON) {
            [$min, $max] = EquipmentStatFormulas::weaponDamage($level);
            $min = max(1, (int) round($min * $damageMultiplier));
            $max = max($min + 1, (int) round($max * $damageMultiplier));
            ShareItemStat::create(['share_item_id' => $item->id, 'stat_type' => ShareItemStatType::ATTACK_MIN, 'value' => $min, 'value_type' => ItemEffectValueType::FLAT]);
            ShareItemStat::create(['share_item_id' => $item->id, 'stat_type' => ShareItemStatType::ATTACK_MAX, 'value' => $max, 'value_type' => ItemEffectValueType::FLAT]);
        } else {
            ShareItemStat::create(['share_item_id' => $item->id, 'stat_type' => ShareItemStatType::ARMOR, 'value' => $armorPerSlot, 'value_type' => ItemEffectValueType::FLAT]);
        }

        // Вторичная стата под класс варианта: Танк — выносливость (+HP), Уворот/Крит —
        // свои статы той же величины поверх базовой брони/урона.
        ShareItemStat::create(['share_item_id' => $item->id, 'stat_type' => $secondaryStat, 'value' => $armorPerSlot, 'value_type' => ItemEffectValueType::FLAT]);

        if ($type === ShareItemType::SHIELD) {
            // Тир2-щит существует только у Танка (см. weaponVariantOverride) — те же откалиброванные
            // числа Тира1, ПЕРЕПРОВЕРИТЬ симуляцией отдельно, когда появятся мобы 20-50 уровня.
            $this->addShieldBlockStats($item->id);

            $skillId = $this->skillIdFor($type);

            if ($skillId !== null) {
                ShareItemRequirement::create([
                    'share_item_id' => $item->id,
                    'type' => ShareItemRequirementType::SKILL,
                    'skill_id' => $skillId,
                    'min_value' => self::TIER2_SHIELD_SKILL_REQUIREMENT,
                ]);
            }
        }

        ShareItemRequirement::create(['share_item_id' => $item->id, 'type' => ShareItemRequirementType::LEVEL, 'min_value' => $level]);
        ShareItemRequirement::create([
            'share_item_id' => $item->id,
            'type' => ShareItemRequirementType::STAT,
            'stat_key' => $gateStat->value,
            'min_value' => max(1, (int) round($level * 0.6)),
        ]);

        return true;
    }
}
