<?php

declare(strict_types=1);

namespace App\Modules\Battle\Presentation\Console;

use App\Modules\Battle\Application\Services\Combat\HitCalculator;
use App\Modules\Battle\Application\Services\Combat\MagicHitCalculator;
use App\Modules\Item\Infrastructure\Persistence\Models\Item;
use App\Modules\MagicSkill\Infrastructure\Persistence\Models\MagicSkill;
use App\Modules\Monster\Infrastructure\Persistence\Models\Monster;
use App\Modules\Player\Domain\Services\PlayerStatService;
use App\Modules\Player\Infrastructure\Persistence\Models\Player;
use App\Modules\Player\Infrastructure\Persistence\Models\PlayerSkill;
use App\Modules\Share\Domain\Enums\ShareItemType;
use App\Modules\Skill\Infrastructure\Persistence\Models\Skill;
use Illuminate\Console\Command;

/**
 * Бои реальных персонажей из базы (статы и шмот как есть, без эликсиров/пати)
 * против моба/босса. Фазы и регенерация босса учитываются.
 */
class SimulateRealPlayers extends Command
{
    /** @var list<array{pct: int, cd: int, max: int, group: string}> */
    private array $heals = [];

    protected $signature = 'battle:simulate-players {--monster= : ID моба/босса} {--min-level=1} {--max-level=200} {--fights=300} {--monster-mod= : Что-если, % к стату: hp:69,min_dmg:32,max_dmg:30} {--heal= : Лечилки в бою: «процент HP:откат в сек[:макс. использований[:группа]]», через запятую, напр. 30:25::p,40:40::p,35:15::f. Предметы одной группы после использования уходят на общий откат (период использованного)} {--round-seconds=2 : Секунд на ход — по ним считаются откаты лечилок} {--heal-costs-turn : Использование лечилки тратит ход (монстр бьёт, игрок не атакует) — что-если} {--heal-below=50 : Пить лечилку, когда HP ниже этого % от максимума}';

    protected $description = 'Бои реальных игроков из базы против моба/босса (статы и экипировка как есть)';

    public function handle(HitCalculator $physical, MagicHitCalculator $magic, PlayerStatService $stats): int
    {
        $monster = Monster::query()->with(['phases', 'mechanics'])->findOrFail((int) $this->option('monster'));
        foreach (array_filter(explode(',', (string) $this->option('monster-mod'))) as $pair) {
            [$stat, $pct] = array_map('trim', explode(':', $pair));
            $monster->{$stat} = (int) round($monster->{$stat} * (1 + (float) $pct / 100));
        }

        $this->info(sprintf('%s (%d ур.): HP %d, урон %d–%d, броня %d', $monster->name, $monster->lvl, $monster->hp, $monster->min_dmg, $monster->max_dmg, $monster->armor));

        $this->heals = [];
        foreach (array_filter(explode(',', (string) $this->option('heal'))) as $entry) {
            $parts = explode(':', $entry);
            $this->heals[] = [
                'pct' => (int) $parts[0],
                'cd' => (int) ($parts[1] ?? 0),
                'max' => isset($parts[2]) && $parts[2] !== '' ? (int) $parts[2] : PHP_INT_MAX,
                'group' => $parts[3] ?? '',
            ];
        }
        usort($this->heals, fn (array $a, array $b): int => $b['pct'] <=> $a['pct']);

        $rows = [];
        $players = Player::query()->whereBetween('lvl', [(int) $this->option('min-level'), (int) $this->option('max-level')])->orderBy('lvl')->get();
        foreach ($players as $player) {
            $sheet = $stats->resolve($player);
            $equip = $player->playerEquip;
            $hands = $this->hands($player, $sheet, $equip?->handLeft, $equip?->handRight);
            $spells = $this->spells($player);
            $magicSkill = (int) (PlayerSkill::query()->where('player_id', $player->id)->where('skill_id', Skill::SKILL_MAGIC_ID)->value('lvl') ?? 0);

            $wins = 0;
            $rounds = [];
            $left = [];
            for ($i = 0; $i < (int) $this->option('fights'); $i++) {
                [$won, $r, $hpLeft] = $this->fight($physical, $magic, $sheet, $monster, $hands, $spells, $magicSkill);
                if ($won) {
                    $wins++;
                    $rounds[] = $r;
                    $left[] = $hpLeft / max(1, $sheet->getHpMax());
                }
            }

            $rows[] = [
                $player->id, $player->lvl, $sheet->getHpMax(), $sheet->getArmor(), $sheet->getDodge(), $sheet->getCritical(),
                implode(' + ', array_map(fn (array $h): string => $h['min'].'–'.$h['max'].'(нав.'.$h['skill'].')', $hands)).($spells ? ' | маг: '.implode(', ', array_column($spells, 'name')) : ''),
                sprintf('%.0f%%', 100 * $wins / (int) $this->option('fights')),
                $rounds ? sprintf('%.1f', array_sum($rounds) / count($rounds)) : '-',
                $left ? sprintf('%.0f%%', 100 * array_sum($left) / count($left)) : '-',
            ];
        }

        $this->table(['id', 'Ур.', 'HP', 'Броня', 'Уворот', 'Крит', 'Оружие', 'Победа', 'Ходов', 'HP после'], $rows);

        return self::SUCCESS;
    }

