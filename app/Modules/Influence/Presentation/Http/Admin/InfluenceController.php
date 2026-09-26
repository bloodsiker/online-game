<?php

declare(strict_types=1);

namespace App\Modules\Influence\Presentation\Http\Admin;

use App\Http\Controllers\Controller;
use App\Modules\Influence\Domain\Enums\InfluenceBonusType;
use App\Modules\Influence\Domain\Enums\InfluenceRewardType;
use App\Modules\Influence\Infrastructure\Persistence\Models\InfluenceMedal;
use App\Modules\Influence\Infrastructure\Persistence\Models\InfluenceMedalStat;
use App\Modules\Influence\Infrastructure\Persistence\Models\InfluenceShopItem;
use App\Modules\Influence\Infrastructure\Persistence\Models\InfluenceShopSection;
use App\Modules\Influence\Infrastructure\Persistence\Models\MapInfluenceLevel;
use App\Modules\Influence\Infrastructure\Persistence\Models\MapInfluenceLevelBonus;
use App\Modules\Influence\Infrastructure\Persistence\Models\MapInfluenceLevelReward;
use App\Modules\Influence\Infrastructure\Persistence\Models\MapInfluenceTransaction;
use App\Modules\Location\Infrastructure\Persistence\Models\LocationGate;
use App\Modules\Location\Infrastructure\Persistence\Models\Map as GameMap;
use App\Modules\Npc\Infrastructure\Persistence\Models\Npc;
use App\Modules\Quest\Infrastructure\Persistence\Models\Quest;
use App\Modules\Share\Domain\Enums\ShareItemStatType;
use App\Modules\Structure\Infrastructure\Persistence\Models\Structure;
use App\Services\Media\AdminImageStorage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

final class InfluenceController extends Controller
{
    public function __construct(private readonly AdminImageStorage $imageStorage) {}

    public function index(): View
    {
        $maps = GameMap::query()
            ->withCount(['locations'])
            ->withCount(['influenceLevels'])
            ->orderBy('name')
            ->get();

        return view('influence::admin.index', compact('maps'));
    }

    public function show(GameMap $map): View
    {
        $map->load(['influenceLevels.medal.stats', 'influenceLevels.bonuses', 'influenceLevels.rewards.item']);
        $medals = InfluenceMedal::query()->where('map_id', $map->id)->with('stats')->orderBy('name')->get();
        $requirements = collect([
            'npc' => DB::table('npc_influence_requirements')->where('map_id', $map->id)->get(),
            'quest' => DB::table('quest_influence_requirements')->where('map_id', $map->id)->get(),
            'gate' => DB::table('location_gate_influence_requirements')->where('map_id', $map->id)->get(),
        ]);
        $shopSections = InfluenceShopSection::query()
            ->where('map_id', $map->id)
            ->with(['structure', 'items.item'])
            ->orderBy('sort_order')
            ->get();
        $transactions = MapInfluenceTransaction::query()
            ->where('map_id', $map->id)
            ->with(['user' => static fn ($query) => $query->without('player')])
            ->latest('id')
            ->limit(50)
            ->get();

        return view('influence::admin.show', [
            'map' => $map,
            'medals' => $medals,
            'bonusTypes' => InfluenceBonusType::cases(),
            'rewardTypes' => InfluenceRewardType::cases(),
            'statTypes' => $this->supportedMedalStatTypes(),
            'requirements' => $requirements,
            'npcs' => Npc::query()->orderBy('name')->get(['id', 'name']),
            'quests' => Quest::query()->orderBy('title')->get(['id', 'title']),
            'gates' => LocationGate::query()->with(['fromLocation', 'toLocation'])->orderBy('id')->get(),
            'structures' => Structure::query()->where('type', Structure::TYPE_SHOP)->orderBy('name')->get(['id', 'name']),
            'shopSections' => $shopSections,
            'transactions' => $transactions,
        ]);
    }

