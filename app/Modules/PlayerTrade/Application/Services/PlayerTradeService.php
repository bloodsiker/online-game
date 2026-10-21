<?php

declare(strict_types=1);

namespace App\Modules\PlayerTrade\Application\Services;

use App\Modules\Backpack\Domain\Models\Backpack;
use App\Modules\Chat\Application\DTOs\LocalizedChatMessage;
use App\Modules\Chat\Application\Services\ChatService;
use App\Modules\Item\Infrastructure\Persistence\Models\Item;
use App\Modules\PlayerTrade\Domain\Events\PlayerTradeChanged;
use App\Modules\PlayerTrade\Infrastructure\Persistence\Models\PlayerTradeSession;
use App\Modules\User\Infrastructure\Persistence\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;

class PlayerTradeService
{
    public function __construct(private readonly ChatService $chatService) {}

    public function participantMoved(int $userId): void
    {
        DB::transaction(function () use ($userId): void {
            $trades = PlayerTradeSession::query()
                ->whereIn('status', [PlayerTradeSession::PENDING, PlayerTradeSession::ACTIVE])
                ->where('expires_at', '>', now())
                ->where(fn ($query) => $query->where('initiator_user_id', $userId)->orWhere('target_user_id', $userId))
                ->orderBy('id')
                ->lockForUpdate()
                ->get();

            foreach ($trades as $trade) {
                if (! $this->clearReadiness($trade)) {
                    PlayerTradeChanged::dispatch($trade->uuid);
                }
            }
        });
    }

    public function resumableForUser(User $user): ?PlayerTradeSession
    {
        return PlayerTradeSession::query()
            ->where('expires_at', '>', now())
            ->where(function ($query) use ($user): void {
                $query->where(function ($query) use ($user): void {
                    $query->where('status', PlayerTradeSession::ACTIVE)
                        ->where(fn ($query) => $query->where('initiator_user_id', $user->id)->orWhere('target_user_id', $user->id));
                })->orWhere(function ($query) use ($user): void {
                    $query->where('status', PlayerTradeSession::PENDING)
                        ->where('initiator_user_id', $user->id);
                });
            })
            ->latest('id')
            ->first();
    }

    public function invite(User $initiator, int $targetId): PlayerTradeSession
    {
        if ($initiator->id === $targetId) {
            throw new RuntimeException(__('Нельзя предложить сделку самому себе.'));
        }

        $target = User::query()->find($targetId);
        if (! $target) {
            throw new RuntimeException(__('Игрок не найден.'));
        }

        $trade = DB::transaction(function () use ($initiator, $target): PlayerTradeSession {
            $userIds = [(int) $initiator->id, (int) $target->id];
            sort($userIds);
            $users = User::query()->whereIn('id', $userIds)->orderBy('id')->lockForUpdate()->get()->keyBy('id');
            if (! $this->areNearby($users->get($initiator->id), $users->get($target->id))) {
                throw new RuntimeException(__('Игрок находится не рядом с вами. Торговать можно только на одной локации.'));
            }
            $this->ensureNoOpenTrade((int) $initiator->id);
            $this->ensureNoOpenTrade((int) $target->id);

            return PlayerTradeSession::query()->create([
                'uuid' => (string) Str::uuid(),
                'initiator_user_id' => $initiator->id,
                'target_user_id' => $target->id,
                'status' => PlayerTradeSession::PENDING,
                'expires_at' => now()->addMinutes(15),
            ]);
        });

        $message = $this->chatService->sendPlayerTradeInviteToUser(
            $target,
            new LocalizedChatMessage('chat.player_trade_invite', [
                'member' => $this->memberLabel($initiator),
                'accept_url' => route('player-trade.accept', $trade->uuid),
                'decline_url' => route('player-trade.decline', $trade->uuid),
            ]),
        );

        $trade->update(['chat_message_id' => $message->id]);

        return $trade;
    }

