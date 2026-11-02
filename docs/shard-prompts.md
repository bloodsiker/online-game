# Промпты для картинок осколков

Стиль — тот же установленный house-style, что и у остальных предметных
иконок игры (см. `premium-artifact-pendant-prompts.md`, `common-artifact-fourth-slot-prompts.md`):
«Stylized 2D fantasy MMORPG inventory icon» + фирменный абзац про bold
illustrated contours. Это материал, не амулет — без оправы/цепочки/крыльев,
просто сам осколок. Прозрачный фон.

Общий шаблон:

```text
Stylized 2D fantasy MMORPG inventory icon, "[NAME]," a magical crystal shard. [SHARD_DESCRIPTION]. Prioritize a striking loot-icon silhouette over physically accurate crystal construction.

Hand-painted 2D game asset in the style of classic browser fantasy MMORPG inventory icons. Bold illustrated contours, exaggerated proportions, saturated colors, broad painted light-and-shadow shapes, and a few sharp graphic highlights. Suggest volume through illustration, not realistic materials or physically accurate lighting. Simplified ornamentation, no tiny engraving, no realistic material refraction, no surface microtexture. Serious heroic fantasy, not cute or chibi. No photography, jewelry advertising, photorealism, 3D rendering, ray tracing, depth of field, or cinematic scene. Nearly frontal view, centered upright composition, instantly readable at 60×60 pixels. The shard itself fills approximately 85-90% of the canvas along its longest dimension, with the entire item visible without cropping.

IMPORTANT: the shard MUST be surrounded by a thick, unmistakable bright white glowing outline — a solid bright white rim light tracing the entire outer silhouette of the shard, then softly fading outward into a white halo. This white glow is a mandatory, highly visible design element, not a subtle effect — render it clearly, as prominent as the shard itself. Preserve the shard's own colors inside the outline; only the glow around the edge is white. Fully transparent background with a true alpha channel (everything outside the glow is pure transparent alpha, not white) — no colored background, no checkerboard pattern, no ground plane, no cast shadow, no text, no watermark, and no external frame. PNG format, 1024×1024 pixels, 1:1 aspect ratio.
```

Ниже — 4 common-осколка (Эха/Забвения/Истока/Безмолвия, форма — рваный
неровный кристалл-обломок), следом 5 uncommon-осколков (Грёзы/Угасания/
Предела/Полумрака/Клятвы) — единая форма: вытянутый гранёный шестигранный
кристалл-шип, и следом 6 rare-осколков (Истины/Жертвы/Покоя/Испытания/
Отражения/Надежды, см.
`database/migrations/2026_10_23_000001_seed_rare_artifact_shards.php`) —
третья форма, ещё более хаотичная, чем у common: не один обломок, а
хаотичный кластер из нескольких острых шипов-отростков, торчащих в разные
стороны от неровного ядра, непредсказуемый асимметричный силуэт.

## Готовые промпты

### Осколок Эха

```text
Stylized 2D fantasy MMORPG inventory icon, "Shard of Echo," a magical crystal shard. A jagged pale translucent glassy shard with bold cyan-white rippling lines painted through it like frozen sound waves, a soft pale-blue glow radiating from its core. Prioritize a striking loot-icon silhouette over physically accurate crystal construction.

Hand-painted 2D game asset in the style of classic browser fantasy MMORPG inventory icons. Bold illustrated contours, exaggerated proportions, saturated colors, broad painted light-and-shadow shapes, and a few sharp graphic highlights. Suggest volume through illustration, not realistic materials or physically accurate lighting. Simplified ornamentation, no tiny engraving, no realistic material refraction, no surface microtexture. Serious heroic fantasy, not cute or chibi. No photography, jewelry advertising, photorealism, 3D rendering, ray tracing, depth of field, or cinematic scene. Nearly frontal view, centered upright composition, instantly readable at 60×60 pixels. The shard itself fills approximately 85-90% of the canvas along its longest dimension, with the entire item visible without cropping.

IMPORTANT: the shard MUST be surrounded by a thick, unmistakable bright white glowing outline — a solid bright white rim light tracing the entire outer silhouette of the shard, then softly fading outward into a white halo. This white glow is a mandatory, highly visible design element, not a subtle effect — render it clearly, as prominent as the shard itself. Preserve the shard's own colors inside the outline; only the glow around the edge is white. Fully transparent background with a true alpha channel (everything outside the glow is pure transparent alpha, not white) — no colored background, no checkerboard pattern, no ground plane, no cast shadow, no text, no watermark, and no external frame. PNG format, 1024×1024 pixels, 1:1 aspect ratio.
```

