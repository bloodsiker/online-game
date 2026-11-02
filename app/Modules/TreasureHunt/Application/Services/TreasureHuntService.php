<?php

declare(strict_types=1);

namespace App\Modules\TreasureHunt\Application\Services;

use App\Modules\Backpack\Domain\Models\Backpack;
use App\Modules\Backpack\Domain\Services\BackpackService;
use App\Modules\Battle\Domain\Enums\BattleDetailStatus;
use App\Modules\Battle\Domain\Enums\BattleStatus;
use App\Modules\Battle\Infrastructure\Persistence\Models\BattleDetail;
use App\Modules\Chat\Application\DTOs\LocalizedChatMessage;
use App\Modules\Chat\Application\Services\ChatService;
use App\Modules\Item\Infrastructure\Persistence\Models\LockpickingAttempt;
use App\Modules\ItemAssembly\Application\Services\AssembleItem;
use App\Modules\ItemAssembly\Domain\Enums\AssemblyResultMode;
use App\Modules\ItemAssembly\Infrastructure\Persistence\Models\ItemAssembly;
use App\Modules\Location\Infrastructure\Persistence\Models\GatheringAttempt;
use App\Modules\Player\Infrastructure\Persistence\Models\Player;
use App\Modules\Player\Infrastructure\Persistence\Models\PlayerSlot;
use App\Modules\Reputation\Application\Services\ReputationService;
use App\Modules\Reputation\Infrastructure\Persistence\Models\PlayerReputation;
use App\Modules\Share\Domain\Enums\ShareItemType;
use App\Modules\TreasureHunt\Infrastructure\Persistence\Models\TreasureHunt;
use App\Modules\TreasureHunt\Infrastructure\Persistence\Models\TreasureMapConfig;
use App\Modules\User\Infrastructure\Persistence\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

final class TreasureHuntService
{
    public function __construct(
        private readonly BackpackService $backpack,
        private readonly ReputationService $reputations,
        private readonly ChatService $chat,
        private readonly TreasureSearchRandom $random,
    ) {}

    public function check(User $user, int $itemId): array
    {
        return $this->locked($user, function (User $user) use ($itemId): array {
            $slot = $this->ownedMap($user, $itemId);
            $config = TreasureMapConfig::where('share_item_id', $slot->item->share_item_id)->where('is_active', true)->first();
            $this->require($config !== null, 'treasure.unavailable');
            $points = $this->points($user, $config);
            if ($points < $config->required_reputation) {
                throw ValidationException::withMessages(['treasure' => __('treasure.reputation_required', ['required' => $config->required_reputation, 'current' => $points])]);
            }
            $this->require(! TreasureHunt::where('user_id', $user->id)->whereNotNull('map_item_id')->whereNotNull('activated_at')->whereNull('found_at')->where('map_item_id', '!=', $itemId)->exists(), 'treasure.another_map');
            $hunt = TreasureHunt::where('map_item_id', $itemId)->lockForUpdate()->first();
            $this->require($hunt !== null, 'treasure.unavailable');
            $this->require((int) $user->currentLocation?->map_id === (int) $hunt->target_map_id && $user->currentLocation?->dungeon_id === null, 'treasure.wrong_territory');
            $this->require((int) $hunt->user_id === (int) $user->id && $hunt->found_at === null, 'treasure.unavailable');
            $this->require((int) $hunt->target_location_id === (int) $user->location_id, 'treasure.wrong_location');
            $hunt->activated_at ??= now();
            $hunt->save();

            return ['ok' => true, 'message' => __('treasure.activated', ['location' => $hunt->target_location_id]), 'location_id' => (int) $hunt->target_location_id];
        });
    }

    public function assemble(User $user, int $fragmentId): array
    {
        return $this->locked($user, function (User $user) use ($fragmentId): array {
            $slot = Backpack::where('user_id', $user->id)->where('item_id', $fragmentId)->where('equipped', 0)->where('count', '>', 0)->first();
            $this->require($slot !== null && $slot->item->itemInfo->type === ShareItemType::TREASURE_MAP_FRAGMENT
                && ($slot->item->expires_at === null || $slot->item->expires_at->isFuture()), 'treasure.fragments_unavailable');
            $assembly = ItemAssembly::where('type', 'treasure_map')
                ->where('is_active', true)
                ->whereHas('components', fn ($query) => $query->where('share_items.id', $slot->item->share_item_id))->lockForUpdate()->first();
            $this->require($assembly !== null, 'treasure.fragments_unavailable');
            $this->assertAssemblyConfiguration($assembly);
            $result = app(AssembleItem::class)->execute(user: $user, assemblyId: (int) $assembly->id);
            if (! $result->ok) {
                return ['ok' => false, 'message' => $result->message];
            }
            $hunt = TreasureHunt::with('targetMap')->where('map_item_id', $result->itemInstanceId)->where('user_id', $user->id)->firstOrFail();

            return ['ok' => true, 'message' => __('treasure.assembled', [
                'name' => $assembly->rewards->first()->name, 'territory' => $hunt->targetMap->name,
            ])];
        });
    }

