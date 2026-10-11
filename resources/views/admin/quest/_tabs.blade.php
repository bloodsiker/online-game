<ul class="nav nav-tabs mb-3">
    <li class="nav-item">
        <a class="nav-link {{ request()->routeIs('admin.quests') ? 'active' : '' }}" href="{{ route('admin.quests') }}">Список квестов</a>
    </li>
    <li class="nav-item">
        <a class="nav-link {{ request()->routeIs('admin.quests.tree') ? 'active' : '' }}" href="{{ route('admin.quests.tree') }}">Связи квестов</a>
    </li>
</ul>
