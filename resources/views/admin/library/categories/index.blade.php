@extends('admin.layout.base')

@section('title', 'Категории Библиотеки')

@section('body')
    <section class="card">
        <div class="card-body">
            <div class="mb-3 d-flex gap-2">
                <a href="{{ route('admin.library.categories.create') }}" class="btn btn-primary btn-sm">Добавить категорию</a>
                <a href="{{ route('admin.library.articles.index') }}" class="btn btn-default btn-sm">Статьи</a>
                <a href="{{ route('library.index') }}" class="btn btn-default btn-sm" target="_blank">Открыть Библиотеку</a>
            </div>

            <div class="table-responsive">
                <table class="table table-hover table-bordered mb-none">
                    <thead>
                    <tr>
                        <th width="60">ID</th>
                        <th>Название</th>
                        <th>Родитель</th>
                        <th width="100">Порядок</th>
                        <th width="100">Статей</th>
                        <th width="100">Активна</th>
                        <th width="190"></th>
                    </tr>
                    </thead>
                    <tbody>
                    @forelse($categories as $category)
                        <tr style="vertical-align: middle">
                            <td>{{ $category->id }}</td>
                            <td>
                                @if($category->parent_id)&mdash; @endif
                                <a href="{{ route('admin.library.categories.edit', $category) }}">{{ $category->name }}</a>
                                <small class="text-muted d-block">/{{ $category->slug }}</small>
                            </td>
                            <td>{{ $category->parent?->name ?? '—' }}</td>
                            <td>{{ $category->sort_order }}</td>
                            <td>{{ $category->articles_count }}</td>
                            <td><span class="badge badge-{{ $category->is_active ? 'success' : 'default' }}">{{ $category->is_active ? 'Да' : 'Нет' }}</span></td>
                            <td class="text-end">
                                <a href="{{ route('admin.library.categories.edit', $category) }}" class="btn btn-xs btn-primary">Изменить</a>
                                <form action="{{ route('admin.library.categories.destroy', $category) }}" method="post" class="d-inline" onsubmit="return confirm('Удалить категорию «{{ $category->name }}»?')">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-xs btn-danger">Удалить</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="text-center text-muted">Категории ещё не созданы</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </section>
@endsection
