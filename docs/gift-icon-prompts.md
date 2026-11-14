# Промпты: иконки подарков

Пять подарков модуля `Gift` (миграция `2026_11_13_000001_create_player_gifts_and_seed_gifts`). Сейчас у всех временная заглушка `storage/app/public/items/gifts/<slug>.gif` — замените картинку в админке предмета (или положите файл с тем же именем).

Общий стиль — как у остальных иконок инвентаря: крупный предмет по центру, белый контур и мягкое белое свечение, прозрачный фон. Отображается в 60×60.

| Подарок | Где продаётся | Цена | Редкость | Файл |
|---|---|---|---|---|
| Букет лесных колокольчиков | Торговая лавка | 300 монет | обычный | `items/gifts/bluebell-bouquet.gif` |
| Медовый пряник | Торговая лавка | 800 монет | необычный | `items/gifts/honey-gingerbread.gif` |
| Фонарь светлячков | Торговая лавка | 2 500 монет | редкий | `items/gifts/firefly-lantern.gif` |
| Хрустальная роза | Премиум | 10 💎 | эпический | `items/gifts/crystal-rose.gif` |
| Звезда Вечности | Премиум | 25 💎 | легендарный | `items/gifts/star-of-eternity.gif` |

Общий хвост для всех промптов:

```text
Classic hand-painted fantasy MMORPG inventory art, crisp contours, soft dimensional shading, strong silhouette readable at 60×60 pixels. Large centered close-up: the object occupies about 85–90% of the canvas. Add a bright white outline around the outer silhouette and a soft white outer glow fading into full transparency. No text, letters, numbers, frame, scenery, backdrop or cast shadow. Fully transparent background with a true alpha channel. PNG, square 1:1, 1024×1024.
```

## Букет лесных колокольчиков

```text
Fantasy MMORPG inventory icon: a small hand-tied bouquet of delicate blue bellflowers with a few white wildflowers and green leaves, stems wrapped in a twist of rough linen and tied with a thin red ribbon bow. Fresh, modest and heartfelt, cool blue and soft green palette with a warm red accent. + общий хвост
```

## Медовый пряник

```text
Fantasy MMORPG inventory icon: a round golden-brown honey gingerbread cake decorated with white sugar icing patterns — swirls and a small heart in the center — and a few glossy drops of honey, resting on a tiny square of parchment. Warm amber, caramel and cream palette, cozy festive feeling. + общий хвост
```

## Фонарь светлячков

```text
Fantasy MMORPG inventory icon: an antique glass lantern with a dark bronze frame and a ring handle, inside it several tiny glowing yellow-green fireflies swirling and lighting the glass from within, a few sparks of light escaping through the top vents. Deep bronze contrasted with soft magical yellow-green glow. + общий хвост
```

## Хрустальная роза

```text
Fantasy MMORPG inventory icon: a single long-stemmed rose carved entirely from clear rock crystal, translucent petals with pale pink inner glow and icy blue reflections, crystal leaves on the stem, tiny star-shaped glints on the edges. Elegant, precious, eternal. Cool crystal palette with a warm pink heart. + общий хвост
```

## Звезда Вечности

```text
Fantasy MMORPG inventory icon: a radiant fragment of a fallen star — a glowing pale-gold and white crystal shard — set inside a small golden ouroboros ring (a dragon biting its own tail) that frames it like a medallion, faint cosmic sparkles and a soft violet aura around the star. The most precious legendary gift: majestic, luminous, mystical. Rich gold, white starlight and violet accents. + общий хвост
```
