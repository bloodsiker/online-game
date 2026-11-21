# Промпты: плейсхолдеры пустых слотов экипировки

Полупрозрачные контурные силуэты (тонкие линии предмета с очень лёгкой подкраской, не сплошная заливка) для пустых ячеек манекена (поверх `public/img/bg/empty_slot.gif`, 60×60). Всего 16 картинок, каждый промпт готов целиком — хвост (стиль линий, прозрачный фон, PNG 1024×1024) уже внутри. Для игры уменьшить до 60×60 и 120×120 (@2x), сохранить как PNG с альфа-каналом.

Пояс и сумка используются в двух слотах каждый (пояс 1/2, сумка 1/2) — одной картинки достаточно. Левая и правая рука — разные картинки.

| № | Слот | Файл |
|---|---|---|
| 1 | Шлем | `helmet.png` |
| 2 | Наплечники | `shoulder.png` |
| 3 | Наручи | `forearm.png` |
| 4 | Левая рука (оружие) | `hand_left.png` |
| 5 | Правая рука (оружие/щит) | `hand_right.png` |
| 6 | Броня | `armor.png` |
| 7 | Поножи | `legging.png` |
| 8 | Кольчуга | `chain_armor.png` |
| 9 | Обувь | `shoes.png` |
| 10 | Пояс | `belt.png` |
| 11 | Сумка | `bag.png` |
| 12 | Кольцо | `ring.png` |
| 13 | Серьга | `earring.png` |
| 14 | Ожерелье | `necklace.png` |
| 15 | Колчан | `quiver.png` |
| 16 | Плащ | `cloak.png` |

## 1. Шлем (`helmet.png`)

```text
Fantasy MMORPG empty equipment slot placeholder icon, outline silhouette: silhouette of a knight helmet with a visor, front view. Style: a ghosted engraved outline of the item, like a faint etched imprint on parchment. Thin, clean, evenly weighted contour lines in dark warm brown (#60462C), drawn semi-transparent at about 50% alpha, trace the outer shape and the main inner details of the item (seams, plates, rim, straps, gem facets). The inside of the shape is only very lightly tinted with the same brown at about 7% opacity, so it reads as a light, mostly empty, see-through outline and not as a solid filled blob. No gradients, no highlights, no gloss, no shadow, no colour other than brown, no text. Centered, occupying about 70% of the canvas with even margins, line width about 14 px at 1024 so the contour stays readable at 60×60 pixels. Fully transparent background with a true alpha channel (the silhouette will be placed over a beige parchment slot at about 35–40% opacity). PNG, square 1:1, 1024×1024.
```

## 2. Наплечники (`shoulder.png`)

```text
Fantasy MMORPG empty equipment slot placeholder icon, outline silhouette: silhouette of a single pauldron (shoulder armor plate) with a layered rounded plate shape, three-quarter view. Style: a ghosted engraved outline of the item, like a faint etched imprint on parchment. Thin, clean, evenly weighted contour lines in dark warm brown (#60462C), drawn semi-transparent at about 50% alpha, trace the outer shape and the main inner details of the item (seams, plates, rim, straps, gem facets). The inside of the shape is only very lightly tinted with the same brown at about 7% opacity, so it reads as a light, mostly empty, see-through outline and not as a solid filled blob. No gradients, no highlights, no gloss, no shadow, no colour other than brown, no text. Centered, occupying about 70% of the canvas with even margins, line width about 14 px at 1024 so the contour stays readable at 60×60 pixels. Fully transparent background with a true alpha channel (the silhouette will be placed over a beige parchment slot at about 35–40% opacity). PNG, square 1:1, 1024×1024.
```

## 3. Наручи (`forearm.png`)

```text
Fantasy MMORPG empty equipment slot placeholder icon, outline silhouette: silhouette of a single bracer (forearm guard) with a slightly tapered cuff shape, lying diagonally. Style: a ghosted engraved outline of the item, like a faint etched imprint on parchment. Thin, clean, evenly weighted contour lines in dark warm brown (#60462C), drawn semi-transparent at about 50% alpha, trace the outer shape and the main inner details of the item (seams, plates, rim, straps, gem facets). The inside of the shape is only very lightly tinted with the same brown at about 7% opacity, so it reads as a light, mostly empty, see-through outline and not as a solid filled blob. No gradients, no highlights, no gloss, no shadow, no colour other than brown, no text. Centered, occupying about 70% of the canvas with even margins, line width about 14 px at 1024 so the contour stays readable at 60×60 pixels. Fully transparent background with a true alpha channel (the silhouette will be placed over a beige parchment slot at about 35–40% opacity). PNG, square 1:1, 1024×1024.
```

