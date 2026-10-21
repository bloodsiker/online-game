@extends('admin.layout.base')
@section('title', $assembly->exists ? 'Редактирование сборки' : 'Новая сборка')
@section('body')
@php
    $componentRows = old('components', $assembly->exists ? $assembly->components->map(fn ($item) => ['share_item_id' => $item->id, 'count' => $item->pivot->count])->all() : []);
    $rewardRows = old('rewards', $assembly->exists ? $assembly->rewards->map(fn ($item) => ['share_item_id' => $item->id, 'count' => $item->pivot->count, 'weight' => $item->pivot->weight])->all() : []);
    $itemNames = $assembly->exists ? $assembly->components->concat($assembly->rewards)->pluck('name', 'id')->all() : [];
@endphp
<section class="card"><div class="card-body">
    @if($errors->any())<div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
    @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
    <form method="post" action="{{ $assembly->exists ? route('admin.item_assemblies.update', $assembly) : route('admin.item_assemblies.store') }}">
        @csrf
        <div class="row">
            <div class="col-md-6 mb-3"><label>Название (русский)</label><input class="form-control" name="name" maxlength="255" required value="{{ old('name', $assembly->getTranslation('name', 'ru')) }}"></div>
            <div class="col-md-6 mb-3"><label>Название (украинский)</label><input class="form-control" name="name_uk" maxlength="255" value="{{ old('name_uk', $assembly->getTranslation('name', 'uk', false)) }}"></div>
            <div class="col-md-4 mb-3"><label>Тип сборки</label><select class="form-control" name="type">@foreach($types as $type)<option value="{{ $type->value }}" @selected(old('type', $assembly->type?->value ?? 'misc') === $type->value)>{{ $type->label() }}</option>@endforeach</select></div>
            <div class="col-md-5 mb-3"><label>Режим результата</label><select class="form-control" name="result_mode" id="assembly-result-mode">@foreach($modes as $mode)<option value="{{ $mode->value }}" @selected(old('result_mode', $assembly->result_mode?->value ?? 'random') === $mode->value)>{{ $mode->label() }}</option>@endforeach</select></div>
            <div class="col-md-3 mb-3"><label>Порядок</label><input class="form-control" type="number" name="sort_order" min="0" max="1000000" value="{{ old('sort_order', $assembly->sort_order ?? 0) }}"></div>
        </div>
        <label class="mb-3"><input type="checkbox" name="is_active" value="1" @checked(old('is_active', $assembly->is_active))> Активна</label>
        <div class="alert alert-info">Сборка не требует рецепта или изучения. Компоненты расходуются только при успешной выдаче награды. Для фиксированного результата оставьте одну награду; для случайного задайте веса.</div>
        @foreach(['components' => ['Компоненты', $componentRows], 'rewards' => ['Возможные награды', $rewardRows]] as $group => [$title, $rows])
            <h4>{{ $title }}</h4>
            <div class="table-responsive"><table class="table table-bordered">
                <thead><tr><th>Предмет</th><th>Количество</th>@if($group === 'rewards')<th class="assembly-weight">Вес</th>@endif<th></th></tr></thead>
                <tbody id="assembly-{{ $group }}">@foreach($rows as $index => $row)@include('admin.item_assembly._row')@endforeach</tbody>
            </table></div>
            <button type="button" class="btn btn-default mb-4 assembly-add-row" data-group="{{ $group }}">Добавить предмет</button>
            <template id="assembly-template-{{ $group }}">@include('admin.item_assembly._row', ['index' => '__INDEX__', 'row' => []])</template>
        @endforeach
        <div class="mt-3"><button class="btn btn-primary">Сохранить</button> <a class="btn btn-default" href="{{ route('admin.item_assemblies.index') }}">Назад</a></div>
    </form>
</div></section>
@endsection
@push('footer_scripts')
<script>
$(function () {
    let index = Date.now();
    function initSelects(root) {
        $(root).find('.assembly-item-select').each(function () {
            if ($(this).hasClass('select2-hidden-accessible')) return;
            $(this).select2({theme: 'bootstrap', width: '100%', placeholder: 'Выберите предмет',
                ajax: {url: @js(route('admin.api.items')), dataType: 'json', delay: 250,
                    data: function (params) {return {q: params.term, page: params.page || 1};},
                    processResults: function (data) {return {results: data.results, pagination: data.pagination};}
                }
            });
        });
    }
    function updateMode() {$('.assembly-weight').toggle($('#assembly-result-mode').val() === 'random');}
    initSelects(document);
    updateMode();
    $('#assembly-result-mode').on('change', updateMode);
    $('.assembly-add-row').on('click', function () {
        const group = this.dataset.group;
        const template = document.getElementById('assembly-template-' + group);
        const body = document.getElementById('assembly-' + group);
        body.insertAdjacentHTML('beforeend', template.innerHTML.replaceAll('__INDEX__', String(index++)));
        initSelects(body);
        updateMode();
    });
    $(document).on('click', '.assembly-remove-row', function () {$(this).closest('tr').remove();});
});
</script>
@endpush
