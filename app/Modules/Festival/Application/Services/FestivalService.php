<?php

declare(strict_types=1);

namespace App\Modules\Festival\Application\Services;

use App\Modules\Backpack\Domain\Services\BackpackService;
use App\Modules\Festival\Domain\Enums\FestivalSide;
use App\Modules\Festival\Infrastructure\Persistence\Models\Festival;
use App\Modules\Festival\Infrastructure\Persistence\Models\FestivalParticipant;
use App\Modules\Festival\Infrastructure\Persistence\Models\FestivalSettings;
use App\Modules\Festival\Infrastructure\Persistence\Models\FestivalStoneRate;
use App\Modules\Share\Infrastructure\Persistence\Models\ShareItem;
use App\Modules\User\Infrastructure\Persistence\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/** Действия игрока у Смотрителя: запись, сдача камней, получение монет, обмен и магазин победителей. */
final class FestivalService
{
    private const MAX_QUANTITY = 100000;

    public function __construct(private readonly BackpackService $backpack) {}

    public function settings(): ?FestivalSettings
    {
        return FestivalSettings::query()->where('is_active', true)->with(['npc', 'coin'])->first();
    }

    public function atNpc(User $user, int $npcId): bool
    {
        $settings = $this->settings();

        return $settings?->npc_id !== null
            && (int) $settings->npc_id === $npcId
            && (int) $settings->npc?->location_id === (int) $user->location_id;
    }

    /** @return Collection<int, FestivalStoneRate> */
    public function stoneRates(): Collection
    {
        return FestivalStoneRate::query()->with('item')->orderBy('sort_order')->orderBy('id')->get();
    }

    public function current(): ?Festival
    {
        return Festival::query()->where('status', '!=', Festival::STATUS_FINISHED)->orderByDesc('id')->first();
    }

    /** @return array<string, mixed> */
    public function pageData(User $user): array
    {
        $settings = $this->settings();
        $festival = $this->current();
        $participant = $festival === null ? null : $this->participant($festival, $user);
        $rates = FestivalStoneRate::query()->with('item')->orderBy('sort_order')->orderBy('id')->get();

        $ids = $rates->pluck('share_item_id')->all();
        if ($settings?->coin_share_item_id !== null) {
            $ids[] = (int) $settings->coin_share_item_id;
        }
        $owned = $ids === [] ? [] : $this->backpack->countByShareItemIds($user, $ids);

        $isWinner = $festival?->status === Festival::STATUS_REWARDS
            && $participant !== null
            && $participant->side === $festival->winner_side;

        return [
            'settings' => $settings,
            'festival' => $festival,
            'participant' => $participant,
            'rates' => $rates,
            'shopUrl' => $settings?->shop_structure_id !== null ? route('barter_shop', ['id' => $settings->shop_structure_id]) : null,
            'owned' => $owned,
            'isWinner' => $isWinner,
            'claimable' => $this->claimablePoints($user),
            'coinCount' => (int) ($owned[(int) $settings?->coin_share_item_id] ?? 0),
        ];
    }

    public function register(User $user, int $npcId, string $side): void
    {
        $side = FestivalSide::tryFrom($side) ?? throw ValidationException::withMessages(['side' => __('festival.invalid_side')]);

        DB::transaction(function () use ($user, $npcId, $side): void {
            $this->lockUser($user);
            $this->requireNpc($user, $npcId);
            $festival = $this->requirePhase(Festival::STATUS_COLLECTION);
            if ($this->participant($festival, $user) !== null) {
                throw ValidationException::withMessages(['side' => __('festival.already_registered')]);
            }
            FestivalParticipant::query()->create([
                'festival_id' => $festival->id,
                'user_id' => $user->id,
                'side' => $side->value,
                'points' => 0,
            ]);
        });
    }

    /** @return array{item: ShareItem, quantity: int, points: int, total: int} */
    public function deposit(User $user, int $npcId, int $shareItemId, int $quantity): array
    {
        $quantity = $this->quantity($quantity);

        return DB::transaction(function () use ($user, $npcId, $shareItemId, $quantity): array {
            $this->lockUser($user);
            $this->requireNpc($user, $npcId);
            $festival = $this->requirePhase(Festival::STATUS_COLLECTION);
            $participant = FestivalParticipant::query()
                ->where('festival_id', $festival->id)->where('user_id', $user->id)->lockForUpdate()->first()
                ?? throw ValidationException::withMessages(['stone' => __('festival.not_registered_err')]);
            $rate = $this->rate($shareItemId);
            $this->takeItem($user, $rate, $quantity);

            $points = $rate->points * $quantity;
            $participant->increment('points', $points);

            return ['item' => $rate->item, 'quantity' => $quantity, 'points' => $points, 'total' => (int) $participant->fresh()->points];
        });
    }

    /** @return int выдано монет */
    public function claim(User $user, int $npcId): int
    {
        return DB::transaction(function () use ($user, $npcId): int {
            $this->lockUser($user);
            $settings = $this->requireNpc($user, $npcId);
            $rows = $this->claimableQuery($user)->lockForUpdate()->get();
            $total = (int) $rows->sum('points');
            if ($total < 1) {
                throw ValidationException::withMessages(['claim' => __('festival.nothing_to_claim')]);
            }
            FestivalParticipant::query()->whereIn('id', $rows->pluck('id'))->update(['reward_claimed_at' => now()]);
            $this->backpack->addItemByShareItem($user, $this->coin($settings), $total);

            return $total;
        });
    }

