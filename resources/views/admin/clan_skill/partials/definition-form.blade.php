<section class="card"><div class="card-body">
    <form action="{{ $action }}" method="post">
        @csrf
        <div class="row">
            <div class="col-md-6"><div class="form-group"><label>Название</label><input class="form-control" name="name" value="{{ old('name', $skill?->name) }}" required></div></div>
            <div class="col-md-3"><div class="form-group"><label>Максимальный уровень</label><input class="form-control" type="number" min="1" name="max_level" value="{{ old('max_level', $skill?->max_level ?? 5) }}" required></div></div>
            <div class="col-md-3"><div class="form-group"><label>Порядок</label><input class="form-control" type="number" min="0" name="sort_order" value="{{ old('sort_order', $skill?->sort_order ?? 0) }}" required></div></div>
        </div>
        <div class="form-group"><label>Иконка (URL или путь)</label><input class="form-control" name="icon" value="{{ old('icon', $skill?->icon) }}"></div>
        <div class="form-group"><label>Описание</label><textarea class="form-control" name="description" rows="3">{{ old('description', $skill?->description) }}</textarea></div>
        <button class="btn btn-primary">{{ $skill ? 'Сохранить' : 'Создать' }}</button>
        <a href="{{ route('admin.clan_skills') }}" class="btn btn-success">Назад</a>
    </form>
</div></section>