## 4. Левая рука (оружие) (`hand_left.png`)

```text
Fantasy MMORPG empty equipment slot placeholder icon, outline silhouette: silhouette of a straight one-handed sword pointing upward, with cross guard and pommel. Style: a ghosted engraved outline of the item, like a faint etched imprint on parchment. Thin, clean, evenly weighted contour lines in dark warm brown (#60462C), drawn semi-transparent at about 50% alpha, trace the outer shape and the main inner details of the item (seams, plates, rim, straps, gem facets). The inside of the shape is only very lightly tinted with the same brown at about 7% opacity, so it reads as a light, mostly empty, see-through outline and not as a solid filled blob. No gradients, no highlights, no gloss, no shadow, no colour other than brown, no text. Centered, occupying about 70% of the canvas with even margins, line width about 14 px at 1024 so the contour stays readable at 60×60 pixels. Fully transparent background with a true alpha channel (the silhouette will be placed over a beige parchment slot at about 35–40% opacity). PNG, square 1:1, 1024×1024.
```

## 5. Правая рука (оружие/щит) (`hand_right.png`)

```text
Fantasy MMORPG empty equipment slot placeholder icon, outline silhouette: silhouette of a round heater-style shield with a central boss, front view. Style: a ghosted engraved outline of the item, like a faint etched imprint on parchment. Thin, clean, evenly weighted contour lines in dark warm brown (#60462C), drawn semi-transparent at about 50% alpha, trace the outer shape and the main inner details of the item (seams, plates, rim, straps, gem facets). The inside of the shape is only very lightly tinted with the same brown at about 7% opacity, so it reads as a light, mostly empty, see-through outline and not as a solid filled blob. No gradients, no highlights, no gloss, no shadow, no colour other than brown, no text. Centered, occupying about 70% of the canvas with even margins, line width about 14 px at 1024 so the contour stays readable at 60×60 pixels. Fully transparent background with a true alpha channel (the silhouette will be placed over a beige parchment slot at about 35–40% opacity). PNG, square 1:1, 1024×1024.
```

## 6. Броня (`armor.png`)

```text
Fantasy MMORPG empty equipment slot placeholder icon, outline silhouette: silhouette of a chest plate cuirass with shoulder openings, front view. Style: a ghosted engraved outline of the item, like a faint etched imprint on parchment. Thin, clean, evenly weighted contour lines in dark warm brown (#60462C), drawn semi-transparent at about 50% alpha, trace the outer shape and the main inner details of the item (seams, plates, rim, straps, gem facets). The inside of the shape is only very lightly tinted with the same brown at about 7% opacity, so it reads as a light, mostly empty, see-through outline and not as a solid filled blob. No gradients, no highlights, no gloss, no shadow, no colour other than brown, no text. Centered, occupying about 70% of the canvas with even margins, line width about 14 px at 1024 so the contour stays readable at 60×60 pixels. Fully transparent background with a true alpha channel (the silhouette will be placed over a beige parchment slot at about 35–40% opacity). PNG, square 1:1, 1024×1024.
```

## 7. Поножи (`legging.png`)

```text
Fantasy MMORPG empty equipment slot placeholder icon, outline silhouette: silhouette of a pair of armored trousers/greaves, front view, two legs slightly apart. Style: a ghosted engraved outline of the item, like a faint etched imprint on parchment. Thin, clean, evenly weighted contour lines in dark warm brown (#60462C), drawn semi-transparent at about 50% alpha, trace the outer shape and the main inner details of the item (seams, plates, rim, straps, gem facets). The inside of the shape is only very lightly tinted with the same brown at about 7% opacity, so it reads as a light, mostly empty, see-through outline and not as a solid filled blob. No gradients, no highlights, no gloss, no shadow, no colour other than brown, no text. Centered, occupying about 70% of the canvas with even margins, line width about 14 px at 1024 so the contour stays readable at 60×60 pixels. Fully transparent background with a true alpha channel (the silhouette will be placed over a beige parchment slot at about 35–40% opacity). PNG, square 1:1, 1024×1024.
```

## 8. Кольчуга (`chain_armor.png`)

