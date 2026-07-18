@extends('admin.layout.base')

@section('title')
    Квесты
@endsection

@section('body')

    <div class="row">
        <div class="col-md-12">
            <section class="card">
                <div class="card-body">
                    <div class="mb-3">
                        <a href="{{ route('admin.quest.create') }}" class="btn btn-primary btn-sm">Создать квест</a>
                    </div>
                    <form method="get" action="{{ route('admin.quests') }}" class="mb-3">
                        <div class="row">
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
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label class="col-form-label">Тип</label>
                                    <select name="type" class="form-control">
                                        <option value="">Все типы</option>
                                        @foreach($questTypes as $type)
                                            <option value="{{ $type->value }}" @selected($filters['type'] === $type->value)>
                                                {{ $type->label() }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label class="col-form-label">Активен</label>
                                    <select name="is_active" class="form-control">
                                        <option value="">Все</option>
                                        <option value="1" @selected($filters['is_active'] === '1')>Да</option>
                                        <option value="0" @selected($filters['is_active'] === '0')>Нет</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label class="col-form-label">Финальный</label>
                                    <select name="is_finish" class="form-control">
                                        <option value="">Все</option>
                                        <option value="1" @selected($filters['is_finish'] === '1')>Да</option>
                                        <option value="0" @selected($filters['is_finish'] === '0')>Нет</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label class="col-form-label">Повторяемость</label>
                                    <select name="repeatable" class="form-control">
                                        <option value="">Все</option>
                                        <option value="yes" @selected($filters['repeatable'] === 'yes')>Есть reset_period</option>
                                        <option value="no" @selected($filters['repeatable'] === 'no')>Без reset_period</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label class="col-form-label">Выдает NPC</label>
                                    <select id="filter-start-npc" name="start_npc_id" class="form-control">
                                        @if($selectedStartNpc)
                                            <option value="{{ $selectedStartNpc->id }}" selected>[{{ $selectedStartNpc->id }}] {{ $selectedStartNpc->name }}</option>
                                        @endif
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label class="col-form-label">Принимает NPC</label>
                                    <select id="filter-complete-npc" name="complete_npc_id" class="form-control">
                                        @if($selectedCompleteNpc)
                                            <option value="{{ $selectedCompleteNpc->id }}" selected>[{{ $selectedCompleteNpc->id }}] {{ $selectedCompleteNpc->name }}</option>
                                        @endif
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div style="text-align: right; margin-top: 12px;">
                            <button class="btn btn-primary btn-sm">Фильтровать</button>
                            <a href="{{ route('admin.quests') }}" class="btn btn-default btn-sm">Сбросить</a>
                        </div>
                    </form>
                    <div class="table-responsive">
                        <table class="table table-hover table-bordered mb-none">
                            <thead>
                            <tr>
                                <th width="50">ID</th>
                                <th>Название</th>
                                <th width="140">Тип</th>
                                <th width="170">Выдает NPC</th>
                                <th width="170">Принимает NPC</th>
                                <th width="80">Заданий</th>
                                <th width="80">Наград</th>
                                <th width="80">Активен</th>
                                <th width="80">Финал</th>
                                <th width="60"></th>
                            </tr>
                            </thead>
                            <tbody>
                            @forelse($quests as $quest)
                                <tr style="vertical-align: middle">
                                    <td>{{ $quest->id }}</td>
                                    <td><a href="{{ route('admin.quest.info', $quest->id) }}">{{ $quest->title }}</a></td>
                                    <td><span class="badge badge-info">{{ $quest->type->label() }}</span></td>
                                    <td>{{ $quest->startNpc ? '[' . $quest->start_npc_id . '] ' . $quest->startNpc->name : '—' }}</td>
                                    <td>{{ $quest->completeNpc ? '[' . $quest->complete_npc_id . '] ' . $quest->completeNpc->name : '—' }}</td>
                                    <td>{{ $quest->objectives_count }}</td>
                                    <td>{{ $quest->rewards_count }}</td>
                                    <td>
                                        @if($quest->is_active)
                                            <span class="badge badge-success">Да</span>
                                        @else
                                            <span class="badge badge-default">Нет</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($quest->is_finish)
                                            <span class="badge badge-success">Да</span>
                                        @else
                                            <span class="badge badge-default">Нет</span>
                                        @endif
                                    </td>
                                    <td>
                                        <a href="{{ route('admin.quest.info', $quest->id) }}" class="btn btn-xs btn-primary">Изменить</a>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="10" class="text-center text-muted">Нет квестов</td></tr>
                            @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-3">
                        {{ $quests->onEachSide(2)->links('admin.pagination') }}
                    </div>
                </div>
            </section>
        </div>
    </div>

@push('footer_scripts')
<script>
    function initQuestNpcFilter(selector, placeholder) {
        $(selector).select2({
            theme: 'bootstrap',
            placeholder: placeholder,
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
    }

    initQuestNpcFilter('#filter-start-npc', 'Все NPC');
    initQuestNpcFilter('#filter-complete-npc', 'Все NPC');
</script>
@endpush

@endsection
