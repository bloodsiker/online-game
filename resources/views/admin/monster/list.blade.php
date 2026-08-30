@extends('admin.layout.base')

@section('title')
    Монстры
@endsection

@section('body')

    <div class="row">
        <div class="col-md-12">
            <section class="card">
                <div class="card-body">
                    <div class="mb-3">
                        <a href="{{ route('admin.monster.create') }}" class="btn btn-success btn-sm">Создать монстра</a>
                    </div>

                    <form method="get" action="{{ route('admin.monsters') }}" class="mb-3">
                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label class="col-form-label">Монстр</label>
                                    <select id="filter-monster" name="monster_name" class="form-control">
                                        <option value="">Все монстры</option>
                                        @foreach($monsterNames as $monsterName)
                                            <option value="{{ $monsterName }}" @selected($filters['monster_name'] === $monsterName)>
                                                {{ $monsterName }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label class="col-form-label">Поиск</label>
                                    <input type="text"
                                           name="q"
                                           class="form-control"
                                           value="{{ $filters['q'] }}"
                                           placeholder="Название или описание">
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label class="col-form-label">Уровень от</label>
                                    <input type="number"
                                           name="level_from"
                                           class="form-control"
                                           value="{{ $filters['level_from'] }}"
                                           min="1">
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label class="col-form-label">Уровень до</label>
                                    <input type="number"
                                           name="level_to"
                                           class="form-control"
                                           value="{{ $filters['level_to'] }}"
                                           min="1">
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label class="col-form-label">Тип</label>
                                    <select name="is_boss" class="form-control">
                                        <option value="">Все монстры</option>
                                        <option value="0" @selected($filters['is_boss'] === '0')>Обычные</option>
                                        <option value="1" @selected($filters['is_boss'] === '1')>Боссы</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div style="text-align: right; margin-top: 12px;">
                            <button class="btn btn-primary btn-sm">Фильтровать</button>
                            <a href="{{ route('admin.monsters') }}" class="btn btn-default btn-sm">Сбросить</a>
                        </div>
                    </form>
                </div>
            </section>

            <section class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover table-bordered mb-none">
                            <thead>
                            <tr>
                                <th width="50">ID</th>
                                <th width="45"></th>
                                <th>Название</th>
                                <th width="50">Ур.</th>
                                <th width="80">HP</th>
                                <th width="80">Атака</th>
                                <th width="60">Опыт</th>
                                <th width="100">Монеты</th>
                                <th width="60">Босс</th>
                                <th width="70"></th>
                                <th width="70"></th>
                            </tr>
                            </thead>
                            <tbody>
                            @forelse($listMonsters as $monster)
                                <tr style="vertical-align: middle">
                                    <td>{{ $monster->id }}</td>
                                    <td>
                                        @if($monster->image)
                                            <img src="{{ $monster->image }}" style="width:50px;height:50px;object-fit:contain;" alt="">
                                        @endif
                                    </td>
                                    <td>
                                        <a href="{{ route('admin.monster.info', $monster->id) }}">{{ $monster->name }}</a>
                                    </td>
                                    <td>{{ $monster->lvl }}</td>
                                    <td>{{ $monster->hp }}</td>
                                    <td>{{ $monster->min_dmg }} – {{ $monster->max_dmg }}</td>
                                    <td>{{ $monster->exp }}</td>
                                    <td>{{ $monster->min_money }} – {{ $monster->max_money }}</td>
                                    <td>
                                        @if($monster->is_boss)
                                            <span class="badge badge-danger">Босс</span>
                                        @endif
                                    </td>
                                    <td><a href="{{ route('admin.monster.info', $monster->id) }}" class="btn btn-xs btn-primary">Детали</a></td>
                                    <td>
                                        <a href="{{ route('info.monster', ['id' => $monster->id]) }}"
                                           onclick="window.open(this.href,'','width=730,height=550,location=no,menubar=no,resizable=yes,scrollbars=yes,status=no,toolbar=no');return false;"
                                           class="btn btn-xs btn-secondary">Игровая страница</a>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="11" class="text-center text-muted">Монстры не найдены</td></tr>
                            @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-3">
                        {{ $listMonsters->onEachSide(2)->links('admin.pagination') }}
                    </div>
                </div>
            </section>
        </div>
    </div>

@endsection

@push('footer_scripts')
<script>
    $(function () {
        $('#filter-monster').select2({
            theme: 'bootstrap',
            placeholder: 'Все монстры',
            allowClear: true,
            width: '100%'
        });
    });
</script>
@endpush
