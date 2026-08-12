<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Modules\Battle\Domain\Enums\BossMechanicType;
use App\Http\Controllers\Controller;
use App\Modules\Monster\Infrastructure\Persistence\Models\BossMechanic;
use App\Modules\Monster\Infrastructure\Persistence\Models\BossPhase;
use App\Modules\Monster\Infrastructure\Persistence\Models\Monster;
use App\Modules\Monster\Infrastructure\Persistence\Models\MonsterSummonPool;
use App\Modules\Share\Infrastructure\Persistence\Models\ShareItem;
use App\Modules\Location\Infrastructure\Persistence\Models\Location;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\View\View;

class MonsterController extends Controller
{
    public function list(Request $request): View
    {
        $filters = [
            'q' => trim((string) $request->query('q', '')),
            'level_from' => $request->query('level_from'),
            'level_to' => $request->query('level_to'),
            'is_boss' => (string) $request->query('is_boss', ''),
        ];

        $listMonsters = Monster::query()
            ->when($filters['q'] !== '', function ($query) use ($filters): void {
                $search = '%'.str_replace(['%', '_'], ['\%', '\_'], $filters['q']).'%';
                $query->where(function ($query) use ($search): void {
                    $query->where('name', 'like', $search)
                        ->orWhere('description', 'like', $search);
                });
            })
            ->when(is_numeric($filters['level_from']), fn ($query) => $query->where('lvl', '>=', (int) $filters['level_from']))
            ->when(is_numeric($filters['level_to']), fn ($query) => $query->where('lvl', '<=', (int) $filters['level_to']))
            ->when(in_array($filters['is_boss'], ['0', '1'], true), fn ($query) => $query->where('is_boss', (int) $filters['is_boss']))
            ->orderByDesc('id')
            ->paginate(50)
            ->withQueryString();

        return view('admin.monster.list', compact('listMonsters', 'filters'));
    }

    public function create(Request $request): mixed
    {
        if ($request->isMethod('POST')) {
            $monster = new Monster;
            $this->fillMonster($monster, $request);
            $monster->save();

            return redirect()->route('admin.monster.info', $monster->id)
                ->with('success', 'Монстр создан.');
        }

        return view('admin.monster.create');
    }

    public function info(Request $request, Monster $monster): mixed
    {
        if ($request->isMethod('POST')) {
            $this->fillMonster($monster, $request);
            $monster->save();

            return redirect()->back()->with('success', 'Сохранено.');
        }

        $monster->load(['items', 'locations.map']);
        if ($monster->is_boss) {
            $monster->load(['phases', 'mechanics', 'summonPool']);
        }

        $mechanicTypes = BossMechanicType::cases();
        $allMonsters = Monster::orderBy('name')->get();

        return view('admin.monster.info', compact('monster', 'mechanicTypes', 'allMonsters'));
    }

    public function infoDrop(Request $request, Monster $monster): RedirectResponse
    {
        $monster->items()->attach($request->input('share_item_id'), [
            'drop_chance' => (float) $request->input('drop_chance', 0),
            'min_count' => (int) $request->input('min_count', 1),
            'max_count' => (int) $request->input('max_count', 1),
        ]);

        return redirect()->back()->with('success', 'Предмет добавлен.');
    }

    public function infoDropUpdate(Request $request, Monster $monster, ShareItem $item): RedirectResponse
    {
        $monster->items()->updateExistingPivot($item->id, [
            'drop_chance' => (float) $request->input('drop_chance', 0),
            'min_count' => (int) $request->input('min_count', 1),
            'max_count' => (int) $request->input('max_count', 1),
        ]);

        return redirect()->back()->with('success', 'Дроп обновлён.');
    }

    public function infoDropDeleteItem(Monster $monster, ShareItem $item): RedirectResponse
    {
        $monster->items()->detach($item->id);

        return redirect()->back()->with('success', 'Предмет удалён.');
    }

    public function infoLocation(Monster $monster): View
    {
        $monster->load(['locations.map']);
        $locations = Location::orderBy('name')->get();

        return view('admin.monster.info_location', compact('monster', 'locations'));
    }

    public function location(Request $request, Monster $monster): RedirectResponse
    {
        $aggression = $request->input('aggression');

        $monster->locations()->attach($request->input('location_id'), [
            'aggression' => $aggression !== '' && $aggression !== null ? (int) $aggression : null,
        ]);

        return redirect()->back()->with('success', 'Локация добавлена.');
    }

    public function updateLocation(Request $request, Monster $monster, Location $location): \Illuminate\Http\JsonResponse
    {
        $aggression = $request->input('aggression');

        $monster->locations()->updateExistingPivot($location->id, [
            'aggression' => $aggression !== '' && $aggression !== null ? (int) $aggression : null,
        ]);

        return response()->json(['status' => 'ok']);
    }

    public function deleteLocation(Monster $monster, Location $location): RedirectResponse
    {
        $monster->locations()->detach($location->id);

        return redirect()->back()->with('success', 'Локация удалена.');
    }

    // ── Boss Phases ───────────────────────────────────────────────────────────

    public function addPhase(Request $request, Monster $monster): RedirectResponse
    {
        BossPhase::create([
            'monster_id' => $monster->id,
            'phase_number' => (int) $request->input('phase_number', 1),
            'hp_threshold' => (int) $request->input('hp_threshold', 50),
            'description' => $request->input('description'),
            'stats_modifiers' => $request->filled('stats_modifiers') ? json_decode($request->input('stats_modifiers'), true) : null,
            'new_skills' => $request->filled('new_skills') ? json_decode($request->input('new_skills'), true) : null,
            'removed_skills' => $request->filled('removed_skills') ? json_decode($request->input('removed_skills'), true) : null,
        ]);

        return redirect()->back()->with('success', 'Фаза добавлена.');
    }

