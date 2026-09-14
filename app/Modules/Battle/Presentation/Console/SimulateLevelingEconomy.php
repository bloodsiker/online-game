<?php

declare(strict_types=1);

namespace App\Modules\Battle\Presentation\Console;

use App\Modules\Battle\Application\Services\Combat\HitCalculator;
use App\Modules\Monster\Domain\Services\MonsterStatFormulas;
use App\Modules\Monster\Infrastructure\Persistence\Models\Monster;
use App\Modules\Player\Domain\Services\ExperienceCurve;
use Illuminate\Console\Command;

/**
 * Экономика прокачки 1→N: сколько золота фармит типовой билд, убивая монстров
 * своего уровня всю дорогу с 1 по N уровень — ориентир для цен на броню/оружие.
 *
 * Переиспользует ту же модель «типового билда своего тира» и ту же формулу
 * «типового моба», что и battle:simulate-pve (SimFighter, MonsterStatFormulas),
 * и ту же кривую опыта (ExperienceCurve::killsPerLevel) — одна кривая опыта на
 * весь проект, экономика не должна считаться по отдельной, рассинхронизированной
 * логике. Урон оружия по-прежнему из формулы SimFighter (минDmg()/maxDmg()
 * растут с уровнем) — калибровка Tier1 (1-20) в EquipmentStatFormulas прямо
 * помечена как несовпадающая с этой формулой, а выбор конкретного оружия на
 * 1-20 неоднозначен (несколько параллельных линеек на одних и тех же уровнях).
 * БРОНЯ же взята из реальных предметов админки — «Кожаный доспех/сапоги/
 * наручи/шлем/поножи/наплечники/щит» (см. GearedFighter::realArmorBonuses) —
 * единственная безальтернативная линейка брони на 1-20, поэтому именно она и
 * определяет цены на одежду, ради которых эта симуляция затевалась.
 *
 * Золото с монстра не зависит от билда/брони — только от опыта монстра,
 * поэтому при поражении (смерть) деньги не начисляются и не теряются (см.
 * ExperienceService::lostExpAfterDeath — штраф идёт по опыту, не по деньгам);
 * при ничьей (таймаут раундов) попытка просто повторяется. Итоговая сумма —
 * ВАЛОВЫЙ фарм, стоимость самой брони из него не вычитается.
 *
 *   php artisan battle:simulate-economy
 *   php artisan battle:simulate-economy --to-level=20 --trials=200
 */
class SimulateLevelingEconomy extends Command
{
    protected $signature = 'battle:simulate-economy
        {--from-level=1 : С какого уровня начинать карьеру (для анализа отдельного тира, напр. 20)}
        {--to-level=20 : До какого уровня вести карьеру (не включительно — считаем фарм from..N-1)}
        {--trials=100 : Число независимых прогонов на билд, для усреднения случайности}
        {--max-rounds=200 : Потолок раундов на один бой — не завершился, считаем ничьей и повторяем}';

    protected $description = 'Экономика прокачки: сколько золота фармится с 1 по N уровень, убивая мобов своего тира типовыми билдами';

    /** Те же архетипы и то же распределение бюджета стат, что и в battle:simulate-pve::playerBuilds() — один эталон билдов на весь проект. */
    private const ARCHETYPES = [
        'Танк' => [0.55, 0.1, 0.1, 0.25],
        'Уворот' => [0.2, 0.5, 0.1, 0.2],
        'Крит' => [0.2, 0.1, 0.5, 0.2],
        'Универсал' => [0.28, 0.28, 0.24, 0.2],
    ];

    /** Калибровка «типового моба» — те же параметры, что typicalMonsterForLevel() в battle:simulate-pve. */
    private const MONSTER_HP_MULTIPLIER = 1.4;

    private const MONSTER_ARMOR_MITIGATION = 0.10;

    private const MONSTER_DODGE_CRIT_CHANCE = 8.0;

    private const MONSTER_DAMAGE_PERCENT_OF_PLAYER_HP = 11.0;

