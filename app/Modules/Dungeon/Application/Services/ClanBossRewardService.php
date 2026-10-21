<?php

declare(strict_types=1);

namespace App\Modules\Dungeon\Application\Services;

use App\Modules\Backpack\Domain\Services\BackpackService;
use App\Modules\Clan\Domain\Enums\ClanLogAction;
use App\Modules\Clan\Domain\Enums\ClanWarehouseAction;
use App\Modules\Clan\Domain\Models\Clan;
use App\Modules\Clan\Domain\Models\ClanWarehouse;
use App\Modules\Clan\Domain\Models\ClanWarehouseLog;
use App\Modules\Clan\Domain\Services\ClanExperienceService;
use App\Modules\Clan\Domain\Services\ClanLogService;
use App\Modules\Dungeon\Domain\Enums\DungeonRewardType;
use App\Modules\Dungeon\Infrastructure\Persistence\Models\ClanBossContribution;
use App\Modules\Dungeon\Infrastructure\Persistence\Models\ClanBossRun;
use App\Modules\Dungeon\Infrastructure\Persistence\Models\ClanBossRunStage;
use App\Modules\Dungeon\Infrastructure\Persistence\Models\Dungeon;
use App\Modules\Item\Infrastructure\Persistence\Models\Item;
use App\Modules\Monster\Infrastructure\Persistence\Models\Monster;
use App\Modules\Player\Domain\Services\ExperienceService;
use App\Modules\Share\Infrastructure\Persistence\Models\ShareItem;
use App\Modules\Structure\Infrastructure\Persistence\Models\Structure;
use App\Modules\User\Infrastructure\Persistence\Models\User;
use Illuminate\Support\Collection;

/**
 * Раздача наград за убитого клан-босса.
 *
 * Вызывается внутри транзакции убийства (см. ClanBossService::handleStageKill),
 * собственных транзакций не открывает.
 */
final class ClanBossRewardService
{
    public function __construct(
        private readonly BackpackService $backpackService,
        private readonly ExperienceService $experienceService,
        private readonly ClanExperienceService $clanExperienceService,
        private readonly ClanLogService $clanLogService,
        private readonly ClanBossRewardRecorder $rewardRecorder,
    ) {}

    /**
     * @return array{qualified:int, warehouse_drops:list<string>, clan_experience:float, gold_granted:int}
     */
    public function grantForStage(ClanBossRun $run, ClanBossRunStage $stageState, Monster $monster, User $killer): array
    {
        $run->loadMissing('dungeon');
        $dungeon = $run->dungeon;

        $eligible = $this->eligibleContributions($run, $stageState);
        $goldByUser = $this->grantGold($stageState, $eligible);
        $goldGranted = array_sum($goldByUser);
        $clanRewards = [];

        $qualified = 0;
        $clanExperience = 0.0;

        foreach ($eligible as $contribution) {
            $user = $contribution->user;

            $rewards = $this->grantDungeonRewards($run, $dungeon, $user);
            $personalRewards = $rewards['personal'];
            if (($goldByUser[$user->id] ?? 0) > 0) {
                $personalRewards[] = $this->rewardEntry('gold', $goldByUser[$user->id]);
            }
            array_push($clanRewards, ...$rewards['clan']);
            $grantedExp = $rewards['experience'];
            $clanExperience += $this->clanExperienceService->awardForMonsterExperience(
                $user->player,
                $monster,
                $grantedExp,
            );

            $contribution->update(['rewarded' => true]);
            $this->rewardRecorder->record($run, $stageState, $user, $personalRewards);
            $qualified++;
        }

        $bossDrops = $this->grantBossDrops($run, $monster, $killer);
        $warehouseDrops = $bossDrops['names'];
        array_push($clanRewards, ...$bossDrops['rewards']);
        if ($clanExperience > 0) {
            $clanRewards[] = $this->rewardEntry('clan_experience', round($clanExperience, 2));
        }
        $this->rewardRecorder->record($run, $stageState, null, $clanRewards);

        $this->clanLogService->write(
            $run->clan_id,
            $killer->id,
            ClanLogAction::BOSS_KILLED,
            sprintf('Клан-босс «%s» повержен. Награду получили: %d.', $monster->name, $qualified),
        );

        return [
            'qualified' => $qualified,
            'warehouse_drops' => $warehouseDrops,
            'clan_experience' => round($clanExperience, 2),
            'gold_granted' => $goldGranted,
        ];
    }

