<?php

declare(strict_types=1);

namespace App\Modules\Event\Presentation\Http;

use App\Modules\Event\Domain\Services\WorldEventFavoriteService;
use App\Modules\Event\Infrastructure\Persistence\Models\WorldEvent;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final readonly class WorldEventFavoriteController
{
    public function __construct(private WorldEventFavoriteService $favoriteService) {}

    public function __invoke(Request $request, WorldEvent $event): JsonResponse
    {
        $validated = $request->validate([
            'favorite' => ['required', 'boolean'],
        ]);

        $favorite = (bool) $validated['favorite'];
        $this->favoriteService->set(
            userId: (int) $request->user()->id,
            eventId: (int) $event->id,
            favorite: $favorite,
        );

        return response()->json([
            'ok' => true,
            'favorite' => $favorite,
        ]);
    }
}
