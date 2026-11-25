# Промпты для иконок: «Тайные знания» (ворох, пособия о боссах, части боссов)

Предметы модуля `SecretKnowledge` (11 шт.), пока на временных картинках. Загружайте в админке
(Предметы → предмет → изображение). Каждый промпт готов целиком, копируется блоком.

Стиль — общий house-style инвентарных иконок:

- **Пособия** — как книги из `grimoire-book-prompts.md`: закрытый том, на обложке символ босса.
  Пособия **не окрашиваются в цвет редкости** (никакого зелёного/синего/фиолетового/оранжевого свечения):
  фон редкости игра накладывает сама, у книги остаются только естественные цвета материалов.
- **Ворох и части боссов** — как трофеи из `hunters-order-trophy-prompts.md`: один предмет крупно,
  белая обводка + угасающее свечение, прозрачный фон.

| id | Предмет | Тип | Редкость |
|---|---|---|---|
| 2368 | Ворох тайных знаний | Квест | Необычный |
| 2370 | Пособие: Крысиный вожак | Пособие о боссе | Необычный |
| 2371 | Хвост Крысиного вожака | Квест | Необычный |
| 2372 | Пособие: Чрево Кургана | Пособие о боссе | Редкий |
| 2373 | Утробный камень Кургана | Квест | Редкий |
| 2374 | Пособие: Костяной Скорпион | Пособие о боссе | Редкий |
| 2375 | Костяное жало Скорпиона | Квест | Редкий |
| 2376 | Пособие: Азазель | Пособие о боссе | Эпический |
| 2377 | Обломанный рог Азазеля | Квест | Эпический |
| 2378 | Пособие: Кровавый Хранитель Склепа | Пособие о боссе | Легендарный |
| 2379 | Кровавое сердце Хранителя Склепа | Квест | Легендарный |

> id указаны по локальной базе; на проде после переноса они будут другими — ищите по названию.

## Ворох тайных знаний

### 1. Ворох тайных знаний (2368)

```text
Fantasy MMORPG inventory icon, quest item: a loose bundle of old weathered parchment sheets, torn map scraps and scribbled notes stacked unevenly and tied crosswise with a frayed hemp cord, a broken red wax seal on top, a few pages curling at the corners, faint pale-blue arcane symbols glowing in the margins as if the writing hides a secret. Classic hand-painted fantasy MMORPG inventory art, crisp contours, soft dimensional shading, strong silhouette readable at 60×60 pixels. Large centered close-up: the item occupies about 85–90% of the canvas, shown alone. Add a bright white outline around the outer silhouette and a soft white outer glow fading into full transparency. No text, no frame, no scenery, no background objects, no shadow. Fully transparent background with a true alpha channel. PNG, square 1:1, 1024×1024.
```

## Пособия о боссах

Общий хвост у всех пяти одинаковый (как у гримуаров), меняется только первый абзац.

### 2. Пособие: Крысиный вожак (2370)

```text
Stylized 2D fantasy MMORPG inventory icon, a closed worn field manual bound in grimy brown leather with scratched and chewed corners, its cover branded with a snarling rat skull emblem in tarnished brass, a long scarred rat tail tucked between the pages as a bookmark, a simple iron clasp, yellowed parchment page edges, natural material colors only, bold illustrated shapes. Prioritize a striking loot-icon silhouette over physically accurate bookbinding.

Hand-painted 2D game asset in the style of classic browser fantasy MMORPG inventory icons. Bold illustrated contours, exaggerated proportions, saturated colors, broad painted light-and-shadow shapes, and a few sharp graphic highlights. Suggest volume through illustration, not realistic materials or physically accurate lighting. Simplified ornamentation, no tiny engraving, no realistic material refraction, no surface microtexture. Serious heroic fantasy, not cute or chibi. No photography, jewelry advertising, photorealism, 3D rendering, ray tracing, depth of field, or cinematic scene. Nearly frontal view, centered upright composition, instantly readable at 60×60 pixels. The book fills approximately 85-90% of the canvas along its longest dimension, with the entire item visible without cropping.

IMPORTANT: the book MUST be surrounded by a thick, unmistakable bright white glowing outline — a solid bright white rim light tracing the entire outer silhouette, then softly fading outward into a white halo. This white glow is a mandatory, highly visible design element, not a subtle effect. Preserve the book's own colors inside the outline; only the glow around the edge is white. Do NOT tint the book or its glow in any rarity color (no green, blue, purple, orange or gold aura) — the game adds the rarity background itself; keep only the natural colors of leather, stone, bone, metal and parchment. Fully transparent background with a true alpha channel (everything outside the glow is pure transparent alpha, not white) — no colored background, no checkerboard pattern, no ground plane, no cast shadow, no text, no watermark, and no external frame. PNG format, 1024×1024 pixels, 1:1 aspect ratio.
```

