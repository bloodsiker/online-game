@extends('admin.layout.base')
@section('title', 'Рецепты: '.$skill->name)
@section('body')
    <section class="card">
        <header class="card-header"><h2 class="card-title">Рецепты профессии «{{ $skill->name }}» ({{ $recipes->count() }})</h2></header>
        <div class="card-body">
            <div class="mb-3">
                <a href="{{ route('admin.skill.info', $skill->id) }}" class="btn btn-default btn-sm">К навыку</a>
                <button type="submit" form="profession-recipe-order" id="profession-order-save" class="btn btn-primary btn-sm" disabled>Сохранить порядок</button>
                <span class="text-muted">Перетащите строки за ↕ или используйте ↑ ↓ на маркере. Порядок применяется в игре.</span>
            </div>
            @if($errors->any())<div class="alert alert-danger">{{ $errors->first() }}</div>@endif
            @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
            <div class="table-responsive">
                <table class="table table-bordered table-hover">
                    <thead><tr><th width="35"></th><th width="65">ID</th><th>Рецепт</th><th width="120">Навык</th><th>Результат</th><th>Использование</th></tr></thead>
                    <tbody id="profession-recipe-rows">
                    @forelse($recipes as $recipe)
                        <tr data-recipe-row="{{ $recipe->id }}">
                            <td><button type="button" class="btn btn-xs btn-default profession-recipe-handle" draggable="true" style="cursor:grab" aria-label="Переместить {{ $recipe->itemInfo->name }}" title="Перетащите или нажмите ↑ ↓">↕</button></td>
                            <td>{{ $recipe->id }}</td>
                            <td>
                                @if($recipe->itemInfo->image)<img src="{{ $recipe->itemInfo->image }}" width="36" height="36" alt="">@endif
                                <a href="{{ route('admin.item.info', $recipe->share_item_id) }}">{{ $recipe->itemInfo->name }} [{{ $recipe->share_item_id }}]</a>
                            </td>
                            <td>{{ max(1, (int) $recipe->itemInfo->skill_lvl) }}</td>
                            <td>
                                @if($recipe->is_random_result)Случайный результат
                                @elseif($recipe->kraftItem)<a href="{{ route('admin.item.info', $recipe->kraft_item_id) }}">{{ $recipe->kraftItem->name }}</a>
                                @else — @endif
                            </td>
                            <td>{{ $recipe->isLearnable() ? 'Изучаемый' : 'Одноразовый' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center text-muted">У этой профессии пока нет рецептов.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </section>
    <form id="profession-recipe-order" action="{{ route('admin.skill.recipes.order', $skill->id) }}" method="post" class="d-none">
        @csrf
        <div id="profession-recipe-order-values">
            @foreach($recipes as $recipe)<input type="hidden" name="recipe_ids[]" value="{{ $recipe->id }}">@endforeach
        </div>
    </form>
@endsection
@push('footer_scripts')
<script>
(() => {
    const body = document.getElementById('profession-recipe-rows');
    const values = document.getElementById('profession-recipe-order-values');
    const save = document.getElementById('profession-order-save');
    let dragged = null;
    const rows = () => [...body.querySelectorAll('[data-recipe-row]')];
    const initial = rows().map(row => row.dataset.recipeRow).join(',');
    function update() {
        const ids = rows().map(row => row.dataset.recipeRow);
        values.replaceChildren(...ids.map(id => {
            const input = document.createElement('input');
            input.type = 'hidden'; input.name = 'recipe_ids[]'; input.value = id;
            return input;
        }));
        save.disabled = ids.join(',') === initial;
    }
    body.addEventListener('dragstart', event => {
        const handle = event.target.closest('.profession-recipe-handle');
        if (!handle) { event.preventDefault(); return; }
        dragged = handle.closest('[data-recipe-row]');
        dragged.style.opacity = '0.5';
        event.dataTransfer.effectAllowed = 'move';
        event.dataTransfer.setData('text/plain', dragged.dataset.recipeRow);
    });
    body.addEventListener('dragover', event => {
        const target = event.target.closest('[data-recipe-row]');
        if (!dragged || !target || target === dragged) return;
        event.preventDefault();
        const rect = target.getBoundingClientRect();
        body.insertBefore(dragged, event.clientY < rect.top + rect.height / 2 ? target : target.nextSibling);
    });
    body.addEventListener('drop', event => { if (dragged) { event.preventDefault(); update(); } });
    body.addEventListener('dragend', () => { if (dragged) dragged.style.opacity = ''; dragged = null; update(); });
    body.addEventListener('keydown', event => {
        const handle = event.target.closest('.profession-recipe-handle');
        if (!handle || !['ArrowUp', 'ArrowDown'].includes(event.key)) return;
        event.preventDefault();
        const row = handle.closest('[data-recipe-row]');
        if (event.key === 'ArrowUp' && row.previousElementSibling) body.insertBefore(row, row.previousElementSibling);
        if (event.key === 'ArrowDown' && row.nextElementSibling) body.insertBefore(row.nextElementSibling, row);
        handle.focus(); update();
    });
})();
</script>
@endpush
