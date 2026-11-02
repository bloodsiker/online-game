<div id="top-users1">
    <div class="b-shape b-aside__shape" id="ratings">
        <div class="b-shape__header">
            <div class="b-shape__decor"></div>
            <div class="homepage-heading homepage-heading--reference">{{ __('homepage.top_players') }}</div>
        </div>
        <div class="b-rate"><div class="b-rate__content">
            <table class="b-rate__table" id="rating-users-rating-0" cellspacing="0" cellpadding="0" style="width:100%">
                <thead class="b-rate__thead"><tr>
                    <th class="b-rate__th b-rate__th--place">#</th>
                    <th class="b-rate__th b-rate__th--clanname">{{ __('homepage.player') }}</th>
                </tr></thead>
                <tbody>
                @forelse($topPlayers as $player)
                    @php($clan = $player->user->clanMembership?->clan)
                    <tr class="b-rate__tr {{ $loop->even ? 'b-rate__tr--even' : '' }}">
                        <td class="b-rate__td b-rate__td--valor">{{ $loop->iteration }}</td>
                        <td class="b-rate__td b-rate__td--clanname">
                            @if($clan && $clan->icon)
                                <a href="{{ route('clan.public', $clan->id) }}" target="_blank" rel="noopener" title="{{ $clan->name }}"><img src="{{ Storage::disk('public')->url($clan->icon) }}" width="13" height="13" style="vertical-align:middle" alt="{{ $clan->name }}"></a>
                            @endif
                            <a href="{{ route('info.user', $player->user_id) }}" target="_blank" rel="noopener"><b>{{ $player->user->name }} [{{ $player->lvl }}]</b></a>
                            <a href="{{ route('info.user', $player->user_id) }}" target="_blank" rel="noopener" title="{{ __('homepage.player_info') }}"><img src="{{ asset('main/images/player_info.gif') }}" width="10" height="10" alt="{{ __('homepage.player_info') }}"></a>
                        </td>
                    </tr>
                @empty
                    <tr><td class="b-rate__td" colspan="2">{{ __('homepage.no_players') }}</td></tr>
                @endforelse
                </tbody>
            </table>
        </div></div>
    </div>
</div>
<div id="top-users2">
    <div class="b-shape b-aside__shape" id="ratings2">
        <div class="b-shape__header">
            <div class="b-shape__decor"></div>
            <div class="homepage-heading homepage-heading--reference">{{ __('homepage.top_clans') }}</div>
        </div>
        <div class="b-rate"><div class="b-rate__content">
            <table class="b-rate__table" id="rating2-users-rating-0" cellspacing="0" cellpadding="0" style="width:100%">
                <thead class="b-rate__thead"><tr>
                    <th class="b-rate__th b-rate__th--place">#</th>
                    <th class="b-rate__th b-rate__th--clanname">{{ __('homepage.clan') }}</th>
                    <th class="b-rate__th b-rate__th--points">{{ __('homepage.level') }}</th>
                </tr></thead>
                <tbody>
                @forelse($topClans as $clan)
                    <tr class="b-rate__tr {{ $loop->even ? 'b-rate__tr--even' : '' }}">
                        <td class="b-rate__td b-rate__td--valor">{{ $loop->iteration }}</td>
                        <td class="b-rate__td b-rate__td--clanname">
                            <a href="{{ route('clan.public', $clan->id) }}" target="_blank" rel="noopener">
                                @if($clan->icon)<img src="{{ Storage::disk('public')->url($clan->icon) }}" width="13" height="13" style="vertical-align:middle" alt="">@endif
                                {{ $clan->name }}
                            </a>
                        </td>
                        <td class="b-rate__td b-rate__td--points">{{ $clan->lvl }}</td>
                    </tr>
                @empty
                    <tr><td class="b-rate__td" colspan="3">{{ __('homepage.no_clans') }}</td></tr>
                @endforelse
                </tbody>
            </table>
        </div></div>
    </div>
</div>
