<?php

declare(strict_types=1);

namespace App\Modules\Structure\ReputationExchange\Domain\Services;

use App\Modules\Backpack\Domain\Models\Backpack;
use App\Modules\Backpack\Domain\Services\BackpackService;
use App\Modules\Chat\Application\Services\ChatService;
use App\Modules\Item\Infrastructure\Persistence\Models\Item;
use App\Modules\Reputation\Application\Services\ReputationService;
use App\Modules\Reputation\Domain\Services\DivineFavorService;
use App\Modules\Reputation\Infrastructure\Persistence\Models\Reputation;
use App\Modules\Structure\Infrastructure\Persistence\Models\Structure;
use App\Modules\Structure\ReputationExchange\Application\DTOs\GambleExchangeOptionDTO;
use App\Modules\Structure\ReputationExchange\Application\DTOs\GambleExchangePageDTO;
use App\Modules\Structure\ReputationExchange\Application\DTOs\GambleExchangeResourceDTO;
use App\Modules\Structure\ReputationExchange\Application\DTOs\ReputationExchangeResultDTO;
use App\Modules\Structure\ReputationExchange\Domain\Contracts\ReputationExchangeStrategy;
use App\Modules\Structure\ReputationExchange\Infrastructure\Persistence\Models\ReputationGambleOption;
use App\Modules\User\Infrastructure\Persistence\Models\User;
use Illuminate\Support\Facades\DB;

/**
 * Обмен ресурса на репутацию с выбором риска: игрок выбирает ресурс, затем
 * один из вариантов «стоимость → шанс успеха» (например 100шт/100%, 50шт/50%,
 * 20шт/20% — на одинаковую награду). Ресурс расходуется всегда, вне
 * зависимости от исхода броска.
 */
readonly class GambleExchangeService implements ReputationExchangeStrategy
{
    public function __construct(
        private ReputationService $reputationService,
        private BackpackService $backpackService,
        private ChatService $chatService,
        private DivineFavorService $divineFavorService,
    ) {}

    public function getPageData(User $user, Structure $structure): array
    {
        $reputation = Reputation::where('npc_id', $structure->npc_id)->firstOrFail();
        $currentPoints = $this->reputationService->getOrCreate($user->player, $reputation)->points;
        $offeringCooldown = $this->reputationService->getOfferingCooldownDiff($user->player, $reputation);

        $options = ReputationGambleOption::with('shareItem')
            ->where('reputation_id', $reputation->id)
            ->orderBy('sort_order')
            ->get()
            ->groupBy('share_item_id');

        $availableCounts = Backpack::select('backpacks.*')
            ->join('items', 'backpacks.item_id', '=', 'items.id')
            ->where('backpacks.user_id', $user->id)
            ->get()
            ->mapWithKeys(static fn ($item) => [(int) $item->item->share_item_id => (int) $item->count])
            ->all();

        $resources = $options->map(function ($group) use ($availableCounts): GambleExchangeResourceDTO {
            $shareItem = $group->first()->shareItem;
            $available = $availableCounts[$shareItem->id] ?? 0;

            $optionDtos = $group->map(static fn (ReputationGambleOption $option): GambleExchangeOptionDTO => new GambleExchangeOptionDTO(
                id: $option->id,
                resourceCost: $option->resource_cost,
                successChance: $option->success_chance,
                rewardPoints: $option->reward_points,
                canAfford: $available >= $option->resource_cost,
            ))->values()->all();

            return new GambleExchangeResourceDTO(
                shareItemId: (int) $shareItem->id,
                name: (string) $shareItem->name,
                image: (string) $shareItem->image,
                rarityColor: (string) $shareItem->rarity?->color(),
                availableCount: $available,
                options: $optionDtos,
            );
        })->values()->all();

        return ['page' => new GambleExchangePageDTO(
            structureId: $structure->id,
            reputationName: (string) $reputation->name,
            currentPoints: $currentPoints,
            resources: $resources,
            offeringCooldown: $offeringCooldown,
        )];
    }

    public function perform(User $user, Structure $structure, array $input): ReputationExchangeResultDTO
    {
        $optionId = (int) ($input['option_id'] ?? 0);

        $option = ReputationGambleOption::with('reputation', 'shareItem')->find($optionId);
        if ($option === null) {
            return new ReputationExchangeResultDTO(false, 'Такой вариант обмена не найден.');
        }

        $cooldown = $this->reputationService->getOfferingCooldownDiff($user->player, $option->reputation);
        if ($cooldown !== null) {
            return new ReputationExchangeResultDTO(false, sprintf('Подношение можно сделать раз в 2 дня. Осталось: %s.', $cooldown));
        }

        $backpackItem = Backpack::select('backpacks.*')
            ->join('items', 'backpacks.item_id', '=', 'items.id')
            ->where('backpacks.user_id', $user->id)
            ->where('backpacks.equipped', 0)
            ->where('items.share_item_id', $option->share_item_id)
            ->first();

        if (! $backpackItem || $backpackItem->count < $option->resource_cost) {
            return new ReputationExchangeResultDTO(false, sprintf('Недостаточно ресурса «%s».', $option->shareItem->name));
        }

        $player = $user->player;
        $succeeded = random_int(1, 100) <= $option->success_chance;

        // Милость уже постоянна для этого игрока (медаль тира или подвиг верхнего
        // тира) — эликсир ей больше не нужен, не засоряем рюкзак бесполезным предметом.
        $elixir = ($option->reputation->elixir_share_item_id && ! $this->divineFavorService->isPermanent($player, $option->reputation))
            ? $option->reputation->elixirItem
            : null;

        DB::transaction(function () use ($user, $backpackItem, $option, $player, $succeeded, $elixir): void {
            if ($backpackItem->count <= $option->resource_cost) {
                Item::whereKey($backpackItem->item_id)->delete();
                $backpackItem->delete();
            } else {
                $backpackItem->count -= $option->resource_cost;
                $backpackItem->save();
            }

            if ($succeeded) {
                $this->reputationService->addPoints($player, $option->reputation, $option->reward_points, touchCooldown: false);
            }

            if ($elixir !== null) {
                $this->backpackService->addItemByShareItem($user, $elixir, 1);
            }

            $this->reputationService->touchOfferingCooldown($player, $option->reputation);
        });

        $elixirNote = $elixir !== null ? sprintf(' Получен «%s» ×1.', $elixir->name) : '';
        $elixirChatNote = $elixir !== null ? sprintf(' Получен [[share_item_%d]] ×1.', $elixir->id) : '';

        if ($succeeded) {
            $this->chatService->sendQuestToUser($user, sprintf(
                'Вы поднесли %d×«%s» и получили <b>+%d</b> репутации <b>«%s»</b>.%s',
                $option->resource_cost,
                $option->shareItem->name,
                $option->reward_points,
                $option->reputation->name,
                $elixirChatNote,
            ));

            return new ReputationExchangeResultDTO(true, sprintf(
                'Успех! Потрачено %d×«%s», получено +%d репутации.%s',
                $option->resource_cost,
                $option->shareItem->name,
                $option->reward_points,
                $elixirNote,
            ));
        }

        return new ReputationExchangeResultDTO(false, sprintf(
            'Не повезло — %d×«%s» потрачено впустую, репутация не изменилась.%s',
            $option->resource_cost,
            $option->shareItem->name,
            $elixirNote,
        ));
    }

    public function viewName(): string
    {
        return 'reputation_exchange::gamble';
    }
}
