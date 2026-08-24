@php
    $btnLeft1   = 'img/bg/btn/btn-left1.gif';
    $btnCenter1 = 'img/bg/btn/btn-cent1.gif';
    $btnRight1  = 'img/bg/btn/btn-right1.gif';
    $btnLeft2   = 'img/bg/btn/btn-left2.gif';
    $btnCenter2 = 'img/bg/btn/btn-cent2.gif';
    $btnRight2  = 'img/bg/btn/btn-right2.gif';

    // Купить товар/Мои лоты/Новый лот — вкладки коммисионного магазина;
    // Биржа/Мои заявки/Новая заявка/Получить — вкладки биржи. Показываем
    // только вкладки той структуры, на которой сейчас находится игрок.
    $commissionTabs = [
        ['key' => 'buy',     'label' => 'Купить товар', 'route' => route('auction',         ['id' => $commissionShop->id]), 'width' => 100],
        ['key' => 'my_lot',  'label' => 'Мои лоты',     'route' => route('auction.my_lot',  ['id' => $commissionShop->id]), 'width' => 80],
        ['key' => 'new_lot', 'label' => 'Новый лот',    'route' => route('auction.new_lot', ['id' => $commissionShop->id]), 'width' => 80],
        ['key' => 'sale_proceeds', 'label' => 'Выручка', 'route' => route('auction.sale_proceeds', ['id' => $commissionShop->id]), 'width' => 70],
    ];
    $exchangeTabs = [
        ['key' => 'exchange',  'label' => 'Продать',      'route' => route('auction.exchange',  ['id' => $exchange->id]), 'width' => 60],
        ['key' => 'my_orders', 'label' => 'Мои заявки',   'route' => route('auction.my_orders', ['id' => $exchange->id]), 'width' => 85],
        ['key' => 'new_order', 'label' => 'Новая заявка', 'route' => route('auction.new_order', ['id' => $exchange->id]), 'width' => 90],
        ['key' => 'claims',    'label' => 'Получить',     'route' => route('auction.claims',    ['id' => $exchange->id]), 'width' => 70],
    ];

    $group = $group ?? (collect($exchangeTabs)->pluck('key')->contains($activeTab) ? 'exchange' : 'commission');
    $tabs = $group === 'exchange' ? $exchangeTabs : $commissionTabs;
@endphp

<table border="0" cellspacing="0" cellpadding="0" width="100%" style="position: relative; top: 0px;">
    <tbody>
    <tr height="21">
        @foreach($tabs as $tab)
            @php $isActive = ($activeTab === $tab['key']); @endphp
            <td width="19"><img src="{{ asset($isActive ? $btnLeft2 : $btnLeft1) }}" width="19" height="21"><br></td>
            <td width="{{ $tab['width'] }}" align="center" style="background: url({{ asset($isActive ? $btnCenter2 : $btnCenter1) }}) center top repeat-x; padding: 0px 2px 6px;">
                <a href="{{ $tab['route'] }}" title="{{ $tab['label'] }}" class="{{ $isActive ? 'btn_2' : 'btn_1' }}">{{ $tab['label'] }}</a>
            </td>
            <td width="19"><img src="{{ asset($isActive ? $btnRight2 : $btnRight1) }}" width="19" height="21"><br></td>
        @endforeach

        <td></td>

        <td width="19"><img src="{{ asset($btnLeft1) }}" width="19" height="21"><br></td>
        <td width="2%" align="center" style="background: url({{ asset($btnCenter1) }}) center top repeat-x; padding: 0px 2px 6px;">
            <a href="{{ route('location') }}" title="Выход" class="btn_1">Выход</a>
        </td>
        <td width="19"><img src="{{ asset($btnRight1) }}" width="19" height="21"><br></td>
    </tr>
    </tbody>
</table>
