@extends('admin.layout.base')

@section('title', 'Статьи Библиотеки')

@section('body')
    <section class="card">
        <div class="card-body">
            <div class="mb-3 d-flex gap-2">
                <a href="{{ route('admin.library.articles.create') }}" class="btn btn-primary btn-sm">Добавить статью</a>
                <a href="{{ route('admin.library.categories.index') }}" class="btn btn-default btn-sm">Категории</a>
                <a href="{{ route('library.index') }}" class="btn btn-default btn-sm" target="_blank">Открыть Библиотеку</a>
            </div>

            <form method="get" class="row g-2 mb-3">
                <div class="col-md-4"><input class="form-control" name="q" value="{{ request('q') }}" placeholder="Поиск по названию"></div>
                <div class="col-md-3"><select class="form-control" name="category_id"><option value="">Все категории</option>@foreach($categories as $category)<option value="{{ $category->id }}" @selected((string) request('category_id') === (string) $category->id)>{{ $category->name }}</option>@endforeach</select></div>
                <div class="col-md-2"><select class="form-control" name="status"><option value="">Все статусы</option>@foreach($statuses as $value => $label)<option value="{{ $value }}" @selected(request('status') === $value)>{{ $label }}</option>@endforeach</select></div>
                <div class="col-md-2"><button class="btn btn-default">Фильтровать</button></div>
            </form>

            <div class="table-responsive">
                <table class="table table-hover table-bordered mb-none">
                    <thead><tr><th width="60">ID</th><th>Название</th><th>Категория</th><th width="130">Статус</th><th width="100">Просмотры</th><th width="150">Публикация</th><th width="190"></th></tr></thead>
                    <tbody>
                    @forelse($articles as $article)
                        <tr style="vertical-align:middle">
                            <td>{{ $article->id }}</td>
                            <td><a href="{{ route('admin.library.articles.edit', $article) }}">{{ $article->title }}</a><small class="text-muted d-block">/{{ $article->slug }}</small></td>
                            <td>{{ $article->category?->name }}</td>
                            <td><span class="badge badge-{{ $article->status === 'published' ? 'success' : 'default' }}">{{ $statuses[$article->status] ?? $article->status }}</span></td>
                            <td>{{ $article->views_count }}</td>
                            <td>{{ $article->published_at?->format('d.m.Y H:i') ?? '—' }}</td>
                            <td class="text-end">
                                @if($article->status === 'published')<a href="{{ route('library.show', $article->slug) }}" class="btn btn-xs btn-default" target="_blank">Открыть</a>@endif
                                <a href="{{ route('admin.library.articles.edit', $article) }}" class="btn btn-xs btn-primary">Изменить</a>
                                <form method="post" action="{{ route('admin.library.articles.destroy', $article) }}" class="d-inline" onsubmit="return confirm('Удалить статью «{{ $article->title }}»?')">@csrf @method('DELETE')<button class="btn btn-xs btn-danger">Удалить</button></form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="text-center text-muted">Статьи ещё не созданы</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mt-3">{{ $articles->onEachSide(2)->links('admin.pagination') }}</div>
        </div>
    </section>
@endsection
