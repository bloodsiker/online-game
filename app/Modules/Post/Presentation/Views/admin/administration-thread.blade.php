@extends('admin.layout.base')
@section('title', 'Обращение #'.$thread->id)
@section('body')
<section class="card"><header class="card-header"><h2 class="card-title">{{ $thread->subject }}</h2></header>
    <div class="card-body">
        <a href="{{ route('admin.post.administration.index') }}">Все обращения</a>
        @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
        @foreach($messages as $message)
            <div class="card my-3"><div class="card-body">
                <b>{{ $message->is_to_administration ? ($message->sender?->name ?? 'Игрок удалён') : 'Администрация ('.($message->administrationSender?->name ?? 'удалённый администратор').')' }}</b>
                <small>{{ $message->created_at->format('d.m.Y H:i') }}</small>
                <div style="white-space:pre-wrap;margin-top:10px">{{ $message->text }}</div>
            </div></div>
        @endforeach
        @if($errors->any())<div class="alert alert-danger">{{ $errors->first() }}</div>@endif
        @if($thread->sender_user_id)
            <form method="post" action="{{ route('admin.post.administration.reply', $thread->id) }}">
                @csrf
                <label for="reply-text">Ответ от имени Администрации</label>
                <textarea id="reply-text" class="form-control mb-3" name="text" rows="7" maxlength="10000" required>{{ old('text') }}</textarea>
                <button class="btn btn-primary">Ответить в игровую почту</button>
            </form>
        @endif
    </div>
</section>
@endsection
