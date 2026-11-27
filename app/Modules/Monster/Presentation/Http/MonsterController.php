<?php

declare(strict_types=1);

namespace App\Modules\Monster\Presentation\Http;

use App\Http\Controllers\Controller;
use App\Modules\Item\Application\ItemTooltip\ItemTooltipCollector;
use App\Modules\Item\Application\ItemTooltip\Strategy\ShareItemTooltipStrategy;
use App\Modules\Monster\Application\DTOs\MonsterInfoPageDTO;
use App\Modules\Monster\Application\UseCases\GetMonsterInfoPage;

class MonsterController extends Controller
{
    public function __construct(
        private readonly GetMonsterInfoPage $getMonsterInfoPage,
        private readonly ItemTooltipCollector $tooltipCollector,
    ) {}

    public function info(string $id)
    {
        $page = $this->getMonsterInfoPage->execute($this->parseId($id));

        return $this->renderInfo($page);
    }

    public function catalogInfo(string $id)
    {
        $page = $this->getMonsterInfoPage->executeByMonsterId($this->parseId($id));

        return $this->renderInfo($page);
    }

    private function parseId(string $id): int
    {
        $value = filter_var(ltrim($id, '0'), FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        abort_if($value === false, 404);

        return $value;
    }

    private function renderInfo(MonsterInfoPageDTO $page)
    {
        $this->tooltipCollector->collectFrom(new ShareItemTooltipStrategy($page->monster->items));

        return view('monster::info', [
            'page' => $page,
            'itemTooltipScript' => $this->tooltipCollector->renderScript(),
        ]);
    }
}
