@extends('admin.layout.base')

@section('title')
    Новое построение
@endsection

@section('body')
    <div class="row">
        <div class="col-md-12">
            <section class="card">
                <div class="card-body">
                    <form action="{{ route('admin.structure.create') }}" method="post">
                        @csrf
                        <div class="row pb-3">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="col-form-label">Название</label>
                                    <input type="text" class="form-control" name="name" value="{{ old('name') }}" required>
                                </div>
                                <div class="form-group">
                                    <label class="col-form-label">Тип</label>
                                    <select class="form-control" name="type" data-plugin-selectTwo required>
                                        @foreach(\App\Modules\Structure\Infrastructure\Persistence\Models\Structure::TYPES as $key => $label)
                                            <option value="{{ $key }}" @selected(old('type') === $key)>{{ $label }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="col-form-label">Описание</label>
                                    <textarea class="form-control" name="description" rows="3">{{ old('description') }}</textarea>
                                </div>
                                <div class="form-group">
                                    <label class="col-form-label">Локация</label>
                                    <select id="sel-location" name="location_id" class="form-control"></select>
                                </div>
                                <div class="form-group">
                                    <label class="col-form-label">НПС</label>
                                    <select id="sel-npc" name="npc_id" class="form-control"></select>
                                </div>
                            </div>
                        </div>
                        <button class="btn btn-primary">Создать</button>
                        <a href="{{ route('admin.structures') }}" class="btn btn-default">Отмена</a>
                    </form>
                </div>
            </section>
        </div>
    </div>
@endsection

@push('footer_scripts')
    <script>
        function initStructureSelect(selector, url, placeholder) {
            $(selector).select2({
                theme: 'bootstrap',
                placeholder: placeholder,
                allowClear: true,
                ajax: {
                    url: url,
                    dataType: 'json',
                    delay: 250,
                    data: function (params) {
                        return {q: params.term, page: params.page || 1};
                    },
                    processResults: function (data, params) {
                        params.page = params.page || 1;
                        return {results: data.results, pagination: {more: data.pagination.more}};
                    },
                    cache: true
                },
                minimumInputLength: 0
            });
        }

        initStructureSelect('#sel-location', '{{ route('admin.api.locations') }}', 'Выберите локацию');
        initStructureSelect('#sel-npc', '{{ route('admin.api.npcs') }}', 'Выберите НПС');
    </script>
@endpush
