@if($effect->stat_modifiers)
    <div class="skill-effect-bonuses" style="margin: 3px 0 6px; line-height: 1.5; color: #386222;">
        @foreach($effect->stat_modifiers as $modifier)
            @continue(!is_array($modifier))
            @php
                $type = $modifier['type'] ?? '';
                $label = \App\Modules\Share\Domain\Enums\PassiveBonusStatType::tryFrom($type)?->label()
                    ?? \App\Modules\Share\Domain\Enums\ShareItemStatType::tryFrom($type)?->label()
                    ?? $type;
                $value = (float) ($modifier['value'] ?? 0);
            @endphp
            <div>{{ $label }}: <b>{{ $value > 0 ? '+' : '' }}{{ $value }}{{ !empty($modifier['is_percent']) ? '%' : '' }}</b></div>
        @endforeach
    </div>
@endif