    public function accept(User $user, string $uuid): PlayerTradeSession
    {
        $trade = DB::transaction(function () use ($user, $uuid): PlayerTradeSession {
            $trade = PlayerTradeSession::query()->where('uuid', $uuid)->lockForUpdate()->first();
            if (! $trade || (int) $trade->target_user_id !== (int) $user->id || $trade->status !== PlayerTradeSession::PENDING) {
                throw new RuntimeException(__('Приглашение на торговлю не найдено или уже обработано.'));
            }
            if ($trade->expires_at?->isPast()) {
                $trade->update(['status' => PlayerTradeSession::CANCELLED]);

                return $trade;
            }

            $users = $this->lockParticipants($trade);
            if (! $this->areNearby($users->get($trade->initiator_user_id), $users->get($trade->target_user_id))) {
                throw new RuntimeException(__('Игрок находится не рядом с вами. Торговать можно только на одной локации.'));
            }
            $this->ensureNoOtherOpenTrade((int) $user->id, (int) $trade->id);
            $this->ensureNoOtherOpenTrade((int) $trade->initiator_user_id, (int) $trade->id);
            $trade->update(['status' => PlayerTradeSession::ACTIVE, 'expires_at' => now()->addMinutes(30)]);

            return $trade;
        });
        if ($trade->status !== PlayerTradeSession::ACTIVE) {
            throw new RuntimeException(__('Срок приглашения на торговлю истёк.'));
        }
        $this->chatService->removeSystemMessageForUser($user, $trade->chat_message_id);

        return $trade->fresh(['initiator.player', 'target.player']);
    }

    public function decline(User $user, string $uuid): void
    {
        $trade = DB::transaction(function () use ($user, $uuid): PlayerTradeSession {
            $trade = PlayerTradeSession::query()->where('uuid', $uuid)->lockForUpdate()->first();
            if (! $trade || (int) $trade->target_user_id !== (int) $user->id || $trade->status !== PlayerTradeSession::PENDING) {
                throw new RuntimeException(__('Приглашение на торговлю не найдено или уже обработано.'));
            }

            $trade->update(['status' => PlayerTradeSession::CANCELLED]);

            return $trade;
        });
        $this->chatService->removeSystemMessageForUser($user, $trade->chat_message_id);
    }

    public function page(User $user, string $uuid): array
    {
        $trade = $this->findForUser($user, $uuid);
        if ($trade->status === PlayerTradeSession::PENDING && (int) $trade->initiator_user_id !== (int) $user->id) {
            throw new RuntimeException(__('Примите приглашение в чате, чтобы открыть сделку.'));
        }
        if ($trade->expires_at?->isPast() && in_array($trade->status, [PlayerTradeSession::PENDING, PlayerTradeSession::ACTIVE], true)) {
            $trade->update(['status' => PlayerTradeSession::CANCELLED]);
        }

        $inventory = Backpack::query()
            ->with(['item.itemInfo'])
            ->where('user_id', $user->id)
            ->where('equipped', false)
            ->whereHas('item.itemInfo', fn ($query) => $query->where('is_give', 1))
            ->orderBy('id')
            ->get()
            ->filter(fn (Backpack $row) => ! $row->item?->expires_at || $row->item->expires_at->isFuture())
            ->values();

        return [
            'trade' => $trade->loadMissing(['initiator.player', 'target.player']),
            'inventory' => $inventory,
            'mySide' => (int) $trade->initiator_user_id === (int) $user->id ? 'initiator' : 'target',
            'csrfToken' => csrf_token(),
            'expiresAt' => $trade->expires_at?->toIso8601String(),
            'canTrade' => $this->areNearby($trade->initiator, $trade->target),
            'tradeBlockMessage' => $this->proximityBlockMessage($trade, (int) $user->id),
        ];
    }

    public function state(User $user, string $uuid): array
    {
        return DB::transaction(function () use ($user, $uuid): array {
            $trade = $this->findForUser($user, $uuid, lock: true);
            if ($trade->expires_at?->isPast() && in_array($trade->status, [PlayerTradeSession::PENDING, PlayerTradeSession::ACTIVE], true)) {
                $trade->update(['status' => PlayerTradeSession::CANCELLED]);
            }
            if ($trade->status === PlayerTradeSession::ACTIVE && ! $this->areNearby($trade->initiator, $trade->target)) {
                $this->clearReadiness($trade);
            }

            return $this->statePayload($trade, (int) $user->id);
        });
    }

    public function itemForTooltip(User $user, string $uuid, int $itemId): Item
    {
        $trade = $this->findForUser($user, $uuid);
        $partnerSide = (int) $trade->initiator_user_id === (int) $user->id ? 'target' : 'initiator';
        $partnerId = (int) $trade->{$partnerSide.'_user_id'};
        $offeredIds = array_map('intval', array_keys($trade->{$partnerSide.'_offer'}['items'] ?? []));

        $row = Backpack::query()
            ->with('item.itemInfo')
            ->where('item_id', $itemId)
            ->where('equipped', false)
            ->whereHas('item.itemInfo', fn ($query) => $query->where('is_give', 1))
            ->whereHas('item', fn ($query) => $query->whereNull('expires_at')->orWhere('expires_at', '>', now()))
            ->where(function ($query) use ($user, $partnerId, $offeredIds): void {
                $query->where('user_id', $user->id)
                    ->orWhere(fn ($query) => $query->where('user_id', $partnerId)->whereIn('id', $offeredIds));
            })
            ->firstOrFail();

        return $row->item;
    }

