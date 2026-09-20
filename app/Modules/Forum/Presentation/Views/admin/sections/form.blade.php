@extends('admin.layout.base')

@section('title', $section ? 'Категория форума: '.$section->name : 'Новая категория форума')

@section('body')
    <section class="card">
        <div class="card-body">
            @if($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
                    </ul>
                </div>
            @endif

            <form method="post" action="{{ $section ? route('admin.forum.sections.update', $section) : route('admin.forum.sections.store') }}" data-floating-save-form>
                @csrf
                @if($section) @method('PUT') @endif

                <div class="row">
                    <div class="col-md-8">
                        <div class="form-group">
                            <label class="col-form-label" for="forum-section-name">Название</label>
                            <input id="forum-section-name" class="form-control" name="name" maxlength="255" value="{{ old('name', $section?->name) }}" required>
                        </div>
                        <div class="form-group">
                            <label class="col-form-label" for="forum-section-slug">Адрес (slug)</label>
                            <input id="forum-section-slug" class="form-control" name="slug" maxlength="255" value="{{ old('slug', $section?->slug) }}" placeholder="Создастся автоматически">
                        </div>
                        <div class="form-group">
                            <label class="col-form-label" for="forum-section-description">Описание</label>
                            <textarea id="forum-section-description" class="form-control" name="description" rows="7">{{ old('description', $section?->description) }}</textarea>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="form-group">
                            <label class="col-form-label" for="forum-section-parent">Родительская категория</label>
                            <select id="forum-section-parent" class="form-control" name="parent_id">
                                <option value="">Корневая категория</option>
                                @foreach($parentSections as $parentSection)
                                    <option value="{{ $parentSection->id }}" @selected((string) old('parent_id', $section?->parent_id) === (string) $parentSection->id)>
                                        {{ $parentSection->name }}
                                    </option>
                                @endforeach
                            </select>
                            <small class="form-text text-muted">Поддерживается два уровня: категория и её подразделы.</small>
                        </div>
                        <div class="form-group">
                            <label class="col-form-label" for="forum-section-order">Порядок</label>
                            <input id="forum-section-order" type="number" min="0" max="32767" class="form-control" name="sort_order" value="{{ old('sort_order', $section?->sort_order ?? 0) }}">
                        </div>
                        <div class="checkbox-custom checkbox-default mt-3">
                            <input type="checkbox" id="forum-section-active" name="is_active" value="1" @checked(old('is_active', $section?->is_active ?? true))>
                            <label for="forum-section-active">Показывать игрокам</label>
                        </div>
                        <hr>
                        <div class="checkbox-custom checkbox-default mt-3">
                            <input type="checkbox" id="forum-section-allow-topics" name="allow_topics" value="1" @checked(old('allow_topics', $section?->allow_topics ?? true))>
                            <label for="forum-section-allow-topics">Разрешить игрокам создавать темы</label>
                        </div>
                        <div class="checkbox-custom checkbox-default mt-3">
                            <input type="checkbox" id="forum-section-allow-comments" name="allow_comments" value="1" @checked(old('allow_comments', $section?->allow_comments ?? true))>
                            <label for="forum-section-allow-comments">Разрешить игрокам писать комментарии</label>
                        </div>
                        <small class="form-text text-muted">Администраторы могут публиковать независимо от этих настроек.</small>
                    </div>
                </div>

                <div class="text-end mt-3">
                    <a href="{{ route('admin.forum.sections.index') }}" class="btn btn-default">Назад</a>
                    <button type="submit" class="btn btn-primary">{{ $section ? 'Сохранить' : 'Создать' }}</button>
                </div>
            </form>
        </div>
    </section>
@endsection
