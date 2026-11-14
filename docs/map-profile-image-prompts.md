# Промпты: картинки карт для публичной информации игрока
Блок «Находится» в публичной информации игрока (`user::partials.location-card`): панорама **5:2**, `object-fit: cover`, поверх нижней части — тёмный градиент с названием карты. Загружается в админке: Карты → карта → «Картинка карты в публичном профиле» (`maps.profile_image`, JPG/PNG/WebP до 5 МБ).
**Размер файла:** генерируйте в 5:2 (например, 1500×600), затем уменьшите до **1200×480** (запас под Retina; минимум по рекомендации админки — 600×240) и сохраните в WebP. Подрезать ничего не нужно — пропорция та же.
Нижние 30% кадра — спокойный тёмный передний план: там лежат градиент и название. Текст на картинку не рисуем.
Общая часть (уже включена в каждый промпт ниже):
```text
Wide cinematic panoramic landscape, strict 5:2 aspect ratio (2.5:1), painterly dark-fantasy MMORPG environment concept art, rich detail, atmospheric depth, warm gold light against deep shadows, one consistent art style for the whole series. Composition: the main subject sits in the upper two-thirds; the bottom 30% is a calm, darker foreground (ground, water, mist) with no important details, because the website overlays a dark gradient and the map name there. No characters, no creatures in the foreground, no text, no letters, no watermark, no frame, no border. --ar 5:2
```
| id | Карта | Что показать |
|---|---|---|
| 1 | Нейрин | Столица. Южные ворота, Торговый переулок, Городское хранилище, Зал Волшебного Алтаря, Площадь Реликвий. |
| 2 | Шепчущий Лес | Дорога у Ворот, Придорожный Столб, Тропа Заблудших, Развилка у Старого Дуба. |
| 3 | Опустевшая Долина | Врата Обезлюдевшей Долины, Заброшенное Поле, Покосившийся Плетень, Пустой Колодец, Обвалившийся Амбар. |
| 4 | Ожившая окраина | Заросшая Обочина, Безлюдный Перекрёсток, Тропа Опустевших Дворов, Тихий Пустырь. |
| 5 | Заброшенное кладбище | Покосившиеся Кресты, Разрытая Земля, Треснувшее Надгробие, Склеп Без Имени, Туманная Низина. |
| 6 | Катакомбы Жертвоприношений | Зал Разбитых Алтарей, Коридор Ржавых Цепей, Галерея Высохшей Крови, Коридор Костяной Пыли. |
| 7 | Канализация | Спуск в канализацию, Скользкий Отвод, Развилка Тоннелей, Перекрёсток Труб, Затопленный Поворот. |
| 8 | Забытый Курган | Первый Свод, Осыпающийся Проход, Тоннель Червоточин, Перекрёсток Костей, Тоннель Осиных Гнёзд. |
| 9 | Заросшая дорога | Вход на Заросшую дорогу, Развилка Покосившейся Вехи, Тупик у Обглоданных Костей, Придорожье Ползучего Плюща. |
| 10 | Сторожевые Холмы | Вход в Сторожевые Холмы, Присыпанный Вал, Выветренный Пост, Тропа Забытой Смены, Гряда Первого Ожидания. |
| 13 | Гранитный Перевал | Вход на Гранитный Перевал, Тропа Первых Валунов, Осыпь Глухого Гула, Склон Спящих Исполинов, Тропа Каменной Пыли. |
| 14 | Заваленная Штольня | Вход в Заваленную Штольню, Штрек Осыпающихся Крепей, Завал Первой Выработки, Ниша Брошенных Кирок, Площадка Старой Лебёдки. |
| 15 | Карригмор | Выход на Простор, Первая Полоса Полей, Развилка Пастушьих Троп, Ветреный Косогор, Межевой Камень Карригмора. |
| 16 | Пепельные Предгорья | Вход в Пепельные Предгорья, Выжженный Склон, Тропа Сухого Пепла, Серая Осыпь, Обугленная Роща. |
| 17 | Пепельные Глубины | Вход в Пепельные Глубины (карта подготовлена, локаций пока нет). |
| 18 | Подскальная дорога | Дорога у подножия скалы между Карригмором и Заваленной Штольней (локаций пока нет). |

Подземелья (карта «dungeon») в публичном профиле игрока не показываются — для них промпты не нужны.

## 1. Нейрин