    /** @return list<array{min: int, max: int, skill: int}> */
    private function hands(Player $player, object $sheet, ?Item $left, ?Item $right): array
    {
        $isWeapon = fn (?Item $item): bool => $item instanceof Item && $item->itemInfo->type === ShareItemType::WEAPON;
        $level = fn (?int $skillId): int => $skillId === null ? 0 : (int) (PlayerSkill::query()->where('player_id', $player->id)->where('skill_id', $skillId)->value('lvl') ?? 0);
        $hands = [];
        if ($isWeapon($left)) {
            $hands[] = ['min' => $sheet->getLeftHandMinDmg(), 'max' => $sheet->getLeftHandMaxDmg(), 'skill' => $level($left->itemInfo->skill_id)];
        }
        if ($isWeapon($right)) {
            $hands[] = ['min' => $sheet->getRightHandMinDmg(), 'max' => $sheet->getRightHandMaxDmg(), 'skill' => $level($right->itemInfo->skill_id)];
        }

        return $hands ?: [['min' => $sheet->getRightHandMinDmg(), 'max' => $sheet->getRightHandMaxDmg(), 'skill' => $level(Skill::SKILL_HAND_ID)]];
    }

    /** @return list<array{name: string, min: int, max: int, power: float, mana: int}> */
    private function spells(Player $player): array
    {
        return $player->activeMagicSkills()->get()
            ->filter(fn (MagicSkill $s): bool => $s->type === 'attack' || (is_object($s->type) && ($s->type->value ?? null) === 'attack'))
            ->map(fn (MagicSkill $s): array => [
                'name' => (string) $s->name, 'min' => (int) $s->min_damage, 'max' => (int) $s->max_damage,
                'power' => (float) $s->power_coefficient, 'mana' => (int) $s->mana_cost,
            ])
            ->sortByDesc('max')->values()->all();
    }

