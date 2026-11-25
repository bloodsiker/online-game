@extends('admin.layout.base')

@section('title') Тайные знания @endsection

@section('body')
<div class="row"><div class="col-md-12"><section class="card"><div class="card-body">
    <h4>Настройки изучения находок</h4>
    <form method="post" action="{{ route('admin.secret_knowledge.settings') }}" class="row align-items-end">
        @csrf
        <div class="col-md-4 mb-2"><label>НПС</label><select class="form-control secret-ajax-select" data-url="{{ route('admin.api.npcs') }}" data-placeholder="Найти НПС" name="npc_id" required>@if($settings?->npc)<option value="{{ $settings->npc->id }}" selected>[{{ $settings->npc->id }}] {{ $settings->npc->name }} (локация {{ $settings->npc->location_id }})</option>@endif</select></div>
        <div class="col-md-4 mb-2"><label>Ворох тайных знаний</label><select class="form-control secret-ajax-select" data-url="{{ route('admin.api.items') }}" data-placeholder="Найти предмет" name="bundle_share_item_id" required>@if($settings?->bundle)<option value="{{ $settings->bundle->id }}" selected>[{{ $settings->bundle->id }}] {{ $settings->bundle->name }}</option>@endif</select></div>
        <div class="col-md-2 mb-2"><label>Шанс успеха, %</label><input class="form-control" type="number" name="analysis_success_chance" min="0" max="100" value="{{ $settings?->analysis_success_chance ?? 25 }}" required></div>
        <div class="col-md-1 mb-2"><label>Знаний от</label><input class="form-control" type="number" name="knowledge_gain_min" min="1" value="{{ $settings?->knowledge_gain_min ?? 1 }}" required></div>
        <div class="col-md-1 mb-2"><label>до</label><input class="form-control" type="number" name="knowledge_gain_max" min="1" value="{{ $settings?->knowledge_gain_max ?? 1 }}" required></div>
        <div class="col-md-12 mb-2"><label><input type="checkbox" name="is_active" value="1" @checked($settings?->is_active)> Включить механику</label></div>
        <div class="col-md-12"><button class="btn btn-primary">Сохранить настройки</button></div>
    </form>
</div></section></div></div>

<div class="row mt-3"><div class="col-md-12"><section class="card"><div class="card-body">
    <h4>Пособия и части боссов</h4>
    <p class="text-muted">Ворох тайных знаний добавьте в таблицу дропа нужных монстров через их настройки. Пособие должно быть предметом типа «Пособие о боссе», часть босса — предметом типа «Квест». Для продажи пособий добавьте их в обычный магазин, привязанный к Финко.</p>
    <form method="post" action="{{ route('admin.secret_knowledge.manuals.store') }}" class="row align-items-end border-bottom pb-3 mb-3">
        @csrf
        <div class="col-md-3 mb-2"><label>Пособие</label><select class="form-control secret-ajax-select" data-url="{{ route('admin.api.items') }}" data-type="boss_manual" data-placeholder="Найти пособие" name="manual_share_item_id" required></select></div>
        <div class="col-md-3 mb-2"><label>Босс</label><select class="form-control secret-ajax-select" data-url="{{ route('admin.api.monsters') }}" data-placeholder="Найти босса" name="monster_id" required></select></div>
        <div class="col-md-2 mb-2"><label>Порог знаний</label><input class="form-control" type="number" name="required_knowledge" min="0" value="0" required></div>
        <div class="col-md-3 mb-2"><label>Квестовая часть</label><select class="form-control secret-ajax-select" data-url="{{ route('admin.api.items') }}" data-type="quest" data-placeholder="Найти часть босса" name="boss_part_share_item_id" required></select></div>
        <div class="col-md-1 mb-2"><label><input type="checkbox" name="is_active" value="1" checked> Активно</label></div>
        <div class="col-md-12"><button class="btn btn-primary">Добавить пособие</button></div>
    </form>
    @foreach($manuals as $manual)
        <form method="post" action="{{ route('admin.secret_knowledge.manuals.update', $manual) }}" class="row align-items-end border-bottom py-3">
            @csrf
            <div class="col-md-3 mb-2"><label>Пособие</label><select class="form-control secret-ajax-select" data-url="{{ route('admin.api.items') }}" data-type="boss_manual" data-placeholder="Найти пособие" name="manual_share_item_id" required><option value="{{ $manual->manual_share_item_id }}" selected>[{{ $manual->manual_share_item_id }}] {{ $manual->manual?->name }}</option></select></div>
            <div class="col-md-3 mb-2"><label>Босс</label><select class="form-control secret-ajax-select" data-url="{{ route('admin.api.monsters') }}" data-placeholder="Найти босса" name="monster_id" required><option value="{{ $manual->monster_id }}" selected>[{{ $manual->monster_id }}] {{ $manual->monster?->name }} ({{ $manual->monster?->lvl }} ур.)</option></select></div>
            <div class="col-md-2 mb-2"><label>Порог знаний</label><input class="form-control" type="number" name="required_knowledge" min="0" value="{{ $manual->required_knowledge }}" required></div>
            <div class="col-md-3 mb-2"><label>Квестовая часть</label><select class="form-control secret-ajax-select" data-url="{{ route('admin.api.items') }}" data-type="quest" data-placeholder="Найти часть босса" name="boss_part_share_item_id" required><option value="{{ $manual->boss_part_share_item_id }}" selected>[{{ $manual->boss_part_share_item_id }}] {{ $manual->bossPart?->name }}</option></select></div>
            <div class="col-md-1 mb-2"><label><input type="checkbox" name="is_active" value="1" @checked($manual->is_active)> Активно</label></div>
            <div class="col-md-12 d-flex align-items-center"><span class="text-muted mr-2">{{ $manual->manual?->name }} → {{ $manual->monster?->name }} → {{ $manual->bossPart?->name }}</span><button class="btn btn-sm btn-success">Сохранить</button></div>
        </form>
        <form method="post" action="{{ route('admin.secret_knowledge.manuals.delete', $manual) }}" class="mt-1 mb-2" onsubmit="return confirm('Удалить настройку пособия?')">@csrf @method('DELETE')<button class="btn btn-sm btn-danger">Удалить</button></form>
    @endforeach
</div></section></div></div>
@push('footer_scripts')
<script>
    $(function () {
        $('.secret-ajax-select').each(function () {
            const select = $(this);
            select.select2({
                theme: 'bootstrap',
                width: '100%',
                placeholder: select.data('placeholder'),
                allowClear: true,
                ajax: {
                    url: select.data('url'),
                    dataType: 'json',
                    delay: 250,
                    data: function (params) {
                        return { q: params.term, page: params.page || 1, type: select.data('type') || undefined };
                    },
                    processResults: function (data, params) {
                        params.page = params.page || 1;
                        return { results: data.results, pagination: { more: data.pagination.more } };
                    },
                    cache: true
                }
            });
        });
    });
</script>
@endpush
@endsection
