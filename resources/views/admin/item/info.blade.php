@extends('admin.layout.base')

@section('title')
    {{ $item->name }}
@endsection

@section('body')

    @include('admin.item.navigation', ['item' => $item])

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
                                <a class="nav-link" data-bs-target="#tab-rarity-upgrade" href="#tab-rarity-upgrade" data-bs-toggle="tab">Апгрейд редкости</a>
                            </li>
                            @if($item->type === \App\Modules\Share\Domain\Enums\ShareItemType::GEM)
                                <li class="nav-item">
                                    <a class="nav-link" data-bs-target="#tab-gem" href="#tab-gem" data-bs-toggle="tab">Камень</a>
                                </li>
                            @endif
                            @if($item->type === \App\Modules\Share\Domain\Enums\ShareItemType::RUNE)
                                <li class="nav-item">
                                    <a class="nav-link" data-bs-target="#tab-rune" href="#tab-rune" data-bs-toggle="tab">Руна</a>
                                </li>
                            @endif
                            @if($item->upgrade_scroll_type !== null || $item->type === \App\Modules\Share\Domain\Enums\ShareItemType::SCROLL)
                                <li class="nav-item">
                                    <a class="nav-link" data-bs-target="#tab-scroll" href="#tab-scroll" data-bs-toggle="tab">Свиток заточки</a>
                                </li>
                            @endif
                            @if($item->type === \App\Modules\Share\Domain\Enums\ShareItemType::BOOK)
                                <li class="nav-item">
                                    <a class="nav-link" data-bs-target="#tab-book" href="#tab-book" data-bs-toggle="tab">Книга</a>
                                </li>
                            @endif
                        </ul>

                        <form id="item-info-form" action="{{ route('admin.item.info', $item->id) }}" method="post" enctype="multipart/form-data" data-floating-save-form>
                            {{ csrf_field() }}
                            <div class="tab-content">

                                {{-- ОСНОВНАЯ --}}
                                <div id="tab-main" class="tab-pane active">
                                    <div class="row pt-3 pb-3">
                                        <div class="col-lg-4">
                                            <div class="form-group">
                                                <label class="col-form-label">Название</label>
                                                <input type="text" class="form-control" name="name" value="{{ $item->name }}">
                                            </div>
                                            <div class="form-group">
                                                <label class="col-form-label">Тип</label>
                                                <select class="form-control" name="type" data-plugin-selectTwo>
                                                    @foreach(\App\Modules\Share\Domain\Enums\ShareItemType::cases() as $type)
                                                        <option value="{{ $type->value }}" @selected($item->type === $type)>{{ $type->label() }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="form-group">
                                                <label class="col-form-label">Редкость</label>
                                                <select class="form-control" name="rarity">
                                                    @foreach($rarities as $rarity)
                                                        <option value="{{ $rarity->value }}"
                                                                style="color:{{ $rarity->color() }}"
                                                                @selected($item->rarity === $rarity)>
                                                            {{ $rarity->label() }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="form-group">
                                                <label class="col-form-label">Слот</label>
                                                <select class="form-control" name="slot" data-plugin-selectTwo>
                                                    <option value=""></option>
                                                    @foreach(\App\Modules\Share\Domain\Enums\ShareItemSlot::cases() as $slot)
                                                        <option value="{{ $slot->value }}" @selected($item->slot === $slot)>{{ $slot->label() }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="form-group">
                                                <label class="col-form-label">Изображение</label>
                                                <div class="mb-2">
                                                    <img id="item-image-preview" src="{{ $item->image }}" alt=""
                                                         style="width:64px;height:64px;object-fit:contain;border:1px solid #ddd;border-radius:4px;background:#f5f5f5;{{ $item->image ? '' : 'display:none;' }}">
                                                    @if($item->image)
                                                        <br><small class="text-muted">{{ $item->image }}</small>
                                                    @endif
                                                </div>
                                                <input type="file" class="form-control" name="image" id="image" accept="image/*">
                                                @if($item->getRawOriginal('image'))
                                                    <div class="checkbox-custom checkbox-danger mt-1">
                                                        <input type="checkbox" id="item-delete-image" name="delete_image" value="1">
                                                        <label for="item-delete-image">Удалить картинку</label>
                                                    </div>
                                                @endif
                                            </div>
                                            <div class="form-group">
                                                <label class="col-form-label">Картинка без фона</label>
                                                <div class="mb-2">
                                                    <img id="item-transparent-image-preview" src="{{ $item->transparent_image ?? '' }}" alt=""
                                                         style="width:64px;height:64px;object-fit:contain;border:1px solid #ddd;border-radius:4px;background:#f5f5f5;{{ $item->transparent_image ? '' : 'display:none;' }}">
                                                    @if($item->transparent_image)
                                                        <br><small class="text-muted">{{ $item->transparent_image }}</small>
                                                    @endif
                                                </div>
                                                <input type="file" class="form-control" name="transparent_image" id="transparent_image" accept="image/png,image/webp,image/gif">
                                                <small class="form-text text-muted">Для карточек и интерфейсов с прозрачным фоном.</small>
                                                @if($item->getRawOriginal('transparent_image'))
                                                    <div class="checkbox-custom checkbox-danger mt-1">
                                                        <input type="checkbox" id="item-delete-transparent-image" name="delete_transparent_image" value="1">
                                                        <label for="item-delete-transparent-image">Удалить картинку без фона</label>
                                                    </div>
                                                @endif
                                            </div>
                                            <div class="form-group">
                                                <label class="col-form-label">Описание</label>
                                                <textarea class="form-control" name="description" rows="5">{{ $item->description }}</textarea>
                                            </div>
                                        </div>

                                        <div class="col-lg-3">
                                            <div class="form-group">
                                                <label class="col-form-label">Цена</label>
                                                <input type="number" class="form-control" name="price" value="{{ $item->price }}">
                                            </div>
                                            <div class="form-group">
                                                <label class="col-form-label">Количество кристаллов</label>
                                                <input type="number" class="form-control" name="break_crystal" value="{{ $item->break_crystal }}">
                                            </div>
                                            <div class="form-group">
                                                <label class="col-form-label">Количество использований</label>
                                                <input type="number" class="form-control" name="count_use" value="{{ $item->count_use }}">
                                            </div>
                                            <div class="form-group">
                                                <label class="col-form-label" for="max_drop_level_difference">Макс. разница уровней для дропа</label>
                                                <input type="number" min="0" max="255" class="form-control" id="max_drop_level_difference" name="max_drop_level_difference" value="{{ old('max_drop_level_difference', $item->max_drop_level_difference) }}">
                                                <small class="form-text text-muted">Пусто — предмет выпадает всегда. Лимит учитывает, насколько уровень игрока выше уровня моба.</small>
                                            </div>
                                            <div class="form-group">
                                                <label class="col-form-label" for="expire">Срок жизни на локации, минут</label>
                                                <input type="number" min="1" class="form-control" id="expire" name="expire" value="{{ old('expire', $item->expire) }}">
                                                <small class="form-text text-muted">Оставьте пустым, чтобы предмет не исчезал.</small>
                                            </div>
                                            <div class="form-group">
                                                <label class="col-form-label">Одна/Две руки</label>
                                                <select class="form-control" name="is_two_hand">
                                                    <option value="0" @selected(!$item->is_two_hand)>Одна рука</option>
                                                    <option value="1" @selected($item->is_two_hand)>Две руки</option>
                                                </select>
                                            </div>
                                        </div>

                                        <div class="col-lg-3">
                                            <div class="form-group">
                                                <label class="col-form-label">Навык / профессия</label>
                                                <select name="skill_id" class="form-control" data-plugin-selectTwo
                                                        data-plugin-options='{ "placeholder": "Не выбран", "allowClear": true }'>
                                                    <option value=""></option>
                                                    @foreach($skills as $skill)
                                                        <option value="{{ $skill->id }}" @selected($item->skill_id === $skill->id)>{{ $skill->name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="form-group">
                                                <label class="col-form-label">Необходим уровень навыка</label>
                                                <input type="number" class="form-control" name="skill_lvl" value="{{ $item->skill_lvl }}">
                                            </div>
                                            <div class="form-group">
                                                <label class="col-form-label">Опыт навыка за действие</label>
                                                <input type="number" class="form-control" name="skill_exp" value="{{ $item->skill_exp }}">
                                            </div>
                                            <div class="form-group">
                                                <label class="col-form-label">Время добычи, сек.</label>
                                                <input type="number" min="1" class="form-control" name="gathering_time_seconds" value="{{ $item->gathering_time_seconds }}">
                                            </div>
                                            <div class="form-group">
                                                <label class="col-form-label">Время респавна, сек.</label>
                                                <input type="number" min="1" class="form-control" name="gathering_respawn_seconds" value="{{ $item->gathering_respawn_seconds }}">
                                            </div>
                                            <div class="form-group">
                                                <label class="col-form-label">Семейство инструмента для добычи</label>
                                                <select name="gathering_tool_family" class="form-control" data-plugin-selectTwo data-plugin-options='{ "placeholder": "Не выбрано", "allowClear": true }'>
                                                    <option value=""></option>
                                                    @foreach($toolFamilies as $family)
                                                        <option value="{{ $family->value }}" @selected($item->gathering_tool_family === $family->value)>{{ $family->label() }}</option>
                                                    @endforeach
                                                </select>
                                                <small class="form-text text-muted">Для ресурса: подойдёт любой инструмент этого семейства, тир только ускоряет добычу.</small>
                                            </div>
                                            <div class="form-group">
                                                <label class="col-form-label">Семейство инструмента (если это инструмент)</label>
                                                <select name="tool_family" class="form-control" data-plugin-selectTwo data-plugin-options='{ "placeholder": "Не выбрано", "allowClear": true }'>
                                                    <option value=""></option>
                                                    @foreach($toolFamilies as $family)
                                                        <option value="{{ $family->value }}" @selected($item->tool_family === $family->value)>{{ $family->label() }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="form-group">
                                                <label class="col-form-label">Бонус скорости добычи, %</label>
                                                <input type="number" min="0" max="100" class="form-control" name="gathering_speed_bonus_percent" value="{{ $item->gathering_speed_bonus_percent }}">
                                                <small class="form-text text-muted">На сколько % быстрее добывается ресурс с этим инструментом в руке. 0 — обычная скорость.</small>
                                            </div>
                                            <div class="form-group">
                                                <label class="col-form-label">Шанс двойной добычи, %</label>
                                                <input type="number" min="0" max="100" class="form-control" name="gathering_double_chance_percent" value="{{ $item->gathering_double_chance_percent }}">
                                                <small class="form-text text-muted">Шанс получить ×2 ресурса за одну добычу с этим инструментом в руке. 0 — без шанса.</small>
                                            </div>
                                        </div>

                                        <div class="col-lg-2">
                                            <div class="form-group">
                                                <label class="col-form-label">Активный</label>
                                                <select class="form-control" name="is_active">
                                                    <option value="1" @selected($item->is_active)>Да</option>
                                                    <option value="0" @selected(!$item->is_active)>Нет</option>
                                                </select>
                                            </div>
                                            <div class="form-group">
                                                <label class="col-form-label">Можно продать магазину</label>
                                                <select class="form-control" name="is_sell">
                                                    <option value="1" @selected($item->is_sell)>Да</option>
                                                    <option value="0" @selected(!$item->is_sell)>Нет</option>
                                                </select>
                                            </div>
                                            <div class="form-group">
                                                <label class="col-form-label">Можно продать на аукционе</label>
                                                <select class="form-control" name="is_auction_sellable">
                                                    <option value="1" @selected($item->is_auction_sellable)>Да</option>
                                                    <option value="0" @selected(!$item->is_auction_sellable)>Нет</option>
                                                </select>
                                            </div>
                                            <div class="form-group">
                                                <label class="col-form-label">Можно передать игроку</label>
                                                <select class="form-control" name="is_give">
                                                    <option value="1" @selected($item->is_give)>Да</option>
                                                    <option value="0" @selected(!$item->is_give)>Нет</option>
                                                </select>
                                            </div>
                                            <div class="form-group">
                                                <label class="col-form-label">Можно выбросить</label>
                                                <select class="form-control" name="is_droppable">
                                                    <option value="1" @selected($item->is_droppable)>Да</option>
                                                    <option value="0" @selected(!$item->is_droppable)>Нет</option>
                                                </select>
                                            </div>
                                            <div class="form-group">
                                                <label class="col-form-label">Складывается в одну ячейку</label>
                                                <select class="form-control" name="is_stackable">
                                                    <option value="1" @selected($item->is_stackable)>Да</option>
                                                    <option value="0" @selected(!$item->is_stackable)>Нет</option>
                                                </select>
                                            </div>
                                            <div class="form-group">
                                                <label class="col-form-label">Имеет вес</label>
                                                <select class="form-control" name="is_weight">
                                                    <option value="1" @selected($item->is_weight)>Да</option>
                                                    <option value="0" @selected(!$item->is_weight)>Нет</option>
                                                </select>
                                            </div>
                                            <div class="form-group">
                                                <label class="col-form-label">Использовать в слоте</label>
                                                <select class="form-control" name="is_slot_usable">
                                                    <option value="0" @selected(!$item->is_slot_usable)>Нет</option>
                                                    <option value="1" @selected($item->is_slot_usable)>Да</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div id="tab-rarity-upgrade" class="tab-pane">
                                    <div class="row pt-3 pb-3">
                                        <div class="col-lg-6">
                                            <div class="form-group">
                                                <label class="col-form-label">Предмет после апгрейда</label>
                                                <select name="upgrade_to_share_item_id" class="form-control" data-plugin-selectTwo data-plugin-options='{ "placeholder": "Не настроен", "allowClear": true }'>
                                                    <option value=""></option>
                                                    @foreach($upgradeTargets as $target)
                                                        <option value="{{ $target->id }}" @selected($item->upgrade_to_share_item_id === $target->id)>
                                                            [{{ $target->id }}] {{ $target->name }} — {{ $target->rarity->label() }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                                <small class="form-text text-muted">Экземпляр предмета превратится в выбранный предмет, сохранив заточку, камни и руны.</small>
                                            </div>
                                            <div class="form-group">
                                                <label class="col-form-label">Стоимость в монетах</label>
                                                <input type="number" min="0" class="form-control" name="upgrade_gold_cost" value="{{ old('upgrade_gold_cost', $item->upgrade_gold_cost) }}">
                                                <small class="form-text text-muted">Материалы добавляются ниже после сохранения предмета.</small>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                {{-- КАМЕНЬ --}}
                                @if($item->type === \App\Modules\Share\Domain\Enums\ShareItemType::GEM)
                                    <div id="tab-gem" class="tab-pane">
                                        <div class="row pt-3 pb-3">
                                            <div class="col-lg-6">
                                                <p class="text-muted">Каждый элемент: <code>{"type": "attack", "value": 10}</code></p>
                                                <div class="form-group">
                                                    <label class="col-form-label">Статы камня (JSON)</label>
                                                    <textarea class="form-control font-monospace" name="gem_stats_json" rows="10" id="info_gem_stats_json">{{ old('gem_stats_json', json_encode($item->gem_stats ?? [], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)) }}</textarea>
                                                </div>
                                            </div>
                                            <div class="col-lg-6">
                                                <label class="col-form-label">Конструктор</label>
                                                <div id="info-gem-builder"></div>
                                                <button type="button" class="btn btn-secondary btn-sm mt-2" id="info-gem-add-row">+ Добавить стат</button>
                                                <button type="button" class="btn btn-info btn-sm mt-2" id="info-gem-load">← Загрузить из JSON</button>
                                                <button type="button" class="btn btn-primary btn-sm mt-2" id="info-gem-apply">Применить в JSON</button>
                                            </div>
                                        </div>
                                    </div>
                                @endif

                                {{-- РУНА --}}
                                @if($item->type === \App\Modules\Share\Domain\Enums\ShareItemType::RUNE)
                                    <div id="tab-rune" class="tab-pane">
                                        <div class="row pt-3 pb-3">
                                            <div class="col-lg-4">
                                                <div class="form-group">
                                                    <label class="col-form-label">Редкость руны</label>
                                                    <select class="form-control" name="rune_rarity">
                                                        <option value="">— не указана —</option>
                                                        @foreach(\App\Modules\Structure\Blacksmith\Domain\Enums\RuneRarity::cases() as $rarity)
                                                            <option value="{{ $rarity->value }}"
                                                                @selected(old('rune_rarity', $item->rune_rarity?->value) === $rarity->value)
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
                                                        $runeStats = ['attack' => 'Атака', 'armor' => 'Защита', 'hp_max' => 'Макс HP', 'mp_max' => 'Макс MP', 'strength' => 'Сила', 'agility' => 'Ловкость', 'intelligence' => 'Интеллект', 'critical' => 'Крит шанс', 'dodge' => 'Уворот'];
                                                        $currentPool = old('rune_stat_pool', $item->rune_stat_pool ?? []);
                                                    @endphp
                                                    @foreach($runeStats as $key => $label)
                                                        <div class="col-lg-4 col-md-6">
                                                            <div class="checkbox">
                                                                <label>
                                                                    <input type="checkbox" name="rune_stat_pool[]" value="{{ $key }}" @checked(in_array($key, $currentPool))>
                                                                    {{ $label }}
                                                                </label>
                                                            </div>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endif

                                {{-- СВИТОК ЗАТОЧКИ --}}
                                @if($item->upgrade_scroll_type !== null || $item->type === \App\Modules\Share\Domain\Enums\ShareItemType::SCROLL)
                                    <div id="tab-scroll" class="tab-pane">
                                        <div class="row pt-3 pb-3">
                                            <div class="col-lg-5">
                                                <div class="form-group">
                                                    <label class="col-form-label">Тип свитка заточки</label>
                                                    <select class="form-control" name="upgrade_scroll_type">
                                                        <option value="">— не свиток заточки —</option>
                                                        @foreach(\App\Modules\Structure\Blacksmith\Domain\Enums\UpgradeScrollType::cases() as $scrollType)
                                                            <option value="{{ $scrollType->value }}"
                                                                @selected(old('upgrade_scroll_type', $item->upgrade_scroll_type?->value) === $scrollType->value)>
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
                                @endif

                                {{-- КНИГА --}}
                                @if($item->type === \App\Modules\Share\Domain\Enums\ShareItemType::BOOK)
                                    <div id="tab-book" class="tab-pane">
                                        <div class="row pt-3 pb-3">
                                            <div class="col-lg-5">
                                                <div class="form-group">
                                                    <label class="col-form-label">Заклинание</label>
                                                    <select name="magic_skill_id" class="form-control" data-plugin-selectTwo
                                                            data-plugin-options='{ "placeholder": "Не выбрано", "allowClear": true }'>
                                                        <option value=""></option>
                                                        @foreach($magicSkills as $id => $name)
                                                            <option value="{{ $id }}"
                                                                @selected(old('magic_skill_id', $item->magicSkillBook?->magic_skill_id) == $id)
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
                                @endif

                            </div>

                            <div class="row mb-3 mt-2">
                                <div class="col-sm-12">
                                    <button class="btn btn-primary">Сохранить</button>
                                    <a href="{{ route('admin.items') }}" class="btn btn-success">Назад</a>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </section>
        </div>
    </div>

    {{-- ПАССИВНЫЕ СТАТЫ --}}
    <div class="row">
        <div class="col-md-12">
            <section class="card">
                <header class="card-header">
                    <h2 class="card-title">Пассивные статы</h2>
                    <p class="card-subtitle text-muted">Постоянные характеристики экипировки: атака, броня, слоты</p>
                </header>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            <form action="{{ route('admin.item.stat.add', $item->id) }}" method="post">
                                {{ csrf_field() }}
                                <div class="form-group mb-2">
                                    <label class="col-form-label">Тип стата</label>
                                    <select name="stat_type" class="form-control" data-plugin-selectTwo>
                                        @foreach($statTypes as $type)
                                            <option value="{{ $type->value }}">{{ $type->label() }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="form-group mb-2">
                                    <label class="col-form-label">Значение</label>
                                    <input type="number" class="form-control" name="value" value="0">
                                </div>
                                <div class="form-group mb-2">
                                    <label class="col-form-label">Тип значения</label>
                                    <select name="value_type" class="form-control" data-plugin-selectTwo>
                                        <option value="flat">Фиксированное</option>
                                        <option value="percent">Процент</option>
                                    </select>
                                </div>
                                <button class="btn btn-primary btn-sm">Добавить стат</button>
                            </form>
                        </div>
                        <div class="col-md-8">
                            <table class="table table-hover table-bordered mb-none">
                                <thead>
                                <tr>
                                    <th>Стат</th>
                                    <th width="100">Значение</th>
                                    <th width="100">Тип</th>
                                    <th width="70"></th>
                                </tr>
                                </thead>
                                <tbody>
                                @forelse($item->stats as $stat)
                                    <tr style="vertical-align: middle">
                                        <td>{{ $stat->stat_type->label() }}</td>
                                        <td>{{ $stat->value }}</td>
                                        <td>{{ $stat->value_type === \App\Modules\Share\Domain\Enums\ItemEffectValueType::PERCENT ? '%' : 'ед.' }}</td>
                                        <td>
                                            <a href="{{ route('admin.item.stat.delete', ['item' => $item->id, 'stat' => $stat->id]) }}"
                                               class="btn btn-xs btn-danger"
                                               onclick="return confirm('Удалить?')">Удалить</a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="4" class="text-center text-muted">Нет статов</td></tr>
                                @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </section>
        </div>
    </div>

    {{-- АКТИВНЫЕ ЭФФЕКТЫ --}}
    <div class="row">
        <div class="col-md-12">
            <section class="card">
                <header class="card-header">
                    <h2 class="card-title">Активные эффекты</h2>
                    <p class="card-subtitle text-muted">Баффы, лечение, урон — применяются при использовании предмета</p>
                </header>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            <form action="{{ route('admin.item.effect.add', $item->id) }}" method="post">
                                {{ csrf_field() }}
                                <div class="form-group mb-2">
                                    <label class="col-form-label">Тип эффекта</label>
                                    <select name="effect_type" class="form-control" data-plugin-selectTwo>
                                        @foreach($effectTypes as $type)
                                            <option value="{{ $type->value }}">{{ $type->label() }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="form-group mb-2">
                                    <label class="col-form-label">Значение</label>
                                    <input type="number" class="form-control" name="value" value="0">
                                </div>
                                <div class="form-group mb-2">
                                    <label class="col-form-label">Тип значения</label>
                                    <select name="value_type" class="form-control" data-plugin-selectTwo>
                                        <option value="flat">Фиксированное</option>
                                        <option value="percent">Процент</option>
                                    </select>
                                </div>
                                <div class="form-group mb-2">
                                    <label class="col-form-label">Длительность (сек.), для баффов</label>
                                    <input type="number" class="form-control" name="duration_seconds" placeholder="Пусто = мгновенно">
                                </div>
                                <button class="btn btn-primary btn-sm">Добавить эффект</button>
                            </form>
                        </div>
                        <div class="col-md-8">
                            <table class="table table-hover table-bordered mb-none">
                                <thead>
                                <tr>
                                    <th>Эффект</th>
                                    <th width="100">Значение</th>
                                    <th width="100">Тип</th>
                                    <th width="120">Длительность</th>
                                    <th width="70"></th>
                                </tr>
                                </thead>
                                <tbody>
                                @forelse($item->effects as $effect)
                                    <tr style="vertical-align: middle">
                                        <td>{{ $effect->effect_type->label() }}</td>
                                        <td>{{ $effect->value }}</td>
                                        <td>{{ $effect->value_type === \App\Modules\Share\Domain\Enums\ItemEffectValueType::PERCENT ? '%' : 'ед.' }}</td>
                                        <td>{{ $effect->duration_seconds ? $effect->duration_seconds . ' сек.' : '—' }}</td>
                                        <td>
                                            <a href="{{ route('admin.item.effect.delete', ['item' => $item->id, 'effect' => $effect->id]) }}"
                                               class="btn btn-xs btn-danger"
                                               onclick="return confirm('Удалить?')">Удалить</a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="5" class="text-center text-muted">Нет эффектов</td></tr>
                                @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </section>
        </div>
    </div>

    {{-- БАФФЫ ПРИ ИСПОЛЬЗОВАНИИ --}}
    <div class="row">
        <div class="col-md-12">
            <section class="card">
                <header class="card-header">
                    <h2 class="card-title">Баффы при использовании</h2>
                    <p class="card-subtitle text-muted">Все баффы из списка накладываются на игрока при использовании предмета.</p>
                </header>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            <form action="{{ route('admin.item.buff.add', $item->id) }}" method="post">
                                {{ csrf_field() }}
                                <div class="form-group mb-2">
                                    <label class="col-form-label">Бафф</label>
                                    <select name="effect_id" class="form-control" data-plugin-selectTwo required>
                                        @forelse($buffEffects as $buffEffect)
                                            <option value="{{ $buffEffect->id }}">{{ $buffEffect->name }}</option>
                                        @empty
                                            <option value="" disabled>Сначала создайте бафф в разделе «Эффекты»</option>
                                        @endforelse
                                    </select>
                                </div>
                                <div class="form-group mb-2">
                                    <label class="col-form-label">Длительность, сек.</label>
                                    <input type="number" class="form-control" name="duration_seconds" value="60" min="1" max="604800" required>
                                </div>
                                <button class="btn btn-primary btn-sm" @disabled($buffEffects->isEmpty())>Добавить бафф</button>
                            </form>
                        </div>
                        <div class="col-md-8">
                            <table class="table table-hover table-bordered mb-none">
                                <thead>
                                <tr>
                                    <th>Бафф</th>
                                    <th>Описание</th>
                                    <th width="130">Длительность</th>
                                    <th width="70"></th>
                                </tr>
                                </thead>
                                <tbody>
                                @forelse($item->buffs as $buff)
                                    <tr style="vertical-align: middle">
                                        <td>{{ $buff->effect->name }}</td>
                                        <td>{{ $buff->effect->description ?: '—' }}</td>
                                        <td>{{ $buff->duration_seconds }} сек.</td>
                                        <td>
                                            <a href="{{ route('admin.item.buff.delete', ['item' => $item->id, 'buff' => $buff->id]) }}"
                                               class="btn btn-xs btn-danger"
                                               onclick="return confirm('Удалить?')">Удалить</a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="4" class="text-center text-muted">Нет баффов</td></tr>
                                @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </section>
        </div>
    </div>

    {{-- ДЕБАФФЫ ПРИ ИСПОЛЬЗОВАНИИ --}}
    <div class="row">
        <div class="col-md-12">
            <section class="card">
                <header class="card-header">
                    <h2 class="card-title">Дебаффы на цель</h2>
                    <p class="card-subtitle text-muted">При использовании игрок выбирает другого активного игрока в своей локации.</p>
                </header>
                <div class="card-body"><div class="row">
                    <div class="col-md-4">
                        <form action="{{ route('admin.item.debuff.add', $item->id) }}" method="post">
                            {{ csrf_field() }}
                            <div class="form-group mb-2"><label class="col-form-label">Дебафф</label>
                                <select name="effect_id" class="form-control" data-plugin-selectTwo required>
                                    @forelse($debuffEffects as $debuffEffect)<option value="{{ $debuffEffect->id }}">{{ $debuffEffect->name }}</option>
                                    @empty<option value="" disabled>Сначала создайте дебафф в разделе «Эффекты»</option>@endforelse
                                </select>
                            </div>
                            <div class="form-group mb-2"><label class="col-form-label">Длительность, сек.</label><input type="number" class="form-control" name="duration_seconds" value="60" min="1" max="604800" required></div>
                            <button class="btn btn-danger btn-sm" @disabled($debuffEffects->isEmpty())>Добавить дебафф</button>
                        </form>
                    </div>
                    <div class="col-md-8"><table class="table table-hover table-bordered mb-none"><thead><tr><th>Дебафф</th><th>Описание</th><th width="130">Длительность</th><th width="70"></th></tr></thead><tbody>
                        @forelse($item->debuffs as $debuff)<tr style="vertical-align: middle"><td>{{ $debuff->effect->name }}</td><td>{{ $debuff->effect->description ?: '—' }}</td><td>{{ $debuff->duration_seconds }} сек.</td><td><a href="{{ route('admin.item.debuff.delete', ['item' => $item->id, 'debuff' => $debuff->id]) }}" class="btn btn-xs btn-danger" onclick="return confirm('Удалить?')">Удалить</a></td></tr>
                        @empty<tr><td colspan="4" class="text-center text-muted">Нет дебаффов</td></tr>@endforelse
                    </tbody></table></div>
                </div></div>
            </section>
        </div>
    </div>

    {{-- ТРЕБОВАНИЯ --}}
    <div class="row">
        <div class="col-md-12">
            <section class="card">
                <header class="card-header">
                    <h2 class="card-title">Требования для надевания / использования</h2>
                    <p class="card-subtitle text-muted">Минимальные характеристики персонажа для экипировки или использования предмета</p>
                </header>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            <form action="{{ route('admin.item.requirement.add', $item->id) }}" method="post" id="req-form">
                                {{ csrf_field() }}
                                <div class="form-group mb-2">
                                    <label class="col-form-label">Тип требования</label>
                                    <select name="type" class="form-control" id="req-type" onchange="updateReqFields()">
                                        @foreach($requirementTypes as $rType)
                                            <option value="{{ $rType->value }}">{{ $rType->label() }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="form-group mb-2" id="req-stat-field" style="display:none">
                                    <label class="col-form-label">Характеристика</label>
                                    <select name="stat_key" class="form-control">
                                        @foreach($playerStatKeys as $key)
                                            <option value="{{ $key->value }}">{{ $key->label() }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="form-group mb-2" id="req-skill-field" style="display:none">
                                    <label class="col-form-label">Навык</label>
                                    <select name="skill_id" class="form-control" data-plugin-selectTwo>
                                        @foreach($skills as $skill)
                                            <option value="{{ $skill->id }}">{{ $skill->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="form-group mb-2">
                                    <label class="col-form-label">Минимальное значение</label>
                                    <input type="number" class="form-control" name="min_value" value="1" min="1">
                                </div>
                                <button class="btn btn-primary btn-sm">Добавить требование</button>
                            </form>
                            <script>
                                function updateReqFields() {
                                    var type = document.getElementById('req-type').value;
                                    document.getElementById('req-stat-field').style.display  = type === 'stat'  ? '' : 'none';
                                    document.getElementById('req-skill-field').style.display = type === 'skill' ? '' : 'none';
                                }
                                updateReqFields();
                            </script>
                        </div>
                        <div class="col-md-8">
                            <table class="table table-hover table-bordered mb-none">
                                <thead>
                                <tr>
                                    <th>Тип</th>
                                    <th>Условие</th>
                                    <th width="100">Мин. значение</th>
                                    <th width="70"></th>
                                </tr>
                                </thead>
                                <tbody>
                                @forelse($item->requirements as $req)
                                    <tr style="vertical-align: middle">
                                        <td>{{ $req->type->label() }}</td>
                                        <td>{{ $req->label() }}</td>
                                        <td>{{ $req->min_value }}</td>
                                        <td>
                                            <a href="{{ route('admin.item.requirement.delete', ['item' => $item->id, 'requirement' => $req->id]) }}"
                                               class="btn btn-xs btn-danger"
                                               onclick="return confirm('Удалить?')">Удалить</a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="4" class="text-center text-muted">Нет требований</td></tr>
                                @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </section>
        </div>
    </div>

    {{-- МАТЕРИАЛЫ АПГРЕЙДА РЕДКОСТИ --}}
    <div class="row">
        <div class="col-md-12">
            <section class="card">
                <header class="card-header">
                    <h2 class="card-title">Материалы для апгрейда редкости</h2>
                    <p class="card-subtitle text-muted">Списываются вместе с монетами при превращении предмета в настроенный результат.</p>
                </header>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            <form action="{{ route('admin.item.rarity_upgrade.material.add', $item->id) }}" method="post">
                                @csrf
                                <div class="form-group mb-2">
                                    <label class="col-form-label">Материал</label>
                                    <select name="share_item_id" class="form-control" data-plugin-selectTwo required>
                                        @foreach($upgradeTargets as $target)
                                            <option value="{{ $target->id }}">[{{ $target->id }}] {{ $target->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="form-group mb-2">
                                    <label class="col-form-label">Количество</label>
                                    <input type="number" min="1" name="count" value="1" class="form-control" required>
                                </div>
                                <button class="btn btn-primary btn-sm">Добавить материал</button>
                            </form>
                        </div>
                        <div class="col-md-8">
                            <table class="table table-hover table-bordered mb-none">
                                <thead><tr><th width="45"></th><th>Материал</th><th width="110">Количество</th><th width="70"></th></tr></thead>
                                <tbody>
                                @forelse($item->rarityUpgradeMaterials as $material)
                                    <tr style="vertical-align: middle">
                                        <td><img src="{{ $material->image }}" width="36" alt=""></td>
                                        <td><a href="{{ route('admin.item.info', $material->id) }}">{{ $material->name }}</a></td>
                                        <td>{{ $material->pivot->count }}</td>
                                        <td>
                                            <form action="{{ route('admin.item.rarity_upgrade.material.delete', ['item' => $item->id, 'material' => $material->id]) }}" method="post">
                                                @csrf @method('DELETE')
                                                <button class="btn btn-xs btn-danger" onclick="return confirm('Удалить?')">Удалить</button>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="4" class="text-center text-muted">Материалы не требуются</td></tr>
                                @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </section>
        </div>
    </div>

    {{-- РЕЦЕПТ (только для типа RECIPE) --}}
    @if($item->type === \App\Modules\Share\Domain\Enums\ShareItemType::RECIPE && $item->recipe)
        <div class="row">
            <div class="col-md-12">
                <section class="card">
                    <header class="card-header"><h2 class="card-title">Рецепт</h2></header>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-4">
                                <form action="{{ route('admin.item.recipe.update', $item->recipe->id) }}" method="post">
                                    {{ csrf_field() }}
                                    <div class="form-group">
                                        <label class="col-form-label">Крафт предмета</label>
                                        <select id="sel-kraft-item" name="kraft_item_id" class="form-control">
                                            @if($item->recipe->kraft_item_id && $item->recipe->kraftItem)
                                                <option value="{{ $item->recipe->kraftItem->id }}" selected>
                                                    [{{ $item->recipe->kraftItem->id }}] {{ $item->recipe->kraftItem->name }}
                                                </option>
                                            @endif
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <label class="col-form-label">Процент успеха (%)</label>
                                        <input type="number" class="form-control" name="percent" value="{{ $item->recipe->percent }}" min="0" max="100">
                                    </div>
                                    <div class="form-group">
                                        <label class="col-form-label">Использование рецепта</label>
                                        <select name="unlock_type" class="form-control">
                                            @foreach(\App\Modules\Share\Domain\Enums\RecipeUnlockType::cases() as $unlockType)
                                                <option value="{{ $unlockType->value }}"
                                                        @selected(old('unlock_type', $item->recipe->unlock_type?->value ?? \App\Modules\Share\Domain\Enums\RecipeUnlockType::SINGLE_USE->value) === $unlockType->value)>
                                                    {{ $unlockType->label() }}
                                                </option>
                                            @endforeach
                                        </select>
                                        <small class="form-text text-muted">Изучаемый рецепт расходуется при изучении и затем навсегда доступен в мастерской. Одноразовый расходуется при крафте в кузнице.</small>
                                    </div>
                                    <button class="btn btn-primary btn-sm">Сохранить рецепт</button>
                                </form>
                            </div>
                            <div class="col-md-8">
                                <div class="mb-3">
                                    <a class="modal-with-zoom-anim ws-normal btn btn-sm btn-primary" href="#modalAddResource">Добавить ресурс</a>
                                </div>
                                <div class="table-responsive">
                                    <table class="table table-hover table-bordered mb-none">
                                        <thead>
                                        <tr>
                                            <th width="45"></th>
                                            <th>Название</th>
                                            <th width="100">Кол-во</th>
                                            <th width="70"></th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        @forelse($item->recipe->items as $needItem)
                                            <tr style="vertical-align: middle">
                                                <td>
                                                    @if($needItem->image)
                                                        <img src="{{ $needItem->image }}" width="36" alt="">
                                                    @endif
                                                </td>
                                                <td><a href="{{ route('admin.item.info', $needItem->id) }}">{{ $needItem->name }}</a></td>
                                                <td>{{ $needItem->pivot->count }}</td>
                                                <td>
                                                    <a href="{{ route('admin.item.recipe.delete_item', ['recipe' => $item->recipe->id, 'item' => $needItem->id]) }}"
                                                       class="btn btn-xs btn-danger"
                                                       onclick="return confirm('Удалить?')">Удалить</a>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr><td colspan="4" class="text-center text-muted">Нет ресурсов</td></tr>
                                        @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>
            </div>
        </div>

        {{-- Модалка: добавить ресурс --}}
        <div id="modalAddResource" class="modal-block zoom-anim-dialog modal-block-primary mfp-hide">
            <section class="card">
                <form action="{{ route('admin.item.recipe.add_item', $item->recipe->id) }}" method="post">
                    <header class="card-header"><h2 class="card-title">Добавить ресурс</h2></header>
                    <div class="card-body">
                        {{ csrf_field() }}
                        <div class="form-group mb-2">
                            <label>Предмет</label>
                            <select id="sel-recipe-item" name="share_item_id" class="form-control"></select>
                        </div>
                        <div class="form-group mb-2">
                            <label>Количество</label>
                            <input type="number" class="form-control" name="count" value="1" min="1">
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
    @endif

@include('admin.layout.summernote', ['selector' => 'textarea[name=description]'])

@push('footer_scripts')
<script>
    document.getElementById('image').addEventListener('change', function () {
        const file = this.files[0];
        if (file) {
            const preview = document.getElementById('item-image-preview');
            preview.src = URL.createObjectURL(file);
            preview.style.display = 'block';
        }
    });

    document.getElementById('transparent_image').addEventListener('change', function () {
        const file = this.files[0];
        if (file) {
            const preview = document.getElementById('item-transparent-image-preview');
            preview.src = URL.createObjectURL(file);
            preview.style.display = 'block';
        }
    });

    @if($item->type === \App\Modules\Share\Domain\Enums\ShareItemType::GEM)
    const gemStatOptions = `
        <option value="attack">Атака</option><option value="armor">Защита</option>
        <option value="hp_max">Макс HP</option><option value="mp_max">Макс MP</option>
        <option value="strength">Сила</option><option value="agility">Ловкость</option>
        <option value="intelligence">Интеллект</option><option value="critical">Крит шанс</option>
        <option value="dodge">Уворот</option>`;

    function makeGemRow(type, value) {
        const div = document.createElement('div');
        div.className = 'gem-stat-row d-flex gap-2 mb-2';
        div.innerHTML = `<select class="form-control gem-stat-type">${gemStatOptions}</select>
            <input type="number" class="form-control gem-stat-value" placeholder="Значение" style="max-width:120px" value="${value || ''}">
            <button type="button" class="btn btn-danger btn-sm gem-remove-row">✕</button>`;
        if (type) div.querySelector('.gem-stat-type').value = type;
        return div;
    }

    function loadGemFromJson() {
        const builder = document.getElementById('info-gem-builder');
        builder.innerHTML = '';
        let stats = [];
        try { stats = JSON.parse(document.getElementById('info_gem_stats_json').value || '[]'); } catch(e) {}
        if (stats.length === 0) stats = [{}];
        stats.forEach(s => builder.appendChild(makeGemRow(s.type, s.value)));
    }

    document.getElementById('info-gem-add-row').addEventListener('click', () =>
        document.getElementById('info-gem-builder').appendChild(makeGemRow('', '')));
    document.getElementById('info-gem-load').addEventListener('click', loadGemFromJson);
    document.getElementById('info-gem-builder').addEventListener('click', function (e) {
        if (e.target.classList.contains('gem-remove-row')) {
            const rows = document.querySelectorAll('#info-gem-builder .gem-stat-row');
            if (rows.length > 1) e.target.closest('.gem-stat-row').remove();
        }
    });
    document.getElementById('info-gem-apply').addEventListener('click', function () {
        const stats = [];
        document.querySelectorAll('#info-gem-builder .gem-stat-row').forEach(row => {
            const type  = row.querySelector('.gem-stat-type').value;
            const value = parseInt(row.querySelector('.gem-stat-value').value, 10);
            if (type && !isNaN(value) && value !== 0) stats.push({ type, value });
        });
        document.getElementById('info_gem_stats_json').value = JSON.stringify(stats, null, 2);
    });

    loadGemFromJson();
    @endif

    @if($item->type === \App\Modules\Share\Domain\Enums\ShareItemType::RECIPE && $item->recipe)
    $('#sel-kraft-item').select2({
        theme: 'bootstrap',
        placeholder: 'Выберите предмет',
        allowClear: true,
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

    $('#sel-recipe-item').select2({
        theme: 'bootstrap',
        dropdownParent: $('#modalAddResource'),
        placeholder: 'Выберите предмет',
        allowClear: true,
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
    @endif
</script>
@endpush

@endsection