    /** @return array{item: ShareItem, quantity: int, coins: int, coin: ShareItem, total: int} */
    public function exchange(User $user, int $npcId, int $shareItemId, int $quantity): array
    {
        $quantity = $this->quantity($quantity);

        return DB::transaction(function () use ($user, $npcId, $shareItemId, $quantity): array {
            $this->lockUser($user);
            $settings = $this->requireNpc($user, $npcId);
            $this->requireWinner($user);
            $rate = $this->rate($shareItemId);
            $this->takeItem($user, $rate, $quantity);
            $coins = $rate->points * $quantity;
            $coin = $this->coin($settings);
            $this->backpack->addItemByShareItem($user, $coin, $coins);
            $total = (int) ($this->backpack->countByShareItemIds($user, [(int) $coin->id])[(int) $coin->id] ?? 0);

            return ['item' => $rate->item, 'quantity' => $quantity, 'coins' => $coins, 'coin' => $coin, 'total' => $total];
        });
    }

    /**
     * Магазин победителей — обычный бартерный магазин-структура; доступ к нему только у игроков
     * стороны-победителя текущего фестиваля (второй этап).
     */
    public function shopAccessError(User $user, int $structureId): ?string
    {
        $settings = FestivalSettings::query()->where('is_active', true)->first();
        if ($settings === null || (int) $settings->shop_structure_id !== $structureId) {
            return null;
        }
        $festival = $this->current();
        $participant = $festival === null ? null : $this->participant($festival, $user);
        $isWinner = $festival?->status === Festival::STATUS_REWARDS
            && $participant !== null
            && $participant->side === $festival->winner_side;

        return $isWinner ? null : __('commerce.festival_winners_only');
    }

    private function claimablePoints(User $user): int
    {
        return (int) $this->claimableQuery($user)->sum('points');
    }

    /** Награда за сданные камни доступна после первого этапа (и после окончания фестиваля), один раз. */
    private function claimableQuery(User $user)
    {
        return FestivalParticipant::query()
            ->where('user_id', $user->id)
            ->where('points', '>', 0)
            ->whereNull('reward_claimed_at')
            ->whereHas('festival', fn ($query) => $query->where('status', '!=', Festival::STATUS_COLLECTION));
    }

    private function participant(Festival $festival, User $user): ?FestivalParticipant
    {
        return FestivalParticipant::query()->where('festival_id', $festival->id)->where('user_id', $user->id)->first();
    }

    private function requireWinner(User $user): void
    {
        $festival = $this->requirePhase(Festival::STATUS_REWARDS);
        $participant = $this->participant($festival, $user);
        if ($participant === null || $participant->side !== $festival->winner_side) {
            throw ValidationException::withMessages(['shop' => __('festival.winners_only')]);
        }
    }

    private function requirePhase(string $status): Festival
    {
        $festival = $this->current();
        if ($festival === null || $festival->status !== $status) {
            throw ValidationException::withMessages(['phase' => __('festival.wrong_phase')]);
        }

        return $festival;
    }

    private function rate(int $shareItemId): FestivalStoneRate
    {
        return FestivalStoneRate::query()->with('item')->where('share_item_id', $shareItemId)->first()
            ?? throw ValidationException::withMessages(['stone' => __('festival.unknown_stone')]);
    }

    private function takeItem(User $user, FestivalStoneRate $rate, int $quantity): void
    {
        $have = (int) ($this->backpack->countByShareItemIds($user, [(int) $rate->share_item_id])[(int) $rate->share_item_id] ?? 0);
        if ($have < $quantity || ! $this->removeTotal($user, $rate->item, $quantity)) {
            throw ValidationException::withMessages(['stone' => __('festival.not_enough_stones')]);
        }
    }

    /** Списывает количество, даже если предмет лежит в рюкзаке несколькими стеками. */
    private function removeTotal(User $user, ShareItem $item, int $quantity): bool
    {
        while ($quantity > 0) {
            $stack = $this->backpack->getItem($user, $item);
            if ($stack === null) {
                return false;
            }
            $take = min((int) $stack->count, $quantity);
            if (! $this->backpack->removeItemByShareItem($user, $item, $take)) {
                return false;
            }
            $quantity -= $take;
        }

        return true;
    }

    private function coin(FestivalSettings $settings): ShareItem
    {
        return $settings->coin ?? throw ValidationException::withMessages(['coin' => __('festival.not_configured')]);
    }

    private function quantity(int $quantity): int
    {
        if ($quantity < 1 || $quantity > self::MAX_QUANTITY) {
            throw ValidationException::withMessages(['quantity' => __('festival.invalid_quantity')]);
        }

        return $quantity;
    }

    private function lockUser(User $user): void
    {
        User::without('player')->whereKey($user->id)->lockForUpdate()->firstOrFail();
    }

    private function requireNpc(User $user, int $npcId): FestivalSettings
    {
        $settings = $this->settings();
        if ($settings === null || (int) $settings->npc_id !== $npcId || (int) $settings->npc?->location_id !== (int) $user->location_id) {
            throw ValidationException::withMessages(['npc' => __('festival.wrong_npc')]);
        }

        return $settings;
    }
}
