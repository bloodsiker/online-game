@php
    preg_match('/\s+([IVXLCDM]+|\d+)$/u', $skill->name, $rank);
@endphp
<a href="{{ route('magic_skill.info', ['skill' => $skill->id]) }}"
   data-library-info-popup
   data-id="library_magic_{{ $skill->id }}"
   title="{{ $skill->name }}"
   onmouseover="showItemInfo(this,event,2)" onmouseout="showItemInfo(this,event,0)"
   style="display: flex; flex-direction: column; align-items: center; gap: 3px; width: 74px; text-decoration: none; color: #7e1d0a;">
    <span style="position: relative; display: block; width: 72px; height: 71px; background: url('{{ asset('main/images/user-reward-frame.png') }}') no-repeat;">
        <img src="{{ $skill->image ?: asset('img/bg/empty_slot.gif') }}" alt="{{ $skill->name }}"
             style="position: absolute; top: 5px; left: 6px; width: 60px; height: 60px; object-fit: contain;">
    </span>
    @if(isset($rank[1]))<b>{{ $rank[1] }}</b>@endif
</a>
