<?php

declare(strict_types=1);

namespace App\Modules\Item\Presentation\Http;

use App\Http\Controllers\Controller;
use App\Modules\Backpack\Domain\Models\Backpack;
use App\Modules\Battle\Application\DTOs\AttackResultDTO;
use App\Modules\Battle\Application\Services\Combat\BattleEffectService;
use App\Modules\Item\Application\ItemEffect\ItemEffectStrategyFactory;
use App\Modules\Item\Application\Services\LockpickingService;
use App\Modules\Item\Application\UseCases\DropItem;
use App\Modules\Item\Application\UseCases\EquipItem;
use App\Modules\Item\Application\UseCases\GetChestPage;
use App\Modules\Item\Application\UseCases\GetHandOverPage;
use App\Modules\Item\Application\UseCases\GetItemInfoPage;
use App\Modules\Item\Application\UseCases\GetPickupItemsPage;
use App\Modules\Item\Application\UseCases\HandOverToUser;
use App\Modules\Item\Application\UseCases\OpenChest;
use App\Modules\Item\Application\UseCases\PickUpInChest;
use App\Modules\Item\Application\UseCases\UnequipItem;
use App\Modules\Item\Domain\Exceptions\ItemUseBlockedException;
use App\Modules\Item\Domain\Services\ItemUsagePolicyService;
use App\Modules\Location\Domain\Contracts\LocationReadRepository;
use App\Modules\Player\Domain\Services\PlayerRevivalService;
use App\Modules\Player\Domain\Services\PlayerStatService;
use App\Modules\Player\Infrastructure\Persistence\Models\Player;
use App\Modules\Quest\Domain\Services\QuestProgressService;
use App\Modules\Share\Domain\Enums\ItemEffectType;
use App\Modules\User\Infrastructure\Persistence\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ItemController extends Controller
{
    public function __construct(
        private readonly GetPickupItemsPage $getPickupItemsPage,
        private readonly DropItem $dropItem,
        private readonly GetHandOverPage $getHandOverPage,
        private readonly HandOverToUser $handOverToUser,
        private readonly EquipItem $equipItem,
        private readonly UnequipItem $unequipItem,
        private readonly OpenChest $openChest,
        private readonly GetChestPage $getChestPage,
        private readonly PickUpInChest $pickUpInChest,
        private readonly GetItemInfoPage $getItemInfoPage,
        private readonly PlayerStatService $statService,
        private readonly BattleEffectService $battleEffectService,
        private readonly LocationReadRepository $locationReadRepository,
        private readonly PlayerRevivalService $revivalService,
        private readonly ItemUsagePolicyService $itemUsagePolicyService,
        private readonly LockpickingService $lockpickingService,
        private readonly QuestProgressService $questProgressService,
    ) {}

    public function pickUp(int $id): mixed
    {
        /** @var User $user */
        $user = Auth::user();

        return view('item::pickup_items', [
            'page' => $this->getPickupItemsPage->execute($user, $id),
        ]);
    }

    public function dropItem(Request $request, int $id): RedirectResponse
    {
        /** @var User $user */
        $user = Auth::user();
        $result = $this->dropItem->execute(
            $user,
            $id,
            $request->integer('c'),
            $request->integer('qty', 0),
        );
        if ($result->message !== '') {
            session()->flash('message', $result->message);
        }

        return redirect()->route('backpack');
    }

    public function handOver(int $id): mixed
    {
        return view('item::hand_over', [
            'page' => $this->getHandOverPage->execute($id),
        ]);
    }

    public function handOverToUser(Request $request, int $id): mixed
    {
        /** @var User $user */
        $user = Auth::user();

        return view('item::hand_over', [
            'page' => $this->handOverToUser->execute($user, $id, $request->integer('uid') ?: null),
        ]);
    }

    public function putOn(int $id): RedirectResponse
    {
        /** @var User $user */
        $user = Auth::user();
        $result = $this->equipItem->execute($user, $id);

        if ($result->message !== '') {
            session()->flash('message', $result->message);
        } else {
            session()->flash('equip_changed', true);
        }
        if ($result->hotbarRefresh) {
            session()->flash('hotbar_refresh', true);
        }

        return redirect()->back();
    }

    public function putOff(int $id): RedirectResponse
    {
        /** @var User $user */
        $user = Auth::user();
        $result = $this->unequipItem->execute($user, $id);

        session()->flash('equip_changed', true);
        if ($result->hotbarRefresh) {
            session()->flash('hotbar_refresh', true);
        }

        return redirect()->back();
    }

    public function openChest(int $id): RedirectResponse
    {
        if ($redirect = $this->lockedChestRedirect($id)) {
            return $redirect;
        }

        /** @var User $user */
        $user = Auth::user();
        $itemId = $this->openChest->execute($user, $id);
        abort_if($itemId === null, 404);

        return redirect()->route('items.view_chest', ['id' => $itemId]);
    }

    public function viewChest(int $id): mixed
    {
        if ($redirect = $this->lockedChestRedirect($id)) {
            return $redirect;
        }

        /** @var User $user */
        $user = Auth::user();

        return view('item::chest_items', [
            'page' => $this->getChestPage->execute($user, $id),
        ]);
    }

    public function pickUpInChest(int $chest, int $id): mixed
    {
        if ($redirect = $this->lockedChestRedirect($chest)) {
            return $redirect;
        }

        /** @var User $user */
        $user = Auth::user();

        return view('item::chest_items', [
            'page' => $this->pickUpInChest->execute($user, $chest, $id),
        ]);
    }

    public function info(int $id): mixed
    {
        /** @var User $user */
        $user = Auth::user();

        return view('item::info', [
            'page' => $this->getItemInfoPage->execute($id, $user->player),
        ]);
    }

    public function infoByShareItem(int $id): mixed
    {
        /** @var User $user */
        $user = Auth::user();

        return view('item::info', [
            'page' => $this->getItemInfoPage->executeByShareItemId($id, $user->player),
        ]);
    }

    private function lockedChestRedirect(int $chestId): ?RedirectResponse
    {
        if (! $this->lockpickingService->isLockedChest($chestId)) {
            return null;
        }

        return redirect()->route('items.lockpick.show', ['id' => $chestId]);
    }

    public function useItem(Request $request, int $id): JsonResponse
    {
        /** @var User $user */
        $user = Auth::user();

        try {
            return DB::transaction(fn (): JsonResponse => $this->performUseItem($request, $id, $user));
        } catch (ItemUseBlockedException $exception) {
            $remainingSeconds = $exception->availableAt !== null
                ? max(0, (int) ceil(now()->diffInSeconds($exception->availableAt, false)))
                : null;

            return response()->json([
                'status' => 'error',
                'code' => $exception->errorCode,
                'message' => $exception->getMessage(),
                'available_at' => $exception->availableAt?->toIso8601String(),
                'remaining_seconds' => $remainingSeconds,
            ], 422);
        }
    }

    private function performUseItem(Request $request, int $id, User $user): JsonResponse
    {
        // Одна стабильная блокировка на игрока сериализует параллельные попытки
        // использования любых его предметов, включая создание первой строки лимита.
        $player = Player::query()->whereKey($user->player_id)->lockForUpdate()->firstOrFail();

        $backpack = Backpack::with(['item.itemInfo.effects', 'item.itemInfo.buffs.effect', 'item.itemInfo.debuffs.effect'])
            ->where('user_id', $user->id)
            ->where('item_id', $id)
            ->where('equipped', 0)
            ->lockForUpdate()
            ->first();

        if (! $backpack) {
            return response()->json(['status' => 'error', 'message' => 'Предмет не найден.'], 422);
        }

        $gate = $this->locationReadRepository->findTeleportUseGate(
            (int) $backpack->item->share_item_id,
            (int) $user->location_id,
        );

        if ($gate !== null) {
            $questUsePlan = $this->questProgressService->prepareItemUse(
                $player,
                (int) $backpack->item->share_item_id,
            );
            $this->itemUsagePolicyService->reserveUse($player, $backpack->item->itemInfo);

            $questUse = $this->questProgressService->progressPreparedItemUse($questUsePlan);

            $user->prev_location_id = $user->location_id;
            $user->location_id = $gate->to_location_id;
            $user->save();

            $removed = false;
            $newCount = $backpack->count;
            if ($gate->consume_item) {
                ['removed' => $removed, 'count' => $newCount] = $this->consumeBackpackItem($backpack);
            }

            return response()->json([
                'status' => 'success',
                'removed' => $removed,
                'count' => $newCount,
                'teleport_url' => route('location'),
                'message' => $questUse['messages'][0] ?? null,
                'quest_messages' => $questUse['messages'],
            ]);
        }

        $instantEffects = $backpack->item->itemInfo->effects->filter(
            fn ($e) => $e->effect_type->isInstant()
        );
        $itemBuffs = $backpack->item->itemInfo->buffs;
        $itemDebuffs = $backpack->item->itemInfo->debuffs;
        $hasRegularEffect = $instantEffects->isNotEmpty() || $itemBuffs->isNotEmpty() || $itemDebuffs->isNotEmpty();
        $questUsePlan = $this->questProgressService->prepareItemUse(
            $player,
            (int) $backpack->item->share_item_id,
        );

        if (! $hasRegularEffect && $questUsePlan === []) {
            return response()->json([
                'status' => 'error',
                'message' => 'Этот предмет нельзя использовать здесь.',
            ], 422);
        }

        $restoresLostExp = $instantEffects->contains(
            fn ($e) => $e->effect_type === ItemEffectType::RESTORE_LOST_EXP
        );
        $revivalEffectId = null;
        if ($restoresLostExp) {
            $revivalActive = $this->revivalService->findActiveRedeemable($player);
            if ($revivalActive === null) {
                // Проверяем здесь, а не только внутри стратегии: предмет
                // многоразовый, и заряд не должен списываться впустую.
                return response()->json([
                    'status' => 'error',
                    'message' => 'Эффект «Возрождение» неактивен — потерянного при смерти опыта для возврата нет.',
                ], 422);
            }

            // Берём id до апдейта: стратегия сама удаляет строку эффекта,
            // а фронту нужно знать, какую иконку снять из effectsContainer.
            $revivalEffectId = $revivalActive->frontendId();
        }

        $target = null;
        if ($itemDebuffs->isNotEmpty()) {
            $target = Player::query()
                ->whereKey($request->integer('target_player_id'))
                ->whereKeyNot($player->id)
                ->whereHas('user', fn ($query) => $query
                    ->where('location_id', $user->location_id)
                    ->where('last_online_at', '>=', now()->subMinutes(10)))
                ->first();

            if ($target === null) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Выберите другого игрока в вашей локации.',
                ], 422);
            }
        }

        $this->itemUsagePolicyService->reserveUse($player, $backpack->item->itemInfo);

        $questUse = $this->questProgressService->progressPreparedItemUse($questUsePlan);

        $stats = $this->statService->resolve($player);
        $expBefore = (int) $player->exp;

        foreach ($instantEffects as $effectModel) {
            $effect = $effectModel->toValueObject();
            $strategy = ItemEffectStrategyFactory::make($effect->type);
            $strategy->apply($player, $effect, $stats->getHpMax(), $stats->getMpMax());
        }

        $effectResult = new AttackResultDTO;
        foreach ($itemBuffs as $itemBuff) {
            $this->battleEffectService->applyEffectToPlayer(
                $itemBuff->effect,
                $player,
                null,
                $effectResult,
                $itemBuff->duration_seconds,
            );
        }

        foreach ($itemDebuffs as $itemDebuff) {
            $this->battleEffectService->applyEffectToPlayer(
                $itemDebuff->effect,
                $target,
                null,
                $effectResult,
                $itemDebuff->duration_seconds,
            );
        }

        if ($hasRegularEffect || $questUse['consume']) {
            ['removed' => $removed, 'count' => $newCount] = $this->consumeBackpackItem($backpack);
        } else {
            $removed = false;
            $newCount = (int) $backpack->count;
        }

        $player->refresh();
        $stats = $this->statService->resolve($player);
        $expRestored = $restoresLostExp ? max(0, (int) $player->exp - $expBefore) : 0;

        return response()->json([
            'status' => 'success',
            'removed' => $removed,
            'count' => $newCount,
            'hp_now' => $player->hp_now,
            'hp_max' => $stats->getHpMax(),
            'mp_now' => $player->mp_now,
            'mp_max' => $stats->getMpMax(),
            'lvl' => $player->lvl,
            'experience' => $player->getPercentExp(),
            'exp_restored' => $expRestored,
            'message' => $expRestored > 0
                ? sprintf('Возвращено потерянного опыта: %s.', number_format($expRestored, 0, '.', ' '))
                : ($questUse['messages'][0] ?? null),
            'quest_messages' => $questUse['messages'],
            'blessings' => array_map(
                static fn ($effect): array => $effect->toArray(),
                $effectResult->getPlayerEffects(),
            ),
            // Иконки effectsContainer на странице героя, которые нужно снять
            // сразу, не дожидаясь ближайшего heartbeat — см. RestoreLostExpStrategy.
            'removed_effects' => $revivalEffectId !== null && $expRestored > 0
                ? [$revivalEffectId]
                : [],
        ]);
    }

    /** @return array{removed: bool, count: int} */
    private function consumeBackpackItem(Backpack $backpack): array
    {
        $item = $backpack->item;
        $configuredUses = max(0, (int) $item->itemInfo->count_use);

        if ($configuredUses > 0) {
            $currentUses = (int) $item->count_use > 0
                ? (int) $item->count_use
                : $configuredUses;
            $item->count_use = $currentUses - 1;

            if ($item->count_use > 0) {
                $item->save();

                return ['removed' => false, 'count' => (int) $item->count_use];
            }

            if ($backpack->count > 1) {
                $backpack->decrement('count');
                $item->count_use = $configuredUses;
                $item->save();

                return ['removed' => false, 'count' => $configuredUses];
            }

            $backpack->delete();

            return ['removed' => true, 'count' => 0];
        }

        if ($backpack->count <= 1) {
            $backpack->delete();

            return ['removed' => true, 'count' => 0];
        }

        $backpack->decrement('count');

        return ['removed' => false, 'count' => (int) $backpack->count];
    }
}
