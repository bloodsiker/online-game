@extends('admin.layout.base')

@section('title', $category ? 'Категория: '.$category->name : 'Новая категория Библиотеки')

@section('body')
    <section class="card">
        <div class="card-body">
            @if($errors->any())
                <div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
            @endif

            <form method="post" enctype="multipart/form-data" action="{{ $category ? route('admin.library.categories.update', $category) : route('admin.library.categories.store') }}" data-floating-save-form>
                @csrf
                @if($category) @method('PUT') @endif

                <div class="row">
                    <div class="col-md-8">
                        <div class="form-group">
                            <label class="col-form-label">Название</label>
                            <input class="form-control" name="name" value="{{ old('name', $category?->name) }}" required>
                        </div>
                        <div class="form-group">
                            <label class="col-form-label">Адрес (slug)</label>
                            <input class="form-control" name="slug" value="{{ old('slug', $category?->slug) }}" placeholder="Создастся автоматически">
                        </div>
                        <div class="form-group">
                            <label class="col-form-label">Описание</label>
                            <textarea class="form-control" name="description" rows="5">{{ old('description', $category?->description) }}</textarea>
                        </div>
                        <div class="form-group">
                            <label class="col-form-label">Тип содержимого</label>
                            <select class="form-control" name="content_type" required>
                                @foreach($contentTypes as $contentType)
                                    <option value="{{ $contentType->value }}" @selected(old('content_type', $category?->content_type?->value ?? 'articles') === $contentType->value)>
                                        {{ $contentType->label() }}
                                    </option>
                                @endforeach
                            </select>
                            <small class="form-text text-muted">Каталог монстров формируется автоматически из игровых данных и не требует статей.</small>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label class="col-form-label">Родительская категория</label>
                            <select class="form-control" name="parent_id">
                                <option value="">Корневая категория</option>
                                @foreach($categories as $item)
                                    <option value="{{ $item->id }}" @selected((string) old('parent_id', $category?->parent_id) === (string) $item->id)>{{ $item->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="col-form-label">Порядок</label>
                            <input type="number" min="0" class="form-control" name="sort_order" value="{{ old('sort_order', $category?->sort_order ?? 0) }}">
                        </div>
                        <div class="form-group">
                            <label class="col-form-label">Изображение</label>
                            <input type="file" class="form-control" name="image" accept="image/*">
                            @if($category?->image)<img src="{{ asset($category->image) }}" alt="" class="mt-2" style="max-width:160px;max-height:100px">@endif
                        </div>
                        <div class="checkbox-custom checkbox-default mt-3">
                            <input type="checkbox" id="is-active" name="is_active" value="1" @checked(old('is_active', $category?->is_active ?? true))>
                            <label for="is-active">Показывать игрокам</label>
                        </div>
                    </div>
                </div>

                <div class="text-end mt-3">
                    <a href="{{ route('admin.library.categories.index') }}" class="btn btn-default">Назад</a>
                    <button class="btn btn-primary">{{ $category ? 'Сохранить' : 'Создать' }}</button>
                </div>
            </form>
        </div>
    </section>
@endsection