    public function handle(HitCalculator $calc): int
    {
        $fromLevel = max(1, (int) $this->option('from-level'));
        $toLevel = max($fromLevel + 1, (int) $this->option('to-level'));
        $trials = max(1, (int) $this->option('trials'));
        $maxRounds = max(1, (int) $this->option('max-rounds'));

        $this->info(sprintf('Карьера %d → %d уровень, %d прогонов на билд, монстры своего тира (own-level typical).', $fromLevel, $toLevel, $trials));
        $this->newLine();

        $summaryRows = [];
        $referenceLevelGold = null; // «Универсал» — для детальной таблицы по уровням

        foreach (self::ARCHETYPES as $name => $split) {
            $trialTotals = [];
            /** @var array<int, list<int>> $perLevelGold уровень => золото за уровень по прогонам */
            $perLevelGold = [];

            for ($trial = 0; $trial < $trials; $trial++) {
                $goldTotal = 0;
                $killsTotal = 0;
                $deathsTotal = 0;

                for ($level = $fromLevel; $level < $toLevel; $level++) {
                    $budget = max(8, 8 * ($level - 1));
                    $gear = GearedFighter::realArmorBonuses($level);
                    $player = new GearedFighter(
                        strength: (int) round($budget * $split[0]),
                        agility: (int) round($budget * $split[1]),
                        intuition: (int) round($budget * $split[2]),
                        endurance: (int) round($budget * $split[3]),
                        level: $level,
                        armorBonus: $gear['armor'],
                        blockChanceBonus: $gear['block_chance'],
                        blockFlatBonus: $gear['block_flat'],
                        blockPercentBonus: $gear['block_percent'],
                    );

                    $monster = $this->typicalMonster($level);
                    $winsNeeded = ExperienceCurve::killsPerLevel($level);
                    $exp = MonsterStatFormulas::expReward($level, 1.0);
                    [$goldMin, $goldMax] = MonsterStatFormulas::moneyRange($exp, $level);

                    $levelGold = 0;
                    $wins = 0;
                    // Потолок попыток — страховка от бесконечного цикла на билде,
                    // который в принципе не может пробить моба (не должно случаться
                    // на откалиброванных архетипах, но лучше упасть с ошибкой, чем зависнуть).
                    $attempts = 0;
                    $maxAttempts = $winsNeeded * 50;

                    while ($wins < $winsNeeded && $attempts < $maxAttempts) {
                        $attempts++;
                        $result = $this->fight($calc, $player, $monster, (int) $monster->hp, $maxRounds);

                        if ($result === 'win') {
                            $wins++;
                            $killsTotal++;
                            $levelGold += mt_rand($goldMin, $goldMax);
                        } elseif ($result === 'lose') {
                            $deathsTotal++;
                        }
                        // 'draw' (таймаут раундов) — попытка просто повторяется, без начислений/потерь.
                    }

                    if ($attempts >= $maxAttempts) {
                        $this->warn(sprintf('%s: уровень %d не удалось пройти за %d попыток — билд слишком слаб для этого моба.', $name, $level, $maxAttempts));
                    }

                    $goldTotal += $levelGold;
                    $perLevelGold[$level][] = $levelGold;
                }

                $trialTotals[] = ['gold' => $goldTotal, 'kills' => $killsTotal, 'deaths' => $deathsTotal];
            }

            $golds = array_column($trialTotals, 'gold');
            $avgGold = array_sum($golds) / $trials;
            $avgKills = array_sum(array_column($trialTotals, 'kills')) / $trials;
            $avgDeaths = array_sum(array_column($trialTotals, 'deaths')) / $trials;

            $summaryRows[] = [
                $name,
                number_format($avgGold, 0, '.', ' '),
                number_format(min($golds), 0, '.', ' '),
                number_format(max($golds), 0, '.', ' '),
                number_format($avgKills, 1),
                number_format($avgDeaths, 1),
            ];

            if ($name === 'Универсал') {
                $referenceLevelGold = $perLevelGold;
            }
        }

        $this->table(
            ['Билд', 'Золото (среднее)', 'Золото (мин)', 'Золото (макс)', 'Убийств всего', 'Смертей всего'],
            $summaryRows,
        );

        if ($referenceLevelGold !== null) {
            $this->newLine();
            $this->info('Золото по уровням, билд «Универсал» (среднее по прогонам, накопительно):');

            $levelRows = [];
            $cumulative = 0;
            foreach ($referenceLevelGold as $level => $values) {
                $avgAtLevel = array_sum($values) / count($values);
                $cumulative += $avgAtLevel;
                $levelRows[] = [
                    $level,
                    number_format($avgAtLevel, 0, '.', ' '),
                    number_format($cumulative, 0, '.', ' '),
                ];
            }

            $this->table(['Уровень', 'Золото за уровень', 'Золото накопительно'], $levelRows);
        }

        return self::SUCCESS;
    }

    private function fight(HitCalculator $calc, SimFighter $player, Monster $monster, int $monsterMaxHp, int $maxRounds): string
    {
        $playerHp = $player->realHp();
        $monsterHpLeft = $monsterMaxHp;

        for ($round = 1; $round <= $maxRounds; $round++) {
            // Игрок атакует первым — как в реальном бою (AttackService → MonsterAttackService).
            $hit = $calc->hit($player, $monster, $player->minDmg(), $player->maxDmg());
            if (! $hit->isDodge()) {
                $monsterHpLeft -= $hit->getDamage();
            }
            if ($monsterHpLeft <= 0) {
                return 'win';
            }

            $counter = $calc->hit($monster, $player, (int) $monster->min_dmg, (int) $monster->max_dmg);
            if (! $counter->isDodge()) {
                $playerHp -= $counter->getDamage();
            }
            if ($playerHp <= 0) {
                return 'lose';
            }
        }

        return 'draw';
    }

