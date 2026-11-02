@extends('admin.layout.base')
@section('title', 'Обращения к администрации')
@section('body')
<section class="card">
    <header class="card-header"><h2 class="card-title">Обращения к администрации</h2></header>
    <div class="card-body">
        <form method="get" class="mb-3">
            <select name="status" class="form-control">
                <option value="">Все обращения</option>
                <option value="pending" @selected($status === 'pending')>Ожидают ответа</option>
                <option value="answered" @selected($status === 'answered')>Отвеченные</option>
            </select>
            <button class="btn btn-primary mt-2">Показать</button>
        </form>
        <table class="table table-striped"><thead><tr><th>ID</th><th>Игрок</th><th>Тема</th><th>Статус</th><th>Последнее сообщение</th></tr></thead><tbody>
            @forelse($threads as $thread)
                <tr><td>{{ $thread->id }}</td><td>{{ $thread->sender?->name ?? 'Игрок удалён' }}</td>
                    <td><a href="{{ route('admin.post.administration.show', $thread->id) }}">{{ $thread->subject }}</a></td>
                    <td>{{ $thread->administration_status === 'pending' ? 'Ожидает ответа' : 'Отвечено' }}</td><td>{{ $thread->administration_last_message_at?->format('d.m.Y H:i') }}</td></tr>
            @empty
                <tr><td colspan="5">Обращений нет.</td></tr>
            @endforelse
        </tbody></table>
        {{ $threads->links() }}
    </div>
</section>
@endsection
