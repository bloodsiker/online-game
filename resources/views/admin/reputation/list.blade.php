@extends('admin.layout.base')

@section('title')
    Репутации
@endsection

@section('body')

    <div class="row">
        <div class="col-md-12">
            <section class="card">
                <div class="card-body">
                    <div class="mb-3">
                        <a href="{{ route('admin.reputation.create') }}" class="btn btn-primary btn-sm">Создать репутацию</a>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover table-bordered mb-none">
                            <thead>
                            <tr>
                                <th width="50">ID</th>
                                <th>Название</th>
                                <th>Описание</th>
                                <th width="80">Уровней</th>
                                <th width="80">В магазине</th>
                                <th width="60"></th>
                            </tr>
                            </thead>
                            <tbody>
                            @forelse($reputations as $rep)
                                <tr style="vertical-align: middle">
                                    <td>{{ $rep->id }}</td>
                                    <td>
                                        @if($rep->icon)
                                            <img src="{{ $rep->icon }}" style="width:24px;height:24px;object-fit:contain;margin-right:6px;vertical-align:middle;" alt="">
                                        @endif
                                        <a href="{{ route('admin.reputation.info', $rep->id) }}">{{ $rep->name }}</a>
                                    </td>
                                    <td class="text-muted small">{{ Str::limit($rep->description, 80) }}</td>
                                    <td>{{ $rep->tiers_count }}</td>
                                    <td>{{ $rep->shop_items_count }}</td>
                                    <td>
                                        <a href="{{ route('admin.reputation.info', $rep->id) }}" class="btn btn-xs btn-primary">Изменить</a>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="6" class="text-center text-muted">Нет репутаций</td></tr>
                            @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </section>
        </div>
    </div>

@endsection