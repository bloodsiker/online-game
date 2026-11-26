@php
    $nav = $nav ?? [];
    $items = [
        'edit' => ['route' => 'admin.player.info', 'label' => 'Редактирование', 'badge' => null],
        'backpack' => ['route' => 'admin.player.backpack', 'label' => 'Рюкзак', 'badge' => $nav['backpack'] ?? null],
        'skills' => ['route' => 'admin.player.skills', 'label' => 'Навыки', 'badge' => $nav['skills'] ?? null],
        'reputations' => ['route' => 'admin.player.reputations', 'label' => 'Репутации', 'badge' => $nav['reputations'] ?? null],
        'quests' => ['route' => 'admin.player.quests', 'label' => 'Квесты', 'badge' => $nav['quests'] ?? null],
        'mutes' => ['route' => 'admin.player.mutes', 'label' => 'Молчание', 'badge' => ($nav['mutes'] ?? 0) ?: null, 'danger' => true],
        'logins' => ['route' => 'admin.player.logins', 'label' => 'Входы', 'badge' => $nav['logins'] ?? null],
        'detention' => ['route' => 'admin.player.detention', 'label' => 'Темница', 'badge' => ($nav['detention'] ?? false) ? 'Заключён' : null, 'danger' => true],
    ];
@endphp
<div class="row mb-3">
    <div class="col-md-12">
        <div class="d-flex flex-wrap align-items-center" style="gap: 8px;">
            <a href="{{ route('admin.players') }}" class="btn btn-sm btn-default">← Игроки</a>
            @foreach($items as $key => $item)
                <a href="{{ route($item['route'], $player) }}" class="btn btn-sm {{ $active === $key ? 'btn-primary' : 'btn-default' }}">
                    {{ $item['label'] }}
                    @if($item['badge'] !== null)
                        <span class="badge {{ ($item['danger'] ?? false) ? 'badge-danger' : ($active === $key ? 'badge-dark' : 'badge-primary') }}">{{ $item['badge'] }}</span>
                    @endif
                </a>
            @endforeach
        </div>
    </div>
</div>
