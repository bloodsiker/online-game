<span class="{{ $rel->isOnline ? '' : 'user_offline' }}">
    <img src="{{ asset('img/icon/users-arrow.gif') }}" class="prv-btn" title="Написать в приват" onclick="sendPrivate('{{ addslashes($rel->userName) }}')" alt="">
    <a href="{{ route('info.user', ['id' => $rel->userId]) }}" target="_blank" class="pnick {{ $rel->isOnline ? '' : 'user_offline' }}" title="Информация о персонаже"><b>{{ $rel->userName }} [{{ $rel->level }}]</b></a>
    @if($rel->clanName)
        @if($rel->clanIconUrl)
            <img class="clan-icon" src="{{ $rel->clanIconUrl }}" title="{{ $rel->clanName }}" alt="{{ $rel->clanName }}" style="{{ $rel->isOnline ? '' : 'opacity:.6' }}">
        @else
            <span class="clan-tag">[{{ $rel->clanName }}]</span>
        @endif
    @endif
</span>