```text
Fantasy MMORPG location banner: a grand fortified fantasy capital city at golden hour: massive pale-stone walls with a huge arched southern gate, spired towers, crowded market rooftops, a glowing domed magic altar hall rising above the city, long banners with a golden ouroboros emblem, warm lantern light in the streets. Wide cinematic panoramic landscape, strict 5:2 aspect ratio (2.5:1), painterly dark-fantasy MMORPG environment concept art, rich detail, atmospheric depth, warm gold light against deep shadows, one consistent art style for the whole series. Composition: the main subject sits in the upper two-thirds; the bottom 30% is a calm, darker foreground (ground, water, mist) with no important details, because the website overlays a dark gradient and the map name there. No characters, no creatures in the foreground, no text, no letters, no watermark, no frame, no border. --ar 5:2
```

## 2. Шепчущий Лес

```text
Fantasy MMORPG location banner: a dense ancient forest seen from a narrow dirt road with a weathered wooden signpost, a huge gnarled old oak at a fork in the path, shafts of pale light through the canopy, drifting mist and faint ghostly pale-blue wisps that seem to whisper between the trunks. Wide cinematic panoramic landscape, strict 5:2 aspect ratio (2.5:1), painterly dark-fantasy MMORPG environment concept art, rich detail, atmospheric depth, warm gold light against deep shadows, one consistent art style for the whole series. Composition: the main subject sits in the upper two-thirds; the bottom 30% is a calm, darker foreground (ground, water, mist) with no important details, because the website overlays a dark gradient and the map name there. No characters, no creatures in the foreground, no text, no letters, no watermark, no frame, no border. --ar 5:2
```

## 3. Опустевшая Долина

```text
Fantasy MMORPG location banner: an abandoned farming valley under an overcast sky: weathered gate posts, a leaning wooden fence, an empty stone well, overgrown fields gone to weeds, a half-collapsed barn, no people, a quiet melancholic mood, muted greens and ochre. Wide cinematic panoramic landscape, strict 5:2 aspect ratio (2.5:1), painterly dark-fantasy MMORPG environment concept art, rich detail, atmospheric depth, warm gold light against deep shadows, one consistent art style for the whole series. Composition: the main subject sits in the upper two-thirds; the bottom 30% is a calm, darker foreground (ground, water, mist) with no important details, because the website overlays a dark gradient and the map name there. No characters, no creatures in the foreground, no text, no letters, no watermark, no frame, no border. --ar 5:2
```

## 4. Ожившая окраина

```text
Fantasy MMORPG location banner: the eerie outskirts of a dead village at dusk: an overgrown roadside, a silent crossroads, empty farmyards with sagging roofs, a quiet wasteland, and a faint unnatural warm glow in the dark windows of the empty houses as if something has stirred back to life. Wide cinematic panoramic landscape, strict 5:2 aspect ratio (2.5:1), painterly dark-fantasy MMORPG environment concept art, rich detail, atmospheric depth, warm gold light against deep shadows, one consistent art style for the whole series. Composition: the main subject sits in the upper two-thirds; the bottom 30% is a calm, darker foreground (ground, water, mist) with no important details, because the website overlays a dark gradient and the map name there. No characters, no creatures in the foreground, no text, no letters, no watermark, no frame, no border. --ar 5:2
```

## 5. Заброшенное кладбище

```text
Fantasy MMORPG location banner: a neglected graveyard on a misty lowland under a cold moon: crooked wooden and stone crosses, freshly disturbed earth, a cracked headstone, a nameless stone crypt with a sealed door in the distance, thick low fog, bare twisted trees. Wide cinematic panoramic landscape, strict 5:2 aspect ratio (2.5:1), painterly dark-fantasy MMORPG environment concept art, rich detail, atmospheric depth, warm gold light against deep shadows, one consistent art style for the whole series. Composition: the main subject sits in the upper two-thirds; the bottom 30% is a calm, darker foreground (ground, water, mist) with no important details, because the website overlays a dark gradient and the map name there. No characters, no creatures in the foreground, no text, no letters, no watermark, no frame, no border. --ar 5:2
```

## 6. Катакомбы Жертвоприношений

```text
Fantasy MMORPG location banner: a vast underground catacomb hall lit by torches: broken stone altars, hanging rusted chains, rows of carved columns, dark stains of dried blood on the floor, drifting bone dust, an ominous crimson glow from the far end of the corridor. Wide cinematic panoramic landscape, strict 5:2 aspect ratio (2.5:1), painterly dark-fantasy MMORPG environment concept art, rich detail, atmospheric depth, warm gold light against deep shadows, one consistent art style for the whole series. Composition: the main subject sits in the upper two-thirds; the bottom 30% is a calm, darker foreground (ground, water, mist) with no important details, because the website overlays a dark gradient and the map name there. No characters, no creatures in the foreground, no text, no letters, no watermark, no frame, no border. --ar 5:2
```

