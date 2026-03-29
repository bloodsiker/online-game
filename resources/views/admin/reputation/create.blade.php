@extends('admin.layout.base')

@section('title')
    Создать репутацию
@endsection

@section('body')

    <div class="row">
        <div class="col-md-6">
            <section class="card">
                <div class="card-body">
                    <form action="{{ route('admin.reputation.create') }}" method="post">
                        {{ csrf_field() }}
                        <div class="form-group">
                            <label class="col-form-label">Название</label>
                            <input type="text" class="form-control" name="name" value="{{ old('name') }}">
                        </div>
                        <div class="form-group">
                            <label class="col-form-label">Описание</label>
                            <textarea class="form-control" name="description" rows="3">{{ old('description') }}</textarea>
                        </div>
                        <div class="form-group">
                            <label class="col-form-label">NPC</label>
                            <select id="sel-npc" name="npc_id" class="form-control"></select>
                        </div>
                        <div class="form-group">
                            <label class="col-form-label">Иконка (URL)</label>
                            <input type="text" class="form-control" name="icon" value="{{ old('icon') }}">
                        </div>
                        <div class="mt-3">
                            <button class="btn btn-primary">Создать</button>
                            <a href="{{ route('admin.reputations') }}" class="btn btn-success">Назад</a>
                        </div>
                    </form>
                </div>
            </section>
        </div>
    </div>

@push('footer_scripts')
<script>
    $('#sel-npc').select2({
        theme: 'bootstrap',
        placeholder: 'Выберите NPC',
        allowClear: true,
        ajax: {
            url: '{{ route('admin.api.npcs') }}',
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