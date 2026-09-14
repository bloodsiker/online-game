@extends('admin.layout.base')

@section('title')
    {{ $reputation->name }}
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
                                <a class="nav-link" data-bs-target="#tab-tiers" href="#tab-tiers" data-bs-toggle="tab">
                                    Уровни <span class="badge badge-primary">{{ $reputation->tiers->count() }}</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" data-bs-target="#tab-shop" href="#tab-shop" data-bs-toggle="tab">
                                    Магазин <span class="badge badge-primary">{{ $reputation->shopItems->count() }}</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" data-bs-target="#tab-exchange" href="#tab-exchange" data-bs-toggle="tab">
                                    Обмен предметов <span class="badge badge-primary">{{ $reputation->exchangeItems->count() }}</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" data-bs-target="#tab-gamble" href="#tab-gamble" data-bs-toggle="tab">
                                    Обмен с риском <span class="badge badge-primary">{{ $reputation->gambleOptions->count() }}</span>
                                </a>
                            </li>
                        </ul>

                        <div class="tab-content">

                            {{-- ОСНОВНАЯ --}}
                            <div id="tab-main" class="tab-pane active">
                                <form action="{{ route('admin.reputation.info', $reputation->id) }}" method="post">
                                    {{ csrf_field() }}
                                    <div class="row pt-3 pb-3">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label class="col-form-label">Название</label>
                                                <input type="text" class="form-control" name="name" value="{{ $reputation->name }}">
                                            </div>
                                            <div class="form-group">
                                                <label class="col-form-label">Описание</label>
                                                <textarea class="form-control" name="description" rows="4">{{ $reputation->description }}</textarea>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label class="col-form-label">NPC</label>
                                                <select id="sel-npc" name="npc_id" class="form-control">
                                                    @if($reputation->npc)
                                                        <option value="{{ $reputation->npc->id }}" selected>[{{ $reputation->npc->id }}] {{ $reputation->npc->name }}</option>
                                                    @endif
                                                </select>
                                            </div>
                                            <div class="form-group">
                                                <label class="col-form-label">Иконка (URL)</label>
                                                <input type="text" class="form-control" name="icon" value="{{ $reputation->icon }}">
                                                @if($reputation->icon)
                                                    <div class="mt-1">
                                                        <img src="{{ str_starts_with($reputation->icon, 'http') || str_starts_with($reputation->icon, '/') ? $reputation->icon : asset($reputation->icon) }}" style="width:48px;height:48px;object-fit:contain;border:1px solid #ddd;border-radius:4px;" alt="">
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                    </div>

                                    <hr>
                                    <h5>Милость богов</h5>
                                    <p class="text-muted small">Если задано — репутация даёт игроку боевой эффект (см. <code>DivineFavorService</code>): пока активен маркер-эффект от эликсира (2ч) — временно, на % текущего тира; как только у тира есть медаль (обычная или подвиг) — % этого тира закрепляется навсегда, эликсир больше не нужен. Величины % задаются на вкладке «Уровни» у каждого тира.</p>
                                    <div class="row">
                                        <div class="col-md-3">
                                            <div class="form-group mb-2">
                                                <label>Тип эффекта</label>
                                                <select class="form-control" name="favor_effect_type">
                                                    <option value="">— нет —</option>
                                                    @foreach($favorTypes as $favorType)
                                                        <option value="{{ $favorType->value }}" @selected(old('favor_effect_type', $reputation->favor_effect_type?->value) === $favorType->value)>
                                                            {{ match($favorType->value) { 'heal' => 'Лечение', 'poison' => 'Яд (DoT по цели)', 'attack_buff' => 'Баф атаки', default => $favorType->value } }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="form-group mb-2">
                                                <label>Шанс срабатывания за удар, %</label>
                                                <input type="number" class="form-control" name="favor_proc_chance" value="{{ $reputation->favor_proc_chance }}" min="1" max="100">
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="form-group mb-2">
                                                <label>Эликсир (за подношение)</label>
                                                <select id="elixir-item-select" name="elixir_share_item_id" class="form-control">
                                                    @if($reputation->elixirItem)
                                                        <option value="{{ $reputation->elixirItem->id }}" selected>[{{ $reputation->elixirItem->id }}] {{ $reputation->elixirItem->name }}</option>
                                                    @endif
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="form-group mb-2">
                                                <label>Маркер-эффект (2ч окно от эликсира)</label>
                                                <select class="form-control" name="favor_marker_effect_id">
                                                    <option value="">— нет —</option>
                                                    @foreach($effects as $effect)
                                                        <option value="{{ $effect->id }}" @selected($reputation->favor_marker_effect_id === $effect->id)>[{{ $effect->id }}] {{ $effect->name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row mb-3">
                                        <div class="col-sm-12">
                                            <button class="btn btn-primary">Сохранить</button>
                                            <a href="{{ route('admin.reputations') }}" class="btn btn-success">Назад</a>
                                        </div>
                                    </div>
                                </form>
                            </div>

                            {{-- УРОВНИ --}}
                            <div id="tab-tiers" class="tab-pane">
                                <div class="pt-3">
                                    <div class="mb-3">
                                        <a class="modal-with-zoom-anim ws-normal btn btn-sm btn-primary" href="#modalTier">Добавить уровень</a>
                                    </div>

                                    @forelse($reputation->tiers as $tier)
                                        <div class="card mb-3">
                                            <div class="card-header d-flex align-items-center justify-content-between py-2">
                                                <div>
                                                    @if($tier->medal_icon)
                                                        <img src="{{ $tier->medalIconUrl() }}" style="width:24px;height:24px;object-fit:contain;margin-right:6px;vertical-align:middle;" alt="">
                                                    @endif
                                                    <strong>{{ $tier->medal_name ?: 'Без названия' }}</strong>
                                                    <span class="text-muted ms-2">{{ number_format($tier->min_points) }} – {{ $tier->max_points !== null ? number_format($tier->max_points) : '∞' }} очков</span>
                                                    @if($tier->favor_percent !== null)
                                                        <span class="badge badge-info ms-2" title="Милость богов на этом тире">🙏 {{ $tier->favor_percent }}% / {{ $tier->favor_duration_seconds ?? 30 }}сек{{ $tier->medal_name ? ' (навсегда)' : ' (только с эликсиром)' }}</span>
                                                    @endif
                                                    @if($tier->feat_favor_percent !== null)
                                                        <span class="badge badge-warning ms-2" title="Милость богов за выполненный подвиг, навсегда">🙏 {{ $tier->feat_favor_percent }}% (подвиг)</span>
                                                    @endif
                                                </div>
                                                <div>
                                                    <a class="modal-with-zoom-anim btn btn-xs btn-primary" href="#modalTierEdit{{ $tier->id }}">Изменить</a>
                                                    <a href="{{ route('admin.reputation.tier.delete', [$reputation->id, $tier->id]) }}"
                                                       class="btn btn-xs btn-danger"
                                                       onclick="return confirm('Удалить уровень со всеми квестами?')">Удалить</a>
                                                </div>
                                            </div>
                                            <div class="card-body py-2">
                                                @if($tier->feat_quest_id)
                                                    <p class="mb-2 small">
                                                        <span class="badge badge-warning">⚔ Подвиг</span>
                                                        <a href="{{ route('admin.quest.info', $tier->feat_quest_id) }}">[{{ $tier->feat_quest_id }}] {{ $tier->featQuest?->title ?? '—' }}</a>
                                                        @if($tier->feat_description)
                                                            <br><span class="text-muted">{{ $tier->feat_description }}</span>
                                                        @endif
                                                        @if($tier->feat_medal_name)
                                                            <br><span class="text-muted">Награда за подвиг: <strong>{{ $tier->feat_medal_name }}</strong>
                                                        </span>
                                                        @endif
                                                    </p>
                                                @endif
                                                <p class="mb-2 small text-muted">Квесты этого уровня:</p>
                                                <table class="table table-sm table-bordered mb-2">
                                                    <thead>
                                                    <tr>
                                                        <th width="50">ID</th>
                                                        <th>Квест</th>
                                                        <th width="70"></th>
                                                    </tr>
                                                    </thead>
                                                    <tbody>
                                                    @forelse($tier->quests as $tierQuest)
                                                        <tr>
                                                            <td>{{ $tierQuest->quest?->id }}</td>
                                                            <td>{{ $tierQuest->quest?->title ?? '—' }}</td>
                                                            <td>
                                                                <a href="{{ route('admin.reputation.tier.quest.delete', [$reputation->id, $tier->id, $tierQuest->id]) }}"
                                                                   class="btn btn-xs btn-danger"
                                                                   onclick="return confirm('Удалить?')">Удалить</a>
                                                            </td>
                                                        </tr>
                                                    @empty
                                                        <tr><td colspan="3" class="text-muted text-center">Нет квестов</td></tr>
                                                    @endforelse
                                                    </tbody>
                                                </table>
                                                <form action="{{ route('admin.reputation.tier.quest.add', [$reputation->id, $tier->id]) }}" method="post" class="d-flex gap-2">
                                                    {{ csrf_field() }}
                                                    <select class="form-control tier-quest-select" name="quest_id" style="max-width:400px;"></select>
                                                    <button class="btn btn-sm btn-outline-primary">Добавить квест</button>
                                                </form>
                                            </div>
                                        </div>

                                        {{-- Модалка: изменить уровень --}}
                                        <div id="modalTierEdit{{ $tier->id }}" class="modal-block zoom-anim-dialog modal-block-primary mfp-hide">
                                            <section class="card">
                                                <form action="{{ route('admin.reputation.tier.update', [$reputation->id, $tier->id]) }}" method="post" enctype="multipart/form-data">
                                                    <header class="card-header"><h2 class="card-title">Изменить уровень «{{ $tier->medal_name ?: $tier->min_points.'+' }}»</h2></header>
                                                    <div class="card-body">
                                                        {{ csrf_field() }}
                                                        <div class="row">
                                                            <div class="col-md-6">
                                                                <div class="form-group mb-2">
                                                                    <label>Мин. очков</label>
                                                                    <input type="number" class="form-control" name="min_points" value="{{ $tier->min_points }}">
                                                                </div>
                                                            </div>
                                                            <div class="col-md-6">
                                                                <div class="form-group mb-2">
                                                                    <label>Макс. очков <small class="text-muted">(пусто = без потолка)</small></label>
                                                                    <input type="number" class="form-control" name="max_points" value="{{ $tier->max_points }}">
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="form-group mb-2">
                                                            <label>Название медали</label>
                                                            <input type="text" class="form-control" name="medal_name" value="{{ $tier->medal_name }}">
                                                        </div>
                                                        <div class="form-group mb-2">
                                                            <label>Изображение медали (URL или путь от <code>public</code>)</label>
                                                            <input type="text" class="form-control" name="medal_icon" value="{{ $tier->medal_icon }}">
                                                            <input type="file" class="form-control mt-2" name="medal_image" accept="image/*">
                                                            <small class="form-text text-muted">Или загрузите файл до 4 МБ. Он заменит указанный путь.</small>
                                                            @if($tier->medal_icon)
                                                                <img src="{{ $tier->medalIconUrl() }}" width="35" height="35" style="object-fit:contain;margin-top:6px;" alt="">
                                                            @endif
                                                        </div>
                                                        <hr>
                                                        <div class="row">
                                                            <div class="col-md-6">
                                                                <div class="form-group mb-2">
                                                                    <label>🙏 Милость богов на этом тире, % <small class="text-muted">(работает, только если у репутации задан «Тип эффекта»; навсегда — если у тира есть медаль, иначе — только с активным эликсиром)</small></label>
                                                                    <input type="number" class="form-control" name="favor_percent" value="{{ $tier->favor_percent }}" min="1" max="100">
                                                                </div>
                                                            </div>
                                                            <div class="col-md-6">
                                                                <div class="form-group mb-2">
                                                                    <label>Длительность милости, сек <small class="text-muted">(суммарно, делится на тики; пусто = 30 по умолчанию)</small></label>
                                                                    <input type="number" class="form-control" name="favor_duration_seconds" value="{{ $tier->favor_duration_seconds }}" min="1">
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <hr>
                                                        <div class="form-group mb-2">
                                                            <label>⚔ Квест-подвиг <small class="text-muted">(без него медаль не выдаётся; для цепочки — финальный квест)</small></label>
                                                            <select class="form-control tier-feat-select" name="feat_quest_id" data-modal="#modalTierEdit{{ $tier->id }}">
                                                                @if($tier->featQuest)
                                                                    <option value="{{ $tier->featQuest->id }}" selected>[{{ $tier->featQuest->id }}] {{ $tier->featQuest->title }}</option>
                                                                @endif
                                                            </select>
                                                        </div>
                                                        <div class="form-group mb-2">
                                                            <label>Описание подвига <small class="text-muted">(видно игроку у заблокированной медали)</small></label>
                                                            <textarea class="form-control" name="feat_description" rows="2">{{ $tier->feat_description }}</textarea>
                                                        </div>
                                                        <div class="form-group mb-2">
                                                            <label>Отдельная медаль за подвиг <small class="text-muted">(пусто = подвиг блокирует обычную медаль)</small></label>
                                                            <input type="text" class="form-control" name="feat_medal_name" value="{{ $tier->feat_medal_name }}">
                                                        </div>
                                                        <div class="form-group mb-2">
                                                            <label>Изображение медали за подвиг (URL или путь от <code>public</code>)</label>
                                                            <input type="text" class="form-control" name="feat_medal_icon" value="{{ $tier->feat_medal_icon }}">
                                                            <input type="file" class="form-control mt-2" name="feat_medal_image" accept="image/*">
                                                            <small class="form-text text-muted">Или загрузите файл до 4 МБ. Он заменит указанный путь.</small>
                                                            @if($tier->feat_medal_icon)
                                                                <img src="{{ $tier->featMedalIconUrl() }}" width="35" height="35" style="object-fit:contain;margin-top:6px;" alt="">
                                                            @endif
                                                        </div>
                                                        <div class="form-group mb-2">
                                                            <label>🙏 Милость богов за подвиг, % <small class="text-muted">(навсегда, приоритет выше обычной медали этого тира)</small></label>
                                                            <input type="number" class="form-control" name="feat_favor_percent" value="{{ $tier->feat_favor_percent }}" min="1" max="100">
                                                        </div>
                                                    </div>
                                                    <footer class="card-footer">
                                                        <div class="col-md-12 text-end">
                                                            <button class="btn btn-primary">Сохранить</button>
                                                            <button type="button" class="btn btn-default modal-dismiss">Отмена</button>
                                                        </div>
                                                    </footer>
                                                </form>
                                            </section>
                                        </div>
                                    @empty
                                        <p class="text-muted">Уровни не добавлены.</p>
                                    @endforelse
                                </div>
                            </div>

                            {{-- МАГАЗИН --}}
                            <div id="tab-shop" class="tab-pane">
                                <div class="pt-3">
                                    <div class="mb-3">
                                        <a class="modal-with-zoom-anim ws-normal btn btn-sm btn-primary" href="#modalShop">Добавить предмет</a>
                                    </div>
                                    <div class="table-responsive">
                                        <table class="table table-hover table-bordered mb-none">
                                            <thead>
                                            <tr>
                                                <th width="50">ID</th>
                                                <th width="45"></th>
                                                <th>Предмет</th>
                                                <th width="100">Цена (монет)</th>
                                                <th width="100">Цена (алмазы)</th>
                                                <th width="110">Мин. очков</th>
                                                <th width="80">Сортировка</th>
                                                <th width="70"></th>
                                            </tr>
                                            </thead>
                                            <tbody>
                                            @forelse($reputation->shopItems as $shopItem)
                                                <tr style="vertical-align: middle">
                                                    <td>{{ $shopItem->id }}</td>
                                                    <td>
                                                        @if($shopItem->item?->image)
                                                            <img src="{{ $shopItem->item->image }}" width="36" alt="">
                                                        @endif
                                                    </td>
                                                    <td>{{ $shopItem->item?->name ?? '—' }}</td>
                                                    <td>{{ number_format($shopItem->price, 0, '', ' ') }}</td>
                                                    <td>{{ number_format($shopItem->diamond, 0, '', ' ') }}</td>
                                                    <td>{{ number_format($shopItem->min_points, 0, '', ' ') }}</td>
                                                    <td>{{ $shopItem->sort_order }}</td>
                                                    <td>
                                                        <a href="{{ route('admin.reputation.shop.delete', [$reputation->id, $shopItem->id]) }}"
                                                           class="btn btn-xs btn-danger"
                                                           onclick="return confirm('Удалить?')">Удалить</a>
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr><td colspan="8" class="text-center text-muted">Нет предметов</td></tr>
                                            @endforelse
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>

                            {{-- ОБМЕН ПРЕДМЕТОВ (линейный) --}}
                            <div id="tab-exchange" class="tab-pane">
                                <div class="pt-3">
                                    @if($exchangeStructure)
                                        <p class="text-muted small">Здание обмена: <a href="{{ route('admin.structure.info', $exchangeStructure->id) }}">[{{ $exchangeStructure->id }}] {{ $exchangeStructure->name }}</a>. Предмет → фиксированное количество очков репутации, в пределах указанного диапазона очков.</p>
                                        <div class="mb-3">
                                            <a class="modal-with-zoom-anim ws-normal btn btn-sm btn-primary" href="#modalExchange">Добавить предмет</a>
                                        </div>
                                    @else
                                        <p class="text-warning small">У NPC этой репутации ещё нет здания «Обмен на репутацию» — создайте Structure (type=reputation_exchange) на этого NPC, прежде чем добавлять предметы обмена.</p>
                                    @endif
                                    <div class="table-responsive">
                                        <table class="table table-hover table-bordered mb-none">
                                            <thead>
                                            <tr>
                                                <th width="50">ID</th>
                                                <th width="45"></th>
                                                <th>Предмет</th>
                                                <th width="90">Очков за 1 шт.</th>
                                                <th width="110">Мин. очков</th>
                                                <th width="110">Макс. очков</th>
                                                <th width="80">Сортировка</th>
                                                <th width="70"></th>
                                            </tr>
                                            </thead>
                                            <tbody>
                                            @forelse($reputation->exchangeItems as $exchangeItem)
                                                <tr style="vertical-align: middle">
                                                    <td>{{ $exchangeItem->id }}</td>
                                                    <td>
                                                        @if($exchangeItem->shareItem?->image)
                                                            <img src="{{ $exchangeItem->shareItem->image }}" width="36" alt="">
                                                        @endif
                                                    </td>
                                                    <td>{{ $exchangeItem->shareItem?->name ?? '—' }}</td>
                                                    <td>{{ $exchangeItem->points }}</td>
                                                    <td>{{ number_format($exchangeItem->min_reputation, 0, '', ' ') }}</td>
                                                    <td>{{ number_format($exchangeItem->max_reputation, 0, '', ' ') }}</td>
                                                    <td>{{ $exchangeItem->sort_order }}</td>
                                                    <td>
                                                        <a href="{{ route('admin.reputation.exchange.delete', [$reputation->id, $exchangeItem->id]) }}"
                                                           class="btn btn-xs btn-danger"
                                                           onclick="return confirm('Удалить?')">Удалить</a>
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr><td colspan="8" class="text-center text-muted">Предметов обмена нет</td></tr>
                                            @endforelse
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>

                            {{-- ОБМЕН С РИСКОМ --}}
                            <div id="tab-gamble" class="tab-pane">
                                <div class="pt-3">
                                    <p class="text-muted small">Механика «выбери ресурс → выбери риск»: игрок выбирает один из вариантов ниже для конкретного ресурса — стоимость, шанс успеха и награду в очках репутации. Ресурс тратится в любом случае, очки начисляются только при успехе. Используется структурой типа «Обмен на репутацию» с <code>exchange_type = gamble</code>.</p>
                                    <div class="mb-3">
                                        <a class="modal-with-zoom-anim ws-normal btn btn-sm btn-primary" href="#modalGamble">Добавить вариант</a>
                                    </div>
                                    <div class="table-responsive">
                                        <table class="table table-hover table-bordered mb-none">
                                            <thead>
                                            <tr>
                                                <th width="50">ID</th>
                                                <th width="45"></th>
                                                <th>Ресурс</th>
                                                <th width="100">Стоимость</th>
                                                <th width="100">Шанс успеха</th>
                                                <th width="110">Награда, очков</th>
                                                <th width="80">Сортировка</th>
                                                <th width="70"></th>
                                            </tr>
                                            </thead>
                                            <tbody>
                                            @forelse($reputation->gambleOptions as $option)
                                                <tr style="vertical-align: middle">
                                                    <td>{{ $option->id }}</td>
                                                    <td>
                                                        @if($option->shareItem?->image)
                                                            <img src="{{ $option->shareItem->image }}" width="36" alt="">
                                                        @endif
                                                    </td>
                                                    <td>{{ $option->shareItem?->name ?? '—' }}</td>
                                                    <td>{{ number_format($option->resource_cost, 0, '', ' ') }}</td>
                                                    <td>{{ $option->success_chance }}%</td>
                                                    <td>{{ number_format($option->reward_points, 0, '', ' ') }}</td>
                                                    <td>{{ $option->sort_order }}</td>
                                                    <td>
                                                        <a href="{{ route('admin.reputation.gamble.delete', [$reputation->id, $option->id]) }}"
                                                           class="btn btn-xs btn-danger"
                                                           onclick="return confirm('Удалить?')">Удалить</a>
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr><td colspan="8" class="text-center text-muted">Вариантов обмена нет</td></tr>
                                            @endforelse
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
            </section>
        </div>
    </div>

    {{-- Модалка: добавить уровень --}}
    <div id="modalTier" class="modal-block zoom-anim-dialog modal-block-primary mfp-hide">
        <section class="card">
            <form action="{{ route('admin.reputation.tier.add', $reputation->id) }}" method="post" enctype="multipart/form-data">
                <header class="card-header"><h2 class="card-title">Добавить уровень</h2></header>
                <div class="card-body">
                    {{ csrf_field() }}
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group mb-2">
                                <label>Мин. очков</label>
                                <input type="number" class="form-control" name="min_points" value="0">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group mb-2">
                                <label>Макс. очков</label>
                                <input type="number" class="form-control" name="max_points" value="0">
                            </div>
                        </div>
                    </div>
                    <div class="form-group mb-2">
                        <label>Название медали</label>
                        <input type="text" class="form-control" name="medal_name">
                    </div>
                    <div class="form-group mb-2">
                        <label>Изображение медали (URL или путь от <code>public</code>)</label>
                        <input type="text" class="form-control" name="medal_icon">
                        <input type="file" class="form-control mt-2" name="medal_image" accept="image/*">
                        <small class="form-text text-muted">Или загрузите файл до 4 МБ. Он заменит указанный путь.</small>
                    </div>
                    <hr>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group mb-2">
                                <label>🙏 Милость богов на этом тире, % <small class="text-muted">(навсегда, если у тира есть медаль, иначе — только с активным эликсиром)</small></label>
                                <input type="number" class="form-control" name="favor_percent" min="1" max="100">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group mb-2">
                                <label>Длительность милости, сек <small class="text-muted">(пусто = 30 по умолчанию)</small></label>
                                <input type="number" class="form-control" name="favor_duration_seconds" min="1">
                            </div>
                        </div>
                    </div>
                    <hr>
                    <div class="form-group mb-2">
                        <label>⚔ Квест-подвиг <small class="text-muted">(без него медаль не выдаётся; для цепочки — финальный квест)</small></label>
                        <select class="form-control tier-feat-select" name="feat_quest_id" data-modal="#modalTier"></select>
                    </div>
                    <div class="form-group mb-2">
                        <label>Описание подвига <small class="text-muted">(видно игроку у заблокированной медали)</small></label>
                        <textarea class="form-control" name="feat_description" rows="2"></textarea>
                    </div>
                    <div class="form-group mb-2">
                        <label>Отдельная медаль за подвиг <small class="text-muted">(пусто = подвиг блокирует обычную медаль)</small></label>
                        <input type="text" class="form-control" name="feat_medal_name">
                    </div>
                    <div class="form-group mb-2">
                        <label>Изображение медали за подвиг (URL или путь от <code>public</code>)</label>
                        <input type="text" class="form-control" name="feat_medal_icon">
                        <input type="file" class="form-control mt-2" name="feat_medal_image" accept="image/*">
                        <small class="form-text text-muted">Или загрузите файл до 4 МБ. Он заменит указанный путь.</small>
                    </div>
                    <div class="form-group mb-2">
                        <label>🙏 Милость богов за подвиг, % <small class="text-muted">(навсегда, приоритет выше обычной медали)</small></label>
                        <input type="number" class="form-control" name="feat_favor_percent" min="1" max="100">
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

    {{-- Модалка: добавить предмет в магазин --}}
    <div id="modalShop" class="modal-block zoom-anim-dialog modal-block-primary mfp-hide">
        <section class="card">
            <form action="{{ route('admin.reputation.shop.add', $reputation->id) }}" method="post">
                <header class="card-header"><h2 class="card-title">Добавить предмет в магазин</h2></header>
                <div class="card-body">
                    {{ csrf_field() }}
                    <div class="form-group mb-2">
                        <label>Предмет</label>
                        <select id="shop-item-select" name="share_item_id" class="form-control"></select>
                    </div>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group mb-2">
                                <label>Цена (монеты)</label>
                                <input type="number" class="form-control" name="price" value="0">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group mb-2">
                                <label>Цена (алмазы)</label>
                                <input type="number" class="form-control" name="diamond" value="0">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group mb-2">
                                <label>Мин. очков репутации</label>
                                <input type="number" class="form-control" name="min_points" value="0">
                            </div>
                        </div>
                    </div>
                    <div class="form-group mb-2">
                        <label>Сортировка</label>
                        <input type="number" class="form-control" name="sort_order" value="0">
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

    {{-- Модалка: добавить предмет линейного обмена --}}
    <div id="modalExchange" class="modal-block zoom-anim-dialog modal-block-primary mfp-hide">
        <section class="card">
            <form action="{{ route('admin.reputation.exchange.add', $reputation->id) }}" method="post">
                <header class="card-header"><h2 class="card-title">Добавить предмет обмена</h2></header>
                <div class="card-body">
                    {{ csrf_field() }}
                    <div class="form-group mb-2">
                        <label>Предмет</label>
                        <select id="exchange-item-select" name="share_item_id" class="form-control"></select>
                    </div>
                    <div class="row">
                        <div class="col-md-3">
                            <div class="form-group mb-2">
                                <label>Очков за 1 шт.</label>
                                <input type="number" class="form-control" name="points" value="5" min="1">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group mb-2">
                                <label>Мин. очков репутации</label>
                                <input type="number" class="form-control" name="min_reputation" value="0" min="0">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group mb-2">
                                <label>Макс. очков репутации</label>
                                <input type="number" class="form-control" name="max_reputation" value="999999" min="1">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group mb-2">
                                <label>Сортировка</label>
                                <input type="number" class="form-control" name="sort_order" value="0">
                            </div>
                        </div>
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

    {{-- Модалка: добавить вариант обмена с риском --}}
    <div id="modalGamble" class="modal-block zoom-anim-dialog modal-block-primary mfp-hide">
        <section class="card">
            <form action="{{ route('admin.reputation.gamble.add', $reputation->id) }}" method="post">
                <header class="card-header"><h2 class="card-title">Добавить вариант обмена</h2></header>
                <div class="card-body">
                    {{ csrf_field() }}
                    <div class="form-group mb-2">
                        <label>Ресурс</label>
                        <select id="gamble-item-select" name="share_item_id" class="form-control"></select>
                    </div>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group mb-2">
                                <label>Стоимость (кол-во ресурса)</label>
                                <input type="number" class="form-control" name="resource_cost" value="0" min="1">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group mb-2">
                                <label>Шанс успеха, %</label>
                                <input type="number" class="form-control" name="success_chance" value="100" min="1" max="100">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group mb-2">
                                <label>Награда, очков репутации</label>
                                <input type="number" class="form-control" name="reward_points" value="0" min="0">
                            </div>
                        </div>
                    </div>
                    <div class="form-group mb-2">
                        <label>Сортировка</label>
                        <input type="number" class="form-control" name="sort_order" value="0">
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
    function formatItemOption(item) {
        if (!item.id) return item.text;
        var img = item.image
            ? '<img src="' + item.image + '" style="width:22px;height:22px;object-fit:contain;margin-right:6px;vertical-align:middle;">'
            : '';
        return $('<span>' + img + item.text + '</span>');
    }

    // NPC select
    $('#sel-npc').select2({
        theme: 'bootstrap',
        placeholder: 'Выберите NPC',
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

    // Shop item select
    $('#shop-item-select').select2({
        theme: 'bootstrap',
        dropdownParent: $('#modalShop'),
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

    // Quest selects per tier
    $('.tier-quest-select').each(function () {
        $(this).select2({
            theme: 'bootstrap',
            placeholder: 'Выберите квест',
            allowClear: true,
            ajax: {
                url: '{{ route('admin.api.quests') }}',
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
    });

    // Divine favor elixir item select
    $('#elixir-item-select').select2({
        theme: 'bootstrap',
        placeholder: 'Выберите эликсир',
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

    // Linear exchange item select
    $('#exchange-item-select').select2({
        theme: 'bootstrap',
        dropdownParent: $('#modalExchange'),
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

    // Gamble option item select
    $('#gamble-item-select').select2({
        theme: 'bootstrap',
        dropdownParent: $('#modalGamble'),
        placeholder: 'Выберите ресурс',
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

    // Feat quest selects (in add/edit tier modals)
    $('.tier-feat-select').each(function () {
        $(this).select2({
            theme: 'bootstrap',
            dropdownParent: $($(this).data('modal')),
            placeholder: 'Без подвига',
            allowClear: true,
            ajax: {
                url: '{{ route('admin.api.quests') }}',
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
    });
</script>
@endpush

@include('admin.layout.summernote', [
    'selector' => 'textarea[name=description]',
    'height' => 220,
    'placeholder' => 'Описание репутации, которое увидят игроки',
])

@endsection