```text
Fantasy MMORPG empty equipment slot placeholder icon, outline silhouette: silhouette of a mail shirt (chainmail hauberk) with short sleeves, front view, with a subtle ring-pattern cut-out texture along the hem only. Style: a ghosted engraved outline of the item, like a faint etched imprint on parchment. Thin, clean, evenly weighted contour lines in dark warm brown (#60462C), drawn semi-transparent at about 50% alpha, trace the outer shape and the main inner details of the item (seams, plates, rim, straps, gem facets). The inside of the shape is only very lightly tinted with the same brown at about 7% opacity, so it reads as a light, mostly empty, see-through outline and not as a solid filled blob. No gradients, no highlights, no gloss, no shadow, no colour other than brown, no text. Centered, occupying about 70% of the canvas with even margins, line width about 14 px at 1024 so the contour stays readable at 60×60 pixels. Fully transparent background with a true alpha channel (the silhouette will be placed over a beige parchment slot at about 35–40% opacity). PNG, square 1:1, 1024×1024.
```

## 9. Обувь (`shoes.png`)

```text
Fantasy MMORPG empty equipment slot placeholder icon, outline silhouette: silhouette of a single sturdy leather boot in side view, toe pointing left. Style: a ghosted engraved outline of the item, like a faint etched imprint on parchment. Thin, clean, evenly weighted contour lines in dark warm brown (#60462C), drawn semi-transparent at about 50% alpha, trace the outer shape and the main inner details of the item (seams, plates, rim, straps, gem facets). The inside of the shape is only very lightly tinted with the same brown at about 7% opacity, so it reads as a light, mostly empty, see-through outline and not as a solid filled blob. No gradients, no highlights, no gloss, no shadow, no colour other than brown, no text. Centered, occupying about 70% of the canvas with even margins, line width about 14 px at 1024 so the contour stays readable at 60×60 pixels. Fully transparent background with a true alpha channel (the silhouette will be placed over a beige parchment slot at about 35–40% opacity). PNG, square 1:1, 1024×1024.
```

## 10. Пояс (`belt.png`)

```text
Fantasy MMORPG empty equipment slot placeholder icon, outline silhouette: silhouette of a leather belt with a square buckle, coiled in a loose horizontal loop. Style: a ghosted engraved outline of the item, like a faint etched imprint on parchment. Thin, clean, evenly weighted contour lines in dark warm brown (#60462C), drawn semi-transparent at about 50% alpha, trace the outer shape and the main inner details of the item (seams, plates, rim, straps, gem facets). The inside of the shape is only very lightly tinted with the same brown at about 7% opacity, so it reads as a light, mostly empty, see-through outline and not as a solid filled blob. No gradients, no highlights, no gloss, no shadow, no colour other than brown, no text. Centered, occupying about 70% of the canvas with even margins, line width about 14 px at 1024 so the contour stays readable at 60×60 pixels. Fully transparent background with a true alpha channel (the silhouette will be placed over a beige parchment slot at about 35–40% opacity). PNG, square 1:1, 1024×1024.
```

## 11. Сумка (`bag.png`)

```text
Fantasy MMORPG empty equipment slot placeholder icon, outline silhouette: silhouette of a small leather satchel bag with a flap and a buckle, front view. Style: a ghosted engraved outline of the item, like a faint etched imprint on parchment. Thin, clean, evenly weighted contour lines in dark warm brown (#60462C), drawn semi-transparent at about 50% alpha, trace the outer shape and the main inner details of the item (seams, plates, rim, straps, gem facets). The inside of the shape is only very lightly tinted with the same brown at about 7% opacity, so it reads as a light, mostly empty, see-through outline and not as a solid filled blob. No gradients, no highlights, no gloss, no shadow, no colour other than brown, no text. Centered, occupying about 70% of the canvas with even margins, line width about 14 px at 1024 so the contour stays readable at 60×60 pixels. Fully transparent background with a true alpha channel (the silhouette will be placed over a beige parchment slot at about 35–40% opacity). PNG, square 1:1, 1024×1024.
```

## 12. Кольцо (`ring.png`)

```text
Fantasy MMORPG empty equipment slot placeholder icon, outline silhouette: silhouette of a ring with a raised diamond-shaped gem on top, front view. Style: a ghosted engraved outline of the item, like a faint etched imprint on parchment. Thin, clean, evenly weighted contour lines in dark warm brown (#60462C), drawn semi-transparent at about 50% alpha, trace the outer shape and the main inner details of the item (seams, plates, rim, straps, gem facets). The inside of the shape is only very lightly tinted with the same brown at about 7% opacity, so it reads as a light, mostly empty, see-through outline and not as a solid filled blob. No gradients, no highlights, no gloss, no shadow, no colour other than brown, no text. Centered, occupying about 70% of the canvas with even margins, line width about 14 px at 1024 so the contour stays readable at 60×60 pixels. Fully transparent background with a true alpha channel (the silhouette will be placed over a beige parchment slot at about 35–40% opacity). PNG, square 1:1, 1024×1024.
```