### Осколок Забвения

```text
Stylized 2D fantasy MMORPG inventory icon, "Shard of Oblivion," a magical crystal shard. A jagged translucent deep-red shard glowing softly from within like a dying ember, bold flowing vein-like patterns painted through it resembling dried blood. Prioritize a striking loot-icon silhouette over physically accurate crystal construction.

Hand-painted 2D game asset in the style of classic browser fantasy MMORPG inventory icons. Bold illustrated contours, exaggerated proportions, saturated colors, broad painted light-and-shadow shapes, and a few sharp graphic highlights. Suggest volume through illustration, not realistic materials or physically accurate lighting. Simplified ornamentation, no tiny engraving, no realistic material refraction, no surface microtexture. Serious heroic fantasy, not cute or chibi. No photography, jewelry advertising, photorealism, 3D rendering, ray tracing, depth of field, or cinematic scene. Nearly frontal view, centered upright composition, instantly readable at 60×60 pixels. The shard itself fills approximately 85-90% of the canvas along its longest dimension, with the entire item visible without cropping.

IMPORTANT: the shard MUST be surrounded by a thick, unmistakable bright white glowing outline — a solid bright white rim light tracing the entire outer silhouette of the shard, then softly fading outward into a white halo. This white glow is a mandatory, highly visible design element, not a subtle effect — render it clearly, as prominent as the shard itself. Preserve the shard's own colors inside the outline; only the glow around the edge is white. Fully transparent background with a true alpha channel (everything outside the glow is pure transparent alpha, not white) — no colored background, no checkerboard pattern, no ground plane, no cast shadow, no text, no watermark, and no external frame. PNG format, 1024×1024 pixels, 1:1 aspect ratio.
```

### Осколок Истока

```text
Stylized 2D fantasy MMORPG inventory icon, "Shard of Origin," a magical crystal shard. A jagged warm translucent amber-white shard glowing softly from within like captured sunlight, bold flowing vein-like patterns painted through it resembling still water. Prioritize a striking loot-icon silhouette over physically accurate crystal construction.

Hand-painted 2D game asset in the style of classic browser fantasy MMORPG inventory icons. Bold illustrated contours, exaggerated proportions, saturated colors, broad painted light-and-shadow shapes, and a few sharp graphic highlights. Suggest volume through illustration, not realistic materials or physically accurate lighting. Simplified ornamentation, no tiny engraving, no realistic material refraction, no surface microtexture. Serious heroic fantasy, not cute or chibi. No photography, jewelry advertising, photorealism, 3D rendering, ray tracing, depth of field, or cinematic scene. Nearly frontal view, centered upright composition, instantly readable at 60×60 pixels. The shard itself fills approximately 85-90% of the canvas along its longest dimension, with the entire item visible without cropping.

IMPORTANT: the shard MUST be surrounded by a thick, unmistakable bright white glowing outline — a solid bright white rim light tracing the entire outer silhouette of the shard, then softly fading outward into a white halo. This white glow is a mandatory, highly visible design element, not a subtle effect — render it clearly, as prominent as the shard itself. Preserve the shard's own colors inside the outline; only the glow around the edge is white. Fully transparent background with a true alpha channel (everything outside the glow is pure transparent alpha, not white) — no colored background, no checkerboard pattern, no ground plane, no cast shadow, no text, no watermark, and no external frame. PNG format, 1024×1024 pixels, 1:1 aspect ratio.
```

### Осколок Безмолвия