    private function assertAssemblyConfiguration(ItemAssembly $assembly): void
    {
        $assembly->load(['components', 'rewards']);
        $reward = $assembly->rewards->first();
        $violations = array_keys(array_filter([
            'result_must_be_fixed' => $assembly->result_mode !== AssemblyResultMode::FIXED,
            'requires_four_components' => $assembly->components->count() !== 4,
            'requires_one_reward' => $assembly->rewards->count() !== 1,
            'invalid_component_type_or_quantity' => $assembly->components->contains(fn ($part) => $part->type !== ShareItemType::TREASURE_MAP_FRAGMENT || (int) $part->pivot->count !== 1),
            'invalid_reward_type_or_quantity' => $reward?->type !== ShareItemType::TREASURE_MAP || (int) $reward?->pivot?->count !== 1,
            'invalid_reward_availability_or_weight' => $reward === null || ! $reward->is_active || (int) $reward->pivot->weight < 1,
            'rarity_mismatch' => $assembly->components->contains(fn ($part) => $part->rarity !== $reward?->rarity),
        ]));
        if ($violations === []) {
            return;
        }
        Log::error('Invalid treasure map assembly configuration.', [
            'assembly_id' => (int) $assembly->id,
            'violations' => $violations,
            'component_ids' => $assembly->components->modelKeys(),
            'reward_ids' => $assembly->rewards->modelKeys(),
        ]);
        throw ValidationException::withMessages(['treasure' => __('treasure.assembly_configuration_error', ['id' => $assembly->id])]);
    }

    public function start(User $user, int $huntId): array
    {
        return $this->locked($user, function (User $user) use ($huntId): array {
            $hunt = $this->ownedHunt($user, $huntId);
            $this->require($hunt->expires_at === null || $hunt->expires_at->lte(now()), 'treasure.already_searching');
            $this->require(! GatheringAttempt::where('player_id', $user->player->id)->where('expires_at', '>', now())->exists()
                && ! LockpickingAttempt::where('player_id', $user->player->id)->where('expires_at', '>', now())->exists(), 'treasure.busy');
            $config = $hunt->config;
            $this->require($config->is_active, 'treasure.unavailable');
            $points = $this->points($user, $config);
            $this->require($points >= $config->required_reputation, 'treasure.reputation_lost');
            $tier = $config->reputation->tiers()->where('min_points', '<=', $points)->orderByDesc('min_points')->first();
            $bonus = $tier ? DB::table('treasure_reputation_bonuses')->where('reputation_tier_id', $tier->id)->first() : null;
            $seconds = max(1, (int) ceil($config->search_duration_seconds * (1 - min(90, max(0, (float) ($bonus->search_speed_bonus_percent ?? 0))) / 100)));
            $hunt->fill([
                'search_token' => (string) Str::uuid(), 'started_at' => now(),
                'completes_at' => now()->addSeconds($seconds), 'expires_at' => now()->addSeconds($seconds + 120),
                'chance_snapshot' => min(100, max(0, $config->success_chance_percent + (float) ($bonus->search_chance_bonus_percent ?? 0))),
            ])->save();

            return ['ok' => true, ...$this->attemptState($hunt)];
        });
    }

    public function complete(User $user, int $huntId, string $token): array
    {
        return $this->locked($user, function (User $user) use ($huntId, $token): array {
            $hunt = $this->ownedHunt($user, $huntId);
            $this->require($hunt->search_token !== null && hash_equals($hunt->search_token, $token), 'treasure.no_attempt');
            $this->require($hunt->completes_at !== null && $hunt->completes_at->lte(now()), 'treasure.too_early');
            if ($hunt->expires_at->lte(now())) {
                $this->clearAttempt($hunt);

                return ['ok' => false, 'message' => __('treasure.expired')];
            }
            $this->require($hunt->config->is_active, 'treasure.unavailable');
            $chance = (float) $hunt->chance_snapshot;
            $this->clearAttempt($hunt);
            if (! $this->random->succeeds($chance)) {
                return ['ok' => true, 'found' => false, 'message' => __('treasure.failed')];
            }

            $config = $hunt->config;
            $reward = $config->reward;
            $this->require($reward?->is_active && $reward->type === ShareItemType::CHEST && (int) $reward->lockConfig?->lock_required_skill > 0, 'treasure.unavailable');
            $slot = $this->ownedMap($user, (int) $hunt->map_item_id);
            $chest = $this->backpack->addItemByShareItem($user, $reward);
            $this->reputations->addPoints($user->player, $config->reputation, (int) $config->reputation_reward, touchCooldown: false);
            $hunt->fill(['found_at' => now(), 'reward_item_id' => $chest->item_id, 'reputation_awarded' => $config->reputation_reward])->save();
            PlayerSlot::where('player_id', $user->player->id)->where('entity_type', 'item')->where('entity_id', $slot->item_id)->delete();
            $slot->delete();
            $slot->item->delete();
            $this->chat->sendLootToUser($user, new LocalizedChatMessage('chat.treasure_found', [
                'item_id' => (int) $reward->id, 'amount' => (int) $config->reputation_reward,
                'reputation' => LocalizedChatMessage::contentName($config->reputation),
            ]));

            return [
                'ok' => true, 'found' => true, 'message' => __('treasure.found', ['name' => $reward->name, 'amount' => $config->reputation_reward]),
                'reward' => ['id' => (int) $chest->item_id, 'share_item_id' => (int) $reward->id, 'name' => $reward->name, 'image' => $reward->image],
                'open_url' => route('items.lockpick.show', ['id' => $chest->item_id, 'modal' => 1]),
            ];
        });
    }

