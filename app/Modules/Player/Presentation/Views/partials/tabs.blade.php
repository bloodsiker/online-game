@php
    $btnLeft1   = 'img/bg/btn/btn-left1.gif';
    $btnCenter1 = 'img/bg/btn/btn-cent1.gif';
    $btnRight1  = 'img/bg/btn/btn-right1.gif';
    $btnLeft2   = 'img/bg/btn/btn-left2.gif';
    $btnCenter2 = 'img/bg/btn/btn-cent2.gif';
    $btnRight2  = 'img/bg/btn/btn-right2.gif';

    $tabs = [
        ['group' => 'character',   'route' => 'character',        'params' => ['group' => 'character'],   'label' => 'Персонаж'],
        ['group' => 'magic_skill', 'route' => 'magic_skill',      'params' => ['group' => 'magic_skill'], 'label' => 'Книга заклинаний'],
        ['group' => 'slots',       'route' => 'slots',            'params' => ['group' => 'slots'],       'label' => 'Слоты'],
        ['group' => 'reputation',  'route' => 'reputation.list',  'params' => ['group' => 'reputation'],  'label' => 'Репутации'],
    ];
@endphp
<table border="0" cellspacing="0" cellpadding="0" style="position: relative; top: -2px;">
    <tbody>
    <tr height="21">
        @foreach($tabs as $tab)
            @php $active = ($group ?? '') === $tab['group']; @endphp
            <td width="19"><img src="{{ asset($active ? $btnLeft2 : $btnLeft1) }}" width="19" height="21"><br></td>
            <td align="center" style="background: url({{ asset($active ? $btnCenter2 : $btnCenter1) }}) center top repeat-x; padding: 0px 2px 3px;">
                <a href="{{ route($tab['route'], $tab['params']) }}" class="{{ $active ? 'btn_2' : 'btn_1' }}">{{ $tab['label'] }}</a>
            </td>
            <td width="19"><img src="{{ asset($active ? $btnRight2 : $btnRight1) }}" width="19" height="21"><br></td>
        @endforeach
    </tr>
    </tbody>
</table>