```text
Stylized 2D fantasy MMORPG inventory icon, "Shard of Silence," a magical crystal shard. A jagged deep indigo-violet shard with an unnaturally smooth muffled surface that seems to dampen the light around it, a faint muted glow bleeding softly from its center. Prioritize a striking loot-icon silhouette over physically accurate crystal construction.

Hand-painted 2D game asset in the style of classic browser fantasy MMORPG inventory icons. Bold illustrated contours, exaggerated proportions, saturated colors, broad painted light-and-shadow shapes, and a few sharp graphic highlights. Suggest volume through illustration, not realistic materials or physically accurate lighting. Simplified ornamentation, no tiny engraving, no realistic material refraction, no surface microtexture. Serious heroic fantasy, not cute or chibi. No photography, jewelry advertising, photorealism, 3D rendering, ray tracing, depth of field, or cinematic scene. Nearly frontal view, centered upright composition, instantly readable at 60×60 pixels. The shard itself fills approximately 85-90% of the canvas along its longest dimension, with the entire item visible without cropping.

IMPORTANT: the shard MUST be surrounded by a thick, unmistakable bright white glowing outline — a solid bright white rim light tracing the entire outer silhouette of the shard, then softly fading outward into a white halo. This white glow is a mandatory, highly visible design element, not a subtle effect — render it clearly, as prominent as the shard itself. Preserve the shard's own colors inside the outline; only the glow around the edge is white. Fully transparent background with a true alpha channel (everything outside the glow is pure transparent alpha, not white) — no colored background, no checkerboard pattern, no ground plane, no cast shadow, no text, no watermark, and no external frame. PNG format, 1024×1024 pixels, 1:1 aspect ratio.
```

### Осколок Грёзы

```text
Stylized 2D fantasy MMORPG inventory icon, "Shard of Reverie," a magical crystal shard. An elongated, perfectly faceted hexagonal crystal spike, translucent lavender-blue, with soft shifting swirls of dreamlike color drifting inside it, like mist caught mid-motion, a gentle hazy glow pulsing from its core. Prioritize a striking loot-icon silhouette over physically accurate crystal construction.

Hand-painted 2D game asset in the style of classic browser fantasy MMORPG inventory icons. Bold illustrated contours, exaggerated proportions, saturated colors, broad painted light-and-shadow shapes, and a few sharp graphic highlights. Suggest volume through illustration, not realistic materials or physically accurate lighting. Simplified ornamentation, no tiny engraving, no realistic material refraction, no surface microtexture. Serious heroic fantasy, not cute or chibi. No photography, jewelry advertising, photorealism, 3D rendering, ray tracing, depth of field, or cinematic scene. Nearly frontal view, centered upright composition, instantly readable at 60×60 pixels. The shard itself fills approximately 85-90% of the canvas along its longest dimension, with the entire item visible without cropping.

IMPORTANT: the shard MUST be surrounded by a thick, unmistakable bright white glowing outline — a solid bright white rim light tracing the entire outer silhouette of the shard, then softly fading outward into a white halo. This white glow is a mandatory, highly visible design element, not a subtle effect — render it clearly, as prominent as the shard itself. Preserve the shard's own colors inside the outline; only the glow around the edge is white. Fully transparent background with a true alpha channel (everything outside the glow is pure transparent alpha, not white) — no colored background, no checkerboard pattern, no ground plane, no cast shadow, no text, no watermark, and no external frame. PNG format, 1024×1024 pixels, 1:1 aspect ratio.
```

### Осколок Угасания

```text
Stylized 2D fantasy MMORPG inventory icon, "Shard of Fading," a magical crystal shard. An elongated, perfectly faceted hexagonal crystal spike, dim ash-grey, with a faint dying ember-orange glow flickering weakly at its core, like a coal that refuses to go out, its facets slightly worn and matte. Prioritize a striking loot-icon silhouette over physically accurate crystal construction.

Hand-painted 2D game asset in the style of classic browser fantasy MMORPG inventory icons. Bold illustrated contours, exaggerated proportions, saturated colors, broad painted light-and-shadow shapes, and a few sharp graphic highlights. Suggest volume through illustration, not realistic materials or physically accurate lighting. Simplified ornamentation, no tiny engraving, no realistic material refraction, no surface microtexture. Serious heroic fantasy, not cute or chibi. No photography, jewelry advertising, photorealism, 3D rendering, ray tracing, depth of field, or cinematic scene. Nearly frontal view, centered upright composition, instantly readable at 60×60 pixels. The shard itself fills approximately 85-90% of the canvas along its longest dimension, with the entire item visible without cropping.

IMPORTANT: the shard MUST be surrounded by a thick, unmistakable bright white glowing outline — a solid bright white rim light tracing the entire outer silhouette of the shard, then softly fading outward into a white halo. This white glow is a mandatory, highly visible design element, not a subtle effect — render it clearly, as prominent as the shard itself. Preserve the shard's own colors inside the outline; only the glow around the edge is white. Fully transparent background with a true alpha channel (everything outside the glow is pure transparent alpha, not white) — no colored background, no checkerboard pattern, no ground plane, no cast shadow, no text, no watermark, and no external frame. PNG format, 1024×1024 pixels, 1:1 aspect ratio.
```