    public function cancel(User $user, int $huntId, string $token): array
    {
        return $this->locked($user, function (User $user) use ($huntId, $token): array {
            $hunt = TreasureHunt::where('id', $huntId)->where('user_id', $user->id)->whereNull('found_at')->lockForUpdate()->first();
            $this->require($hunt !== null && $hunt->search_token !== null && hash_equals($hunt->search_token, $token), 'treasure.no_attempt');
            $this->clearAttempt($hunt);

            return ['ok' => true, 'message' => __('treasure.cancelled')];
        }, checkContext: false);
    }

    public function activeAtLocation(User $user): ?array
    {
        if (! Schema::hasTable('treasure_hunts')) {
            return null;
        }
        $hunt = TreasureHunt::with('config.item')->where('user_id', $user->id)->where('target_location_id', $user->location_id)
            ->whereNotNull('map_item_id')->whereNotNull('activated_at')->whereNull('found_at')->first();
        if ($hunt === null || ! $hunt->config?->is_active || ! Backpack::where('user_id', $user->id)->where('item_id', $hunt->map_item_id)->where('equipped', 0)->exists()) {
            return null;
        }

        return ['id' => $hunt->id, 'name' => $hunt->config->item->name, ...$this->attemptState($hunt)];
    }

    private function attemptState(TreasureHunt $hunt): array
    {
        return ['token' => $hunt->search_token, 'server_time' => now()->getTimestampMs(), 'started_at' => $hunt->started_at?->getTimestampMs(), 'completes_at' => $hunt->completes_at?->getTimestampMs(), 'expires_at' => $hunt->expires_at?->getTimestampMs()];
    }

    private function locked(User $user, \Closure $action, bool $checkContext = true): array
    {
        return DB::transaction(function () use ($user, $action, $checkContext): array {
            $player = Player::whereKey($user->player->id)->lockForUpdate()->firstOrFail();
            $locked = User::without('player')->whereKey($user->id)->lockForUpdate()->firstOrFail();
            $locked->setRelation('player', $player);
            if ($checkContext) {
                $this->require((int) $player->hp_now > 0, 'treasure.dead');
                $this->require(! BattleDetail::where('user_id', $locked->id)->where('status', BattleDetailStatus::LIFE->value)
                    ->whereHas('battle', fn ($query) => $query->where('status', BattleStatus::ACTIVE->value)->where('location_id', $locked->location_id))->exists(), 'treasure.battle');
            }

            return $action($locked);
        }, attempts: 3);
    }

    private function ownedMap(User $user, int $itemId): Backpack
    {
        $slot = Backpack::with('item.itemInfo')->where('user_id', $user->id)->where('item_id', $itemId)->where('equipped', 0)->lockForUpdate()->first();
        $this->require($slot !== null && (int) $slot->count === 1 && $slot->item?->itemInfo?->is_active && $slot->item->itemInfo->type === ShareItemType::TREASURE_MAP
            && ($slot->item->expires_at === null || $slot->item->expires_at->isFuture()), 'treasure.no_map');

        return $slot;
    }

    private function ownedHunt(User $user, int $huntId): TreasureHunt
    {
        $hunt = TreasureHunt::with(['config.reputation', 'config.reward.lockConfig'])->where('id', $huntId)->where('user_id', $user->id)->whereNotNull('activated_at')->whereNull('found_at')->lockForUpdate()->first();
        $this->require($hunt !== null && $hunt->map_item_id !== null, 'treasure.no_map');
        $this->require((int) $hunt->target_location_id === (int) $user->location_id, 'treasure.wrong_location');
        $slot = $this->ownedMap($user, (int) $hunt->map_item_id);
        $this->require((int) $slot->item->share_item_id === (int) $hunt->config->share_item_id, 'treasure.unavailable');

        return $hunt;
    }

    private function points(User $user, TreasureMapConfig $config): int
    {
        return (int) PlayerReputation::where('player_id', $user->player->id)->where('reputation_id', $config->reputation_id)->value('points');
    }

    private function clearAttempt(TreasureHunt $hunt): void
    {
        $hunt->fill(['search_token' => null, 'started_at' => null, 'completes_at' => null, 'expires_at' => null, 'chance_snapshot' => null])->save();
    }

    private function require(bool $condition, string $key): void
    {
        if (! $condition) {
            throw ValidationException::withMessages(['treasure' => __($key)]);
        }
    }
}
