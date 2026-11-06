<?php

declare(strict_types=1);

namespace App\Modules\Battle\Presentation\Console;

use App\Modules\Battle\Application\Services\Combat\HitCalculator;
use App\Modules\Battle\Application\Services\Combat\MagicHitCalculator;
use App\Modules\Battle\Domain\Contracts\FightHitInterface;
use App\Modules\Battle\Domain\Enums\CombatClass;
use App\Modules\Effect\Infrastructure\Persistence\Models\Effect;
use App\Modules\MagicSkill\Infrastructure\Persistence\Models\MagicSkill;
use App\Modules\Monster\Infrastructure\Persistence\Models\Monster;
use App\Modules\Player\Domain\Services\PlayerStatFormulas;
use App\Modules\Share\Domain\Enums\ShareItemRequirementType;
use App\Modules\Share\Infrastructure\Persistence\Models\ShareItem;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;

/**
 * PvE по тирам экипировки: персонаж ровно своего уровня в реальном сете из БД,
 * свободные очки разложены строго под требования надетых вещей (остаток — в
 * главную стату билда), против реальных не-боссов своего уровня ±1.
 */
class SimulateGearTiers extends Command
{
    protected $signature = 'battle:simulate-gear-tiers {--fights=300 : Боёв на пару билд/моб} {--path : Редкость растёт с уровнем (путь прокачки через апгрейды)} {--monster= : Только этот моб/босс по id (фазы и регенерация босса учитываются)} {--levels= : Свои уровни через запятую} {--monster-mod= : Что-если для мобов, % к стату: armor:-50,hp:-20,min_dmg:-20,max_dmg:-20} {--rarity= : Одна редкость для --levels} {--extra= : Что-если: бонус к стату билда, напр. Жнец:critical:20,Жнец:crit_damage:30; с % — процент: Титан:armor:10%} {--elixir= : Баффы эликсиров (слаги эффектов) всем билдам или билду: elixir_strength,Жнец:elixir_fury} {--weapon-scale= : Что-если: множитель атаки оружия билда, напр. Мамонт:0.88} {--weapon-step= : Что-если: шаг атаки оружия воинов по редкостям от вершины цепочки, напр. 2:1.29,3:1.26} {--tier= : Только один тир (1, 2, 3)} {--spell-dmg=1 : Множитель базового урона атакующих заклинаний (что-если, БД не меняется)} {--spell-power= : Подменить power_coefficient всех атакующих заклинаний (что-если)} {--spell-power-map= : Коэффициент по id заклинания, напр. 72:0.2,75:0.15 (что-если)}';

    protected $description = 'Бои реальных сетов T1–T3 против мобов своего уровня';

    private const FREE_PER_LEVEL = 5;

    /** Расовый прирост за уровень — Человек (нейтральная раса). */
    private const RACIAL = ['strength' => 0.8, 'agility' => 0.8, 'intuition' => 0.8, 'wisdom' => 0.8, 'intelligence' => 0.8, 'endurance' => 1.0];

    /** Путь прокачки: уровень → редкость, до которой игрок успел улучшить вещи. */
    private const PATH = [
        1 => [7 => 'common', 13 => 'uncommon', 20 => 'rare'],
        2 => [25 => 'common', 30 => 'uncommon', 36 => 'rare', 43 => 'epic', 50 => 'legendary'],
        3 => [60 => 'uncommon', 70 => 'rare', 80 => 'epic', 90 => 'legendary'],
    ];

    /** Навык «Колдовство» — гейт атакующих заклинаний и скидка на ману. */
    private const SORCERY_SKILL_ID = 27;

    /** @var list<array{name: string, min: int, max: int, power: float, mana: int, skill: int, intelligence: int, wisdom: int}>|null */
    private ?array $spellBook = null;