### 3. Пособие: Чрево Кургана (2372)

```text
Stylized 2D fantasy MMORPG inventory icon, a closed heavy tome with a cover of cracked grey stone slabs crusted with dry earth and moss, the cover carved with a gaping toothed maw sunk into a burial mound, small bone fragments wedged into the binding, thick bronze corner caps, yellowed parchment page edges, natural material colors only, bold illustrated shapes. Prioritize a striking loot-icon silhouette over physically accurate bookbinding.

Hand-painted 2D game asset in the style of classic browser fantasy MMORPG inventory icons. Bold illustrated contours, exaggerated proportions, saturated colors, broad painted light-and-shadow shapes, and a few sharp graphic highlights. Suggest volume through illustration, not realistic materials or physically accurate lighting. Simplified ornamentation, no tiny engraving, no realistic material refraction, no surface microtexture. Serious heroic fantasy, not cute or chibi. No photography, jewelry advertising, photorealism, 3D rendering, ray tracing, depth of field, or cinematic scene. Nearly frontal view, centered upright composition, instantly readable at 60×60 pixels. The book fills approximately 85-90% of the canvas along its longest dimension, with the entire item visible without cropping.

IMPORTANT: the book MUST be surrounded by a thick, unmistakable bright white glowing outline — a solid bright white rim light tracing the entire outer silhouette, then softly fading outward into a white halo. This white glow is a mandatory, highly visible design element, not a subtle effect. Preserve the book's own colors inside the outline; only the glow around the edge is white. Do NOT tint the book or its glow in any rarity color (no green, blue, purple, orange or gold aura) — the game adds the rarity background itself; keep only the natural colors of leather, stone, bone, metal and parchment. Fully transparent background with a true alpha channel (everything outside the glow is pure transparent alpha, not white) — no colored background, no checkerboard pattern, no ground plane, no cast shadow, no text, no watermark, and no external frame. PNG format, 1024×1024 pixels, 1:1 aspect ratio.
```

### 4. Пособие: Костяной Скорпион (2374)

```text
Stylized 2D fantasy MMORPG inventory icon, a closed tome bound in overlapping pale ivory bone plates like a scorpion's carapace, the cover embossed with a skeletal scorpion with its tail raised, a curved bone stinger forming the clasp, dark brownish venom stains dripping down the spine, yellowed parchment page edges, natural material colors only, bold illustrated shapes. Prioritize a striking loot-icon silhouette over physically accurate bookbinding.

Hand-painted 2D game asset in the style of classic browser fantasy MMORPG inventory icons. Bold illustrated contours, exaggerated proportions, saturated colors, broad painted light-and-shadow shapes, and a few sharp graphic highlights. Suggest volume through illustration, not realistic materials or physically accurate lighting. Simplified ornamentation, no tiny engraving, no realistic material refraction, no surface microtexture. Serious heroic fantasy, not cute or chibi. No photography, jewelry advertising, photorealism, 3D rendering, ray tracing, depth of field, or cinematic scene. Nearly frontal view, centered upright composition, instantly readable at 60×60 pixels. The book fills approximately 85-90% of the canvas along its longest dimension, with the entire item visible without cropping.

IMPORTANT: the book MUST be surrounded by a thick, unmistakable bright white glowing outline — a solid bright white rim light tracing the entire outer silhouette, then softly fading outward into a white halo. This white glow is a mandatory, highly visible design element, not a subtle effect. Preserve the book's own colors inside the outline; only the glow around the edge is white. Do NOT tint the book or its glow in any rarity color (no green, blue, purple, orange or gold aura) — the game adds the rarity background itself; keep only the natural colors of leather, stone, bone, metal and parchment. Fully transparent background with a true alpha channel (everything outside the glow is pure transparent alpha, not white) — no colored background, no checkerboard pattern, no ground plane, no cast shadow, no text, no watermark, and no external frame. PNG format, 1024×1024 pixels, 1:1 aspect ratio.
```

