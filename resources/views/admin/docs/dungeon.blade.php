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
                        игрок входит, при необходимости тратит ключ из <code>backpacks</code>
                        <span class="arrow">→</span>
                        создаётся <code>dungeon_sessions</code>
                        <span class="arrow">→</span>
                        игрок или группа телепортируются в <code>first_location_id</code>
                        <span class="arrow">→</span>
                        монстры спавнятся в <code>monster_on_locations</code> с привязкой по <code>dungeon_session_id</code>
                        <span class="arrow">→</span>
                        бой идёт на обычных локациях данжа
                        <span class="arrow">→</span>
                        при survival-данже волны двигаются через <code>current_wave</code> в сессии
                        <span class="arrow">→</span>
                        при смерти игрока применяется настройка <code>death_behavior</code>
                        <span class="arrow">→</span>
                        при завершении выдаются <code>dungeon_rewards</code>, сессия помечается <code>completed_at</code> или удаляется при выходе
                    </div>

                    <div class="flow-box">
                        <strong>Кулдаун:</strong>
                        <code>dungeon_cooldowns</code> — персональный или глобальный кулдаун на вход. Если <code>user_id = 0</code> → глобальный кулдаун для всех.<br><br>
                        <strong>Группы:</strong>
                        <code>parties</code> + <code>party_members</code> — формируются до входа. Для группы создаётся ведущая сессия и дочерние сессии участников с общим пулом монстров.
                    </div>

                    <div class="flow-box">
                        <strong>Админка:</strong>
                        базовые настройки поведения при смерти редактируются в разделе <code>/admin/dungeons</code>.
                        Выбранная локация возврата должна принадлежать этому же данжу.
                    </div>
                </div>

                {{-- ══════════════════════════════════════════════════════════════ --}}
                {{-- dungeons --}}
                {{-- ══════════════════════════════════════════════════════════════ --}}
                <div class="doc-section">
                    <h3>
                        <span class="tbl-name">dungeons</span>
                        <small class="text-muted ms-2">→</small>
                        <span class="model-name">App\Modules\Dungeon\Infrastructure\Persistence\Models\Dungeon</span>
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
                            <tr>
                                <td>map_id</td>
                                <td>bigint / null</td>
                                <td>FK → <code>maps.id</code>. Карта, к которой относится вход или отображение данжа</td>
                                <td>ID карты или NULL</td>
                            </tr>
                            <tr>
                                <td>entry_location_id</td>
                                <td>bigint / null</td>
                                <td>FK → <code>locations.id</code>. Локация снаружи, откуда игрок заходит в данж</td>
                                <td>ID локации или NULL</td>
                            </tr>
                            <tr>
                                <td>first_location_id</td>
                                <td>bigint / null</td>
                                <td>
                                    FK → <code>locations.id</code>. Первая внутренняя локация данжа.
                                    При входе игрок телепортируется сюда.
                                </td>
                                <td>ID локации данжа или NULL</td>
                            </tr>
                            <tr>
                                <td>exit_location_id</td>
                                <td>bigint / null</td>
                                <td>
                                    FK → <code>locations.id</code>. Внутренняя локация, на которой разрешён обычный выход из данжа.
                                </td>
                                <td>ID локации данжа или NULL</td>
                            </tr>
                            <tr>
                                <td>return_location_id</td>
                                <td>bigint / null</td>
                                <td>
                                    FK → <code>locations.id</code>. Внешняя локация, куда игрок возвращается при выходе, окончании таймера или варианте смерти с выбросом наружу.
                                </td>
                                <td>ID локации или NULL</td>
                            </tr>
                            <tr>
                                <td>death_behavior</td>
                                <td>enum</td>
                                <td>
                                    Что делать с игроком после смерти в бою внутри данжа:<br>
                                    <span class="badge-enum">exit</span> — завершить поход и вывести наружу<br>
                                    <span class="badge-enum">return_to_start</span> — оставить сессию и вернуть в начало/указанную локацию данжа<br>
                                    <span class="badge-enum">kick_can_reenter</span> — вывести наружу, но сохранить сессию до конца таймера
                                </td>
                                <td>
                                    <span class="badge-enum">exit</span>
                                    <span class="badge-enum">return_to_start</span>
                                    <span class="badge-enum">kick_can_reenter</span>
                                </td>
                            </tr>
                            <tr>
                                <td>death_return_location_id</td>
                                <td>bigint / null</td>
                                <td>
                                    FK → <code>locations.id</code>. Переопределяет точку телепорта после смерти.
                                    Для <code>return_to_start</code> должна быть внутренней локацией текущего данжа.
                                    В админке допускается выбирать только локации этого данжа.
                                </td>
                                <td>ID локации данжа или NULL</td>
                            </tr>
                            <tr>
                                <td>monster_respawn</td>
                                <td>boolean</td>
                                <td>Разрешён ли респаун монстров внутри активной сессии данжа</td>
                                <td><code>true</code> / <code>false</code></td>
                            </tr>
                            <tr>
                                <td>wave_count</td>
                                <td>tinyint / null</td>
                                <td>Количество волн для survival-данжа</td>
                                <td><code>1</code>… или NULL</td>
                            </tr>
                            <tr>
                                <td>xp_multiplier</td>
                                <td>decimal / float</td>
                                <td>Множитель опыта за прохождение/боевые события данжа</td>
                                <td><code>1.0</code>, <code>1.5</code>, <code>2.0</code> …</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="doc-section">
                    <h3><i class="bx bx-info-circle"></i> Актуальная реализация</h3>
                    <div class="section-intro">
                        Старый экспериментальный слой с таблицами <code>dungeon_floors</code>, <code>dungeon_floor_branches</code>,
                        <code>dungeon_floor_monster_pool</code>, <code>dungeon_bosses</code>, <code>dungeon_runs</code>,
                        <code>dungeon_run_floors</code> и логикой pity удалён из runtime-кода как неиспользуемый.
                        Текущая реализация работает через обычные локации, ворота данжа и таблицу <code>dungeon_sessions</code>.
                    </div>
                </div>

                <div class="doc-section">
                    <h3><i class="bx bx-skull"></i> Поведение при смерти в данже</h3>
                    <div class="section-intro">
                        Поведение задаётся на уровне конкретного данжа полями <code>death_behavior</code> и
                        <code>death_return_location_id</code>. Настройки доступны в админке: <code>/admin/dungeons</code>.
                    </div>

                    <table class="table table-bordered table-hover field-table">
                        <thead><tr><th>Значение</th><th>Что происходит</th><th>Сессия</th><th>Куда телепортирует</th></tr></thead>
                        <tbody>
                            <tr>
                                <td>exit</td>
                                <td>Поход считается проваленным, игрок выходит из данжа</td>
                                <td>Удаляется</td>
                                <td><code>return_location_id</code>, если задан; иначе безопасная внешняя локация по умолчанию</td>
                            </tr>
                            <tr>
                                <td>return_to_start</td>
                                <td>Игрок остаётся в активном походе и возвращается назад в данж</td>
                                <td>Сохраняется</td>
                                <td><code>death_return_location_id</code>, если задан; иначе <code>first_location_id</code></td>
                            </tr>
                            <tr>
                                <td>kick_can_reenter</td>
                                <td>Игрока выбрасывает наружу, но кнопка входа может вернуть его в активный поход, пока не истёк таймер</td>
                                <td>Сохраняется до истечения <code>expires_at</code> или ручного выхода</td>
                                <td><code>death_return_location_id</code>, если задан; иначе <code>return_location_id</code></td>
                            </tr>
                        </tbody>
                    </table>

                    <p>
                        Для варианта <code>kick_can_reenter</code> важно наличие таймера <code>time_limit_seconds</code>,
                        иначе активная сессия может жить слишком долго. Для <code>return_to_start</code> поле
                        <code>death_return_location_id</code> должно указывать на внутреннюю локацию этого же данжа.
                    </p>
                </div>

                {{-- ══════════════════════════════════════════════════════════════ --}}
                {{-- dungeon_rewards --}}
                {{-- ══════════════════════════════════════════════════════════════ --}}
                <div class="doc-section">
                    <h3>
                        <span class="tbl-name">dungeon_rewards</span>
                        <small class="text-muted ms-2">→</small>
                        <span class="model-name">App\Modules\Dungeon\Infrastructure\Persistence\Models\DungeonReward</span>
                    </h3>
                    <div class="section-intro">
                        Таблица наград данжа. Награды выдаются при успешном завершении активной сессии данжа.
                        Каждая строка — отдельная позиция награды с собственным шансом.
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
                                    Шанс выпадения в процентах. Проверяется в коде при завершении данжа.
                                </td>
                                <td><code>0.01</code> (0.01%) … <code>100.0</code> (гарантия)</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                {{-- ══════════════════════════════════════════════════════════════ --}}
                {{-- dungeon_sessions --}}
                {{-- ══════════════════════════════════════════════════════════════ --}}
                <div class="doc-section">
                    <h3>
                        <span class="tbl-name">dungeon_sessions</span>
                        <small class="text-muted ms-2">→</small>
                        <span class="model-name">App\Modules\Dungeon\Infrastructure\Persistence\Models\DungeonSession</span>
                    </h3>
                    <div class="section-intro">
                        Активная сессия прохождения данжа для конкретного игрока.
                        Для группы создаётся ведущая сессия лидера и дочерние сессии участников с общим пулом монстров.
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
                                <td>FK → <code>users.id</code>. Для одного пользователя может существовать только одна активная сессия</td>
                                <td></td>
                            </tr>
                            <tr>
                                <td>primary_session_id</td>
                                <td>bigint / null</td>
                                <td>ID ведущей сессии. <span class="badge-note">NULL для соло и лидера группы</span></td>
                                <td>ID сессии или NULL</td>
                            </tr>
                            <tr>
                                <td>current_wave</td>
                                <td>tinyint</td>
                                <td>Текущая волна survival-данжа. Для обычного данжа обычно остаётся <code>1</code></td>
                                <td><code>1</code>, <code>2</code>, <code>3</code> …</td>
                            </tr>
                            <tr>
                                <td>entered_at</td>
                                <td>timestamp</td>
                                <td>
                                    Когда игрок вошёл в данж
                                </td>
                                <td></td>
                            </tr>
                            <tr>
                                <td>expires_at</td>
                                <td>timestamp / null</td>
                                <td>Дедлайн сессии = <code>entered_at + time_limit_seconds</code>. NULL если таймера нет.</td>
                                <td></td>
                            </tr>
                            <tr>
                                <td>completed_at</td>
                                <td>timestamp / null</td>
                                <td>Когда данж был полностью завершён и награды уже выданы</td>
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
                        <span class="model-name">App\Modules\Dungeon\Infrastructure\Persistence\Models\DungeonCooldown</span>
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
                                <td>dungeon_session_id</td>
                                <td>bigint / null</td>
                                <td>
                                    FK → <code>dungeon_sessions.id</code>. Добавлено для данжей.
                                    При спавне монстра в данже поле указывает на сессию-владельца пула монстров.
                                    Обычные локационные монстры: <code>dungeon_session_id = null</code>.
                                </td>
                                <td><span class="badge-note">миграция 000006</span></td>
                            </tr>
                        </tbody>
                    </table>

                    <h4>item_on_locations</h4>
                    <table class="table table-bordered table-hover field-table">
                        <thead><tr><th>Поле</th><th>Тип</th><th>Описание</th><th></th></tr></thead>
                        <tbody>
                            <tr>
                                <td>dungeon_session_id</td>
                                <td>bigint / null</td>
                                <td>FK → <code>dungeon_sessions.id</code>. Используется для привязки дропа к конкретной сессии данжа.</td>
                                <td><span class="badge-note">миграция 000006</span></td>
                            </tr>
                        </tbody>
                    </table>
                </div>

            </div>
        </section>
    </div>
</div>

@endsection
