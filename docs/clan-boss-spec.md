# Клан-босс — спека MVP

Дата: 2026-10-12. Статус: реализовано (MVP), см. раздел 10.

Актуализация механики времени: у клановых боссов нет отдельного таймера этапа.
Действует только `clan_boss_runs.expires_at`, заданный общим временем данжа при открытии похода.
`dungeon_stages.time_limit_seconds` для клановых боссов не применяется, новые состояния этапов
создаются с `expires_at = null`. Старые значения `clan_boss_run_stages.expires_at` игнорируются:
они не запрещают удары и не приводят к пропуску босса. Смена босса происходит после убийства,
а по общему таймеру завершается весь поход. Упоминания окна жизни отдельного босса ниже — историческая часть MVP.

## 1. Что делаем

### Подробный учёт наград

Миграция `2026_10_17_000008_create_clan_boss_reward_receipts.php` добавляет журнал фактической выдачи:
одна квитанция на получателя (игрока или клан) и этап текущего похода. В JSON хранятся точные
монеты, бриллианты, реально начисленный опыт, опыт клана и предметы с количеством, переводами
названия и редкостью на момент выдачи. Награды игрока и предметы в клановом хранилище разделены.
Квитанции создаются в той же транзакции, что и начисление. Изменение баланса, имени игрока,
названия предмета или настроек наград не пересчитывает историю. Для старых походов без
квитанций выводится явная пометка об отсутствии подробного учёта; задним числом награды не выдаются.

После коммита событие `ClanBossRewardGranted` ставит уведомление в очередь: личная награда
сообщается получателю, клановая — в канал клана. Предметы в сообщениях используют игровые
шорткоды. `notified_at` и блокировка квитанции защищают от повторного уведомления при ретраях.
При завершении похода по времени записываются только фактически начисленные монеты.

Асинхронный клановый рейд на боссов по очереди (слабый → сильный) за один открытый поход.
Каждый соклан бьет в удобное время в течение окна жизни, урон всех суммируется в общий HP босса.
Вход — через новую структуру на локации 28. Награды — личные (золото+опыт) и клановые (склад, клан-опыт, рейтинг урона).

## 2. Зафиксированные решения

1. Формат — асинхронный общий HP (не синхронный tower-поход группой).
2. Вход — новый тип структуры `clan_boss` на локации 28, внутри кнопка «Войти / Открыть поход» в привязанный данж. Ключ (`dungeons.entry_share_item_id`) опционален: если задан — списывается один раз при открытии похода кланом, участники дальше бьют бесплатно.
3. Лимиты — общий таймер похода и пакет ударов игрока на весь `clan_boss_run`: игрок делает настраиваемое число ударов подряд, затем ждёт настраиваемый КД и получает новый пакет. Удар = 1 раунд атаки в `FightOrchestrator::attack()`.
4. Редкий дроп — из `monster_has_items` босса, падает сразу в `clan_warehouses`, а не на землю.
5. Фазы/механики — как у обычных боссов (`BossPhaseService`, `BossMechanicsService`). Призыв аддов не делать. Прерывание каста не делать. Клановые баффы действуют как обычно, отдельной интеграции нет.
6. Порог награды — >1% суммарного урона по боссу, иначе ничего. Анти-твин: минимум дней в клане + минимальный урон (настраивается на данже).

## 3. Схема данных

### 3.1. Новые колонки (миграции)

`dungeons`:
- `is_clan_boss` bool default false — маркер клан-босс данжа (`type = tower`).
- `min_clan_days` int default 3 — анти-твин: минимум дней в клане для наград (и/или для ударов — см. 4.2).
- `min_damage` bigint default 0 — абсолютный минимум урона для награды (дополнительно к 1%).
- `clan_boss_hit_batch_size` unsigned small default 10 — ударов в пакете.
- `clan_boss_hit_batch_cooldown_seconds` unsigned int default 21600 — КД после исчерпания пакета; действует через переходы между боссами.

`dungeon_stages` (1 этаж = 1 босс):
- `clan_cooldown_seconds` int nullable — КД клана перед повторным открытием этого босса.
- `time_limit_seconds` (уже есть) — окно жизни босса.

Старые `max_hits_per_user` и `personal_cooldown_seconds` на `dungeon_stages` больше не участвуют в лимите атак клан-босса.

`structures`:
- новый тип `clan_boss` (`Structure::TYPE_CLAN_BOSS`, `TYPES`, `isClanBoss()`).
- `dungeon_id` nullable FK → `dungeons` — привязка структуры к клан-босс данжу.

