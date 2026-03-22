@php
    $clanTabs = [
        ['route' => 'clan.member',      'label' => 'Состав клана', 'width' => 100],
        ['route' => 'clan.role',        'label' => 'Звания',       'width' => 60],
        ['route' => 'clan.information', 'label' => 'Информация',   'width' => 70],
        ['route' => 'clan.skills',      'label' => 'Навыки',          'width' => 70],
        ['route' => 'clan.quests',      'label' => 'Квесты',       'width' => 80],
        ['route' => null,               'label' => 'Управление',      'width' => 70],
        ['route' => 'clan.logs',        'label' => 'Логи',            'width' => 50],
    ];
    $activeTab = $activeTab ?? null;
@endphp

<table border="0" cellspacing="0" cellpadding="0" width="100%" style="position: relative; top: 0px;">
    <tbody>
    <tr height="21">
        @foreach($clanTabs as $tab)
            @php
                $isActive = $tab['route'] && $activeTab === $tab['route'];
                $l = $isActive ? 'img/bg/btn/btn-left2.gif'  : 'img/bg/btn/btn-left1.gif';
                $c = $isActive ? 'img/bg/btn/btn-cent2.gif'  : 'img/bg/btn/btn-cent1.gif';
                $r = $isActive ? 'img/bg/btn/btn-right2.gif' : 'img/bg/btn/btn-right1.gif';
                $cls = $isActive ? 'btn_2' : 'btn_1';
                $href = $tab['route'] ? route($tab['route']) : '';
            @endphp
            <td width="19"><img src="{{ asset($l) }}" width="19" height="21"><br></td>
            <td width="{{ $tab['width'] }}" align="center"
                style="background: url({{ asset($c) }}) center top repeat-x; padding: 0px 2px 6px;">
                <a href="{{ $href }}" class="{{ $cls }}">{{ $tab['label'] }}</a>
            </td>
            <td width="19"><img src="{{ asset($r) }}" width="19" height="21"><br></td>
        @endforeach
        <td></td>
    </tr>
    </tbody>
</table>
