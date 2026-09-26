@php
    $selectedLocations = collect(old('location_ids', $event?->locations?->pluck('id')->all() ?? []))->map(fn($id) => (int) $id)->all();
    $stageRows = collect(old('stages', $event?->stages?->map(fn($stage) => [
        'id' => $stage->id,
        'title' => $stage->title,
        'objective_type' => $stage->objective_type->value,
        'targets' => $stage->targets->map(fn($target) => [
            'id' => $target->id,
            'share_item_id' => $target->share_item_id,
            'monster_id' => $target->monster_id,
            'spawn_weight' => $target->spawn_weight,
            'max_active' => $target->max_active,
        ])->all(),
        'global_limit' => $stage->global_limit,
        'player_limit' => $stage->player_limit,
        'spawn_limit' => $stage->spawn_limit,
        'respawn_seconds' => $stage->respawn_seconds,
        'item_lifetime_minutes' => $stage->item_lifetime_minutes,
        'influence_per_item' => $stage->influence_per_item,
    ])->all() ?? [[
        'title' => 'Этап 1', 'objective_type' => 'collect',
        'targets' => [['share_item_id' => null, 'monster_id' => null, 'spawn_weight' => 100, 'max_active' => null]],
        'global_limit' => 100, 'player_limit' => 5, 'spawn_limit' => 10, 'respawn_seconds' => 60,
        'item_lifetime_minutes' => 1440, 'influence_per_item' => 1,
    ]]));
@endphp
<div class="row">
    <div class="col-md-8"><div class="form-group mb-3"><label>Название</label><input class="form-control" name="title" required value="{{ old('title', $event?->title) }}"></div></div>
    <div class="col-md-4"><div class="form-group mb-3"><label class="d-block">Активно</label><input type="checkbox" name="is_active" value="1" @checked(old('is_active', $event?->is_active ?? true))></div></div>
</div>
<div class="form-group mb-3">
    <label>Описание</label>
    <textarea id="world-event-description-editor" class="form-control" rows="10" name="description">{{ old('description', $event?->description) }}</textarea>
</div>
<div class="form-group mb-3">
    <label>Картинка события</label>
    @if($event?->image)
        <div class="mb-2">
            <img src="{{ $event->image }}" alt="{{ $event->title }}" style="width:120px;height:120px;object-fit:contain;border:1px solid #ddd;padding:4px;background:#fff;">
        </div>
    @endif
    <input type="file" class="form-control" name="image" accept="image/*">
    <small class="form-text text-muted">Изображение будет сохранено в storage. Максимальный размер — 4 МБ.</small>
    @if($event?->image)
        <div class="checkbox-custom checkbox-default mt-2">
            <input type="checkbox" id="delete-world-event-image" name="delete_image" value="1">
            <label for="delete-world-event-image">Удалить текущую картинку</label>
        </div>
    @endif
</div>
<hr><h4>Место проведения</h4>
<div class="row">
    <div class="col-md-6"><div class="form-group mb-3"><label>Карта проведения</label><select id="world-event-map" class="form-control" name="map_id" required><option value="">—</option>@foreach($maps as $map)<option value="{{ $map->id }}" data-parent="{{ $map->parent_id }}" @selected((int) old('map_id', $event?->map_id) === $map->id)>[{{ $map->id }}] {{ $map->name }}</option>@endforeach</select><small>На этой карте будут появляться предметы или монстры события.</small></div></div>
    <div class="col-md-6"><div class="form-group mb-3"><label>Начислять влияние территории</label><select id="world-event-influence-map" class="form-control" name="influence_map_id" required><option value="">—</option>@foreach($maps as $map)<option value="{{ $map->id }}" @selected((int) old('influence_map_id', $event?->influence_map_id ?? $event?->map_id) === $map->id)>[{{ $map->id }}] {{ $map->name }}</option>@endforeach</select><small>Для дочерней карты автоматически предлагается её родитель, но выбор можно изменить.</small></div></div>
</div>
<div class="form-group mb-3"><label>Локации появления</label><select id="world-event-locations" class="form-control" name="location_ids[]" multiple>@foreach($locations as $location)<option data-map="{{ $location->map_id }}" value="{{ $location->id }}" @selected(in_array($location->id, $selectedLocations, true))>[{{ $location->id }}] {{ $location->name }}</option>@endforeach</select><small>Если ничего не выбрано, цели будут случайно появляться на всех локациях выбранной карты проведения.</small></div>
<div class="form-group mb-3"><label>Название территории для текста влияния</label><input class="form-control" name="influence_name" value="{{ old('influence_name', $event?->influence_name) }}" placeholder="Например: Дартронге"><small>Будет показано: «увеличивать ваше влияние в Дартронге». Если пусто — используется название выбранной территории влияния.</small></div>

<hr>
<div class="d-flex justify-content-between align-items-center mb-2">
    <div><h4 class="mb-0">Этапы события</h4><small>После выполнения общей цели автоматически начинается следующий этап. Личный лимит считается отдельно для каждого этапа.</small></div>
    <button type="button" class="btn btn-info btn-sm" id="world-event-stage-add">+ Добавить этап</button>