`monster_on_locations`:
- `clan_boss_stage_state_id` nullable FK → `clan_boss_run_stages` — маркер общего инстанса босса. Обычные бои его не имеют, хук клан-босса срабатывает только при наличии.

### 3.2. Новые таблицы

`clan_boss_runs` — открытый кланом поход:
- `id`, `dungeon_id` FK, `clan_id` FK, `opened_by_user_id` FK,
- `current_stage_id` nullable FK → `dungeon_stages`,
- `status`: `active/completed/expired`,
- `started_at`, `expires_at` (окно жизни данжа = `dungeons.time_limit_seconds`), `finished_at` nullable.

`clan_boss_run_stages` — состояние каждого босса в походе:
- `id`, `clan_boss_run_id` FK, `dungeon_stage_id` FK,
- `monster_on_location_id` FK — единственный общий инстанс босса,
- `hp_max`, `status`: `active/killed/expired`, `started_at`, `expires_at` (окно жизни босса = `dungeon_stages.time_limit_seconds`), `killed_at` nullable.

`clan_boss_contributions` — вклад игроков (рейтинг, лимиты, награды):
- `id`, `clan_boss_run_id` FK, `dungeon_stage_id` FK, `user_id` FK,
- `damage` bigint default 0, `hits` int default 0,
- `last_hit_at` nullable (время последнего удара по этому боссу), `rewarded` bool default false.

`clan_boss_player_attack_states` — состояние пакета ударов игрока на весь поход:
- unique `(clan_boss_run_id, user_id)`;
- `hits_in_batch` unsigned small — сколько ударов потрачено в текущем пакете;
- `cooldown_until` nullable — после последнего удара пакета; по истечении следующий удар начинает новый пакет.
- unique `(clan_boss_run_id, dungeon_stage_id, user_id)`.

`clan_boss_cooldowns` — клановые КД:
- `dungeon_stage_id` FK, `clan_id` FK, `available_at`.
- Проверяется при открытии/переходе: следующий босс открывается только после `available_at`.

## 4. Потоки

### 4.1. Открытие похода

1. Офицер (право клана, уточнить какое — кандидат: управление/казна) на структуре `clan_boss` жмет «Открыть поход».
2. Проверки: клан есть; нет активного `clan_boss_runs` этого данжа у клана; клановый КД (`clan_boss_cooldowns`) истек; ключ есть в рюкзаке открывающего (если `entry_share_item_id` задан) → списать 1 шт; `min_level` открывающего.
3. Создать `clan_boss_runs` (`expires_at = now + dungeon.time_limit_seconds`), создать stage-state для этапа 1: заспавнить один `MonsterOnLocation` (босс этапа, `hp_now/hp_max = monster.hp`, `location_id = 28`, `active = 1`, `clan_boss_stage_state_id` — после создания state обновить связь), `current_stage_id = 1`.

### 4.2. Удар по боссу

1. Соклан на локации 28 жмет «Атаковать» → `PlayerAttackInitiator`-подобный код создает отдельный `Battle` (`location_id = 28`) с `BattleDetail` игрока + `BattleDetail` на общий `MonsterOnLocation`.
2. Каждый удар — штатный `FightOrchestrator::attack()`. До нанесения урона — гарды клан-босса: состоит в клане-владельце run; активные run и stage-state (окна не истекли); состояние пакета не находится на КД. Пока пакет не исчерпан, удары идут без личного КД. После `clan_boss_hit_batch_size`-го удара ставится КД из `clan_boss_hit_batch_cooldown_seconds`; счётчик и КД общие для всех боссов этого похода.
3. Урон пишется в общий инстанс. Строки `MonsterOnLocation`, run и состояния пакета лочатся (`lockForUpdate`), чтобы параллельные атаки не превысили пакет. В той же транзакции сервис пишет урон/число ударов в `clan_boss_contributions` и продвигает состояние пакета.
4. Обычный опыт за урон (`awardMonsterExperience` → и клан-опыт капает) и прокачка скиллов остаются как есть. Обычный `dropMoney` при убийстве общего инстанса подавляется (деньги раздаст reward-сервис), чтобы не достались только добившему.
5. Контратака босса — штатная, бьет каждого атакующего отдельно (приемлемо для MVP).

### 4.3. Убийство босса и переход

