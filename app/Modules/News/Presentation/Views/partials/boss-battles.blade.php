<div class="b-aside__discuss homepage-boss-battles">
    <div class="b-shape__header">
        <div class="b-shape__decor"></div>
        <div class="homepage-heading homepage-heading--reference">{{ __('homepage.boss_battles') }}</div>
    </div>
    <div class="b-shape__content">
        <div id="hot-fights-box">
            @forelse($homepageBossBattles ?? [] as $battle)
                @php
                    $bosses = $battle->detailsWithMonsters->pluck('locationMonster.monster')->filter()->unique('id');
                    $firstBoss = $bosses->first();
                    $names = $bosses->map(fn ($boss) => $boss->name.' ['.$boss->lvl.']')->implode(', ');
                @endphp
                <a class="b-discuss__element homepage-boss-battles__battle" href="{{ route('fight.log', ['id' => $battle->id]) }}" target="_blank" rel="noopener noreferrer">
                    <span class="b-discuss__title homepage-boss-battles__title">
                        @if($firstBoss?->image)<img src="{{ asset($firstBoss->image) }}" width="24" height="24" alt="" loading="lazy">@endif
                        <span>{{ $names ?: __('homepage.boss_battle_fallback') }}</span>
                    </span>
                    <span class="b-discuss__desc">
                        <time datetime="{{ $battle->created_at?->toAtomString() }}">{{ $battle->created_at?->format('d.m.Y H:i') }}</time>
                        · <span class="{{ $battle->status->isActive() ? 'homepage-boss-battles__active' : '' }}">{{ $battle->status->isActive() ? __('homepage.boss_battle_active') : __('homepage.boss_battle_finished') }}</span>
                    </span>
                </a>
            @empty
                <p class="homepage-boss-battles__empty">{{ __('homepage.no_boss_battles') }}</p>
            @endforelse
        </div>
    </div>
</div>