    public function saveOffer(User $user, string $uuid, array $items, int $money): array
    {
        $blockReason = DB::transaction(function () use ($user, $uuid, $items, $money): ?string {
            $trade = PlayerTradeSession::query()->where('uuid', $uuid)->lockForUpdate()->first();
            if (! $trade || ! in_array((int) $user->id, [(int) $trade->initiator_user_id, (int) $trade->target_user_id], true)
                || $trade->status !== PlayerTradeSession::ACTIVE || $trade->expires_at?->isPast()) {
                throw new RuntimeException(__('Сделка не активна или истёк срок её действия.'));
            }
            $users = $this->lockParticipants($trade);
            if (! $this->areNearby($users->get($trade->initiator_user_id), $users->get($trade->target_user_id))) {
                $this->clearReadiness($trade);

                return __('Игрок находится не рядом с вами. Торговать можно только на одной локации.');
            }
            $lockedUser = $users->get($user->id);
            if ($money < 0 || $money > (int) $lockedUser->money) {
                throw new RuntimeException(__('Недостаточно монет для такого предложения.'));
            }

            $cleanItems = [];
            foreach ($items as $entry) {
                $backpackId = (int) ($entry['backpack_id'] ?? 0);
                $count = (int) ($entry['count'] ?? 0);
                if ($backpackId < 1 || $count < 1) {
                    continue;
                }
                $row = Backpack::query()->with('item.itemInfo')->whereKey($backpackId)
                    ->where('user_id', $user->id)->where('equipped', false)->lockForUpdate()->first();
                if (! $row || ! $row->item || ! $row->item->itemInfo->is_give || ($row->item->expires_at && $row->item->expires_at->isPast())) {
                    throw new RuntimeException(__('Один из выбранных предметов больше нельзя предложить.'));
                }
                if ($count > (int) $row->count || (! $row->item->itemInfo->is_stackable && $count !== (int) $row->count)) {
                    throw new RuntimeException(__('Проверьте количество предмета в предложении.'));
                }
                $cleanItems[$backpackId] = $count;
            }

            $side = (int) $trade->initiator_user_id === (int) $user->id ? 'initiator' : 'target';
            $trade->update([
                $side.'_offer' => ['items' => $cleanItems, 'money' => $money],
                'initiator_confirmed' => false,
                'target_confirmed' => false,
            ]);

            return null;
        });
        if ($blockReason !== null) {
            throw new RuntimeException($blockReason);
        }

        return $this->state($user, $uuid);
    }

    public function confirm(User $user, string $uuid, bool $confirmed = true): array
    {
        $blockReason = DB::transaction(function () use ($user, $uuid, $confirmed): ?string {
            $trade = PlayerTradeSession::query()->where('uuid', $uuid)->lockForUpdate()->first();
            if (! $trade || ! in_array((int) $user->id, [(int) $trade->initiator_user_id, (int) $trade->target_user_id], true)) {
                throw new RuntimeException(__('Сделка не найдена.'));
            }
            if ($trade->status !== PlayerTradeSession::ACTIVE) {
                throw new RuntimeException(__('Сделка больше не активна.'));
            }
            if ($trade->expires_at?->isPast()) {
                $trade->update(['status' => PlayerTradeSession::CANCELLED]);

                return __('Срок сделки истёк.');
            }

            $users = $this->lockParticipants($trade);
            if (! $this->areNearby($users->get($trade->initiator_user_id), $users->get($trade->target_user_id))) {
                $this->clearReadiness($trade);

                return __('Игрок находится не рядом с вами. Торговать можно только на одной локации.');
            }

            $side = (int) $trade->initiator_user_id === (int) $user->id ? 'initiator' : 'target';
            $trade->update([$side.'_confirmed' => $confirmed]);
            $trade->refresh();

            if ($trade->initiator_confirmed && $trade->target_confirmed) {
                $this->complete($trade, $users);
            }

            return null;
        });
        if ($blockReason !== null) {
            throw new RuntimeException($blockReason);
        }

        return $this->state($user, $uuid);
    }