1. Первый же удар, доводящий общий `hp_now` до 0, в той же транзакции (`lockForUpdate` на run/state): `active = 0`, stage-state `killed`, `BossDefeatLog` — одна строка на добившего (семантика рейтинга не меняется).
2. Вызвать `ClanBossRewardService::grantForStage(run, stage)`:
   - `total = hp_max`; квалыфицированы: `damage > 1% total` И `damage >= dungeons.min_damage` И `дней в клане >= min_clan_days`.
   - Игрокам: золото + опыт — по `dungeon_rewards` (`GOLD`, `EXPERIENCE`; `DIAMOND` — по решению, по умолчанию тоже раздать) через существующий `DungeonRewardService`-подобный код для каждого квалифицированного.
   - Клану: клан-опыт (через `ClanExperienceService`, сумма по участникам или фиксированный бонус — решить при реализации; кандидат: вызвать для каждого квала с его exp).
   - Редкий дроп: ролл `monster_has_items` босса → создать `Item` по `ShareItem` → строки `clan_warehouses` (+ `ClanWarehouseLog`, `ClanLog`).
   - Пометить `rewarded = true`; записать клановый КД следующего этапа (`clan_cooldown_seconds` текущего? или открываемого — зафиксировать при реализации: КД относится к убитому боссу).
3. Если есть следующий `dungeon_stages` (по `number`/`next_stage_id`) и окно данжа не истекло — создать stage-state + общий инстанс следующего босса, `current_stage_id` обновить. Иначе run `completed`, `finished_at = now`.
4. Бои других игроков, ссылающиеся на убитый инстанс, завершаются штатно (цель мертва).

### 4.4. Протухание

Шедулер (аналог `ProcessExpiredDungeonRuns`, напр. `ProcessExpiredClanBossRuns`, раз в минуту):
- stage-state с истекшим `expires_at` и статусом `active` → `expired`; run продолжается? Решение MVP: поход продолжается, босс считается пропущенным — переход к следующему этапу без наград (или run `expired` целиком — выбрать при реализации; кандидат: пропуск с пометкой, чтобы клан мог бить следующего).
- run с истекшим `expires_at` → `expired`, общий инстанс деактивировать, бои завершить.

## 5. Структура: UI и роуты (по образцу `ClanSkillHall`)

- `Structure/ClanBoss/Presentation/Http/ClanBossController`: `index($id)` (статус похода, HP-бар текущего босса, список этапов, топ урона по текущему/прошлым боссам, кнопки «Открыть поход» / «Атаковать»), `open($id)` (POST), `attack($id)` (POST — создать бой и редирект в бой).
- Роуты `clan-boss/{id}`, `clan-boss/{id}/open`, `clan-boss/{id}/attack`.
- Вьюха `clan_boss::index`. Проверка `isClanBoss()` + `location_id = 28` + игрок на локации.
- Админка: клан-босс данж собирается существующими средствами (данж `tower` + `is_clan_boss`, этапы с монстрами-боссами `is_boss = 1` с фазами/механиками, награды `dungeon_rewards`, структура `clan_boss` с `dungeon_id`).

## 6. Интеграция с боем — точки врезки

- `FightOrchestrator::attack()`: после загрузки `locationMonster` проверить `clan_boss_stage_state_id`; лочить строку `MonsterOnLocation` (`lockForUpdate`); вызвать гарды `ClanBossService::assertCanHit()`; после `AttackService::execute()` снять реальную дельту HP и записать contribution; при `hp_now <= 0` идти клан-босс веткой вместо `AttackService::handleMonsterDeath()` (подавить `dropMoney`, квестовый прогресс и `DungeonStageService::handleMonsterKilled` для этого инстанса — иначе tower-логика сработает на общем мобе).
- Ограничение MVP: `boss_metadata` (щиты/иммунитеты/отражение) хранится на `battles`, т.е. пер-бой, а не пер-босс; фазы (`current_phase`, статы) — общие через инстанс. Приемлемо, фиксируем как известное ограничение.

## 7. Тесты

- Юнит/фича на `ClanBossService`: открытие (ключ/без ключа, повторное открытие запрещено, клановый КД), пакет (удары без промежуточного КД, блок после исчерпания, восстановление после КД, сохранение пакета при переходе на следующего босса), окно жизни и чужой клан.
- Суммирование урона: два игрока бьют общий инстанс → `damage` в contributions сходится с `hp_max - hp_now`.
- Награды: порог 1% (ниже — ничего), редкий дроп уходит в `clan_warehouses`, а не на землю/в рюкзак; `rewarded` идемпотентен при двойном вызове.
- Переход этапов: слабый → сильный, завершение run после последнего.
- Протухание: истекший stage/run через консольную команду.
- По образцу `tests/Feature/Modules/Dungeon/DungeonStageServiceTest.php` (sqlite in-memory, ручное создание схемы).

