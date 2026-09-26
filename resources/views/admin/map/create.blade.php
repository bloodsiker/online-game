@extends('admin.layout.base')

@section('title')
    Создать карту
@endsection

@section('body')

    <div class="row">
        <div class="col-md-5">
            <section class="card">
                <div class="card-body">
                    <form action="{{ route('admin.map.create') }}" method="post">
                        {{ csrf_field() }}
                        <div class="form-group">
                            <label class="col-form-label">Название</label>
                            <input type="text" class="form-control" name="name" value="{{ old('name') }}">
                        </div>
                        <div class="form-group">
                            <label class="col-form-label">Slug</label>
                            <input type="text" class="form-control" name="slug" value="{{ old('slug') }}">
                        </div>
                        <div class="form-group">
                            <label class="col-form-label">Папка для карт</label>
                            <input type="text" class="form-control" name="folder" value="{{ old('folder') }}">
                        </div>
                        <div class="form-group">
                            <label class="col-form-label">Родительская карта</label>
                            <select name="parent_id" class="form-control" data-plugin-selectTwo
                                    data-plugin-options='{ "placeholder": "Выберите карту", "allowClear": true }'>
                                <option value=""></option>
                                @foreach($allMaps as $m)
                                    <option value="{{ $m->id }}" @selected((int) old('parent_id') === $m->id)>{{ $m->name }} [{{ $m->id }}]</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="col-form-label">Локация возрождения</label>
                            <select id="resp-location-select" name="resp_location_id" class="form-control" required>
                                <option value=""></option>
                                @if($selectedRespawnLocation)
                                    <option value="{{ $selectedRespawnLocation->id }}" selected>
                                        [{{ $selectedRespawnLocation->id }}] {{ trim((string) $selectedRespawnLocation->name) !== '' ? $selectedRespawnLocation->name : 'Локация №'.$selectedRespawnLocation->id }}@if($selectedRespawnLocation->map) ({{ $selectedRespawnLocation->map->name }})@endif
                                    </option>
                                @endif
                            </select>
                            <small class="form-text text-muted">После смерти игрок будет перемещён в выбранную локацию.</small>
                        </div>
                        <div class="mt-3">
                            <button class="btn btn-primary">Создать</button>
                            <a href="{{ route('admin.maps') }}" class="btn btn-success">Назад</a>
                        </div>
                    </form>
                </div>
            </section>
        </div>
    </div>

@push('footer_scripts')
<script>
    $('#resp-location-select').select2({
        theme: 'bootstrap',
        width: '100%',
        placeholder: 'Выберите локацию',
        ajax: {
            url: '{{ route('admin.api.locations') }}',
            dataType: 'json',
            delay: 250,
            data: function (params) {
                return { q: params.term, page: params.page || 1 };
            },
            processResults: function (data, params) {
                params.page = params.page || 1;

                return {
                    results: data.results,
                    pagination: { more: data.pagination.more }
                };
            },
            cache: true
        },
        minimumInputLength: 0
    });
</script>
@endpush

@endsection