### Осколок Предела

```text
Stylized 2D fantasy MMORPG inventory icon, "Shard of the Threshold," a magical crystal shard. An elongated, perfectly faceted hexagonal crystal spike, pale frost-white, with one additional impossibly sharp cut running straight across its facets, as if sliced along an invisible boundary, a thin cold blue-white line glowing along that cut. Prioritize a striking loot-icon silhouette over physically accurate crystal construction.

Hand-painted 2D game asset in the style of classic browser fantasy MMORPG inventory icons. Bold illustrated contours, exaggerated proportions, saturated colors, broad painted light-and-shadow shapes, and a few sharp graphic highlights. Suggest volume through illustration, not realistic materials or physically accurate lighting. Simplified ornamentation, no tiny engraving, no realistic material refraction, no surface microtexture. Serious heroic fantasy, not cute or chibi. No photography, jewelry advertising, photorealism, 3D rendering, ray tracing, depth of field, or cinematic scene. Nearly frontal view, centered upright composition, instantly readable at 60×60 pixels. The shard itself fills approximately 85-90% of the canvas along its longest dimension, with the entire item visible without cropping.

IMPORTANT: the shard MUST be surrounded by a thick, unmistakable bright white glowing outline — a solid bright white rim light tracing the entire outer silhouette of the shard, then softly fading outward into a white halo. This white glow is a mandatory, highly visible design element, not a subtle effect — render it clearly, as prominent as the shard itself. Preserve the shard's own colors inside the outline; only the glow around the edge is white. Fully transparent background with a true alpha channel (everything outside the glow is pure transparent alpha, not white) — no colored background, no checkerboard pattern, no ground plane, no cast shadow, no text, no watermark, and no external frame. PNG format, 1024×1024 pixels, 1:1 aspect ratio.
```

### Осколок Полумрака

```text
Stylized 2D fantasy MMORPG inventory icon, "Shard of Dusk," a magical crystal shard. An elongated, perfectly faceted hexagonal crystal spike split cleanly down its length into two halves — one half glowing warm dim orange like fading daylight, the other half deep cool violet-grey like encroaching night, the two tones meeting at a sharp graphic line without blending. Prioritize a striking loot-icon silhouette over physically accurate crystal construction.

Hand-painted 2D game asset in the style of classic browser fantasy MMORPG inventory icons. Bold illustrated contours, exaggerated proportions, saturated colors, broad painted light-and-shadow shapes, and a few sharp graphic highlights. Suggest volume through illustration, not realistic materials or physically accurate lighting. Simplified ornamentation, no tiny engraving, no realistic material refraction, no surface microtexture. Serious heroic fantasy, not cute or chibi. No photography, jewelry advertising, photorealism, 3D rendering, ray tracing, depth of field, or cinematic scene. Nearly frontal view, centered upright composition, instantly readable at 60×60 pixels. The shard itself fills approximately 85-90% of the canvas along its longest dimension, with the entire item visible without cropping.

IMPORTANT: the shard MUST be surrounded by a thick, unmistakable bright white glowing outline — a solid bright white rim light tracing the entire outer silhouette of the shard, then softly fading outward into a white halo. This white glow is a mandatory, highly visible design element, not a subtle effect — render it clearly, as prominent as the shard itself. Preserve the shard's own colors inside the outline; only the glow around the edge is white. Fully transparent background with a true alpha channel (everything outside the glow is pure transparent alpha, not white) — no colored background, no checkerboard pattern, no ground plane, no cast shadow, no text, no watermark, and no external frame. PNG format, 1024×1024 pixels, 1:1 aspect ratio.
```

