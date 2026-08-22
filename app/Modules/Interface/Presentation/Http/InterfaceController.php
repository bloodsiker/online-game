<?php

declare(strict_types=1);

namespace App\Modules\Interface\Presentation\Http;

use App\Http\Controllers\Controller;
use App\Modules\Friend\Domain\Contracts\FriendRelationshipRepository;
use App\Modules\Interface\Application\UseCases\GetHeroPage;
use App\Modules\Interface\Application\UseCases\GetOnMapPage;
use App\Modules\Interface\Application\UseCases\GetWhoPage;
use App\Modules\Interface\Application\UseCases\HeartbeatPlayer;
use App\Modules\User\Infrastructure\Persistence\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class InterfaceController extends Controller
{
    public function __construct(
        private readonly GetOnMapPage $getOnMapPage,
        private readonly GetWhoPage $getWhoPage,
        private readonly GetHeroPage $getHeroPage,
        private readonly HeartbeatPlayer $heartbeatPlayer,
    ) {}

    public function index()
    {
        return view('interface::index');
    }

    public function main()
    {
        return view('interface::index');
    }

    public function game()
    {
        return view('interface::index');
    }

    public function interface()
    {
        return view('interface::interface');
    }

    public function onMap(Request $request)
    {
        /** @var ?User $user */
        $user = Auth::user();

        return view($this->getOnMapPage->execute($request->string('s')->toString(), $user)->view);
    }

    public function menu()
    {
        return view('interface::menu');
    }

    public function who(FriendRelationshipRepository $friendRepository)
    {
        /** @var User $user */
        $user = Auth::user();

        return view('interface::who', [
            'page' => $this->getWhoPage->execute($user),
            'ignoredUserIds' => $friendRepository->getIgnoredUserIdsByPlayerId((int) $user->player_id),
        ]);
    }

    public function hero()
    {
        /** @var User $user */
        $user = Auth::user();

        return view('interface::hero', [
            'page' => $this->getHeroPage->execute($user),
        ]);
    }

    public function heartbeat(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();
        $heartbeat = $this->heartbeatPlayer->execute($user);
        $payload = $heartbeat->toArray();

        if ($heartbeat->dead) {
            $payload['death_url'] = route('location');
        }

        return response()->json($payload);
    }
}
