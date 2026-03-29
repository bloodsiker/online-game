@extends('admin.layout.base')

@section('title')
    Создать локацию
@endsection

@section('body')

    <div class="row">
        <div class="col-md-5">
            <section class="card">
                <div class="card-body">
                    <form action="{{ route('admin.location.create') }}" method="post">
                        {{ csrf_field() }}
                        <div class="form-group">
                            <label class="col-form-label">Название</label>
                            <input type="text" class="form-control" name="name" value="{{ old('name') }}">
                        </div>
                        <div class="form-group">
                            <label class="col-form-label">Описание</label>
                            <textarea class="form-control" name="description" rows="4">{{ old('description') }}</textarea>
                        </div>
                        <div class="form-group">
                            <label class="col-form-label">Карта</label>
                            <select id="sel-map" name="map_id" class="form-control"></select>
                        </div>
                        <div class="form-group">
                            <div class="checkbox-custom checkbox-primary">
                                <input type="hidden" name="is_locked" value="0">
                                <input type="checkbox" id="is_locked" name="is_locked" value="1">
                                <label for="is_locked">Заперта (требует доступа)</label>
                            </div>
                        </div>
                        <div class="mt-3">
                            <button class="btn btn-primary">Создать</button>
                            <a href="{{ route('admin.locations') }}" class="btn btn-success">Назад</a>
                        </div>
                    </form>
                </div>
            </section>
        </div>
    </div>

@push('footer_scripts')
<script>
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
