<section class="card"><div class="card-body">
    <form action="{{ $action }}" method="post">
        @csrf
        @include('admin.partials.content-translations', ['prefix' => 'clan-skill-definition', 'content' => $skill, 'fields' => [
            'name' => ['label' => 'Название'],
            'description' => ['label' => 'Описание', 'multiline' => true],
        ]])
        <div class="row">
            <div class="col-md-3"><div class="form-group"><label>Максимальный уровень</label><input class="form-control" type="number" min="1" name="max_level" value="{{ old('max_level', $skill?->max_level ?? 5) }}" required></div></div>
            <div class="col-md-3"><div class="form-group"><label>Порядок</label><input class="form-control" type="number" min="0" name="sort_order" value="{{ old('sort_order', $skill?->sort_order ?? 0) }}" required></div></div>
        </div>
        <div class="form-group"><label>Иконка (URL или путь)</label><input class="form-control" name="icon" value="{{ old('icon', $skill?->icon) }}"></div>
        <button class="btn btn-primary">{{ $skill ? 'Сохранить' : 'Создать' }}</button>
        <a href="{{ route('admin.clan_skills') }}" class="btn btn-success">Назад</a>
    </form>
</div></section>