### Осколок Клятвы

```text
Stylized 2D fantasy MMORPG inventory icon, "Shard of the Oath," a magical crystal shard. An elongated, perfectly faceted hexagonal crystal spike, warm honey-gold, with a steady, unwavering amber glow burning evenly at its core, unlike the flickering light of the other shards — calm, constant, and resolute. Prioritize a striking loot-icon silhouette over physically accurate crystal construction.

Hand-painted 2D game asset in the style of classic browser fantasy MMORPG inventory icons. Bold illustrated contours, exaggerated proportions, saturated colors, broad painted light-and-shadow shapes, and a few sharp graphic highlights. Suggest volume through illustration, not realistic materials or physically accurate lighting. Simplified ornamentation, no tiny engraving, no realistic material refraction, no surface microtexture. Serious heroic fantasy, not cute or chibi. No photography, jewelry advertising, photorealism, 3D rendering, ray tracing, depth of field, or cinematic scene. Nearly frontal view, centered upright composition, instantly readable at 60×60 pixels. The shard itself fills approximately 85-90% of the canvas along its longest dimension, with the entire item visible without cropping.

IMPORTANT: the shard MUST be surrounded by a thick, unmistakable bright white glowing outline — a solid bright white rim light tracing the entire outer silhouette of the shard, then softly fading outward into a white halo. This white glow is a mandatory, highly visible design element, not a subtle effect — render it clearly, as prominent as the shard itself. Preserve the shard's own colors inside the outline; only the glow around the edge is white. Fully transparent background with a true alpha channel (everything outside the glow is pure transparent alpha, not white) — no colored background, no checkerboard pattern, no ground plane, no cast shadow, no text, no watermark, and no external frame. PNG format, 1024×1024 pixels, 1:1 aspect ratio.
```

### Осколок Истины

```text
Stylized 2D fantasy MMORPG inventory icon, "Shard of Truth," a magical crystal shard. A chaotic cluster of several jagged crystal spikes jutting outward in unpredictable directions from an irregular core, perfectly clear and glass-transparent, with sharp graphic light-refraction lines painted through it like cracked glass. Prioritize a striking loot-icon silhouette over physically accurate crystal construction.

Hand-painted 2D game asset in the style of classic browser fantasy MMORPG inventory icons. Bold illustrated contours, exaggerated proportions, saturated colors, broad painted light-and-shadow shapes, and a few sharp graphic highlights. Suggest volume through illustration, not realistic materials or physically accurate lighting. Simplified ornamentation, no tiny engraving, no realistic material refraction, no surface microtexture. Serious heroic fantasy, not cute or chibi. No photography, jewelry advertising, photorealism, 3D rendering, ray tracing, depth of field, or cinematic scene. Nearly frontal view, centered upright composition, instantly readable at 60×60 pixels. The shard itself fills approximately 85-90% of the canvas along its longest dimension, with the entire item visible without cropping.

IMPORTANT: the shard MUST be surrounded by a thick, unmistakable bright white glowing outline — a solid bright white rim light tracing the entire outer silhouette of the shard, then softly fading outward into a white halo. This white glow is a mandatory, highly visible design element, not a subtle effect — render it clearly, as prominent as the shard itself. Preserve the shard's own colors inside the outline; only the glow around the edge is white. Fully transparent background with a true alpha channel (everything outside the glow is pure transparent alpha, not white) — no colored background, no checkerboard pattern, no ground plane, no cast shadow, no text, no watermark, and no external frame. PNG format, 1024×1024 pixels, 1:1 aspect ratio.
```

### Осколок Жертвы

