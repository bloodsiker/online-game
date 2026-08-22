@extends('admin.layout.base')

@section('title')
    Создать НПС
@endsection

@section('body')

    <div class="row">
        <div class="col-md-6">
            <section class="card">
                <div class="card-body">
                    <form action="{{ route('admin.npc.create') }}" method="post" enctype="multipart/form-data">
                        {{ csrf_field() }}
                        <div class="form-group">
                            <label class="col-form-label">Имя</label>
                            <input type="text" class="form-control" name="name" value="{{ old('name') }}">
                        </div>
                        <div class="form-group">
                            <label class="col-form-label">Локация</label>
                            <select id="sel-location" name="location_id" class="form-control"></select>
                        </div>
                        <div class="checkbox-custom checkbox-default">
                            <input type="checkbox" id="hide-location" name="hide_location" value="1" @checked(old('hide_location'))>
                            <label for="hide-location">Скрывать местоположение в информации об НПС</label>
                        </div>
                        <div class="form-group">
                            <label class="col-form-label">Описание</label>
                            <textarea class="form-control" name="description" rows="5">{{ old('description') }}</textarea>
                        </div>
                        <div class="form-group">
                            <label class="col-form-label">Изображение</label>
                            <div class="mb-1">
                                <img id="npc-preview" src="" alt="" style="width:64px;height:64px;object-fit:contain;border:1px solid #ddd;border-radius:4px;background:#f5f5f5;display:none;">
                            </div>
                            <input type="file" class="form-control" name="image" id="npc-image" accept="image/*">
                        </div>
                        <div class="mt-3">
                            <button class="btn btn-primary">Создать</button>
                            <a href="{{ route('admin.npc') }}" class="btn btn-success">Назад</a>
                        </div>
                    </form>
                </div>
            </section>
        </div>
    </div>

    <script>
        document.getElementById('npc-image').addEventListener('change', function () {
            const preview = document.getElementById('npc-preview');
            if (this.files[0]) {
                preview.src = URL.createObjectURL(this.files[0]);
                preview.style.display = 'block';
            }
        });
    </script>

@push('footer_scripts')
<script>
    $('#sel-location').select2({
        theme: 'bootstrap',
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