    /**
     * @param  list<array{min: int, max: int, skill: int}>  $hands
     * @param  list<array{name: string, min: int, max: int, power: float, mana: int}>  $spells
     * @return array{0: bool, 1: int, 2: int}
     */
    private function fight(HitCalculator $physical, MagicHitCalculator $magic, object $sheet, Monster $monster, array $hands, array $spells, int $magicSkill): array
    {
        $hp = $sheet->getHpMax();
        $mp = $sheet->getMpMax();
        $maxMonsterHp = (int) $monster->hp;
        $monsterHp = $maxMonsterHp;
        $dmgMod = 0.0;
        $phasesDone = [];
        $regenDone = [];
        $discount = HitCalculator::weaponSkillMultiplier($magicSkill);
        $isMagicMonster = $monster->usesMagicAttack();
        $hpMax = $sheet->getHpMax();
        $nextAt = array_fill(0, count($this->heals), 0);
        $groupAt = [];
        $uses = array_fill(0, count($this->heals), 0);
        $roundSeconds = max(1, (int) $this->option('round-seconds'));
        $healBelow = (int) $this->option('heal-below');

        for ($round = 1; $round <= 200; $round++) {
            $healedThisRound = false;
            // Лечилки — свободное действие (ход монстра не тратится), ограничены только откатом предмета.
            $now = ($round - 1) * $roundSeconds;
            foreach ($this->heals as $i => $heal) {
                if (100 * $hp >= $healBelow * $hpMax) {
                    break;
                }
                if ($now >= $nextAt[$i] && $uses[$i] < $heal['max'] && $now >= ($groupAt[$heal['group']] ?? 0)) {
                    $hp = min($hpMax, $hp + (int) ($hpMax * $heal['pct'] / 100));
                    $nextAt[$i] = $now + $heal['cd'];
                    if ($heal['group'] !== '') {
                        $groupAt[$heal['group']] = $now + $heal['cd'];
                    }
                    $uses[$i]++;
                    $healedThisRound = true;
                }
            }
            $skipAttack = $healedThisRound && $this->option('heal-costs-turn');

            $hits = [];
            $spell = null;
            foreach ($skipAttack ? [] : $spells as $candidate) {
                if ((int) ceil($candidate['mana'] / $discount) <= $mp) {
                    $spell = $candidate;
                    break;
                }
            }
            if ($skipAttack) {
                $hits = [];
            } elseif ($spell !== null) {
                $mp -= (int) ceil($spell['mana'] / $discount);
                $hit = $magic->hit($sheet, $monster, $spell['min'], $spell['max'], $spell['power'], $magicSkill);
                $hits = [$hit];
            } else {
                $hits = array_map(fn (array $h) => $physical->hit($sheet, $monster, $h['min'], $h['max'], $h['skill']), $hands);
            }
            foreach ($hits as $hit) {
                if (! $hit->isDodge()) {
                    $monsterHp -= $hit->getDamage();
                }
            }
            if ($monsterHp <= 0) {
                return [true, $round, $hp];
            }

            if ($monster->is_boss) {
                $pct = 100 * $monsterHp / max(1, $maxMonsterHp);
                foreach ($monster->phases as $phase) {
                    if (! isset($phasesDone[$phase->id]) && $pct <= (float) $phase->hp_threshold) {
                        $phasesDone[$phase->id] = true;
                        $dmgMod = (float) (($phase->stats_modifiers ?? [])['max_dmg'] ?? 0);
                    }
                }
                foreach ($monster->mechanics as $mech) {
                    if ($mech->is_active && $mech->mechanic_type?->value === 'regeneration' && ! isset($regenDone[$mech->id])
                        && $pct <= (float) $mech->trigger_hp_percent) {
                        $regenDone[$mech->id] = true;
                        $monsterHp = min($maxMonsterHp, $monsterHp + (int) ($maxMonsterHp * (($mech->config ?? [])['heal_percent'] ?? 10) / 100));
                    }
                }
            }

            $mMin = (int) round($monster->min_dmg * (1 + $dmgMod / 100));
            $mMax = (int) round($monster->max_dmg * (1 + $dmgMod / 100));
            $hit = $isMagicMonster
                ? $magic->hit($monster, $sheet, $mMin, $mMax, (float) $monster->magic_power_coefficient)
                : $physical->hit($monster, $sheet, $mMin, $mMax);
            if (! $hit->isDodge()) {
                $hp -= $hit->getDamage();
                $monsterHp -= $hit->getReflectedDamage();
            }
            if ($hp <= 0) {
                return [false, $round, 0];
            }
        }

        return [false, 200, $hp];
    }
}
