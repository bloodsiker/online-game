# Иконки заклинаний (magic_skills.image)

Стиль — по образцу уже загруженной иконки `magic-skills/MBLl5x...` «Атака
ястреба»: живописная полу-реалистичная RPG-иконка умения, фон — яркий
насыщенный тёпло-золотистый/оранжевый, как тёплый закатный свет, почти
полностью заливает кадр, лёгкая виньетка только по самым углам (НЕ тёмный
и не мрачный фон — у «Каменной кожи» он темнее и сужен к краям, этот
эталон не используем). Предмет/символ заполняет почти весь кадр, драматичное
боковое освещение с ярким контровым краем. **Это НЕ тот же стиль**, что у
`img/passives/*`/`img/boss-mechanics/*` (глянцевые 3D-иконки на
зелёном/синем переливающемся фоне) — тот отдельный набор для пассивок
рун/боссов, сюда не подходит.

Квадрат, 512×512, без текста/рамки/водяного знака — сам квадрат уже рамка.

Для скиллов, которые навешивают debuff-эффект (`effects` таблица), сверился
с уже реально загруженными иконками этих самых эффектов (`effects.image`,
тот же тёплый золотисто-янтарный стиль) — где мотив уже узнаваемо устоялся
в игре, повторяю его в иконке скилла, а не придумываю новый символ с нуля:
- «Паралич» (id 3) — чёрная тень, оплетённая синими электрическими разрядами.
- «Страх» (id 48) — кричащий призрачный череп в фиолетовых трещинах-разрядах.
- «Разрыв брони» (id 17) — треснувшая стальная кираса с огненной трещиной.
- «Отравление от укуса» (id 15) — оскалившаяся змея с ядом.
- «Обморожение» (id 18) — ледяные шипы, расходящиеся от тлеющего уголька внутри.
- «Слабость» (id 19) — тёмно-фиолетовый бесплотный призрак/дух.

## Боевые (12)

### Огненный шар (id 1)
```text
Painterly semi-realistic RPG spell icon, a swirling orange-red fireball orb with a white-hot core, trailing wisps of flame and drifting embers around its edge. Bright warm golden-orange glowing background, rich and saturated like warm sunset light filling almost the entire frame, only a subtle vignette darkening the very corners, dramatic rim lighting along the orb's edge, filling most of the frame. Square composition, no text, no watermark, no border.
```

### Ледяная стрела (id 2)
```text
Painterly semi-realistic RPG spell icon, a sharp crystalline shard of blue-white ice shaped like an arrowhead, faint frost mist curling off its jagged surface, tiny ice crystals catching the light. Bright warm golden-orange glowing background, rich and saturated like warm sunset light filling almost the entire frame, only a subtle vignette darkening the very corners, cool blue rim lighting along the ice edge contrasting the warm backdrop, filling most of the frame. Square composition, no text, no watermark, no border.
```

### Молния (id 70)
```text
Painterly semi-realistic RPG spell icon, a jagged crackling bolt of electric-blue lightning branching across the frame, small arcs of white-hot electricity sparking off its length. Bright warm golden-orange glowing background, rich and saturated like warm sunset light filling almost the entire frame, only a subtle vignette darkening the very corners, sharp bright rim lighting along the bolt, filling most of the frame. Square composition, no text, no watermark, no border.
```

### Разряд слабости (id 71)
Мотив — тот же призрачный дух, что у уже загруженного эффекта «Слабость» (id 19, `effects/JwDsGk...`): тёмно-фиолетовый бесплотный силуэт с развевающимися дымчатыми щупальцами-рукавами.
```text
Painterly semi-realistic RPG spell icon, a ghostly hooded wraith figure made of swirling dark-violet smoke, its formless clawed arms reaching outward, faint pale lavender wisps trailing off its body like draining energy. Bright warm golden-orange glowing background, rich and saturated like warm sunset light filling almost the entire frame, only a subtle vignette darkening the very corners, cool violet rim lighting against the warm backdrop, filling most of the frame. Square composition, no text, no watermark, no border.
```

### Огненный залп (id 72)
```text
Painterly semi-realistic RPG spell icon, a larger, more violent fireball than a simple flame — multiple braided trails of orange-red fire spiraling around a blinding white-hot core, scattered sparks and embers streaking outward. Bright warm golden-orange glowing background, rich and saturated like warm sunset light filling almost the entire frame, only a subtle vignette darkening the very corners, intense bright rim lighting, filling most of the frame. Square composition, no text, no watermark, no border.
```

