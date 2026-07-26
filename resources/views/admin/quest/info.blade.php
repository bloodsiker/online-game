@extends('admin.layout.base')

@section('title')
    {{ $quest->title }}
@endsection

@section('body')

    <div class="row">
        <div class="col-md-12">
            <section class="card">
                <div class="card-body">
                    <div class="tabs">
                        <ul class="nav nav-tabs">
                            <li class="nav-item active">
                                <a class="nav-link active" data-bs-target="#tab-main" href="#tab-main" data-bs-toggle="tab">Основная</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" data-bs-target="#tab-objectives" href="#tab-objectives" data-bs-toggle="tab">
                                    Задания <span class="badge badge-primary">{{ $quest->objectives->count() }}</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" data-bs-target="#tab-rewards" href="#tab-rewards" data-bs-toggle="tab">
                                    Награды <span class="badge badge-primary">{{ $quest->rewards->count() }}</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" data-bs-target="#tab-dialogues" href="#tab-dialogues" data-bs-toggle="tab">
                                    Диалог <span class="badge badge-primary">{{ $quest->dialogues->count() }}</span>
                                </a>
                            </li>
                        </ul>

                        <div class="tab-content">

                            {{-- ОСНОВНАЯ --}}
                            <div id="tab-main" class="tab-pane active">
                                <form action="{{ route('admin.quest.info', $quest->id) }}" method="post">
                                    {{ csrf_field() }}
                                    <div class="row pt-3 pb-3">

                                        <div class="col-lg-6">
                                            <h6 class="text-muted mb-3">Основное</h6>
                                            <div class="form-group">
                                                <label class="col-form-label">Название</label>
                                                <input type="text" class="form-control" name="title" value="{{ $quest->title }}">
                                            </div>
                                            <div class="form-group">
                                                <label class="col-form-label">Описание</label>
                                                <textarea class="form-control" name="description" rows="5">{{ $quest->description }}</textarea>
                                            </div>
                                            <div class="form-group">
                                                <label class="col-form-label">Тип</label>
                                                <select class="form-control" name="type" data-plugin-selectTwo>
                                                    @foreach($questTypes as $type)
                                                        <option value="{{ $type->value }}" @selected($quest->type === $type)>{{ $type->label() }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="form-group">
                                                <label class="col-form-label">Период сброса (часы, для повторяемых)</label>
                                                <input type="number" class="form-control" name="reset_period" value="{{ $quest->reset_period }}">
                                            </div>
                                        </div>

                                        <div class="col-lg-6">
                                            <h6 class="text-muted mb-3">Связи</h6>
                                            <div class="form-group">
                                                <label class="col-form-label">NPC начала</label>
                                                <select id="sel-start-npc" name="start_npc_id" class="form-control">
                                                    @if($quest->startNpc)
                                                        <option value="{{ $quest->startNpc->id }}" selected>[{{ $quest->startNpc->id }}] {{ $quest->startNpc->name }}</option>
                                                    @endif
                                                </select>
                                            </div>
                                            <div class="form-group">
                                                <label class="col-form-label">NPC завершения</label>
                                                <select id="sel-complete-npc" name="complete_npc_id" class="form-control">
                                                    @if($quest->completeNpc)
                                                        <option value="{{ $quest->completeNpc->id }}" selected>[{{ $quest->completeNpc->id }}] {{ $quest->completeNpc->name }}</option>
                                                    @endif
                                                </select>
                                            </div>
                                            <div class="form-group">
                                                <label class="col-form-label">Предыдущий квест</label>
                                                <select id="sel-parent-quest" name="parent_quest_id" class="form-control">
                                                    @if($quest->parentQuest)
                                                        <option value="{{ $quest->parentQuest->id }}" selected>[{{ $quest->parentQuest->id }}] {{ $quest->parentQuest->title }}</option>
                                                    @endif
                                                </select>
                                            </div>
                                            <div class="form-group">
                                                <label class="col-form-label">Следующий квест</label>
                                                <select id="sel-after-quest" name="after_quest_id" class="form-control">
                                                    @if($quest->afterQuest)
                                                        <option value="{{ $quest->afterQuest->id }}" selected>[{{ $quest->afterQuest->id }}] {{ $quest->afterQuest->title }}</option>
                                                    @endif
                                                </select>
                                            </div>
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label class="col-form-label">Активен</label>
                                                        <select class="form-control" name="is_active">
                                                            <option value="1" @selected($quest->is_active)>Да</option>
                                                            <option value="0" @selected(!$quest->is_active)>Нет</option>
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label class="col-form-label">Финальный</label>
                                                        <select class="form-control" name="is_finish">
                                                            <option value="0" @selected(!$quest->is_finish)>Нет</option>
                                                            <option value="1" @selected($quest->is_finish)>Да</option>
                                                        </select>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row mb-3">
                                        <div class="col-sm-12">
                                            <button class="btn btn-primary">Сохранить</button>
                                            <a href="{{ route('admin.quests') }}" class="btn btn-success">Назад</a>
                                        </div>
                                    </div>
                                </form>
                            </div>

                            {{-- ЗАДАНИЯ --}}
                            <div id="tab-objectives" class="tab-pane">
                                <div class="pt-3">
                                    <div class="mb-3">
                                        <a class="modal-with-zoom-anim ws-normal btn btn-sm btn-primary" href="#modalObjective">Добавить задание</a>
                                    </div>
                                    <div class="table-responsive">
                                        <table class="table table-hover table-bordered mb-none">
                                            <thead>
                                            <tr>
                                                <th width="50">ID</th>
                                                <th width="100">Тип</th>
                                                <th>Описание</th>
                                                <th width="100">Цель (ID)</th>
                                                <th width="100">Предмет</th>
                                                <th width="80">Кол-во</th>
                                                <th width="80">Шанс</th>
                                                <th width="70"></th>
                                            </tr>
                                            </thead>
                                            <tbody>
                                            @forelse($quest->objectives as $obj)
                                                <tr style="vertical-align: middle">
                                                    <td>{{ $obj->id }}</td>
                                                    <td><span class="badge badge-info">{{ $obj->type }}</span></td>
                                                    <td>{{ $obj->description }}</td>
                                                    <td>
                                                        @if($obj->type === 'deliver')
                                                            {{ $obj->shareItem?->name ?? $obj->target_id }}
                                                        @else
                                                            {{ $obj->target_id }}
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if($obj->collectItem)
                                                            <img src="{{ $obj->collectItem->image }}" style="width:20px;vertical-align:middle;" alt="">
                                                            {{ $obj->collectItem->name }}
                                                        @endif
                                                    </td>
                                                    <td>{{ $obj->required_amount }}</td>
                                                    <td>{{ $obj->drop_chance !== null ? $obj->drop_chance . '%' : '—' }}</td>
                                                    <td>
                                                        <a href="{{ route('admin.quest.objective.delete', [$quest->id, $obj->id]) }}"
                                                           class="btn btn-xs btn-danger"
                                                           onclick="return confirm('Удалить?')">Удалить</a>
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr><td colspan="8" class="text-center text-muted">Нет заданий</td></tr>
                                            @endforelse
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>

                            {{-- НАГРАДЫ --}}
                            <div id="tab-rewards" class="tab-pane">
                                <div class="pt-3">
                                    <div class="mb-3">
                                        <a class="modal-with-zoom-anim ws-normal btn btn-sm btn-primary" href="#modalReward">Добавить награду</a>
                                    </div>
                                    <div class="table-responsive">
                                        <table class="table table-hover table-bordered mb-none">
                                            <thead>
                                            <tr>
                                                <th width="50">ID</th>
                                                <th width="160">Тип</th>
                                                <th>Предмет / Локация</th>
                                                <th width="100">Кол-во</th>
                                                <th width="70"></th>
                                            </tr>
                                            </thead>
                                            <tbody>
                                            @forelse($quest->rewards as $reward)
                                                <tr style="vertical-align: middle">
                                                    <td>{{ $reward->id }}</td>
                                                    <td><span class="badge badge-primary">{{ $reward->type->label() }}</span></td>
                                                    <td>
                                                        @if($reward->itemInfo)
                                                            @if($reward->itemInfo->image)
                                                                <img src="{{ $reward->itemInfo->image }}" style="width:20px;vertical-align:middle;" alt="">
                                                            @endif
                                                            {{ $reward->itemInfo->name }}
                                                        @elseif($reward->location)
                                                            {{ $reward->location->name }}
                                                        @elseif($reward->reputation)
                                                            {{ $reward->reputation->name }}
                                                        @else
                                                            —
                                                        @endif
                                                    </td>
                                                    <td>{{ $reward->amount ?: '—' }}</td>
                                                    <td>
                                                        <a href="{{ route('admin.quest.reward.delete', [$quest->id, $reward->id]) }}"
                                                           class="btn btn-xs btn-danger"
                                                           onclick="return confirm('Удалить?')">Удалить</a>
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr><td colspan="5" class="text-center text-muted">Нет наград</td></tr>
                                            @endforelse
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>

                            {{-- ДИАЛОГ --}}
                            <div id="tab-dialogues" class="tab-pane">
                                <div class="pt-3">
                                    <p class="text-muted">Реплики показываются по одной на странице квеста до его принятия (по порядку). Текст ответа — это кнопка, по клику на которую игрок переходит к следующей реплике; на последней реплике клик по ней принимает квест. Если реплик нет — показывается обычное описание квеста.</p>
                                    <div class="mb-3">
                                        <a class="modal-with-zoom-anim ws-normal btn btn-sm btn-primary" href="#modalDialogue">Добавить реплику</a>
                                    </div>
                                    @foreach($quest->dialogues as $dialogue)
                                        <form action="{{ route('admin.quest.dialogue.update', [$quest->id, $dialogue->id]) }}" method="post" class="mb-3">
                                            {{ csrf_field() }}
                                            <div class="row">
                                                <div class="col-md-1">
                                                    <label class="col-form-label">Порядок</label>
                                                    <input type="number" min="1" class="form-control" name="order" value="{{ $dialogue->order }}">
                                                </div>
                                                <div class="col-md-6">
                                                    <label class="col-form-label">Текст реплики</label>
                                                    <textarea class="form-control" name="description" rows="3">{{ $dialogue->description }}</textarea>
                                                </div>
                                                <div class="col-md-3">
                                                    <label class="col-form-label">Текст ответа (кнопка)</label>
                                                    <input type="text" class="form-control" name="reply_text" value="{{ $dialogue->reply_text }}">
                                                </div>
                                                <div class="col-md-2 d-flex align-items-end" style="gap:6px;">
                                                    <button class="btn btn-primary btn-sm">Сохранить</button>
                                                    <a href="{{ route('admin.quest.dialogue.delete', [$quest->id, $dialogue->id]) }}"
                                                       class="btn btn-danger btn-sm"
                                                       onclick="return confirm('Удалить реплику?')">Удалить</a>
                                                </div>
                                            </div>
                                        </form>
                                    @endforeach
                                    @if($quest->dialogues->isEmpty())
                                        <p class="text-center text-muted">Реплик пока нет</p>
                                    @endif
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
            </section>
        </div>
    </div>

    <div id="modalDialogue" class="modal-block zoom-anim-dialog modal-block-primary mfp-hide">
        <section class="card">
            <form action="{{ route('admin.quest.dialogue.add', $quest->id) }}" method="post">
                <header class="card-header"><h2 class="card-title">Добавить реплику</h2></header>
                <div class="card-body">
                    {{ csrf_field() }}
                    <div class="form-group mb-2">
                        <label class="col-form-label">Порядок</label>
                        <input type="number" min="1" class="form-control" name="order" value="{{ $quest->dialogues->max('order') + 1 }}">
                    </div>
                    <div class="form-group mb-2">
                        <label class="col-form-label">Текст реплики</label>
                        <textarea class="form-control" name="description" rows="4" required></textarea>
                    </div>
                    <div class="form-group mb-2">
                        <label class="col-form-label">Текст ответа (кнопка)</label>
                        <input type="text" class="form-control" name="reply_text" value="Далее">
                    </div>
                </div>
                <footer class="card-footer">
                    <div class="text-end">
                        <button class="btn btn-primary">Сохранить</button>
                        <button type="button" class="btn btn-default modal-dismiss">Отмена</button>
                    </div>
                </footer>
            </form>
        </section>
    </div>

    {{-- Модалка: добавить задание --}}
    <div id="modalObjective" class="modal-block zoom-anim-dialog modal-block-primary mfp-hide">
        <section class="card">
            <form action="{{ route('admin.quest.objective.add', $quest->id) }}" method="post">
                <header class="card-header"><h2 class="card-title">Добавить задание</h2></header>
                <div class="card-body">
                    {{ csrf_field() }}
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group mb-2">
                                <label>Тип задания</label>
                                <select name="type" id="obj-type" class="form-control" data-plugin-selectTwo>
                                    <option value="kill">kill — убить монстра</option>
                                    <option value="collect">collect — собрать предмет</option>
                                    <option value="talk">talk — поговорить с NPC</option>
                                    <option value="deliver">deliver — сдать предмет</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group mb-2">
                                <label>Тип цели</label>
                                <select name="target_type" id="obj-target-type" class="form-control" data-plugin-selectTwo>
                                    <option value="monster">monster</option>
                                    <option value="npc">npc</option>
                                    <option value="item">item</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="form-group mb-2">
                        <label>ID цели <small class="text-muted">(ID монстра / NPC / предмета)</small></label>
                        <input type="number" class="form-control" name="target_id">
                    </div>
                    <div class="form-group mb-2" id="obj-item-row">
                        <label>Предмет для сбора (collect)</label>
                        <select id="obj-item-select" name="share_item_id" class="form-control"></select>
                    </div>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group mb-2">
                                <label>Кол-во</label>
                                <input type="number" class="form-control" name="required_amount" value="1" min="1">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group mb-2">
                                <label>Шанс дропа (%)</label>
                                <input type="number" step="0.01" class="form-control" name="drop_chance" placeholder="только для collect">
                            </div>
                        </div>
                    </div>
                    <div class="form-group mb-2">
                        <label>Описание для игрока</label>
                        <input type="text" class="form-control" name="description">
                    </div>
                </div>
                <footer class="card-footer">
                    <div class="col-md-12 text-end">
                        <button class="btn btn-primary">Добавить</button>
                        <button type="button" class="btn btn-default modal-dismiss">Отмена</button>
                    </div>
                </footer>
            </form>
        </section>
    </div>

    {{-- Модалка: добавить награду --}}
    <div id="modalReward" class="modal-block zoom-anim-dialog modal-block-primary mfp-hide">
        <section class="card">
            <form action="{{ route('admin.quest.reward.add', $quest->id) }}" method="post">
                <header class="card-header"><h2 class="card-title">Добавить награду</h2></header>
                <div class="card-body">
                    {{ csrf_field() }}
                    <div class="form-group mb-2">
                        <label>Тип награды</label>
                        <select name="type" id="reward-type" class="form-control" data-plugin-selectTwo>
                            @foreach($rewardTypes as $rt)
                                <option value="{{ $rt->value }}">{{ $rt->label() }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group mb-2">
                        <label>Количество <small class="text-muted">(опыт / монеты / очки)</small></label>
                        <input type="number" class="form-control" name="amount" value="0">
                    </div>
                    <div class="form-group mb-2" id="reward-item-row">
                        <label>Предмет <small class="text-muted">(для типа item)</small></label>
                        <select id="reward-item-select" name="share_item_id" class="form-control"></select>
                    </div>
                    <div class="form-group mb-2" id="reward-location-row">
                        <label>Локация <small class="text-muted">(для типа location_access)</small></label>
                        <select id="reward-location-select" name="location_id" class="form-control"></select>
                    </div>
                    <div class="form-group mb-2" id="reward-reputation-row">
                        <label>Репутация <small class="text-muted">(для типа reputation_points)</small></label>
                        <select name="reputation_id" class="form-control">
                            <option value="">— не выбрана —</option>
                            @foreach($reputations as $rep)
                                <option value="{{ $rep->id }}">[{{ $rep->id }}] {{ $rep->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <footer class="card-footer">
                    <div class="col-md-12 text-end">
                        <button class="btn btn-primary">Добавить</button>
                        <button type="button" class="btn btn-default modal-dismiss">Отмена</button>
                    </div>
                </footer>
            </form>
        </section>
    </div>

@push('footer_scripts')
<script>
    function makeAjaxSelect2(selector, url, placeholder) {
        $(selector).select2({
            theme: 'bootstrap',
            placeholder: placeholder,
            allowClear: true,
            ajax: {
                url: url,
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

    makeAjaxSelect2('#sel-start-npc',    '{{ route('admin.api.npcs') }}',   'Выберите NPC');
    makeAjaxSelect2('#sel-complete-npc', '{{ route('admin.api.npcs') }}',   'Выберите NPC');
    makeAjaxSelect2('#sel-parent-quest', '{{ route('admin.api.quests') }}', 'Выберите квест');
    makeAjaxSelect2('#sel-after-quest',  '{{ route('admin.api.quests') }}', 'Выберите квест');

    function formatItemOption(item) {
        if (!item.id) return item.text;
        var img = item.image
            ? '<img src="' + item.image + '" style="width:22px;height:22px;object-fit:contain;margin-right:6px;vertical-align:middle;">'
            : '';
        return $('<span>' + img + item.text + '</span>');
    }

    $('#obj-item-select').select2({
        theme: 'bootstrap',
        dropdownParent: $('#modalObjective'),
        placeholder: 'Выберите предмет',
        allowClear: true,
        templateResult: formatItemOption,
        templateSelection: formatItemOption,
        ajax: {
            url: '{{ route('admin.api.items') }}',
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

    $('#reward-item-select').select2({
        theme: 'bootstrap',
        dropdownParent: $('#modalReward'),
        placeholder: 'Выберите предмет',
        allowClear: true,
        templateResult: formatItemOption,
        templateSelection: formatItemOption,
        ajax: {
            url: '{{ route('admin.api.items') }}',
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

    $('#reward-location-select').select2({
        theme: 'bootstrap',
        dropdownParent: $('#modalReward'),
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

    // Показывать/скрывать строку предмета в зависимости от типа задания
    $('#obj-type').on('change', function () {
        $('#obj-item-row').toggle($(this).val() === 'collect');
    }).trigger('change');

    // Показывать/скрывать поля в зависимости от типа награды
    $('#reward-type').on('change', function () {
        var val = $(this).val();
        $('#reward-item-row').toggle(val === 'item');
        $('#reward-location-row').toggle(val === 'location_access');
        $('#reward-reputation-row').toggle(val === 'reputation_points');
    }).trigger('change');
</script>
@endpush

@endsection