<?php

declare(strict_types=1);

namespace App\Modules\Item\Presentation\Http;

use App\Modules\Item\Application\UseCases\FillQuiver;
use App\Modules\User\Infrastructure\Persistence\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

final class QuiverController
{
    public function __construct(
        private readonly FillQuiver $fillQuiver,
    ) {}

    public function fill(int $id): JsonResponse
    {
        /** @var User $user */
        $user = Auth::user();
        $result = $this->fillQuiver->execute($user, $id);

        return response()->json([
            'ok' => $result->ok,
            'message' => $result->message,
            'quiver_item_id' => $result->quiverItemId,
            'quiver_tooltip' => $result->quiverTooltip,
        ], $result->ok ? 200 : 422);
    }
}