    public function storeLevel(Request $request, GameMap $map): RedirectResponse
    {
        $data = $request->validate([
            'level' => ['required', 'integer', 'min:1', 'max:65535'],
            'name' => ['required', 'string', 'max:255'],
            'required_influence' => ['required', 'integer', 'min:0'],
            'influence_medal_id' => ['nullable', 'integer', 'exists:influence_medals,id'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
        ]);
        $data['map_id'] = $map->id;
        $data['is_active'] = $request->boolean('is_active', true);
        MapInfluenceLevel::query()->create($data);

        return back()->with('success', 'Уровень влияния добавлен.');
    }

    public function updateLevel(Request $request, GameMap $map, MapInfluenceLevel $level): RedirectResponse
    {
        abort_unless((int) $level->map_id === (int) $map->id, 404);
        $level->update($request->validate([
            'level' => ['required', 'integer', 'min:1', 'max:65535'],
            'name' => ['required', 'string', 'max:255'],
            'required_influence' => ['required', 'integer', 'min:0'],
            'influence_medal_id' => ['nullable', 'integer', 'exists:influence_medals,id'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
        ]) + ['is_active' => $request->boolean('is_active')]);

        return back()->with('success', 'Уровень обновлён.');
    }

    public function destroyLevel(GameMap $map, MapInfluenceLevel $level): RedirectResponse
    {
        abort_unless((int) $level->map_id === (int) $map->id, 404);
        $level->delete();

        return back()->with('success', 'Уровень удалён.');
    }

    public function storeMedal(Request $request, GameMap $map): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'rating_points' => ['nullable', 'integer', 'min:0'],
            'icon' => ['nullable', 'image', 'max:4096'],
        ]);
        $data['map_id'] = $map->id;
        if ($request->hasFile('icon')) {
            $data['icon'] = $this->imageStorage->storeOnPublicDisk($request->file('icon'), 'influence/medals');
        }
        InfluenceMedal::query()->create($data);

