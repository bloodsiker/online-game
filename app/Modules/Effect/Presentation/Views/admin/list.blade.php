@extends('admin.layout.base')

@section('title')
    Эффекты
@endsection

@section('body')

    <div class="row">
        <div class="col-md-12">
            <section class="card">
                <div class="card-body">
                    <div class="mb-3">
                        <a href="{{ route('admin.effect.create') }}" class="btn btn-primary btn-sm">Добавить эффект</a>
                    </div>
                    <form method="get" action="{{ route('admin.effects') }}" class="mb-3">
                        <div class="row">
                            <div class="col-md-5"><div class="form-group"><label>Поиск по названию</label><input class="form-control" name="q" value="{{ $filters['q'] }}" placeholder="Название или slug"></div></div>
                            <div class="col-md-3"><div class="form-group"><label>Тип</label><select class="form-control" name="type"><option value="">Все типы</option>@foreach($types as $type)<option value="{{ $type }}" @selected($filters['type'] === $type)>{{ $type }}</option>@endforeach</select></div></div>
                            <div class="col-md-2"><div class="form-group"><label>Механика</label><select class="form-control" name="active_type"><option value="">Все</option>@foreach($activeTypes as $activeType)<option value="{{ $activeType->value }}" @selected($filters['active_type'] === $activeType->value)>{{ $activeType->label() }}</option>@endforeach</select></div></div>
                            <div class="col-md-2 d-flex align-items-end"><div class="form-group"><button class="btn btn-primary btn-sm">Фильтровать</button> <a href="{{ route('admin.effects') }}" class="btn btn-default btn-sm">Сбросить</a></div></div>
                        </div>
                    </form>
                    <div class="table-responsive">
                        <table class="table table-hover table-bordered mb-none">
                            <thead>
                            <tr>
                                <th width="50">ID</th>
                                <th width="58">Иконка</th>
                                <th>Название</th>
                                <th width="110">Slug</th>
                                <th width="90">Тип</th>
                                <th width="120">Механика</th>
                                <th width="150">Масштабирование</th>
                                <th width="90">Стакается</th>
                                <th width="90">Тик/значение</th>
                                <th width="90">Скиллов</th>
                                <th width="90">Монстров</th>
                                <th width="70"></th>
                            </tr>
                            </thead>
                            <tbody>
                            @forelse($list as $effect)
                                <tr style="vertical-align: middle">
                                    <td>{{ $effect->id }}</td>
                                    <td class="text-center">
                                        @if($effect->image)
                                            <img src="{{ $effect->image }}" alt="{{ $effect->name }}" style="width:36px;height:36px;object-fit:contain;">
                                        @else
                                            <span class="text-muted">—</span>
                                        @endif
                                    </td>
                                    <td><a href="{{ route('admin.effect.info', $effect->id) }}">{{ $effect->name }}</a></td>
                                    <td class="text-muted small">{{ $effect->slug }}</td>
                                    <td>
                                        @php
                                            $badge = match($effect->type) {
                                                'buff'    => 'badge-success',
                                                'debuff'  => 'badge-danger',
                                                default   => 'badge-secondary',
                                            };
                                        @endphp
                                        <span class="badge {{ $badge }}">{{ $effect->type }}</span>
                                    </td>
                                    <td>{{ $effect->resolvedActiveType()?->label() ?? '—' }}</td>
                                    <td>{{ $effect->resolvedDamageScalingType()->label() }}</td>
                                    <td class="text-center">
                                        @if($effect->is_stackable)
                                            <span class="badge badge-success">до {{ $effect->max_stacks }}</span>
                                        @else
                                            <span class="badge badge-secondary">нет</span>
                                        @endif
                                    </td>
                                    <td>{{ $effect->tick_interval }}с / {{ $effect->value_per_tick ?? '—' }}</td>
                                    <td>{{ $effect->magic_skills_count }}</td>
                                    <td>{{ $effect->monsters_count }}</td>
                                    <td>
                                        <a href="{{ route('admin.effect.info', $effect->id) }}" class="btn btn-xs btn-primary">Изменить</a>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="12" class="text-center text-muted">Нет эффектов</td></tr>
                            @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </section>
        </div>
    </div>

@endsection
