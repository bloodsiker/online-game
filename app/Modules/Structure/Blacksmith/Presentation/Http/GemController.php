<?php

declare(strict_types=1);

namespace App\Modules\Structure\Blacksmith\Presentation\Http;

use App\Http\Controllers\Controller;
use App\Modules\Player\Domain\Services\PlayerStatService;
use App\Modules\Structure\Blacksmith\Application\DTOs\GemActionDTO;
use App\Modules\Structure\Blacksmith\Application\UseCases\GetGemsPage;
use App\Modules\Structure\Blacksmith\Application\UseCases\InsertGem;
use App\Modules\Structure\Blacksmith\Application\UseCases\OpenSocket;
use App\Modules\Structure\Blacksmith\Application\UseCases\RemoveGem;
use App\Modules\User\Infrastructure\Persistence\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class GemController extends Controller
{
    public function __construct(
        private readonly GetGemsPage $getGemsPage,
        private readonly InsertGem $insertGem,
        private readonly RemoveGem $removeGem,
        private readonly OpenSocket $openSocket,
        private readonly PlayerStatService $statService,
    ) {}

    public function index(Request $request, int $id): View
    {
        /** @var User $user */
        $user = Auth::user();
        $page = $this->getGemsPage->execute($user, $id);

        $player = $user->player;
        $sheet = $this->statService->resolve($player);
        $hpMp = [
            'hp' => ['current' => $player->hp_now, 'max' => $sheet->getHpMax()],
            'mp' => ['current' => $player->mp_now, 'max' => $sheet->getMpMax()],
        ];

        return view('blacksmith::gems', [
            'blacksmith' => $page->blacksmith,
            'user' => $user,
            'items' => $page->items,
            'gems' => $page->gems,
            'mounts' => $page->mounts,
            'itemTooltipScript' => $page->itemTooltipScript,
            'hpMp' => $hpMp,
        ]);
    }

    public function insertGem(Request $request, int $id): RedirectResponse
    {
        $user = Auth::user();

        $request->validate([
            'item_id' => 'required|integer',
            'gem_id' => 'required|integer',
            'socket_index' => 'required|integer|min:0|max:3',
        ]);

        $result = $this->insertGem->execute(new GemActionDTO(
            user: $user,
            itemId: $request->integer('item_id'),
            gemId: $request->integer('gem_id'),
            socketIndex: $request->integer('socket_index'),
        ));

        session()->flash('message', $result->message);
        session()->flash('gem_success', $result->success);

        return redirect()->route('blacksmith.gems', ['id' => $id]);
    }

    public function removeGem(Request $request, int $id): RedirectResponse
    {
        $user = Auth::user();

        $request->validate([
            'item_id' => 'required|integer',
            'socket_index' => 'required|integer|min:0|max:3',
        ]);

        $result = $this->removeGem->execute(new GemActionDTO(
            user: $user,
            itemId: $request->integer('item_id'),
            socketIndex: $request->integer('socket_index'),
        ));

        session()->flash('message', $result->message);
        session()->flash('gem_success', $result->success);

        return redirect()->route('blacksmith.gems', ['id' => $id]);
    }

    public function openSocket(Request $request, int $id): RedirectResponse
    {
        $user = Auth::user();

        $request->validate([
            'item_id' => 'required|integer',
            'mount_id' => 'required|integer',
        ]);

        $result = $this->openSocket->execute(new GemActionDTO(
            user: $user,
            itemId: $request->integer('item_id'),
            mountId: $request->integer('mount_id'),
        ));

        session()->flash('message', $result->message);
        session()->flash('gem_success', $result->success);

        return redirect()->route('blacksmith.gems', ['id' => $id]);
    }
}