    /**
     * Босса не добили (поход истёк) — только монеты пропорционально
     * нанесённому урону, без опыта/предметов/дропа. Та же формула, что и
     * при килле, просто с меньшей итоговой суммой (меньше % снятого HP).
     */
    public function grantGoldForUnfinishedStage(ClanBossRun $run, ClanBossRunStage $stageState): int
    {
        $eligible = $this->eligibleContributions($run, $stageState);

        $goldByUser = $this->grantGold($stageState, $eligible);
        $goldGranted = array_sum($goldByUser);

        foreach ($eligible as $contribution) {
            $contribution->update(['rewarded' => true]);
            $amount = $goldByUser[$contribution->user_id] ?? 0;
            $this->rewardRecorder->record($run, $stageState, $contribution->user, $amount > 0 ? [$this->rewardEntry('gold', $amount)] : [], reason: 'run_expired');
        }

        $this->rewardRecorder->record($run, $stageState, null, [], reason: 'run_expired');

        return $goldGranted;
    }

    /**
     * @return Collection<int, ClanBossContribution>
     */
    private function eligibleContributions(ClanBossRun $run, ClanBossRunStage $stageState): Collection
    {
        return ClanBossContribution::query()
            ->where('clan_boss_run_id', $run->id)
            ->where('dungeon_stage_id', $stageState->dungeon_stage_id)
            ->where('damage', '>', 0)
            ->where('rewarded', false)
            ->orderByDesc('damage')
            ->get()
            ->filter(function (ClanBossContribution $contribution) use ($run): bool {
                $user = $contribution->user;
                if ($user === null) {
                    return false;
                }

                $membership = $user->clanMembership;
                if ($membership === null || (int) $membership->clan_id !== (int) $run->clan_id) {
                    return false;
                }

                $user->loadMissing('player');

                return $user->player !== null;
            })
            ->values();
    }

    /**
     * Делит `gold_budget` этажа между контрибьюторами пропорционально их
     * доле урона. Бюджет масштабируется по % фактически снятого HP, поэтому
     * при недобитом боссе сумма сама получается меньше — без отдельной ветки.
     *
     * @param  Collection<int, ClanBossContribution>  $eligible
     * @return array<int, int> начисленные монеты по ID игрока
     */
    private function grantGold(ClanBossRunStage $stageState, Collection $eligible): array
    {
        $goldBudget = (int) ($stageState->stage->gold_budget ?? 0);
        $totalDamage = (int) $eligible->sum('damage');
        if ($goldBudget <= 0 || $totalDamage <= 0) {
            return [];
        }

        $hpMax = max(1, (int) $stageState->hp_max);
        $pool = (int) floor($goldBudget * min(1.0, $totalDamage / $hpMax));

        $granted = [];
        foreach ($eligible as $contribution) {
            $share = (int) floor($pool * $contribution->damage / $totalDamage);
            if ($share <= 0) {
                continue;
            }

            $contribution->user->increment('money', $share);
            $granted[$contribution->user_id] = $share;
        }

        return $granted;
    }

    /**
     * Золото/опыт/диаманты — каждому квалифицированному.
     * Предметы из dungeon_rewards — в клановый склад.
     */
    private function grantDungeonRewards(ClanBossRun $run, Dungeon $dungeon, User $user): array
    {
        $grantedExp = 0;
        $personal = [];
        $clan = [];

        foreach ($dungeon->rewards()->with('shareItem')->get() as $reward) {
            if ((mt_rand(0, 100000) / 1000) > $reward->drop_chance) {
                continue;
            }

            $amount = $reward->randomAmount();

            switch ($reward->type) {
                case DungeonRewardType::GOLD:
                    $user->increment('money', $amount);
                    $personal[] = $this->rewardEntry('gold', $amount);
                    break;
                case DungeonRewardType::DIAMOND:
                    $user->increment('diamond', $amount);
                    $personal[] = $this->rewardEntry('diamond', $amount);
                    break;
                case DungeonRewardType::EXPERIENCE:
                    $gain = $this->grantExperience($user, $amount);
                    $grantedExp += $gain;
                    $personal[] = $this->rewardEntry('experience', $gain);
                    break;
                case DungeonRewardType::ITEM:
                    $destination = $this->putItemToClanWarehouse($run, $reward->shareItem, $amount, $user->id, $user);
                    if ($destination === 'player') {
                        $personal[] = $this->rewardEntry('item', $amount, $reward->shareItem);
                    } elseif ($destination === 'clan') {
                        $clan[] = $this->rewardEntry('item', $amount, $reward->shareItem);
                    }
                    break;
            }
        }

        return ['experience' => $grantedExp, 'personal' => $personal, 'clan' => $clan];
    }

