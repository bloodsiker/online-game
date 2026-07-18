<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Modules\Npc\Infrastructure\Persistence\Models\Npc;
use App\Modules\Npc\Infrastructure\Persistence\Models\NpcDialogueNode;
use App\Modules\Npc\Infrastructure\Persistence\Models\NpcDialogueOption;
use Illuminate\Http\Request;

class NpcController extends Controller
{
    public function list()
    {
        $list = Npc::with('location')->orderByDesc('id')->get();

        return view('admin.npc.list', compact('list'));
    }

    public function dialogues()
    {
        $list = Npc::with('location')
            ->withCount([
                'dialogueNodes',
                'dialogueNodes as active_dialogue_nodes_count' => static fn ($query) => $query->where('is_active', true),
            ])
            ->orderByDesc('dialogue_nodes_count')
            ->orderByDesc('id')
            ->get();

        return view('admin.npc.dialogues', compact('list'));
    }

    public function create(Request $request): mixed
    {
        if ($request->isMethod('POST')) {
            $npc = new Npc;
            $this->fillNpc($npc, $request);
            $npc->save();

            return redirect()->route('admin.npc.info', $npc->id)
                ->with('success', 'НПС создан.');
        }

        return view('admin.npc.create');
    }

    public function info(Request $request, Npc $npc): mixed
    {
        if ($request->isMethod('POST')) {
            $this->fillNpc($npc, $request);
            $npc->save();

            return redirect()->back()->with('success', 'Сохранено.');
        }

        $npc->load(['dialogueNodes.options.toNode']);

        return view('admin.npc.info', compact('npc'));
    }

    public function addDialogueNode(Request $request, Npc $npc): mixed
    {
        $data = $this->validateDialogueNode($request);
        $data['npc_id'] = $npc->id;
        $data['is_start'] = $request->boolean('is_start') || ! $npc->dialogueNodes()->exists();
        $data['is_active'] = $request->boolean('is_active');

        if ($data['is_start']) {
            $npc->dialogueNodes()->update(['is_start' => false]);
        }

        NpcDialogueNode::create($data);

        return redirect()->route('admin.npc.info', $npc->id)->with('success', 'Ветка диалога добавлена.');
    }

    public function updateDialogueNode(Request $request, Npc $npc, NpcDialogueNode $node): mixed
    {
        $this->ensureNodeBelongsToNpc($npc, $node);

        $data = $this->validateDialogueNode($request);
        $data['is_start'] = $request->boolean('is_start');
        $data['is_active'] = $request->boolean('is_active');

        if ($data['is_start']) {
            $npc->dialogueNodes()->where('id', '!=', $node->id)->update(['is_start' => false]);
        }

        $node->update($data);

        return redirect()->route('admin.npc.info', $npc->id)->with('success', 'Ветка диалога сохранена.');
    }

    public function deleteDialogueNode(Npc $npc, NpcDialogueNode $node): mixed
    {
        $this->ensureNodeBelongsToNpc($npc, $node);

        $hasIncomingOptions = NpcDialogueOption::where('to_node_id', $node->id)->exists();
        if ($hasIncomingOptions) {
            return redirect()->route('admin.npc.info', $npc->id)
                ->with('error', 'Нельзя удалить ветку, пока на нее ведут кнопки.');
        }

        $wasStart = $node->is_start;
        $node->delete();

        if ($wasStart) {
            $nextNode = $npc->dialogueNodes()->orderBy('sort_order')->orderBy('id')->first();
            if ($nextNode) {
                $nextNode->update(['is_start' => true]);
            }
        }

        return redirect()->route('admin.npc.info', $npc->id)->with('success', 'Ветка диалога удалена.');
    }

    public function addDialogueOption(Request $request, Npc $npc, NpcDialogueNode $node): mixed
    {
        $this->ensureNodeBelongsToNpc($npc, $node);
        $data = $this->validateDialogueOption($request, $npc);
        $data['npc_dialogue_node_id'] = $node->id;
        $data['is_active'] = $request->boolean('is_active');

        NpcDialogueOption::create($data);

        return redirect()->route('admin.npc.info', $npc->id)->with('success', 'Кнопка диалога добавлена.');
    }

    public function updateDialogueOption(Request $request, Npc $npc, NpcDialogueNode $node, NpcDialogueOption $option): mixed
    {
        $this->ensureNodeBelongsToNpc($npc, $node);
        if ((int) $option->npc_dialogue_node_id !== (int) $node->id) {
            abort(404);
        }

        $data = $this->validateDialogueOption($request, $npc);
        $data['is_active'] = $request->boolean('is_active');
        $option->update($data);

        return redirect()->route('admin.npc.info', $npc->id)->with('success', 'Кнопка диалога сохранена.');
    }

    public function deleteDialogueOption(Npc $npc, NpcDialogueNode $node, NpcDialogueOption $option): mixed
    {
        $this->ensureNodeBelongsToNpc($npc, $node);
        if ((int) $option->npc_dialogue_node_id !== (int) $node->id) {
            abort(404);
        }

        $option->delete();

        return redirect()->route('admin.npc.info', $npc->id)->with('success', 'Кнопка диалога удалена.');
    }

    // ─────────────────────────────────────────────────────────────────────────

    private function fillNpc(Npc $npc, Request $request): void
    {
        $npc->name = $request->input('name');
        $npc->description = $request->input('description');
        $npc->location_id = $request->input('location_id') ?: null;

        if ($request->hasFile('image')) {
            $npc->image = $request->file('image')->store('npc', 'public');
        }
    }

    /**
     * @return array{title: string, description: string, sort_order: int}
     */
    private function validateDialogueNode(Request $request): array
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:65535'],
        ]);
        $data['sort_order'] = (int) ($data['sort_order'] ?? 0);

        return $data;
    }

    /**
     * @return array{button_text: string, to_node_id: int, sort_order: int}
     */
    private function validateDialogueOption(Request $request, Npc $npc): array
    {
        $data = $request->validate([
            'button_text' => ['required', 'string', 'max:255'],
            'to_node_id' => ['required', 'integer'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:65535'],
        ]);
        $data['sort_order'] = (int) ($data['sort_order'] ?? 0);

        $targetExists = $npc->dialogueNodes()->whereKey((int) $data['to_node_id'])->exists();
        if (! $targetExists) {
            abort(422, 'Целевая ветка диалога не принадлежит этому НПС.');
        }

        $data['to_node_id'] = (int) $data['to_node_id'];

        return $data;
    }

    private function ensureNodeBelongsToNpc(Npc $npc, NpcDialogueNode $node): void
    {
        if ((int) $node->npc_id !== (int) $npc->id) {
            abort(404);
        }
    }
}
