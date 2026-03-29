@extends('admin.layout.base')

@section('title')
    Навыки
@endsection

@section('body')

    <div class="row">
        <div class="col-md-12">
            <section class="card">
                <div class="card-body">
                    <div class="mb-3">
                        <a href="{{ route('admin.skill.create') }}" class="btn btn-primary btn-sm">Добавить навык</a>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover table-bordered mb-none">
                            <thead>
                            <tr>
                                <th width="50">ID</th>
                                <th>Название</th>
                                <th width="160">Тип</th>
                                <th>Описание</th>
                                <th width="70"></th>
                            </tr>
                            </thead>
                            <tbody>
                            @forelse($list as $skill)
                                <tr style="vertical-align: middle">
                                    <td>{{ $skill->id }}</td>
                                    <td><a href="{{ route('admin.skill.info', $skill->id) }}">{{ $skill->name }}</a></td>
                                    <td>
                                        @php
                                            $badge = match($skill->type) {
                                                'combat'  => 'badge-danger',
                                                'magic'   => 'badge-primary',
                                                default   => 'badge-success',
                                            };
                                            $label = match($skill->type) {
                                                'combat'  => 'Боевой',
                                                'magic'   => 'Магический',
                                                'peaceful'=> 'Мирный',
                                                default   => $skill->type,
                                            };
                                        @endphp
                                        <span class="badge {{ $badge }}">{{ $label }}</span>
                                    </td>
                                    <td class="text-muted small">{{ Str::limit($skill->description, 80) }}</td>
                                    <td>
                                        <a href="{{ route('admin.skill.info', $skill->id) }}" class="btn btn-xs btn-primary">Изменить</a>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="text-center text-muted">Нет навыков</td></tr>
                            @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </section>
        </div>
    </div>

@endsection