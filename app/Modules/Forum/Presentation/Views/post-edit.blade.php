@extends('forum::layout')

@section('title', 'Изменение сообщения — Форум')
@section('panel-title', $post->topic->section->name)

@section('content')
    <div class="forum-page">
        @include('forum::_heading')

        @if($forumMute)
            @include('forum::_mute-notice')
        @else
            <div class="forum-form-box">
                <h3 class="forum-form-box__title">Изменение сообщения</h3>
                <form action="{{ route('forum.post.update', ['id' => $post->id]) }}" method="post">
                    @csrf
                    @method('PUT')
                    <div class="forum-field">
                        <textarea id="forum-editor" name="body">{{ old('body', $post->body) }}</textarea>
                        @error('body')<div class="forum-error">{{ $message }}</div>@enderror
                    </div>
                    <button type="submit" class="forum-submit">Сохранить</button>
                    <a href="{{ route('forum.topic', ['id' => $post->topic_id]) }}" class="forum-cancel">Отмена</a>
                </form>
            </div>
            @include('forum::_editor')
        @endif
    </div>
@endsection
