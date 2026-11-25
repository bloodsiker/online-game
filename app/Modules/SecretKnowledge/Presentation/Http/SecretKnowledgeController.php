<?php

declare(strict_types=1);

namespace App\Modules\SecretKnowledge\Presentation\Http;

use App\Http\Controllers\Controller;
use App\Modules\Item\Application\ItemTooltip\ItemTooltipCollector;
use App\Modules\Item\Application\ItemTooltip\Strategy\ShareItemTooltipStrategy;
use App\Modules\SecretKnowledge\Application\Services\SecretKnowledgeService;
use App\Modules\SecretKnowledge\Infrastructure\Persistence\Models\BossKnowledgeManual;
use App\Modules\User\Infrastructure\Persistence\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

final class SecretKnowledgeController extends Controller
{
    public function __construct(
        private readonly SecretKnowledgeService $knowledge,
        private readonly ItemTooltipCollector $tooltipCollector,
    ) {}

    public function index(Request $request, int $npc): View
    {
        /** @var User $user */
        $user = $request->user();
        abort_unless($this->knowledge->atNpc($user, $npc), 403);
        $page = $this->knowledge->pageData($user);
        $settings = $this->knowledge->settings();

        // Тултипы при наведении: ворох, пособия и части боссов.
        $this->tooltipCollector->collectFrom(new ShareItemTooltipStrategy(collect([$settings?->bundle])
            ->merge($page['manuals']->pluck('manual'))
            ->merge($page['manuals']->pluck('bossPart'))
            ->filter()
            ->unique('id')
            ->values()));

        return view('secret-knowledge::index', [
            'npcId' => $npc,
            'settings' => $settings,
            'points' => $page['points'],
            'bundleCount' => $page['bundleCount'],
            'manuals' => $page['manuals'],
            'itemTooltipScript' => $this->tooltipCollector->renderScript(),
            'activeStudy' => $this->knowledge->activeStudy($user),
        ]);
    }

    public function startStudy(Request $request, int $npc): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        return response()->json($this->knowledge->startStudy($user, $npc));
    }

    public function completeStudy(Request $request, int $npc): JsonResponse
    {
        $data = $request->validate(['token' => ['required', 'string', 'max:64']]);
        /** @var User $user */
        $user = $request->user();

        return response()->json($this->knowledge->completeStudy($user, $npc, (string) $data['token']));
    }

    public function cancelStudy(Request $request, int $npc): JsonResponse
    {
        $data = $request->validate(['token' => ['required', 'string', 'max:64']]);
        /** @var User $user */
        $user = $request->user();
        $this->knowledge->cancelStudy($user, (string) $data['token']);

        return response()->json(['ok' => true, 'message' => __('secret_knowledge.study_cancelled')]);
    }

    public function study(Request $request, int $npc, BossKnowledgeManual $manual): RedirectResponse
    {
        /** @var User $user */
        $user = $request->user();
        $studied = $this->knowledge->studyManual($user, $npc, (int) $manual->id);

        return redirect()->route('secret_knowledge.index', ['npc' => $npc])
            ->with('message', __('secret_knowledge.study_result', ['manual' => $studied->manual->name]));
    }
}
