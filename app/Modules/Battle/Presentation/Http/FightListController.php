<?php

declare(strict_types=1);

namespace App\Modules\Battle\Presentation\Http;

use App\Modules\Battle\Application\DTOs\FightListFilterDTO;
use App\Modules\Battle\Application\UseCases\GetFightList;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

final class FightListController
{
    public function __construct(
        private readonly GetFightList $getFightList,
    ) {}

    public function index(Request $request): View
    {
        $mode = in_array($request->query('mode'), ['finished', 'all'], strict: true) ? $request->query('mode') : 'running';
        $filter = FightListFilterDTO::fromRequest($request);
        $page = max(1, (int) $request->query('page', 1));

        // «Текущие»/«Завершенные» бои показывают только эту локацию; «Все бои» — сервер целиком
        $locationId = $mode === 'all' ? null : $request->user()->location_id;

        $result = $this->getFightList->execute($mode, $filter, $page, $locationId);

        return view('battle::fights', ['page' => $result]);
    }
}
