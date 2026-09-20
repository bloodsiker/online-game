@extends('admin.layout.base')

@section('title', 'Категории форума')

@section('body')
    <section class="card">
        <div class="card-body">
            <div class="mb-3 d-flex gap-2">
                <a href="{{ route('admin.forum.sections.create') }}" class="btn btn-primary btn-sm">Добавить категорию</a>
                <a href="{{ route('forum.index') }}" class="btn btn-default btn-sm" target="_blank">Открыть форум</a>
            </div>

            <div class="table-responsive">
                <table class="table table-hover table-bordered mb-none">
                    <thead>
                    <tr>
                        <th width="60">ID</th>
                        <th>Название</th>
                        <th>Родитель</th>
                        <th width="100">Порядок</th>
                        <th width="90">Тем</th>
                        <th width="100">Активна</th>
                        <th width="150">Публикация</th>
                        <th width="190"></th>
                    </tr>
                    </thead>
                    <tbody>
                    @forelse($sections as $forumSection)
                        @include('forum::admin.sections.row', ['forumSection' => $forumSection, 'isChild' => false])
                        @foreach($forumSection->allChildren as $childSection)
                            @include('forum::admin.sections.row', ['forumSection' => $childSection, 'isChild' => true])
                        @endforeach
                    @empty
                        <tr><td colspan="8" class="text-center text-muted">Категории ещё не созданы</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </section>
@endsection
