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
use App\Modules\Reputation\Infrastructure\Persistence\Models\Reputation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

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

        $quest->load(['objectives.shareItem', 'objectives.collectItem', 'rewards.itemInfo', 'rewards.location', 'rewards.reputation', 'dialogues', 'startNpc', 'completeNpc', 'parentQuest', 'afterQuest']);
        $questTypes = QuestType::cases();
        $rewardTypes = QuestRewardType::cases();
        $reputations = Reputation::orderBy('name')->get();

        return view('admin.quest.info', compact('quest', 'questTypes', 'rewardTypes', 'reputations'));
    }

    public function addObjective(Request $request, Quest $quest): RedirectResponse
    {
        QuestObjective::create([
            'quest_id' => $quest->id,
            'type' => $request->input('type'),
            'target_type' => $request->input('target_type'),
            'target_id' => $request->input('target_id') ?: null,
            'share_item_id' => $request->input('share_item_id') ?: null,
            'required_amount' => (int) $request->input('required_amount', 1),
            'drop_chance' => $request->filled('drop_chance') ? (float) $request->input('drop_chance') : null,
            'description' => $request->input('description'),
        ]);

        return redirect()->back()->with('success', 'Задание добавлено.');
    }

    public function deleteObjective(Quest $quest, QuestObjective $objective): RedirectResponse
    {
        $objective->delete();

        return redirect()->back()->with('success', 'Задание удалено.');
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

    public function deleteReward(Quest $quest, QuestReward $reward): RedirectResponse
    {
        $reward->delete();

        return redirect()->back()->with('success', 'Награда удалена.');
    }

    public function addDialogue(Request $request, Quest $quest): RedirectResponse
    {
        QuestDialogue::create([
            'quest_id' => $quest->id,
            'order' => (int) $request->input('order', $quest->dialogues()->max('order') + 1),
            'description' => $request->input('description'),
            'reply_text' => $request->input('reply_text') ?: 'Далее',
        ]);

        return redirect()->back()->with('success', 'Реплика добавлена.');
    }

    public function updateDialogue(Request $request, Quest $quest, QuestDialogue $dialogue): RedirectResponse
    {
        $dialogue->update([
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
        $quest->title = $request->input('title');
        $quest->description = $request->input('description');
        $quest->type = $request->input('type');
        $quest->start_npc_id = $request->input('start_npc_id') ?: null;
        $quest->complete_npc_id = $request->input('complete_npc_id') ?: null;
        $quest->parent_quest_id = $request->input('parent_quest_id') ?: null;
        $quest->after_quest_id = $request->input('after_quest_id') ?: null;
        $quest->reset_period = $request->filled('reset_period') ? (int) $request->input('reset_period') : null;
        $quest->is_active = (bool) $request->input('is_active', true);
        $quest->is_finish = (bool) $request->input('is_finish', false);
    }
}
