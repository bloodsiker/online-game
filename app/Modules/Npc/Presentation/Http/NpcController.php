<?php

declare(strict_types=1);

namespace App\Modules\Npc\Presentation\Http;

use App\Http\Controllers\Controller;
use App\Modules\Npc\Application\UseCases\GetNpcInfoPage;
use App\Modules\Npc\Application\UseCases\GetNpcPage;
use App\Modules\Npc\Infrastructure\Persistence\Models\Npc;
use App\Modules\Npc\Infrastructure\Persistence\Models\NpcDialogueNode;
use App\Modules\User\Infrastructure\Persistence\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NpcController extends Controller
{
    public function __construct(
        private readonly GetNpcPage $getNpcPage,
        private readonly GetNpcInfoPage $getNpcInfoPage,
    ) {}

    public function index(int $id)
    {
        /** @var User $user */
        $user = Auth::user();
        $page = $this->getNpcPage->execute($user, $id);

        return view('npc::index', [
            'npc' => $page->npc,
            'quests' => $page->quests,
            'questsInProgress' => $page->questsInProgress,
            'questsOnCooldown' => $page->questsOnCooldown,
            'message' => $page->message,
            'messageType' => $page->messageType,
            'player' => $page->player,
            'dialogueStartNode' => $page->dialogueStartNode,
            'reputationShops' => $page->reputationShops,
            'isClanRegistrar' => $page->isClanRegistrar,
            'hasClanMembership' => $page->hasClanMembership,
        ]);
    }

    public function info(string $uuid)
    {
        return view('npc::info', [
            'page' => $this->getNpcInfoPage->execute($uuid),
        ]);
    }

    public function dialogue(Request $request, int $id, ?int $node = null)
    {
        $npc = Npc::findOrFail($id);
        $dialogueNode = $node !== null
            ? NpcDialogueNode::where('npc_id', $npc->id)->where('is_active', true)->findOrFail($node)
            : NpcDialogueNode::where('npc_id', $npc->id)
                ->where('is_active', true)
                ->orderByDesc('is_start')
                ->orderBy('sort_order')
                ->orderBy('id')
                ->firstOrFail();

        $dialogueNode->load([
            'activeOptions.toNode' => fn ($query) => $query->where('npc_id', $npc->id),
        ]);

        // Узел, из которого пришли — для кнопки «Назад» (активный, того же НПС)
        $fromId = $request->integer('from') ?: null;
        $backNode = $fromId
            ? NpcDialogueNode::where('npc_id', $npc->id)->where('is_active', true)->find($fromId)
            : null;

        return view('npc::dialogue', [
            'npc' => $npc,
            'node' => $dialogueNode,
            'backNode' => $backNode,
        ]);
    }
}
