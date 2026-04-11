<?php

namespace App\Http\Controllers;

use App\Models\Battle\Battle;
use App\Models\Dungeon\DungeonSession;
use App\Models\Item\ItemOnLocation;
use App\Models\Monster\Monster;
use App\Models\Monster\MonsterOnLocation;
use App\Models\User;
use App\Repositories\LocationRepository;
use App\Repositories\MonsterOnLocationRepository;
use App\Services\Battle\BattleOrchestrator;
use App\Services\Battle\MonsterSelector;
use App\Services\BattleService;
use App\Services\DungeonService;
use App\Services\PlayerMovementService;
use App\Services\PlayerStatService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class LocationController extends Controller
{
    public function __construct(
        readonly protected BattleOrchestrator $battleOrchestrator,
        readonly protected BattleService $battleService,
        readonly protected MonsterSelector $monsterSelector,
        readonly protected MonsterOnLocationRepository $monsterOnLocationRepository,
        readonly protected LocationRepository $locationRepository,
        readonly protected PlayerMovementService $playerMovementService,
        readonly protected PlayerStatService $statService,
        readonly protected DungeonService $dungeonService,
    ) {}

    public function index()
    {
        $user = Auth::user();

        // Teleport player out if dungeon session expired
        if ($this->dungeonService->expireSessionIfNeeded($user)) {
            session()->flash('message', 'Время в подземелье истекло! Вы возвращены к входу.');
            return redirect()->route('location');
        }

        $player = $user->player;
        $playerDecorator = $this->statService->resolve($player);
        $location = $user->currentLocation;

        if (!$location) {
            abort(404);
        }

        $battle = $this->battleOrchestrator->handleLocationEntry($location);
//        $battle = $this->battleService->battleOnLocation($location);

        if ($battle instanceof Battle) {
            $randomAttackedMonster = $this->monsterSelector->getRandomActiveMonster($battle);
            if ($randomAttackedMonster->locationMonster instanceof MonsterOnLocation) {
                $randomAttackedMonster->locationMonster->regenerate();
            }

            return view('fight.index', compact('battle', 'randomAttackedMonster', 'player', 'playerDecorator'));
        }

        $monsterOnLocation   = $this->monsterOnLocationRepository->getMonstersOnLocation($location);
        $locationUsersJson   = $this->getLocationUsersJson($location->id);
        $dungeonSession      = $this->dungeonService->getActiveSession($user->id);
        $itemsOnLocationCount = $this->getItemsOnLocation($user, $location->id)->count();

        return view('location.index', compact('location', 'battle', 'monsterOnLocation', 'player', 'playerDecorator', 'user', 'locationUsersJson', 'dungeonSession', 'itemsOnLocationCount'));
    }

    public function moveTo($direction)
    {
        $user = Auth::user();

        $result = $this->playerMovementService->move($user, $direction);

        if (!$result->success) {
            session()->flash('message', $result->message);
        }

        $location = $this->locationRepository->getOneById($user->location_id);

        $battle = $this->battleService->battleOnLocation($location);
//        $battle = $this->battleOrchestrator->handlePlayerAttack($location);

        $monsterOnLocation = $this->monsterOnLocationRepository->getMonstersOnLocation($location);

        return view('location.index', [
            'location'             => $location,
            'battle'               => $battle,
            'monsterOnLocation'    => $monsterOnLocation,
            'user'                 => $user,
            'player'               => $user->player,
            'playerDecorator'      => $this->statService->resolve($user->player),
            'speedModifier'        => $result->speedModifier,
            'locationUsersJson'    => $this->getLocationUsersJson($location->id),
            'dungeonSession'       => $this->dungeonService->getActiveSession($user->id),
            'itemsOnLocationCount' => $this->getItemsOnLocation($user, $location->id)->count(),
        ]);
    }

    private function getLocationUsersJson(int $locationId): string
    {
        $tenMinutesAgo = Carbon::now()->subMinutes(10);

        $users = User::with(['player', 'clanMembership.clan'])
            ->where('location_id', $locationId)
            ->orderByDesc('last_online_at')
            ->get()
            ->map(function (User $u) use ($tenMinutesAgo): array {
                $clan = $u->clanMembership?->clan;
                return [
                    'id'        => $u->id,
                    'name'      => $u->name,
                    'lvl'       => $u->player->lvl,
                    'time'      => $u->last_online_at?->format('H:i') ?? '',
                    'is_online' => ($u->last_online_at?->timestamp ?? 0) > $tenMinutesAgo->timestamp,
                    'info_url'  => route('info.user', ['id' => $u->id]),
                    'clan_name' => $clan?->name,
                    'clan_icon' => $clan?->icon ? Storage::disk('public')->url($clan->icon) : null,
                ];
            });

        return json_encode($users->values(), JSON_UNESCAPED_UNICODE);
    }

    public function takeItems()
    {
        $user     = Auth::user();
        $location = $user->currentLocation;

        $itemsOnLocation = $this->getItemsOnLocation($user, $location->id);

        return view('location.take_items', compact('location', 'itemsOnLocation'));
    }

    private function getItemsOnLocation(User $user, int $locationId): \Illuminate\Database\Eloquent\Collection
    {
        $query = ItemOnLocation::with(['item', 'item.itemInfo'])->where('location_id', $locationId);

        $dungeonSessionId = null;
        if ($user->currentLocation->dungeon_id !== null) {
            $session          = DungeonSession::where('user_id', $user->id)->first();
            $dungeonSessionId = $session?->monsterSessionId();
        }

        if ($dungeonSessionId !== null) {
            $query->where('dungeon_session_id', $dungeonSessionId);
        } else {
            $query->whereNull('dungeon_session_id');
        }

        return $query->get();
    }
}
