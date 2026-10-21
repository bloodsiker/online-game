# Мировые боссы: Опустевшая Долина и Катакомбы Жертвоприношений

Два open-world босса, добавлены напрямую в БД (не миграцией — по явной просьбе).
Характеристики намеренно завышены ×10 относительно обычного моба того же
уровня по hp/armor/урону — цель: соло только на премиум-шмоте, в основном
ходят группой.

## Костяной Скорпион (monster id=350)

- **Карта:** Опустевшая Долина (map_id=3)
- **Локация:** Овраг Исполинского Логова (location_id=283)
- **Уровень:** 36
- **Лор:** Его панцирь срастался десятилетиями — с костями скота, странников
  и других скорпионов, что пытались забрать его логово. Он не охотится — он
  ждёт, зарывшись в сухую землю Долины, и выходит на поверхность, лишь
  почуяв кровь. Ни один одиночный отряд не возвращался из его оврага —
  только большие группы с хорошим снаряжением.

| Стат | Обычный моб зоны (~lvl 36) | Костяной Скорпион (×10 hp/armor/dmg) |
|---|---|---|
| hp | ~290 | **2900** |
| armor | ~21 | **210** |
| min_dmg / max_dmg | 27 / 51 | **189 / 357** (срезано на 30% от первонач. 270/510 после тест-боя на lvl48+прем) |
| dodge | 0 | 0 |
| critical | 29-37 | 40 |
| magic_resistance | 0-20 | 20 |
| attack_type | physical | physical |
| passive | — | — |
| aggression | — | 90 |
| respawn | — | 90-150 мин |
| exp (предварительно) | 2160 | 15000 |
| gold (финально) | 173-347 | **100000-150000** (задано пользователем — ориентир «сколько реально фармится в зоне за час», не расчётное) |

## Азазель (monster id=351)

- **Карта:** Катакомбы Жертвоприношений (map_id=6)
- **Локация:** Задымлённое Подземелье (location_id=818) — финал карты,
  на момент создания единственный монстр на этой карте
- **Уровень:** 45
- **Лор:** Когда-то простой безымянный служитель у алтарей катакомб — пока
  однажды сам не стал жертвой собственного ритуала. Культ не вернул его
  душу, а дал взамен нечто худшее. Теперь он не служит жертвеннику — он
  питается теми, кто проходит катакомбы до конца, вытягивая жизнь так же,
  как когда-то вытягивали её из жертв на его алтаре.

| Стат | Обычный моб зоны (~lvl 45) | Азазель (×10 hp/armor/dmg) |
|---|---|---|
| hp | ~300 | **3000** |
| armor | ~40 | **400** |
| min_dmg / max_dmg | 40 / 77 | **400 / 770** |
| dodge | 0-16 | 40 |
| critical | 16-105 | 60 |
| magic_resistance | 0-100 | 50 |
| attack_type | physical | physical |
| passive | vampirism 15% (у обычных мобов) | **vampirism 20%** |
| aggression | — | 90 |
| respawn | — | 90-150 мин |
| exp (предварительно) | 3019 | 18000 |
| gold (предварительно) | 191-382 | 1500-3000 |

## Фазы боя (`boss_mechanics`)

Механики триггерятся по % HP босса (`trigger_hp_percent`, срабатывает когда
HP ≤ порога), реализация — `app/Modules/Battle/.../Boss/Mechanics/*`.

### Костяной Скорпион

| HP ≤ | Механика | Конфиг | Смысл |
|---|---|---|---|
| 70% | `aoe_attack` | 45% базового урона, повтор раз в 4 хода | Удар хвостом по всем участникам боя |
| 35% | `enrage` | +50% урона (одноразово, cooldown 50) | Раненый зверь бьёт сильнее |
| 15% | `shield` | щит 1500 hp на 3 хода (одноразово) | Панцирь на последнем издыхании — тянет время, вынуждает группу на добивание |

### Азазель

| Триггер | Механика | Конфиг | Смысл |
|---|---|---|---|
| HP ≤ 75% | `life_drain` | 35% от атаки, повтор раз в 4 хода | Высасывает жизнь сверх базового пассива vampirism |
| HP ≤ 50% | `summon_minions` | 1-2 шт., максимум 2 живых, кд 6 ходов | Призывает забытых жертв катакомб — Дряхлый Скелет (id269) / Мертвец (id278), пул в `monster_summon_pool` |
| смерть | `death_explosion` | 40% от max hp | Ритуальный взрыв при убийстве — последний укус |


# Промпты для картинок: Костяной Скорпион и Азазель