```text
Stylized 2D fantasy MMORPG inventory icon, "Shard of Sacrifice," a magical crystal shard. A chaotic cluster of several jagged crystal spikes jutting outward in unpredictable directions from an irregular core, deep dark-red and unusually dense-looking, with darker crimson veins pooling near its center like old dried blood. Prioritize a striking loot-icon silhouette over physically accurate crystal construction.

Hand-painted 2D game asset in the style of classic browser fantasy MMORPG inventory icons. Bold illustrated contours, exaggerated proportions, saturated colors, broad painted light-and-shadow shapes, and a few sharp graphic highlights. Suggest volume through illustration, not realistic materials or physically accurate lighting. Simplified ornamentation, no tiny engraving, no realistic material refraction, no surface microtexture. Serious heroic fantasy, not cute or chibi. No photography, jewelry advertising, photorealism, 3D rendering, ray tracing, depth of field, or cinematic scene. Nearly frontal view, centered upright composition, instantly readable at 60×60 pixels. The shard itself fills approximately 85-90% of the canvas along its longest dimension, with the entire item visible without cropping.

IMPORTANT: the shard MUST be surrounded by a thick, unmistakable bright white glowing outline — a solid bright white rim light tracing the entire outer silhouette of the shard, then softly fading outward into a white halo. This white glow is a mandatory, highly visible design element, not a subtle effect — render it clearly, as prominent as the shard itself. Preserve the shard's own colors inside the outline; only the glow around the edge is white. Fully transparent background with a true alpha channel (everything outside the glow is pure transparent alpha, not white) — no colored background, no checkerboard pattern, no ground plane, no cast shadow, no text, no watermark, and no external frame. PNG format, 1024×1024 pixels, 1:1 aspect ratio.
```

### Осколок Покоя

```text
Stylized 2D fantasy MMORPG inventory icon, "Shard of Rest," a magical crystal shard. A chaotic cluster of several jagged crystal spikes jutting outward in unpredictable directions from an irregular core, smooth pale white-blue with no visible cracks, glowing with an unusually calm, even, soft inner light. Prioritize a striking loot-icon silhouette over physically accurate crystal construction.

Hand-painted 2D game asset in the style of classic browser fantasy MMORPG inventory icons. Bold illustrated contours, exaggerated proportions, saturated colors, broad painted light-and-shadow shapes, and a few sharp graphic highlights. Suggest volume through illustration, not realistic materials or physically accurate lighting. Simplified ornamentation, no tiny engraving, no realistic material refraction, no surface microtexture. Serious heroic fantasy, not cute or chibi. No photography, jewelry advertising, photorealism, 3D rendering, ray tracing, depth of field, or cinematic scene. Nearly frontal view, centered upright composition, instantly readable at 60×60 pixels. The shard itself fills approximately 85-90% of the canvas along its longest dimension, with the entire item visible without cropping.

IMPORTANT: the shard MUST be surrounded by a thick, unmistakable bright white glowing outline — a solid bright white rim light tracing the entire outer silhouette of the shard, then softly fading outward into a white halo. This white glow is a mandatory, highly visible design element, not a subtle effect — render it clearly, as prominent as the shard itself. Preserve the shard's own colors inside the outline; only the glow around the edge is white. Fully transparent background with a true alpha channel (everything outside the glow is pure transparent alpha, not white) — no colored background, no checkerboard pattern, no ground plane, no cast shadow, no text, no watermark, and no external frame. PNG format, 1024×1024 pixels, 1:1 aspect ratio.
```

### Осколок Испытания

```text
Stylized 2D fantasy MMORPG inventory icon, "Shard of the Trial," a magical crystal shard. A chaotic cluster of several jagged crystal spikes jutting outward in unpredictable directions from an irregular core, rough unpolished grey-brown stone-like surface with raw, uneven broken edges on every spike. Prioritize a striking loot-icon silhouette over physically accurate crystal construction.

Hand-painted 2D game asset in the style of classic browser fantasy MMORPG inventory icons. Bold illustrated contours, exaggerated proportions, saturated colors, broad painted light-and-shadow shapes, and a few sharp graphic highlights. Suggest volume through illustration, not realistic materials or physically accurate lighting. Simplified ornamentation, no tiny engraving, no realistic material refraction, no surface microtexture. Serious heroic fantasy, not cute or chibi. No photography, jewelry advertising, photorealism, 3D rendering, ray tracing, depth of field, or cinematic scene. Nearly frontal view, centered upright composition, instantly readable at 60×60 pixels. The shard itself fills approximately 85-90% of the canvas along its longest dimension, with the entire item visible without cropping.

IMPORTANT: the shard MUST be surrounded by a thick, unmistakable bright white glowing outline — a solid bright white rim light tracing the entire outer silhouette of the shard, then softly fading outward into a white halo. This white glow is a mandatory, highly visible design element, not a subtle effect — render it clearly, as prominent as the shard itself. Preserve the shard's own colors inside the outline; only the glow around the edge is white. Fully transparent background with a true alpha channel (everything outside the glow is pure transparent alpha, not white) — no colored background, no checkerboard pattern, no ground plane, no cast shadow, no text, no watermark, and no external frame. PNG format, 1024×1024 pixels, 1:1 aspect ratio.
```

