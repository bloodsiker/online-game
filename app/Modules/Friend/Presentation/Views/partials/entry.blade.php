<span class="{{ $rel->isOnline ? '' : 'user_offline' }}">
    <img src="{{ asset('img/icon/users-arrow.gif') }}" class="prv-btn" title="{{ __('Написать в приват') }}" onclick="sendPrivate('{{ addslashes($rel->userName) }}')" alt="">
    @if($rel->clanName)
        &nbsp;
        @if($rel->clanIconUrl)
            <a href="{{ route('clan.public', ['clan' => $rel->clanId]) }}" target="_blank" title="{{ $rel->clanName }}">
                <img class="clan-icon" src="{{ $rel->clanIconUrl }}" title="{{ $rel->clanName }}" alt="{{ $rel->clanName }}" style="{{ $rel->isOnline ? '' : 'opacity:.6' }}">
            </a>
        @else
            <a class="clan-tag" href="{{ route('clan.public', ['clan' => $rel->clanId]) }}" target="_blank" title="{{ $rel->clanName }}">[{{ $rel->clanName }}]</a>
        @endif
    @endif
    &nbsp;<a href="{{ route('info.user', ['id' => $rel->userId]) }}" target="_blank" class="pnick {{ $rel->isOnline ? '' : 'user_offline' }}" title="{{ __('Информация о персонаже') }}"><b>{{ $rel->userName }} [{{ $rel->level }}]</b></a>
    &nbsp;<a href="{{ route('info.user', ['id' => $rel->userId]) }}" title="{{ __('Информация о персонаже') }}" onclick="window.open(this.href, '', 'width=930,height=700,location=no,menubar=no,resizable=yes,scrollbars=yes,status=no,toolbar=no'); return false;"><img src="{{ asset('main/images/player_info.gif') }}" width="10" height="10" align="absmiddle" alt="{{ __('Информация о персонаже') }}"></a>
</span>