    public function handle(HitCalculator $physical, MagicHitCalculator $magic): int
    {
        $fights = max(50, (int) $this->option('fights'));
        $onlyTier = $this->option('tier') !== null ? (int) $this->option('tier') : null;

        foreach ($this->builds() as $tier => $tierBuilds) {
            if ($onlyTier !== null && $onlyTier !== $tier) {
                continue;
            }
            $rows = [];
            foreach ($tierBuilds as $build) {
                $pairs = [];
                if ($this->option('levels')) {
                    foreach (array_map('intval', explode(',', (string) $this->option('levels'))) as $level) {
                        $pairs[] = [$level, (string) ($this->option('rarity') ?: $build['rarities'][0])];
                    }
                } elseif ($this->option('path')) {
                    foreach (self::PATH[$tier] as $level => $rarity) {
                        $pairs[] = [$level, $rarity];
                    }
                } else {
                    foreach ($build['levels'] as $level) {
                        foreach ($build['rarities'] as $rarity) {
                            $pairs[] = [$level, $rarity];
                        }
                    }
                }
                foreach ($pairs as [$level, $rarity]) {
                    $build['tier'] = $tier;
                    $rows[] = $this->runBuild($physical, $magic, $build, $level, $rarity, $fights);
                }
            }
            $this->info("=== T{$tier} ===");
            $this->table(['Билд', 'Ур.', 'Редкость', 'Надето', 'Статы', 'HP', 'MP', 'Урон/ход', 'Мобов', 'Победа ср.', 'Худший моб', 'Ходов', 'HP после', 'MP/бой'], $rows);
        }

        $this->line('Без усилений/камней/рун/артефактов/зелий, раса Человек, навык владения = round(ур.×1.8), мана: старт боя с полной (10 + 3×(мудрость−1)), без регена в бою, кончилась — удар посохом; дуал-вилд не учитывается.');

        return self::SUCCESS;
    }

    /** @return array<int, list<array<string, mixed>>> */
    private function builds(): array
    {
        $t1 = ['common', 'rare'];
        $t2 = ['common', 'legendary'];
        $t3 = ['uncommon', 'legendary'];

        return [
            1 => [
                ['name' => 'Кожаный', 'set' => ['Кожан'], 'dump' => 'strength', 'mage' => false, 'levels' => [7, 13, 20], 'rarities' => $t1,
                    'weapons' => ['common' => [76, 172], 'rare' => [449, 458]]],
                ['name' => 'Пепельная Башня', 'set' => ['Пепельн'], 'dump' => 'intelligence', 'mage' => true, 'levels' => [7, 13, 20], 'rarities' => $t1,
                    'weapons' => ['common' => [389], 'rare' => [466]]],
            ],
            2 => [
                ['name' => 'Мамонт (булава+щит)', 'set' => ['Мамонт', 'мамонт'], 'dump' => 'strength', 'mage' => false, 'levels' => [25, 34, 50], 'rarities' => $t2,
                    'weapons' => ['common' => [654], 'legendary' => [694]]],
                ['name' => 'Сумеречная (кинжал)', 'set' => ['Сумеречн'], 'dump' => 'agility', 'mage' => false, 'levels' => [25, 34, 50], 'rarities' => $t2,
                    'weapons' => ['common' => [589], 'legendary' => [598]]],
                ['name' => 'Палач (меч)', 'set' => ['палача'], 'dump' => 'intuition', 'mage' => false, 'levels' => [25, 34, 50], 'rarities' => $t2,
                    'weapons' => ['common' => [519], 'legendary' => [547]]],
                ['name' => 'Всполох (посох)', 'set' => ['Всполох'], 'dump' => 'intelligence', 'mage' => true, 'levels' => [25, 34, 50], 'rarities' => $t2,
                    'weapons' => ['common' => [1062], 'legendary' => [1066]]],
                ['name' => 'Иней (посох)', 'set' => ['Иней'], 'dump' => 'intelligence', 'mage' => true, 'levels' => [25, 34, 50], 'rarities' => $t2,
                    'weapons' => ['common' => [610], 'legendary' => [644]]],
            ],
            3 => [
                ['name' => 'Титан (булава+щит)', 'set' => ['Титан'], 'dump' => 'endurance', 'mage' => false, 'levels' => [60, 75, 90], 'rarities' => $t3,
                    'weapons' => ['uncommon' => [1385], 'legendary' => [1388]]],
                ['name' => 'Призрачный (клинок)', 'set' => ['ризрачн'], 'dump' => 'agility', 'mage' => false, 'levels' => [60, 75, 90], 'rarities' => $t3,
                    'weapons' => ['uncommon' => [1780], 'legendary' => [1331]]],
                ['name' => 'Жнец (меч)', 'set' => ['жнеца'], 'dump' => 'intuition', 'mage' => false, 'levels' => [60, 75, 90], 'rarities' => $t3,
                    'weapons' => ['uncommon' => [1406], 'legendary' => [1409]]],
                ['name' => 'Архимаг (посох)', 'set' => ['Архимаг'], 'dump' => 'intelligence', 'mage' => true, 'levels' => [60, 75, 90], 'rarities' => $t3,
                    'weapons' => ['uncommon' => [1634], 'legendary' => [1637]]],
                ['name' => 'Астрал (посох)', 'set' => ['Астрал'], 'dump' => 'intelligence', 'mage' => true, 'levels' => [60, 75, 90], 'rarities' => $t3,
                    'weapons' => ['uncommon' => [1586], 'legendary' => [1589]]],
            ],
        ];
    }

