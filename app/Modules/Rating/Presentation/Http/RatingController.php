<?php

declare(strict_types=1);

namespace App\Modules\Rating\Presentation\Http;

use App\Modules\Rating\Application\UseCases\GetRatingPage;
use App\Modules\Rating\Application\UseCases\SearchRating;
use App\Modules\User\Infrastructure\Persistence\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RatingController
{
    public function __construct(
        private readonly GetRatingPage $getRatingPage,
        private readonly SearchRating $searchRating,
    ) {}

    public function index(Request $request)
    {
        /** @var User $user */
        $user = Auth::user();

        return view('rating::index', $this->getRatingPage->execute(
            $user,
            (string) $request->query('type', 'level'),
        ));
    }

    public function search(Request $request): JsonResponse
    {
        $result = $this->searchRating->execute(
            (string) $request->get('nick', ''),
            (string) $request->get('type', 'level'),
        );

        if (! $result->ok) {
            return response()->json(['error' => $result->error]);
        }

        return response()->json([
            'page' => $result->page,
            'position' => $result->position,
        ]);
    }
}
