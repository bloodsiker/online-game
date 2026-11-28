@php
    $btnLeft1 = 'img/bg/btn/btn-left1.gif'; $btnCenter1 = 'img/bg/btn/btn-cent1.gif'; $btnRight1 = 'img/bg/btn/btn-right1.gif';
    $btnLeft2 = 'img/bg/btn/btn-left2.gif'; $btnCenter2 = 'img/bg/btn/btn-cent2.gif'; $btnRight2 = 'img/bg/btn/btn-right2.gif';
    $tabs = [
        'index' => [route('festival.index', ['npc' => $npcId]), __('festival.title'), 190],
        'rules' => [route('festival.rules', ['npc' => $npcId]), __('festival.tab_rules'), 90],
    ];
@endphp
<table border="0" cellspacing="0" cellpadding="0" width="100%" style="position: relative; top: 0px;">
    <tbody>
    <tr height="21">
        @foreach($tabs as $key => [$url, $label, $width])
            @php $on = $key === $active; @endphp
            <td width="19"><img src="{{ asset($on ? $btnLeft2 : $btnLeft1) }}" width="19" height="21" alt=""></td>
            <td width="{{ $width }}" align="center" style="background: url({{ asset($on ? $btnCenter2 : $btnCenter1) }}) center top repeat-x; padding: 0px 2px 4px; white-space: nowrap;">
                <a href="{{ $url }}" class="{{ $on ? 'btn_2' : 'btn_1' }}">{{ $label }}</a>
            </td>
            <td width="19"><img src="{{ asset($on ? $btnRight2 : $btnRight1) }}" width="19" height="21" alt=""></td>
        @endforeach
        <td></td>
        <td width="19"><img src="{{ asset($btnLeft1) }}" width="19" height="21" alt=""></td>
        <td width="2%" align="center" style="background: url({{ asset($btnCenter1) }}) center top repeat-x; padding: 0px 2px 4px;">
            <a href="{{ route('location') }}" class="btn_1">{{ __('Выход') }}</a>
        </td>
        <td width="19"><img src="{{ asset($btnRight1) }}" width="19" height="21" alt=""></td>
    </tr>
    </tbody>
</table>