    public function cancel(User $user, string $uuid): void
    {
        DB::transaction(function () use ($user, $uuid): void {
            $trade = PlayerTradeSession::query()->where('uuid', $uuid)->lockForUpdate()->first();
            if (! $trade || ! in_array((int) $user->id, [(int) $trade->initiator_user_id, (int) $trade->target_user_id], true)) {
                throw new RuntimeException(__('Сделка не найдена.'));
            }
            if (! in_array($trade->status, [PlayerTradeSession::PENDING, PlayerTradeSession::ACTIVE], true)) {
                throw new RuntimeException(__('Сделка уже завершена.'));
            }
            $trade->update(['status' => PlayerTradeSession::CANCELLED]);
        });
    }

    private function complete(PlayerTradeSession $trade, Collection $users): void
    {
        $initiator = $users->get((int) $trade->initiator_user_id);
        $target = $users->get((int) $trade->target_user_id);
        if (! $initiator || ! $target) {
            throw new RuntimeException(__('Один из участников сделки больше недоступен.'));
        }

        $offers = [
            (int) $initiator->id => $trade->initiator_offer ?? ['items' => [], 'money' => 0],
            (int) $target->id => $trade->target_offer ?? ['items' => [], 'money' => 0],
        ];
        foreach ($offers as $ownerId => $offer) {
            if ((int) ($offer['money'] ?? 0) > (int) $users->get($ownerId)->money) {
                throw new RuntimeException(__('Монет в кошельке стало недостаточно; сделка не завершена.'));
            }
        }

        $transferRows = [];
        foreach ($offers as $ownerId => $offer) {
            $receiverId = $ownerId === (int) $initiator->id ? (int) $target->id : (int) $initiator->id;
            foreach (($offer['items'] ?? []) as $backpackId => $count) {
                $row = Backpack::query()->with('item.itemInfo')->whereKey((int) $backpackId)
                    ->where('user_id', $ownerId)->where('equipped', false)->lockForUpdate()->first();
                if (! $row || ! $row->item?->itemInfo->is_give || (int) $count < 1 || (int) $count > (int) $row->count
                    || (! $row->item->itemInfo->is_stackable && (int) $count !== (int) $row->count)
                    || ($row->item->expires_at && $row->item->expires_at->isPast())) {
                    throw new RuntimeException(__('Состав сделки изменился: предмет больше нельзя передать.'));
                }
                $transferRows[] = [$row, $receiverId, (int) $count, (int) $row->count];
            }
        }

        foreach ($offers as $ownerId => $offer) {
            $amount = (int) ($offer['money'] ?? 0);
            if ($amount > 0) {
                $users->get($ownerId)->decrement('money', $amount);
                $receiverId = $ownerId === (int) $initiator->id ? (int) $target->id : (int) $initiator->id;
                $users->get($receiverId)->increment('money', $amount);
            }
        }

        foreach ($transferRows as [$row, $receiverId, $count, $originalCount]) {
            if ($count === $originalCount) {
                $row->update(['user_id' => $receiverId]);

                continue;
            }

            $row->decrement('count', $count);
            $copy = Item::create(['share_item_id' => $row->item->share_item_id]);
            $copy->count_use = $row->item->count_use;
            $copy->expires_at = $row->item->expires_at;
            $copy->save();

            $newBackpackRow = new Backpack;
            $newBackpackRow->user_id = $receiverId;
            $newBackpackRow->item_id = $copy->id;
            $newBackpackRow->count = $count;
            $newBackpackRow->equipped = false;
            $newBackpackRow->sort_order = 0;
            $newBackpackRow->save();
        }

        $trade->update(['status' => PlayerTradeSession::COMPLETED, 'completed_at' => now()]);
    }

    private function statePayload(PlayerTradeSession $trade, int $viewerId): array
    {
        return [
            'status' => $trade->status,
            'expires_at' => $trade->expires_at?->toIso8601String(),
            'initiator_confirmed' => $trade->initiator_confirmed,
            'target_confirmed' => $trade->target_confirmed,
            'initiator' => $this->participantPayload($trade, 'initiator'),
            'target' => $this->participantPayload($trade, 'target'),
            'my_side' => (int) $trade->initiator_user_id === $viewerId ? 'initiator' : 'target',
            'can_trade' => $this->areNearby($trade->initiator, $trade->target),
            'block_message' => $this->proximityBlockMessage($trade, $viewerId),
        ];
    }