</div>
<div id="world-event-stages">
    @foreach($stageRows as $index => $stage)
        <div class="card mb-3 world-event-stage-row">
            <div class="card-header d-flex justify-content-between align-items-center">
                <b class="world-event-stage-number">Этап {{ $index + 1 }}</b>
                <button type="button" class="btn btn-danger btn-xs world-event-stage-remove">Удалить</button>
            </div>
            <div class="card-body">
                @if(!empty($stage['id']))<input type="hidden" name="stages[{{ $index }}][id]" value="{{ $stage['id'] }}">@endif
                <div class="row">
                    <div class="col-md-7"><div class="form-group"><label>Название этапа</label><input class="form-control" name="stages[{{ $index }}][title]" required value="{{ $stage['title'] ?? 'Этап '.($index + 1) }}"></div></div>
                    <div class="col-md-5"><div class="form-group"><label>Тип цели</label><select class="form-control world-event-stage-objective" name="stages[{{ $index }}][objective_type]" required>@foreach($objectiveTypes as $type)<option value="{{ $type->value }}" @selected(($stage['objective_type'] ?? 'collect') === $type->value)>{{ $type->label() }}</option>@endforeach</select></div></div>
                </div>
                <div class="d-flex justify-content-between align-items-center mb-2"><div><b>Возможные цели</b><br><small>Вес задаёт относительную частоту появления. Пустой максимум — без отдельного ограничения.</small></div><button type="button" class="btn btn-info btn-xs world-event-target-add">+ Добавить цель</button></div>
                <div class="world-event-targets" data-next-target-index="{{ count($stage['targets'] ?? []) }}">
                    @foreach(($stage['targets'] ?? []) as $targetIndex => $target)
                        @include('event::admin.world-events._target-row', ['stageIndex' => $index, 'targetIndex' => $targetIndex, 'target' => $target])
                    @endforeach
                </div>
                <div class="row">
                    @foreach([
                        'global_limit' => ['Общая цель', 100], 'player_limit' => ['Лимит игрока', 5],
                        'spawn_limit' => ['Одновременно на карте', 10], 'respawn_seconds' => ['Респавн, секунд', 60],
                        'item_lifetime_minutes' => ['Срок жизни предмета, минут', 1440], 'influence_per_item' => ['Влияние за цель', 1],
                    ] as $field => [$label, $default])
                        <div class="col-md-2"><div class="form-group"><label>{{ $label }}</label><input type="number" min="{{ $field === 'influence_per_item' ? 0 : 1 }}" class="form-control" name="stages[{{ $index }}][{{ $field }}]" required value="{{ $stage[$field] ?? $default }}"></div></div>
                    @endforeach
                </div>
            </div>
        </div>
    @endforeach
</div>

<template id="world-event-stage-template">
    <div class="card mb-3 world-event-stage-row">
        <div class="card-header d-flex justify-content-between align-items-center"><b class="world-event-stage-number"></b><button type="button" class="btn btn-danger btn-xs world-event-stage-remove">Удалить</button></div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-7"><div class="form-group"><label>Название этапа</label><input class="form-control world-event-stage-title" name="stages[__INDEX__][title]" required></div></div>
                <div class="col-md-5"><div class="form-group"><label>Тип цели</label><select class="form-control world-event-stage-objective" name="stages[__INDEX__][objective_type]" required>@foreach($objectiveTypes as $type)<option value="{{ $type->value }}">{{ $type->label() }}</option>@endforeach</select></div></div>
            </div>
            <div class="d-flex justify-content-between align-items-center mb-2"><div><b>Возможные цели</b><br><small>Вес задаёт относительную частоту появления. Пустой максимум — без отдельного ограничения.</small></div><button type="button" class="btn btn-info btn-xs world-event-target-add">+ Добавить цель</button></div>
            <div class="world-event-targets" data-next-target-index="1">
                @include('event::admin.world-events._target-row', ['stageIndex' => '__INDEX__', 'targetIndex' => 0, 'target' => []])
            </div>
            <div class="row">
                @foreach(['global_limit' => ['Общая цель', 100], 'player_limit' => ['Лимит игрока', 5], 'spawn_limit' => ['Одновременно на карте', 10], 'respawn_seconds' => ['Респавн, секунд', 60], 'item_lifetime_minutes' => ['Срок жизни предмета, минут', 1440], 'influence_per_item' => ['Влияние за цель', 1]] as $field => [$label, $default])
                    <div class="col-md-2"><div class="form-group"><label>{{ $label }}</label><input type="number" min="{{ $field === 'influence_per_item' ? 0 : 1 }}" class="form-control" name="stages[__INDEX__][{{ $field }}]" required value="{{ $default }}"></div></div>
                @endforeach
            </div>
        </div>
    </div>
</template>
<template id="world-event-target-template">
    @include('event::admin.world-events._target-row', ['stageIndex' => '__STAGE__', 'targetIndex' => '__TARGET__', 'target' => []])
