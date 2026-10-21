<div style="margin-top: 12px;">
    <span class="section-title">{{ __('Выданные награды') }}</span>
    @forelse($rewardReceipts->groupBy('clan_boss_run_stage_id') as $receipts)
        <p class="b" style="margin: 8px 0 4px;">{{ __('Этап') }} {{ $receipts->first()->stageState?->stage?->number ?? '—' }} — {{ __('Босс') }}: {{ $receipts->first()->bossName() }}</p>
        <table class="history-tbl" cellspacing="0" cellpadding="0">
            <thead><tr><th>{{ __('Получатель') }}</th><th>{{ __('Полученные награды') }}</th><th>{{ __('Основание') }}</th><th>{{ __('Выдано') }}</th></tr></thead>
            <tbody>
            @foreach($receipts as $receipt)
                <tr class="{{ $receipt->user_id === auth()->id() ? 'me-row' : ($loop->even ? 'bg_l' : '') }}">
                    <td>
                        @if($receipt->recipient_type === 'player' && $receipt->user !== null)
                            <span class="player-row" style="display:inline-flex;align-items:center;gap:4px;white-space:nowrap">
                                <a href="#" class="icon-link" title="{{ __('Приватное сообщение') }}" onclick="clanBossInsertPrivate({{ \Illuminate\Support\Js::from($receipt->user->name) }}); return false;"><img src="{{ asset('img/icon/users-arrow.gif') }}" width="12" height="10" border="0" alt="{{ __('Приватное сообщение') }}"></a>
                                @include('clan::partials.player-clan-icon', ['playerUser' => $receipt->user])
                                <a href="{{ route('info.user', $receipt->user_id) }}" onclick="clanBossOpenWindow(this.href, 730, 800); return false;"><b>{{ $receipt->recipient_name }}&nbsp;[{{ $receipt->user->player?->lvl ?? '?' }}]</b></a>
                                <a href="{{ route('info.user', $receipt->user_id) }}" class="icon-link" title="{{ __('Информация о персонаже') }}" onclick="clanBossOpenWindow(this.href, 730, 800); return false;"><img src="{{ asset('main/images/player_info.gif') }}" width="10" height="10" border="0" alt="{{ __('Информация о персонаже') }}"></a>
                            </span>
                        @elseif($receipt->recipient_type === 'clan')
                            <b>{{ __('Клан') }} «{{ $receipt->recipient_name }}»</b>
                        @else
                            <b>{{ $receipt->recipient_name }}</b>
                        @endif
                    </td>
                    <td>
                        @forelse($receipt->rewards as $entry)
                            <div>
                                @switch($entry['type'])
                                    @case('gold') {{ __(':amount монет', ['amount' => format_money($entry['amount'])]) }} @break
                                    @case('diamond') {{ __(':amount бриллиантов', ['amount' => format_money($entry['amount'])]) }} @break
                                    @case('experience') {{ __(':amount опыта', ['amount' => format_money($entry['amount'])]) }} @break
                                    @case('clan_experience') {{ __(':amount опыта клана', ['amount' => format_money($entry['amount'], 2)]) }} @break
                                    @case('item')
                                        <a href="{{ route('items.info.share', ['id' => $entry['item_id']]) }}"
                                           style="color: {{ \App\Modules\Share\Domain\Enums\ItemRarity::tryFrom($entry['rarity'] ?? '')?->color() ?? '#666' }}"
                                           onclick="return clanBossOpenWindow(this.href, 730, 700)">{{ ($entry['item_name'][app()->getLocale()] ?? '') ?: ($entry['item_name']['ru'] ?? __('Предмет удалён')) }}</a>
                                        ×{{ $entry['amount'] }}
                                        @if($receipt->recipient_type === 'clan') <small>({{ __('Клановое хранилище') }})</small> @endif
                                        @break
                                @endswitch
                            </div>
                        @empty
                            {{ __('Награды не начислялись.') }}
                        @endforelse
                    </td>
                    <td>{{ $receipt->reason === 'boss_killed' ? __('Босс повержен') : __('Поход завершён по времени') }}</td>
                    <td nowrap>{{ $receipt->created_at->format('d.m.Y H:i') }}</td>
                </tr>
            @endforeach
            </tbody>
        </table>
    @empty
        <div class="empty-state">{{ ($rewardsFinal ?? false) ? __('Подробный учёт наград для этого похода не вёлся.') : __('Награды появятся после убийства босса или завершения похода.') }}</div>
    @endforelse
</div>