    /** @param array<string, mixed> $build */
    private function runBuild(HitCalculator $physical, MagicHitCalculator $magic, array $build, int $level, string $rarity, int $fights): array
    {
        $gear = $this->gear($build, $level, $rarity);
        [$stats, $missing] = $this->allocate($build, $level, $gear);
        $extra = [];
        $percent = [];
        foreach (array_filter(explode(',', (string) $this->option('extra'))) as $triple) {
            [$who, $stat, $value] = array_map('trim', explode(':', $triple));
            if (str_contains($build['name'], $who)) {
                if (str_ends_with($value, '%')) {
                    $percent[$stat] = ($percent[$stat] ?? 0) + (float) rtrim($value, '%');
                } else {
                    $extra[$stat] = ($extra[$stat] ?? 0) + (int) $value;
                }
            }
        }
        foreach (array_filter(explode(',', (string) $this->option('elixir'))) as $entry) {
            $parts = array_map('trim', explode(':', $entry));
            [$who, $slug] = count($parts) === 2 ? $parts : ['', $parts[0]];
            if ($who !== '' && ! str_contains($build['name'], $who)) {
                continue;
            }
            foreach (Effect::query()->where('slug', $slug)->value('stat_modifiers') ?? [] as $mod) {
                $key = (string) ($mod['type'] ?? $mod['stat'] ?? '');
                if (! empty($mod['is_percent'])) {
                    $percent[$key] = ($percent[$key] ?? 0) + (float) $mod['value'];
                } else {
                    $extra[$key] = ($extra[$key] ?? 0) + (int) $mod['value'];
                }
            }
        }
        $fighter = new GearTierFighter($level, $stats, $gear, $extra, $percent);
        $skill = (int) round($level * 1.8);
        $spells = $build['mage'] ? $this->spells($stats, $skill) : [];
        $fighter->mpMax = $build['mage'] ? PlayerStatFormulas::DEFAULT_MP + PlayerStatFormulas::MP_PER_MUD * ($stats['wisdom'] - 1) : 0;

        $monsters = $this->option('monster')
            ? Monster::query()->with(['phases', 'mechanics'])->whereKey((int) $this->option('monster'))->get()
            : Monster::query()->where('is_boss', false)->whereBetween('lvl', [$level - 1, $level + 1])->get();
        foreach (array_filter(explode(',', (string) $this->option('monster-mod'))) as $pair) {
            [$stat, $pct] = array_map('trim', explode(':', $pair));
            foreach ($monsters as $m) {
                $m->{$stat} = (int) round($m->{$stat} * (1 + (float) $pct / 100));
            }
        }
        if ($monsters->isEmpty()) {
            $monsters = Monster::query()->where('is_boss', false)->orderByRaw('ABS(lvl - ?)', [$level])->limit(3)->get();
        }

        $rates = [];
        $rounds = [];
        $hpLeft = [];
        $dmgSamples = [];
        $mpSpent = [];
        foreach ($monsters as $monster) {
            $wins = 0;
            for ($i = 0; $i < $fights; $i++) {
                [$won, $r, $left, $dealt, $mpUsed] = $this->fight($physical, $magic, $fighter, $monster, $spells, $skill);
                $wins += $won ? 1 : 0;
                if ($won) {
                    $rounds[] = $r;
                    $hpLeft[] = $left / max(1, $fighter->hp);
                    $mpSpent[] = $mpUsed / max(1, $fighter->mpMax);
                }
                $dmgSamples[] = $dealt / max(1, $r);
            }
            $rates[$monster->name.' ('.$monster->lvl.')'] = 100 * $wins / $fights;
        }

        asort($rates);
        $worst = array_key_first($rates);
        $slots = count($gear);

        return [
            $build['name'],
            $level,
            $rarity,
            $slots.' шт.',
            $missing ? 'не хватает '.$missing : 'ок',
            $fighter->hp,
            $build['mage'] ? $fighter->mpMax : '-',
            (int) round(array_sum($dmgSamples) / max(1, count($dmgSamples))).($spells ? ' ('.$spells[0]['name'].')' : ''),
            count($rates),
            sprintf('%.0f%%', array_sum($rates) / max(1, count($rates))),
            sprintf('%s %.0f%%', $worst, $rates[$worst] ?? 0),
            $rounds ? sprintf('%.1f', array_sum($rounds) / count($rounds)) : '-',
            $hpLeft ? sprintf('%.0f%%', 100 * array_sum($hpLeft) / count($hpLeft)) : '-',
            $build['mage'] && $mpSpent ? sprintf('%.0f%%', 100 * array_sum($mpSpent) / count($mpSpent)) : '-',
        ];
    }

