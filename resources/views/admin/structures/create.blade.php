@extends('admin.layout.base')

@section('title')
    Новое построение
@endsection

@section('body')
    <div class="row">
        <div class="col-md-12">
            <section class="card">
                <div class="card-body">
                    <form action="{{ route('admin.structure.create') }}" method="post" enctype="multipart/form-data">
                        @csrf
                        <div class="row pb-3">
                            <div class="col-md-6">
                                @include('admin.partials.content-translations', ['prefix' => 'structure-new', 'content' => null])
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
                                    <label class="col-form-label">Локация</label>
                                    <select id="sel-location" name="location_id" class="form-control"></select>
                                </div>
                                <div class="form-group">
                                    <label class="col-form-label">НПС</label>
                                    <select id="sel-npc" name="npc_id" class="form-control"></select>
                                </div>
                                <div class="form-group">
                                    <label class="col-form-label">Данж (для логова клан-босса)</label>
                                    <select id="sel-dungeon" name="dungeon_id" class="form-control"></select>
                                </div>
                                <div class="form-group">
                                    <label class="col-form-label">Изображение</label>
                                    <div class="mb-1">
                                        <img id="structure-preview" src="" alt=""
                                             style="width:64px;height:64px;object-fit:contain;border:1px solid #ddd;border-radius:4px;background:#f5f5f5;display:none;">
                                    </div>
                                    <input type="file" class="form-control mt-1" name="image" id="structure-image" accept="image/*">
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
        initStructureSelect('#sel-dungeon', '{{ route('admin.api.dungeons') }}', 'Без данжа');

        document.getElementById('structure-image').addEventListener('change', function () {
            const preview = document.getElementById('structure-preview');
            if (this.files[0]) {
                preview.src = URL.createObjectURL(this.files[0]);
                preview.style.display = 'block';
            }
        });
    </script>
@endpush
