<?php

declare(strict_types=1);

namespace App\Modules\Battle\Presentation\Console;

use App\Modules\Battle\Application\Services\Combat\HitCalculator;
use App\Modules\Battle\Application\Services\Combat\MagicHitCalculator;
use App\Modules\Battle\Domain\Contracts\FightHitInterface;
use App\Modules\Battle\Domain\Enums\CombatClass;
use App\Modules\Monster\Infrastructure\Persistence\Models\Monster;
use App\Modules\Player\Domain\Services\PlayerStatFormulas;
use App\Modules\Share\Domain\Enums\ShareItemRequirementType;
use App\Modules\Share\Domain\Enums\ShareItemStatType;
use App\Modules\Share\Domain\Enums\ShareItemType;
use App\Modules\Share\Infrastructure\Persistence\Models\ShareItem;
use Illuminate\Console\Command;

/** PvE-аудит ростера на эталонных персонажах с предметами, реально существующими в БД. */
class SimulatePveRoster extends Command
{
    protected $signature = 'battle:simulate-pve-roster {--fights=500 : Боёв на профиль против каждого моба} {--max-level=80}';

    protected $description = 'Проверяет мобов на эталонных персонажах равного уровня и доступной экипировке';

    public function handle(HitCalculator $physical, MagicHitCalculator $magic): int
    {
        $fights = max(100, (int) $this->option('fights'));
        $items = ShareItem::query()
            ->whereIn('type', [ShareItemType::WEAPON, ShareItemType::ARMOR, ShareItemType::SHIELD])
            ->with(['stats', 'requirements'])
            ->get();
        $monsters = Monster::query()->whereBetween('lvl', [1, (int) $this->option('max-level')])->orderBy('lvl')->get();
        $profiles = ['Танк' => [0.55, 0.10, 0.10, 0.25, false], 'Уворот' => [0.20, 0.50, 0.10, 0.20, false], 'Крит' => [0.20, 0.10, 0.50, 0.20, false], 'Маг' => [0.00, 0.00, 0.00, 0.30, true]];
        $summary = array_fill_keys(array_keys($profiles), ['easy' => 0, 'normal' => 0, 'hard' => 0, 'fail' => 0]);
        $problems = [];

        foreach ($monsters as $monster) {
            foreach ($profiles as $name => [$str, $agi, $intu, $end, $isMage]) {
                $fighter = $this->fighter($monster->lvl, $str, $agi, $intu, $end, $isMage, $items);
                $wins = 0;
                for ($i = 0; $i < $fights; $i++) {
                    $wins += $this->fight($physical, $magic, $fighter, $monster, $isMage) ? 1 : 0;
                }
                $rate = 100 * $wins / $fights;
                $bucket = $rate >= 85 ? 'easy' : ($rate >= 55 ? 'normal' : ($rate >= 25 ? 'hard' : 'fail'));
                $summary[$name][$bucket]++;
                if ($bucket !== 'easy') {
                    $problems[] = [$monster->lvl, $monster->name, $name, sprintf('%.1f%%', $rate), $bucket];
                }
            }
        }

        $this->table(['Профиль', 'Лёгкие ≥85%', 'Норма 55–84%', 'Сложные 25–54%', 'Непроходимые <25%'], array_map(
            static fn (string $name) => [$name, $summary[$name]['easy'], $summary[$name]['normal'], $summary[$name]['hard'], $summary[$name]['fail']],
            array_keys($profiles),
        ));
        $this->table(['Ур.', 'Моб', 'Профиль', 'Победа', 'Оценка'], $problems);
        $this->info("Проверено мобов: {$monsters->count()}, боёв на пару: {$fights}. Экипировка выбирается из доступных предметов БД без усилений, камней и рун.");

        return self::SUCCESS;
    }

    private function fighter(int $level, float $strShare, float $agiShare, float $intuShare, float $endShare, bool $isMage, $items): RosterReferenceFighter
    {
        $budget = max(8, 8 * ($level - 1));
        $stats = ['strength' => max(1, (int) round($budget * $strShare)), 'agility' => max(1, (int) round($budget * $agiShare)), 'intuition' => max(1, (int) round($budget * $intuShare)), 'endurance' => max(1, (int) round($budget * $endShare)), 'intelligence' => $isMage ? max(3, (int) round($budget * .5)) : 1, 'wisdom' => $isMage ? max(2, (int) round($budget * .2)) : 1];
        $gear = [];
        foreach ($items->filter(fn (ShareItem $item) => $this->meetsRequirements($item, $level, $stats))->groupBy(fn (ShareItem $item) => ($item->slot?->value ?? 'none')) as $slotItems) {
            $gear[] = $slotItems->sortByDesc(fn (ShareItem $item) => $this->scoreItem($item, $isMage, $stats))->first();
        }
        $bonus = array_fill_keys(['armor', 'dodge', 'critical', 'endurance', 'intelligence', 'wisdom', 'magic_resistance', 'attack_min', 'attack_max'], 0);
        foreach ($gear as $item) foreach ($item->stats as $stat) {
            $key = match ($stat->stat_type) { ShareItemStatType::ARMOR => 'armor', ShareItemStatType::DODGE => 'dodge', ShareItemStatType::CRITICAL => 'critical', ShareItemStatType::ENDURANCE => 'endurance', ShareItemStatType::INTELLIGENCE => 'intelligence', ShareItemStatType::WISDOM => 'wisdom', ShareItemStatType::MAGIC_RESISTANCE => 'magic_resistance', ShareItemStatType::ATTACK_MIN => 'attack_min', ShareItemStatType::ATTACK_MAX => 'attack_max', default => null };
            if ($key !== null) $bonus[$key] += (int) $stat->value;
        }
        return new RosterReferenceFighter($level, $stats, $bonus, $isMage);
    }

