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
                    <div class="table-responsive">
                        <table class="table table-hover table-bordered mb-none">
                            <thead>
                            <tr>
                                <th width="50">ID</th>
                                <th>Название</th>
                                <th width="110">Slug</th>
                                <th width="90">Тип</th>
                                <th width="100">Длит-ть</th>
                                <th width="90">Стакается</th>
                                <th width="90">Тик/значение</th>
                                <th width="90">Скиллов</th>
                                <th width="70"></th>
                            </tr>
                            </thead>
                            <tbody>
                            @forelse($list as $effect)
                                <tr style="vertical-align: middle">
                                    <td>{{ $effect->id }}</td>
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
                                    <td>{{ $effect->duration }}с</td>
                                    <td class="text-center">
                                        @if($effect->is_stackable)
                                            <span class="badge badge-success">до {{ $effect->max_stacks }}</span>
                                        @else
                                            <span class="badge badge-secondary">нет</span>
                                        @endif
                                    </td>
                                    <td>{{ $effect->tick_interval }}с / {{ $effect->value_per_tick ?? '—' }}</td>
                                    <td>{{ $effect->magic_skills_count }}</td>
                                    <td>
                                        <a href="{{ route('admin.effect.info', $effect->id) }}" class="btn btn-xs btn-primary">Изменить</a>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="9" class="text-center text-muted">Нет эффектов</td></tr>
                            @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </section>
        </div>
    </div>

@endsection