    public function updatePhase(Request $request, Monster $monster, BossPhase $phase): RedirectResponse
    {
        $phase->phase_number = (int) $request->input('phase_number', 1);
        $phase->hp_threshold = (int) $request->input('hp_threshold', 50);
        $phase->description = $request->input('description');
        $phase->stats_modifiers = $request->filled('stats_modifiers') ? json_decode($request->input('stats_modifiers'), true) : null;
        $phase->new_skills = $request->filled('new_skills') ? json_decode($request->input('new_skills'), true) : null;
        $phase->removed_skills = $request->filled('removed_skills') ? json_decode($request->input('removed_skills'), true) : null;
        $phase->save();

        return redirect()->back()->with('success', 'Фаза обновлена.');
    }

    public function deletePhase(Monster $monster, BossPhase $phase): RedirectResponse
    {
        $phase->delete();

        return redirect()->back()->with('success', 'Фаза удалена.');
    }

    // ── Boss Mechanics ────────────────────────────────────────────────────────

    public function addMechanic(Request $request, Monster $monster): RedirectResponse
    {
        BossMechanic::create([
            'monster_id' => $monster->id,
            'mechanic_type' => $request->input('mechanic_type'),
            'trigger_hp_percent' => $request->filled('trigger_hp_percent') ? (int) $request->input('trigger_hp_percent') : null,
            'trigger_turn' => $request->filled('trigger_turn') ? (int) $request->input('trigger_turn') : null,
            'priority' => (int) $request->input('priority', 0),
            'is_active' => (bool) $request->input('is_active', true),
            'config' => $request->filled('config') ? json_decode($request->input('config'), true) : null,
        ]);

        return redirect()->back()->with('success', 'Механика добавлена.');
    }

    public function updateMechanic(Request $request, Monster $monster, BossMechanic $mechanic): RedirectResponse
    {
        $mechanic->mechanic_type = $request->input('mechanic_type');
        $mechanic->trigger_hp_percent = $request->filled('trigger_hp_percent') ? (int) $request->input('trigger_hp_percent') : null;
        $mechanic->trigger_turn = $request->filled('trigger_turn') ? (int) $request->input('trigger_turn') : null;
        $mechanic->priority = (int) $request->input('priority', 0);
        $mechanic->is_active = (bool) $request->input('is_active', true);
        $mechanic->config = $request->filled('config') ? json_decode($request->input('config'), true) : null;
        $mechanic->save();

        return redirect()->back()->with('success', 'Механика обновлена.');
    }

    public function deleteMechanic(Monster $monster, BossMechanic $mechanic): RedirectResponse
    {
        $mechanic->delete();

        return redirect()->back()->with('success', 'Механика удалена.');
    }

    public function toggleMechanic(Monster $monster, BossMechanic $mechanic): RedirectResponse
    {
        $mechanic->is_active = ! $mechanic->is_active;
        $mechanic->save();

        return redirect()->back()->with('success', $mechanic->is_active ? 'Механика включена.' : 'Механика отключена.');
    }

    // ── Пул призыва (кого может призвать этот босс) ─────────────────────────────

    public function addSummonPool(Request $request, Monster $monster): RedirectResponse
    {
        MonsterSummonPool::create([
            'monster_id' => $monster->id,
            'minion_monster_id' => (int) $request->input('minion_monster_id'),
            'weight' => max(1, (int) $request->input('weight', 1)),
        ]);

        return redirect()->back()->with('success', 'Моб добавлен в пул призыва.');
    }

    public function deleteSummonPool(Monster $monster, MonsterSummonPool $summonPool): RedirectResponse
    {
        $summonPool->delete();

        return redirect()->back()->with('success', 'Моб удалён из пула призыва.');
    }

    // ─────────────────────────────────────────────────────────────────────────

    private function fillMonster(Monster $monster, Request $request): void
    {
        $monster->name = $request->input('name');
        $monster->description = $request->input('description');
        $monster->lvl = (int) $request->input('lvl', 1);
        $monster->hp = (int) $request->input('hp', 1);
        $monster->armor = (int) $request->input('armor', 0);
        $monster->dodge = (int) $request->input('dodge', 0);
        $monster->critical = (int) $request->input('critical', 0);
        $monster->min_dmg = (float) $request->input('min_dmg', 0);
        $monster->max_dmg = (float) $request->input('max_dmg', 0);
        $monster->aggression = (int) $request->input('aggression', 0);
        $monster->exp = (int) $request->input('exp', 0);
        $monster->min_money = (int) $request->input('min_money', 0);
        $monster->max_money = (int) $request->input('max_money', 0);
        $monster->is_boss = (bool) $request->input('is_boss', false);
        $monster->respawn_min_minutes = $request->filled('respawn_min_minutes') ? (int) $request->input('respawn_min_minutes') : null;
        $monster->respawn_max_minutes = $request->filled('respawn_max_minutes') ? (int) $request->input('respawn_max_minutes') : null;
        $monster->respawn_at = $request->filled('respawn_at') ? $request->input('respawn_at') : null;

        if ($request->hasFile('image')) {
            $monster->image = $this->storeImage($request->file('image'));
        }
    }

    private function storeImage(UploadedFile $file): string
    {
        return $file->store('monsters', 'public');
    }
}