### Укус яда (id 73)
Мотив близок к уже загруженному эффекту «Отравление от укуса» (id 15, `effects/9IgLW1...`): оскаленная змеиная голова с ядом — держу тот же символ.
```text
Painterly semi-realistic RPG spell icon, a snarling serpent's head lunging forward with bared fangs, a thick droplet of glowing green venom hanging from one fang, toxic green mist curling around its jaw and neck. Bright warm golden-orange glowing background, rich and saturated like warm sunset light filling almost the entire frame, only a subtle vignette darkening the very corners, sickly green rim lighting along the fangs, filling most of the frame. Square composition, no text, no watermark, no border.
```

### Ледяной шип (id 74)
Мотив близок к уже загруженному эффекту «Обморожение» (id 18, `effects/FkU9pp...`): ледяные шипы, расходящиеся от тлеющего уголька внутри.
```text
Painterly semi-realistic RPG spell icon, sharp translucent blue-white ice spikes radiating outward from a small glowing orange ember at the very center, like frost crystallizing around a dying spark of warmth, fine mist drifting off the spike tips. Bright warm golden-orange glowing background, rich and saturated like warm sunset light filling almost the entire frame, only a subtle vignette darkening the very corners, cold blue-white rim lighting along the spikes, filling most of the frame. Square composition, no text, no watermark, no border.
```

### Кровавый клинок (id 75)
```text
Painterly semi-realistic RPG spell icon, a curved blade with a crimson-red glowing edge, dark blood-red energy dripping and swirling off the steel like liquid flame. Bright warm golden-orange glowing background, rich and saturated like warm sunset light filling almost the entire frame, only a subtle vignette darkening the very corners, deep red rim lighting along the blade's edge, filling most of the frame. Square composition, no text, no watermark, no border.
```

### Разрушение доспеха (id 76)
Мотив = уже загруженный эффект «Разрыв брони» (id 17, `effects/i9k10R...`): треснувшая стальная кираса с огненной трещиной — повторяю почти буквально.
```text
Painterly semi-realistic RPG spell icon, a cracked steel breastplate split down the middle by a jagged glowing orange fracture, small fragments of metal breaking away and drifting off, sparks scattering from the crack. Bright warm golden-orange glowing background, rich and saturated like warm sunset light filling almost the entire frame, only a subtle vignette darkening the very corners, harsh bright rim lighting along the broken edges, filling most of the frame. Square composition, no text, no watermark, no border.
```

### Парализующий взгляд (id 77)
Эффект «Паралич» (id 3, `effects/Hpplbl...`) уже устоялся как тёмная тень в синих разрядах — встраиваю в неё гипнотический взгляд, а не отдельный символ глаза (название скилла про "взгляд", но наносимый статус тот же, что от других источников паралича).
```text
Painterly semi-realistic RPG spell icon, a dark humanoid shadow-silhouette wrapped and crackling with jagged electric-blue lightning arcs, a single glowing violet eye visible at the center of its hooded head, its hypnotic gaze radiating thin rigid lines of pale energy outward. Bright warm golden-orange glowing background, rich and saturated like warm sunset light filling almost the entire frame, only a subtle vignette darkening the very corners, bright blue-violet rim lighting, filling most of the frame. Square composition, no text, no watermark, no border.
```

### Испепеляющий вихрь (id 78)
```text
Painterly semi-realistic RPG spell icon, a massive swirling vortex of orange-red fire spiraling violently inward toward a blinding white core, thick ribbons of flame and a storm of embers caught in the spin — the most intense and dominant fire effect in the set. Bright warm golden-orange glowing background, rich and saturated like warm sunset light filling almost the entire frame, only a subtle vignette darkening the very corners, blazing bright rim lighting, filling the entire frame. Square composition, no text, no watermark, no border.
```

### Клич ужаса (id 79)
Мотив = уже загруженный эффект «Страх» (id 48, `effects/lKCwWI...`): кричащий бледный череп с расходящимися фиолетовыми трещинами-разрядами на пергаментном фоне — повторяю почти буквально.
```text
Painterly semi-realistic RPG spell icon, a screaming pale skull with hollow dark eye sockets, its mouth open wide in a silent scream, jagged violet lightning-like cracks radiating outward from the skull in all directions, a faint violet haze surrounding it. Bright warm golden-orange glowing background, rich and saturated like warm sunset light filling almost the entire frame, only a subtle vignette darkening the very corners, cold violet rim lighting contrasting the warm backdrop, filling most of the frame. Square composition, no text, no watermark, no border.
```

