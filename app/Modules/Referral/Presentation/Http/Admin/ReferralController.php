<?php

declare(strict_types=1);

namespace App\Modules\Referral\Presentation\Http\Admin;

use App\Modules\Referral\Domain\Enums\ReferralRewardType;
use App\Modules\Referral\Infrastructure\Persistence\Models\Referral;
use App\Modules\Referral\Infrastructure\Persistence\Models\ReferralRewardStage;
use App\Modules\Share\Infrastructure\Persistence\Models\ShareItem;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

final class ReferralController
{
    public function stages(): View
    {
        $stages = ReferralRewardStage::with('rewardItem')->orderBy('level_threshold')->get();
        $rewardTypes = ReferralRewardType::cases();
        $items = ShareItem::orderBy('name')->get(['id', 'name']);

        return view('referral::admin.stages', compact('stages', 'rewardTypes', 'items'));
    }

    public function storeStage(Request $request): RedirectResponse
    {
        $type = ReferralRewardType::from($request->input('reward_type'));

        ReferralRewardStage::create([
            'level_threshold' => (int) $request->input('level_threshold'),
            'reward_type' => $type,
            'reward_item_id' => $type === ReferralRewardType::ITEM ? (int) $request->input('reward_item_id') : null,
            'reward_value' => (int) $request->input('reward_value'),
            'sort' => (int) $request->input('sort', 0),
            'is_active' => true,
        ]);

        return redirect()->back()->with('success', 'Этап добавлен.');
    }

    public function deleteStage(ReferralRewardStage $stage): RedirectResponse
    {
        $stage->delete();

        return redirect()->back()->with('success', 'Этап удалён.');
    }

    public function stats(): View
    {
        $referrals = Referral::with(['referrer', 'referred.player', 'claims.stage'])
            ->latest()
            ->paginate(50);

        return view('referral::admin.stats', compact('referrals'));
    }
}
