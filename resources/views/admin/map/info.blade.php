@extends('admin.layout.base')

@section('title')
    {{ $map->name }}
@endsection

@section('body')

    <div class="row">
        <div class="col-md-12">
            <section class="card">
                <div class="card-body">
                    <div class="tabs">
                        <ul class="nav nav-tabs">
                            <li class="nav-item active">
                                <a class="nav-link active" data-bs-target="#tab-main" href="#tab-main" data-bs-toggle="tab">Основная</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" data-bs-target="#tab-locations" href="#tab-locations" data-bs-toggle="tab">
                                    Локации <span class="badge badge-primary">{{ $locations->count() }}</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" data-bs-target="#tab-resources" href="#tab-resources" data-bs-toggle="tab">
                                    Ресурсы <span class="badge badge-primary">{{ $mapResources->count() }}</span>
                                </a>
                            </li>
                        </ul>

                        <div class="tab-content">

                            {{-- ОСНОВНАЯ --}}
                            <div id="tab-main" class="tab-pane active">
                                <form action="{{ route('admin.map.info', $map->id) }}" method="post" enctype="multipart/form-data">
                                    {{ csrf_field() }}
                                    <div class="row pt-3 pb-3">
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label class="col-form-label">Название</label>
                                                <input type="text" class="form-control" name="name" value="{{ $map->name }}">
                                            </div>
                                            <div class="form-group">
                                                <label class="col-form-label">Slug</label>
                                                <input type="text" class="form-control" name="slug" value="{{ $map->slug }}">
                                            </div>
                                            <div class="form-group">
                                                <label class="col-form-label">Папка для карт</label>
                                                <input type="text" class="form-control" name="folder" value="{{ $map->folder }}">
                                            </div>
                                            <div class="form-group">
                                                <label class="col-form-label">Родительская карта</label>
                                                <select name="parent_id" class="form-control" data-plugin-selectTwo
                                                        data-plugin-options='{ "placeholder": "Выберите карту", "allowClear": true }'>
                                                    <option value=""></option>
                                                    @foreach($allMaps as $m)
                                                        <option value="{{ $m->id }}" @selected($map->parent_id === $m->id)>{{ $m->name }} [{{ $m->id }}]</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <div class="checkbox-custom checkbox-primary">
                                                    <input type="hidden" name="has_gathering_field" value="0">
                                                    <input type="checkbox" id="has_gathering_field" name="has_gathering_field" value="1" @checked($map->has_gathering_field)>
                                                    <label for="has_gathering_field">Показывать поле сбора ресурсов</label>
                                                </div>
                                            </div>
                                            <div class="form-group">
                                                <label class="col-form-label">Картинка поля сбора</label>
                                                @if($map->gathering_field_image)
                                                    <div class="mb-1">
                                                        <img src="{{ $map->gathering_field_image }}" alt=""
                                                             style="width:200px;object-fit:cover;border:1px solid #ddd;border-radius:4px;background:#f5f5f5;">
                                                        <small class="text-muted d-block">{{ $map->gathering_field_image }}</small>
                                                    </div>
                                                @endif
                                                <input type="file" class="form-control mt-1" name="gathering_field_image" accept="image/*">
                                                @if($map->getRawOriginal('gathering_field_image'))
                                                    <div class="checkbox-custom checkbox-danger mt-1">
                                                        <input type="checkbox" id="delete_gathering_field_image" name="delete_gathering_field_image" value="1">
                                                        <label for="delete_gathering_field_image">Удалить картинку</label>
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row mb-3">
                                        <div class="col-sm-12">
                                            <button class="btn btn-primary">Сохранить</button>
                                            <a href="{{ route('admin.maps') }}" class="btn btn-success">Назад</a>
                                        </div>
                                    </div>
                                </form>
                            </div>

                            {{-- ЛОКАЦИИ --}}
                            <div id="tab-locations" class="tab-pane">
                                <div class="pt-3">
                                    <div class="mb-3">
                                        <a class="modal-with-zoom-anim ws-normal btn btn-sm btn-primary" href="#modalAddLocation">Добавить локацию</a>
                                    </div>
                                    <div class="table-responsive">
                                        <table class="table table-hover table-bordered mb-none">
                                            <thead>
                                            <tr>
                                                <th width="50">ID</th>
                                                <th>Название</th>
                                            </tr>
                                            </thead>
                                            <tbody>
                                            @forelse($locations as $location)
                                                <tr style="vertical-align: middle">
                                                    <td>{{ $location->id }}</td>
                                                    <td>
                                                        <a href="{{ route('admin.location.info', $location->id) }}">{{ $location->name }}</a>
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr><td colspan="2" class="text-center text-muted">Нет локаций</td></tr>
                                            @endforelse
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>

                            {{-- РЕСУРСЫ ОБЩЕГО ПОЛЯ КАРТЫ --}}
                            <div id="tab-resources" class="tab-pane">
                                <div class="pt-3">
                                    <form action="{{ route('admin.map.gathering-resource.save', $map->id) }}" method="post" class="mb-4">
                                        {{ csrf_field() }}
                                        <div class="row">
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label class="col-form-label">Ресурс</label>
                                                    <select name="share_item_id" class="form-control" data-plugin-selectTwo required>
                                                        <option value=""></option>
                                                        @foreach($gatheringResources as $resource)
                                                            <option value="{{ $resource->id }}">[{{ $resource->id }}] {{ $resource->name }} · {{ $resource->skill?->name }} · {{ $resource->gathering_time_seconds }} сек.</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-md-2"><div class="form-group"><label class="col-form-label">Кружков</label><input type="number" name="max_active" min="1" max="100" value="1" class="form-control" required></div></div>
                                            <div class="col-md-1"><div class="form-group"><label class="col-form-label">X от</label><input type="number" name="min_x" min="1" max="99" value="8" class="form-control" required></div></div>
                                            <div class="col-md-1"><div class="form-group"><label class="col-form-label">X до</label><input type="number" name="max_x" min="1" max="99" value="92" class="form-control" required></div></div>
                                            <div class="col-md-1"><div class="form-group"><label class="col-form-label">Y от</label><input type="number" name="min_y" min="1" max="99" value="16" class="form-control" required></div></div>
                                            <div class="col-md-1"><div class="form-group"><label class="col-form-label">Y до</label><input type="number" name="max_y" min="1" max="99" value="74" class="form-control" required></div></div>
                                            <div class="col-md-2"><div class="form-group"><label class="col-form-label">&nbsp;</label><div><button class="btn btn-primary">Добавить / обновить</button></div></div></div>
                                        </div>
                                        <p class="text-muted mb-0">Координаты задаются в процентах поля. Одинаковый ресурс можно настроить по-разному на разных картах.</p>
                                    </form>

                                    <div class="table-responsive">
                                        <table class="table table-hover table-bordered mb-none">
                                            <thead><tr><th>Ресурс</th><th>Профессия</th><th>Инструмент</th><th>Добыча / респавн</th><th width="115">Кружков</th><th width="310">Область, %</th><th width="155"></th></tr></thead>
                                            <tbody>
                                            @forelse($mapResources as $configuration)
                                                <tr>
                                                    <td><a href="{{ route('admin.item.info', $configuration->resource->id) }}">[{{ $configuration->resource->id }}] {{ $configuration->resource->name }}</a></td>
                                                    <td>{{ $configuration->resource->skill?->name }} {{ $configuration->resource->skill_lvl }} ур.</td>
                                                    <td>{{ \App\Modules\Share\Domain\Enums\GatheringToolFamily::tryFrom((string) $configuration->resource->gathering_tool_family)?->label() ?? 'не настроен' }}</td>
                                                    <td>{{ $configuration->resource->gathering_time_seconds }} / {{ $configuration->resource->gathering_respawn_seconds }} сек.</td>
                                                    <td>
                                                        <div class="input-group input-group-sm">
                                                            <span class="input-group-text" title="Сейчас создано кружков">{{ $configuration->nodes->count() }} /</span>
                                                            <input form="gathering-config-{{ $configuration->id }}" type="number" name="max_active" min="1" max="100" value="{{ $configuration->max_active }}" class="form-control" aria-label="Максимум активных кружков" required>
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <div class="d-flex gap-1 align-items-center">
                                                            <span>X</span>
                                                            <input form="gathering-config-{{ $configuration->id }}" type="number" name="min_x" min="1" max="99" value="{{ $configuration->min_x }}" class="form-control form-control-sm" aria-label="X от" required>
                                                            <span>–</span>
                                                            <input form="gathering-config-{{ $configuration->id }}" type="number" name="max_x" min="1" max="99" value="{{ $configuration->max_x }}" class="form-control form-control-sm" aria-label="X до" required>
                                                            <span>Y</span>
                                                            <input form="gathering-config-{{ $configuration->id }}" type="number" name="min_y" min="1" max="99" value="{{ $configuration->min_y }}" class="form-control form-control-sm" aria-label="Y от" required>
                                                            <span>–</span>
                                                            <input form="gathering-config-{{ $configuration->id }}" type="number" name="max_y" min="1" max="99" value="{{ $configuration->max_y }}" class="form-control form-control-sm" aria-label="Y до" required>
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <form id="gathering-config-{{ $configuration->id }}" action="{{ route('admin.map.gathering-resource.update', ['map' => $map->id, 'resource' => $configuration->id]) }}" method="post" class="mb-1">
                                                            @csrf @method('PATCH')
                                                            <button class="btn btn-xs btn-primary">Сохранить</button>
                                                        </form>
                                                        <a href="{{ route('admin.item.info', $configuration->resource->id) }}" class="btn btn-xs btn-default mb-1" title="Время добычи, респавн, профессия и инструмент">Предмет</a>
                                                        <form action="{{ route('admin.map.gathering-resource.delete', ['map' => $map->id, 'resource' => $configuration->id]) }}" method="post" onsubmit="return confirm('Удалить ресурс и все его кружки с карты?')">
                                                            @csrf @method('DELETE')
                                                            <button class="btn btn-xs btn-danger">Удалить</button>
                                                        </form>
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr><td colspan="7" class="text-center text-muted">Ресурсы на карте не настроены</td></tr>
                                            @endforelse
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
            </section>
        </div>
    </div>

    {{-- Модалка: добавить локацию --}}
    <div id="modalAddLocation" class="modal-block zoom-anim-dialog modal-block-primary mfp-hide">
        <section class="card">
            <form action="{{ route('admin.map.location', $map->id) }}" method="post">
                <header class="card-header"><h2 class="card-title">Добавить локацию на карту</h2></header>
                <div class="card-body">
                    {{ csrf_field() }}
                    <div class="form-group mb-2">
                        <label>Локация</label>
                        <select id="sel-add-location" name="location_id" class="form-control"></select>
                    </div>
                </div>
                <footer class="card-footer">
                    <div class="col-md-12 text-end">
                        <button class="btn btn-primary">Добавить</button>
                        <button type="button" class="btn btn-default modal-dismiss">Отмена</button>
                    </div>
                </footer>
            </form>
        </section>
    </div>

@push('footer_scripts')
<script>
    if (window.location.hash === '#tab-resources') {
        const resourcesTab = document.querySelector('[data-bs-target="#tab-resources"]');
        if (resourcesTab && window.bootstrap) {
            bootstrap.Tab.getOrCreateInstance(resourcesTab).show();
        }
    }

    $('#sel-add-location').select2({
        theme: 'bootstrap',
        dropdownParent: $('#modalAddLocation'),
        placeholder: 'Выберите локацию',
        allowClear: true,
        ajax: {
            url: '{{ route('admin.api.locations') }}',
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
