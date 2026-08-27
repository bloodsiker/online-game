@extends('admin.layout.base')

@section('title', 'Клановые навыки')

@section('body')
    <section class="card">
        <div class="card-body">
            <div class="mb-3"><a href="{{ route('admin.clan_skill.create') }}" class="btn btn-primary btn-sm">Добавить клановый навык</a></div>
            <div class="table-responsive">
                <table class="table table-hover table-bordered mb-none">
                    <thead><tr><th>ID</th><th>Название</th><th>Макс. ур.</th><th>Уровней настроено</th><th>Порядок</th><th></th></tr></thead>
                    <tbody>
                    @forelse($skills as $skill)
                        <tr>
                            <td>{{ $skill->id }}</td>
                            <td><a href="{{ route('admin.clan_skill.edit', $skill) }}">{{ $skill->name }}</a></td>
                            <td>{{ $skill->max_level }}</td>
                            <td>{{ $skill->levels_count }}</td>
                            <td>{{ $skill->sort_order }}</td>
                            <td><a href="{{ route('admin.clan_skill.edit', $skill) }}" class="btn btn-xs btn-primary">Изменить</a></td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center text-muted">Клановые навыки пока не добавлены.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </section>
@endsection
