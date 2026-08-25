@extends('admin.layout.base')

@section('title')
    Скиллы
@endsection

@section('body')

    <div class="row">
        <div class="col-md-12">
            <section class="card">
                <div class="card-body">
                    <div class="mb-3">
                        <a href="{{ route('admin.magic_skill.create') }}" class="btn btn-primary btn-sm">Добавить скилл</a>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover table-bordered mb-none">
                            <thead>
                            <tr>
                                <th width="50">ID</th>
                                <th width="58">Иконка</th>
                                <th>Название</th>
                                <th width="110">Slug</th>
                                <th width="100">Тип</th>
                                <th width="90">Цель</th>
                                <th width="70">Ур.</th>
                                <th width="80">Мана</th>
                                <th width="90">Кулдаун</th>
                                <th width="90">Эффекты</th>
                                <th width="80">Пассивный</th>
                                <th width="70"></th>
                            </tr>
                            </thead>
                            <tbody>
                            @forelse($list as $skill)
                                <tr style="vertical-align: middle">
                                    <td>{{ $skill->id }}</td>
                                    <td class="text-center">
                                        @if($skill->image)
                                            <img src="{{ $skill->image }}" alt="{{ $skill->name }}" style="width:36px;height:36px;object-fit:contain;">
                                        @else
                                            <span class="text-muted">—</span>
                                        @endif
                                    </td>
                                    <td><a href="{{ route('admin.magic_skill.info', $skill->id) }}">{{ $skill->name }}</a></td>
                                    <td class="text-muted small">{{ $skill->slug }}</td>
                                    <td><span class="badge badge-primary">{{ $skill->type }}</span></td>
                                    <td>{{ $skill->target_type }}</td>
                                    <td>{{ $skill->level }}</td>
                                    <td>{{ $skill->mana_cost }}</td>
                                    <td>{{ $skill->cooldown }}с</td>
                                    <td>{{ $skill->skill_effects_count }}</td>
                                    <td class="text-center">
                                        @if($skill->is_passive)
                                            <span class="badge badge-success">да</span>
                                        @else
                                            <span class="badge badge-secondary">нет</span>
                                        @endif
                                    </td>
                                    <td>
                                        <a href="{{ route('admin.magic_skill.info', $skill->id) }}" class="btn btn-xs btn-primary">Изменить</a>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="12" class="text-center text-muted">Нет скиллов</td></tr>
                            @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </section>
        </div>
    </div>

@endsection
