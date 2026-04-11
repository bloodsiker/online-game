@extends('admin.layout.base')

@section('title') Документация — Данжи @endsection

@section('body')

<style>
    .doc-section { margin-bottom: 36px; }
    .doc-section h3 { font-size: 15px; font-weight: 700; border-bottom: 2px solid #0088cc; padding-bottom: 6px; margin-bottom: 14px; color: #1a2a3a; }
    .doc-section h4 { font-size: 13px; font-weight: 700; color: #444; margin: 14px 0 6px; }
    .doc-section p, .doc-section li { font-size: 12px; color: #555; line-height: 1.7; }
    .doc-section ul { padding-left: 18px; margin-bottom: 8px; }
    .field-table th { font-size: 11px; background: #f0f4f8; }
    .field-table td { font-size: 12px; vertical-align: top; padding: 6px 10px !important; }
    .field-table td:first-child { font-family: monospace; font-weight: 600; color: #c0392b; white-space: nowrap; }
    .field-table td:nth-child(2) { color: #888; font-size: 11px; white-space: nowrap; }
    .field-table td:nth-child(3) { }
    .field-table td:last-child { color: #27ae60; font-size: 11px; }
    .badge-enum { display: inline-block; background: #e8f0fe; color: #1967d2; border-radius: 3px; padding: 1px 6px; font-size: 11px; font-family: monospace; margin: 1px; }
    .badge-note { display: inline-block; background: #fff3cd; color: #856404; border-radius: 3px; padding: 1px 6px; font-size: 11px; }
    .flow-box { background: #f8f9fa; border: 1px solid #dee2e6; border-radius: 6px; padding: 14px 18px; font-size: 12px; margin-bottom: 12px; }
    .flow-box code { background: #e9ecef; padding: 1px 5px; border-radius: 3px; font-size: 11px; }
    .arrow { color: #0088cc; font-weight: bold; margin: 0 4px; }
    .tbl-name { font-family: monospace; font-size: 13px; font-weight: 700; color: #0d47a1; background: #e8f0fe; padding: 2px 8px; border-radius: 4px; }
    .model-name { font-family: monospace; font-size: 12px; color: #6a1b9a; background: #f3e5f5; padding: 1px 6px; border-radius: 3px; }
    .section-intro { background: #f0f7ff; border-left: 4px solid #0088cc; padding: 10px 14px; margin-bottom: 16px; font-size: 12px; color: #333; border-radius: 0 4px 4px 0; }
</style>

<div class="row">
    <div class="col-lg-12">
        <section class="card">
            <header class="card-header">
                <div class="card-actions">
                    <i class="bx bx-dungeon" style="font-size:20px;color:#0088cc"></i>
                </div>
                <h2 class="card-title">Система данжей — документация</h2>
                <p class="card-subtitle">Описание таблиц, моделей, полей и допустимых значений</p>
            </header>
            <div class="card-body">

                {{-- ══════════════════════════════════════════════════════════════ --}}
                {{-- СХЕМА ПОТОКА --}}
                {{-- ══════════════════════════════════════════════════════════════ --}}
                <div class="doc-section">
                    <h3><i class="bx bx-git-branch"></i> Общая схема работы</h3>

                    <div class="flow-box">
                        <strong>Жизненный цикл данжа:</strong><br><br>
                        <code>dungeons</code> (справочник)
                        <span class="arrow">→</span>
                        игрок входит, тратит ключ из <code>backpacks</code>
                        <span class="arrow">→</span>
                        создаётся <code>dungeon_runs</code> (инстанс)
                        <span class="arrow">→</span>
                        <code>dungeon_run_participants</code> (кто участвует)
                        <span class="arrow">→</span>
                        открывается первый <code>dungeon_run_floors</code>
                        <span class="arrow">→</span>
                        спавнятся монстры в <code>monster_on_locations</code> (с <code>dungeon_run_floor_id</code>)
                        <span class="arrow">→</span>
                        создаётся <code>battles</code>
                        <span class="arrow">→</span>
                        бой завершён → переход на след. этаж
                        <span class="arrow">→</span>
                        все этажи пройдены → <code>dungeon_rewards</code> раздаются, ран помечается <code>completed</code>
                    </div>

                    <div class="flow-box">
                        <strong>Кулдаун:</strong>
                        <code>dungeon_cooldowns</code> — после завершения рана. Если <code>user_id = 0</code> → глобальный кулдаун для всех.<br><br>
                        <strong>Pity (гарантия дропа):</strong>
                        <code>dungeon_pity</code> — считает провалы. При провале <code>fail_count++</code>. При успехе — сбрасывается.
                        Если <code>fail_count >= pity_threshold</code> в <code>dungeon_rewards</code> → награда выдаётся гарантированно.<br><br>
                        <strong>Группы:</strong>
                        <code>parties</code> + <code>party_members</code> — до входа в данж. При входе статус группы → <code>in_dungeon</code>.
                    </div>
                </div>

                {{-- ══════════════════════════════════════════════════════════════ --}}
                {{-- dungeons --}}
                {{-- ══════════════════════════════════════════════════════════════ --}}
                <div class="doc-section">
                    <h3>
                        <span class="tbl-name">dungeons</span>
                        <small class="text-muted ms-2">→</small>
                        <span class="model-name">App\Models\Dungeon\Dungeon</span>
                    </h3>
                    <div class="section-intro">
                        Справочник данжей. Каждая запись — отдельный тип данжа (не инстанс!).
                        Здесь настраиваются все параметры: тип, кулдаун, ограничения на вход, таймер.
                    </div>

                    <table class="table table-bordered table-hover field-table">
                        <thead>
                            <tr>
                                <th>Поле</th>
                                <th>Тип</th>
                                <th>Описание</th>
                                <th>Допустимые значения</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>name</td>
                                <td>varchar(150)</td>
                                <td>Название данжа, показывается игрокам</td>
                                <td>Любая строка</td>
                            </tr>
                            <tr>
                                <td>description</td>
                                <td>text / null</td>
                                <td>Описание для игроков на странице данжа</td>
                                <td>Любая строка или NULL</td>
                            </tr>
                            <tr>
                                <td>tier</td>
                                <td>tinyint</td>
                                <td>Сложность / уровень данжа. Используется для сортировки и масштабирования наград</td>
                                <td><code>1</code> (новичок) … <code>10</code> (эндгейм)</td>
                            </tr>
                            <tr>
                                <td>type</td>
                                <td>enum</td>
                                <td>
                                    Тип данжа — определяет стратегию прохождения:<br>
                                    <span class="badge-enum">linear</span> обычный, этаж за этажем, провал = конец<br>
                                    <span class="badge-enum">survival</span> волны, провал = завершить с тем что есть<br>
                                    <span class="badge-enum">boss_rush</span> только боссы подряд, провал = конец
                                </td>
                                <td>
                                    <span class="badge-enum">linear</span>
                                    <span class="badge-enum">survival</span>
                                    <span class="badge-enum">boss_rush</span>
                                </td>
                            </tr>
                            <tr>
                                <td>entry_share_item_id</td>
                                <td>bigint / null</td>
                                <td>
                                    FK → <code>share_items.id</code>. Предмет-ключ, который тратится при входе.
                                    <span class="badge-note">NULL = вход без ключа</span>
                                </td>
                                <td>ID из таблицы <code>share_items</code> или NULL</td>
                            </tr>
                            <tr>
                                <td>max_players</td>
                                <td>tinyint</td>
                                <td>
                                    Максимальное число участников. <code>1</code> = соло-данж.
                                    Групповой данж требует наличия группы при входе.
                                </td>
                                <td><code>1</code> (соло), <code>2–10</code> (группа)</td>
                            </tr>
                            <tr>
                                <td>cooldown_type</td>
                                <td>enum</td>
                                <td>
                                    Кому начисляется кулдаун после завершения рана:<br>
                                    <span class="badge-enum">personal</span> — каждому участнику отдельно<br>
                                    <span class="badge-enum">global</span> — всем сразу (sentinel <code>user_id=0</code> в <code>dungeon_cooldowns</code>)
                                </td>
                                <td>
                                    <span class="badge-enum">personal</span>
                                    <span class="badge-enum">global</span>
                                </td>
                            </tr>
                            <tr>
                                <td>cooldown_seconds</td>
                                <td>int unsigned</td>
                                <td>Длительность кулдауна в секундах</td>
                                <td>
                                    <code>3600</code> = 1ч, <code>86400</code> = 24ч, <code>604800</code> = 7д.<br>
                                    <code>0</code> = нет кулдауна
                                </td>
                            </tr>
                            <tr>
                                <td>time_limit_seconds</td>
                                <td>int / null</td>
                                <td>
                                    Таймер на весь инстанс. По истечении — ран считается проваленным.
                                    <span class="badge-note">NULL = без таймера</span>
                                </td>
                                <td><code>300</code>=5мин, <code>600</code>=10мин … или NULL</td>
                            </tr>
                            <tr>
                                <td>min_level</td>
                                <td>tinyint</td>
                                <td>Минимальный уровень персонажа для входа</td>
                                <td><code>1</code>–<code>100</code></td>
                            </tr>
                            <tr>
                                <td>is_active</td>
                                <td>boolean</td>
                                <td>Виден ли данж игрокам и доступен ли для входа</td>
                                <td><code>true</code> / <code>false</code></td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                {{-- ══════════════════════════════════════════════════════════════ --}}
                {{-- dungeon_floors --}}
                {{-- ══════════════════════════════════════════════════════════════ --}}
                <div class="doc-section">
                    <h3>
                        <span class="tbl-name">dungeon_floors</span>
                        <small class="text-muted ms-2">→</small>
                        <span class="model-name">App\Models\Dungeon\DungeonFloor</span>
                    </h3>
                    <div class="section-intro">
                        Этажи/комнаты данжа (справочник). Каждый данж имеет минимум 1 этаж.
                        Порядок прохождения задаётся через <code>dungeon_floor_branches</code>.
                    </div>

                    <table class="table table-bordered table-hover field-table">
                        <thead><tr><th>Поле</th><th>Тип</th><th>Описание</th><th>Допустимые значения</th></tr></thead>
                        <tbody>
                            <tr>
                                <td>dungeon_id</td>
                                <td>bigint</td>
                                <td>FK → <code>dungeons.id</code></td>
                                <td>ID данжа</td>
                            </tr>
                            <tr>
                                <td>floor_number</td>
                                <td>tinyint</td>
                                <td>Порядковый номер этажа (только для отображения)</td>
                                <td><code>1</code>, <code>2</code>, <code>3</code> …</td>
                            </tr>
                            <tr>
                                <td>label</td>
                                <td>varchar / null</td>
                                <td>Название этажа, показывается игроку при входе. NULL = просто «Этаж N»</td>
                                <td>Строка или NULL</td>
                            </tr>
                            <tr>
                                <td>is_first</td>
                                <td>boolean</td>
                                <td>
                                    Является ли этаж стартовым. При создании рана система ищет этаж с <code>is_first=true</code>.
                                    <span class="badge-note">Только 1 этаж в данже может быть первым</span>
                                </td>
                                <td><code>true</code> / <code>false</code></td>
                            </tr>
                            <tr>
                                <td>is_boss_floor</td>
                                <td>boolean</td>
                                <td>Признак босс-этажа. Влияет только на отображение (иконка, цвет). Монстров всё равно берёт из пула.</td>
                                <td><code>true</code> / <code>false</code></td>
                            </tr>
                            <tr>
                                <td>time_limit_seconds</td>
                                <td>int / null</td>
                                <td>
                                    Таймер на этот конкретный этаж. Отдельный от таймера всего инстанса.
                                    <span class="badge-note">NULL = без таймера на этаже</span>
                                </td>
                                <td><code>60</code>=1мин … <code>600</code>=10мин или NULL</td>
                            </tr>
                            <tr>
                                <td>min_monsters</td>
                                <td>tinyint</td>
                                <td>Минимальное кол-во монстров, которые спавнятся на этаже</td>
                                <td><code>1</code>–<code>max_monsters</code></td>
                            </tr>
                            <tr>
                                <td>max_monsters</td>
                                <td>tinyint</td>
                                <td>Максимальное кол-во монстров. Реальное число — случайное в диапазоне [min, max]</td>
                                <td><code>1</code>–<code>20</code></td>
                            </tr>
                            <tr>
                                <td>wave_count</td>
                                <td>tinyint</td>
                                <td>
                                    Кол-во волн монстров на этаже. После победы над волной спавнится следующая.
                                    <span class="badge-note">Сейчас используется только в survival</span>
                                </td>
                                <td><code>1</code>–<code>10</code></td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                {{-- ══════════════════════════════════════════════════════════════ --}}
                {{-- dungeon_floor_branches --}}
                {{-- ══════════════════════════════════════════════════════════════ --}}
                <div class="doc-section">
                    <h3>
                        <span class="tbl-name">dungeon_floor_branches</span>
                        <small class="text-muted ms-2">→</small>
                        <span class="model-name">App\Models\Dungeon\DungeonFloorBranch</span>
                    </h3>
                    <div class="section-intro">
                        Связи между этажами — граф переходов. Для линейного данжа: каждый этаж имеет ровно 1 ветку.
                        Для ветвящегося: несколько веток → игрок выбирает куда идти.
                        Последний этаж не имеет веток — это признак конца данжа.
                    </div>

                    <table class="table table-bordered table-hover field-table">
                        <thead><tr><th>Поле</th><th>Тип</th><th>Описание</th><th>Допустимые значения</th></tr></thead>
                        <tbody>
                            <tr>
                                <td>from_floor_id</td>
                                <td>bigint</td>
                                <td>FK → <code>dungeon_floors.id</code>. С какого этажа идёт переход</td>
                                <td>ID этажа</td>
                            </tr>
                            <tr>
                                <td>to_floor_id</td>
                                <td>bigint</td>
                                <td>FK → <code>dungeon_floors.id</code>. На какой этаж ведёт переход</td>
                                <td>ID этажа</td>
                            </tr>
                            <tr>
                                <td>label</td>
                                <td>varchar / null</td>
                                <td>Название кнопки выбора (при ветвлении). NULL = просто показывает номер этажа</td>
                                <td>Строка или NULL. Пример: «Тёмный путь», «Лёгкий путь»</td>
                            </tr>
                        </tbody>
                    </table>

                    <h4>Примеры структур:</h4>
                    <div class="flow-box">
                        <strong>Линейный (3 этажа):</strong><br>
                        <code>floor_1</code> → <code>floor_2</code> → <code>floor_3</code> → (нет веток = финал)
                    </div>
                    <div class="flow-box">
                        <strong>Ветвящийся (выбор на 2м этаже):</strong><br>
                        <code>floor_1</code> → <code>floor_2a</code> «Лёгкий» → <code>floor_3</code><br>
                        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;→ <code>floor_2b</code> «Сложный» → <code>floor_3</code>
                    </div>
                </div>

                {{-- ══════════════════════════════════════════════════════════════ --}}
                {{-- dungeon_floor_monster_pool --}}
                {{-- ══════════════════════════════════════════════════════════════ --}}
                <div class="doc-section">
                    <h3>
                        <span class="tbl-name">dungeon_floor_monster_pool</span>
                        <small class="text-muted ms-2">→</small>
                        <span class="model-name">App\Models\Dungeon\DungeonFloorMonsterPool</span>
                    </h3>
                    <div class="section-intro">
                        Пул монстров для этажа. При входе на этаж система выбирает монстров из пула с учётом весов (<code>weight</code>).
                        Кол-во монстров — случайное от <code>min_monsters</code> до <code>max_monsters</code> из <code>dungeon_floors</code>.
                    </div>

                    <table class="table table-bordered table-hover field-table">
                        <thead><tr><th>Поле</th><th>Тип</th><th>Описание</th><th>Допустимые значения</th></tr></thead>
                        <tbody>
                            <tr>
                                <td>floor_id</td>
                                <td>bigint</td>
                                <td>FK → <code>dungeon_floors.id</code></td>
                                <td></td>
                            </tr>
                            <tr>
                                <td>monster_id</td>
                                <td>bigint</td>
                                <td>FK → <code>monsters.id</code>. Обычный монстр из игры, тот же что и на локациях</td>
                                <td>Любой существующий монстр</td>
                            </tr>
                            <tr>
                                <td>weight</td>
                                <td>tinyint</td>
                                <td>
                                    Вес при случайном выборе монстра из пула. Чем выше — тем чаще появляется.
                                    Суммарные веса не обязаны давать 100 — это относительные числа.
                                </td>
                                <td>
                                    <code>10</code> = редкий, <code>50</code> = средний, <code>100</code> = частый<br>
                                    По умолчанию: <code>10</code>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                {{-- ══════════════════════════════════════════════════════════════ --}}
                {{-- dungeon_bosses --}}
                {{-- ══════════════════════════════════════════════════════════════ --}}
                <div class="doc-section">
                    <h3>
                        <span class="tbl-name">dungeon_bosses</span>
                        <small class="text-muted ms-2">→</small>
                        <span class="model-name">App\Models\Dungeon\DungeonBoss</span>
                    </h3>
                    <div class="section-intro">
                        Привязка боссов к данжу и этажу. Используется в стратегии <code>boss_rush</code> для трекинга количества боссов.
                        Сам босс — это обычный монстр с <code>is_boss=true</code> в таблице <code>monsters</code>, уже имеющий фазы и механики.
                    </div>

                    <table class="table table-bordered table-hover field-table">
                        <thead><tr><th>Поле</th><th>Тип</th><th>Описание</th><th>Допустимые значения</th></tr></thead>
                        <tbody>
                            <tr>
                                <td>dungeon_id</td>
                                <td>bigint</td>
                                <td>FK → <code>dungeons.id</code></td>
                                <td></td>
                            </tr>
                            <tr>
                                <td>floor_id</td>
                                <td>bigint / null</td>
                                <td>FK → <code>dungeon_floors.id</code>. На каком этаже появляется босс. NULL = финальный этаж</td>
                                <td>ID этажа или NULL</td>
                            </tr>
                            <tr>
                                <td>monster_id</td>
                                <td>bigint</td>
                                <td>FK → <code>monsters.id</code>. Монстр-босс</td>
                                <td>Монстры с <code>is_boss=true</code></td>
                            </tr>
                            <tr>
                                <td>sort_order</td>
                                <td>tinyint</td>
                                <td>Порядок появления в boss_rush данже</td>
                                <td><code>1</code>, <code>2</code>, <code>3</code> …</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                {{-- ══════════════════════════════════════════════════════════════ --}}
                {{-- dungeon_rewards --}}
                {{-- ══════════════════════════════════════════════════════════════ --}}
                <div class="doc-section">
                    <h3>
                        <span class="tbl-name">dungeon_rewards</span>
                        <small class="text-muted ms-2">→</small>
                        <span class="model-name">App\Models\Dungeon\DungeonReward</span>
                    </h3>
                    <div class="section-intro">
                        Таблица наград данжа. Все награды раздаются при успешном завершении рана (<code>DungeonRewardService::distribute()</code>).
                        Каждая строка — отдельная позиция награды с собственным шансом и pity.
                    </div>

                    <table class="table table-bordered table-hover field-table">
                        <thead><tr><th>Поле</th><th>Тип</th><th>Описание</th><th>Допустимые значения</th></tr></thead>
                        <tbody>
                            <tr>
                                <td>dungeon_id</td>
                                <td>bigint</td>
                                <td>FK → <code>dungeons.id</code></td>
                                <td></td>
                            </tr>
                            <tr>
                                <td>type</td>
                                <td>enum</td>
                                <td>
                                    Тип награды:<br>
                                    <span class="badge-enum">gold</span> — деньги (<code>users.money += amount</code>)<br>
                                    <span class="badge-enum">diamond</span> — премиум валюта (<code>users.diamond += amount</code>)<br>
                                    <span class="badge-enum">item</span> — предмет в рюкзак (<code>share_item_id</code> обязателен)<br>
                                    <span class="badge-enum">experience</span> — опыт (<code>players.exp += amount</code>)
                                </td>
                                <td>
                                    <span class="badge-enum">gold</span>
                                    <span class="badge-enum">diamond</span>
                                    <span class="badge-enum">item</span>
                                    <span class="badge-enum">experience</span>
                                </td>
                            </tr>
                            <tr>
                                <td>share_item_id</td>
                                <td>bigint / null</td>
                                <td>
                                    FK → <code>share_items.id</code>. Обязателен только для типа <code>item</code>.
                                    <span class="badge-note">Для остальных типов — NULL</span>
                                </td>
                                <td>ID предмета или NULL</td>
                            </tr>
                            <tr>
                                <td>amount_min</td>
                                <td>int</td>
                                <td>Минимальное кол-во (золото, опыт) или штук предмета</td>
                                <td><code>1</code> … любое положительное</td>
                            </tr>
                            <tr>
                                <td>amount_max</td>
                                <td>int</td>
                                <td>Максимальное кол-во. Фактическое = rand(min, max)</td>
                                <td>≥ <code>amount_min</code></td>
                            </tr>
                            <tr>
                                <td>drop_chance</td>
                                <td>float</td>
                                <td>
                                    Шанс выпадения в процентах. Проверяется через <code>mt_rand(0, 10000) / 100 &lt; drop_chance</code>.
                                    Игнорируется, если сработал pity.
                                </td>
                                <td><code>0.01</code> (0.01%) … <code>100.0</code> (гарантия)</td>
                            </tr>
                            <tr>
                                <td>pity_threshold</td>
                                <td>int / null</td>
                                <td>
                                    После скольких провалов подряд (<code>dungeon_pity.fail_count</code>) награда гарантируется.
                                    <span class="badge-note">NULL = нет pity</span>
                                </td>
                                <td><code>5</code>, <code>10</code>, <code>20</code> … или NULL</td>
                            </tr>
                            <tr>
                                <td>is_pity_only</td>
                                <td>boolean</td>
                                <td>
                                    Если <code>true</code> — предмет выдаётся ТОЛЬКО по pity (шанс вообще не проверяется без срабатывания pity).
                                    Используется для очень редких наград.
                                </td>
                                <td><code>true</code> / <code>false</code></td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                {{-- ══════════════════════════════════════════════════════════════ --}}
                {{-- dungeon_runs --}}
                {{-- ══════════════════════════════════════════════════════════════ --}}
                <div class="doc-section">
                    <h3>
                        <span class="tbl-name">dungeon_runs</span>
                        <small class="text-muted ms-2">→</small>
                        <span class="model-name">App\Models\Dungeon\DungeonRun</span>
                    </h3>
                    <div class="section-intro">
                        Инстанс прохождения — конкретная «попытка» данжа игроком или группой.
                        Создаётся при входе, живёт пока статус <code>active</code>.
                    </div>

                    <table class="table table-bordered table-hover field-table">
                        <thead><tr><th>Поле</th><th>Тип</th><th>Описание</th><th>Допустимые значения</th></tr></thead>
                        <tbody>
                            <tr>
                                <td>dungeon_id</td>
                                <td>bigint</td>
                                <td>FK → <code>dungeons.id</code></td>
                                <td></td>
                            </tr>
                            <tr>
                                <td>leader_id</td>
                                <td>bigint</td>
                                <td>FK → <code>users.id</code>. Кто создал ран (лидер группы или соло игрок)</td>
                                <td></td>
                            </tr>
                            <tr>
                                <td>party_id</td>
                                <td>bigint / null</td>
                                <td>FK → <code>parties.id</code>. <span class="badge-note">NULL для соло</span></td>
                                <td>ID группы или NULL</td>
                            </tr>
                            <tr>
                                <td>current_floor_id</td>
                                <td>bigint / null</td>
                                <td>FK → <code>dungeon_run_floors.id</code>. Указатель на текущий активный этаж инстанса</td>
                                <td>ID текущего DungeonRunFloor</td>
                            </tr>
                            <tr>
                                <td>status</td>
                                <td>enum</td>
                                <td>
                                    Текущее состояние рана:<br>
                                    <span class="badge-enum">active</span> — в процессе прохождения<br>
                                    <span class="badge-enum">completed</span> — успешно завершён, награды выданы<br>
                                    <span class="badge-enum">failed</span> — провалён (таймер, смерть), наград нет<br>
                                    <span class="badge-enum">abandoned</span> — покинут игроком вручную
                                </td>
                                <td>
                                    <span class="badge-enum">active</span>
                                    <span class="badge-enum">completed</span>
                                    <span class="badge-enum">failed</span>
                                    <span class="badge-enum">abandoned</span>
                                </td>
                            </tr>
                            <tr>
                                <td>started_at</td>
                                <td>timestamp</td>
                                <td>Когда начат ран</td>
                                <td></td>
                            </tr>
                            <tr>
                                <td>expires_at</td>
                                <td>timestamp / null</td>
                                <td>Дедлайн всего инстанса = <code>started_at + time_limit_seconds</code>. NULL если таймера нет.</td>
                                <td></td>
                            </tr>
                            <tr>
                                <td>metadata</td>
                                <td>json / null</td>
                                <td>
                                    Дополнительные данные, специфичные для стратегии:<br>
                                    survival: <code>{"waves_survived": 3}</code><br>
                                    boss_rush: <code>{"bosses_total": 5, "bosses_defeated": 2}</code>
                                </td>
                                <td>JSON-объект или NULL</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                {{-- ══════════════════════════════════════════════════════════════ --}}
                {{-- dungeon_run_floors --}}
                {{-- ══════════════════════════════════════════════════════════════ --}}
                <div class="doc-section">
                    <h3>
                        <span class="tbl-name">dungeon_run_floors</span>
                        <small class="text-muted ms-2">→</small>
                        <span class="model-name">App\Models\Dungeon\DungeonRunFloor</span>
                    </h3>
                    <div class="section-intro">
                        Каждый открытый этаж инстанса. При входе на новый этаж создаётся запись здесь,
                        спавнятся монстры, создаётся битва.
                    </div>

                    <table class="table table-bordered table-hover field-table">
                        <thead><tr><th>Поле</th><th>Тип</th><th>Описание</th><th>Допустимые значения</th></tr></thead>
                        <tbody>
                            <tr>
                                <td>run_id</td>
                                <td>bigint</td>
                                <td>FK → <code>dungeon_runs.id</code></td>
                                <td></td>
                            </tr>
                            <tr>
                                <td>floor_id</td>
                                <td>bigint</td>
                                <td>FK → <code>dungeon_floors.id</code> — из справочника</td>
                                <td></td>
                            </tr>
                            <tr>
                                <td>battle_id</td>
                                <td>bigint / null</td>
                                <td>FK → <code>battles.id</code>. Заполняется сразу при открытии этажа через <code>DungeonMonsterSpawner</code></td>
                                <td></td>
                            </tr>
                            <tr>
                                <td>status</td>
                                <td>enum</td>
                                <td>
                                    <span class="badge-enum">active</span> — бой идёт<br>
                                    <span class="badge-enum">completed</span> — этаж пройден<br>
                                    <span class="badge-enum">failed</span> — этаж провален (таймер/смерть)
                                </td>
                                <td>
                                    <span class="badge-enum">active</span>
                                    <span class="badge-enum">completed</span>
                                    <span class="badge-enum">failed</span>
                                </td>
                            </tr>
                            <tr>
                                <td>started_at</td>
                                <td>timestamp</td>
                                <td>Когда игрок вошёл на этаж</td>
                                <td></td>
                            </tr>
                            <tr>
                                <td>floor_expires_at</td>
                                <td>timestamp / null</td>
                                <td>Дедлайн этажа = <code>started_at + dungeon_floors.time_limit_seconds</code></td>
                                <td></td>
                            </tr>
                            <tr>
                                <td>completed_at</td>
                                <td>timestamp / null</td>
                                <td>Когда этаж был завершён (победа или провал)</td>
                                <td></td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                {{-- ══════════════════════════════════════════════════════════════ --}}
                {{-- dungeon_cooldowns --}}
                {{-- ══════════════════════════════════════════════════════════════ --}}
                <div class="doc-section">
                    <h3>
                        <span class="tbl-name">dungeon_cooldowns</span>
                        <small class="text-muted ms-2">→</small>
                        <span class="model-name">App\Models\Dungeon\DungeonCooldown</span>
                    </h3>
                    <div class="section-intro">
                        Хранит информацию о том, когда данж снова доступен.
                        Используется <code>updateOrCreate</code> — при повторном завершении запись обновляется.
                    </div>

                    <table class="table table-bordered table-hover field-table">
                        <thead><tr><th>Поле</th><th>Тип</th><th>Описание</th><th>Допустимые значения</th></tr></thead>
                        <tbody>
                            <tr>
                                <td>dungeon_id</td>
                                <td>bigint</td>
                                <td>FK → <code>dungeons.id</code></td>
                                <td></td>
                            </tr>
                            <tr>
                                <td>user_id</td>
                                <td>bigint</td>
                                <td>
                                    FK → <code>users.id</code> для персонального кулдауна.<br>
                                    <span class="badge-note">user_id = 0</span> — специальный sentinel для глобального кулдауна
                                    (MySQL 5.7 не поддерживает partial unique index, поэтому NULL нельзя использовать).
                                </td>
                                <td>ID пользователя или <code>0</code> (глобальный)</td>
                            </tr>
                            <tr>
                                <td>available_at</td>
                                <td>timestamp</td>
                                <td>Когда данж снова доступен. Если <code>available_at &lt; now()</code> — кулдаун прошёл.</td>
                                <td></td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                {{-- ══════════════════════════════════════════════════════════════ --}}
                {{-- dungeon_pity --}}
                {{-- ══════════════════════════════════════════════════════════════ --}}
                <div class="doc-section">
                    <h3>
                        <span class="tbl-name">dungeon_pity</span>
                        <small class="text-muted ms-2">→</small>
                        <span class="model-name">App\Models\Dungeon\DungeonPity</span>
                    </h3>
                    <div class="section-intro">
                        Счётчик неудачных попыток для гарантии выпадения редкой награды.
                        Логика: провал → <code>fail_count++</code>. Успех → <code>fail_count = 0</code>, <code>last_pity_at = now()</code>.
                        Когда <code>fail_count >= dungeon_rewards.pity_threshold</code> — награда гарантируется.
                    </div>

                    <table class="table table-bordered table-hover field-table">
                        <thead><tr><th>Поле</th><th>Тип</th><th>Описание</th><th>Допустимые значения</th></tr></thead>
                        <tbody>
                            <tr>
                                <td>dungeon_id</td>
                                <td>bigint</td>
                                <td>FK → <code>dungeons.id</code></td>
                                <td></td>
                            </tr>
                            <tr>
                                <td>user_id</td>
                                <td>bigint</td>
                                <td>FK → <code>users.id</code>. Pity всегда индивидуальный</td>
                                <td></td>
                            </tr>
                            <tr>
                                <td>fail_count</td>
                                <td>int</td>
                                <td>Текущее кол-во провалов подряд без получения pity-награды</td>
                                <td><code>0</code> … ∞. Сбрасывается в 0 при успешном завершении</td>
                            </tr>
                            <tr>
                                <td>last_fail_at</td>
                                <td>timestamp / null</td>
                                <td>Когда был последний провал. Для диагностики.</td>
                                <td></td>
                            </tr>
                            <tr>
                                <td>last_pity_at</td>
                                <td>timestamp / null</td>
                                <td>Когда последний раз срабатывал pity. Для диагностики.</td>
                                <td></td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                {{-- ══════════════════════════════════════════════════════════════ --}}
                {{-- parties --}}
                {{-- ══════════════════════════════════════════════════════════════ --}}
                <div class="doc-section">
                    <h3>
                        <span class="tbl-name">parties</span> + <span class="tbl-name">party_members</span>
                        <small class="text-muted ms-2">→</small>
                        <span class="model-name">App\Models\Party\Party</span>
                        <span class="model-name">App\Models\Party\PartyMember</span>
                    </h3>
                    <div class="section-intro">
                        Система групп. Группа создаётся до входа в данж. При входе статус → <code>in_dungeon</code>.
                        При выходе лидера — группа расформировывается.
                    </div>

                    <h4>parties</h4>
                    <table class="table table-bordered table-hover field-table">
                        <thead><tr><th>Поле</th><th>Тип</th><th>Описание</th><th>Допустимые значения</th></tr></thead>
                        <tbody>
                            <tr>
                                <td>leader_user_id</td>
                                <td>bigint</td>
                                <td>FK → <code>users.id</code>. Лидер группы — единственный кто может приглашать и начинать данж</td>
                                <td></td>
                            </tr>
                            <tr>
                                <td>status</td>
                                <td>enum</td>
                                <td>
                                    <span class="badge-enum">open</span> — ждём игроков, можно приглашать<br>
                                    <span class="badge-enum">in_dungeon</span> — в данже, новых не добавить<br>
                                    <span class="badge-enum">disbanded</span> — расформирована
                                </td>
                                <td>
                                    <span class="badge-enum">open</span>
                                    <span class="badge-enum">in_dungeon</span>
                                    <span class="badge-enum">disbanded</span>
                                </td>
                            </tr>
                            <tr>
                                <td>max_size</td>
                                <td>tinyint</td>
                                <td>Максимальное количество участников. Должно совпадать с <code>dungeons.max_players</code></td>
                                <td><code>2</code>–<code>10</code></td>
                            </tr>
                        </tbody>
                    </table>

                    <h4>party_members</h4>
                    <table class="table table-bordered table-hover field-table">
                        <thead><tr><th>Поле</th><th>Тип</th><th>Описание</th><th>Допустимые значения</th></tr></thead>
                        <tbody>
                            <tr>
                                <td>party_id</td>
                                <td>bigint</td>
                                <td>FK → <code>parties.id</code></td>
                                <td></td>
                            </tr>
                            <tr>
                                <td>user_id</td>
                                <td>bigint</td>
                                <td>FK → <code>users.id</code>. Включая лидера (лидер тоже является участником)</td>
                                <td></td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                {{-- ══════════════════════════════════════════════════════════════ --}}
                {{-- Связанные изменения в других таблицах --}}
                {{-- ══════════════════════════════════════════════════════════════ --}}
                <div class="doc-section">
                    <h3><i class="bx bx-link-alt"></i> Изменения в существующих таблицах</h3>

                    <h4>monster_on_locations</h4>
                    <table class="table table-bordered table-hover field-table">
                        <thead><tr><th>Поле</th><th>Тип</th><th>Описание</th><th></th></tr></thead>
                        <tbody>
                            <tr>
                                <td>dungeon_run_floor_id</td>
                                <td>bigint / null</td>
                                <td>
                                    FK → <code>dungeon_run_floors.id</code>. Добавлено для данжей.
                                    При спавне монстра в данже <code>location_id = null</code>, <code>dungeon_run_floor_id = &lt;id&gt;</code>.
                                    Обычные локационные монстры: <code>dungeon_run_floor_id = null</code>.
                                </td>
                                <td><span class="badge-note">миграция 000011</span></td>
                            </tr>
                        </tbody>
                    </table>

                    <h4>battles</h4>
                    <table class="table table-bordered table-hover field-table">
                        <thead><tr><th>Поле</th><th>Тип</th><th>Описание</th><th></th></tr></thead>
                        <tbody>
                            <tr>
                                <td>location_id</td>
                                <td>bigint / <strong>null</strong></td>
                                <td>Стало nullable. Для данжевых битв = NULL.</td>
                                <td><span class="badge-note">изменено в миграции 000012</span></td>
                            </tr>
                            <tr>
                                <td>dungeon_run_floor_id</td>
                                <td>bigint / null</td>
                                <td>FK → <code>dungeon_run_floors.id</code>. Заполняется для данжевых битв. Для обычных = NULL.</td>
                                <td><span class="badge-note">миграция 000012</span></td>
                            </tr>
                        </tbody>
                    </table>
                </div>

            </div>
        </section>
    </div>
</div>

@endsection