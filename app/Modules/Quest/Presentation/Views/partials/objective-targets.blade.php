@if(!empty($info))
    @if(!empty($info['monsters']))
        <div class="quest-target-info">
            {{ __('Мобы:') }}
            @foreach($info['monsters'] as $m)
                <a href="{{ $m['url'] }}" onclick="window.open(this.href,'','width=730,height=550,location=no,menubar=no,resizable=yes,scrollbars=yes,status=no,toolbar=no');return false;">{{ $m['name'] }}</a> {{ $m['lvl'] }}{{ $loop->last ? '' : ',' }}
            @endforeach
        </div>
    @endif
    @if(!empty($info['maps']))
        <div class="quest-target-info">
            {{ __('Где искать:') }}
            @foreach($info['maps'] as $map)
                <a href="{{ $map['url'] }}" target="_blank">{{ $map['name'] }}</a>{{ $loop->last ? '' : ',' }}
            @endforeach
        </div>
    @endif
    @if(!empty($info['npc']))
        <div class="quest-target-info">
            {{ __('NPC:') }}
            <a href="{{ $info['npc']['url'] }}" onclick="window.open(this.href,'','width=730,height=550,location=no,menubar=no,resizable=yes,scrollbars=yes,status=no,toolbar=no');return false;">{{ $info['npc']['name'] }}</a>
            @if($info['npc']['map'])
                (<a href="{{ $info['npc']['map']['url'] }}" target="_blank">{{ $info['npc']['map']['name'] }}</a>)
            @endif
        </div>
    @endif
@endif