## 8. Валидация

- `php artisan test --filter ClanBoss`, затем широкие `Battle/Dungeon/Clan` тесты.
- `./vendor/bin/pint` по измененным PHP-файлам.

## 9. Открытые вопросы (решить при реализации)

Решено при реализации MVP:
1. Поход открывает лидер (`clan_roles.is_leader`) или владелец клана. Отдельного permission нет.
2. `min_clan_days` проверяется только для наград, бить может и новичок (урон все равно не даст награды).
3. Клан-опыт: для каждого квалифицированного вызывается существующий `ClanExperienceService::awardForMonsterExperience` с выданным ему опытом (штатные срезы по разнице уровней работают).
4. `DIAMOND`-награды из `dungeon_rewards` раздаются квалам как есть.
5. Протухший босс пропускается без наград (статус `skipped`), поход идет дальше.
6. `clan_cooldown_seconds` относится к убитому боссу.
7. Босс спавнится с `aggression = 0` (без автоагро на лок. 28), `is_drop_money = 1` (обычная раздача денег подавлена — деньги идут через награды), регена нет (`MonsterOnLocation::regenerate()` пропускает общие инстансы).

## 10. Что реализовано (файлы)

- Миграции `2026_10_12_300000_create_clan_boss_tables.php` и `2026_10_12_310000_add_clan_boss_attack_batches.php`: параметры пакета в `dungeons`, таблица `clan_boss_player_attack_states` и базовая схема клан-босса.
- Модели `Dungeon/.../Models/ClanBoss{Run,RunStage,Contribution,Cooldown}` + `isClanBoss()` в `Dungeon`/`Structure`/`MonsterOnLocation`, `clanBossDungeon()` в `Structure`.
- `Dungeon/Application/Services/ClanBossService` (open/assertCanHit/recordHit/handleStageKill/expireOverdue/rating) и `ClanBossRewardService` (порог `>1%` + `min_damage`, золото/опыт/диаманты квалам, `ITEM`-награды и ролл `monster_has_items` в `clan_warehouses`, фолбэк в рюкзак топ-урона, `ClanLogAction::BOSS_KILLED`).
- Врезка в бой: `FightOrchestrator` (лок строки босса, гарды, запись вклада, клановая ветка смерти, тихий выход для чужих добиваний), `BattleFinishService` (нет граунд-дропа из клан-босса).
- Структура: `Structure/ClanBoss` (контроллер `index/open/attack`, роуты `clan_boss*`, вьюха с HP-баром и рейтингом), пункт в `LocationPageViewMapper`, регистрация в `StructureServiceProvider`.
- Админка: `Admin/DungeonController@info/saveStage` + вьюхи (флаг `is_clan_boss`, `min_clan_days`, `min_damage`, лимиты этапов).
- Шедулер: `ProcessExpiredClanBossRuns` + задача `dungeons.process_expired_clan_boss_runs` в `ScheduledTaskRegistry`.
- Тесты: `tests/Feature/Modules/Dungeon/ClanBossServiceTest` (4 теста), схема `DungeonStageManagementTest` дополнена новыми колонками.
- Известное ограничение: `boss_metadata` (щиты/иммунитеты/отражение) хранится на `battles`, т.е. пер-бой, а не пер-босс.
- Для запуска нужны: данж `tower` + `is_clan_boss`, этапы с 1 боссом `is_boss=1`, структура `clan_boss` на лок. 28 с `dungeon_id`, `php artisan migrate`.
- Наполнение целиком через админку:
  1. Монстры (`/admin/monsters`): создать боссов (`is_boss`), статы, фазы (`boss_phases`), механики (`boss_mechanics`, без призыва), дроп (`monster_has_items` — редкое полетит в клан-склад).
  2. Данж (`/admin/dungeons`): тип `tower`, галка «Клан-босс», `min_clan_days`, `min_damage`, общий срок похода (`time_limit_seconds`), размер пакета и КД восстановления, ключ входа опционально, `min_level`; этапы по порядку (слабый → сильный): при необходимости индивидуальное окно босса и КД клана перед повторным походом; на каждом этапе 1 монстр-босс (`quantity = 1`); награды данжа (`dungeon_rewards`: золото/опыт/диаманты игрокам, предметы — в склад).
  3. Структура (`/admin/structures`): тип «Логово кланового босса», локация 28, привязка данжа (`dungeon_id`).
