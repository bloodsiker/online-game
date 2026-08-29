<?php

declare(strict_types=1);

namespace App\Modules\Structure\Workshop\Presentation\Http;

use App\Http\Controllers\Controller;
use App\Modules\Structure\Workshop\Application\UseCases\CraftProfessionItem;
use App\Modules\Structure\Workshop\Application\UseCases\GetWorkshopPage;
use App\Modules\Structure\Workshop\Application\UseCases\LearnRecipe;
use App\Modules\User\Infrastructure\Persistence\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

class WorkshopController extends Controller
{
    public function __construct(
        private readonly GetWorkshopPage $getWorkshopPage,
        private readonly CraftProfessionItem $craftProfessionItem,
        private readonly LearnRecipe $learnRecipe,
    ) {}

    public function index(int $id): mixed
    {
        /** @var User $user */
        $user = Auth::user();

        return view('workshop::index', $this->getWorkshopPage->execute($user, $id));
    }

    public function craft(int $id, int $recipe): RedirectResponse
    {
        /** @var User $user */
        $user = Auth::user();
        $result = $this->craftProfessionItem->execute($user, $id, $recipe);

        return redirect()->back()->with($result->ok ? 'message' : 'error', $result->message);
    }

    public function learn(int $item): JsonResponse
    {
        /** @var User $user */
        $user = Auth::user();
        $result = $this->learnRecipe->execute($user, $item);

        return response()->json($result->toArray(), $result->httpCode);
    }
}