## Лечение (3 ранга)

### Малое исцеление (id 27)
```text
Painterly semi-realistic RPG spell icon, a small delicate sprout of soft green light shaped like an unfurling leaf, a gentle warm-white glow radiating from its center, a few tiny sparkles drifting around it. Bright warm golden-orange glowing background, rich and saturated like warm sunset light filling almost the entire frame, only a subtle vignette darkening the very corners, soft gentle rim lighting, filling most of the frame. Square composition, no text, no watermark, no border.
```

### Среднее исцеление (id 80)
```text
Painterly semi-realistic RPG spell icon, a fuller blooming cluster of green-white healing light shaped like opening petals, a brighter warm glow pulsing from its core, more sparkles swirling around it than a lesser version. Bright warm golden-orange glowing background, rich and saturated like warm sunset light filling almost the entire frame, only a subtle vignette darkening the very corners, brighter rim lighting, filling most of the frame. Square composition, no text, no watermark, no border.
```

### Великое исцеление (id 28)
```text
Painterly semi-realistic RPG spell icon, a radiant fully-bloomed flower of pure golden-white healing light, its petals glowing intensely and casting bright rays outward, a cascade of sparkling motes swirling around it — the most powerful and luminous healing effect in the set. Bright warm golden-orange glowing background, rich and saturated like warm sunset light filling almost the entire frame, only a subtle vignette darkening the very corners, brilliant rim lighting, filling the entire frame. Square composition, no text, no watermark, no border.
```

## Боевой клич (6 рангов)

### Боевой клич I (id 24)
```text
Painterly semi-realistic RPG spell icon, a clenched warrior's fist wrapped in a thin aura of red-orange battle energy, small cracks of light glowing between the fingers. Bright warm golden-orange glowing background, rich and saturated like warm sunset light filling almost the entire frame, only a subtle vignette darkening the very corners, soft red rim lighting, filling most of the frame. Square composition, no text, no watermark, no border.
```

### Боевой клич II (id 81)
```text
Painterly semi-realistic RPG spell icon, a clenched warrior's fist wrapped in a thicker, more vivid aura of red-orange battle energy, jagged cracks of bright light glowing between the fingers, small sparks breaking off the aura. Bright warm golden-orange glowing background, rich and saturated like warm sunset light filling almost the entire frame, only a subtle vignette darkening the very corners, stronger red rim lighting, filling most of the frame. Square composition, no text, no watermark, no border.
```

### Боевой клич III (id 82)
```text
Painterly semi-realistic RPG spell icon, a clenched warrior's fist engulfed in a blazing aura of red-orange battle energy, the fist barely visible beneath cracking light and streaming sparks. Bright warm golden-orange glowing background, rich and saturated like warm sunset light filling almost the entire frame, only a subtle vignette darkening the very corners, bright red-orange rim lighting, filling most of the frame. Square composition, no text, no watermark, no border.
```

### Боевой клич IV (id 134)
```text
Painterly semi-realistic RPG spell icon, a clenched warrior's fist wreathed in a roaring aura of red-orange battle energy, wide jagged cracks of intense light tearing through the aura, a thick trail of sparks streaming off the knuckles. Bright warm golden-orange glowing background, rich and saturated like warm sunset light filling almost the entire frame, only a subtle vignette darkening the very corners, strong red-orange rim lighting, filling most of the frame. Square composition, no text, no watermark, no border.
```

### Боевой клич V (id 135)
```text
Painterly semi-realistic RPG spell icon, a clenched warrior's fist consumed by a raging inferno of red-orange battle energy, the fist almost fully hidden beneath searing light and a dense storm of sparks whipping outward. Bright warm golden-orange glowing background, rich and saturated like warm sunset light filling almost the entire frame, only a subtle vignette darkening the very corners, intense red-orange rim lighting, filling most of the frame. Square composition, no text, no watermark, no border.
```

### Боевой клич VI (id 136)
```text
Painterly semi-realistic RPG spell icon, a clenched warrior's fist fully engulfed in a towering blaze of red-orange battle energy, the whole frame ablaze with cracking light and an explosive shockwave of sparks radiating outward — the most powerful and fiery version in the set. Bright warm golden-orange glowing background, rich and saturated like warm sunset light filling the entire frame, only a subtle vignette darkening the very corners, brilliant red-orange rim lighting, filling the entire frame. Square composition, no text, no watermark, no border.
```

