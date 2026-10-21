@extends('admin.layout.base')
@section('title', 'Сборки предметов')
@section('body')
<section class="card"><div class="card-body">
    <div class="mb-3"><a class="btn btn-primary" href="{{ route('admin.item_assemblies.create') }}">Добавить сборку</a></div>
    <form method="get" class="mb-3">
        <div class="row align-items-end">
            <div class="col-md-5"><label>Название</label><input class="form-control" name="q" maxlength="100" value="{{ request('q') }}"></div>
            <div class="col-md-4"><label>Тип сборки</label><select class="form-control" name="type"><option value="">Все типы</option>@foreach($types as $type)<option value="{{ $type->value }}" @selected(request('type') === $type->value)>{{ $type->label() }}</option>@endforeach</select></div>
            <div class="col-md-3"><button class="btn btn-primary">Найти</button> <a class="btn btn-default" href="{{ route('admin.item_assemblies.index') }}">Сбросить</a></div>
        </div>
    </form>
    <div class="table-responsive"><table class="table table-bordered table-hover">
        <thead><tr><th>ID</th><th>Название</th><th>Тип</th><th>Результат</th><th>Компонентов</th><th>Наград</th><th>Активна</th><th></th></tr></thead>
        <tbody>@forelse($assemblies as $assembly)
            <tr><td>{{ $assembly->id }}</td><td>{{ $assembly->name }}</td><td>{{ $assembly->type->label() }}</td><td>{{ $assembly->result_mode->label() }}</td><td>{{ $assembly->components_count }}</td><td>{{ $assembly->rewards_count }}</td><td>{{ $assembly->is_active ? 'Да' : 'Нет' }}</td><td><a class="btn btn-sm btn-primary" href="{{ route('admin.item_assemblies.edit', $assembly) }}">Редактировать</a></td></tr>
        @empty<tr><td colspan="8" class="text-center">Сборок пока нет.</td></tr>@endforelse</tbody>
    </table></div>
    {{ $assemblies->links('admin.pagination') }}
</div></section>
@endsection
