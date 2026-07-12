<?php

declare(strict_types=1);

namespace App\Modules\Battle\Application\UseCases;

use App\Modules\Battle\Application\DTOs\FightListFilterDTO;
use App\Modules\Battle\Application\DTOs\FightListItemDTO;
use App\Modules\Battle\Application\DTOs\FightListPageDTO;
use App\Modules\Battle\Domain\Enums\BattleDetailStatus;
use App\Modules\Battle\Domain\Enums\BattleStatus;
use App\Modules\Battle\Infrastructure\Persistence\Models\Battle;
use Illuminate\Support\Carbon;

final class GetFightList
{
    private const PER_PAGE = 10;

    public function execute(string $mode, FightListFilterDTO $filter, int $page, ?int $locationId = null): FightListPageDTO
    {
        $status = match ($mode) {
            'finished' => BattleStatus::FINISH,
            'running' => BattleStatus::ACTIVE,
            default => null,
        };

        $query = Battle::query()
            ->with(['location', 'details.user.player', 'details.locationMonster.monster'])
            ->orderByDesc('id');

        if ($status !== null) {
            $query->where('status', $status);
        }

        if ($locationId !== null) {
            $query->where('location_id', $locationId);
        }

        if ($filter->nick !== '') {
            $query->whereHas('details.user', fn ($q) => $q->where('name', 'like', '%'.$filter->nick.'%'));
        }

        if ($filter->monsterName !== '') {
            $query->whereHas('details.locationMonster.monster', fn ($q) => $q->where('name', 'like', '%'.$filter->monsterName.'%'));
        }

        if ($filter->dateFrom !== null) {
            $query->whereDate('created_at', '>=', $filter->dateFrom);
        }

        if ($filter->dateTo !== null) {
            $query->whereDate('created_at', '<=', $filter->dateTo);
        }

        $paginator = $query->paginate(self::PER_PAGE, page: $page)->withQueryString();

        $items = $paginator->getCollection()->map(fn (Battle $battle) => $this->toDTO($battle));
        $paginator->setCollection($items);

        return new FightListPageDTO($mode, $paginator, $filter);
    }

    private function toDTO(Battle $battle): FightListItemDTO
    {
        $playerDetails = $battle->details->whereNotNull('user_id');
        $monsterDetails = $battle->details->whereNotNull('location_monster_id');

        $players = $playerDetails
            ->filter(fn ($d) => $d->user !== null)
            ->map(fn ($d) => [
                'userId' => $d->user->id,
                'name' => $d->user->name,
                'level' => (int) ($d->user->player?->lvl ?? 1),
                'alive' => $d->status === BattleDetailStatus::LIFE,
            ])
            ->values()->all();

        $monsters = $monsterDetails
            ->filter(fn ($d) => $d->locationMonster?->monster !== null)
            ->map(fn ($d) => [
                'locationMonsterId' => $d->location_monster_id,
                'name' => $d->locationMonster->monster->name,
                'level' => (int) $d->locationMonster->monster->lvl,
                'alive' => $d->status === BattleDetailStatus::LIFE,
            ])
            ->values()->all();

        $monsterNames = collect($monsters)->pluck('name')->unique()->values();
        $title = match (true) {
            $monsterNames->isEmpty() => 'Бой #'.$battle->id,
            $monsterNames->count() === 1 => 'Бой с '.$monsterNames->first(),
            default => 'Бой с '.$monsterNames->first().' и др.',
        };

        $typeLabel = (count($monsters) > 1 || count($players) > 1) ? 'групповой бой' : 'бой 1x1';

        $endedAt = $battle->status === BattleStatus::FINISH ? $battle->updated_at : Carbon::now();
        $seconds = max(0, (int) $battle->created_at->diffInSeconds($endedAt));
        $duration = $seconds >= 60 ? intdiv($seconds, 60).' хв' : $seconds.' сек';

        $winnerLabel = null;
        if ($battle->status === BattleStatus::FINISH) {
            $monstersAllDead = count($monsters) > 0 && collect($monsters)->every(fn ($m) => ! $m['alive']);
            $playersAllDead = count($players) > 0 && collect($players)->every(fn ($p) => ! $p['alive']);

            $winnerLabel = match (true) {
                $monstersAllDead => 'Победила команда №1',
                $playersAllDead => 'Победила команда №2',
                default => null,
            };
        }

        return new FightListItemDTO(
            id: $battle->id,
            title: $title,
            typeLabel: $typeLabel,
            startedAt: $battle->created_at->format('d.m H:i'),
            rounds: $battle->rounds,
            duration: $duration,
            players: $players,
            monsters: $monsters,
            winnerLabel: $winnerLabel,
            locationId: $battle->location_id,
            locationName: $battle->location?->name,
        );
    }
}
