@extends('admin.layout.base')

@section('title')
    Новость: {{ $news->title }}
@endsection

@section('body')

    <div class="row">
        <div class="col-md-12">
            <section class="card">
                <header class="card-header">
                    <h2 class="card-title">Редактирование новости</h2>
                </header>
                <div class="card-body">
                    <form action="{{ route('admin.news.info', $news->id) }}" method="post">
                        {{ csrf_field() }}
                        @include('admin.news._form', ['news' => $news])
                        <div class="text-end mt-3">
                            <a href="{{ route('admin.news') }}" class="btn btn-default">Назад</a>
                            <button class="btn btn-primary">Сохранить</button>
                        </div>
                    </form>
                </div>
            </section>
        </div>
    </div>

    <div class="row mt-4">
        <div class="col-md-12">
            <section class="card">
                <header class="card-header">
                    <h2 class="card-title">Комментарии игроков</h2>
                </header>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover table-bordered mb-none">
                            <thead>
                            <tr>
                                <th width="50">ID</th>
                                <th width="180">Игрок</th>
                                <th>Комментарий</th>
                                <th width="120">Виден</th>
                                <th width="160">Дата</th>
                                <th width="150"></th>
                            </tr>
                            </thead>
                            <tbody>
                            @forelse($news->comments as $comment)
                                <tr style="vertical-align: middle">
                                    <td>{{ $comment->id }}</td>
                                    <td>{{ $comment->user?->player?->name ?? $comment->user?->name ?? '—' }}</td>
                                    <td>{{ $comment->message }}</td>
                                    <td>
                                        @if($comment->is_visible)
                                            <span class="badge badge-success">Да</span>
                                        @else
                                            <span class="badge badge-default">Нет</span>
                                        @endif
                                    </td>
                                    <td>{{ $comment->created_at?->format('d.m.Y H:i') }}</td>
                                    <td class="text-end">
                                        <a href="{{ route('admin.news.comment.toggle', [$news->id, $comment->id]) }}" class="btn btn-xs btn-warning">
                                            {{ $comment->is_visible ? 'Скрыть' : 'Показать' }}
                                        </a>
                                        <a href="{{ route('admin.news.comment.delete', [$news->id, $comment->id]) }}"
                                           class="btn btn-xs btn-danger"
                                           onclick="return confirm('Удалить комментарий?')">Удалить</a>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="6" class="text-center text-muted">Комментариев нет</td></tr>
                            @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </section>
        </div>
    </div>

@endsection