### 5. Пособие: Азазель (2376)

```text
Stylized 2D fantasy MMORPG inventory icon, a closed demonic grimoire bound in charred black leather with smouldering edges, the cover branded with a burnt-in horned demon sigil with faint smouldering ember cracks, wrapped shut by two crossing iron chains with a heavy padlock, small curved horn spikes on the corners, a few thin grey smoke wisps, natural material colors only, bold illustrated shapes. Prioritize a striking loot-icon silhouette over physically accurate bookbinding.

Hand-painted 2D game asset in the style of classic browser fantasy MMORPG inventory icons. Bold illustrated contours, exaggerated proportions, saturated colors, broad painted light-and-shadow shapes, and a few sharp graphic highlights. Suggest volume through illustration, not realistic materials or physically accurate lighting. Simplified ornamentation, no tiny engraving, no realistic material refraction, no surface microtexture. Serious heroic fantasy, not cute or chibi. No photography, jewelry advertising, photorealism, 3D rendering, ray tracing, depth of field, or cinematic scene. Nearly frontal view, centered upright composition, instantly readable at 60×60 pixels. The book fills approximately 85-90% of the canvas along its longest dimension, with the entire item visible without cropping.

IMPORTANT: the book MUST be surrounded by a thick, unmistakable bright white glowing outline — a solid bright white rim light tracing the entire outer silhouette, then softly fading outward into a white halo. This white glow is a mandatory, highly visible design element, not a subtle effect. Preserve the book's own colors inside the outline; only the glow around the edge is white. Do NOT tint the book or its glow in any rarity color (no green, blue, purple, orange or gold aura) — the game adds the rarity background itself; keep only the natural colors of leather, stone, bone, metal and parchment. Fully transparent background with a true alpha channel (everything outside the glow is pure transparent alpha, not white) — no colored background, no checkerboard pattern, no ground plane, no cast shadow, no text, no watermark, and no external frame. PNG format, 1024×1024 pixels, 1:1 aspect ratio.
```

### 6. Пособие: Кровавый Хранитель Склепа (2378)

```text
Stylized 2D fantasy MMORPG inventory icon, a closed ancient tome bound in deep crimson leather with ornate dark-gold corner guards, the cover adorned with a gothic crypt-gate emblem framing a large dark blood-red gemstone, thin trickles of dark blood running along the spine, yellowed parchment page edges, natural material colors only, bold illustrated shapes. Prioritize a striking loot-icon silhouette over physically accurate bookbinding.

Hand-painted 2D game asset in the style of classic browser fantasy MMORPG inventory icons. Bold illustrated contours, exaggerated proportions, saturated colors, broad painted light-and-shadow shapes, and a few sharp graphic highlights. Suggest volume through illustration, not realistic materials or physically accurate lighting. Simplified ornamentation, no tiny engraving, no realistic material refraction, no surface microtexture. Serious heroic fantasy, not cute or chibi. No photography, jewelry advertising, photorealism, 3D rendering, ray tracing, depth of field, or cinematic scene. Nearly frontal view, centered upright composition, instantly readable at 60×60 pixels. The book fills approximately 85-90% of the canvas along its longest dimension, with the entire item visible without cropping.

IMPORTANT: the book MUST be surrounded by a thick, unmistakable bright white glowing outline — a solid bright white rim light tracing the entire outer silhouette, then softly fading outward into a white halo. This white glow is a mandatory, highly visible design element, not a subtle effect. Preserve the book's own colors inside the outline; only the glow around the edge is white. Do NOT tint the book or its glow in any rarity color (no green, blue, purple, orange or gold aura) — the game adds the rarity background itself; keep only the natural colors of leather, stone, bone, metal and parchment. Fully transparent background with a true alpha channel (everything outside the glow is pure transparent alpha, not white) — no colored background, no checkerboard pattern, no ground plane, no cast shadow, no text, no watermark, and no external frame. PNG format, 1024×1024 pixels, 1:1 aspect ratio.
```

