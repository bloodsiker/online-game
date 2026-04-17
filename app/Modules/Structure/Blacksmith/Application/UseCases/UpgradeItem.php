<?php

declare(strict_types=1);

namespace App\Modules\Structure\Blacksmith\Application\UseCases;

use App\Enums\ShareItemType;
use App\Modules\Backpack\Domain\Models\Backpack;
use App\Modules\Structure\Blacksmith\Application\DTOs\BlacksmithActionResultDTO;
use App\Modules\Structure\Blacksmith\Application\DTOs\UpgradeItemDTO;
use App\Modules\Structure\Blacksmith\Domain\Enums\UpgradeScrollType;
use App\Modules\Structure\Blacksmith\Domain\Services\UpgradeService;
use Illuminate\Support\Facades\DB;

class UpgradeItem
{
    public function __construct(
        private readonly UpgradeService $upgradeService,
    ) {}

    public function execute(UpgradeItemDTO $data): BlacksmithActionResultDTO
    {
        $itemSlot = Backpack::select('backpacks.*')
            ->join('items', 'backpacks.item_id', '=', 'items.id')
            ->join('share_items', 'items.share_item_id', '=', 'share_items.id')
            ->where('backpacks.user_id', $data->user->id)
            ->where('backpacks.item_id', $data->itemId)
            ->whereIn('share_items.type', [
                ShareItemType::WEAPON->value,
                ShareItemType::SHIELD->value,
                ShareItemType::ARMOR->value,
                ShareItemType::BELT->value,
            ])
            ->first();

        if (! $itemSlot instanceof Backpack) {
            return new BlacksmithActionResultDTO(false, 'Предмет не найден.');
        }

        $baseScrollSlot = Backpack::select('backpacks.*')
            ->join('items', 'backpacks.item_id', '=', 'items.id')
            ->join('share_items', 'items.share_item_id', '=', 'share_items.id')
            ->where('backpacks.user_id', $data->user->id)
            ->where('backpacks.item_id', $data->baseScrollId)
            ->where('share_items.upgrade_scroll_type', UpgradeScrollType::BASE->value)
            ->first();

        if (! $baseScrollSlot instanceof Backpack) {
            return new BlacksmithActionResultDTO(false, 'Свиток заточки не найден. Для заточки необходим свиток заточки.');
        }

        $bonusScrollSlot = null;
        if ($data->bonusScrollId !== null) {
            $bonusScrollSlot = Backpack::select('backpacks.*')
                ->join('items', 'backpacks.item_id', '=', 'items.id')
                ->join('share_items', 'items.share_item_id', '=', 'share_items.id')
                ->where('backpacks.user_id', $data->user->id)
                ->where('backpacks.item_id', $data->bonusScrollId)
                ->where('share_items.type', ShareItemType::SCROLL->value)
                ->whereIn('share_items.upgrade_scroll_type', [
                    UpgradeScrollType::PROTECTION->value,
                    UpgradeScrollType::STABILIZER->value,
                    UpgradeScrollType::LUCKY->value,
                ])
                ->first();
        }

        $result = DB::transaction(fn () => $this->upgradeService->upgrade(
            $data->user,
            $itemSlot,
            $baseScrollSlot,
            $bonusScrollSlot,
        ));

        return new BlacksmithActionResultDTO(
            ok: $result['success'],
            message: $result['message'],
            success: $result['success'],
            destroyed: $result['destroyed'] ?? false,
        );
    }
}
