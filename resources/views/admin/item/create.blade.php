@extends('admin.layout.base')

@section('title')
    Создать предмет
@endsection

@section('body')

    <div class="row">
        <div class="col-md-12">
            <section class="card">
                <div class="card-body">
                    <div class="tabs">
                        <ul class="nav nav-tabs" id="createTabs">
                            <li class="nav-item active">
                                <a class="nav-link active" data-bs-target="#tab-main" href="#tab-main" data-bs-toggle="tab">Основная</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" data-bs-target="#tab-gem" href="#tab-gem" data-bs-toggle="tab">Камень</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" data-bs-target="#tab-rune" href="#tab-rune" data-bs-toggle="tab">Руна</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" data-bs-target="#tab-scroll" href="#tab-scroll" data-bs-toggle="tab">Свиток заточки</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" data-bs-target="#tab-book" href="#tab-book" data-bs-toggle="tab">Книга</a>
                            </li>
                        </ul>

                        <form action="{{ route('admin.item.store') }}" method="post" enctype="multipart/form-data">
                            {{ csrf_field() }}
                            <div class="tab-content">

                                {{-- ОСНОВНАЯ --}}
                                <div id="tab-main" class="tab-pane active">
                                    <div class="row form-group pb-3 pt-3">
                                        <div class="col-lg-4">
                                            <div class="form-group">
                                                <label class="col-form-label" for="name">Название</label>
                                                <input type="text" class="form-control" name="name" id="name" value="{{ old('name') }}" placeholder="">
                                            </div>

                                            <div class="form-group">
                                                <label class="col-form-label" for="type">Тип</label>
                                                <select class="form-control" name="type" id="type">
                                                    @foreach(\App\Modules\Share\Domain\Enums\ShareItemType::cases() as $type)
                                                        <option value="{{ $type->value }}" @selected(old('type') === $type->value)>
                                                            {{ $type->label() }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>

                                            <div class="form-group">
                                                <label class="col-form-label" for="slot">Слот</label>
                                                <select class="form-control" name="slot" id="slot">
                                                    <option value=""></option>
                                                    @foreach(\App\Modules\Share\Domain\Enums\ShareItemSlot::cases() as $slot)
                                                        <option value="{{ $slot->value }}" @selected(old('slot') === $slot->value)>
                                                            {{ $slot->label() }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>

                                            <div class="form-group">
                                                <label class="col-form-label">Изображение</label>
                                                <div class="mb-2">
                                                    <img id="create-image-preview" src="" alt=""
                                                         style="width:64px;height:64px;object-fit:contain;border:1px solid #ddd;border-radius:4px;background:#f5f5f5;display:none;">
                                                </div>
                                                <input type="file" class="form-control" name="image" id="create-image" accept="image/*">
                                            </div>

                                            <div class="form-group">
                                                <label class="col-form-label">Картинка без фона</label>
                                                <div class="mb-2">
                                                    <img id="create-transparent-image-preview" src="" alt=""
                                                         style="width:64px;height:64px;object-fit:contain;border:1px solid #ddd;border-radius:4px;background:#f5f5f5;display:none;">
                                                </div>
                                                <input type="file" class="form-control" name="transparent_image" id="create-transparent-image" accept="image/png,image/webp,image/gif">
                                                <small class="form-text text-muted">Для карточек и интерфейсов с прозрачным фоном.</small>
                                            </div>

                                            <div class="form-group">
                                                <label class="col-form-label" for="description">Описание</label>
                                                <textarea class="form-control" name="description" rows="5" id="description">{{ old('description') }}</textarea>
                                            </div>
                                        </div>

                                        <div class="col-lg-3">
                                            <div class="form-group">
                                                <label class="col-form-label" for="price">Цена</label>
                                                <input type="text" class="form-control" id="price" name="price" value="{{ old('price', 0) }}">
                                            </div>

                                            <div class="form-group">
                                                <label class="col-form-label" for="break_crystal">Количество кристалов</label>
                                                <input type="text" class="form-control" id="break_crystal" name="break_crystal" value="{{ old('break_crystal', 0) }}">
                                            </div>

                                            <div class="form-group">
                                                <label class="col-form-label" for="count_use">Количество использований</label>
                                                <input type="text" class="form-control" id="count_use" name="count_use" value="{{ old('count_use', 0) }}">
                                            </div>

                                            <div class="form-group">
                                                <label class="col-form-label" for="max_drop_level_difference">Макс. разница уровней для дропа</label>
                                                <input type="number" min="0" max="255" class="form-control" id="max_drop_level_difference" name="max_drop_level_difference" value="{{ old('max_drop_level_difference') }}">
                                                <small class="form-text text-muted">Пусто — предмет выпадает всегда. Лимит учитывает, насколько уровень игрока выше уровня моба.</small>
                                            </div>

                                            <div class="form-group">
                                                <label class="col-form-label" for="expire">Срок жизни на локации, минут</label>
                                                <input type="number" min="1" class="form-control" id="expire" name="expire" value="{{ old('expire') }}">
                                                <small class="form-text text-muted">Оставьте пустым, чтобы предмет не исчезал.</small>
                                            </div>

                                            <div class="form-group">
                                                <label class="col-form-label" for="is_two_hand">Одна/Две руки</label>
                                                <select class="form-control" name="is_two_hand" id="is_two_hand">
                                                    <option value="0" @selected(old('is_two_hand', '0') === '0')>Одна рука</option>
                                                    <option value="1" @selected(old('is_two_hand') === '1')>Две руки</option>
                                                </select>
                                            </div>
                                        </div>

                                        <div class="col-lg-3">
                                            <div class="form-group">
                                                <label class="col-form-label" for="skill_id">Навык / профессия</label>
                                                <select id="skill_id" name="skill_id" data-plugin-selectTwo class="form-control populate placeholder" data-plugin-options='{ "placeholder": "Не выбран", "allowClear": true }'>
                                                    <option value=""></option>
                                                    @foreach($skills as $skill)
                                                        <option value="{{ $skill->id }}" @selected(old('skill_id') == $skill->id)>{{ $skill->name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="form-group">
                                                <label class="col-form-label" for="skill_lvl">Необходимый уровень</label>
                                                <input type="text" class="form-control" id="skill_lvl" name="skill_lvl" value="{{ old('skill_lvl') }}">
                                            </div>
                                            <div class="form-group">
                                                <label class="col-form-label" for="skill_exp">Опыт навыка за действие</label>
                                                <input type="text" class="form-control" id="skill_exp" name="skill_exp" value="{{ old('skill_exp') }}">
                                            </div>
                                            <div class="form-group">
                                                <label class="col-form-label" for="gathering_time_seconds">Время добычи, сек.</label>
                                                <input type="number" min="1" class="form-control" id="gathering_time_seconds" name="gathering_time_seconds" value="{{ old('gathering_time_seconds') }}">
                                            </div>
                                            <div class="form-group">
                                                <label class="col-form-label" for="gathering_respawn_seconds">Время респавна, сек.</label>
                                                <input type="number" min="1" class="form-control" id="gathering_respawn_seconds" name="gathering_respawn_seconds" value="{{ old('gathering_respawn_seconds') }}">
                                            </div>
                                            <div class="form-group">
                                                <label class="col-form-label" for="gathering_tool_family">Семейство инструмента для добычи</label>
                                                <select id="gathering_tool_family" name="gathering_tool_family" class="form-control" data-plugin-selectTwo data-plugin-options='{ "placeholder": "Не выбрано", "allowClear": true }'>
                                                    <option value=""></option>
                                                    @foreach($toolFamilies as $family)
                                                        <option value="{{ $family->value }}" @selected(old('gathering_tool_family') === $family->value)>{{ $family->label() }}</option>
                                                    @endforeach
                                                </select>
                                                <small class="form-text text-muted">Для ресурса: подойдёт любой инструмент этого семейства, тир только ускоряет добычу.</small>
                                            </div>
                                            <div class="form-group">
                                                <label class="col-form-label" for="tool_family">Семейство инструмента (если это инструмент)</label>
                                                <select id="tool_family" name="tool_family" class="form-control" data-plugin-selectTwo data-plugin-options='{ "placeholder": "Не выбрано", "allowClear": true }'>
                                                    <option value=""></option>
                                                    @foreach($toolFamilies as $family)
                                                        <option value="{{ $family->value }}" @selected(old('tool_family') === $family->value)>{{ $family->label() }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="form-group">
                                                <label class="col-form-label" for="gathering_speed_bonus_percent">Бонус скорости добычи, %</label>
                                                <input type="number" min="0" max="100" class="form-control" id="gathering_speed_bonus_percent" name="gathering_speed_bonus_percent" value="{{ old('gathering_speed_bonus_percent', 0) }}">
                                                <small class="form-text text-muted">На сколько % быстрее добывается ресурс с этим инструментом в руке. 0 — обычная скорость.</small>
                                            </div>
                                            <div class="form-group">
                                                <label class="col-form-label" for="gathering_double_chance_percent">Шанс двойной добычи, %</label>
                                                <input type="number" min="0" max="100" class="form-control" id="gathering_double_chance_percent" name="gathering_double_chance_percent" value="{{ old('gathering_double_chance_percent', 0) }}">
                                                <small class="form-text text-muted">Шанс получить ×2 ресурса за одну добычу с этим инструментом в руке. 0 — без шанса.</small>
                                            </div>
                                        </div>

                                        <div class="col-lg-2">
                                            <div class="form-group">
                                                <label class="col-form-label" for="is_active">Активный</label>
                                                <select class="form-control" name="is_active" id="is_active">
                                                    <option value="1" @selected(old('is_active', '1') === '1')>Да</option>
                                                    <option value="0" @selected(old('is_active') === '0')>Нет</option>
                                                </select>
                                            </div>
                                            <div class="form-group">
                                                <label class="col-form-label" for="is_sell">Можно продать магазину</label>
                                                <select class="form-control" name="is_sell" id="is_sell">
                                                    <option value="1" @selected(old('is_sell', '1') === '1')>Да</option>
                                                    <option value="0" @selected(old('is_sell') === '0')>Нет</option>
                                                </select>
                                            </div>
                                            <div class="form-group">
                                                <label class="col-form-label" for="is_auction_sellable">Можно продать на аукционе</label>
                                                <select class="form-control" name="is_auction_sellable" id="is_auction_sellable">
                                                    <option value="1" @selected(old('is_auction_sellable') === '1')>Да</option>
                                                    <option value="0" @selected(old('is_auction_sellable', '0') === '0')>Нет</option>
                                                </select>
                                            </div>
                                            <div class="form-group">
                                                <label class="col-form-label" for="is_give">Можно передать игроку</label>
                                                <select class="form-control" name="is_give" id="is_give">
                                                    <option value="1" @selected(old('is_give', '1') === '1')>Да</option>
                                                    <option value="0" @selected(old('is_give') === '0')>Нет</option>
                                                </select>
                                            </div>
                                            <div class="form-group">
                                                <label class="col-form-label" for="is_droppable">Можно выбросить</label>
                                                <select class="form-control" name="is_droppable" id="is_droppable">
                                                    <option value="1" @selected(old('is_droppable', '1') === '1')>Да</option>
                                                    <option value="0" @selected(old('is_droppable') === '0')>Нет</option>
                                                </select>
                                            </div>
                                            <div class="form-group">
                                                <label class="col-form-label" for="is_stackable">Складывается в одну ячейку</label>
                                                <select class="form-control" name="is_stackable" id="is_stackable">
                                                    <option value="1" @selected(old('is_stackable') === '1')>Да</option>
                                                    <option value="0" @selected(old('is_stackable', '0') === '0')>Нет</option>
                                                </select>
                                            </div>
                                            <div class="form-group">
                                                <label class="col-form-label" for="is_weight">Имеет вес</label>
                                                <select class="form-control" name="is_weight" id="is_weight">
                                                    <option value="1" @selected(old('is_weight', '1') === '1')>Да</option>
                                                    <option value="0" @selected(old('is_weight') === '0')>Нет</option>
                                                </select>
                                            </div>
                                            <div class="form-group">
                                                <label class="col-form-label" for="is_slot_usable">Использовать в слоте</label>
                                                <select class="form-control" name="is_slot_usable" id="is_slot_usable">
                                                    <option value="0" @selected(old('is_slot_usable', '0') === '0')>Нет</option>
                                                    <option value="1" @selected(old('is_slot_usable') === '1')>Да</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                {{-- КАМЕНЬ --}}
                                <div id="tab-gem" class="tab-pane">
                                    <div class="row pt-3 pb-3">
                                        <div class="col-lg-6">
                                            <p class="text-muted">Статы камня в формате JSON-массива. Каждый элемент: <code>{"type": "attack", "value": 10}</code></p>
                                            <div class="form-group">
                                                <label class="col-form-label">Статы камня (JSON)</label>
                                                <textarea class="form-control font-monospace" name="gem_stats_json" rows="10" id="gem_stats_json" placeholder='[{"type":"attack","value":5}]'>{{ old('gem_stats_json', '[]') }}</textarea>
                                            </div>
                                            <div class="mt-2">
                                                <strong>Доступные типы статов:</strong>
                                                <ul class="text-muted small mt-1">
                                                    <li><code>attack</code> — Атака</li>
                                                    <li><code>armor</code> — Защита</li>
                                                    <li><code>hp_max</code> — Макс HP</li>
                                                    <li><code>mp_max</code> — Макс MP</li>
                                                    <li><code>strength</code> — Сила</li>
                                                    <li><code>agility</code> — Ловкость</li>
                                                    <li><code>intelligence</code> — Интеллект</li>
                                                    <li><code>critical</code> — Крит шанс (%)</li>
                                                    <li><code>dodge</code> — Уворот (%)</li>
                                                </ul>
                                            </div>
                                        </div>
                                        <div class="col-lg-6">
                                            <label class="col-form-label">Конструктор</label>
                                            <div id="gem-builder">
                                                <div class="gem-stat-row d-flex gap-2 mb-2">
                                                    <select class="form-control gem-stat-type">
                                                        <option value="attack">Атака</option>
                                                        <option value="armor">Защита</option>
                                                        <option value="hp_max">Макс HP</option>
                                                        <option value="mp_max">Макс MP</option>
                                                        <option value="strength">Сила</option>
                                                        <option value="agility">Ловкость</option>
                                                        <option value="intelligence">Интеллект</option>
                                                        <option value="critical">Крит шанс</option>
                                                        <option value="dodge">Уворот</option>
                                                    </select>
                                                    <input type="number" class="form-control gem-stat-value" placeholder="Значение" style="max-width: 120px">
                                                    <button type="button" class="btn btn-danger btn-sm gem-remove-row">✕</button>
                                                </div>
                                            </div>
                                            <button type="button" class="btn btn-secondary btn-sm mt-2" id="gem-add-row">+ Добавить стат</button>
                                            <button type="button" class="btn btn-primary btn-sm mt-2" id="gem-apply">Применить в JSON</button>
                                        </div>
                                    </div>
                                </div>

                                {{-- РУНА --}}
                                <div id="tab-rune" class="tab-pane">
                                    <div class="row pt-3 pb-3">
                                        <div class="col-lg-4">
                                            <div class="form-group">
                                                <label class="col-form-label" for="rune_rarity">Редкость руны</label>
                                                <select class="form-control" name="rune_rarity" id="rune_rarity">
                                                    <option value="">— не указана —</option>
                                                    @foreach(\App\Modules\Structure\Blacksmith\Domain\Enums\RuneRarity::cases() as $rarity)
                                                        <option value="{{ $rarity->value }}" @selected(old('rune_rarity') === $rarity->value)
                                                                style="color: {{ $rarity->color() }}">
                                                            {{ $rarity->label() }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-lg-8">
                                            <label class="col-form-label">Пул статов руны</label>
                                            <p class="text-muted small">Отметьте статы, которые могут выпасть при вставке руны:</p>
                                            <div class="row">
                                                @php
                                                    $runeStats = [
                                                        'attack'        => 'Атака',
                                                        'armor'         => 'Защита',
                                                        'hp_max'        => 'Макс HP',
                                                        'mp_max'        => 'Макс MP',
                                                        'strength'      => 'Сила',
                                                        'agility'       => 'Ловкость',
                                                        'intelligence'  => 'Интеллект',
                                                        'critical'      => 'Крит шанс',
                                                        'dodge'         => 'Уворот',
                                                    ];
                                                @endphp
                                                @foreach($runeStats as $key => $label)
                                                    <div class="col-lg-4 col-md-6">
                                                        <div class="checkbox">
                                                            <label>
                                                                <input type="checkbox" name="rune_stat_pool[]" value="{{ $key }}"
                                                                    @checked(in_array($key, old('rune_stat_pool', [])))>
                                                                {{ $label }}
                                                            </label>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                {{-- СВИТОК ЗАТОЧКИ --}}
                                <div id="tab-scroll" class="tab-pane">
                                    <div class="row pt-3 pb-3">
                                        <div class="col-lg-5">
                                            <div class="form-group">
                                                <label class="col-form-label" for="upgrade_scroll_type">Тип свитка заточки</label>
                                                <select class="form-control" name="upgrade_scroll_type" id="upgrade_scroll_type">
                                                    <option value="">— не свиток заточки —</option>
                                                    @foreach(\App\Modules\Structure\Blacksmith\Domain\Enums\UpgradeScrollType::cases() as $scrollType)
                                                        <option value="{{ $scrollType->value }}" @selected(old('upgrade_scroll_type') === $scrollType->value)>
                                                            {{ $scrollType->label() }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="mt-3">
                                                @foreach(\App\Modules\Structure\Blacksmith\Domain\Enums\UpgradeScrollType::cases() as $scrollType)
                                                    <div class="mb-2">
                                                        <strong>{{ $scrollType->label() }}</strong>
                                                        <span class="text-muted small"> — {{ $scrollType->description() }}</span>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                {{-- КНИГА --}}
                                <div id="tab-book" class="tab-pane">
                                    <div class="row pt-3 pb-3">
                                        <div class="col-lg-5">
                                            <div class="form-group">
                                                <label class="col-form-label" for="magic_skill_id">Заклинание (для типа «Книга заклинаний»)</label>
                                                <select id="magic_skill_id" name="magic_skill_id" data-plugin-selectTwo class="form-control populate placeholder" data-plugin-options='{ "placeholder": "Не выбрано", "allowClear": true }'>
                                                    <option value=""></option>
                                                    @foreach($magicSkills as $id => $name)
                                                        <option value="{{ $id }}"
                                                            @selected(old('magic_skill_id') == $id)
                                                            @disabled(in_array($id, $claimedMagicSkillIds))>
                                                            {{ $name }}{{ in_array($id, $claimedMagicSkillIds) ? ' (уже привязано)' : '' }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                                <small class="form-text text-muted">Игрок, использующий эту книгу, изучит выбранное заклинание.</small>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                            </div>

                            <div class="row justify-content-start mt-3 mb-3">
                                <div class="col-sm-12">
                                    <button class="btn btn-primary">Создать</button>
                                    <a href="{{ route('admin.items') }}" class="btn btn-success">Назад</a>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </section>
        </div>
    </div>

@include('admin.layout.summernote', ['selector' => 'textarea[name=description]'])

@push('footer_scripts')
<script>
        // Image preview
        document.getElementById('create-image').addEventListener('change', function () {
            const file = this.files[0];
            const preview = document.getElementById('create-image-preview');
            if (file) {
                preview.src = URL.createObjectURL(file);
                preview.style.display = 'block';
            }
        });

        document.getElementById('create-transparent-image').addEventListener('change', function () {
            const file = this.files[0];
            const preview = document.getElementById('create-transparent-image-preview');
            if (file) {
                preview.src = URL.createObjectURL(file);
                preview.style.display = 'block';
            }
        });

        // Gem stat builder
        document.getElementById('gem-add-row').addEventListener('click', function () {
            const row = document.querySelector('.gem-stat-row').cloneNode(true);
            row.querySelector('.gem-stat-value').value = '';
            document.getElementById('gem-builder').appendChild(row);
        });

        document.getElementById('gem-builder').addEventListener('click', function (e) {
            if (e.target.classList.contains('gem-remove-row')) {
                const rows = document.querySelectorAll('.gem-stat-row');
                if (rows.length > 1) e.target.closest('.gem-stat-row').remove();
            }
        });

        document.getElementById('gem-apply').addEventListener('click', function () {
            const rows = document.querySelectorAll('.gem-stat-row');
            const stats = [];
            rows.forEach(row => {
                const type  = row.querySelector('.gem-stat-type').value;
                const value = parseInt(row.querySelector('.gem-stat-value').value, 10);
                if (type && !isNaN(value) && value !== 0) {
                    stats.push({ type, value });
                }
            });
            document.getElementById('gem_stats_json').value = JSON.stringify(stats, null, 2);
        });
</script>
@endpush

@endsection