## Части боссов

### 7. Хвост Крысиного вожака (2371)

```text
Fantasy MMORPG inventory icon, quest trophy item: one long thick scarred rat tail curled into a loose S-shape, leathery pinkish-grey skin with ring-like segments and old bite scars, a ragged tuft of matted grey-brown fur at the severed base, a small rusty iron ring clamped near the base as a mark of the pack leader. Classic hand-painted fantasy MMORPG inventory art, crisp contours, soft dimensional shading, strong silhouette readable at 60×60 pixels. Large centered close-up: the item occupies about 85–90% of the canvas, shown alone. Add a bright white outline around the outer silhouette and a soft white outer glow fading into full transparency. No text, no frame, no scenery, no background objects, no shadow. Fully transparent background with a true alpha channel. PNG, square 1:1, 1024×1024.
```

### 8. Утробный камень Кургана (2373)

```text
Fantasy MMORPG inventory icon, quest trophy item: one heavy rounded petrified stomach stone, smooth polished grey-brown surface with small bone fragments and a broken tooth embedded in it, cracked through in places with sickly murky-green light glowing from inside the cracks. Classic hand-painted fantasy MMORPG inventory art, crisp contours, soft dimensional shading, strong silhouette readable at 60×60 pixels. Large centered close-up: the item occupies about 85–90% of the canvas, shown alone. Add a bright white outline around the outer silhouette and a soft white outer glow fading into full transparency. No text, no frame, no scenery, no background objects, no shadow. Fully transparent background with a true alpha channel. PNG, square 1:1, 1024×1024.
```

### 9. Костяное жало Скорпиона (2375)

```text
Fantasy MMORPG inventory icon, quest trophy item: one large curved scorpion stinger made of pale ivory bone with two jointed tail segments still attached, fine cracks along the surface, a single glistening dark violet drop of venom hanging from the needle-sharp tip, lying diagonally. Classic hand-painted fantasy MMORPG inventory art, crisp contours, soft dimensional shading, strong silhouette readable at 60×60 pixels. Large centered close-up: the item occupies about 85–90% of the canvas, shown alone. Add a bright white outline around the outer silhouette and a soft white outer glow fading into full transparency. No text, no frame, no scenery, no background objects, no shadow. Fully transparent background with a true alpha channel. PNG, square 1:1, 1024×1024.
```

### 10. Обломанный рог Азазеля (2377)

```text
Fantasy MMORPG inventory icon, quest trophy item: one broken curved demon horn, ridged glossy black surface, jagged snapped end with molten ember-orange cracks glowing through it, thin wisps of smoke and a few ember sparks rising from the break, lying diagonally. Classic hand-painted fantasy MMORPG inventory art, crisp contours, soft dimensional shading, strong silhouette readable at 60×60 pixels. Large centered close-up: the item occupies about 85–90% of the canvas, shown alone. Add a bright white outline around the outer silhouette and a soft white outer glow fading into full transparency. No text, no frame, no scenery, no background objects, no shadow. Fully transparent background with a true alpha channel. PNG, square 1:1, 1024×1024.
```

### 11. Кровавое сердце Хранителя Склепа (2379)

```text
Fantasy MMORPG inventory icon, quest trophy item: one dark crimson heart still faintly pulsing, wrapped in thin black veins and dried blood, pale grey crypt dust on its surface, a deep red light glowing from within, a broken iron crypt chain link tangled around one of its vessels. Classic hand-painted fantasy MMORPG inventory art, crisp contours, soft dimensional shading, strong silhouette readable at 60×60 pixels. Large centered close-up: the item occupies about 85–90% of the canvas, shown alone. Add a bright white outline around the outer silhouette and a soft white outer glow fading into full transparency. No text, no frame, no scenery, no background objects, no shadow. Fully transparent background with a true alpha channel. PNG, square 1:1, 1024×1024.
```
