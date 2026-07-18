@extends('admin.layout.base')

@section('title')
    Новости
@endsection

@section('body')

    <div class="row">
        <div class="col-md-12">
            <section class="card">
                <div class="card-body">
                    <div class="mb-3">
                        <a href="{{ route('admin.news.create') }}" class="btn btn-sm btn-primary">Добавить новость</a>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover table-bordered mb-none">
                            <thead>
                            <tr>
                                <th width="50">ID</th>
                                <th>Заголовок</th>
                                <th width="120">Просмотров</th>
                                <th width="120">Комментарии</th>
                                <th width="110">Активна</th>
                                <th width="160">Дата создания</th>
                                <th width="150"></th>
                            </tr>
                            </thead>
                            <tbody>
                            @forelse($news as $item)
                                <tr style="vertical-align: middle">
                                    <td>{{ $item->id }}</td>
                                    <td><a href="{{ route('admin.news.info', $item->id) }}">{{ $item->title }}</a></td>
                                    <td>{{ $item->views_count }}</td>
                                    <td>
                                        {{ $item->visible_comments_count }} / {{ $item->comments_count }}
                                        @unless($item->allow_comments)
                                            <span class="badge badge-default">закрыты</span>
                                        @endunless
                                    </td>
                                    <td>
                                        @if($item->is_active)
                                            <span class="badge badge-success">Да</span>
                                        @else
                                            <span class="badge badge-default">Нет</span>
                                        @endif
                                    </td>
                                    <td>{{ $item->created_at?->format('d.m.Y H:i') }}</td>
                                    <td class="text-end">
                                        <a href="{{ route('admin.news.info', $item->id) }}" class="btn btn-xs btn-primary">Изменить</a>
                                        <a href="{{ route('admin.news.delete', $item->id) }}"
                                           class="btn btn-xs btn-danger"
                                           onclick="return confirm('Удалить новость «{{ $item->title }}»?')">Удалить</a>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="7" class="text-center text-muted">Нет новостей</td></tr>
                            @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-3">
                        {{ $news->onEachSide(2)->links('admin.pagination') }}
                    </div>
                </div>
            </section>
        </div>
    </div>

@endsection