    /**
     * Броня сета нужной редкости (слоты, чей уровень уже открыт) + оружие
     * (последнее из списка, доступное по уровню) + щит сета, если есть.
     *
     * @param  array<string, mixed>  $build
     * @return Collection<int, ShareItem>
     */
    private function gear(array $build, int $level, string $rarity): Collection
    {
        $query = ShareItem::query()->with(['stats', 'requirements'])->where('rarity', $rarity)->whereIn('type', ['armor', 'shield']);
        $query->where(function ($q) use ($build): void {
            foreach ($build['set'] as $pattern) {
                $q->orWhere('name_legacy', 'like', '%'.$pattern.'%');
            }
        });
        $gear = $query->get()->filter(fn (ShareItem $item) => $this->levelOf($item) <= $level);
        if ($build['mage']) {
            $gear = $gear->reject(fn (ShareItem $item) => $item->type->value === 'shield');
        }
        $gear = $gear->groupBy(fn (ShareItem $item) => $item->slot?->value.'|'.$item->type->value)->map(fn ($g) => $g->first())->values();

        $weapon = ShareItem::query()->with(['stats', 'requirements'])->whereIn('id', $build['weapons'][$rarity] ?? $this->upgradedWeapons($build, $rarity))->get()
            ->filter(fn (ShareItem $item) => $this->levelOf($item) <= $level)
            ->sortBy(fn (ShareItem $item) => $this->levelOf($item))->last();

        if ($weapon !== null && ! $build['mage']) {
            $this->applyWeaponStep($weapon, (int) ($build['tier'] ?? 0));
            foreach (array_filter(explode(',', (string) $this->option('weapon-scale'))) as $pair) {
                [$who, $factor] = array_map('trim', explode(':', $pair));
                if (str_contains($build['name'], $who)) {
                    foreach ($weapon->stats as $stat) {
                        if (in_array($stat->stat_type->value, ['attack_min', 'attack_max'], true)) {
                            $stat->value = (int) round($stat->value * (float) $factor);
                        }
                    }
                }
            }
        }

        return $weapon ? $gear->push($weapon) : $gear;
    }

    /**
     * Оружие нужной редкости по апгрейд-цепочке от оружия младшей заданной редкости.
     *
     * @param  array<string, mixed>  $build
     * @return list<int>
     */
    private function upgradedWeapons(array $build, string $rarity): array
    {
        $ids = [];
        foreach ($build['weapons'][array_key_first($build['weapons'])] as $id) {
            $item = ShareItem::query()->find($id);
            for ($step = 0; $item !== null && $item->rarity->value !== $rarity && $step < 6; $step++) {
                $item = $item->upgrade_to_share_item_id ? ShareItem::query()->find($item->upgrade_to_share_item_id) : null;
            }
            if ($item !== null) {
                $ids[] = (int) $item->id;
            }
        }

        return $ids;
    }

