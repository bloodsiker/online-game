@foreach($skill->skillEffects as $effect)
    <div class="skill-effect-row">
        <span class="skill-effect-name">{{ $effect->name }}</span>
        @if($effect->pivot->duration_seconds > 0)
            <span class="skill-effect-duration">{{ format_cooldown($effect->pivot->duration_seconds) }}</span>
        @endif
        @if($effect->pivot->chance < 100)
            <span class="skill-effect-chance">{{ $effect->pivot->chance }}%</span>
        @endif
    </div>
    @if($effect->resolvedActiveType()?->isHoT()
        && $effect->resolvedDamageScalingType() === \App\Modules\Effect\Domain\Enums\EffectDamageScalingType::TARGET_MAX_HP)
        <div class="skill-effect-description" style="color: #386222; line-height: 1.4;">
            {{ __('Лечение:') }} <b>{{ $effect->value_per_tick }}% HP</b> / {{ format_cooldown($effect->pivot->duration_seconds) }}
        </div>
    @elseif($effect->description && empty($effect->stat_modifiers))
        <div class="skill-effect-description" style="color: #5d4530; line-height: 1.4;">{{ $effect->description }}</div>
    @endif
    @include('magic-skill::partials.effect-bonuses', ['effect' => $effect])
@endforeach
