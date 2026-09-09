@extends('admin.layout.base')

@section('title')
    Травмы
@endsection

@section('body')
    <div class="row">
        <div class="col-md-12">
            <section class="card">
                <div class="card-body">
                    <div class="mb-3">
                        <a href="{{ route('admin.injury_type.create') }}" class="btn btn-primary btn-sm">Добавить травму</a>
                    </div>

                    <form method="get" action="{{ route('admin.injury_types') }}" class="mb-3">
                        <div class="row">
                            <div class="col-md-5">
                                <div class="form-group">
                                    <label>Поиск</label>
                                    <input class="form-control" name="q" value="{{ $filters['q'] }}" placeholder="Название или slug">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Часть тела</label>
                                    <select class="form-control" name="body_part">
                                        <option value="">Все части тела</option>
                                        @foreach($bodyParts as $part)
                                            <option value="{{ $part->value }}" @selected($filters['body_part'] === $part->value)>{{ $part->label() }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3 d-flex align-items-end">
                                <div class="form-group">
                                    <button class="btn btn-primary btn-sm">Фильтровать</button>
                                    <a href="{{ route('admin.injury_types') }}" class="btn btn-default btn-sm">Сбросить</a>
                                </div>
                            </div>
                        </div>
                    </form>

                    <div class="table-responsive">
                        <table class="table table-hover table-bordered mb-none">
                            <thead>
                            <tr>
                                <th width="50">ID</th>
                                <th width="75">Картинка</th>
                                <th>Название</th>
                                <th width="120">Часть тела</th>
                                <th width="100">Тяжесть</th>
                                <th width="110">Длительность</th>
                                <th width="90">Вес</th>
                                <th>Модификаторы</th>
                                <th width="85">Активна</th>
                                <th width="90"></th>
                            </tr>
                            </thead>
                            <tbody>
                            @forelse($injuryTypes as $injuryType)
                                <tr style="vertical-align: middle">
                                    <td>{{ $injuryType->id }}</td>
                                    <td class="text-center">
                                        @if($injuryType->image)
                                            <img src="{{ $injuryType->image }}" alt="{{ $injuryType->name }}" style="width:60px;height:60px;object-fit:contain;">
                                        @else
                                            <span class="text-muted">CSS-бинт</span>
                                        @endif
                                    </td>
                                    <td>
                                        <a href="{{ route('admin.injury_type.edit', $injuryType) }}">{{ $injuryType->name }}</a>
                                        <div class="small text-muted">{{ $injuryType->slug }}</div>
                                    </td>
                                    <td>{{ $injuryType->body_part->label() }}</td>
                                    <td>{{ $injuryType->severity->label() }}</td>
                                    <td>{{ $injuryType->durationMinutes() }} мин.</td>
                                    <td>{{ $injuryType->drop_weight }}</td>
                                    <td>
                                        @forelse($injuryType->stat_modifiers ?? [] as $modifier)
                                            <span class="badge badge-danger">
                                                {{ \App\Modules\Player\Domain\Enums\PlayerStatKey::tryFrom($modifier['stat'] ?? '')?->label() ?? ($modifier['stat'] ?? '—') }}:
                                                {{ ($modifier['value'] ?? 0) > 0 ? '+' : '' }}{{ $modifier['value'] ?? 0 }}{{ !empty($modifier['is_percent']) ? '%' : '' }}
                                            </span>
                                        @empty
                                            <span class="text-muted">нет</span>
                                        @endforelse
                                    </td>
                                    <td class="text-center">
                                        <span class="badge {{ $injuryType->is_active ? 'badge-success' : 'badge-secondary' }}">
                                            {{ $injuryType->is_active ? 'да' : 'нет' }}
                                        </span>
                                    </td>
                                    <td><a href="{{ route('admin.injury_type.edit', $injuryType) }}" class="btn btn-xs btn-primary">Изменить</a></td>
                                </tr>
                            @empty
                                <tr><td colspan="10" class="text-center text-muted">Травмы не созданы</td></tr>
                            @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </section>
        </div>
    </div>
@endsection