    /**
     * Что-если без записи в БД: атака = атака вершины апгрейд-цепочки / шаг^(шагов до вершины).
     */
    private function applyWeaponStep(ShareItem $weapon, int $tier): void
    {
        $steps = [];
        foreach (array_filter(explode(',', (string) $this->option('weapon-step'))) as $pair) {
            [$t, $step] = array_map('trim', explode(':', $pair));
            $steps[(int) $t] = (float) $step;
        }
        if (! isset($steps[$tier])) {
            return;
        }

        $top = $weapon;
        $distance = 0;
        while ($top->upgrade_to_share_item_id && $distance < 6) {
            $top = ShareItem::query()->with('stats')->find($top->upgrade_to_share_item_id);
            $distance++;
        }
        $divider = $steps[$tier] ** $distance;
        foreach ($weapon->stats as $stat) {
            $key = $stat->stat_type->value;
            if ($key === 'attack_min' || $key === 'attack_max') {
                $topValue = (int) ($top->stats->first(fn ($s) => $s->stat_type->value === $key)?->value ?? $stat->value);
                $stat->value = (int) round($topValue / $divider);
            }
        }
    }

    private function levelOf(ShareItem $item): int
    {
        return (int) ($item->requirements->firstWhere('type', ShareItemRequirementType::LEVEL)?->min_value ?? 1);
    }

    /**
     * @param  array<string, mixed>  $build
     * @param  Collection<int, ShareItem>  $gear
     * @return array{0: array<string, int>, 1: string}
     */
    private function allocate(array $build, int $level, Collection $gear): array
    {
        $stats = [];
        foreach (self::RACIAL as $key => $perLevel) {
            $stats[$key] = 1 + (int) floor($perLevel * ($level - 1));
        }
        $pool = self::FREE_PER_LEVEL * $level;

        $required = [];
        foreach ($gear as $item) {
            foreach ($item->requirements->where('type', ShareItemRequirementType::STAT) as $req) {
                $required[$req->stat_key] = max($required[$req->stat_key] ?? 0, (int) $req->min_value);
            }
        }
        $missing = [];
        foreach ($required as $key => $min) {
            $need = max(0, $min - $stats[$key]);
            $spend = min($need, $pool);
            $stats[$key] += $spend;
            $pool -= $spend;
            if ($stats[$key] < $min) {
                $missing[] = $key.' '.($min - $stats[$key]);
            }
        }
        $stats[$build['dump']] += $pool;

        return [$stats, implode(', ', $missing)];
    }

    /**
     * Атакующие заклинания из БД, доступные по «Колдовству» и базовым статам
     * (MagicSkillRequirementService проверяет статы без шмота), от сильного к слабому.
     * Стоимость уже со скидкой навыка, как в MagicCastGuard.
     *
     * @param  array<string, int>  $stats
     * @return list<array{name: string, min: int, max: int, power: float, mana: int, skill: int, intelligence: int, wisdom: int}>
     */
    private function spells(array $stats, int $skill): array
    {
        $this->spellBook ??= MagicSkill::query()->with('requirements')->where('type', 'attack')->get()
            ->map(function (MagicSkill $spell): array {
                $req = fn (string $type, ?string $key = null): int => (int) ($spell->requirements
                    ->first(fn ($r) => $r->type->value === $type && ($key === null || $r->stat_key === $key))?->min_value ?? 0);

                return [
                    'id' => (int) $spell->id,
                    'name' => (string) $spell->name,
                    'min' => (int) $spell->min_damage,
                    'max' => (int) $spell->max_damage,
                    'power' => (float) $spell->power_coefficient,
                    'mana' => (int) $spell->mana_cost,
                    'skill' => $req('skill'),
                    'intelligence' => $req('stat', 'intelligence'),
                    'wisdom' => $req('stat', 'wisdom'),
                ];
            })
            ->sortByDesc('max')->values()->all();

        $discount = HitCalculator::weaponSkillMultiplier($skill);
        $scale = (float) $this->option('spell-dmg');
        $power = $this->option('spell-power');
        $powerMap = [];
        foreach (array_filter(explode(',', (string) $this->option('spell-power-map'))) as $pair) {
            [$id, $coef] = array_map('trim', explode(':', $pair));
            $powerMap[(int) $id] = (float) $coef;
        }

        return array_values(array_map(
            fn (array $spell): array => [
                'mana' => (int) ceil($spell['mana'] / $discount),
                'min' => max(1, (int) round($spell['min'] * $scale)),
                'max' => max(1, (int) round($spell['max'] * $scale)),
                'power' => $powerMap[$spell['id']] ?? ($power !== null ? (float) $power : $spell['power']),
            ] + $spell,
            array_filter($this->spellBook, fn (array $spell): bool => $skill >= $spell['skill']
                && $stats['intelligence'] >= $spell['intelligence'] && $stats['wisdom'] >= $spell['wisdom']),
        ));
    }

