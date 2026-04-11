@extends('admin.layout.base')

@section('title') Документация — Кланы @endsection

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
    .field-table td:last-child { color: #27ae60; font-size: 11px; }
    .badge-enum { display: inline-block; background: #e8f0fe; color: #1967d2; border-radius: 3px; padding: 1px 6px; font-size: 11px; font-family: monospace; margin: 1px; }
    .badge-perm { display: inline-block; background: #e6f4ea; color: #1e7e34; border-radius: 3px; padding: 1px 6px; font-size: 11px; font-family: monospace; margin: 1px; }
    .badge-note { display: inline-block; background: #fff3cd; color: #856404; border-radius: 3px; padding: 1px 6px; font-size: 11px; }
    .flow-box { background: #f8f9fa; border: 1px solid #dee2e6; border-radius: 6px; padding: 14px 18px; font-size: 12px; margin-bottom: 12px; }
    .flow-box code { background: #e9ecef; padding: 1px 5px; border-radius: 3px; font-size: 11px; }
    .arrow { color: #0088cc; font-weight: bold; margin: 0 4px; }
    .tbl-name { font-family: monospace; font-size: 13px; font-weight: 700; color: #0d47a1; background: #e8f0fe; padding: 2px 8px; border-radius: 4px; }
    .model-name { font-family: monospace; font-size: 12px; color: #6a1b9a; background: #f3e5f5; padding: 1px 6px; border-radius: 3px; }
    .section-intro { background: #f0f7ff; border-left: 4px solid #0088cc; padding: 10px 14px; margin-bottom: 16px; font-size: 12px; color: #333; border-radius: 0 4px 4px 0; }
    .perm-bit { font-family: monospace; color: #888; }
</style>

<div class="row">
    <div class="col-lg-12">
        <section class="card">
            <header class="card-header">
                <div class="card-actions">
                    <i class="bx bx-shield" style="font-size:20px;color:#0088cc"></i>
                </div>
                <h2 class="card-title">Система кланов — документация</h2>
                <p class="card-subtitle">Описание таблиц, моделей, полей, прав и механик</p>
            </header>
            <div class="card-body">

                {{-- ══════════════════════════════════════════════════════════════ --}}
                {{-- ОБЩАЯ СХЕМА --}}
                {{-- ══════════════════════════════════════════════════════════════ --}}
                <div class="doc-section">
                    <h3><i class="bx bx-git-branch"></i> Общая схема работы</h3>

                    <div class="flow-box">
                        <strong>Создание клана:</strong><br><br>
                        Игрок создаёт клан через форму
                        <span class="arrow">→</span>
                        <code>ClanService::create()</code> создаёт запись в <code>clans</code>
                        <span class="arrow">→</span>
                        автоматически создаются 4 роли: <em>Глава клана</em> (все права), <em>Офицер</em>, <em>Рядовой</em>, <em>Новичок</em> (только чат)
                        <span class="arrow">→</span>
                        создатель добавляется в <code>clan_members</code> с ролью Главы
                    </div>

                    <div class="flow-box">
                        <strong>Вступление в клан:</strong><br><br>
                        Офицер отправляет приглашение
                        <span class="arrow">→</span>
                        запись в <code>clan_join_requests</code> со статусом <code>invite</code>
                        <span class="arrow">→</span>
                        игрок принимает
                        <span class="arrow">→</span>
                        создаётся <code>clan_members</code>
                        <span class="arrow">→</span>
                        <code>ClanSkillService::applyAllSkillsToPlayer()</code> — все навыки клана применяются к игроку
                    </div>

                    <div class="flow-box">
                        <strong>Выход / кик:</strong><br><br>
                        Игрок покидает клан или его исключают
                        <span class="arrow">→</span>
                        <code>clan_members</code> запись удаляется
                        <span class="arrow">→</span>
                        <code>ClanSkillService::removeAllSkillsFromPlayer()</code> — навыки клана снимаются с игрока
                        <span class="arrow">→</span>
                        запись в <code>clan_logs</code>
                    </div>

                    <div class="flow-box">
                        <strong>Передача руководства:</strong><br><br>
                        Глава назначает другого члена главой через форму ролей
                        <span class="arrow">→</span>
                        атомарно: старый глава понижается до первой дефолтной роли без <code>is_leader</code>
                        <span class="arrow">→</span>
                        новый участник получает роль с <code>is_leader = true</code>
                        <span class="arrow">→</span>
                        лог записывается
                    </div>
                </div>

                {{-- ══════════════════════════════════════════════════════════════ --}}
                {{-- clans --}}
                {{-- ══════════════════════════════════════════════════════════════ --}}
                <div class="doc-section">
                    <h3><span class="tbl-name">clans</span> &nbsp;<span class="model-name">Clan</span></h3>
                    <p class="section-intro">Основная запись клана. Один клан — один owner.</p>

                    <table class="table table-bordered table-sm field-table">
                        <thead><tr><th>Поле</th><th>Тип</th><th>Описание</th><th>Примечание</th></tr></thead>
                        <tbody>
                        <tr><td>id</td><td>bigint PK</td><td>Идентификатор клана</td><td></td></tr>
                        <tr><td>name</td><td>string</td><td>Название клана</td><td>Уникальное</td></tr>
                        <tr><td>description</td><td>text nullable</td><td>Описание клана</td><td>Редактируется через CHANGE_NEWS</td></tr>
                        <tr><td>news_1&nbsp;/ news_2&nbsp;/ news_3</td><td>string nullable</td><td>Три строки новостей клана</td><td>Отображаются в информации клана</td></tr>
                        <tr><td>icon</td><td>string nullable</td><td>Путь к иконке клана</td><td>Хранится в <code>storage/clan_icons/</code></td></tr>
                        <tr><td>owner_id</td><td>FK → users</td><td>Текущий глава клана</td><td>Eager-loaded по умолчанию</td></tr>
                        <tr><td>lvl</td><td>tinyint</td><td>Уровень клана</td><td>По умолчанию 1. Влияет на доступность навыков</td></tr>
                        <tr><td>warehouse_capacity</td><td>int</td><td>Число ячеек склада</td><td>Растёт с уровнем клана</td></tr>
                        <tr><td>points</td><td>int</td><td>Бонусные очки клана</td><td>Зарабатываются участниками в бою, тратятся на навыки</td></tr>
                        <tr><td>treasury</td><td>int</td><td>Казна (монеты)</td><td>Пополняется через ClanTreasury, тратится главой</td></tr>
                        </tbody>
                    </table>
                </div>

                {{-- ══════════════════════════════════════════════════════════════ --}}
                {{-- clan_roles --}}
                {{-- ══════════════════════════════════════════════════════════════ --}}
                <div class="doc-section">
                    <h3><span class="tbl-name">clan_roles</span> &nbsp;<span class="model-name">ClanRole</span></h3>
                    <p class="section-intro">Роли (звания) внутри клана. Права хранятся как битовая маска.</p>

                    <table class="table table-bordered table-sm field-table">
                        <thead><tr><th>Поле</th><th>Тип</th><th>Описание</th><th>Примечание</th></tr></thead>
                        <tbody>
                        <tr><td>id</td><td>bigint PK</td><td></td><td></td></tr>
                        <tr><td>clan_id</td><td>FK → clans</td><td>Клан которому принадлежит роль</td><td></td></tr>
                        <tr><td>name</td><td>string(16)</td><td>Название роли</td><td>Макс. 16 символов</td></tr>
                        <tr><td>permissions</td><td>int</td><td>Битовая маска прав</td><td>Сумма битов <code>ClanPermission</code></td></tr>
                        <tr><td>is_leader</td><td>boolean</td><td>Является ли роль ролью главы</td><td>Только одна роль в клане может быть главой. Нельзя изменить права и удалить</td></tr>
                        <tr><td>is_default</td><td>boolean</td><td>Системная роль (нельзя удалить)</td><td>4 роли создаются автоматически при создании клана</td></tr>
                        </tbody>
                    </table>

                    <h4>Битовые права <code>ClanPermission</code></h4>
                    <table class="table table-bordered table-sm field-table">
                        <thead><tr><th>Константа</th><th>Бит</th><th>Описание</th></tr></thead>
                        <tbody>
                        <tr><td>INVITE</td><td class="perm-bit">1</td><td>Приглашать игроков в клан</td></tr>
                        <tr><td>KICK</td><td class="perm-bit">2</td><td>Исключать участников из клана</td></tr>
                        <tr><td>CHANGE_PERMS</td><td class="perm-bit">32</td><td>Управлять ролями: создавать, редактировать, удалять</td></tr>
                        <tr><td>CHANGE_RANKS</td><td class="perm-bit">64</td><td>Менять роли участникам</td></tr>
                        <tr><td>CHANGE_NEWS</td><td class="perm-bit">128</td><td>Редактировать описание и новости клана</td></tr>
                        <tr><td>DEPOSIT</td><td class="perm-bit">512</td><td>Класть вещи на клановый склад</td></tr>
                        <tr><td>WITHDRAW_MONEY</td><td class="perm-bit">1024</td><td>Забирать деньги из казны</td></tr>
                        <tr><td>CHAT</td><td class="perm-bit">2048</td><td>Писать в клановый чат</td></tr>
                        <tr><td>WITHDRAW_ITEMS</td><td class="perm-bit">8192</td><td>Забирать вещи с кланового склада</td></tr>
                        <tr><td>LEARN_SKILL</td><td class="perm-bit">16384</td><td>Изучать клановые навыки</td></tr>
                        </tbody>
                    </table>
                    <p><span class="badge-note">Проверка права</span> — <code>ClanRole::hasPermission(ClanPermission $p): bool</code> — побитовое AND: <code>$this->permissions &amp; $p->bit()</code></p>
                </div>

                {{-- ══════════════════════════════════════════════════════════════ --}}
                {{-- clan_members --}}
                {{-- ══════════════════════════════════════════════════════════════ --}}
                <div class="doc-section">
                    <h3><span class="tbl-name">clan_members</span> &nbsp;<span class="model-name">ClanMember</span></h3>
                    <p class="section-intro">Связь игрок ↔ клан. Один игрок — максимум один активный членский токен.</p>

                    <table class="table table-bordered table-sm field-table">
                        <thead><tr><th>Поле</th><th>Тип</th><th>Описание</th><th>Примечание</th></tr></thead>
                        <tbody>
                        <tr><td>id</td><td>bigint PK</td><td></td><td></td></tr>
                        <tr><td>clan_id</td><td>FK → clans</td><td>Клан</td><td></td></tr>
                        <tr><td>user_id</td><td>FK → users</td><td>Игрок</td><td>UNIQUE — нельзя состоять в двух кланах</td></tr>
                        <tr><td>role_id</td><td>FK → clan_roles</td><td>Текущая роль (звание)</td><td>Изменяется через CHANGE_RANKS</td></tr>
                        <tr><td>points</td><td>int</td><td>Личные бонусные очки участника</td><td>Начисляются в бою, суммируются в <code>clans.points</code></td></tr>
                        </tbody>
                    </table>
                </div>

                {{-- ══════════════════════════════════════════════════════════════ --}}
                {{-- clan_join_requests --}}
                {{-- ══════════════════════════════════════════════════════════════ --}}
                <div class="doc-section">
                    <h3><span class="tbl-name">clan_join_requests</span> &nbsp;<span class="model-name">ClanJoinRequest</span></h3>
                    <p class="section-intro">Приглашения в клан (пока только <code>invite</code> — игрок не может подать заявку сам).</p>

                    <table class="table table-bordered table-sm field-table">
                        <thead><tr><th>Поле</th><th>Тип</th><th>Описание</th><th>Примечание</th></tr></thead>
                        <tbody>
                        <tr><td>id</td><td>bigint PK</td><td></td><td></td></tr>
                        <tr><td>clan_id</td><td>FK → clans</td><td>Клан, выслав приглашение</td><td></td></tr>
                        <tr><td>user_id</td><td>FK → users</td><td>Кому выслано</td><td></td></tr>
                        <tr><td>status</td><td>enum</td><td>Статус запроса</td><td><span class="badge-enum">invite</span> <span class="badge-enum">accepted</span> <span class="badge-enum">declined</span></td></tr>
                        </tbody>
                    </table>
                </div>

                {{-- ══════════════════════════════════════════════════════════════ --}}
                {{-- clan_logs --}}
                {{-- ══════════════════════════════════════════════════════════════ --}}
                <div class="doc-section">
                    <h3><span class="tbl-name">clan_logs</span> &nbsp;<span class="model-name">ClanLog</span></h3>
                    <p class="section-intro">Журнал всех действий в клане. Записывается автоматически при каждой операции через <code>ClanService::log()</code>.</p>

                    <table class="table table-bordered table-sm field-table">
                        <thead><tr><th>Поле</th><th>Тип</th><th>Описание</th><th>Примечание</th></tr></thead>
                        <tbody>
                        <tr><td>id</td><td>bigint PK</td><td></td><td></td></tr>
                        <tr><td>clan_id</td><td>FK → clans</td><td>Клан</td><td></td></tr>
                        <tr><td>user_id</td><td>FK → users nullable</td><td>Кто совершил действие</td><td>null — системное событие</td></tr>
                        <tr><td>action</td><td>enum <code>ClanLogAction</code></td><td>Тип события</td><td>
                            <span class="badge-enum">clan_created</span>
                            <span class="badge-enum">invited</span>
                            <span class="badge-enum">invited_cancel</span>
                            <span class="badge-enum">left</span>
                            <span class="badge-enum">kicked</span>
                            <span class="badge-enum">promoted</span>
                            <span class="badge-enum">bonus_points_earned</span>
                            <span class="badge-enum">skill_learned</span>
                            <span class="badge-enum">skill_upgraded</span>
                        </td></tr>
                        <tr><td>details</td><td>string</td><td>Текстовое описание действия</td><td>Генерируется сервисом автоматически</td></tr>
                        </tbody>
                    </table>
                </div>

                {{-- ══════════════════════════════════════════════════════════════ --}}
                {{-- clan_warehouses --}}
                {{-- ══════════════════════════════════════════════════════════════ --}}
                <div class="doc-section">
                    <h3><span class="tbl-name">clan_warehouses</span> &nbsp;<span class="model-name">ClanWarehouse</span></h3>
                    <p class="section-intro">Клановый склад — ячейки для хранения вещей. Доступен участникам с правами DEPOSIT / WITHDRAW_ITEMS.</p>

                    <table class="table table-bordered table-sm field-table">
                        <thead><tr><th>Поле</th><th>Тип</th><th>Описание</th><th>Примечание</th></tr></thead>
                        <tbody>
                        <tr><td>id</td><td>bigint PK</td><td></td><td></td></tr>
                        <tr><td>clan_id</td><td>FK → clans</td><td>Клан-владелец</td><td></td></tr>
                        <tr><td>item_id</td><td>FK → items</td><td>Предмет</td><td></td></tr>
                        <tr><td>count</td><td>int</td><td>Количество предметов в ячейке</td><td></td></tr>
                        <tr><td>depositor_user_id</td><td>FK → users nullable</td><td>Кто положил предмет</td><td>Для истории и логов</td></tr>
                        </tbody>
                    </table>

                    <h4>Связанные таблицы</h4>
                    <table class="table table-bordered table-sm field-table">
                        <thead><tr><th>Таблица</th><th>Модель</th><th>Описание</th></tr></thead>
                        <tbody>
                        <tr><td>clan_warehouse_logs</td><td class="model-name">ClanWarehouseLog</td><td>Лог каждой операции положить/взять со склада (user_id, item_id, count, action: deposit/withdraw)</td></tr>
                        <tr><td>clan_treasury_logs</td><td class="model-name">ClanTreasuryLog</td><td>Лог операций с казной (user_id, amount, direction: deposit/withdraw)</td></tr>
                        </tbody>
                    </table>

                    <div class="flow-box">
                        <strong>Лимит ячеек:</strong> <code>clans.warehouse_capacity</code> — максимальное число записей в <code>clan_warehouses</code> для данного клана.
                        Нельзя положить вещь если все ячейки заняты.<br><br>
                        <strong>Казна:</strong> хранится прямо в <code>clans.treasury</code> как int. <code>ClanTreasuryLog</code> фиксирует каждое движение денег.
                    </div>
                </div>

                {{-- ══════════════════════════════════════════════════════════════ --}}
                {{-- НАВЫКИ КЛАНА --}}
                {{-- ══════════════════════════════════════════════════════════════ --}}
                <div class="doc-section">
                    <h3><i class="bx bx-star"></i> Клановые навыки</h3>
                    <p class="section-intro">Клан может изучать навыки, которые автоматически применяются ко всем участникам.</p>

                    <div class="flow-box">
                        <strong>Схема навыков:</strong><br><br>
                        <code>clan_skill_definitions</code> (справочник навыков, max_level)
                        <span class="arrow">→</span>
                        <code>clan_skill_levels</code> (требования для каждого уровня)
                        <span class="arrow">→</span>
                        <code>clan_learned_skills</code> (что изучил конкретный клан)
                        <span class="arrow">→</span>
                        при изучении <code>player_magic_skills</code> обновляется для всех участников
                    </div>

                    <h4><span class="tbl-name">clan_skill_definitions</span> &nbsp;<span class="model-name">ClanSkillDefinition</span></h4>
                    <table class="table table-bordered table-sm field-table">
                        <thead><tr><th>Поле</th><th>Тип</th><th>Описание</th></tr></thead>
                        <tbody>
                        <tr><td>id</td><td>bigint PK</td><td></td></tr>
                        <tr><td>name</td><td>string</td><td>Название навыка</td></tr>
                        <tr><td>description</td><td>text nullable</td><td>Описание навыка</td></tr>
                        <tr><td>icon</td><td>string nullable</td><td>Путь к иконке</td></tr>
                        <tr><td>max_level</td><td>tinyint</td><td>Максимальный уровень навыка</td></tr>
                        <tr><td>sort_order</td><td>int</td><td>Порядок отображения в списке</td></tr>
                        </tbody>
                    </table>

                    <h4><span class="tbl-name">clan_skill_levels</span> &nbsp;<span class="model-name">ClanSkillLevel</span></h4>
                    <p>Требования для каждого уровня навыка:</p>
                    <table class="table table-bordered table-sm field-table">
                        <thead><tr><th>Поле</th><th>Тип</th><th>Описание</th><th>Примечание</th></tr></thead>
                        <tbody>
                        <tr><td>clan_skill_definition_id</td><td>FK</td><td>Навык</td><td></td></tr>
                        <tr><td>level</td><td>tinyint</td><td>Номер уровня (1, 2, 3…)</td><td></td></tr>
                        <tr><td>required_clan_level</td><td>tinyint</td><td>Минимальный уровень клана</td><td>Проверяется по <code>clans.lvl</code></td></tr>
                        <tr><td>required_bonus_points</td><td>int</td><td>Стоимость в бонусных очках</td><td>Списываются из <code>clans.points</code></td></tr>
                        <tr><td>share_item_id</td><td>FK → share_items nullable</td><td>Предмет-камень для изучения</td><td>Берётся из рюкзака инициатора</td></tr>
                        <tr><td>share_item_count</td><td>int nullable</td><td>Количество предметов</td><td>По умолчанию 1</td></tr>
                        <tr><td>magic_skill_id</td><td>FK → magic_skills nullable</td><td>Магический навык, который получают участники при изучении</td><td>Применяется через <code>player_magic_skills</code></td></tr>
                        </tbody>
                    </table>

                    <h4><span class="tbl-name">clan_learned_skills</span> &nbsp;<span class="model-name">ClanLearnedSkill</span></h4>
                    <table class="table table-bordered table-sm field-table">
                        <thead><tr><th>Поле</th><th>Тип</th><th>Описание</th></tr></thead>
                        <tbody>
                        <tr><td>clan_id</td><td>FK → clans</td><td>Клан</td></tr>
                        <tr><td>clan_skill_definition_id</td><td>FK</td><td>Какой навык изучен</td></tr>
                        <tr><td>current_level</td><td>tinyint</td><td>Текущий уровень навыка у клана</td></tr>
                        </tbody>
                    </table>

                    <div class="flow-box">
                        <strong>При изучении навыка (в транзакции):</strong><br>
                        1. Проверяется уровень клана, бонусные очки, наличие предмета в рюкзаке<br>
                        2. Списываются <code>clans.points</code><br>
                        3. Предмет удаляется из <code>backpacks</code> инициатора<br>
                        4. Создаётся/обновляется запись в <code>clan_learned_skills</code><br>
                        5. Старый magic_skill (предыдущего уровня) удаляется у всех участников из <code>player_magic_skills</code><br>
                        6. Новый magic_skill добавляется всем участникам через <code>upsert</code>
                    </div>
                </div>

                {{-- ══════════════════════════════════════════════════════════════ --}}
                {{-- КВЕСТЫ КЛАНА --}}
                {{-- ══════════════════════════════════════════════════════════════ --}}
                <div class="doc-section">
                    <h3><i class="bx bx-task"></i> Клановые квесты</h3>
                    <p class="section-intro">Квесты, доступные клану в целом. Прогресс общий на весь клан.</p>

                    <h4><span class="tbl-name">quest_clan_progress</span> &nbsp;<span class="model-name">QuestClanProgress</span></h4>
                    <table class="table table-bordered table-sm field-table">
                        <thead><tr><th>Поле</th><th>Тип</th><th>Описание</th><th>Примечание</th></tr></thead>
                        <tbody>
                        <tr><td>id</td><td>bigint PK</td><td></td><td></td></tr>
                        <tr><td>clan_id</td><td>FK → clans</td><td>Клан-участник квеста</td><td></td></tr>
                        <tr><td>user_id</td><td>FK → users</td><td>Кто принял квест</td><td></td></tr>
                        <tr><td>quest_id</td><td>FK → quests</td><td>Квест</td><td></td></tr>
                        <tr><td>status</td><td>enum <code>QuestPlayerStatus</code></td><td>Статус прохождения</td><td><span class="badge-enum">in_progress</span> <span class="badge-enum">completed</span> <span class="badge-enum">failed</span></td></tr>
                        <tr><td>stage</td><td>int</td><td>Текущий этап квеста</td><td></td></tr>
                        </tbody>
                    </table>

                    <h4><span class="tbl-name">quest_clan_objectives</span> &nbsp;<span class="model-name">QuestClanObjective</span></h4>
                    <table class="table table-bordered table-sm field-table">
                        <thead><tr><th>Поле</th><th>Тип</th><th>Описание</th></tr></thead>
                        <tbody>
                        <tr><td>quest_clan_progress_id</td><td>FK</td><td>Прогресс квеста</td></tr>
                        <tr><td>quest_objective_id</td><td>FK → quest_objectives</td><td>Конкретная задача квеста</td></tr>
                        <tr><td>current_count</td><td>int</td><td>Текущий прогресс выполнения задачи</td></tr>
                        </tbody>
                    </table>
                </div>

                {{-- ══════════════════════════════════════════════════════════════ --}}
                {{-- МАРШРУТЫ --}}
                {{-- ══════════════════════════════════════════════════════════════ --}}
                <div class="doc-section">
                    <h3><i class="bx bx-link"></i> Маршруты (routes/web.php)</h3>

                    <table class="table table-bordered table-sm field-table">
                        <thead><tr><th>Метод</th><th>URL</th><th>Имя</th><th>Право</th><th>Описание</th></tr></thead>
                        <tbody>
                        <tr><td>GET</td><td>/clan</td><td>clan.index</td><td>—</td><td>Главная страница клана / список кланов</td></tr>
                        <tr><td>POST</td><td>/clan</td><td>clan.create</td><td>—</td><td>Создать клан</td></tr>
                        <tr><td>GET</td><td>/clan/member</td><td>clan.member</td><td>—</td><td>Список участников</td></tr>
                        <tr><td>POST</td><td>/clan/member/save-roles</td><td>clan.member.save-roles</td><td>CHANGE_RANKS</td><td>Сохранить роли участников</td></tr>
                        <tr><td>POST</td><td>/clan/member/leave</td><td>clan.member.leave</td><td>—</td><td>Покинуть клан</td></tr>
                        <tr><td>DELETE</td><td>/clan/member/{target}</td><td>clan.member.kick</td><td>KICK</td><td>Исключить участника</td></tr>
                        <tr><td>GET</td><td>/clan/role</td><td>clan.role</td><td>CHANGE_PERMS</td><td>Управление ролями</td></tr>
                        <tr><td>POST</td><td>/clan/role/add</td><td>clan.role.add</td><td>CHANGE_PERMS</td><td>Добавить роль</td></tr>
                        <tr><td>POST</td><td>/clan/role/save</td><td>clan.role.save</td><td>CHANGE_PERMS</td><td>Сохранить права ролей</td></tr>
                        <tr><td>DELETE</td><td>/clan/role/{role}</td><td>clan.role.delete</td><td>CHANGE_PERMS</td><td>Удалить роль (только не-дефолтные)</td></tr>
                        <tr><td>POST</td><td>/clan/invite</td><td>clan.invite</td><td>INVITE</td><td>Пригласить игрока по нику</td></tr>
                        <tr><td>GET</td><td>/clan/request/{joinRequest}</td><td>clan.request.cancel</td><td>INVITE</td><td>Отменить приглашение</td></tr>
                        <tr><td>GET</td><td>/clan/information</td><td>clan.information</td><td>—</td><td>Информация о клане</td></tr>
                        <tr><td>POST</td><td>/clan/information/description</td><td>clan.information.description</td><td>CHANGE_NEWS</td><td>Сохранить описание</td></tr>
                        <tr><td>POST</td><td>/clan/information/news</td><td>clan.information.news</td><td>CHANGE_NEWS</td><td>Сохранить новости</td></tr>
                        <tr><td>GET</td><td>/clan/logs</td><td>clan.logs</td><td>—</td><td>Лог активности клана</td></tr>
                        <tr><td>GET</td><td>/clan/quests</td><td>clan.quests</td><td>—</td><td>Клановые квесты</td></tr>
                        <tr><td>GET</td><td>/clan/skills</td><td>clan.skills</td><td>—</td><td>Клановые навыки</td></tr>
                        <tr><td>POST</td><td>/clan/skills/{id}/learn</td><td>clan.skills.learn</td><td>LEARN_SKILL</td><td>Изучить / улучшить навык</td></tr>
                        <tr><td>GET</td><td>/clan-warehouse/{id}</td><td>clan-warehouse.put</td><td>DEPOSIT</td><td>Интерфейс сдачи вещи</td></tr>
                        <tr><td>POST</td><td>/clan-warehouse/{id}</td><td>clan-warehouse.put.action</td><td>DEPOSIT</td><td>Положить вещь на склад</td></tr>
                        <tr><td>GET</td><td>/clan-warehouse/{id}/take</td><td>clan-warehouse.take</td><td>WITHDRAW_ITEMS</td><td>Интерфейс получения вещи</td></tr>
                        <tr><td>POST</td><td>/clan-warehouse/{id}/take</td><td>clan-warehouse.take.action</td><td>WITHDRAW_ITEMS</td><td>Взять вещь со склада</td></tr>
                        <tr><td>GET</td><td>/clan-warehouse/{id}/logs</td><td>clan-warehouse.logs</td><td>—</td><td>Лог операций склада</td></tr>
                        <tr><td>GET</td><td>/clan-warehouse/{id}/treasury</td><td>clan-warehouse.treasury</td><td>—</td><td>Казна клана</td></tr>
                        <tr><td>POST</td><td>/clan-warehouse/{id}/treasury</td><td>clan-warehouse.treasury.action</td><td>DEPOSIT / WITHDRAW_MONEY</td><td>Пополнить / забрать из казны</td></tr>
                        </tbody>
                    </table>

                    <h4>Маршруты Admin (routes/admin.php)</h4>
                    <table class="table table-bordered table-sm field-table">
                        <thead><tr><th>Метод</th><th>URL</th><th>Описание</th></tr></thead>
                        <tbody>
                        <tr><td>GET</td><td>/admin/clans</td><td>Список всех кланов</td></tr>
                        <tr><td>GET</td><td>/admin/clan/{clan}</td><td>Подробная информация о клане</td></tr>
                        </tbody>
                    </table>
                </div>

                {{-- ══════════════════════════════════════════════════════════════ --}}
                {{-- СЕРВИСЫ --}}
                {{-- ══════════════════════════════════════════════════════════════ --}}
                <div class="doc-section">
                    <h3><i class="bx bx-cog"></i> Сервисы</h3>

                    <h4><code>ClanService</code></h4>
                    <table class="table table-bordered table-sm field-table">
                        <thead><tr><th>Метод</th><th>Требуемое право</th><th>Описание</th></tr></thead>
                        <tbody>
                        <tr><td>create(User, string $name, UploadedFile $icon): Clan</td><td>—</td><td>Создаёт клан, 4 дефолтных роли, добавляет создателя как главу</td></tr>
                        <tr><td>invite(User $inviter, string $nick): void</td><td>INVITE</td><td>Создаёт ClanJoinRequest со статусом invite</td></tr>
                        <tr><td>inviteCancel(User, ClanJoinRequest): void</td><td>INVITE</td><td>Удаляет приглашение</td></tr>
                        <tr><td>leaveClan(User): void</td><td>— (не глава)</td><td>Удаляет ClanMember. Глава покинуть не может</td></tr>
                        <tr><td>kickMember(User $kicker, User $target): void</td><td>KICK</td><td>Удаляет ClanMember target. Нельзя кикнуть главу</td></tr>
                        <tr><td>saveMemberRoles(User, array $members): void</td><td>CHANGE_RANKS</td><td>Массово обновляет role_id участников. Атомарная передача руководства</td></tr>
                        <tr><td>addRole(User, string $name): ClanRole</td><td>CHANGE_PERMS</td><td>Создаёт новую роль с правом CHAT</td></tr>
                        <tr><td>saveRoles(User, array $grades): void</td><td>CHANGE_PERMS</td><td>Сохраняет название и права каждой роли (кроме is_leader)</td></tr>
                        <tr><td>deleteRole(User, ClanRole): void</td><td>CHANGE_PERMS</td><td>Удаляет роль если она не дефолтная и не используется</td></tr>
                        <tr><td>saveDescription(User, string): void</td><td>CHANGE_NEWS</td><td>Обновляет clans.description</td></tr>
                        <tr><td>saveNews(User, string×3): void</td><td>CHANGE_NEWS</td><td>Обновляет clans.news_1/2/3</td></tr>
                        <tr><td>addBonusPoints(ClanMember, int): void</td><td>—</td><td>Прибавляет очки участнику и клану (вызывается из боёвки)</td></tr>
                        </tbody>
                    </table>

                    <h4><code>ClanSkillService</code></h4>
                    <table class="table table-bordered table-sm field-table">
                        <thead><tr><th>Метод</th><th>Описание</th></tr></thead>
                        <tbody>
                        <tr><td>learn(Clan, ClanSkillDefinition, Player): ?string</td><td>Изучает/улучшает навык в транзакции. Возвращает null при успехе или строку с ошибкой</td></tr>
                        <tr><td>applyAllSkillsToPlayer(Player, Clan): void</td><td>Применяет все текущие навыки клана к игроку (при вступлении)</td></tr>
                        <tr><td>removeAllSkillsFromPlayer(Player, Clan): void</td><td>Снимает все навыки клана с игрока (при выходе/кике)</td></tr>
                        </tbody>
                    </table>
                </div>

            </div>
        </section>
    </div>
</div>

@endsection