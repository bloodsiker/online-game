@extends('admin.layout.base')

@php
    $editing = $injuryType->exists;
    $modifiers = array_values(old('stat_modifiers', $injuryType->stat_modifiers ?? []));
@endphp

@section('title')
    {{ $editing ? 'Травма: '.$injuryType->name : 'Новая травма' }}
@endsection

@section('body')
    <div class="row">
        <div class="col-md-8">
            <section class="card">
                <div class="card-body">
                    @if($errors->any())
                        <div class="alert alert-danger">
                            <strong>Травма не сохранена:</strong>
                            <ul class="mb-0">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form action="{{ $editing ? route('admin.injury_type.update', $injuryType) : route('admin.injury_type.store') }}" method="post" enctype="multipart/form-data">
                        {{ csrf_field() }}

                        <div class="row">
                            <div class="col-md-8">
                                <div class="form-group">
                                    <label>Название</label>
                                    <input class="form-control" name="name" maxlength="120" required value="{{ old('name', $injuryType->name) }}">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Slug</label>
                                    <input class="form-control" name="slug" maxlength="120" required value="{{ old('slug', $injuryType->slug) }}" placeholder="broken-left-arm">
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Описание</label>
                            <textarea class="form-control" name="description" rows="3">{{ old('description', $injuryType->description) }}</textarea>
                        </div>

                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Часть тела</label>
                                    <select class="form-control" name="body_part" required>
                                        @foreach($bodyParts as $part)
                                            <option value="{{ $part->value }}" @selected(old('body_part', $injuryType->body_part?->value) === $part->value)>{{ $part->label() }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Уровень тяжести</label>
                                    <select class="form-control" name="severity" required>
                                        @foreach($severities as $severity)
                                            <option value="{{ $severity->value }}" @selected((int) old('severity', $injuryType->severity?->value ?? 1) === $severity->value)>{{ $severity->value }} — {{ $severity->label() }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label>Минуты</label>
                                    <input type="number" min="1" max="43200" class="form-control" name="duration_minutes" required value="{{ old('duration_minutes', $editing ? $injuryType->durationMinutes() : 15) }}">
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label>Вес</label>
                                    <input type="number" min="0" max="100000" class="form-control" name="drop_weight" required value="{{ old('drop_weight', $injuryType->drop_weight ?? 10) }}">
                                </div>
                            </div>
                        </div>
                        <p class="text-muted">Вес определяет относительную вероятность выбора этой травмы среди всех активных. Значение 0 исключает её из выпадения.</p>

                        <div class="form-group">
                            <label>Картинка травмы</label>
                            @if($injuryType->image)
                                <div class="mb-2">
                                    <img src="{{ $injuryType->image }}" alt="{{ $injuryType->name }}" style="width:60px;height:60px;object-fit:contain;border:1px solid #ddd;padding:3px;background:#fff;">
                                </div>
                            @endif
                            <input type="file" class="form-control" name="image" accept="image/*">
                            <small class="form-text text-muted">Рекомендуемый размер 60×60 px, максимум 4 МБ. Без картинки используется стандартный CSS-бинт.</small>
                            @if($injuryType->image)
                                <div class="checkbox-custom checkbox-default mt-2">
                                    <input type="checkbox" id="delete-injury-image" name="delete_image" value="1">
                                    <label for="delete-injury-image">Удалить текущую картинку</label>
                                </div>
                            @endif
                        </div>

                        <section class="card mt-3">
                            <header class="card-header"><h2 class="card-title">Изменение характеристик</h2></header>
                            <div class="card-body">
                                <p class="text-muted">Для штрафа указывайте отрицательное значение, например −10% к силе.</p>
                                <div id="injury-modifiers">
                                    @foreach($modifiers as $index => $modifier)
                                        <div class="row align-items-end injury-modifier-row mb-2">
                                            <div class="col-md-5">
                                                <label>Характеристика</label>
                                                <select class="form-control" name="stat_modifiers[{{ $index }}][stat]" required>
                                                    @foreach($statKeys as $statKey)
                                                        <option value="{{ $statKey->value }}" @selected(($modifier['stat'] ?? '') === $statKey->value)>{{ $statKey->label() }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="col-md-3"><label>Значение</label><input class="form-control" type="number" step="0.01" name="stat_modifiers[{{ $index }}][value]" required value="{{ $modifier['value'] ?? 0 }}"></div>
                                            <div class="col-md-2"><label>Режим</label><select class="form-control" name="stat_modifiers[{{ $index }}][is_percent]"><option value="0" @selected(empty($modifier['is_percent']))>Число</option><option value="1" @selected(!empty($modifier['is_percent']))>Проценты</option></select></div>
                                            <div class="col-md-2"><button type="button" class="btn btn-danger btn-sm remove-injury-modifier">Удалить</button></div>
                                        </div>
                                    @endforeach
                                </div>
                                <button type="button" id="add-injury-modifier" class="btn btn-default btn-sm">Добавить характеристику</button>
                            </div>
                        </section>

                        <div class="checkbox-custom checkbox-default mt-3">
                            <input type="checkbox" id="injury-is-active" name="is_active" value="1" @checked(old('is_active', $injuryType->is_active ?? true))>
                            <label for="injury-is-active">Может выпадать после смерти</label>
                        </div>

                        <div class="mt-3">
                            <button class="btn btn-primary">Сохранить</button>
                            <a href="{{ route('admin.injury_types') }}" class="btn btn-success">Назад</a>
                        </div>
                    </form>

                    @if($editing)
                        <form action="{{ route('admin.injury_type.destroy', $injuryType) }}" method="post" class="mt-3" onsubmit="return confirm('Удалить эту травму?');">
                            {{ csrf_field() }}
                            @method('DELETE')
                            <button class="btn btn-danger btn-sm">Удалить травму</button>
                            @if($injuryType->playerInjuries()->exists())
                                <small class="text-muted ml-2">Удаление недоступно, пока существуют выданные экземпляры.</small>
                            @endif
                        </form>
                    @endif
                </div>
            </section>
        </div>
    </div>

    <template id="injury-modifier-template">
        <div class="row align-items-end injury-modifier-row mb-2">
            <div class="col-md-5"><label>Характеристика</label><select class="form-control" name="stat_modifiers[__INDEX__][stat]" required>@foreach($statKeys as $statKey)<option value="{{ $statKey->value }}">{{ $statKey->label() }}</option>@endforeach</select></div>
            <div class="col-md-3"><label>Значение</label><input class="form-control" type="number" step="0.01" name="stat_modifiers[__INDEX__][value]" required value="-5"></div>
            <div class="col-md-2"><label>Режим</label><select class="form-control" name="stat_modifiers[__INDEX__][is_percent]"><option value="0">Число</option><option value="1" selected>Проценты</option></select></div>
            <div class="col-md-2"><button type="button" class="btn btn-danger btn-sm remove-injury-modifier">Удалить</button></div>
        </div>
    </template>

    <script>
        (function () {
            const list = document.getElementById('injury-modifiers');
            const template = document.getElementById('injury-modifier-template');
            const addButton = document.getElementById('add-injury-modifier');
            if (!list || !template || !addButton) return;

            let index = {{ count($modifiers) }};
            addButton.addEventListener('click', function () {
                list.insertAdjacentHTML('beforeend', template.innerHTML.replaceAll('__INDEX__', index++));
            });
            list.addEventListener('click', function (event) {
                if (event.target.classList.contains('remove-injury-modifier')) {
                    event.target.closest('.injury-modifier-row').remove();
                }
            });
        })();
    </script>
@endsection
