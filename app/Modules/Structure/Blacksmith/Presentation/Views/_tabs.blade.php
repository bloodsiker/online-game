@php
    $btnLeft1   = 'img/bg/btn/btn-left1.gif';
    $btnCenter1 = 'img/bg/btn/btn-cent1.gif';
    $btnRight1  = 'img/bg/btn/btn-right1.gif';
    $btnLeft2   = 'img/bg/btn/btn-left2.gif';
    $btnCenter2 = 'img/bg/btn/btn-cent2.gif';
    $btnRight2  = 'img/bg/btn/btn-right2.gif';

    $tabs = [
        ['key' => 'kraft',   'label' => 'Крафтить',       'route' => route('blacksmith',        ['id' => $blacksmith->id]), 'width' => 80],
        ['key' => 'break',   'label' => 'Разбить предмет','route' => route('blacksmith.break',  ['id' => $blacksmith->id]), 'width' => 110],
        ['key' => 'upgrade', 'label' => 'Заточка',        'route' => route('blacksmith.upgrade',['id' => $blacksmith->id]), 'width' => 70],
        ['key' => 'rarity-upgrade', 'label' => 'Апгрейд', 'route' => route('blacksmith.rarity_upgrade', ['id' => $blacksmith->id]), 'width' => 70],
        ['key' => 'gems',    'label' => 'Камни',           'route' => route('blacksmith.gems',  ['id' => $blacksmith->id]), 'width' => 65],
        ['key' => 'runes',   'label' => 'Руны',            'route' => route('blacksmith.runes', ['id' => $blacksmith->id]), 'width' => 55],
    ];
@endphp

<table border="0" cellspacing="0" cellpadding="0" width="100%" style="position: relative; top: 0px;">
    <tbody>
    <tr height="21">
        @foreach($tabs as $tab)
            @php $isActive = ($activeTab === $tab['key']); @endphp
            <td width="19"><img src="{{ asset($isActive ? $btnLeft2 : $btnLeft1) }}" width="19" height="21"></td>
            <td width="{{ $tab['width'] }}" align="center" style="background: url({{ asset($isActive ? $btnCenter2 : $btnCenter1) }}) center top repeat-x; padding: 0px 2px 6px;">
                <a href="{{ $tab['route'] }}" class="{{ $isActive ? 'btn_2' : 'btn_1' }}">{{ $tab['label'] }}</a>
            </td>
            <td width="19"><img src="{{ asset($isActive ? $btnRight2 : $btnRight1) }}" width="19" height="21"></td>
        @endforeach

        <td></td>

        <td width="19"><img src="{{ asset($btnLeft1) }}" width="19" height="21"></td>
        <td width="2%" align="center" style="background: url({{ asset($btnCenter1) }}) center top repeat-x; padding: 0px 2px 6px;">
            <a href="{{ route('location') }}" class="btn_1">Выход</a>
        </td>
        <td width="19"><img src="{{ asset($btnRight1) }}" width="19" height="21"></td>
    </tr>
    </tbody>
</table>