## Каменная кожа (6 рангов)

### Каменная кожа I (id 25)
Уже есть сгенерированная картинка (сверено визуально — гладкая кожа с
одной тонкой раскалённой трещиной вдоль края на тёплом золотистом фоне).
Промпт ниже — только на случай перегенерации, чтобы I ранг остался в
одной линейке со II-VI.
```text
Painterly semi-realistic RPG spell icon, a close-up of smooth skin just beginning to crack into thin grey stone, a single faint ember-orange fissure glowing along one edge where the transformation starts. Bright warm golden-orange glowing background, rich and saturated like warm sunset light filling almost the entire frame, only a subtle vignette darkening the very corners, soft rim lighting along the stone's edge, filling most of the frame. Square composition, no text, no watermark, no border.
```

### Каменная кожа II (id 83)
```text
Painterly semi-realistic RPG spell icon, a close-up of thick cracked grey stone skin with glowing orange fissures running deeper and brighter than a lesser version, small embers glowing in the widest cracks. Bright warm golden-orange glowing background, rich and saturated like warm sunset light filling almost the entire frame, only a subtle vignette darkening the very corners, stronger rim lighting along the stone's edge, filling most of the frame. Square composition, no text, no watermark, no border.
```

### Каменная кожа III (id 84)
```text
Painterly semi-realistic RPG spell icon, a dense plate of ancient grey-black stone armor fully covering the frame, deep molten-orange fissures glowing intensely across its surface like a cracked volcanic shell. Bright warm golden-orange glowing background, rich and saturated like warm sunset light filling almost the entire frame, only a subtle vignette darkening the very corners, bright rim lighting, filling most of the frame. Square composition, no text, no watermark, no border.
```

### Каменная кожа IV (id 128)
```text
Painterly semi-realistic RPG spell icon, a dense plate of ancient grey-black stone armor, wider and deeper molten-orange fissures glowing across its surface, thin streams of liquid fire beginning to flow along the widest cracks. Bright warm golden-orange glowing background, rich and saturated like warm sunset light filling almost the entire frame, only a subtle vignette darkening the very corners, strong rim lighting, filling most of the frame. Square composition, no text, no watermark, no border.
```

### Каменная кожа V (id 129)
```text
Painterly semi-realistic RPG spell icon, a massive slab of obsidian-dark stone armor fully encasing the frame, rivers of molten orange-white light flowing through deep fissures across its surface, a faint heat shimmer in the air. Bright warm golden-orange glowing background, rich and saturated like warm sunset light filling almost the entire frame, only a subtle vignette darkening the very corners, intense rim lighting, filling most of the frame. Square composition, no text, no watermark, no border.
```

### Каменная кожа VI (id 130)
```text
Painterly semi-realistic RPG spell icon, a colossal mountain-like armor of black volcanic stone, entirely veined with blazing molten-orange fissures like lava flowing through living rock, intense heat distortion radiating outward — the most fortified and intense version in the set. Bright warm golden-orange glowing background, rich and saturated like warm sunset light filling the entire frame, only a subtle vignette darkening the very corners, brilliant rim lighting, filling the entire frame. Square composition, no text, no watermark, no border.
```

## Благословение (6 рангов)

### Благословение I (id 26)
```text
Painterly semi-realistic RPG spell icon, a small golden halo ring hovering in the air, a few soft rays of warm white-gold light radiating gently outward from its center. Bright warm golden-orange glowing background, rich and saturated like warm sunset light filling almost the entire frame, only a subtle vignette darkening the very corners, soft golden rim lighting, filling most of the frame. Square composition, no text, no watermark, no border.
```

### Благословение II (id 85)
```text
Painterly semi-realistic RPG spell icon, a larger ornate golden halo ring hovering in the air, brighter rays of warm white-gold light radiating outward, small sparkles orbiting it. Bright warm golden-orange glowing background, rich and saturated like warm sunset light filling almost the entire frame, only a subtle vignette darkening the very corners, stronger golden rim lighting, filling most of the frame. Square composition, no text, no watermark, no border.
```

### Благословение III (id 86)
```text
Painterly semi-realistic RPG spell icon, a grand radiant golden halo ring blazing with intense white-gold light, powerful rays bursting outward in all directions, a shower of sparkling motes surrounding it. Bright warm golden-orange glowing background, rich and saturated like warm sunset light filling almost the entire frame, only a subtle vignette darkening the very corners, bright golden rim lighting, filling most of the frame. Square composition, no text, no watermark, no border.
```

