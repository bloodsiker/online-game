@extends('admin.layout.base')

@section('title')
    Акции банка
@endsection

@section('body')
    <div class="row">
        <div class="col-md-12">
            <section class="card">
                <div class="card-body">
                    <div class="mb-3">
                        <a href="{{ route('admin.bank.stock.create') }}" class="btn btn-sm btn-primary">Создать акцию</a>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover table-bordered mb-none">
                            <thead>
                            <tr>
                                <th width="50">ID</th>
                                <th>Название</th>
                                <th width="160">Начало</th>
                                <th width="160">Конец</th>
                                <th width="80">Уровни</th>
                                <th width="80">Активна</th>
                                <th width="80"></th>
                            </tr>
                            </thead>
                            <tbody>
                            @forelse($stocks as $stock)
                                <tr>
                                    <td>{{ $stock->id }}</td>
                                    <td><a href="{{ route('admin.bank.stock.info', $stock->id) }}">{{ $stock->name }}</a></td>
                                    <td>{{ $stock->starts_at->format('d.m.Y H:i') }}</td>
                                    <td>{{ $stock->ends_at->format('d.m.Y H:i') }}</td>
                                    <td class="text-center"><span class="badge badge-primary">{{ $stock->tiers_count }}</span></td>
                                    <td class="text-center">
                                        @if($stock->is_active)
                                            <span class="badge badge-success">Да</span>
                                        @else
                                            <span class="badge badge-default">Нет</span>
                                        @endif
                                    </td>
                                    <td style="white-space:nowrap;">
                                        <a href="{{ route('admin.bank.stock.info', $stock->id) }}" class="btn btn-xs btn-primary">Изменить</a>
                                        <a href="{{ route('admin.bank.stock.duplicate', $stock->id) }}" class="btn btn-xs btn-default ml-1">Копировать</a>
                                        <a href="{{ route('admin.bank.stock.delete', $stock->id) }}"
                                           onclick="return confirm('Удалить акцию «{{ $stock->name }}» и все её уровни?')"
                                           class="btn btn-xs btn-danger ml-1">Удалить</a>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="7" class="text-center text-muted">Нет акций</td></tr>
                            @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </section>
        </div>
    </div>
@endsection