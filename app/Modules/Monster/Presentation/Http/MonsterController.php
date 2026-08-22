<?php

declare(strict_types=1);

namespace App\Modules\Monster\Presentation\Http;

use App\Http\Controllers\Controller;
use App\Modules\Monster\Application\UseCases\GetMonsterInfoPage;
use App\Services\ItemTooltip\ItemTooltipCollector;
use App\Services\ItemTooltip\Strategy\ShareItemTooltipStrategy;

class MonsterController extends Controller
{
    public function __construct(
        private readonly GetMonsterInfoPage $getMonsterInfoPage,
        private readonly ItemTooltipCollector $tooltipCollector,
    ) {}

    public function info(int $id)
    {
        $page = $this->getMonsterInfoPage->execute($id);

        $this->tooltipCollector->collectFrom(new ShareItemTooltipStrategy($page->monster->items));

        return view('monster::info', [
            'page' => $page,
            'itemTooltipScript' => $this->tooltipCollector->renderScript(),
        ]);
    }
}
