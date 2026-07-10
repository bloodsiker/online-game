<?php

declare(strict_types=1);

namespace App\Modules\Event\Presentation\Http\Admin;

use App\Modules\Event\Domain\Enums\ActivityBonusRewardType;
use App\Modules\Event\Domain\Enums\ActivityPeriod;
use App\Modules\Event\Infrastructure\Persistence\Models\EventActivity;
use App\Modules\Monster\Infrastructure\Persistence\Models\Monster;
use App\Modules\Share\Infrastructure\Persistence\Models\ShareItem;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

final class EventActivityController
{
    public function index(): View
    {
        $activities = EventActivity::with(['monster', 'rewardItem', 'bonusRewardItem'])
            ->orderBy('period')
            ->orderBy('sort_order')
            ->get();

        return view('event::admin.activities', ['activities' => $activities]);
    }

    public function create(): View
    {
        return view('event::admin.activity-create', [
            'activity' => null,
        ] + $this->formData(null));
    }

    public function store(Request $request): RedirectResponse
    {
        EventActivity::create($this->validated($request));

        return redirect()->route('admin.event.activities')->with('success', 'Активность создана.');
    }

    public function edit(EventActivity $activity): View
    {
        return view('event::admin.activity-edit', [
            'activity' => $activity,
        ] + $this->formData($activity));
    }

    public function update(Request $request, EventActivity $activity): RedirectResponse
    {
        $activity->update($this->validated($request));

        return redirect()->route('admin.event.activities')->with('success', 'Активность обновлена.');
    }

    public function toggle(EventActivity $activity): RedirectResponse
    {
        $activity->update(['is_active' => ! $activity->is_active]);

        return redirect()->back()->with('success', $activity->is_active ? 'Активность включена.' : 'Активность выключена.');
    }

    public function delete(EventActivity $activity): RedirectResponse
    {
        $activity->delete();

        return redirect()->route('admin.event.activities')->with('success', 'Активность удалена.');
    }

    /** @return array<string, mixed> */
    private function validated(Request $request): array
    {
        $data = $request->validate([
            'period' => ['required', 'string'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'monster_id' => ['nullable', 'integer', 'exists:monsters,id'],
            'required_count' => ['required', 'integer', 'min:1'],
            'reward_share_item_id' => ['required', 'integer', 'exists:share_items,id'],
            'reward_item_amount' => ['required', 'integer', 'min:1'],
            'bonus_reward_type' => ['nullable', 'string'],
            'bonus_reward_amount' => ['nullable', 'integer', 'min:1'],
            'bonus_reward_share_item_id' => ['nullable', 'integer', 'exists:share_items,id'],
            'sort_order' => ['required', 'integer', 'min:0'],
        ]);

        $data['period'] = ActivityPeriod::from($data['period']);
        $data['is_active'] = $request->boolean('is_active');

        $bonusType = $data['bonus_reward_type'] !== null && $data['bonus_reward_type'] !== ''
            ? ActivityBonusRewardType::from($data['bonus_reward_type'])
            : null;

        $data['bonus_reward_type'] = $bonusType;

        if ($bonusType === null) {
            $data['bonus_reward_amount'] = null;
            $data['bonus_reward_share_item_id'] = null;
        } elseif ($bonusType !== ActivityBonusRewardType::ITEM) {
            $data['bonus_reward_share_item_id'] = null;
        }

        return $data;
    }

    /** @return array<string, mixed> */
    private function formData(?EventActivity $activity): array
    {
        // Ajax-select2 не хранит список опций — предвыбранный предмет рендерим сами
        // (old() важнее значения модели: форма могла вернуться с ошибкой валидации)
        $rewardItemId = (int) old('reward_share_item_id', $activity?->reward_share_item_id);
        $bonusItemId = (int) old('bonus_reward_share_item_id', $activity?->bonus_reward_share_item_id);

        return [
            'periods' => ActivityPeriod::cases(),
            'bonusTypes' => ActivityBonusRewardType::cases(),
            'monsters' => Monster::orderBy('name')->get(['id', 'name']),
            'rewardItemSelected' => $rewardItemId > 0 ? ShareItem::find($rewardItemId, ['id', 'name']) : null,
            'bonusItemSelected' => $bonusItemId > 0 ? ShareItem::find($bonusItemId, ['id', 'name']) : null,
        ];
    }
}
