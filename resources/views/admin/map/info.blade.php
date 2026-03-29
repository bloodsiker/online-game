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
                        </ul>

                        <div class="tab-content">

                            {{-- ОСНОВНАЯ --}}
                            <div id="tab-main" class="tab-pane active">
                                <form action="{{ route('admin.map.info', $map->id) }}" method="post">
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