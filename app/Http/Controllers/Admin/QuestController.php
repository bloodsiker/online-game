<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Enums\QuestRewardType;
use App\Enums\QuestType;
use App\Http\Controllers\Controller;
use App\Models\Quest\Quest;
use App\Models\Quest\QuestObjective;
use App\Models\Quest\QuestReward;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class QuestController extends Controller
{
    public function list()
    {
        $quests = Quest::withCount(['objectives', 'rewards'])
            ->orderByDesc('id')
            ->get();

        return view('admin.quest.list', compact('quests'));
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

        $quest->load(['objectives.shareItem', 'objectives.collectItem', 'rewards.itemInfo', 'rewards.location', 'startNpc', 'completeNpc', 'parentQuest', 'afterQuest']);
        $questTypes = QuestType::cases();
        $rewardTypes = QuestRewardType::cases();

        return view('admin.quest.info', compact('quest', 'questTypes', 'rewardTypes'));
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
        ]);

        return redirect()->back()->with('success', 'Награда добавлена.');
    }

    public function deleteReward(Quest $quest, QuestReward $reward): RedirectResponse
    {
        $reward->delete();

        return redirect()->back()->with('success', 'Награда удалена.');
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
