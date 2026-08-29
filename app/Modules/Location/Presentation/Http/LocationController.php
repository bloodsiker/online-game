<?php

declare(strict_types=1);

namespace App\Modules\Location\Presentation\Http;

use App\Http\Controllers\Controller;
use App\Modules\Location\Application\UseCases\GetGatheringPage;
use App\Modules\Location\Application\UseCases\GetLocationPage;
use App\Modules\Location\Application\UseCases\GetMapsPage;
use App\Modules\Location\Application\UseCases\GetTakeItemsPage;
use App\Modules\Location\Application\UseCases\MoveToLocation;
use App\Modules\Location\Application\UseCases\PassThroughGate;
use App\Modules\Location\Domain\Services\GatheringService;
use App\Modules\User\Infrastructure\Persistence\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class LocationController extends Controller
{
    public function __construct(
        private readonly GetLocationPage $getLocationPage,
        private readonly GetGatheringPage $getGatheringPage,
        private readonly GetMapsPage $getMapsPage,
        private readonly MoveToLocation $moveToLocation,
        private readonly GetTakeItemsPage $getTakeItemsPage,
        private readonly PassThroughGate $passThroughGate,
        private readonly GatheringService $gatheringService,
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

    public function gathering(): mixed
    {
        /** @var User $user */
        $user = Auth::user();

        return view('location::gathering', [
            'page' => $this->getGatheringPage->execute($user),
        ]);
    }

    public function gatheringState(): JsonResponse
    {
        /** @var User $user */
        $user = Auth::user();

        return response()->json($this->gatheringService->state($user));
    }

    public function startGathering(int $node): JsonResponse
    {
        /** @var User $user */
        $user = Auth::user();
        $result = $this->gatheringService->start($user, $node);

        return response()->json($result->toArray(), $result->httpCode);
    }

    public function completeGathering(): JsonResponse
    {
        /** @var User $user */
        $user = Auth::user();
        $result = $this->gatheringService->complete($user);

        return response()->json($result->toArray(), $result->httpCode);
    }

    public function cancelGathering(): JsonResponse
    {
        /** @var User $user */
        $user = Auth::user();
        $result = $this->gatheringService->cancel($user);

        return response()->json($result->toArray(), $result->httpCode);
    }

    public function maps(): mixed
    {
        /** @var ?User $user */
        $user = Auth::user();
        $currentMapId = $user?->loadMissing('currentLocation')->currentLocation?->map_id;

        return view('location::maps', [
            'page' => $this->getMapsPage->execute($currentMapId !== null ? (int) $currentMapId : null),
        ]);
    }
}
