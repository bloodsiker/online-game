<?php

namespace App\Http\Controllers;

use App\Models\Battle\Battle;
use App\Models\Item\ItemOnLocation;
use App\Repositories\LocationRepository;
use App\Repositories\MonsterOnLocationRepository;
use App\Services\Battle\BattleOrchestrator;
use App\Services\Battle\MonsterSelector;
use App\Services\BattleService;
use Illuminate\Support\Facades\Auth;

class LocationController extends Controller
{
    public function __construct(
        readonly protected BattleOrchestrator $battleOrchestrator,
        readonly protected BattleService $battleService,
        readonly protected MonsterSelector $monsterSelector,
        readonly protected MonsterOnLocationRepository $monsterOnLocationRepository,
        readonly protected LocationRepository $locationRepository,
    ) {}

    public function index()
    {
        $user = Auth::user();
        $player = $user->player;
        $location = $user->currentLocation;

        if (!$location) {
            abort(404);
        }

        $battle = $this->battleOrchestrator->handleLocationEntry($location);
//        $battle = $this->battleService->battleOnLocation($location);

        if ($battle instanceof Battle) {
            $randomAttackedMonster = $this->monsterSelector->getRandomActiveMonster($battle);

            return view('fight.index', compact('battle', 'randomAttackedMonster', 'player'));
        }

        $monsterOnLocation = $this->monsterOnLocationRepository->getMonstersOnLocation($location);

        return view('location.index', compact('location', 'battle', 'monsterOnLocation', 'player'));
    }

    public function moveTo($direction)
    {
        $user = Auth::user();
        $player = $user->player;
        $location = $user->currentLocation;

        if ($location->$direction) {
            $user->prev_location_id = $user->location_id;
            $user->location_id = $location->$direction;
            $user->save();
        }

        $location = $this->locationRepository->getOneById($user->location_id);

        $battle = $this->battleService->battleOnLocation($location);
//        $battle = $this->battleOrchestrator->handlePlayerAttack($location);

        $monsterOnLocation = $this->monsterOnLocationRepository->getMonstersOnLocation($location);

        return view('location.index', compact('location', 'battle', 'monsterOnLocation', 'player'));
    }

    public function takeItems()
    {
        $user = Auth::user();
        $location = $user->currentLocation;

        $itemsOnLocation = ItemOnLocation::with(['item', 'item.itemInfo'])->where(['location_id' => $location->id])->get();

        return view('location.take_items', compact('location', 'itemsOnLocation'));
    }
}