### Благословение IV (id 131)
```text
Painterly semi-realistic RPG spell icon, two nested golden halo rings hovering and slowly counter-rotating in the air, intricate ornate patterns etched along their edges, bright rays of white-gold light radiating outward, a denser trail of sparkles orbiting between the rings. Bright warm golden-orange glowing background, rich and saturated like warm sunset light filling almost the entire frame, only a subtle vignette darkening the very corners, strong golden rim lighting, filling most of the frame. Square composition, no text, no watermark, no border.
```

### Благословение V (id 132)
```text
Painterly semi-realistic RPG spell icon, a radiant cluster of overlapping golden halo rings forming a small sun-like core, intense white-gold rays bursting outward in all directions, a dense shower of sparkling motes swirling around it. Bright warm golden-orange glowing background, rich and saturated like warm sunset light filling almost the entire frame, only a subtle vignette darkening the very corners, intense golden rim lighting, filling most of the frame. Square composition, no text, no watermark, no border.
```

### Благословение VI (id 133)
```text
Painterly semi-realistic RPG spell icon, a blazing celestial construct of concentric golden halo rings forming a miniature radiant sun, overwhelming white-gold light bursting outward in powerful rays, a dense storm of sparkling motes surrounding it — the most divine and luminous version in the set. Bright warm golden-orange glowing background, rich and saturated like warm sunset light filling the entire frame, only a subtle vignette darkening the very corners, brilliant golden rim lighting, filling the entire frame. Square composition, no text, no watermark, no border.
```

## Регенерация (6 рангов)

### Регенерация I (id 29)
```text
Painterly semi-realistic RPG spell icon, a slow gentle spiral of soft green healing energy curling upward like a wisp of glowing mist, a faint warm-green light pulsing at its center. Bright warm golden-orange glowing background, rich and saturated like warm sunset light filling almost the entire frame, only a subtle vignette darkening the very corners, soft green rim lighting, filling most of the frame. Square composition, no text, no watermark, no border.
```

### Регенерация II (id 87)
```text
Painterly semi-realistic RPG spell icon, a fuller, faster spiral of vivid green healing energy swirling upward with more visible motion, a brighter pulsing green glow at its center, tiny sparkles trailing the spiral. Bright warm golden-orange glowing background, rich and saturated like warm sunset light filling almost the entire frame, only a subtle vignette darkening the very corners, brighter green rim lighting, filling most of the frame. Square composition, no text, no watermark, no border.
```

### Регенерация III (id 88)
```text
Painterly semi-realistic RPG spell icon, a dense swirling vortex of brilliant green healing energy spiraling rapidly upward, radiant with intense glowing light and a dense trail of sparkling motes. Bright warm golden-orange glowing background, rich and saturated like warm sunset light filling almost the entire frame, only a subtle vignette darkening the very corners, brilliant green rim lighting, filling most of the frame. Square composition, no text, no watermark, no border.
```

### Регенерация IV (id 125)
```text
Painterly semi-realistic RPG spell icon, a wide fast-spinning column of brilliant emerald healing energy, visibly taller and denser than before, a thick trail of sparkling motes swirling around it and a bright pulsing core. Bright warm golden-orange glowing background, rich and saturated like warm sunset light filling almost the entire frame, only a subtle vignette darkening the very corners, intense green rim lighting, filling most of the frame. Square composition, no text, no watermark, no border.
```

### Регенерация V (id 126)
```text
Painterly semi-realistic RPG spell icon, a towering, rapidly spinning column of radiant emerald-white healing energy, a dense shower of sparkling motes streaming outward from its core, almost blinding with restorative light. Bright warm golden-orange glowing background, rich and saturated like warm sunset light filling almost the entire frame, only a subtle vignette darkening the very corners, intense radiant green-white rim lighting, filling most of the frame. Square composition, no text, no watermark, no border.
```

### Регенерация VI (id 127)
```text
Painterly semi-realistic RPG spell icon, a towering blazing vortex of pure emerald-white healing light, spinning with tremendous force, a dense explosive shower of sparkling motes radiating outward in every direction — the most potent and vivid regenerative effect in the set. Bright warm golden-orange glowing background, rich and saturated like warm sunset light filling the entire frame, only a subtle vignette darkening the very corners, brilliant radiant rim lighting, filling the entire frame. Square composition, no text, no watermark, no border.
```