См. `docs/valley-catacombs-world-bosses.md` (характеристики, лор, размещение).

## Костяной Скорпион (monster id=350)

```
Painterly semi-realistic dark fantasy bestiary illustration, a massive ancient scorpion with a thick cracked carapace entirely overgrown and fused with bleached bones — skulls, ribs and horns of cattle and fallen travelers embedded into its plated back and tail, dripping venomous stinger raised high, standing half-buried in the cracked dry earth of a desolate ravine littered with bones and withered scarecrows, dust and heat haze rising around it under a dull overcast sky. Dark dusty ochre-grey atmosphere, dramatic harsh daylight, square composition, full-bleed with no border or frame, creature filling most of the frame.
```

## Азазель (monster id=351)

```
Painterly semi-realistic dark fantasy bestiary illustration, a gaunt once-human acolyte transformed into a demon, pale cracked skin stretched over an elongated frame, tattered sacrificial robes fused into his flesh, clawed hands stained dark with old blood, faint sickly violet glow leaking from cracks across his chest and hollow eyes, standing before a smoke-filled ritual dungeon chamber with broken altars and dying braziers behind him, thin smoke coiling around his feet. Dark violet-grey smoky atmosphere, dramatic dim ember glow, square composition, full-bleed with no border or frame, creature filling most of the frame.
```


**Исправление собственной ошибки:** здесь было написано, что без
`cooldown_turns` `enrage`/`shield` сработают каждый ход и `attack_modifier`
будет бесконечно расти. Это неверно — `BossMechanicsService::processMechanics()`
(отдельно от `canTrigger()`) хранит `boss_metadata['mechanics_triggered']`
и пропускает механику, если у неё **нет** `cooldown_turns` в конфиге и она
уже сработала хоть раз за бой — то есть без кулдауна эти механики и так
одноразовые. `cooldown_turns: 50` в конфиге `enrage`/`shield` выше избыточен,
но не вреден — просто другой путь к тому же результату (переключает
проверку на `cooldown_turns`-ветку в `BaseBossMechanic::canTrigger()`).

### Реализованные недостающие механики движка

При подборе механик обнаружился баг: в `BossMechanicType` заявлено 15
типов, а классов в `Boss/Mechanics/` было только 12 — `TELEPORT`,
`BUFF_SELF` и `DEBUFF_PLAYER` не имели реализации (повесить такую
механику на босса привело бы к `RuntimeException` прямо во время боя).
Реализованы все три:

- **`TeleportMechanic`** — на N ходов все удары игрока проходят мимо
  (`boss_metadata['teleport_dodge']`, потребляется новым
  `AttackService::checkTeleportDodge()`, срабатывает раньше иммунитета/щита).
- **`BuffSelfMechanic`** — универсальный самобафф: `attack_increase_percent`
  копится в уже рабочем `attack_modifier` (как у Enrage/Berserk),
  `defense_increase_percent` — новый `boss_metadata['defense_buff']`,
  гасит % входящего урона (`AttackService::applyDefenseBuff()`).
- **`DebuffPlayerMechanic`** — накладывает на игрока существующий боевой
  эффект (яд/кровотечение/оглушение/паралич) через уже рабочий
  `BattleEffectService::applyCustomEffectToPlayer()` — тот же путь, что у
  пассивки «Оглушение» рун и немилости Кайрелла. Не стал изобретать
  произвольный «−X к стату»: это требует записи в каталоге `effects` со
  `stat_modifiers`, которой тут взять негде.

Пока не навешены ни на одного босса — например, Азазелю (демон) логично
подошли бы `teleport`/`debuff_player`, если захочется усложнить фазы.

## Примечания

- exp/gold — предварительные числа, не привязаны к конкретному лут-дропу;
  досчитаем вместе с таблицей дропа (отдельная тема).
- `attack_type` оставлен `physical` для обоих — в игре нет ни одного
  монстра с `attack_type='magic'`, поэтому не стал изобретать непроверенную
  комбинацию (`magic_attack`/`magic_power_coefficient`) без прецедента в коде.
- `passive_type` поддерживает только `vampirism`/`reflect` (см.
  `monsters.passive_type`) — яда/DOT как системного пассива нет, поэтому у
  Костяного Скорпиона яд выражен только через `critical`, без отдельного
  эффекта урона со временем.
- Оба — `is_boss=1`, `does_not_regenerate_hp=1`, размещены через
  `location_has_monsters` (а не `monster_on_locations` — та таблица хранит
  уже заспавненные инстансы, а не правила спавна).
