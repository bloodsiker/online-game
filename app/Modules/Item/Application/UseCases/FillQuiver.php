<?php

declare(strict_types=1);

namespace App\Modules\Item\Application\UseCases;

use App\Modules\Item\Application\DTOs\FillQuiverResultDTO;
use App\Modules\Item\Application\ItemTooltip\ItemTooltipCollector;
use App\Modules\Item\Application\ItemTooltip\ItemTooltipRenderer;
use App\Modules\Item\Application\ItemTooltip\Strategy\ItemModelTooltipStrategy;
use App\Modules\Item\Domain\Services\QuiverService;
use App\Modules\Item\Infrastructure\Persistence\Models\Item;
use App\Modules\User\Infrastructure\Persistence\Models\User;

/**
 * Пополнение надетого колчана стрелами из рюкзака (пункт меню «Пополнить колчан»).
 * Возвращает свежий тултип колчана, чтобы обновить его во фрейме экипировки без перезагрузки.
 */
final class FillQuiver
{
    public function __construct(
        private readonly QuiverService $quiverService,
        private readonly ItemTooltipRenderer $tooltipRenderer,
    ) {}

    public function execute(User $user, int $arrowItemId): FillQuiverResultDTO
    {
        $result = $this->quiverService->fill($user, $arrowItemId);
        $quiverItemId = $result['quiverItemId'] ?? null;

        if (! $result['ok'] || $quiverItemId === null) {
            return new FillQuiverResultDTO(ok: $result['ok'], message: $result['message']);
        }

        $quiver = Item::query()->find($quiverItemId);
        $tooltips = $quiver === null
            ? []
            : (new ItemTooltipCollector($this->tooltipRenderer))->collectFrom(new ItemModelTooltipStrategy([$quiver]))->all();

        return new FillQuiverResultDTO(
            ok: true,
            message: $result['message'],
            quiverItemId: $quiverItemId,
            quiverTooltip: $tooltips[$quiverItemId] ?? null,
        );
    }
}