    private function meetsRequirements(ShareItem $item, int $level, array $stats): bool
    {
        foreach ($item->requirements as $requirement) {
            if ($requirement->type === ShareItemRequirementType::LEVEL && $level < $requirement->min_value) return false;
            if ($requirement->type === ShareItemRequirementType::STAT && ($stats[$requirement->stat_key] ?? 0) < $requirement->min_value) return false;
            if ($requirement->type === ShareItemRequirementType::SKILL) return false;
        }
        return true;
    }

    private function scoreItem(ShareItem $item, bool $mage, array $stats): int
    {
        $score = 0;
        foreach ($item->stats as $stat) $score += match ($stat->stat_type) { ShareItemStatType::ATTACK_MIN, ShareItemStatType::ATTACK_MAX => $mage ? 0 : (int) $stat->value * 6, ShareItemStatType::INTELLIGENCE, ShareItemStatType::WISDOM, ShareItemStatType::MAGIC_RESISTANCE => $mage ? (int) $stat->value * 5 : (int) $stat->value, default => (int) $stat->value * 3 };
        return $score;
    }

    private function fight(HitCalculator $physical, MagicHitCalculator $magic, RosterReferenceFighter $fighter, Monster $monster, bool $isMage): bool
    {
        $hp = $fighter->hp; $monsterHp = (int) $monster->hp;
        [$spellMin, $spellMax] = match (true) {
            $fighter->getLevel() >= 55 && $fighter->getIntelligence() >= 90 => [30, 44],
            $fighter->getLevel() >= 20 && $fighter->getIntelligence() >= 35 => [12, 18],
            default => [4, 7],
        };
        for ($round = 0; $round < 200; $round++) {
            $hit = $isMage ? $magic->hit($fighter, $monster, $spellMin, $spellMax, .15) : $physical->hit($fighter, $monster, $fighter->minDamage, $fighter->maxDamage);
            if (! $hit->isDodge()) $monsterHp -= $hit->getDamage();
            if ($monsterHp <= 0) return true;
            $hit = $physical->hit($monster, $fighter, (int) $monster->min_dmg, (int) $monster->max_dmg);
            if (! $hit->isDodge()) $hp -= $hit->getDamage();
            if ($hp <= 0) return false;
        }
        return false;
    }
}

final class RosterReferenceFighter implements FightHitInterface
{
    public int $hp; public int $minDamage; public int $maxDamage;
    public function __construct(private int $level, private array $stats, private array $bonus, private bool $mage) {
        $this->hp = PlayerStatFormulas::DEFAULT_HP + PlayerStatFormulas::HP_PER_LEVEL * ($level - 1) + PlayerStatFormulas::HP_PER_ENDURANCE * max(0, $stats['endurance'] + $bonus['endurance'] - 1);
        $scale = 1 + PlayerStatFormulas::strengthDamagePercent($stats['strength'], $level) / 100;
        $this->minDamage = max(1, (int) floor((1 + $bonus['attack_min']) * $scale)); $this->maxDamage = max($this->minDamage, (int) floor((2 + $bonus['attack_max']) * $scale));
    }
    public function getLevel(): int { return $this->level; } public function getArmor(): int { return max(0, ($this->stats['strength'] - 1) + $this->bonus['armor']); } public function getDodge(): int { return max(0, ($this->stats['agility'] - 1) + $this->bonus['dodge']); } public function getCritical(): int { return max(0, ($this->stats['intuition'] - 1) + $this->bonus['critical']); } public function getIntelligence(): int { return $this->stats['intelligence'] + $this->bonus['intelligence']; } public function getMagicResistance(): int { return max(0, $this->stats['wisdom'] - 1 + $this->bonus['magic_resistance']); } public function getMagicAttack(): int { return 0; } public function getCombatClass(): CombatClass { return $this->mage ? CombatClass::TANK : (($this->stats['strength'] >= $this->stats['agility'] && $this->stats['strength'] >= $this->stats['intuition']) ? CombatClass::TANK : ($this->stats['agility'] >= $this->stats['intuition'] ? CombatClass::DODGE : CombatClass::CRIT)); } public function getClassShare(CombatClass $class): float { $total = max(1, $this->stats['strength'] + $this->stats['agility'] + $this->stats['intuition']); return match ($class) { CombatClass::TANK => $this->stats['strength'] / $total, CombatClass::DODGE => $this->stats['agility'] / $total, CombatClass::CRIT => $this->stats['intuition'] / $total }; } public function getCritDamage(): int { return (int) round(PlayerStatFormulas::CRIT_DAMAGE_BASE + PlayerStatFormulas::critDamageBonus($this->stats['intuition'], $this->level)); } public function getBlockChance(): int { return 0; } public function getBlockFlat(): int { return 0; } public function getBlockPercent(): int { return 0; }
}
