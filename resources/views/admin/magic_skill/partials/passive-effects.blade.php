@php
    $passiveEffects = array_values($passiveEffects ?? []);
@endphp

<section class="card mt-3">
    <header class="card-header"><h2 class="card-title">Пассивные бонусы</h2></header>
    <div class="card-body">
        <p class="text-muted mb-3">Используются пассивными навыками, в том числе выданными кланом. Активные заклинания эти бонусы не применяют.</p>
        <div id="passive-effects">
            @foreach($passiveEffects as $index => $effect)
                <div class="row align-items-end passive-effect-row mb-2">
                    <div class="col-md-5"><label>Характеристика</label><select class="form-control" name="passive_effects[{{ $index }}][type]"><option value="">—</option>@foreach(\App\Modules\Clan\Domain\Enums\ClanSkillEffectType::cases() as $type)<option value="{{ $type->value }}" @selected(($effect['type'] ?? '') === $type->value)>{{ $type->label() }}</option>@endforeach</select></div>
                    <div class="col-md-3"><label>Значение</label><input class="form-control" type="number" step="0.01" name="passive_effects[{{ $index }}][value]" value="{{ $effect['value'] ?? 0 }}"></div>
                    <div class="col-md-2"><label>Режим</label><select class="form-control" name="passive_effects[{{ $index }}][is_percent]"><option value="0" @selected(empty($effect['is_percent']))>Число</option><option value="1" @selected(!empty($effect['is_percent']))>Проценты</option></select></div>
                    <div class="col-md-2"><button type="button" class="btn btn-danger btn-sm remove-passive-effect">Удалить</button></div>
                </div>
            @endforeach
        </div>
        <button type="button" id="add-passive-effect" class="btn btn-default btn-sm">Добавить бонус</button>
    </div>
</section>

<template id="passive-effect-template">
    <div class="row align-items-end passive-effect-row mb-2">
        <div class="col-md-5"><label>Характеристика</label><select class="form-control" name="passive_effects[__INDEX__][type]"><option value="">—</option>@foreach(\App\Modules\Clan\Domain\Enums\ClanSkillEffectType::cases() as $type)<option value="{{ $type->value }}">{{ $type->label() }}</option>@endforeach</select></div>
        <div class="col-md-3"><label>Значение</label><input class="form-control" type="number" step="0.01" name="passive_effects[__INDEX__][value]" value="0"></div>
        <div class="col-md-2"><label>Режим</label><select class="form-control" name="passive_effects[__INDEX__][is_percent]"><option value="0">Число</option><option value="1">Проценты</option></select></div>
        <div class="col-md-2"><button type="button" class="btn btn-danger btn-sm remove-passive-effect">Удалить</button></div>
    </div>
</template>

<script>
    (function () {
        const list = document.getElementById('passive-effects');
        const template = document.getElementById('passive-effect-template');
        const addButton = document.getElementById('add-passive-effect');
        if (!list || !template || !addButton) return;

        let index = {{ count($passiveEffects) }};
        addButton.addEventListener('click', function () {
            list.insertAdjacentHTML('beforeend', template.innerHTML.replaceAll('__INDEX__', index++));
        });
        list.addEventListener('click', function (event) {
            if (event.target.classList.contains('remove-passive-effect')) {
                event.target.closest('.passive-effect-row').remove();
            }
        });
    })();
</script>