    /**
     * Маг каждый ход кастует самое сильное заклинание, на которое хватает маны; маны нет — бьёт оружием.
     *
     * @param  list<array<string, mixed>>  $spells
     * @return array{0: bool, 1: int, 2: int, 3: int, 4: int}
     */
    private function fight(HitCalculator $physical, MagicHitCalculator $magic, GearTierFighter $fighter, Monster $monster, array $spells, int $skill): array
    {
        $hp = $fighter->hp;
        $mp = $fighter->mpMax;
        $maxMonsterHp = (int) $monster->hp;
        $dmgMod = 0.0;
        $phasesDone = [];
        $regenDone = [];
        $monsterHp = (int) $monster->hp;
        $dealt = 0;
        $isMagicMonster = $monster->usesMagicAttack();

        for ($round = 1; $round <= 200; $round++) {
            $spell = null;
            foreach ($spells as $candidate) {
                if ($candidate['mana'] <= $mp) {
                    $spell = $candidate;
                    break;
                }
            }
            if ($spell !== null) {
                $mp -= $spell['mana'];
            }
            $hit = $spell
                ? $magic->hit($fighter, $monster, $spell['min'], $spell['max'], $spell['power'], $skill)
                : $physical->hit($fighter, $monster, $fighter->minDamage, $fighter->maxDamage, $skill);
            if (! $hit->isDodge()) {
                $monsterHp -= $hit->getDamage();
                $dealt += $hit->getDamage();
            }
            if ($monsterHp <= 0) {
                return [true, $round, $hp, $dealt, $fighter->mpMax - $mp];
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
                ? $magic->hit($monster, $fighter, $mMin, $mMax, (float) $monster->magic_power_coefficient)
                : $physical->hit($monster, $fighter, $mMin, $mMax);
            if (! $hit->isDodge()) {
                $hp -= $hit->getDamage();
                $monsterHp -= $hit->getReflectedDamage();
            }
            if ($hp <= 0) {
                return [false, $round, 0, $dealt, $fighter->mpMax - $mp];
            }
        }

        return [false, 200, $hp, $dealt, $fighter->mpMax - $mp];
    }
}

/** Персонаж, собранный по тем же формулам, что PlayerStatService::buildSheet(). */
final class GearTierFighter implements FightHitInterface
{
    public int $hp;

    public int $mpMax = 0;

    public int $minDamage;

    public int $maxDamage;

    public int $wisdom;

    /** @var array<string, int> */
    private array $primary;

    /** @var array<string, int> */
    private array $item = [];

