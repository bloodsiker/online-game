<?php

declare(strict_types=1);

namespace App\Modules\Backpack\Application\UseCases;

use App\Models\User;
use App\Modules\Backpack\Domain\Services\BackpackService;
use App\Modules\Backpack\Domain\Services\ItemTooltip\BackpackItemTooltipStrategy;
use App\Services\ItemTooltip\ItemTooltipCollector;

class GetBackpack
{
    public function __construct(
        private readonly BackpackService      $backpackService,
        private readonly ItemTooltipCollector $collector,
    ) {}

    public function execute(User $user, array $filters = []): array
    {
        $data        = $this->backpackService->getBackpackData($user, $filters);
        $playerEquip = $user->player->playerEquip;

        $this->collector->collectFrom(new BackpackItemTooltipStrategy($data->getBackpack()));
        $itemTooltipScript = $this->collector->renderScript();

        return compact('data', 'user', 'playerEquip', 'itemTooltipScript');
    }
}