## 7. Канализация

```text
Fantasy MMORPG location banner: the city sewers: a long arched brick tunnel with slimy walls, old iron pipes crossing the ceiling, a junction of several tunnels, murky green water along a stone walkway, dim greenish lantern light and rising steam. Wide cinematic panoramic landscape, strict 5:2 aspect ratio (2.5:1), painterly dark-fantasy MMORPG environment concept art, rich detail, atmospheric depth, warm gold light against deep shadows, one consistent art style for the whole series. Composition: the main subject sits in the upper two-thirds; the bottom 30% is a calm, darker foreground (ground, water, mist) with no important details, because the website overlays a dark gradient and the map name there. No characters, no creatures in the foreground, no text, no letters, no watermark, no frame, no border. --ar 5:2
```

## 8. Забытый Курган

```text
Fantasy MMORPG location banner: the inside of an ancient burial mound: vaulted earthen-and-stone passages held up by huge old stone ribs, crumbling walls riddled with burrows, a crossroads strewn with old bones, giant papery wasp nests clinging to the ceiling, faint warm light from a distant opening. Wide cinematic panoramic landscape, strict 5:2 aspect ratio (2.5:1), painterly dark-fantasy MMORPG environment concept art, rich detail, atmospheric depth, warm gold light against deep shadows, one consistent art style for the whole series. Composition: the main subject sits in the upper two-thirds; the bottom 30% is a calm, darker foreground (ground, water, mist) with no important details, because the website overlays a dark gradient and the map name there. No characters, no creatures in the foreground, no text, no letters, no watermark, no frame, no border. --ar 5:2
```

## 9. Заросшая дорога

```text
Fantasy MMORPG location banner: an old paved road swallowed by nature: thorny vines across the way, creeping ivy on the roadside, a tilted split milestone, a dead end with gnawed bones, twisted dry trees, a heavy overgrown atmosphere in sickly greens and browns. Wide cinematic panoramic landscape, strict 5:2 aspect ratio (2.5:1), painterly dark-fantasy MMORPG environment concept art, rich detail, atmospheric depth, warm gold light against deep shadows, one consistent art style for the whole series. Composition: the main subject sits in the upper two-thirds; the bottom 30% is a calm, darker foreground (ground, water, mist) with no important details, because the website overlays a dark gradient and the map name there. No characters, no creatures in the foreground, no text, no letters, no watermark, no frame, no border. --ar 5:2
```

## 10. Сторожевые Холмы

```text
Fantasy MMORPG location banner: windswept rolling hills with long-abandoned watch posts: a half-buried earthen rampart, a weathered stone outpost tower, a faint trail between silent boulders, a lone rusted brazier, a huge cold sky with fast clouds, a feeling of someone who never came back. Wide cinematic panoramic landscape, strict 5:2 aspect ratio (2.5:1), painterly dark-fantasy MMORPG environment concept art, rich detail, atmospheric depth, warm gold light against deep shadows, one consistent art style for the whole series. Composition: the main subject sits in the upper two-thirds; the bottom 30% is a calm, darker foreground (ground, water, mist) with no important details, because the website overlays a dark gradient and the map name there. No characters, no creatures in the foreground, no text, no letters, no watermark, no frame, no border. --ar 5:2
```

## 13. Гранитный Перевал

```text
Fantasy MMORPG location banner: a harsh granite mountain pass: a narrow trail between huge boulders, a rockfall of loose stones, mountain slopes whose rock formations resemble sleeping stone giants, drifting stone dust, cold bluish light with a warm sun glow on the far peaks. Wide cinematic panoramic landscape, strict 5:2 aspect ratio (2.5:1), painterly dark-fantasy MMORPG environment concept art, rich detail, atmospheric depth, warm gold light against deep shadows, one consistent art style for the whole series. Composition: the main subject sits in the upper two-thirds; the bottom 30% is a calm, darker foreground (ground, water, mist) with no important details, because the website overlays a dark gradient and the map name there. No characters, no creatures in the foreground, no text, no letters, no watermark, no frame, no border. --ar 5:2
```

## 14. Заваленная Штольня

