<?php

declare(strict_types=1);

/**
 * Real FightOrchestrator audit. The live database is SELECT-only.
 * All simulated accounts, inventory, effects and battles exist in SQLite :memory:.
 */

use App\Modules\Battle\Application\Services\Combat\FightOrchestrator;
use App\Modules\Battle\Application\Services\Combat\HitCalculator;
use App\Modules\Battle\Infrastructure\Persistence\BattleRepository;
use App\Modules\Battle\Infrastructure\Persistence\Models\BattleRound;
use App\Modules\Item\Domain\Services\ItemRequirementService;
use App\Modules\Item\Infrastructure\Persistence\Models\Item;
use App\Modules\Location\Infrastructure\Persistence\Models\Location;
use App\Modules\MagicSkill\Application\Services\MagicSkillRequirementService;
use App\Modules\MagicSkill\Infrastructure\Persistence\Models\MagicSkill;
use App\Modules\Monster\Infrastructure\Persistence\Models\Monster;
use App\Modules\Monster\Infrastructure\Persistence\MonsterOnLocationRepository;
use App\Modules\Player\Application\UseCases\RegisterPlayerProfile;
use App\Modules\Player\Domain\Services\PlayerStatService;
use App\Modules\Player\Infrastructure\Persistence\Models\PlayerEquipment;
use App\Modules\Share\Infrastructure\Persistence\Models\ShareItem;
use App\Modules\User\Infrastructure\Persistence\Models\User;
use Carbon\Carbon;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Events\NullDispatcher;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;

require dirname(__DIR__).'/vendor/autoload.php';
$app = require dirname(__DIR__).'/bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();
$options = getopt('', ['fights:', 'min-level:', 'max-level:', 'profile:', 'skill-scale:', 'snapshot-only', 'output:', 'resume', 'monster-id:', 'traces']);
$trials = max(1, (int) ($options['fights'] ?? 100));
$minimum = max(1, (int) ($options['min-level'] ?? 1));
$maximum = min(75, (int) ($options['max-level'] ?? 55));
$profile = (string) ($options['profile'] ?? 'staff');
$skillScale = max(1, (int) ($options['skill-scale'] ?? 1));
$output = $options['output'] ?? storage_path('app/reports/mage-pve-'.date('Ymd-His').'-'.$profile);
if (! in_array($profile, ['staff', 'folio'], true)) {
    throw new InvalidArgumentException('profile must be staff or folio');
}
if (! is_dir(dirname($output))) {
    mkdir(dirname($output), 0775, true);
}
$source = DB::connection();
$source->beforeExecuting(function (string $sql): void {
    if (! preg_match('/^\s*(select|show|explain)\b/i', $sql)) {
        throw new RuntimeException('Live DB writes are prohibited by this audit.');
    }
});
config([
    'database.connections.mage_audit' => ['driver' => 'sqlite', 'database' => ':memory:', 'prefix' => '', 'foreign_key_constraints' => false],
    'database.default' => 'mage_audit', 'cache.default' => 'array', 'session.driver' => 'array', 'broadcasting.default' => 'log',
]);
DB::setDefaultConnection('mage_audit');
$audit = DB::connection('mage_audit');
if ($audit->getDriverName() !== 'sqlite' || $audit->getDatabaseName() !== ':memory:') {
    throw new RuntimeException('Audit must run in SQLite :memory:.');
}
$nullEvents = new NullDispatcher(app('events'));
Event::swap($nullEvents);
Model::setEventDispatcher($nullEvents);
$source->unsetEventDispatcher();
$audit->unsetEventDispatcher();
$source->disableQueryLog();
$audit->disableQueryLog();
Queue::fake();
Http::preventStrayRequests();
Cache::setDefaultDriver('array');
app()->setLocale('ru');

