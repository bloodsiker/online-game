<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Modules\Npc\Infrastructure\Persistence\Models\Npc;
use App\Modules\Quest\Domain\Enums\QuestRewardType;
use App\Modules\Quest\Domain\Enums\QuestType;
use App\Modules\Quest\Infrastructure\Persistence\Models\Quest;
use App\Modules\Quest\Infrastructure\Persistence\Models\QuestDialogue;
use App\Modules\Quest\Infrastructure\Persistence\Models\QuestObjective;
use App\Modules\Quest\Infrastructure\Persistence\Models\QuestReward;
use App\Modules\Quest\Infrastructure\Persistence\Models\QuestStage;
use App\Modules\Reputation\Infrastructure\Persistence\Models\Reputation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class QuestController extends Controller
{
    public function list(Request $request)
    {
        $filters = [
            'q' => trim((string) $request->query('q', '')),
            'type' => (string) $request->query('type', ''),
            'is_active' => (string) $request->query('is_active', ''),
            'is_finish' => (string) $request->query('is_finish', ''),
            'repeatable' => (string) $request->query('repeatable', ''),
            'start_npc_id' => (int) $request->query('start_npc_id', 0),
            'complete_npc_id' => (int) $request->query('complete_npc_id', 0),
        ];

        $quests = Quest::query()
            ->with(['startNpc', 'completeNpc'])
            ->withCount(['objectives', 'rewards'])
            ->when($filters['q'] !== '', function ($query) use ($filters): void {
                $search = '%'.str_replace(['%', '_'], ['\%', '\_'], $filters['q']).'%';
                $query->where(function ($query) use ($search): void {
                    $query->where('title', 'like', $search)
                        ->orWhere('description', 'like', $search);
                });
            })
            ->when(QuestType::tryFrom($filters['type']) !== null, fn ($query) => $query->where('type', $filters['type']))
            ->when(in_array($filters['is_active'], ['0', '1'], true), fn ($query) => $query->where('is_active', (int) $filters['is_active']))
            ->when(in_array($filters['is_finish'], ['0', '1'], true), fn ($query) => $query->where('is_finish', (int) $filters['is_finish']))
            ->when($filters['repeatable'] === 'yes', fn ($query) => $query->whereNotNull('reset_period'))
            ->when($filters['repeatable'] === 'no', fn ($query) => $query->whereNull('reset_period'))
            ->when($filters['start_npc_id'] > 0, fn ($query) => $query->where('start_npc_id', $filters['start_npc_id']))
            ->when($filters['complete_npc_id'] > 0, fn ($query) => $query->where('complete_npc_id', $filters['complete_npc_id']))
            ->orderByDesc('id')
            ->paginate(50)
            ->withQueryString();

        $questTypes = QuestType::cases();
        $selectedStartNpc = $filters['start_npc_id'] > 0 ? Npc::find($filters['start_npc_id']) : null;
        $selectedCompleteNpc = $filters['complete_npc_id'] > 0 ? Npc::find($filters['complete_npc_id']) : null;

        return view('admin.quest.list', compact('quests', 'filters', 'questTypes', 'selectedStartNpc', 'selectedCompleteNpc'));
    }

    public function create(Request $request): mixed
    {
        if ($request->isMethod('POST')) {
            $quest = new Quest;
            $this->fillQuest($quest, $request);
            $quest->save();

            return redirect()->route('admin.quest.info', $quest->id)
                ->with('success', 'Квест создан.');
        }

        $questTypes = QuestType::cases();

        return view('admin.quest.create', compact('questTypes'));
    }

    public function info(Request $request, Quest $quest): mixed
    {
        if ($request->isMethod('POST')) {
            $this->fillQuest($quest, $request);
            $quest->save();

            return redirect()->back()->with('success', 'Сохранено.');
        }

        $quest->load([
            'objectives.shareItem', 'objectives.collectItem', 'rewards.itemInfo',
            'rewards.location', 'rewards.reputation', 'dialogues.npc', 'stages.completeNpc', 'stages.grantItem',
            'startNpc', 'completeNpc', 'parentQuest', 'afterQuest', 'nextQuests',
        ]);
        $questTypes = QuestType::cases();
        $rewardTypes = QuestRewardType::cases();
        $reputations = Reputation::orderBy('name')->get();

        return view('admin.quest.info', compact('quest', 'questTypes', 'rewardTypes', 'reputations'));
    }

    public function addObjective(Request $request, Quest $quest): RedirectResponse
    {
        $targetIds = collect(explode(',', (string) $request->input('target_ids')))
            ->map(fn ($id) => (int) trim($id))
            ->filter()
            ->values();

        $type = (string) $request->input('type');
        $shareItemId = $request->input('share_item_id') ?: null;
        $targetId = $request->input('target_id') ?: $targetIds->first();
        if ($type === 'deliver' && $shareItemId) {
            $targetId = $shareItemId;
        } elseif ($type === 'use_item' && $request->input('target_type') === 'item' && ! $targetId) {
            $targetId = $shareItemId;
        }

        QuestObjective::create([
            'quest_id' => $quest->id,
            'stage_id' => $request->input('stage_id') ?: null,
            'type' => $type,
            'target_type' => $request->input('target_type'),
            'target_id' => $targetId,
            'target_ids' => $targetIds->count() > 1 ? $targetIds->all() : null,
            'share_item_id' => $shareItemId,
            'consume_item' => $request->boolean('consume_item', true),
            'map_id' => $request->input('map_id') ?: null,
            'required_amount' => (int) $request->input('required_amount', 1),
            'drop_chance' => $request->filled('drop_chance') ? (float) $request->input('drop_chance') : null,
            'description' => $request->input('description'),
        ]);

        return redirect()->back()->with('success', 'Задание добавлено.');
    }

    public function updateObjective(Request $request, Quest $quest, QuestObjective $objective): RedirectResponse
    {
        abort_unless((int) $objective->quest_id === (int) $quest->id, 404);
        $targetIds = collect(explode(',', (string) $request->input('target_ids')))
            ->map(fn ($id) => (int) trim($id))
            ->filter()
            ->values();

        $type = (string) $request->input('type');
        $shareItemId = $request->input('share_item_id') ?: null;
        $targetId = $request->input('target_id') ?: $targetIds->first();
        if ($type === 'deliver' && $shareItemId) {
            $targetId = $shareItemId;
        } elseif ($type === 'use_item' && $request->input('target_type') === 'item' && ! $targetId) {
            $targetId = $shareItemId;
        }

        $objective->update([
            'stage_id' => $request->input('stage_id') ?: null,
            'type' => $type,
            'target_type' => $request->input('target_type'),
            'target_id' => $targetId,
            'target_ids' => $targetIds->count() > 1 ? $targetIds->all() : null,
            'share_item_id' => $shareItemId,
            'consume_item' => $request->boolean('consume_item', true),
            'map_id' => $request->input('map_id') ?: null,
            'required_amount' => (int) $request->input('required_amount', 1),
            'drop_chance' => $request->filled('drop_chance') ? (float) $request->input('drop_chance') : null,
            'description' => $request->input('description'),
        ]);

        return redirect()->back()->with('success', 'Задание сохранено.');
    }

    public function deleteObjective(Quest $quest, QuestObjective $objective): RedirectResponse
    {
        abort_unless((int) $objective->quest_id === (int) $quest->id, 404);
        $objective->delete();

        return redirect()->back()->with('success', 'Задание удалено.');
    }

    public function addStage(Request $request, Quest $quest): RedirectResponse
    {
        $data = $this->stageData($request, $quest);
        $quest->stages()->create($data);

        return redirect()->back()->with('success', 'Этап добавлен.');
    }

    public function updateStage(Request $request, Quest $quest, QuestStage $stage): RedirectResponse
    {
        abort_unless((int) $stage->quest_id === (int) $quest->id, 404);
        $stage->update($this->stageData($request, $quest, $stage));

        return redirect()->back()->with('success', 'Этап сохранён.');
    }

    public function deleteStage(Quest $quest, QuestStage $stage): RedirectResponse
    {
        abort_unless((int) $stage->quest_id === (int) $quest->id, 404);
        $stage->delete();

        return redirect()->back()->with('success', 'Этап удалён. Цели этапа отвязаны.');
    }

    public function addReward(Request $request, Quest $quest): RedirectResponse
    {
        QuestReward::create([
            'quest_id' => $quest->id,
            'type' => $request->input('type'),
            'amount' => (int) $request->input('amount', 0),
            'share_item_id' => $request->input('share_item_id') ?: null,
            'location_id' => $request->input('location_id') ?: null,
            'reputation_id' => $request->input('reputation_id') ?: null,
        ]);

        return redirect()->back()->with('success', 'Награда добавлена.');
    }

    public function updateReward(Request $request, Quest $quest, QuestReward $reward): RedirectResponse
    {
        $reward->update([
            'type' => $request->input('type'),
            'amount' => (int) $request->input('amount', 0),
            'share_item_id' => $request->input('share_item_id') ?: null,
            'location_id' => $request->input('location_id') ?: null,
            'reputation_id' => $request->input('reputation_id') ?: null,
        ]);

        return redirect()->back()->with('success', 'Награда сохранена.');
    }

    public function deleteReward(Quest $quest, QuestReward $reward): RedirectResponse
    {
        $reward->delete();

        return redirect()->back()->with('success', 'Награда удалена.');
    }

    public function addDialogue(Request $request, Quest $quest): RedirectResponse
    {
        QuestDialogue::create([
            'quest_id' => $quest->id,
            'npc_id' => $request->input('npc_id') ?: null,
            'order' => (int) $request->input('order', $quest->dialogues()->max('order') + 1),
            'description' => $request->input('description'),
            'reply_text' => $request->input('reply_text') ?: 'Далее',
        ]);

        return redirect()->back()->with('success', 'Реплика добавлена.');
    }

    public function updateDialogue(Request $request, Quest $quest, QuestDialogue $dialogue): RedirectResponse
    {
        $dialogue->update([
            'npc_id' => $request->input('npc_id') ?: null,
            'order' => (int) $request->input('order', $dialogue->order),
            'description' => $request->input('description'),
            'reply_text' => $request->input('reply_text') ?: 'Далее',
        ]);

        return redirect()->back()->with('success', 'Реплика сохранена.');
    }

    public function deleteDialogue(Quest $quest, QuestDialogue $dialogue): RedirectResponse
    {
        $dialogue->delete();

        return redirect()->back()->with('success', 'Реплика удалена.');
    }

    // ─────────────────────────────────────────────────────────────────────────

    private function fillQuest(Quest $quest, Request $request): void
    {
        $parentQuestId = $request->input('parent_quest_id') ?: null;
        $previousQuestId = $request->input('after_quest_id') ?: null;

        if ($quest->exists && ((int) $parentQuestId === (int) $quest->id || (int) $previousQuestId === (int) $quest->id)) {
            throw ValidationException::withMessages([
                'after_quest_id' => 'Квест не может ссылаться сам на себя.',
            ]);
        }

        if ($quest->exists && $this->dependencyChainContains((int) $previousQuestId, (int) $quest->id)) {
            throw ValidationException::withMessages([
                'after_quest_id' => 'Такая связь создаёт циклическую цепочку квестов.',
            ]);
        }

        $quest->title = $request->input('title');
        $quest->description = $request->input('description');
        $quest->type = $request->input('type');
        $quest->start_npc_id = $request->input('start_npc_id') ?: null;
        $quest->complete_npc_id = $request->input('complete_npc_id') ?: null;
        $quest->parent_quest_id = $parentQuestId;
        $quest->after_quest_id = $previousQuestId;
        $quest->reset_period = $request->filled('reset_period') ? (int) $request->input('reset_period') : null;
        $quest->is_active = (bool) $request->input('is_active', true);
        $quest->is_finish = (bool) $request->input('is_finish', false);
    }

    private function dependencyChainContains(int $questId, int $searchedQuestId): bool
    {
        $visited = [];

        while ($questId > 0 && ! isset($visited[$questId])) {
            if ($questId === $searchedQuestId) {
                return true;
            }

            $visited[$questId] = true;
            $questId = (int) Quest::query()->whereKey($questId)->value('after_quest_id');
        }

        return false;
    }

    /** @return array<string, mixed> */
    private function stageData(Request $request, Quest $quest, ?QuestStage $stage = null): array
    {
        $stageType = in_array($request->input('stage_type'), ['action', 'wait'], true)
            ? $request->input('stage_type')
            : 'action';

        return [
            'complete_npc_id' => $request->input('complete_npc_id') ?: null,
            'order' => max(1, (int) $request->input('order', $stage?->order ?? (($quest->stages()->max('order') ?? 0) + 1))),
            'title' => $request->input('title'),
            'description' => $request->input('description'),
            'stage_type' => $stageType,
            'wait_duration_seconds' => $stageType === 'wait'
                ? max(1, (int) $request->input('wait_duration_seconds', 60))
                : null,
            'waiting_text' => $stageType === 'wait'
                ? ($request->input('waiting_text') ?: 'Ты пришёл слишком рано. Возвращайся позже.')
                : null,
            'ready_text' => $stageType === 'wait' ? $request->input('ready_text') : null,
            'grant_share_item_id' => $request->input('grant_share_item_id') ?: null,
            'grant_amount' => max(1, (int) $request->input('grant_amount', 1)),
        ];
    }
}