    private function grantExperience(User $user, int $amount): int
    {
        $gain = $this->experienceService->calculateGain($user->player, $amount);
        $user->player->increment('exp', $gain);

        return $gain;
    }

    /**
     * Ролл редкого дропа из monster_has_items сразу в клановый склад.
     *
     * @return array{names: list<string>, rewards: list<array>}
     */
    private function grantBossDrops(ClanBossRun $run, Monster $monster, User $killer): array
    {
        $dropped = [];
        $rewards = [];
        $monster->loadMissing('items');

        foreach ($monster->items as $shareItem) {
            $randomChance = random_int(1, 100000) / 1000;
            if ($randomChance > $shareItem->pivot->drop_chance) {
                continue;
            }

            $count = mt_rand($shareItem->pivot->min_count, $shareItem->pivot->max_count);
            // Boss drops are clan rewards only: never redirect them into a
            // player's backpack if the item is forbidden or the warehouse is full.
            if ($this->putItemToClanWarehouse($run, $shareItem, $count, $killer->id)) {
                $dropped[] = (string) $shareItem->name;
                $rewards[] = $this->rewardEntry('item', $count, $shareItem);
            }
        }

        return ['names' => $dropped, 'rewards' => $rewards];
    }

    /**
     * Кладет предмет в клановый склад. Если склад недоступен (нет структуры,
     * переполнен, предмет запрещен) — отдает в рюкзак, чтобы дроп не сгорел.
     */
    private function putItemToClanWarehouse(ClanBossRun $run, ?ShareItem $shareItem, int $amount, int $depositorId, ?User $fallbackUser = null): ?string
    {
        if ($shareItem === null) {
            return null;
        }

        $target = $this->resolveWarehouseStructure();

        if ($target === null || ! $shareItem->is_clan_warehouse_allowed || $this->isWarehouseFull((int) $run->clan_id, $target->id)) {
            if ($fallbackUser !== null) {
                $this->backpackService->addItemByShareItem($fallbackUser, $shareItem, $amount);

                return 'player';
            }

            return null;
        }

        $item = new Item;
        $item->share_item_id = $shareItem->id;
        $item->count_use = $shareItem->count_use;
        $item->save();

        ClanWarehouse::create([
            'clan_id' => $run->clan_id,
            'structure_id' => $target->id,
            'depositor_user_id' => $depositorId,
            'item_id' => $item->id,
            'count' => $amount,
        ]);
        ClanWarehouseLog::create([
            'clan_id' => $run->clan_id,
            'user_id' => $depositorId,
            'structure_id' => $target->id,
            'item_id' => $item->id,
            'action' => ClanWarehouseAction::PUT,
            'count' => $amount,
        ]);

        return 'clan';
    }

    private function rewardEntry(string $type, int|float $amount, ?ShareItem $item = null): array
    {
        return [
            'type' => $type, 'amount' => $amount, 'item_id' => $item?->id,
            'item_name' => $item?->getTranslations('name'), 'rarity' => $item?->rarity?->value,
        ];
    }

    private function resolveWarehouseStructure(): ?Structure
    {
        return Structure::query()
            ->where('type', Structure::TYPE_CLAN_WAREHOUSE)
            ->orderBy('id')
            ->first();
    }

    private function isWarehouseFull(int $clanId, int $structureId): bool
    {
        $clan = Clan::query()->find($clanId);
        if ($clan === null) {
            return true;
        }

        return ClanWarehouse::query()
            ->where('clan_id', $clanId)
            ->where('structure_id', $structureId)
            ->count() >= $clan->warehouse_capacity;
    }
}
