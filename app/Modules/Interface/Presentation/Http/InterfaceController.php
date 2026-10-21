<?php

declare(strict_types=1);

namespace App\Modules\Interface\Presentation\Http;

use App\Http\Controllers\Controller;
use App\Modules\Friend\Domain\Contracts\FriendRelationshipRepository;
use App\Modules\Interface\Application\UseCases\GetHeroPage;
use App\Modules\Interface\Application\UseCases\GetOnMapPage;
use App\Modules\Interface\Application\UseCases\GetWhoPage;
use App\Modules\Interface\Application\UseCases\HeartbeatPlayer;
use App\Modules\Post\Application\UseCases\GetMailbox;
use App\Modules\Race\Infrastructure\Persistence\Models\Race;
use App\Modules\Race\Infrastructure\Persistence\Models\RaceAvatar;
use App\Modules\Structure\Infrastructure\Persistence\Models\Structure;
use App\Modules\Structure\Prison\Application\Services\DetentionService;
use App\Modules\User\Application\Services\GameSettingsService;
use App\Modules\User\Infrastructure\Persistence\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class InterfaceController extends Controller
{
    public function __construct(
        private readonly GetOnMapPage $getOnMapPage,
        private readonly GetWhoPage $getWhoPage,
        private readonly GetHeroPage $getHeroPage,
        private readonly HeartbeatPlayer $heartbeatPlayer,
        private readonly GetMailbox $mailbox,
        private readonly GameSettingsService $gameSettings,
    ) {}

    public function index()
    {
        return $this->gameView();
    }

    public function main()
    {
        return $this->gameView();
    }

    public function game()
    {
        return $this->gameView();
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
        $detentionService = app(DetentionService::class);
        $released = $detentionService->releaseIfExpired($user);
        $heartbeat = $this->heartbeatPlayer->execute($user);
        $payload = $heartbeat->toArray();

        $detention = $detentionService->activeFor($user);
        if ($detention !== null) {
            $payload['detention_url'] = route('prison.index', ['id' => $detention->prison_structure_id]);
        } elseif ($released) {
            $payload['prison_released_url'] = route('location');
        }

        if ($heartbeat->dead) {
            $payload['death_url'] = route('location');
        }

        return response()->json($payload);
    }

    public function csrfToken(Request $request): JsonResponse
    {
        return response()->json([
            'token' => $request->session()->token(),
        ]);
    }

    private function gameView(): View|RedirectResponse
    {
        /** @var ?User $user */
        $user = Auth::user();

        if (! $user instanceof User) {
            return redirect()->route('index');
        }

        $adminToolUsers = $user->is_admin
            ? User::query()
                ->without('player')
                ->select(['id', 'name'])
                ->whereNotNull('player_id')
                ->orderBy('name')
                ->get()
            : collect();
        $adminToolPrisons = $user->is_admin
            ? Structure::query()
                ->with('location')
                ->where('type', Structure::TYPE_PRISON)
                ->whereNotNull('location_id')
                ->orderBy('id')
                ->get()
            : collect();

        $races = Race::query()->select(['id', 'name'])->orderBy('name')->get();

        $myAvatars = Schema::hasTable('race_avatars') && $user->player !== null
            ? RaceAvatar::query()
                ->where('race_id', $user->player->race_id)
                ->where('sex', (string) $user->profile?->sex)
                ->orderBy('sort_order')
                ->orderBy('id')
                ->get()
            : collect();

        return view('interface::index', [
            'hasUnreadMail' => $this->mailbox->hasUnread($user),
            'adminToolUsers' => $adminToolUsers,
            'adminToolPrisons' => $adminToolPrisons,
            'races' => $races,
            'myAvatars' => $myAvatars,
            'gameSettings' => $this->gameSettings->get($user->id),
        ]);
    }
}