</template>
<hr><h4>Расписание</h4>
<div class="row">
    <div class="col-md-4"><div class="form-group mb-3"><label>Первый / следующий запуск</label><input type="datetime-local" class="form-control" name="next_start_at" value="{{ old('next_start_at', $event?->next_start_at?->format('Y-m-d\TH:i')) }}"><small>Пусто — только ручной запуск</small></div></div>
    <div class="col-md-4"><div class="form-group mb-3"><label>Повторять каждые, минут</label><input type="number" min="1" class="form-control" name="repeat_interval_minutes" value="{{ old('repeat_interval_minutes', $event?->repeat_interval_minutes) }}"><small>Пусто — одноразовое событие</small></div></div>
    <div class="col-md-4"><div class="form-group mb-3"><label>Продолжительность, минут</label><input type="number" min="1" class="form-control" name="duration_minutes" required value="{{ old('duration_minutes', $event?->duration_minutes ?? 60) }}"></div></div>
</div>
@if($errors->any())<div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
<button class="btn btn-success" type="submit">Сохранить</button>
@include('admin.layout.summernote', [
    'selector' => '#world-event-description-editor',
    'height' => 260,
    'placeholder' => 'Введите описание события',
    'toolbarPreset' => 'extended',
    'imageUploadUrl' => route('admin.event.world-events.upload-image'),
])
@push('footer_scripts')
<script>
$('#world-event-map, #world-event-influence-map, #world-event-locations').select2({theme:'bootstrap'});
function initStageRow(row) {
    var $row = $(row);
    $row.find('[data-target-item], [data-target-monster]').select2({theme:'bootstrap', width:'100%'});
    toggleStageTarget(row);
}
function toggleStageTarget(row) {
    var $row = $(row);
    var isCollect = $row.find('.world-event-stage-objective').val() === 'collect';
    $row.find('.world-event-target-item').toggle(isCollect).find('select').prop('required', isCollect);
    $row.find('.world-event-target-monster').toggle(!isCollect).find('select').prop('required', !isCollect);
}
function renumberStages() {
    $('#world-event-stages .world-event-stage-row').each(function (index) {
        $(this).find('.world-event-stage-number').text('Этап ' + (index + 1));
    });
}
$('#world-event-stages .world-event-stage-row').each(function () { initStageRow(this); });
$('#world-event-stages').on('change', '.world-event-stage-objective', function () { toggleStageTarget($(this).closest('.world-event-stage-row')); });
$('#world-event-stages').on('click', '.world-event-stage-remove', function () {
    if ($('#world-event-stages .world-event-stage-row').length <= 1) return;
    $(this).closest('.world-event-stage-row').remove();
    renumberStages();
});
$('#world-event-stages').on('click', '.world-event-target-remove', function () {
    var targets = $(this).closest('.world-event-targets');
    if (targets.find('.world-event-target-row').length <= 1) return;
    $(this).closest('.world-event-target-row').remove();
});
$('#world-event-stages').on('click', '.world-event-target-add', function () {
    var stageRow = $(this).closest('.world-event-stage-row');
    var targets = stageRow.find('.world-event-targets');
    var stageIndex = stageRow.find('.world-event-stage-objective').attr('name').match(/stages\[(\d+)\]/)[1];
    var targetIndex = Number(targets.attr('data-next-target-index'));
    targets.attr('data-next-target-index', targetIndex + 1);
    var html = document.getElementById('world-event-target-template').innerHTML
        .replaceAll('__STAGE__', stageIndex)
        .replaceAll('__TARGET__', targetIndex);
    targets.append(html);
    var targetRow = targets.find('.world-event-target-row').last();
    targetRow.find('[data-target-item], [data-target-monster]').select2({theme:'bootstrap', width:'100%'});
    toggleStageTarget(stageRow);
});
var nextStageIndex = {{ $stageRows->count() }};
$('#world-event-stage-add').on('click', function () {
    var index = nextStageIndex++;
    var html = document.getElementById('world-event-stage-template').innerHTML.replaceAll('__INDEX__', index);
    $('#world-event-stages').append(html);
    var row = $('#world-event-stages .world-event-stage-row').last();
    row.find('.world-event-stage-title').val('Этап ' + $('#world-event-stages .world-event-stage-row').length);
    initStageRow(row);
    renumberStages();
});
var influenceMapChanged = Boolean($('#world-event-influence-map').val());
$('#world-event-influence-map').on('change', function () { influenceMapChanged = true; });
$('#world-event-map').on('change', function () {
    var mapId = this.value;
    $('#world-event-locations option').each(function () { this.disabled = !!mapId && this.dataset.map !== mapId; });
    $('#world-event-locations').trigger('change.select2');
    if (!influenceMapChanged && mapId) {
        var parentId = $(this).find('option:selected').data('parent');
        $('#world-event-influence-map').val(parentId || mapId).trigger('change.select2');
    }
}).trigger('change');
</script>
@endpush
