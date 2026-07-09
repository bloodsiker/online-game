@php
    $btnLeft1   = 'img/bg/btn/btn-left1.gif';
    $btnCenter1 = 'img/bg/btn/btn-cent1.gif';
    $btnRight1  = 'img/bg/btn/btn-right1.gif';
    $btnLeft2   = 'img/bg/btn/btn-left2.gif';
    $btnCenter2 = 'img/bg/btn/btn-cent2.gif';
    $btnRight2  = 'img/bg/btn/btn-right2.gif';
@endphp
<table border="0" cellspacing="0" cellpadding="0" width="100%" style="position: relative; top: 0px;">
    <tbody>
    <tr height="21">
        @php $topupActive = ($activeMenu ?? '') === 'topup'; @endphp
        <td width="19"><img src="{{ asset($topupActive ? $btnLeft2 : $btnLeft1) }}" width="19" height="21"><br></td>
        <td width="2%" align="center" style="background: url({{ asset($topupActive ? $btnCenter2 : $btnCenter1) }}) center top repeat-x; padding: 0px 2px 6px;">
            <a href="{{ route('premium.topup') }}" class="{{ $topupActive ? 'btn_2' : 'btn_1' }}">Покупка</a>
        </td>
        <td width="19"><img src="{{ asset($topupActive ? $btnRight2 : $btnRight1) }}" width="19" height="21"><br></td>

        @php $shopActive = ($activeMenu ?? '') === 'shop'; @endphp
        <td width="19"><img src="{{ asset($shopActive ? $btnLeft2 : $btnLeft1) }}" width="19" height="21"><br></td>
        <td width="150" align="center" style="background: url({{ asset($shopActive ? $btnCenter2 : $btnCenter1) }}) center top repeat-x; padding: 0px 2px 6px;">
            <a href="{{ route('premium.shop') }}" class="{{ $shopActive ? 'btn_2' : 'btn_1' }}">Премиальный магазин</a>
        </td>
        <td width="19"><img src="{{ asset($shopActive ? $btnRight2 : $btnRight1) }}" width="19" height="21"><br></td>

        @php $stockActive = ($activeMenu ?? '') === 'stock'; @endphp
        <td width="19"><img src="{{ asset($stockActive ? $btnLeft2 : $btnLeft1) }}" width="19" height="21"><br></td>
        <td width="2%" align="center" style="background: url({{ asset($stockActive ? $btnCenter2 : $btnCenter1) }}) center top repeat-x; padding: 0px 2px 6px;">
            <a href="{{ route('premium.stock') }}" class="{{ $stockActive ? 'btn_2' : 'btn_1' }}">Акции</a>
        </td>
        <td width="19"><img src="{{ asset($stockActive ? $btnRight2 : $btnRight1) }}" width="19" height="21"><br></td>

        <td></td>

        <td width="19"><img src="{{ asset($btnLeft1) }}" width="19" height="21"><br></td>
        <td width="2%" align="center" style="background: url({{ asset($btnCenter1) }}) center top repeat-x; padding: 0px 2px 6px;">
            <a href="{{ route('location') }}" class="btn_1">Выход</a>
        </td>
        <td width="19"><img src="{{ asset($btnRight1) }}" width="19" height="21"><br></td>
    </tr>
    </tbody>
</table>