    /**
     * @param  array<string, int>  $base  первичные статы без шмота
     * @param  iterable<ShareItem>  $gear
     */
    /**
     * @param  array<string, int>  $extra  плоские бонусы к статам
     * @param  array<string, float>  $percent  процентные бонусы (баффы эликсиров): armor, hp_max, attack, magic_attack, critical, dodge, magic_resistance
     */
    public function __construct(private int $level, private array $base, iterable $gear, array $extra = [], private array $percent = [])
    {
        $this->item = $extra;
        foreach ($gear as $piece) {
            foreach ($piece->stats as $stat) {
                $key = $stat->stat_type->value;
                $this->item[$key] = ($this->item[$key] ?? 0) + (int) $stat->value;
            }
        }
        $this->primary = [];
        foreach (['strength', 'agility', 'intuition', 'wisdom', 'intelligence', 'endurance'] as $key) {
            $this->primary[$key] = $base[$key] + $this->bonus($key);
        }
        $this->wisdom = $this->primary['wisdom'];
        $this->hp = PlayerStatFormulas::DEFAULT_HP + PlayerStatFormulas::HP_PER_LEVEL * ($level - 1)
            + PlayerStatFormulas::HP_PER_ENDURANCE * max(0, $this->primary['endurance'] - 1) + $this->bonus('hp_max');
        $scale = 1 + PlayerStatFormulas::strengthDamagePercent((float) $this->primary['strength'], $level) / 100;
        $this->hp = $this->pct('hp_max', $this->hp);
        $this->minDamage = max(1, $this->pct('attack', (int) floor((1 + $this->bonus('attack_min')) * $scale)));
        $this->maxDamage = max($this->minDamage, $this->pct('attack', (int) floor((2 + $this->bonus('attack_max')) * $scale)));
    }

    private function bonus(string $key): int
    {
        return $this->item[$key] ?? 0;
    }

    private function pct(string $key, int $value): int
    {
        return (int) floor($value * (1 + ($this->percent[$key] ?? 0) / 100));
    }

    public function getLevel(): int
    {
        return $this->level;
    }

    public function getArmor(): int
    {
        return $this->pct('armor', max(0, $this->primary['strength'] - 1) + $this->bonus('armor'));
    }

    public function getDodge(): int
    {
        return $this->pct('dodge', max(0, $this->primary['agility'] - 1) + $this->bonus('dodge'));
    }

    public function getCritical(): int
    {
        return $this->pct('critical', max(0, $this->primary['intuition'] - 1) + $this->bonus('critical'));
    }

    public function getIntelligence(): int
    {
        return $this->primary['intelligence'];
    }

    public function getMagicResistance(): int
    {
        return $this->pct('magic_resistance', max(0, $this->primary['wisdom'] - 1) + $this->bonus('magic_resistance'));
    }

    public function getMagicAttack(): int
    {
        return $this->pct('magic_attack', $this->bonus('magic_attack'));
    }

    public function getMagicDamagePercent(): int
    {
        return $this->bonus('magic_damage_percent');
    }

    public function getMagicCriticalChance(): int
    {
        return $this->bonus('magic_critical');
    }

    public function getCombatClass(): CombatClass
    {
        $s = $this->base;

        return ($s['strength'] >= $s['agility'] && $s['strength'] >= $s['intuition']) ? CombatClass::TANK
            : ($s['agility'] >= $s['intuition'] ? CombatClass::DODGE : CombatClass::CRIT);
    }

    public function getClassShare(CombatClass $class): float
    {
        $total = max(1, $this->base['strength'] + $this->base['agility'] + $this->base['intuition']);

        return match ($class) {
            CombatClass::TANK => $this->base['strength'] / $total,
            CombatClass::DODGE => $this->base['agility'] / $total,
            CombatClass::CRIT => $this->base['intuition'] / $total,
        };
    }

    public function getCritDamage(): int
    {
        return (int) round(PlayerStatFormulas::CRIT_DAMAGE_BASE
            + PlayerStatFormulas::critDamageBonus((float) $this->primary['intuition'], $this->level) + $this->bonus('crit_damage'));
    }

    public function getMagicCritDamage(): int
    {
        return PlayerStatFormulas::CRIT_DAMAGE_BASE + $this->bonus('magic_crit_damage');
    }

    public function getBlockChance(): int
    {
        return $this->bonus('block_chance');
    }

    public function getBlockFlat(): int
    {
        return $this->bonus('block_flat');
    }

    public function getBlockPercent(): int
    {
        return $this->bonus('block_percent');
    }
}