### Осколок Отражения

```text
Stylized 2D fantasy MMORPG inventory icon, "Shard of Reflection," a magical crystal shard. A chaotic cluster of several jagged crystal spikes jutting outward in unpredictable directions from an irregular core, mirror-polished silver surface on every facet, each spike catching a bright sharp graphic highlight like a shard of broken mirror. Prioritize a striking loot-icon silhouette over physically accurate crystal construction.

Hand-painted 2D game asset in the style of classic browser fantasy MMORPG inventory icons. Bold illustrated contours, exaggerated proportions, saturated colors, broad painted light-and-shadow shapes, and a few sharp graphic highlights. Suggest volume through illustration, not realistic materials or physically accurate lighting. Simplified ornamentation, no tiny engraving, no realistic material refraction, no surface microtexture. Serious heroic fantasy, not cute or chibi. No photography, jewelry advertising, photorealism, 3D rendering, ray tracing, depth of field, or cinematic scene. Nearly frontal view, centered upright composition, instantly readable at 60×60 pixels. The shard itself fills approximately 85-90% of the canvas along its longest dimension, with the entire item visible without cropping.

IMPORTANT: the shard MUST be surrounded by a thick, unmistakable bright white glowing outline — a solid bright white rim light tracing the entire outer silhouette of the shard, then softly fading outward into a white halo. This white glow is a mandatory, highly visible design element, not a subtle effect — render it clearly, as prominent as the shard itself. Preserve the shard's own colors inside the outline; only the glow around the edge is white. Fully transparent background with a true alpha channel (everything outside the glow is pure transparent alpha, not white) — no colored background, no checkerboard pattern, no ground plane, no cast shadow, no text, no watermark, and no external frame. PNG format, 1024×1024 pixels, 1:1 aspect ratio.
```

### Осколок Надежды

```text
Stylized 2D fantasy MMORPG inventory icon, "Shard of Hope," a magical crystal shard. A chaotic cluster of several jagged crystal spikes jutting outward in unpredictable directions from an irregular core, warm honey-gold and softly translucent, with a flickering bright glow that pulses brighter at the tips of the spikes than at its center. Prioritize a striking loot-icon silhouette over physically accurate crystal construction.

Hand-painted 2D game asset in the style of classic browser fantasy MMORPG inventory icons. Bold illustrated contours, exaggerated proportions, saturated colors, broad painted light-and-shadow shapes, and a few sharp graphic highlights. Suggest volume through illustration, not realistic materials or physically accurate lighting. Simplified ornamentation, no tiny engraving, no realistic material refraction, no surface microtexture. Serious heroic fantasy, not cute or chibi. No photography, jewelry advertising, photorealism, 3D rendering, ray tracing, depth of field, or cinematic scene. Nearly frontal view, centered upright composition, instantly readable at 60×60 pixels. The shard itself fills approximately 85-90% of the canvas along its longest dimension, with the entire item visible without cropping.

IMPORTANT: the shard MUST be surrounded by a thick, unmistakable bright white glowing outline — a solid bright white rim light tracing the entire outer silhouette of the shard, then softly fading outward into a white halo. This white glow is a mandatory, highly visible design element, not a subtle effect — render it clearly, as prominent as the shard itself. Preserve the shard's own colors inside the outline; only the glow around the edge is white. Fully transparent background with a true alpha channel (everything outside the glow is pure transparent alpha, not white) — no colored background, no checkerboard pattern, no ground plane, no cast shadow, no text, no watermark, and no external frame. PNG format, 1024×1024 pixels, 1:1 aspect ratio.
```