    private function participantPayload(PlayerTradeSession $trade, string $side): array
    {
        $offer = $trade->{$side.'_offer'} ?? ['items' => [], 'money' => 0];
        $items = [];
        if (! empty($offer['items'])) {
            $rows = Backpack::query()->with('item.itemInfo')
                ->where('user_id', $trade->{$side.'_user_id'})
                ->where('equipped', false)
                ->whereHas('item.itemInfo', fn ($query) => $query->where('is_give', 1))
                ->whereIn('id', array_map('intval', array_keys($offer['items'])))->get()->keyBy('id');
            foreach ($offer['items'] as $id => $count) {
                $row = $rows->get((int) $id);
                if ($row) {
                    $items[] = ['backpack_id' => (int) $row->id, 'item_id' => (int) $row->item_id, 'name' => $row->item->itemInfo->name, 'image' => asset($row->item->itemInfo->image), 'count' => (int) $count];
                }
            }
        }

        return [
            'name' => $trade->{$side}->player?->name ?? $trade->{$side}->name,
            'items' => $items,
            'money' => (int) ($offer['money'] ?? 0),
            'confirmed' => (bool) $trade->{$side.'_confirmed'},
        ];
    }

    private function findForUser(User $user, string $uuid, bool $lock = false): PlayerTradeSession
    {
        return PlayerTradeSession::query()->with(['initiator.player', 'target.player'])
            ->where('uuid', $uuid)
            ->where(fn ($query) => $query->where('initiator_user_id', $user->id)->orWhere('target_user_id', $user->id))
            ->when($lock, fn ($query) => $query->lockForUpdate())
            ->firstOrFail();
    }

    private function areNearby(?User $initiator, ?User $target): bool
    {
        return $initiator !== null && $target !== null
            && (int) $initiator->location_id > 0
            && (int) $initiator->location_id === (int) $target->location_id;
    }

    private function lockParticipants(PlayerTradeSession $trade): Collection
    {
        return User::query()
            ->whereIn('id', [$trade->initiator_user_id, $trade->target_user_id])
            ->orderBy('id')
            ->lockForUpdate()
            ->get()
            ->keyBy('id');
    }

    private function clearReadiness(PlayerTradeSession $trade): bool
    {
        if ($trade->initiator_confirmed || $trade->target_confirmed) {
            return $trade->update(['initiator_confirmed' => false, 'target_confirmed' => false]);
        }

        return false;
    }

    private function proximityBlockMessage(PlayerTradeSession $trade, int $viewerId): string
    {
        if ($this->areNearby($trade->initiator, $trade->target)) {
            return '';
        }

        $partner = (int) $trade->initiator_user_id === $viewerId ? $trade->target : $trade->initiator;

        return __('Игрок :name находится на другой локации. Торговля заблокирована, пока вы не окажетесь рядом.', [
            'name' => $partner?->name ?? __('Игрок'),
        ]);
    }

    private function findActiveForUser(User $user, string $uuid): PlayerTradeSession
    {
        $trade = $this->findForUser($user, $uuid);
        if ($trade->status !== PlayerTradeSession::ACTIVE || $trade->expires_at?->isPast()) {
            throw new RuntimeException(__('Сделка не активна или истёк срок её действия.'));
        }

        return $trade;
    }

    private function ensureNoOpenTrade(int $userId): void
    {
        $this->ensureNoOtherOpenTrade($userId, null);
    }

    private function ensureNoOtherOpenTrade(int $userId, ?int $exceptId): void
    {
        $query = PlayerTradeSession::query()->whereIn('status', [PlayerTradeSession::PENDING, PlayerTradeSession::ACTIVE])
            ->where('expires_at', '>', now())
            ->where(fn ($query) => $query->where('initiator_user_id', $userId)->orWhere('target_user_id', $userId));
        if ($exceptId !== null) {
            $query->where('id', '!=', $exceptId);
        }
        if ($query->exists()) {
            throw new RuntimeException(__('У одного из игроков уже есть незавершённая сделка.'));
        }
    }

    private function memberLabel(User $user): LocalizedChatMessage
    {
        $user->loadMissing(['player', 'clanMembership.clan']);
        $clan = $user->clanMembership?->clan;

        return new LocalizedChatMessage('chat.player_trade_member', [
            'name' => $user->player?->name ?? $user->name,
            'level' => (int) ($user->player?->lvl ?? 0),
            'user_id' => (int) $user->id,
            'icon' => asset('main/images/player_info.gif'),
            'clan_icon' => $clan?->icon
                ? new LocalizedChatMessage('chat.clan_icon', ['url' => Storage::disk('public')->url($clan->icon)])
                : '',
        ]);
    }
}
