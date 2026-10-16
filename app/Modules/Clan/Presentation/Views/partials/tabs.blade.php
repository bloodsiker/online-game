@php
    $clanTabs = [
        ['route' => 'clan.member',      'label' => __('Состав клана')],
        ['route' => 'clan.role',        'label' => __('Звания')],
        ['route' => 'clan.information', 'label' => __('Информация')],
        ['route' => 'clan.characteristics', 'label' => __('Характеристики')],
        ['route' => 'clan.skills',      'label' => __('Навыки')],
        ['route' => 'clan.quests',      'label' => __('Квесты')],
        ['route' => 'clan.boss',        'label' => __('Башня')],
        ['route' => 'clan.logs',        'label' => __('Логи')],
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
            <td align="center" style="background: url({{ asset($c) }}) center top repeat-x; padding: 0px 6px 6px; white-space: nowrap;">
                <a href="{{ $href }}" class="{{ $cls }}" style="white-space: nowrap;">{{ $tab['label'] }}</a>
            </td>
            <td width="19"><img src="{{ asset($r) }}" width="19" height="21"><br></td>
        @endforeach
        <td width="100%"></td>
    </tr>
    </tbody>
</table>
