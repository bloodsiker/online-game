<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Modules\MagicSkill\Infrastructure\Persistence\Models\Effect;
use Illuminate\Http\Request;

class EffectController extends Controller
{
    public function list()
    {
        $list = Effect::withCount('magicSkills')->orderByDesc('id')->get();

        return view('admin.effect.list', compact('list'));
    }

    public function create(Request $request): mixed
    {
        if ($request->isMethod('POST')) {
            $effect = new Effect;
            $this->fillEffect($effect, $request);
            $effect->save();

            return redirect()->route('admin.effect.info', $effect->id)
                ->with('success', 'Эффект создан.');
        }

        return view('admin.effect.create');
    }

    public function info(Request $request, Effect $effect): mixed
    {
        if ($request->isMethod('POST')) {
            $this->fillEffect($effect, $request);
            $effect->save();

            return redirect()->back()->with('success', 'Сохранено.');
        }

        $effect->load('magicSkills');

        return view('admin.effect.info', compact('effect'));
    }

    // ─────────────────────────────────────────────────────────────────────────

    private function fillEffect(Effect $effect, Request $request): void
    {
        $effect->name = $request->input('name');
        $effect->slug = $request->input('slug');
        $effect->type = $request->input('type');
        $effect->description = $request->input('description');
        $effect->chance = (int) $request->input('chance', 0);
        $effect->duration = (int) $request->input('duration', 0);
        $effect->is_stackable = (bool) $request->input('is_stackable', false);
        $effect->max_stacks = (int) $request->input('max_stacks', 1);
        $effect->tick_interval = (int) $request->input('tick_interval', 1);
        $effect->value_per_tick = $request->filled('value_per_tick') ? (int) $request->input('value_per_tick') : null;
        $effect->is_dispellable = (bool) $request->input('is_dispellable', true);

        $statModifiers = $request->input('stat_modifiers');
        $effect->stat_modifiers = $statModifiers ? json_decode((string) $statModifiers, true) : null;
    }
}
