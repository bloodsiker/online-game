<?php

declare(strict_types=1);

namespace App\Modules\Location\Presentation\Http;

use App\Http\Controllers\Controller;
use App\Modules\Location\Application\UseCases\GetLocationPage;
use App\Modules\Location\Application\UseCases\GetTakeItemsPage;
use App\Modules\Location\Application\UseCases\MoveToLocation;
use App\Modules\Location\Application\UseCases\PassThroughGate;
use App\Modules\User\Infrastructure\Persistence\Models\User;
use Illuminate\Support\Facades\Auth;

class LocationController extends Controller
{
    public function __construct(
        private readonly GetLocationPage $getLocationPage,
        private readonly MoveToLocation $moveToLocation,
        private readonly GetTakeItemsPage $getTakeItemsPage,
        private readonly PassThroughGate $passThroughGate,
    ) {}

    public function index(): mixed
    {
        /** @var User $user */
        $user = Auth::user();
        $result = $this->getLocationPage->execute($user);

        if ($result->fight !== null) {
            return view('battle::index', [
                'battle' => $result->fight->battle,
                'player' => $result->fight->player,
                'playerDecorator' => $result->fight->playerDecorator,
                'randomAttackedMonster' => $result->fight->randomAttackedMonster,
            ]);
        }

        return view('location::index', ['page' => $result->page]);
    }

    public function moveTo(string $direction): mixed
    {
        /** @var User $user */
        $user = Auth::user();

        return view('location::index', [
            'page' => $this->moveToLocation->execute($user, $direction),
        ]);
    }

    public function passGate(int $gateId): mixed
    {
        /** @var User $user */
        $user = Auth::user();

        return view('location::index', [
            'page' => $this->passThroughGate->execute($user, $gateId),
        ]);
    }

    public function takeItems(): mixed
    {
        /** @var User $user */
        $user = Auth::user();

        return view('location::take_items', [
            'page' => $this->getTakeItemsPage->execute($user),
        ]);
    }
}
