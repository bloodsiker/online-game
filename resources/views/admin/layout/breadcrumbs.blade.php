@php
    $currentRouteName = request()->route()?->getName();

    $section = match (true) {
        request()->routeIs('admin.race', 'admin.race.*') => ['label' => 'Расы', 'route' => 'admin.race'],
        request()->routeIs('admin.maps', 'admin.map.*') => ['label' => 'Карты', 'route' => 'admin.maps'],
        request()->routeIs('admin.location-gates', 'admin.location-gate.*') => ['label' => 'Врата локаций', 'route' => 'admin.location-gates'],
        request()->routeIs('admin.locations', 'admin.location.*') => ['label' => 'Локации', 'route' => 'admin.locations'],
        request()->routeIs('admin.npc.dialogues', 'admin.npc.dialogue.*') => ['label' => 'Диалоги НПС', 'route' => 'admin.npc.dialogues'],
        request()->routeIs('admin.npc', 'admin.npc.create', 'admin.npc.info') => ['label' => 'НПС', 'route' => 'admin.npc'],
        request()->routeIs('admin.skills', 'admin.skill.*') => ['label' => 'Навыки', 'route' => 'admin.skills'],
        request()->routeIs('admin.items', 'admin.item.*') => ['label' => 'Предметы', 'route' => 'admin.items'],
        request()->routeIs('admin.players', 'admin.player.*') => ['label' => 'Игроки', 'route' => 'admin.players'],
        request()->routeIs('admin.clans', 'admin.clan.*') => ['label' => 'Кланы', 'route' => 'admin.clans'],
        request()->routeIs('admin.reputations', 'admin.reputation.*') => ['label' => 'Репутации', 'route' => 'admin.reputations'],
        request()->routeIs('admin.quests', 'admin.quest.*') => ['label' => 'Квесты', 'route' => 'admin.quests'],
        request()->routeIs('admin.monsters', 'admin.monster.*') => ['label' => 'Монстры', 'route' => 'admin.monsters'],
        request()->routeIs('admin.dungeons', 'admin.dungeon.*') => ['label' => 'Данжи', 'route' => 'admin.dungeons'],
        request()->routeIs('admin.structures', 'admin.structure.*') => ['label' => 'Построения', 'route' => 'admin.structures'],
        request()->routeIs('admin.bank.stocks', 'admin.bank.stock.*') => ['label' => 'Акции банка', 'route' => 'admin.bank.stocks'],
        request()->routeIs('admin.action', 'admin.action.*') => ['label' => 'Действия на локации', 'route' => 'admin.action'],
        request()->routeIs('admin.news', 'admin.news.*') => ['label' => 'Новости', 'route' => 'admin.news'],
        request()->routeIs('admin.event.activities', 'admin.event.activity.*') => ['label' => 'Активности событий', 'route' => 'admin.event.activities'],
        request()->routeIs('admin.referral.stages', 'admin.referral.stage.*') => ['label' => 'Этапы наград', 'route' => 'admin.referral.stages'],
        request()->routeIs('admin.referral.stats') => ['label' => 'Статистика рефералов', 'route' => 'admin.referral.stats'],
        request()->routeIs('admin.post.send', 'admin.post.send.store') => ['label' => 'Почта', 'route' => 'admin.post.send'],
        request()->routeIs('admin.users') => ['label' => 'Пользователи', 'route' => 'admin.users'],
        request()->routeIs('admin.dashboard') => ['label' => 'Дашборд', 'route' => 'admin.dashboard'],
        request()->routeIs('admin.docs.*') => ['label' => 'Документация', 'route' => null],
        default => null,
    };

    $hasParent = $section !== null
        && $section['route'] !== null
        && $currentRouteName !== $section['route'];
@endphp

<div class="right-wrapper text-end admin-breadcrumb-wrapper">
    <ol class="breadcrumbs admin-breadcrumbs" aria-label="Навигационная цепочка">
        <li class="admin-breadcrumb-home">
            <a href="{{ route('admin.dashboard') }}" aria-label="Дашборд" title="Дашборд">
                <i class="bx bx-home-alt" aria-hidden="true"></i>
            </a>
        </li>

        @if($hasParent)
            <li class="admin-breadcrumb-parent">
                <a href="{{ route($section['route']) }}" title="Вернуться в раздел «{{ $section['label'] }}»">
                    <i class="bx bx-arrow-back" aria-hidden="true"></i>
                    {{ $section['label'] }}
                </a>
            </li>
        @elseif($section !== null && $section['route'] === null)
            <li class="admin-breadcrumb-section"><span>{{ $section['label'] }}</span></li>
        @endif

        <li class="admin-breadcrumb-current" aria-current="page"><span title="{{ $pageTitle }}">{{ $pageTitle }}</span></li>
    </ol>
</div>
