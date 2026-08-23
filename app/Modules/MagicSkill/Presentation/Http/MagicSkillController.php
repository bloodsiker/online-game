<?php

declare(strict_types=1);

namespace App\Modules\MagicSkill\Presentation\Http;

use App\Http\Controllers\Controller;
use App\Modules\MagicSkill\Application\UseCases\GetMagicSkillPage;
use App\Modules\MagicSkill\Application\UseCases\LearnMagicSkillFromBook;
use App\Modules\MagicSkill\Application\UseCases\UpdateEquippedMagicSkills;
use App\Modules\MagicSkill\Application\UseCases\UpdateMagicSkillOrder;
use App\Modules\MagicSkill\Application\UseCases\UseMagicSkill;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MagicSkillController extends Controller
{
    public function __construct(
        private readonly GetMagicSkillPage $getMagicSkillPage,
        private readonly UpdateEquippedMagicSkills $updateEquippedMagicSkills,
        private readonly UpdateMagicSkillOrder $updateMagicSkillOrder,
        private readonly UseMagicSkill $useMagicSkill,
        private readonly LearnMagicSkillFromBook $learnMagicSkillFromBook,
    ) {}

    public function index(Request $request)
    {
        return view('magic-skill::index', [
            'page' => $this->getMagicSkillPage->execute(
                Auth::user(),
                (string) $request->get('group', 'magic_skill'),
            ),
        ]);
    }

    public function updateSkill(Request $request): JsonResponse
    {
        $result = $this->updateEquippedMagicSkills->execute(
            Auth::user(),
            $request->input('skills', []),
        );

        return response()->json($result->toArray(), $result->httpCode);
    }

    public function updateOrder(Request $request): JsonResponse
    {
        $result = $this->updateMagicSkillOrder->execute(
            Auth::user(),
            $request->input('ids', []),
        );

        return response()->json($result->toArray(), $result->httpCode);
    }

    public function useSkill(Request $request, int $skill): JsonResponse
    {
        $result = $this->useMagicSkill->execute(
            Auth::user(),
            $skill,
            $request->integer('target_player_id') ?: null,
        );

        return response()->json($result->toArray(), $result->httpCode);
    }

    public function learnFromBook(int $item): JsonResponse
    {
        $result = $this->learnMagicSkillFromBook->execute(Auth::user(), $item);

        return response()->json($result->toArray(), $result->httpCode);
    }
}
