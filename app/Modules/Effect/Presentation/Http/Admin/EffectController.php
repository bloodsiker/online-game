<?php

declare(strict_types=1);

namespace App\Modules\Effect\Presentation\Http\Admin;

use App\Http\Controllers\Controller;
use App\Modules\Effect\Domain\Enums\ActiveEffectType;
use App\Modules\Effect\Domain\Enums\EffectDamageScalingType;
use App\Modules\Effect\Infrastructure\Persistence\Models\Effect;
use Illuminate\Http\Request;

class EffectController extends Controller
{
    public function list(Request $request)
    {
        $filters = [
            'q' => trim((string) $request->query('q', '')),
            'type' => trim((string) $request->query('type', '')),
            'active_type' => trim((string) $request->query('active_type', '')),
        ];

        $types = Effect::query()->whereNotNull('type')->distinct()->orderBy('type')->pluck('type');
        $activeTypes = ActiveEffectType::cases();
        $list = Effect::query()
            ->withCount(['magicSkills', 'monsters'])
            ->when($filters['q'] !== '', function ($query) use ($filters): void {
                $search = '%'.str_replace(['%', '_'], ['\\%', '\\_'], $filters['q']).'%';
                $query->where(fn ($query) => $query->where('name', 'like', $search)->orWhere('slug', 'like', $search));
            })
            ->when($filters['type'] !== '' && $types->contains($filters['type']), fn ($query) => $query->where('type', $filters['type']))
            ->when(ActiveEffectType::tryFrom($filters['active_type']) !== null, fn ($query) => $query->where('active_type', $filters['active_type']))
            ->orderByDesc('id')
            ->get();

        return view('effect::admin.list', compact('list', 'filters', 'types', 'activeTypes'));
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

        $activeTypes = ActiveEffectType::cases();
        $damageScalingTypes = EffectDamageScalingType::cases();

        return view('effect::admin.create', compact('activeTypes', 'damageScalingTypes'));
    }

    public function info(Request $request, Effect $effect): mixed
    {
        if ($request->isMethod('POST')) {
            $this->fillEffect($effect, $request);
            $effect->save();

            return redirect()->back()->with('success', 'Сохранено.');
        }

        $effect->load(['magicSkills', 'monsters']);

        $activeTypes = ActiveEffectType::cases();
        $damageScalingTypes = EffectDamageScalingType::cases();

        return view('effect::admin.info', compact('effect', 'activeTypes', 'damageScalingTypes'));
    }

    // ─────────────────────────────────────────────────────────────────────────

    private function fillEffect(Effect $effect, Request $request): void
    {
        $effect->name = $request->input('name');
        $effect->slug = $request->input('slug');
        $effect->type = $request->input('type');
        $effect->active_type = $request->filled('active_type')
            ? ActiveEffectType::from((string) $request->input('active_type'))
            : null;
        $effect->damage_scaling_type = $request->filled('damage_scaling_type')
            ? EffectDamageScalingType::tryFrom((string) $request->input('damage_scaling_type'))
            : null;
        $effect->description = $request->input('description');
        $effect->chance = (int) $request->input('chance', 0);
        $effect->is_stackable = (bool) $request->input('is_stackable', false);
        $effect->max_stacks = (int) $request->input('max_stacks', 1);
        $effect->tick_interval = (int) $request->input('tick_interval', 1);
        $effect->value_per_tick = $request->filled('value_per_tick') ? (int) $request->input('value_per_tick') : null;
        $effect->is_dispellable = (bool) $request->input('is_dispellable', true);

        if ($request->hasFile('image')) {
            $request->validate(['image' => ['image', 'max:4096']]);
            $oldImage = $effect->getRawOriginal('image');
            $effect->image = $request->file('image')->store('effects', 'public');
            $this->deleteStorageImage($oldImage);
        } elseif ($request->boolean('delete_image')) {
            $this->deleteStorageImage($effect->getRawOriginal('image'));
            $effect->image = null;
        }

        $statModifiers = $request->input('stat_modifiers');
        $effect->stat_modifiers = $statModifiers ? json_decode((string) $statModifiers, true) : null;
    }
}
