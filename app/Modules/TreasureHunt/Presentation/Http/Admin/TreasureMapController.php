<?php

declare(strict_types=1);

namespace App\Modules\TreasureHunt\Presentation\Http\Admin;

use App\Http\Controllers\Controller;
use App\Modules\Reputation\Infrastructure\Persistence\Models\Reputation;
use App\Modules\Reputation\Infrastructure\Persistence\Models\ReputationTier;
use App\Modules\Share\Domain\Enums\ShareItemType;
use App\Modules\Share\Infrastructure\Persistence\Models\ShareItem;
use App\Modules\TreasureHunt\Application\Requests\SaveTreasureMapRequest;
use App\Modules\TreasureHunt\Application\Services\ManageTreasureMaps;
use App\Modules\TreasureHunt\Infrastructure\Persistence\Models\TreasureHunt;
use App\Modules\TreasureHunt\Infrastructure\Persistence\Models\TreasureMapConfig;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

final class TreasureMapController extends Controller
{
    public function index()
    {
        return view('treasure::admin.index', [
            'configs' => TreasureMapConfig::with(['item', 'reward', 'reputation'])->orderBy('required_reputation')->paginate(20),
            'tiers' => ReputationTier::with('reputation')->whereIn('reputation_id', TreasureMapConfig::select('reputation_id'))->orderBy('min_points')->get(),
            'bonuses' => DB::table('treasure_reputation_bonuses')->get()->keyBy('reputation_tier_id'),
            'history' => TreasureHunt::whereNotNull('found_at')->orderByDesc('found_at')->paginate(20, ['*'], 'history_page'),
        ]);
    }

    public function form(Request $request, ?int $config = null)
    {
        $record = $config ? TreasureMapConfig::with(['item', 'reward'])->findOrFail($config) : new TreasureMapConfig;
        if ($config === null && $request->filled('share_item_id')) {
            $data = $request->validate(['share_item_id' => ['required', 'integer', Rule::exists('share_items', 'id')->where('type', ShareItemType::TREASURE_MAP->value)]]);
            $existing = TreasureMapConfig::where('share_item_id', $data['share_item_id'])->first();
            if ($existing !== null) {
                return redirect()->route('admin.treasure_maps.edit', $existing->id);
            }
            $record->share_item_id = (int) $data['share_item_id'];
        }

        return view('treasure::admin.form', [
            'config' => $record, 'reputations' => Reputation::orderBy('id')->get(['id', 'name']),
            'selectedItems' => ShareItem::whereIn('id', array_filter([old('share_item_id', $record->share_item_id), old('reward_share_item_id', $record->reward_share_item_id)]))->get()->keyBy('id'),
        ]);
    }

    public function save(SaveTreasureMapRequest $request, ManageTreasureMaps $manager, ?int $config = null)
    {
        $record = $manager->save($request->validated(), $config);

        return redirect()->route('admin.treasure_maps.edit', $record->id)->with('success', 'Карта сохранена.');
    }

    public function bonuses(Request $request, ManageTreasureMaps $manager)
    {
        $data = $request->validate([
            'tiers' => ['required', 'array'],
            'tiers.*' => ['array:search_chance_bonus_percent,search_speed_bonus_percent'],
            'tiers.*.search_chance_bonus_percent' => ['required', 'numeric', 'decimal:0,3', 'min:0', 'max:100'],
            'tiers.*.search_speed_bonus_percent' => ['required', 'numeric', 'decimal:0,2', 'min:0', 'max:90'],
        ]);
        abort_unless(ReputationTier::whereIn('id', array_keys($data['tiers']))->count() === count($data['tiers']), 422);
        $manager->saveBonuses($data['tiers']);

        return redirect()->route('admin.treasure_maps.index')->with('success', 'Бонусы сохранены.');
    }
}
