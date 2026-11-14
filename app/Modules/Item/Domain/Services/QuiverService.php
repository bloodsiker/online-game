<?php

declare(strict_types=1);

namespace App\Modules\Item\Domain\Services;

use App\Modules\Backpack\Domain\Models\Backpack;
use App\Modules\Backpack\Domain\Services\BackpackService;
use App\Modules\Item\Domain\Enums\ArrowConsumeStatus;
use App\Modules\Item\Infrastructure\Persistence\Models\Item;
use App\Modules\Player\Infrastructure\Persistence\Models\Player;
use App\Modules\Player\Infrastructure\Persistence\Models\PlayerEquipment;
use App\Modules\Share\Domain\Enums\ShareItemType;
use App\Modules\Share\Infrastructure\Persistence\Models\ShareItem;
use App\Modules\User\Infrastructure\Persistence\Models\User;
use Illuminate\Support\Facades\DB;

/**
 * Колчан для стрельбы из лука: в надетом колчане лежат стрелы одного типа
 * (items.quiver_arrow_*), каждый выстрел расходует одну стрелу.
 */
class QuiverService
{
    public function __construct(
        private readonly BackpackService $backpackService,
        private readonly ItemRequirementService $requirementService,
    ) {}

    /**
     * Перекладывает стрелы из рюкзака в надетый колчан (сколько поместится).
     * Стрелы другого типа, лежавшие в колчане, возвращаются в рюкзак.
     *
     * @return array{ok: bool, message: string, quiverItemId?: int}
     */
    public function fill(User $user, int $arrowItemId): array
    {
        return DB::transaction(function () use ($user, $arrowItemId): array {
            $equipment = PlayerEquipment::query()->where('player_id', $user->player->id)->lockForUpdate()->first();
            $quiver = $equipment?->quiver !== null
                ? Item::query()->whereKey($equipment->quiver)->lockForUpdate()->first()
                : null;

            if ($quiver === null || $quiver->itemInfo->type !== ShareItemType::QUIVER) {
                return $this->fail(__('Сначала наденьте колчан.'));
            }

            $arrowStack = Backpack::query()
                ->where('user_id', $user->id)
                ->where('item_id', $arrowItemId)
                ->where('equipped', 0)
                ->lockForUpdate()
                ->first();
            $arrowInfo = $arrowStack?->item?->itemInfo;

            if ($arrowStack === null || $arrowInfo === null || $arrowInfo->type !== ShareItemType::ARROW) {
                return $this->fail(__('Стрелы не найдены в рюкзаке.'));
            }

            // Требования стрел (уровень) проверяются при закладке в колчан.
            $requirementError = $this->requirementService->check($user->player, $arrowInfo);
            if ($requirementError !== null) {
                return $this->fail($requirementError);
            }

            $returned = 0;
            if ($quiver->quiver_arrow_share_item_id !== null
                && $quiver->quiver_arrow_share_item_id !== $arrowInfo->id
                && $quiver->quiver_arrow_count > 0) {
                $returned = $quiver->quiver_arrow_count;
                $this->backpackService->addItemByShareItem($user, $quiver->quiverArrow, $returned);
                $quiver->quiver_arrow_count = 0;
            }

            if ($quiver->quiver_arrow_count === 0) {
                $quiver->quiver_arrow_share_item_id = $arrowInfo->id;
            }

            $capacity = (int) ($quiver->itemInfo->quiver_capacity ?? 0);
            $amount = min($capacity - $quiver->quiver_arrow_count, (int) $arrowStack->count);

            if ($amount <= 0) {
                return $this->fail(__('Колчан уже полон.'));
            }

            if (! $this->backpackService->removeItem($user, $arrowItemId, $amount)) {
                return $this->fail(__('Не удалось взять стрелы из рюкзака.'));
            }

            $quiver->quiver_arrow_count += $amount;
            $quiver->save();

            $message = sprintf(
                __('В колчан добавлено %d шт. «%s» (%d/%d).'),
                $amount,
                e($arrowInfo->name),
                $quiver->quiver_arrow_count,
                $capacity,
            );
            if ($returned > 0) {
                $message .= ' '.sprintf(__('Прежние стрелы (%d шт.) возвращены в рюкзак.'), $returned);
            }

            return ['ok' => true, 'message' => $message, 'quiverItemId' => (int) $quiver->id];
        });
    }

    /**
     * Расходует одну стрелу из надетого колчана под выстрел.
     *
     * @return array{status: ArrowConsumeStatus, arrow: ?ShareItem}
     */
    public function consumeArrow(Player $player): array
    {
        $quiverId = $player->playerEquip?->quiver;
        if ($quiverId === null) {
            return ['status' => ArrowConsumeStatus::NO_QUIVER, 'arrow' => null];
        }

        $decremented = Item::query()
            ->whereKey($quiverId)
            ->where('quiver_arrow_count', '>', 0)
            ->decrement('quiver_arrow_count');

        if ($decremented !== 1) {
            return ['status' => ArrowConsumeStatus::EMPTY, 'arrow' => null];
        }

        $arrow = ShareItem::query()
            ->with('stats')
            ->whereKey(Item::query()->whereKey($quiverId)->value('quiver_arrow_share_item_id'))
            ->first();

        return ['status' => ArrowConsumeStatus::CONSUMED, 'arrow' => $arrow];
    }

    /** @return array{ok: bool, message: string} */
    private function fail(string $message): array
    {
        return ['ok' => false, 'message' => $message];
    }
}
