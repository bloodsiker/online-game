<?php

declare(strict_types=1);

namespace App\Modules\Monster\Presentation\Http;

use App\Http\Controllers\Controller;
use App\Modules\Monster\Application\UseCases\GetMonsterInfoPage;

class MonsterController extends Controller
{
    public function __construct(
        private readonly GetMonsterInfoPage $getMonsterInfoPage,
    ) {}

    public function info(int $id)
    {
        return view('monster::info', [
            'page' => $this->getMonsterInfoPage->execute($id),
        ]);
    }
}
