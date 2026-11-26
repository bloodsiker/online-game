<?php

declare(strict_types=1);

namespace App\Modules\ItemSpawn\Presentation\Http\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

final class ItemSpawnAdminController extends Controller
{
    public function index(): View
    {
        $maps = DB::table('maps')->orderBy('id')->get(['id', 'name'])->mapWithKeys(static function (object $map): array {
            $names = json_decode((string) $map->name, true);

            return [(int) $map->id => is_array($names) ? (string) ($names['ru'] ?? reset($names)) : (string) $map->name];
        });
        $rules = DB::table('map_item_spawn_rules')->orderBy('id')->get()->map(function (object $rule): object {
            $rule->items = DB::table('map_item_spawn_rule_items as r')
                ->join('share_items as s', 's.id', '=', 'r.share_item_id')
                ->where('r.rule_id', $rule->id)
                ->orderByDesc('r.weight')
                ->get(['r.id', 'r.share_item_id', 'r.weight', 'r.min_count', 'r.max_count', 's.name']);
            foreach ($rule->items as $item) {
                $names = json_decode((string) $item->name, true);
                $item->title = is_array($names) ? (string) ($names['ru'] ?? reset($names)) : (string) $item->name;
            }

            return $rule;
        });

        return view('item-spawn::admin', ['rules' => $rules, 'maps' => $maps]);
    }

    public function saveRule(Request $request, int $rule): RedirectResponse
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:120'],
            'per_tick' => ['required', 'integer', 'min:0', 'max:500'],
            'interval_minutes' => ['required', 'integer', 'min:1', 'max:1440'],
            'map_max' => ['required', 'integer', 'min:0', 'max:5000'],
            'ttl_minutes' => ['required', 'integer', 'min:1', 'max:43200'],
            'rain_multiplier' => ['required', 'numeric', 'min:1', 'max:20'],
        ]);
        DB::table('map_item_spawn_rules')->where('id', $rule)->update($data + [
            'is_active' => $request->boolean('is_active'),
            'updated_at' => now(),
        ]);

        return back()->with('success', 'Правило сохранено.');
    }

    public function saveItem(Request $request, int $rule, int $item): RedirectResponse
    {
        $data = $request->validate([
            'weight' => ['required', 'integer', 'min:0', 'max:1000'],
            'min_count' => ['required', 'integer', 'min:1', 'max:100'],
            'max_count' => ['required', 'integer', 'min:1', 'max:100', 'gte:min_count'],
        ]);
        DB::table('map_item_spawn_rule_items')->where('rule_id', $rule)->where('id', $item)->update($data + ['updated_at' => now()]);

        return back()->with('success', 'Предмет правила сохранён.');
    }

    public function addItem(Request $request, int $rule): RedirectResponse
    {
        $data = $request->validate([
            'share_item_id' => ['required', 'integer', 'exists:share_items,id'],
            'weight' => ['required', 'integer', 'min:1', 'max:1000'],
        ]);
        DB::table('map_item_spawn_rule_items')->updateOrInsert(
            ['rule_id' => $rule, 'share_item_id' => $data['share_item_id']],
            ['weight' => $data['weight'], 'min_count' => 1, 'max_count' => 1, 'created_at' => now(), 'updated_at' => now()],
        );

        return back()->with('success', 'Предмет добавлен в правило.');
    }

    public function deleteItem(int $rule, int $item): RedirectResponse
    {
        DB::table('map_item_spawn_rule_items')->where('rule_id', $rule)->where('id', $item)->delete();

        return back()->with('success', 'Предмет убран из правила.');
    }
}
