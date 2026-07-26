@extends('admin.layout.base')

@section('title')
    Локация: {{ $location->name }}
@endsection

@section('body')

    <form action="{{ route('admin.location.info', $location->id) }}" method="post">
        {{ csrf_field() }}
        <div class="row">

            {{-- Левая колонка: основная + монстры --}}
            <div class="col-md-5">
                <section class="card">
                    <header class="card-header"><h2 class="card-title">Основная информация</h2></header>
                    <div class="card-body">
                        <div class="form-group">
                            <label class="col-form-label">Название</label>
                            <input type="text" class="form-control" name="name" value="{{ $location->name }}">
                        </div>
                        <div class="form-group">
                            <label class="col-form-label">Описание</label>
                            <textarea class="form-control" name="description" rows="5">{{ $location->description }}</textarea>
                        </div>
                        <div class="form-group">
                            <label class="col-form-label">Карта</label>
                            <select id="sel-map" name="map_id" class="form-control">
                                @if($location->map)
                                    <option value="{{ $location->map->id }}" selected>[{{ $location->map->id }}] {{ $location->map->name }}</option>
                                @endif
                            </select>
                        </div>
                        <div class="form-group">
                            <div class="checkbox-custom checkbox-primary">
                                <input type="hidden" name="is_locked" value="0">
                                <input type="checkbox" id="is_locked" name="is_locked" value="1" @checked($location->is_locked)>
                                <label for="is_locked">Заперта (требует доступа)</label>
                            </div>
                        </div>
                    </div>
                </section>

                <section class="card">
                    <header class="card-header"><h2 class="card-title">Монстры</h2></header>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="col-form-label">Количество монстров</label>
                                    <input type="number" class="form-control" name="count_monster" value="{{ $location->count_monster }}" min="0">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="col-form-label">Процент респауна (%)</label>
                                    <input type="number" class="form-control" name="percent_respawn_monster" value="{{ $location->percent_respawn_monster }}" min="0" max="100">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="col-form-label">Время респауна (сек.)</label>
                                    <input type="number" class="form-control" name="time_not_attack" value="{{ $location->time_not_attack }}" min="0">
                                </div>
                            </div>
                            @if($location->last_respawn_monster_at)
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="col-form-label">Последний респаун</label>
                                        <input type="text" class="form-control" value="{{ $location->last_respawn_monster_at->format('d.m.Y H:i:s') }}" readonly>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                </section>

                <div class="mb-3">
                    <button class="btn btn-primary">Сохранить</button>
                    <a href="{{ route('admin.locations') }}" class="btn btn-success">Назад</a>
                </div>
            </div>

            {{-- Правая колонка: направления --}}
            <div class="col-md-7">
                <section class="card">
                    <header class="card-header"><h2 class="card-title">Направления переходов</h2></header>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="col-form-label">Север (↑)</label>
                                    <select class="direction-select form-control" name="north">
                                        @if($location->northSide)
                                            <option value="{{ $location->northSide->id }}" selected>[{{ $location->northSide->id }}] {{ $location->northSide->name }}</option>
                                        @endif
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="col-form-label">Юг (↓)</label>
                                    <select class="direction-select form-control" name="south">
                                        @if($location->southSide)
                                            <option value="{{ $location->southSide->id }}" selected>[{{ $location->southSide->id }}] {{ $location->southSide->name }}</option>
                                        @endif
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="col-form-label">Запад (←)</label>
                                    <select class="direction-select form-control" name="west">
                                        @if($location->westSide)
                                            <option value="{{ $location->westSide->id }}" selected>[{{ $location->westSide->id }}] {{ $location->westSide->name }}</option>
                                        @endif
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="col-form-label">Восток (→)</label>
                                    <select class="direction-select form-control" name="east">
                                        @if($location->eastSide)
                                            <option value="{{ $location->eastSide->id }}" selected>[{{ $location->eastSide->id }}] {{ $location->eastSide->name }}</option>
                                        @endif
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="col-form-label">Вверх (⬆)</label>
                                    <select class="direction-select form-control" name="up">
                                        @if($location->upSide)
                                            <option value="{{ $location->upSide->id }}" selected>[{{ $location->upSide->id }}] {{ $location->upSide->name }}</option>
                                        @endif
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="col-form-label">Вниз (⬇)</label>
                                    <select class="direction-select form-control" name="down">
                                        @if($location->downSide)
                                            <option value="{{ $location->downSide->id }}" selected>[{{ $location->downSide->id }}] {{ $location->downSide->name }}</option>
                                        @endif
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>
            </div>

        </div>
    </form>

    <div class="row">
        <div class="col-md-12">
            <section class="card">
                <header class="card-header"><h2 class="card-title">Мобы на локации</h2></header>
                <div class="card-body">
                    <div class="right mb-3">
                        <a class="modal-with-zoom-anim ws-normal btn btn-sm btn-primary" href="#modalAddMonster">Добавить моба</a>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover table-bordered mb-none">
                            <thead>
                            <tr>
                                <th width="50">ID</th>
                                <th>Монстр</th>
                                <th width="60">Ур.</th>
                                <th width="160">Агрессия (override)</th>
                                <th width="70"></th>
                            </tr>
                            </thead>
                            <tbody>
                            @forelse($location->monsters as $monster)
                                <tr style="vertical-align: middle">
                                    <td class="text-center">{{ $monster->id }}</td>
                                    <td><a href="{{ route('admin.monster.info', $monster->id) }}">{{ $monster->name }}</a></td>
                                    <td class="text-center">{{ $monster->lvl }}</td>
                                    <td>
                                        <form action="{{ route('admin.location.monster.aggression', ['location' => $location->id, 'monster' => $monster->id]) }}" method="post" class="d-flex gap-1">
                                            @csrf
                                            <input type="number" name="aggression" min="0" max="100"
                                                   value="{{ $monster->pivot->aggression ?? '' }}"
                                                   placeholder="из монстра"
                                                   class="form-control form-control-sm" style="width:100px">
                                            <button class="btn btn-xs btn-secondary">✓</button>
                                        </form>
                                    </td>
                                    <td><a href="{{ route('admin.location.monster.delete', ['location' => $location->id, 'monster' => $monster->id]) }}" class="mb-1 mt-1 me-1 btn btn-xs btn-danger">Удалить</a></td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="text-center text-muted">Мобы не назначены</td></tr>
                            @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </section>
        </div>
    </div>

    <div id="modalAddMonster" class="modal-block zoom-anim-dialog modal-block-primary mfp-hide">
        <section class="card">
            <form action="{{ route('admin.location.monster.add', $location->id) }}" method="post">
                <header class="card-header">
                    <h2 class="card-title">Добавить моба</h2>
                </header>
                <div class="card-body">
                    {{ csrf_field() }}
                    <div class="form-group mb-2">
                        <label class="control-label">Монстр</label>
                        <select id="monster_id" name="monster_id" data-plugin-selectTwo class="form-control populate placeholder" data-plugin-options='{ "placeholder": "Выберите монстра", "allowClear": true }'>
                            @foreach($monsters as $m)
                                <option value="{{ $m->id }}">[{{ $m->lvl }} ур.] {{ $m->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group mb-2">
                        <label class="control-label">Агрессия (0–100, пусто = из монстра)</label>
                        <input type="number" name="aggression" min="0" max="100" class="form-control" placeholder="По умолчанию из монстра">
                    </div>
                </div>
                <footer class="card-footer">
                    <div class="row">
                        <div class="col-md-12 text-end">
                            <button class="btn btn-primary">Сохранить</button>
                            <button type="button" class="btn btn-default modal-dismiss">Отмена</button>
                        </div>
                    </div>
                </footer>
            </form>
        </section>
    </div>

@push('footer_scripts')
<script>
    var locationAjax = {
        url: '{{ route('admin.api.locations') }}',
        dataType: 'json',
        delay: 250,
        data: function (p) { return { q: p.term, page: p.page || 1 }; },
        processResults: function (data, p) {
            p.page = p.page || 1;
            return { results: data.results, pagination: { more: data.pagination.more } };
        },
        cache: true
    };

    $('.direction-select').each(function () {
        $(this).select2({
            theme: 'bootstrap',
            placeholder: 'Не задано',
            allowClear: true,
            ajax: locationAjax,
            minimumInputLength: 0
        });
    });

    $('#sel-map').select2({
        theme: 'bootstrap',
        placeholder: 'Выберите карту',
        allowClear: true,
        ajax: {
            url: '{{ route('admin.api.maps') }}',
            dataType: 'json',
            delay: 250,
            data: function (p) { return { q: p.term, page: p.page || 1 }; },
            processResults: function (data, p) {
                p.page = p.page || 1;
                return { results: data.results, pagination: { more: data.pagination.more } };
            },
            cache: true
        },
        minimumInputLength: 0
    });
</script>
@endpush

@endsection