    /** Тот же расчёт, что typicalMonsterForLevel() в battle:simulate-pve — общий эталон «моба своего тира». */
    private function typicalMonster(int $level): Monster
    {
        $hp = MonsterStatFormulas::hp($level, self::MONSTER_HP_MULTIPLIER);
        $armor = MonsterStatFormulas::armorForMitigation($level, self::MONSTER_ARMOR_MITIGATION);
        $dodge = MonsterStatFormulas::rawStatForChance($level, self::MONSTER_DODGE_CRIT_CHANCE);
        $critical = MonsterStatFormulas::rawStatForChance($level, self::MONSTER_DODGE_CRIT_CHANCE);
        [$minDmg, $maxDmg] = MonsterStatFormulas::damageRange($level, self::MONSTER_DAMAGE_PERCENT_OF_PLAYER_HP);

        return new Monster([
            'lvl' => $level,
            'hp' => $hp,
            'min_dmg' => $minDmg,
            'max_dmg' => $maxDmg,
            'armor' => $armor,
            'dodge' => $dodge,
            'critical' => $critical,
            'magic_resistance' => $level,
        ]);
    }
}

/**
 * SimFighter + броня с реальных стартовых предметов админки (не формула).
 * Оружие/статы-от-стата остаются как у SimFighter — меняется только источник
 * брони и блока щитом.
 */
final class GearedFighter extends SimFighter
{
    public function __construct(
        int $strength,
        int $agility,
        int $intuition,
        int $endurance,
        int $level,
        private readonly int $armorBonus = 0,
        private readonly int $blockChanceBonus = 0,
        private readonly int $blockFlatBonus = 0,
        private readonly int $blockPercentBonus = 0,
    ) {
        parent::__construct($strength, $agility, $intuition, $endurance, $level);
    }

    public function getArmor(): int
    {
        return parent::getArmor() + $this->armorBonus;
    }

    public function getBlockChance(): int
    {
        return $this->blockChanceBonus;
    }

    public function getBlockFlat(): int
    {
        return $this->blockFlatBonus;
    }

    public function getBlockPercent(): int
    {
        return $this->blockPercentBonus;
    }

    /**
     * Броня/блок с реальных предметов админки, ПО СЛОТАМ (тир1 «Кожаный...»,
     * тир2 «Мамонт») — id и уровень-требование взяты из share_item_requirements
     * (type=level), значения статов — из share_item_stats:
     *
     *   слот         тир1 (lvl → armor)        тир2 (lvl → armor)
     *   armor        77  доспех       1 → 2     150 Нагрудник «Мамонт»  20 → 5
     *   shoes        80  сапоги       2 → 1     153 Сапоги «Мамонт»     22 → 5
     *   forearm      84  наручи       4 → 2     156 Рукавицы «Мамонт»   25 → 6
     *   helmet       89  шлем         7 → 3     159 Шлем «Мамонт»       29 → 7
     *   legging      95  поножи      10 → 4     162 Поножи «Мамонт»     34 → 8
     *   shoulder     102 наплечники  13 → 4     165 Наплечники «Мамонт» 39 → 9
     *   hand(щит)    110 щит         16 → 5     168 Щит «Мамонт»        44 → 10
     *                    (+block 27/10/50 — совпадает в обоих тирах)
     *   chain_armor  119 кольчуга    20 → 5     169 Кольчуга «Мамонт»   50 → 12
     *
     * Каждый слот — ОДИН предмет разом (тир2 заменяет тир1 в том же слоте, не
     * складывается с ним), поэтому на каждом слоте берётся значение САМОЙ
     * поздней разблокировки ≤ текущему уровню, а слоты между собой суммируются.
     * Оружие («Тесак Головореза»/«Кастет «Мамонт»» и параллельные линейки на
     * тех же уровнях) сюда намеренно не включено — см. докблок класса команды.
     *
     * @return array{armor: int, block_chance: int, block_flat: int, block_percent: int}
     */
    public static function realArmorBonuses(int $level): array
    {
        // слот => [уровень => статы], по возрастанию уровня
        $slots = [
            'armor' => [1 => ['armor' => 2], 20 => ['armor' => 5]],
            'shoes' => [2 => ['armor' => 1], 22 => ['armor' => 5]],
            'forearm' => [4 => ['armor' => 2], 25 => ['armor' => 6]],
            'helmet' => [7 => ['armor' => 3], 29 => ['armor' => 7]],
            'legging' => [10 => ['armor' => 4], 34 => ['armor' => 8]],
            'shoulder' => [13 => ['armor' => 4], 39 => ['armor' => 9]],
            'hand' => [
                16 => ['armor' => 5, 'block_chance' => 27, 'block_flat' => 10, 'block_percent' => 50],
                44 => ['armor' => 10, 'block_chance' => 27, 'block_flat' => 10, 'block_percent' => 50],
            ],
            'chain_armor' => [20 => ['armor' => 5], 50 => ['armor' => 12]],
        ];

        $total = ['armor' => 0, 'block_chance' => 0, 'block_flat' => 0, 'block_percent' => 0];
        foreach ($slots as $tiers) {
            $applicable = null;
            foreach ($tiers as $unlockLevel => $bonuses) {
                if ($level >= $unlockLevel) {
                    $applicable = $bonuses;
                }
            }
            if ($applicable === null) {
                continue;
            }
            foreach ($applicable as $stat => $value) {
                $total[$stat] += $value;
            }
        }

        return $total;
    }
}
