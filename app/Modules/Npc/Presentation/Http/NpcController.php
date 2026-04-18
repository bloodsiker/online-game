<?php

declare(strict_types=1);

namespace App\Modules\Npc\Presentation\Http;

use App\Http\Controllers\Controller;
use App\Modules\Npc\Application\UseCases\GetNpcInfoPage;
use App\Modules\Npc\Application\UseCases\GetNpcPage;
use App\Modules\User\Infrastructure\Persistence\Models\User;
use Illuminate\Support\Facades\Auth;

class NpcController extends Controller
{
    public function __construct(
        private readonly GetNpcPage $getNpcPage,
        private readonly GetNpcInfoPage $getNpcInfoPage,
    ) {}

    public function index(int $id)
    {
        /** @var User $user */
        $user = Auth::user();
        $page = $this->getNpcPage->execute($user, $id);

        return view('npc::index', [
            'npc' => $page->npc,
            'quests' => $page->quests,
            'questsInProgress' => $page->questsInProgress,
            'questsOnCooldown' => $page->questsOnCooldown,
            'message' => $page->message,
            'messageType' => $page->messageType,
            'player' => $page->player,
        ]);
    }

    public function info(int $id)
    {
        return view('npc::info', [
            'page' => $this->getNpcInfoPage->execute($id),
        ]);
    }
}