```text
Fantasy MMORPG location banner: an abandoned collapsed mine tunnel: rotten timber supports, a cave-in blocking the shaft, abandoned pickaxes in a niche, a rusty old winch platform, dusty hanging lanterns throwing small warm pools of light, rails vanishing into the dark. Wide cinematic panoramic landscape, strict 5:2 aspect ratio (2.5:1), painterly dark-fantasy MMORPG environment concept art, rich detail, atmospheric depth, warm gold light against deep shadows, one consistent art style for the whole series. Composition: the main subject sits in the upper two-thirds; the bottom 30% is a calm, darker foreground (ground, water, mist) with no important details, because the website overlays a dark gradient and the map name there. No characters, no creatures in the foreground, no text, no letters, no watermark, no frame, no border. --ar 5:2
```

## 15. Карригмор

```text
Fantasy MMORPG location banner: view of a small cozy fantasy town seen from the approach road, the town is the clear main subject filling the centre of the frame: clustered timber-framed and stone houses with steep tiled roofs, smoking chimneys, a low stone wall with a modest gate, a small church tower and market square rooftops, warm lit windows, with golden wheat fields and a winding road leading to the gate, warm evening sky, welcoming and peaceful. Wide cinematic panoramic landscape, strict 5:2 aspect ratio (2.5:1), painterly dark-fantasy MMORPG environment concept art, rich detail, atmospheric depth, warm gold light against deep shadows, one consistent art style for the whole series. Composition: the main subject sits in the upper two-thirds; the bottom 30% is a calm, darker foreground (ground, water, mist) with no important details, because the website overlays a dark gradient and the map name there. No characters, no creatures in the foreground, no text, no letters, no watermark, no frame, no border. --ar 5:2
```

## 16. Пепельные Предгорья

```text
Fantasy MMORPG location banner: scorched foothills under a smoky sky: burnt slopes, a trail of dry grey ash, a grey scree, a grove of charred black trees, faint ember glow on the ground, volcanic mountains in the haze, falling ash flakes. Wide cinematic panoramic landscape, strict 5:2 aspect ratio (2.5:1), painterly dark-fantasy MMORPG environment concept art, rich detail, atmospheric depth, warm gold light against deep shadows, one consistent art style for the whole series. Composition: the main subject sits in the upper two-thirds; the bottom 30% is a calm, darker foreground (ground, water, mist) with no important details, because the website overlays a dark gradient and the map name there. No characters, no creatures in the foreground, no text, no letters, no watermark, no frame, no border. --ar 5:2
```

## 17. Пепельные Глубины

```text
Fantasy MMORPG location banner: interior of a vast underground cave system, no sky visible: a huge cavern with a rough black basalt ceiling and hanging stalactites, glowing red-orange magma rifts and lava streams in the floor, ember-lit ash drifting in the air, glassy obsidian columns, a narrow tunnel mouth leading deeper into darkness, faint molten glow lighting the cave walls, claustrophobic, dark and dangerous. Wide cinematic panoramic landscape, strict 5:2 aspect ratio (2.5:1), painterly dark-fantasy MMORPG environment concept art, rich detail, atmospheric depth, warm gold light against deep shadows, one consistent art style for the whole series. Composition: the main subject sits in the upper two-thirds; the bottom 30% is a calm, darker foreground (ground, water, mist) with no important details, because the website overlays a dark gradient and the map name there. No characters, no creatures in the foreground, no text, no letters, no watermark, no frame, no border. --ar 5:2
```

## 18. Подскальная дорога

```text
Fantasy MMORPG location banner: an old road running beneath a towering rock cliff: a narrow cobbled track hugging the base of a huge overhanging granite wall, loose boulders and fallen stones at the roadside, a cracked wooden cart wheel and a rotten signpost, thin waterfalls of meltwater trickling down the rock, cold blue shadow under the overhang with a warm glow of the town far behind, a dark mine entrance faintly visible at the far end of the road, heavy and slightly ominous mood. Wide cinematic panoramic landscape, strict 5:2 aspect ratio (2.5:1), painterly dark-fantasy MMORPG environment concept art, rich detail, atmospheric depth, warm gold light against deep shadows, one consistent art style for the whole series. Composition: the main subject sits in the upper two-thirds; the bottom 30% is a calm, darker foreground (ground, water, mist) with no important details, because the website overlays a dark gradient and the map name there. No characters, no creatures in the foreground, no text, no letters, no watermark, no frame, no border. --ar 5:2
```