## 13. Серьга (`earring.png`)

```text
Fantasy MMORPG empty equipment slot placeholder icon, outline silhouette: silhouette of a single drop earring on a curved hook with a diamond-shaped pendant. Style: a ghosted engraved outline of the item, like a faint etched imprint on parchment. Thin, clean, evenly weighted contour lines in dark warm brown (#60462C), drawn semi-transparent at about 50% alpha, trace the outer shape and the main inner details of the item (seams, plates, rim, straps, gem facets). The inside of the shape is only very lightly tinted with the same brown at about 7% opacity, so it reads as a light, mostly empty, see-through outline and not as a solid filled blob. No gradients, no highlights, no gloss, no shadow, no colour other than brown, no text. Centered, occupying about 70% of the canvas with even margins, line width about 14 px at 1024 so the contour stays readable at 60×60 pixels. Fully transparent background with a true alpha channel (the silhouette will be placed over a beige parchment slot at about 35–40% opacity). PNG, square 1:1, 1024×1024.
```

## 14. Ожерелье (`necklace.png`)

```text
Fantasy MMORPG empty equipment slot placeholder icon, outline silhouette: silhouette of a necklace: a curved chain with a diamond-shaped pendant at the bottom. Style: a ghosted engraved outline of the item, like a faint etched imprint on parchment. Thin, clean, evenly weighted contour lines in dark warm brown (#60462C), drawn semi-transparent at about 50% alpha, trace the outer shape and the main inner details of the item (seams, plates, rim, straps, gem facets). The inside of the shape is only very lightly tinted with the same brown at about 7% opacity, so it reads as a light, mostly empty, see-through outline and not as a solid filled blob. No gradients, no highlights, no gloss, no shadow, no colour other than brown, no text. Centered, occupying about 70% of the canvas with even margins, line width about 14 px at 1024 so the contour stays readable at 60×60 pixels. Fully transparent background with a true alpha channel (the silhouette will be placed over a beige parchment slot at about 35–40% opacity). PNG, square 1:1, 1024×1024.
```

## 15. Колчан (`quiver.png`)

```text
Fantasy MMORPG empty equipment slot placeholder icon, outline silhouette: silhouette of a leather quiver tilted slightly, with three arrows with fletching sticking out of the top. Style: a ghosted engraved outline of the item, like a faint etched imprint on parchment. Thin, clean, evenly weighted contour lines in dark warm brown (#60462C), drawn semi-transparent at about 50% alpha, trace the outer shape and the main inner details of the item (seams, plates, rim, straps, gem facets). The inside of the shape is only very lightly tinted with the same brown at about 7% opacity, so it reads as a light, mostly empty, see-through outline and not as a solid filled blob. No gradients, no highlights, no gloss, no shadow, no colour other than brown, no text. Centered, occupying about 70% of the canvas with even margins, line width about 14 px at 1024 so the contour stays readable at 60×60 pixels. Fully transparent background with a true alpha channel (the silhouette will be placed over a beige parchment slot at about 35–40% opacity). PNG, square 1:1, 1024×1024.
```

## 16. Плащ (`cloak.png`)

```text
Fantasy MMORPG empty equipment slot placeholder icon, outline silhouette: silhouette of a long hooded cloak hanging straight down, front view, with the hood raised at the top, a round clasp joining the two edges at the collar and soft vertical folds flowing to a slightly flared hem. Style: a ghosted engraved outline of the item, like a faint etched imprint on parchment. Thin, clean, evenly weighted contour lines in dark warm brown (#60462C), drawn semi-transparent at about 50% alpha, trace the outer shape and the main inner details of the item (seams, plates, rim, straps, gem facets). The inside of the shape is only very lightly tinted with the same brown at about 7% opacity, so it reads as a light, mostly empty, see-through outline and not as a solid filled blob. No gradients, no highlights, no gloss, no shadow, no colour other than brown, no text. Centered, occupying about 70% of the canvas with even margins, line width about 14 px at 1024 so the contour stays readable at 60×60 pixels. Fully transparent background with a true alpha channel (the silhouette will be placed over a beige parchment slot at about 35–40% opacity). PNG, square 1:1, 1024×1024.
```
