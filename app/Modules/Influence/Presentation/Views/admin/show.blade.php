@extends('admin.layout.base')

@section('title', 'Влияние: '.$map->name)

@section('body')
    <div class="tabs">
        <ul class="nav nav-tabs">
            <li class="nav-item"><a class="nav-link active" href="#levels" data-bs-toggle="tab">Уровни</a></li>
            <li class="nav-item"><a class="nav-link" href="#medals" data-bs-toggle="tab">Медали</a></li>
            <li class="nav-item"><a class="nav-link" href="#access" data-bs-toggle="tab">Доступ к контенту</a></li>
            <li class="nav-item"><a class="nav-link" href="#shop" data-bs-toggle="tab">Магазин территории</a></li>
            <li class="nav-item"><a class="nav-link" href="#history" data-bs-toggle="tab">История начислений</a></li>
        </ul>
        <div class="tab-content">
            <div id="levels" class="tab-pane active pt-3">
                <section class="card mb-3">
                    <div class="card-header"><strong>Добавить уровень</strong></div>
                    <div class="card-body">
                        <form method="post" action="{{ route('admin.influence.level.store', $map) }}">
                            @csrf
                            <div class="row">
                                <div class="col-md-1"><label>№</label><input class="form-control" type="number" name="level" min="1" required></div>
                                <div class="col-md-3"><label>Название</label><input class="form-control" name="name" required></div>
                                <div class="col-md-2"><label>Порог</label><input class="form-control" type="number" name="required_influence" min="0" required></div>
                                <div class="col-md-3"><label>Медаль</label><select class="form-control" name="influence_medal_id"><option value="">— нет —</option>@foreach($medals as $medal)<option value="{{ $medal->id }}">{{ $medal->name }}</option>@endforeach</select></div>
                                <div class="col-md-1"><label>Порядок</label><input class="form-control" type="number" name="sort_order" value="0"></div>
                                <div class="col-md-1"><label>Активен</label><input type="hidden" name="is_active" value="0"><input type="checkbox" name="is_active" value="1" checked></div>
                                <div class="col-md-1 d-flex align-items-end"><button class="btn btn-primary">Добавить</button></div>
                            </div>
                        </form>
                    </div>
                </section>

                @forelse($map->influenceLevels as $level)
                    <section class="card mb-3">
                        <div class="card-header d-flex justify-content-between">
                            <strong>{{ $level->level }}. {{ $level->name }} — {{ number_format($level->required_influence, 0, '', ' ') }}</strong>
                            <form method="post" action="{{ route('admin.influence.level.destroy', [$map, $level]) }}" onsubmit="return confirm('Удалить уровень?')">@csrf @method('DELETE')<button class="btn btn-xs btn-danger">Удалить</button></form>
                        </div>
                        <div class="card-body">
                            <form method="post" action="{{ route('admin.influence.level.update', [$map, $level]) }}" class="mb-3">@csrf @method('PUT')
                                <div class="row">
                                    <div class="col-md-1"><input class="form-control" type="number" name="level" value="{{ $level->level }}" required></div>
                                    <div class="col-md-3"><input class="form-control" name="name" value="{{ $level->name }}" required></div>
                                    <div class="col-md-2"><input class="form-control" type="number" name="required_influence" value="{{ $level->required_influence }}" required></div>
                                    <div class="col-md-3"><select class="form-control" name="influence_medal_id"><option value="">— без медали —</option>@foreach($medals as $medal)<option value="{{ $medal->id }}" @selected($level->influence_medal_id === $medal->id)>{{ $medal->name }}</option>@endforeach</select></div>
                                    <div class="col-md-1"><input class="form-control" type="number" name="sort_order" value="{{ $level->sort_order }}"></div>
                                    <div class="col-md-1"><input type="hidden" name="is_active" value="0"><input type="checkbox" name="is_active" value="1" @checked($level->is_active)> Активен</div>
                                    <div class="col-md-1"><button class="btn btn-success">Сохранить</button></div>
                                </div>
                            </form>

                            <div class="row">
                                <div class="col-md-6">
                                    <h5>Локальные бонусы</h5>
                                    @foreach($level->bonuses as $bonus)
                                        <form class="d-inline" method="post" action="{{ route('admin.influence.bonus.destroy', [$map, $level, $bonus]) }}">@csrf @method('DELETE')<span class="badge badge-info">{{ $bonus->bonus_type->label() }}: {{ $bonus->value }}%</span><button class="btn btn-link btn-xs text-danger">×</button></form>
                                    @endforeach
                                    <form method="post" action="{{ route('admin.influence.bonus.store', [$map, $level]) }}" class="row mt-2">@csrf
                                        <div class="col-md-7"><select class="form-control" name="bonus_type">@foreach($bonusTypes as $type)<option value="{{ $type->value }}">{{ $type->label() }}</option>@endforeach</select></div>
                                        <div class="col-md-3"><input class="form-control" type="number" step="0.001" min="0" max="100" name="value" required placeholder="%"></div>
                                        <div class="col-md-2"><button class="btn btn-primary">ОК</button></div>
                                    </form>
                                </div>
                                <div class="col-md-6">
                                    <h5>Одноразовые награды</h5>
                                    @foreach($level->rewards as $reward)
                                        <form class="d-inline" method="post" action="{{ route('admin.influence.reward.destroy', [$map, $level, $reward]) }}">@csrf @method('DELETE')<span class="badge badge-success">{{ $reward->reward_type->label() }}: {{ $reward->item?->name ?? $reward->amount }}</span><button class="btn btn-link btn-xs text-danger">×</button></form>
                                    @endforeach
                                    <form method="post" action="{{ route('admin.influence.reward.store', [$map, $level]) }}" class="row mt-2">@csrf
                                        <div class="col-md-3"><select class="form-control" name="reward_type">@foreach($rewardTypes as $type)<option value="{{ $type->value }}">{{ $type->label() }}</option>@endforeach</select></div>
                                        <div class="col-md-4"><input class="form-control" type="number" name="share_item_id" placeholder="ID предмета"></div>
                                        <div class="col-md-3"><input class="form-control" type="number" min="1" name="amount" value="1" required></div>
                                        <div class="col-md-2"><button class="btn btn-primary">ОК</button></div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </section>
                @empty
                    <p class="text-muted">Уровни пока не настроены.</p>
                @endforelse
            </div>

            <div id="medals" class="tab-pane pt-3">
                <section class="card mb-3"><div class="card-header"><strong>Создать медаль</strong></div><div class="card-body">
                    <form method="post" enctype="multipart/form-data" action="{{ route('admin.influence.medal.store', $map) }}">@csrf
                        <div class="row"><div class="col-md-3"><input class="form-control" name="name" placeholder="Название" required></div><div class="col-md-3"><input class="form-control" name="description" placeholder="Описание"></div><div class="col-md-2"><input class="form-control" type="number" name="rating_points" min="0" value="0" placeholder="Очки рейтинга"></div><div class="col-md-3"><input class="form-control" type="file" name="icon" accept="image/*"></div><div class="col-md-1"><button class="btn btn-primary">Создать</button></div></div>
                    </form>
                </div></section>
                @foreach($medals as $medal)
                    <section class="card mb-3"><div class="card-header d-flex justify-content-between"><strong>@if($medal->iconUrl())<img src="{{ $medal->iconUrl() }}" width="32" height="32" style="object-fit:contain" alt="">@endif {{ $medal->name }} (рейтинг: {{ $medal->rating_points }})</strong><form method="post" action="{{ route('admin.influence.medal.destroy', [$map, $medal]) }}" onsubmit="return confirm('Удалить медаль?')">@csrf @method('DELETE')<button class="btn btn-xs btn-danger">Удалить</button></form></div><div class="card-body">
                        <form method="post" enctype="multipart/form-data" action="{{ route('admin.influence.medal.update', [$map, $medal]) }}" class="row mb-3">@csrf @method('PUT')<div class="col-md-3"><input class="form-control" name="name" value="{{ $medal->name }}" required></div><div class="col-md-3"><input class="form-control" name="description" value="{{ $medal->description }}" placeholder="Описание"></div><div class="col-md-2"><input class="form-control" type="number" min="0" name="rating_points" value="{{ $medal->rating_points }}"></div><div class="col-md-3"><input class="form-control" type="file" name="icon" accept="image/*"></div><div class="col-md-1"><button class="btn btn-success">Сохранить</button></div></form>
                        @foreach($medal->stats as $stat)<form class="d-inline" method="post" action="{{ route('admin.influence.medal.stat.destroy', [$map, $medal, $stat]) }}">@csrf @method('DELETE')<span class="badge badge-warning">{{ $stat->stat_type->label() }}: {{ $stat->value }}{{ $stat->is_percent ? '%' : '' }}</span><button class="btn btn-link btn-xs text-danger">×</button></form>@endforeach
                        <form method="post" action="{{ route('admin.influence.medal.stat.store', [$map, $medal]) }}" class="row mt-2">@csrf<div class="col-md-4"><select class="form-control" name="stat_type">@foreach($statTypes as $type)<option value="{{ $type->value }}">{{ $type->label() }}</option>@endforeach</select></div><div class="col-md-3"><input class="form-control" type="number" step="0.001" name="value" required></div><div class="col-md-2"><label><input type="checkbox" name="is_percent" value="1"> Проценты</label></div><div class="col-md-2"><button class="btn btn-primary">Добавить</button></div></form>
                    </div></section>
                @endforeach
            </div>

            <div id="access" class="tab-pane pt-3">
                <section class="card mb-3"><div class="card-header"><strong>Новое требование</strong></div><div class="card-body">
                    <form method="post" action="{{ route('admin.influence.requirement.store', $map) }}">@csrf
                        <div class="row">
                            <div class="col-md-2"><select class="form-control" name="target_type" id="influence-target-type"><option value="npc">NPC</option><option value="quest">Квест</option><option value="gate">Врата локации</option></select></div>
                            <div class="col-md-5">
                                <select class="form-control influence-target-select" name="target_id" data-type="npc">@foreach($npcs as $npc)<option value="{{ $npc->id }}">[{{ $npc->id }}] {{ $npc->name }}</option>@endforeach</select>
                                <select class="form-control influence-target-select d-none" data-type="quest">@foreach($quests as $quest)<option value="{{ $quest->id }}">[{{ $quest->id }}] {{ $quest->title }}</option>@endforeach</select>
                                <select class="form-control influence-target-select d-none" data-type="gate">@foreach($gates as $gate)<option value="{{ $gate->id }}">[{{ $gate->id }}] {{ $gate->fromLocation?->name }} → {{ $gate->toLocation?->name }}</option>@endforeach</select>
                            </div>
                            <div class="col-md-3"><input class="form-control" type="number" min="0" name="required_influence" required placeholder="Необходимое влияние"></div>
                            <div class="col-md-2"><button class="btn btn-primary">Сохранить</button></div>
                        </div>
                    </form>
                </div></section>
                @foreach(['npc' => 'NPC', 'quest' => 'Квесты', 'gate' => 'Врата'] as $type => $label)
                    <section class="card mb-3"><div class="card-header"><strong>{{ $label }}</strong></div><div class="card-body"><table class="table table-sm table-bordered"><thead><tr><th>ID цели</th><th>Требуется влияния</th><th></th></tr></thead><tbody>
                    @foreach($requirements[$type] as $requirement)
                        @php($targetColumn = $type === 'npc' ? 'npc_id' : ($type === 'quest' ? 'quest_id' : 'location_gate_id'))
                        <tr><td>{{ $requirement->{$targetColumn} }}</td><td>{{ number_format($requirement->required_influence, 0, '', ' ') }}</td><td><form method="post" action="{{ route('admin.influence.requirement.destroy', [$map, $type, $requirement->id]) }}">@csrf @method('DELETE')<button class="btn btn-xs btn-danger">Удалить</button></form></td></tr>
                    @endforeach
                    </tbody></table></div></section>
                @endforeach
            </div>

            <div id="shop" class="tab-pane pt-3">
                <section class="card mb-3"><div class="card-header"><strong>Добавить раздел территории в городской магазин</strong></div><div class="card-body">
                    <form method="post" action="{{ route('admin.influence.shop.section.store', $map) }}">@csrf<div class="row"><div class="col-md-4"><select class="form-control" name="structure_id" required>@foreach($structures as $structure)<option value="{{ $structure->id }}">[{{ $structure->id }}] {{ $structure->name }}</option>@endforeach</select></div><div class="col-md-3"><input class="form-control" name="name" value="{{ $map->name }}" required></div><div class="col-md-2"><input class="form-control" type="number" min="0" name="required_influence" value="0" placeholder="Порог"></div><div class="col-md-1"><input class="form-control" type="number" min="0" name="sort_order" value="0"></div><div class="col-md-2"><button class="btn btn-primary">Добавить</button></div></div></form>
                </div></section>
                @foreach($shopSections as $section)
                    <section class="card mb-3"><div class="card-header d-flex justify-content-between"><strong>{{ $section->name }} · {{ $section->structure?->name }} · от {{ number_format($section->required_influence, 0, '', ' ') }}</strong><form method="post" action="{{ route('admin.influence.shop.section.destroy', [$map, $section]) }}">@csrf @method('DELETE')<button class="btn btn-xs btn-danger">Удалить раздел</button></form></div><div class="card-body">
                        <table class="table table-sm table-bordered"><thead><tr><th>Предмет</th><th>Порог</th><th>Монеты</th><th>Алмазы</th><th></th></tr></thead><tbody>@foreach($section->items as $shopItem)<tr><td>[{{ $shopItem->share_item_id }}] {{ $shopItem->item?->name }}</td><td>{{ $shopItem->required_influence }}</td><td>{{ $shopItem->price }}</td><td>{{ $shopItem->diamond }}</td><td><form method="post" action="{{ route('admin.influence.shop.item.destroy', [$map, $section, $shopItem]) }}">@csrf @method('DELETE')<button class="btn btn-xs btn-danger">Удалить</button></form></td></tr>@endforeach</tbody></table>
                        <form method="post" action="{{ route('admin.influence.shop.item.store', [$map, $section]) }}">@csrf<div class="row"><div class="col-md-2"><input class="form-control" type="number" name="share_item_id" required placeholder="ID предмета"></div><div class="col-md-2"><input class="form-control" type="number" min="0" name="required_influence" value="0" placeholder="Порог"></div><div class="col-md-2"><input class="form-control" type="number" min="0" name="price" value="0" placeholder="Монеты"></div><div class="col-md-2"><input class="form-control" type="number" min="0" name="diamond" value="0" placeholder="Алмазы"></div><div class="col-md-2"><input class="form-control" type="number" min="1" name="purchase_limit" placeholder="Лимит"></div><div class="col-md-2"><button class="btn btn-primary">Добавить товар</button></div></div></form>
                    </div></section>
                @endforeach
            </div>

            <div id="history" class="tab-pane pt-3">
                <section class="card"><div class="card-body"><table class="table table-sm table-bordered table-striped"><thead><tr><th>Дата</th><th>Игрок</th><th>Изменение</th><th>Источник</th><th>Описание</th></tr></thead><tbody>
                @forelse($transactions as $transaction)
                    <tr><td>{{ $transaction->created_at?->format('d.m.Y H:i:s') }}</td><td>[{{ $transaction->user_id }}] {{ $transaction->user?->name }}</td><td class="{{ $transaction->amount >= 0 ? 'text-success' : 'text-danger' }}">{{ $transaction->amount >= 0 ? '+' : '' }}{{ $transaction->amount }}</td><td>{{ $transaction->source_type }} #{{ $transaction->source_id }}</td><td>{{ $transaction->description }}</td></tr>
                @empty
                    <tr><td colspan="5" class="text-center text-muted">Начислений пока нет.</td></tr>
                @endforelse
                </tbody></table></div></section>
            </div>
        </div>
    </div>
@endsection

@push('footer_scripts')
<script>
document.getElementById('influence-target-type')?.addEventListener('change', function () {
    document.querySelectorAll('.influence-target-select').forEach(function (select) {
        const active = select.dataset.type === this.value;
        select.classList.toggle('d-none', !active);
        select.disabled = !active;
        select.name = active ? 'target_id' : '';
    }, this);
});
</script>
@endpush
