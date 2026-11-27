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
    @if($effect->resolvedActiveType()?->isDoT())
        @php
            $dotPercent = $effect->pivot->power_percent !== null ? (float) $effect->pivot->power_percent : null;
            $dotBasis = $effect->pivot->power_basis ?: ($effect->resolvedDamageScalingType() === \App\Modules\Effect\Domain\Enums\EffectDamageScalingType::TARGET_MAX_HP ? 'target_max_hp' : 'hit');
            $dotPercent ??= $dotBasis === 'hit' ? 50.0 : 2.0;
        @endphp
        <div class="skill-effect-description" style="color: #a4150c; line-height: 1.4;">
            {{ __('Урон за всё время:') }} <b>{{ rtrim(rtrim(number_format($dotPercent, 2, '.', ''), '0'), '.') }}%</b>
            {{ $dotBasis === 'hit' ? __('от урона удара') : __('от макс. HP цели') }}
        </div>
    @endif
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
