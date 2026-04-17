<?php

declare(strict_types=1);

namespace App\Modules\Structure\Blacksmith\Presentation\Http;

use App\Http\Controllers\Controller;
use App\Modules\Structure\Blacksmith\Application\DTOs\GemActionDTO;
use App\Modules\Structure\Blacksmith\Application\UseCases\GetGemsPage;
use App\Modules\Structure\Blacksmith\Application\UseCases\InsertGem;
use App\Modules\Structure\Blacksmith\Application\UseCases\OpenSocket;
use App\Modules\Structure\Blacksmith\Application\UseCases\RemoveGem;
use App\Modules\User\Infrastructure\Persistence\Models\User;
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
    ) {}

    public function index(Request $request, int $id): \Illuminate\Contracts\View\View
    {
        /** @var User $user */
        $user = Auth::user();
        $page = $this->getGemsPage->execute($user, $id);

        return view('blacksmith::gems', [
            'blacksmith' => $page->blacksmith,
            'user' => $user,
            'items' => $page->items,
            'gems' => $page->gems,
            'socketKits' => $page->socketKits,
            'itemTooltipScript' => $page->itemTooltipScript,
        ]);
    }

    public function insertGem(Request $request, int $id): RedirectResponse
    {
        $user = Auth::user();

        $request->validate([
            'item_id' => 'required|integer',
            'gem_id' => 'required|integer',
            'socket_index' => 'required|integer|min:0|max:2',
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
            'socket_index' => 'required|integer|min:0|max:2',
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
            'kit_id' => 'required|integer',
        ]);

        $result = $this->openSocket->execute(new GemActionDTO(
            user: $user,
            itemId: $request->integer('item_id'),
            kitId: $request->integer('kit_id'),
        ));

        session()->flash('message', $result->message);
        session()->flash('gem_success', $result->success);

        return redirect()->route('blacksmith.gems', ['id' => $id]);
    }
}
