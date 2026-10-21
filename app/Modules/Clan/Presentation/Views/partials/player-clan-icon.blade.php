@php($playerClan = $playerUser->clanMembership?->clan)
@if($playerClan?->icon)
    <a href="{{ route('clan.public', ['clan' => $playerClan->id]) }}" class="icon-link" title="{{ $playerClan->name }}"
       onclick="window.open(this.href, '', 'width=730,height=700,location=yes,menubar=no,resizable=yes,scrollbars=yes,status=no,toolbar=no'); return false;"><img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($playerClan->icon) }}" alt="{{ $playerClan->name }}" width="13" height="13" border="0" style="vertical-align:middle"></a>
@endif