        return back()->with('success', 'Медаль создана.');
    }

    public function destroyMedal(GameMap $map, InfluenceMedal $medal): RedirectResponse
    {
        abort_unless((int) $medal->map_id === (int) $map->id, 404);
        $medal->delete();

        return back()->with('success', 'Медаль удалена.');
    }

    public function updateMedal(Request $request, GameMap $map, InfluenceMedal $medal): RedirectResponse
    {
        abort_unless((int) $medal->map_id === (int) $map->id, 404);
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'rating_points' => ['required', 'integer', 'min:0'],
            'icon' => ['nullable', 'image', 'max:4096'],
        ]);
        unset($data['icon']);
        if ($request->hasFile('icon')) {
            $data['icon'] = $this->imageStorage->storeOnPublicDisk($request->file('icon'), 'influence/medals');
        }
        $medal->update($data);

        return back()->with('success', 'Медаль обновлена.');
    }

    public function storeMedalStat(Request $request, GameMap $map, InfluenceMedal $medal): RedirectResponse
    {
        abort_unless((int) $medal->map_id === (int) $map->id, 404);
        $data = $request->validate([
            'stat_type' => ['required', Rule::in(array_map(
                static fn (ShareItemStatType $type): string => $type->value,
                $this->supportedMedalStatTypes(),
            ))],
            'value' => ['required', 'numeric'],
            'is_percent' => ['nullable', 'boolean'],
        ]);
        InfluenceMedalStat::query()->updateOrCreate([
            'influence_medal_id' => $medal->id,
            'stat_type' => $data['stat_type'],
            'is_percent' => $request->boolean('is_percent'),
        ], ['value' => $data['value']]);

        return back()->with('success', 'Характеристика медали сохранена.');
    }

    public function destroyMedalStat(GameMap $map, InfluenceMedal $medal, InfluenceMedalStat $stat): RedirectResponse
    {
        abort_unless((int) $medal->map_id === (int) $map->id && (int) $stat->influence_medal_id === (int) $medal->id, 404);
        $stat->delete();

        return back()->with('success', 'Характеристика удалена.');
    }

    public function storeBonus(Request $request, GameMap $map, MapInfluenceLevel $level): RedirectResponse
    {
        abort_unless((int) $level->map_id === (int) $map->id, 404);
        $data = $request->validate(['bonus_type' => ['required', Rule::enum(InfluenceBonusType::class)], 'value' => ['required', 'numeric', 'min:0', 'max:100']]);
        MapInfluenceLevelBonus::query()->updateOrCreate(
            ['map_influence_level_id' => $level->id, 'bonus_type' => $data['bonus_type']],
            ['value' => $data['value']],
        );

        return back()->with('success', 'Бонус уровня сохранён.');
    }

    public function destroyBonus(GameMap $map, MapInfluenceLevel $level, MapInfluenceLevelBonus $bonus): RedirectResponse
    {
        abort_unless((int) $level->map_id === (int) $map->id && (int) $bonus->map_influence_level_id === (int) $level->id, 404);
        $bonus->delete();

        return back()->with('success', 'Бонус удалён.');
    }

    public function storeReward(Request $request, GameMap $map, MapInfluenceLevel $level): RedirectResponse
    {
        abort_unless((int) $level->map_id === (int) $map->id, 404);
        $data = $request->validate([
            'reward_type' => ['required', Rule::enum(InfluenceRewardType::class)],
            'share_item_id' => [Rule::requiredIf($request->input('reward_type') === InfluenceRewardType::ITEM->value), 'nullable', 'integer', 'exists:share_items,id'],
            'amount' => ['required', 'integer', 'min:1'],
        ]);
        MapInfluenceLevelReward::query()->create(['map_influence_level_id' => $level->id, ...$data]);

        return back()->with('success', 'Награда добавлена.');
    }

    public function destroyReward(GameMap $map, MapInfluenceLevel $level, MapInfluenceLevelReward $reward): RedirectResponse
    {
        abort_unless((int) $level->map_id === (int) $map->id && (int) $reward->map_influence_level_id === (int) $level->id, 404);
        $reward->delete();

        return back()->with('success', 'Награда удалена.');
    }

    public function storeRequirement(Request $request, GameMap $map): RedirectResponse
    {
        $data = $request->validate([
            'target_type' => ['required', 'in:npc,quest,gate'],
            'target_id' => ['required', 'integer', 'min:1'],
            'required_influence' => ['required', 'integer', 'min:0'],
        ]);
        [$table, $column] = match ($data['target_type']) {
            'npc' => ['npc_influence_requirements', 'npc_id'],
            'quest' => ['quest_influence_requirements', 'quest_id'],
            'gate' => ['location_gate_influence_requirements', 'location_gate_id'],
        };
        DB::table($table)->updateOrInsert(
            [$column => $data['target_id'], 'map_id' => $map->id],
            ['required_influence' => $data['required_influence'], 'created_at' => now(), 'updated_at' => now()],
        );

        return back()->with('success', 'Требование влияния сохранено.');
    }

    public function destroyRequirement(GameMap $map, string $type, int $requirement): RedirectResponse
    {
        $table = match ($type) {
            'npc' => 'npc_influence_requirements',
            'quest' => 'quest_influence_requirements',
            'gate' => 'location_gate_influence_requirements',
            default => abort(404),
        };
        DB::table($table)->where('id', $requirement)->where('map_id', $map->id)->delete();

        return back()->with('success', 'Требование удалено.');
    }

    public function storeShopSection(Request $request, GameMap $map): RedirectResponse
    {
        $data = $request->validate([
            'structure_id' => ['required', 'integer', 'exists:structures,id'],
            'name' => ['required', 'string', 'max:255'],
            'required_influence' => ['required', 'integer', 'min:0'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ]);
        InfluenceShopSection::query()->create([
            ...$data,
            'map_id' => $map->id,
            'is_active' => true,
        ]);

        return back()->with('success', 'Раздел магазина добавлен.');
    }

    public function destroyShopSection(GameMap $map, InfluenceShopSection $section): RedirectResponse
    {
        abort_unless((int) $section->map_id === (int) $map->id, 404);
        $section->delete();

        return back()->with('success', 'Раздел магазина удалён.');
    }

    public function storeShopItem(Request $request, GameMap $map, InfluenceShopSection $section): RedirectResponse
    {
        abort_unless((int) $section->map_id === (int) $map->id, 404);
        $data = $request->validate([
            'share_item_id' => ['required', 'integer', 'exists:share_items,id'],
            'required_influence' => ['required', 'integer', 'min:0'],
            'price' => ['required', 'integer', 'min:0'],
            'diamond' => ['required', 'integer', 'min:0'],
            'purchase_limit' => ['nullable', 'integer', 'min:1'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ]);
        $section->items()->create([...$data, 'is_active' => true]);

        return back()->with('success', 'Товар добавлен.');
    }

    public function destroyShopItem(GameMap $map, InfluenceShopSection $section, InfluenceShopItem $item): RedirectResponse
    {
        abort_unless((int) $section->map_id === (int) $map->id && (int) $item->influence_shop_section_id === (int) $section->id, 404);
        $item->delete();

        return back()->with('success', 'Товар удалён.');
    }

    /** @return list<ShareItemStatType> */
    private function supportedMedalStatTypes(): array
    {
        return array_values(array_filter(
            ShareItemStatType::cases(),
            static fn (ShareItemStatType $type): bool => ! in_array($type, [
                ShareItemStatType::ATTACK_MIN,
                ShareItemStatType::ATTACK_MAX,
                ShareItemStatType::BAG_SLOT,
                ShareItemStatType::BELT_SLOT,
            ], true),
        ));
    }
}