$referenceTables = [
    'share_items', 'share_item_stats', 'share_item_effects', 'share_item_requirements', 'share_recipes',
    'share_recipe_has_items', 'share_recipe_results', 'skills', 'skill_level_requirements',
    'magic_skills', 'magic_skill_requirements', 'magic_skill_effects', 'magic_skill_books',
    'effects', 'monsters', 'monster_effects', 'monster_summon_pool', 'boss_mechanics', 'boss_phases',
    'races', 'experiences', 'maps', 'locations', 'location_has_monsters', 'injury_types',
    'structures', 'dungeons', 'reputations', 'reputation_tiers', 'reputation_categories',
];
$tables = array_map(fn ($row) => array_values((array) $row)[0], $source->select("SHOW FULL TABLES WHERE Table_type = 'BASE TABLE'"));
$snapshot = [];
foreach ($tables as $table) {
    $columns = $source->getSchemaBuilder()->getColumns($table);
    $definitions = [];
    foreach ($columns as $column) {
        $type = strtolower($column['type_name']);
        $numeric = preg_match('/int|decimal|float|double|real|bool|bit/', $type);
        $sqlType = preg_match('/int|bool|bit/', $type) ? 'INTEGER' : ($numeric ? 'REAL' : 'TEXT');
        $definition = '"'.$column['name'].'" '.$sqlType;
        if ($column['name'] === 'id' && $sqlType === 'INTEGER') {
            $definition .= ' PRIMARY KEY AUTOINCREMENT';
        } elseif ($column['default'] !== null) {
            $default = trim((string) $column['default'], "'");
            if (strtolower($default) === 'current_timestamp' || strtolower($default) === 'current_timestamp()') {
                $definition .= ' DEFAULT CURRENT_TIMESTAMP';
            } elseif (! str_contains($default, '(')) {
                $definition .= ' DEFAULT '.($numeric && is_numeric($default) ? $default : $audit->getPdo()->quote($default));
            }
        }
        $definitions[] = $definition;
    }
    $audit->statement('CREATE TABLE "'.$table.'" ('.implode(',', $definitions).')');
    foreach ($columns as $column) {
        if (str_ends_with($column['name'], '_id')) {
            $audit->statement('CREATE INDEX "audit_'.$table.'_'.$column['name'].'" ON "'.$table.'" ("'.$column['name'].'")');
        }
    }
    if (in_array($table, $referenceTables, true)) {
        $rows = $source->table($table)->get()->map(fn ($row) => (array) $row)->all();
        foreach (array_chunk($rows, 40) as $chunk) {
            $audit->table($table)->insert($chunk);
        }
        $snapshot[$table] = count($rows);
    }
}
file_put_contents($output.'-snapshot.json', json_encode($snapshot, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
echo 'Snapshot ready: '.array_sum($snapshot)." reference rows; live writes prohibited.\n";

$allGear = ShareItem::query()->where('is_active', true)->whereIn('type', ['weapon', 'armor', 'shield'])->with(['stats', 'requirements.skill'])->get();
$allSpells = MagicSkill::query()->where('name->ru', 'not like', 'Кланов%')->with(['requirements.skill', 'skillEffects'])->get();
$monsters = Monster::query()->whereBetween('lvl', [1, 75])->where('is_boss', false)
    ->when(isset($options['monster-id']), fn ($query) => $query->whereKey((int) $options['monster-id']))
    ->whereHas('locations')->with('effects')->orderBy('lvl')->orderBy('id')->get();
if (isset($options['snapshot-only'])) {
    file_put_contents($output.'-catalog.json', json_encode([
        'gear' => $allGear->map(fn ($i) => ['id' => $i->id, 'name' => $i->name, 'rarity' => $i->rarity->value, 'slot' => $i->slot?->value, 'is_two_hand' => $i->is_two_hand, 'skill_id' => $i->skill_id, 'requirements' => $i->requirements->toArray(), 'stats' => $i->stats->toArray()]),
        'spells' => $allSpells->toArray(), 'monsters' => $monsters->toArray(),
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    exit(0);
}

function mageGearLevel(ShareItem $item): ?int
{
    $name = $item->getTranslation('name', 'ru');
    $slot = $item->slot?->value;
    if ($item->rarity->value !== 'common' && ! (str_contains($name, 'Архимаг') || str_contains($name, 'Астрал'))) {
        return null;
    }
    if (preg_match('/Усиленн|Закалён|Заговорён|Мифичес/u', $name)) {
        return null;
    }
    if (str_contains($name, 'Пепельной Башни')) {
        return ['hand' => 1, 'armor' => 1, 'shoes' => 2, 'forearm' => 4, 'helmet' => 7, 'legging' => 10, 'shoulder' => 13, 'chain_armor' => 20][$slot] ?? null;
    }
    if (str_contains($name, 'Иней') || str_contains($name, 'Всполох')) {
        return ['hand' => 20, 'armor' => 20, 'shoes' => 22, 'forearm' => 25, 'helmet' => 30, 'legging' => 35, 'shoulder' => 40, 'chain_armor' => 45][$slot] ?? null;
    }
    if (str_contains($name, 'Архимаг') || str_contains($name, 'Астрал')) {
        return ['hand' => 55, 'armor' => 60, 'shoes' => 65, 'forearm' => 70, 'helmet' => 75, 'legging' => 80, 'shoulder' => 85, 'chain_armor' => 90][$slot] ?? null;
    }

    return null;
}

function mageGearScore(ShareItem $item, string $profile): float
{
    $score = 0;
    foreach ($item->stats as $stat) {
        $weight = match ($stat->stat_type->value) {
            'intelligence', 'magic_attack' => 3,
            'magic_critical' => $profile === 'folio' ? 5 : 2,
            'magic_crit_damage' => $profile === 'folio' ? 1 : .3,
            'armor' => $profile === 'staff' ? 1.5 : .5,
            'endurance', 'wisdom', 'magic_resistance' => 1,
            default => 0,
        };
        $score += $weight * $stat->value;
    }

    return $score;
}

$catalogWarnings = [];
$results = [];
$builds = [];
if (isset($options['resume']) && is_file($output.'.json')) {
    $previous = json_decode(file_get_contents($output.'.json'), true, flags: JSON_THROW_ON_ERROR);
    if ($previous['profile'] !== $profile || $previous['trials'] !== $trials || $previous['skill_scale'] !== $skillScale) {
        throw new RuntimeException('Cannot mix different audit profiles when resuming.');
    }
    $results = array_values(array_filter($previous['results'], fn ($row) => $row['level'] <= $maximum));
    $builds = array_filter($previous['builds'], fn ($level) => (int) $level <= $maximum, ARRAY_FILTER_USE_KEY);
    if ($builds !== []) {
        $minimum = max($minimum, max(array_keys($builds)));
    }
}
$completed = array_fill_keys(array_map(fn ($row) => $row['level'].':'.$row['monster_id'], $results), true);
$save = function () use ($output, $profile, $trials, $skillScale, &$builds, &$results): void {
    file_put_contents($output.'.json.tmp', json_encode(['profile' => $profile, 'trials' => $trials, 'skill_scale' => $skillScale, 'builds' => $builds, 'results' => $results], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    rename($output.'.json.tmp', $output.'.json');
};
$start = microtime(true);
for ($level = $minimum; $level <= $maximum; $level++) {
    DB::beginTransaction();
    try {
        $location = Location::query()->setEagerLoads([])->orderBy('id')->firstOrFail();
        $user = new User;
        $user->forceFill(['name' => 'MageAudit', 'email' => 'mage-audit@example.invalid', 'password' => 'not-a-login', 'location_id' => $location->id, 'money' => 0, 'diamond' => 0])->save();
        $player = app(RegisterPlayerProfile::class)->execute($user, 2);
        $race = $player->race;
        $points = 5 + (int) $race->free_stats * ($level - 1);
        $allocation = ['intelligence' => (int) round($points * .5), 'wisdom' => (int) round($points * .2)];
        $allocation['endurance'] = $points - array_sum($allocation);
        foreach (['strength', 'agility', 'intuition', 'wisdom', 'intelligence', 'endurance'] as $stat) {
            $player->$stat = 1 + ($level - 1) * $race->$stat + ($allocation[$stat] ?? 0);
        }
        $player->lvl = $level;
        $player->free_stats = 0;
        $player->exp_up = 2000000000;
        $player->exp_diff = 2000000000;
        $player->mp_max = 10 + 3 * max(0, floor($player->wisdom) - 1);
        $player->save();
        $skillLevel = min(300, $level * $skillScale);
        foreach (DB::table('skills')->pluck('id') as $skillId) {
            DB::table('player_skills')->insert(['player_id' => $player->id, 'skill_id' => $skillId, 'lvl' => $skillLevel, 'exp' => 0, 'exp_up' => 2000000000]);
        }
        $gear = $allGear->filter(fn ($item) => ($gate = mageGearLevel($item)) !== null && $gate <= $level
            && app(ItemRequirementService::class)->check($player, $item) === null);
        $slots = [];
        foreach ($gear->groupBy(fn ($item) => $item->slot->value) as $slot => $items) {
            if ($slot === 'hand') {
                $weapon = $items->filter(fn ($item) => $item->skill_id === ($profile === 'staff' ? 10 : 11))->sortByDesc(fn ($item) => mageGearScore($item, $profile))->first();
                if ($weapon) {
                    $slots['hand_left'] = $weapon;
                }
                if (! $weapon?->is_two_hand) {
                    $orb = $items->where('skill_id', 12)->sortByDesc(fn ($item) => mageGearScore($item, $profile))->first();
                    if ($orb) {
                        $slots['hand_right'] = $orb;
                    }
                }
            } else {
                $slots[$slot] = $items->sortByDesc(fn ($item) => mageGearScore($item, $profile))->first();
            }
        }
        $equipment = PlayerEquipment::query()->where('player_id', $player->id)->firstOrFail();
        foreach ($slots as $slot => $info) {
            $item = Item::query()->create(['share_item_id' => $info->id]);
            DB::table('backpacks')->insert(['user_id' => $user->id, 'item_id' => $item->id, 'count' => 1, 'equipped' => 1]);
            $equipment->$slot = $item->id;
        }
        $equipment->save();
        $user = $user->fresh();
        Auth::setUser($user);
        $player = $user->player;
        $stats = app(PlayerStatService::class);
        $stats->invalidate();
        $sheet = $stats->resolve($player);
        $player->hp_max = $sheet->hpMax;
        $player->hp_now = $sheet->hpMax;
        $player->mp_now = $sheet->mpMax;
        $player->save();
        $spells = $allSpells->filter(fn ($spell) => app(MagicSkillRequirementService::class)->check($player, $spell) === null);
        $replaced = $spells->pluck('upgrades_skill_id')->filter()->all();
        $spells = $spells->reject(fn ($spell) => in_array($spell->id, $replaced));
        foreach ($spells as $spell) {
            DB::table('player_magic_skills')->insert(['player_id' => $player->id, 'magic_skill_id' => $spell->id, 'is_equipped' => 1, 'cooldown_end_at' => null]);
        }
        $stats->invalidate();
        $sheet = $stats->resolve($player->fresh());
        $builds[$level] = [
            'level' => $level, 'skill_level' => $skillLevel, 'hp' => $sheet->hpMax, 'mp' => $sheet->mpMax,
            'stats' => array_diff_key(get_object_vars($sheet), array_flip(['modifiers', 'debuffReduction', 'combatClass'])),
            'gear' => array_map(fn ($item) => ['id' => $item->id, 'name' => $item->name], $slots),
            'spells' => $spells->map(fn ($spell) => ['id' => $spell->id, 'name' => $spell->name])->values()->all(),
        ];
        $targets = $monsters->where('lvl', $level);
        foreach ($targets as $monster) {
            if (isset($completed[$level.':'.$monster->id])) {
                continue;
            }
            $row = ['level' => $level, 'monster_id' => $monster->id, 'monster' => $monster->name, 'monster_hp' => $monster->hp,
                'fights' => $trials, 'wins' => 0, 'losses' => 0, 'draws' => 0, 'rounds' => 0, 'hp_left' => 0, 'mp_left' => 0, 'physical_fallbacks' => 0];
            for ($trial = 0; $trial < $trials; $trial++) {
                DB::beginTransaction();
                try {
                    $clock = Carbon::parse('2030-01-01 00:00:00');
                    Carbon::setTestNow($clock);
                    $player = $user->player->fresh();
                    $player->hp_now = $sheet->hpMax;
                    $player->mp_now = $sheet->mpMax;
                    $player->save();
                    Auth::setUser($user->fresh());
                    $stats->invalidate();
                    $spawn = app(MonsterOnLocationRepository::class)->createMonsterOnLocation($monster, $location);
                    $repository = app(BattleRepository::class);
                    $battle = $repository->createBattle($location);
                    $repository->createBattleDetails($battle, Auth::user());
                    $repository->createBattleDetails($battle, null, $spawn);
                    $outcome = 'draws';
                    for ($round = 1; $round <= 150; $round++) {
                        $clock = $clock->copy()->addSeconds(2);
                        Carbon::setTestNow($clock);
                        $player = $player->fresh();
                        $spawn = $spawn->fresh();
                        $stats->invalidate($player);
                        $currentSheet = $stats->resolve($player);
                        $eligible = $spells->where('is_passive', false)->filter(function ($spell) use ($player, $skillLevel): bool {
                            $cooldown = DB::table('player_magic_skills')->where('player_id', $player->id)->where('magic_skill_id', $spell->id)->value('cooldown_end_at');

                            return ($cooldown === null || now()->gte($cooldown))
                                && $player->mp_now >= ceil($spell->mana_cost / HitCalculator::weaponSkillMultiplier($skillLevel));
                        });
                        $heal = $eligible->where('type', 'heal')->sortByDesc('base_healing')->first();
                        $attack = $eligible->where('type', 'attack')->sortByDesc(fn ($spell) => ($spell->min_damage + $spell->max_damage) / 2 + ($currentSheet->intelligence + $currentSheet->magicAttack) * $spell->power_coefficient)->first();
                        $action = $heal && $player->hp_now < $currentSheet->hpMax * .4
                            ? $heal->id : ($attack?->id ?? 0);
                        if ($action === 0) {
                            $row['physical_fallbacks']++;
                        }
                        $fight = app(FightOrchestrator::class)->attack($battle->id, $spawn->id, $action);
                        $player = $player->fresh();
                        $spawn = $spawn->fresh();
                        if ($fight->isPlayerDead() || $player->hp_now <= 0) {
                            $outcome = 'losses';
                            break;
                        }
                        if ($spawn->hp_now <= 0 || ! $spawn->active) {
                            $outcome = 'wins';
                            break;
                        }
                    }
                    $row[$outcome]++;
                    if (isset($options['traces']) && ! is_file($output.'-'.$monster->id.'-'.$outcome.'.json')) {
                        file_put_contents($output.'-'.$monster->id.'-'.$outcome.'.json', json_encode([
                            'outcome' => $outcome, 'monster' => $monster->toArray(), 'build' => $builds[$level],
                            'rounds' => BattleRound::query()->where('battle_id', $battle->id)->orderBy('id')->get(['round_number', 'action'])->toArray(),
                        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
                    }
                    $row['rounds'] += min(150, $round);
                    if ($outcome === 'wins') {
                        $row['hp_left'] += $player->hp_now;
                        $row['mp_left'] += $player->mp_now;
                    }
                } finally {
                    DB::rollBack();
                    Carbon::setTestNow();
                    $stats->invalidate();
                    Cache::flush();
                    gc_collect_cycles();
                }
            }
            $results[] = $row;
            printf("L%02d %-35s %3d/%d wins, %.1f rounds, %.1fs elapsed\n", $level, $monster->name, $row['wins'], $trials, $row['rounds'] / $trials, microtime(true) - $start);
            $save();
        }
    } finally {
        DB::rollBack();
        Auth::forgetGuards();
        app(PlayerStatService::class)->invalidate();
        Cache::flush();
    }
    if ($targets->isEmpty()) {
        printf("L%02d no equal-level regular monsters\n", $level);
        $save();
    }
}
$save();
echo $output.".json\n";
