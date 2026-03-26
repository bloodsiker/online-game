@php
    $btnLeft1   = 'img/bg/btn/btn-left1.gif';
    $btnCenter1 = 'img/bg/btn/btn-cent1.gif';
    $btnRight1  = 'img/bg/btn/btn-right1.gif';
    $btnLeft2   = 'img/bg/btn/btn-left2.gif';
    $btnCenter2 = 'img/bg/btn/btn-cent2.gif';
    $btnRight2  = 'img/bg/btn/btn-right2.gif';

    $tabs = [
        'put'      => ['route' => route('clan.warehouse', ['id' => $clanWarehouse->id]), 'label' => 'Положить'],
        'take'     => ['route' => route('clan.warehouse.take', ['id' => $clanWarehouse->id]), 'label' => 'Забрать'],
        'logs'     => ['route' => route('clan.warehouse.logs', ['id' => $clanWarehouse->id]), 'label' => 'Журнал'],
        'clan'     => ['route' => route('clan.member'), 'label' => 'Клан'],
    ];
@endphp
<table border="0" cellspacing="0" cellpadding="0" width="100%" style="position: relative; top: 0px;">
    <tbody>
    <tr height="21">
        @foreach($tabs as $key => $tab)
            @php $isActive = ($activeTab ?? '') === $key; @endphp
            <td width="19"><img src="{{ asset($isActive ? $btnLeft2 : $btnLeft1) }}" width="19" height="21"><br></td>
            <td width="2%" align="center" style="background: url({{ asset($isActive ? $btnCenter2 : $btnCenter1) }}) center top repeat-x; padding: 0px 2px 6px;">
                <a href="{{ $tab['route'] }}" class="{{ $isActive ? 'btn_2' : 'btn_1' }}">{{ $tab['label'] }}</a>
            </td>
            <td width="19"><img src="{{ asset($isActive ? $btnRight2 : $btnRight1) }}" width="19" height="21"><br></td>
            @if($key === 'logs')
                <td></td>
            @endif
        @endforeach
    </tr>
    </tbody>
</table>
