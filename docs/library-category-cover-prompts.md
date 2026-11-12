# Промпты для обложек разделов библиотеки (17 категорий)

Референс: https://w1.dwar.ru/info/library/start.php — карточки 268×150, резная золотисто-бронзовая фэнтезийная рама, внутри либо сетка 2×4 тематических иконок на чередующемся цветном фоне, либо геральдическая сцена (двойной герб). У референса название вплавлено в картинку лентой-свитком снизу.

**У нас иначе: название категории уже рисует сама вёрстка** (`library-home-tile span` в `layout.blade.php:259` — тёмный градиент + золотой текст поверх картинки, всегда видим). Значит **текст/название/ленту-свиток в картинку заливать НЕ нужно** — иначе получится двойной заголовок. Каждый промпт явно это оговаривает и просит нижнюю пятую часть кадра оставить темнее/спокойнее — там всё равно будет читаться наложенный текст.

Соотношение сторон 268:150 ≈ 1.79:1 — указывать `--ar 268:150` (или `--ar 16:9` как близкий стандартный вариант, если генератор не принимает произвольные дроби).

## 1. Профессии
```
Fantasy MMORPG game UI cover art, 268:150 aspect ratio. Use the EXACT same ornate carved gold-and-bronze picture frame as in the "Артефакты" and "Боевые архетипы" covers of this same series — identical frame thickness, identical filigree corner ornament design, identical carved-metal texture and color. Inside that frame: a 2x4 grid of small square icons on an alternating warm-brown/deep-teal checkered background, each icon representing a peaceful crafting/gathering profession — a herbalist's sickle, a fishing rod, a miner's pickaxe, a lumberjack's axe, a bubbling alchemist's flask, a steaming cooking pot, a blacksmith's hammer over an anvil, a tailor's needle and thread spool. Rich painterly game-art rendering, warm lighting. The icon grid must be fully contained within the top 75% of the frame's interior height — reserve the bottom 25% of the frame as an empty, plain, softly dark area with no icons or ornaments touching it, exactly the same size and placement as the empty bottom area in the "Артефакты" and "Боевые архетипы" covers (a title will be overlaid there by the website). NO text, NO letters, NO banner ribbon anywhere in the image. --ar 268:150
```

## 2. Репутации
```
Fantasy MMORPG game UI cover art, 268:150 aspect ratio. Use the EXACT same ornate carved gold-and-bronze picture frame as in the "Артефакты" and "Боевые архетипы" covers of this same series — identical frame thickness, identical filigree corner ornament design, identical carved-metal texture and color. Inside that frame: a symmetric heraldic scene of four small emblem crests arranged around a central glowing insignia — a city guard's tower-and-sword crest, a hunter's order crest with crossed bows, a relic-seeker's crest with an ancient rune stone, and a crimson brotherhood's crest with a blood-red banner — each crest on its own small shield shape, warm gold metal trim. Rich painterly game-art rendering, dramatic lighting. The heraldic scene must be fully contained within the top 75% of the frame's interior height — reserve the bottom 25% of the frame as an empty, plain, softly dark area with no crests or ornaments touching it, exactly the same size and placement as the empty bottom area in the "Артефакты" and "Боевые архетипы" covers (a title will be overlaid there by the website). NO text, NO letters, NO banner ribbon anywhere in the image. --ar 268:150
```

## 3. Бестиарий
```
Fantasy MMORPG game UI cover art, 268:150 aspect ratio. Use the EXACT same ornate carved gold-and-bronze picture frame as in the "Артефакты" and "Боевые архетипы" covers of this same series — identical frame thickness, identical filigree corner ornament design, identical carved-metal texture and color. Inside that frame: a 2x4 grid of eight menacing monster head portraits on an alternating icy-blue/warm-brown checkered background — a snarling wolf, a feral wildcat, a giant hornet, a roaring bear, a venomous serpent, a grinning skull-faced ghoul, a wild boar, and a shark-like sea beast. Detailed painterly creature art, dramatic dark lighting, each head centered in its own cell. The icon grid must be fully contained within the top 75% of the frame's interior height — reserve the bottom 25% of the frame as an empty, plain, softly dark area with no icons or ornaments touching it, exactly the same size and placement as the empty bottom area in the "Артефакты" and "Боевые архетипы" covers (a title will be overlaid there by the website). NO text, NO letters, NO banner ribbon anywhere in the image. --ar 268:150
```

## 4. Клан
```
Fantasy MMORPG game UI cover art, ornate carved gold-and-bronze picture frame with small filigree corner ornaments, 268:150 aspect ratio. Inside the frame: two large heraldic guild banners crossed diagonally behind a glowing treasury chest overflowing with gold coins and gems, each banner bearing a different bold clan-crest emblem (a roaring lion crest and a crossed-swords crest), rich crimson and deep-blue banner fabric with gold trim. Rich painterly game-art rendering, warm dramatic lighting. Leave the bottom fifth of the frame darker and visually calm/uncluttered (a title will be overlaid there by the website) — NO text, NO letters, NO banner ribbon anywhere in the image. --ar 268:150
```

## 5. Боевые архетипы
```
Fantasy MMORPG game UI cover art, ornate carved gold-and-bronze picture frame with small filigree corner ornaments, 268:150 aspect ratio. Inside the frame: three large heroic weapon-and-gear emblems arranged side by side, each representing a combat archetype — a massive tower shield crossed with a broadsword (tank), a pair of curved twin daggers wreathed in a swirling cloak motif (evasion/agility), and a glowing rapier crossed with a sharp crystal shard (precision/critical). Warm dramatic rim lighting, rich painterly game-art rendering. Leave the bottom fifth of the frame darker and visually calm/uncluttered (a title will be overlaid there by the website) — NO text, NO letters, NO banner ribbon anywhere in the image. --ar 268:150
```

## 6. Мир Вечности
```
Fantasy MMORPG game UI cover art, ornate carved gold-and-bronze picture frame with small filigree corner ornaments, 268:150 aspect ratio. Inside the frame: a grand double heraldic crest scene split into two halves like a world map emblem — left half a fiery crimson crest with a coiled dragon, right half a serene icy-blue crest with a radiant crystal star, both crests framed by winged golden ornamental wreaths meeting in the center, evoking an entire fantasy world's founding emblem. Rich painterly epic game-art rendering, dramatic lighting. Leave the bottom fifth of the frame darker and visually calm/uncluttered (a title will be overlaid there by the website) — NO text, NO letters, NO banner ribbon anywhere in the image. --ar 268:150
```

## 7. Кузня
```
Fantasy MMORPG game UI cover art, ornate carved gold-and-bronze picture frame with small filigree corner ornaments, 268:150 aspect ratio. Inside the frame: a glowing blacksmith forge scene — a heavy iron anvil with a red-hot half-forged sword blade laid across it, a raised hammer mid-strike, showering orange sparks, framed by a scattered arrangement of finished armor pieces (a helmet, a gauntlet, a shield) glowing faintly from the forge heat. Rich painterly game-art rendering, warm orange forge-light contrasted with cool shadows. Leave the bottom fifth of the frame darker and visually calm/uncluttered (a title will be overlaid there by the website) — NO text, NO letters, NO banner ribbon anywhere in the image. --ar 268:150
```

## 8. Комиссионный магазин
```
Fantasy MMORPG game UI cover art, ornate carved gold-and-bronze picture frame with small filigree corner ornaments, 268:150 aspect ratio. Inside the frame: an ornate merchant's trading scene — a brass balance scale weighing a glowing gem against a pile of gold coins, flanked by a bulging coin purse and a rolled parchment ledger tied with a ribbon, warm parchment-and-brass color palette. Rich painterly game-art rendering, warm candlelit lighting. Leave the bottom fifth of the frame darker and visually calm/uncluttered (a title will be overlaid there by the website) — NO text, NO letters, NO banner ribbon anywhere in the image. --ar 268:150
```

## 9. Травмы
```
Fantasy MMORPG game UI cover art, ornate carved gold-and-bronze picture frame with small filigree corner ornaments, 268:150 aspect ratio. Inside the frame: a battlefield medic's still-life scene — a wrapped linen bandage roll, a wooden splint bound with leather straps, a small bundle of healing herbs, and a cracked bone fragment, arranged on a weathered wooden surface, muted desaturated color palette with a single warm highlight. Rich painterly game-art rendering, somber lighting. Leave the bottom fifth of the frame darker and visually calm/uncluttered (a title will be overlaid there by the website) — NO text, NO letters, NO banner ribbon anywhere in the image. --ar 268:150
```

## 10. Артефакты
```
Fantasy MMORPG game UI cover art, ornate carved gold-and-bronze picture frame with small filigree corner ornaments, 268:150 aspect ratio. Inside the frame: a 2x4 grid of eight glowing magical artifact icons on an alternating deep-purple/midnight-blue checkered background — an ornate glowing ring, a jeweled amulet, a floating crystal orb, an enchanted bracelet, a rune-etched pendant, a levitating relic shard, a glowing signet, and a mystical talisman, each item radiating a soft magical light. Rich painterly game-art rendering, mystical atmosphere. Leave the bottom fifth of the frame darker and visually calm/uncluttered (a title will be overlaid there by the website) — NO text, NO letters, NO banner ribbon anywhere in the image. --ar 268:150
```

## 11. Расы
```
Fantasy MMORPG game UI cover art, ornate carved gold-and-bronze picture frame with small filigree corner ornaments, 268:150 aspect ratio. Inside the frame: five heroic character portrait busts arranged in a row, each a different fantasy race — a noble human warrior, a slender high elf with pointed ears, a pale dark elf with sharp features and dark markings, a stocky bearded dwarf, and a small cheerful hobbit — each portrait framed by its own small ornamental oval border. Rich painterly game-art rendering, warm heroic lighting. Leave the bottom fifth of the frame darker and visually calm/uncluttered (a title will be overlaid there by the website) — NO text, NO letters, NO banner ribbon anywhere in the image. --ar 268:150
```

## 12. Карта
```
Fantasy MMORPG game UI cover art, 268:150 aspect ratio. Use the EXACT same ornate carved gold-and-bronze picture frame as in the "Артефакты" and "Боевые архетипы" covers of this same series — identical frame thickness, identical filigree corner ornament design, identical carved-metal texture and color. Inside that frame: an aged parchment world map, hand-drawn ink illustrations of forests, mountain ranges, a winding river and a walled city, dotted travel routes connecting them, an ornate compass rose in one corner, warm sepia-and-faded-gold parchment tones with a few burnt/curled edges. Rich painterly game-art rendering, warm candlelit atmosphere. The map illustration must be fully contained within the top 75% of the frame's interior height — reserve the bottom 25% of the frame as an empty, plain, softly dark area with no map details touching it, exactly the same size and placement as the empty bottom area in the "Артефакты" and "Боевые архетипы" covers (a title will be overlaid there by the website). NO text, NO letters, NO place names, NO banner ribbon anywhere in the image. --ar 268:150
```

## 13. Снаряжение
```
Fantasy MMORPG game UI cover art, 268:150 aspect ratio. Use the EXACT same ornate carved gold-and-bronze picture frame as in the "Артефакты" and "Боевые архетипы" covers of this same series — identical frame thickness, identical filigree corner ornament design, identical carved-metal texture and color. Inside that frame: a 2x4 grid of eight distinct equipment icons on an alternating steel-grey/deep-brown checkered background — a longsword, a kite shield, a horned helmet, a chainmail hauberk, a pair of gauntlets, tall greaves, a spiked pauldron, and a leather belt with a buckle. Rich painterly game-art rendering, cool metallic lighting. The icon grid must be fully contained within the top 75% of the frame's interior height — reserve the bottom 25% of the frame as an empty, plain, softly dark area with no icons or ornaments touching it, exactly the same size and placement as the empty bottom area in the "Артефакты" and "Боевые архетипы" covers (a title will be overlaid there by the website). NO text, NO letters, NO banner ribbon anywhere in the image. --ar 268:150
```

> **Исправлено (12.11.2026).** Первая генерация 14–17 вышла с толстой яркой золотой рамой, красными камнями и нижним блоком в отдельной рамке на 25% высоты. Эталон серии (например, «Расы», «Артефакты», «Бестиарий») — тонкая тусклая бронзовая рама, синие сапфиры только в углах, нижние ~19% — просто затемнение картинки без рамки. Генерировать **с прикреплённой старой обложкой** (лучше «Расы» или «Артефакты») как референсом.

## 14. Навыки
```
Fantasy MMORPG game UI cover art, 268:150 aspect ratio, matching the attached reference cover exactly in frame and layout. FRAME: a THIN antique frame, only about 1.5% of the image width, made of aged dull dark-bronze and tarnished gold with dark patina (not shiny, not polished, not bright yellow gold). Small curling filigree scroll ornaments ONLY in the four corners, each corner with one tiny blue sapphire; one small ornament at the top center of the frame. NO red gems, NO diamond-shaped jewels on the sides, NO massive ornate border, NO inner frames or boxes. The artwork fills the whole interior edge to edge. Inside the frame: a 2x4 grid of eight skill icons on an alternating warm-brown/deep-green checkered background — the top row shows combat mastery (a longsword, a battle axe, a round shield, a glowing spellbook) and the bottom row shows peaceful professions (a herbalist's sickle with a sprig of herbs, a fishing rod, a miner's pickaxe over a chunk of ore, a bubbling alchemist's flask), each icon with a small golden progress-star above it suggesting growing mastery. Rich painterly game-art rendering, warm lighting. The icon grid is placed in the upper 80% of the image. BOTTOM AREA: the bottom 19% of the image is NOT a separate panel — the same scene simply fades into a near-black dark-brown gradient band, separated from the artwork only by a thin faint horizontal line, with no frame, no border and no ornaments around it (the website overlays the category title there). NO text, NO letters, NO banner ribbon anywhere in the image. --ar 268:150
```

## 15. Интерфейс
```
Fantasy MMORPG game UI cover art, 268:150 aspect ratio, matching the attached reference cover exactly in frame and layout. FRAME: a THIN antique frame, only about 1.5% of the image width, made of aged dull dark-bronze and tarnished gold with dark patina (not shiny, not polished, not bright yellow gold). Small curling filigree scroll ornaments ONLY in the four corners, each corner with one tiny blue sapphire; one small ornament at the top center of the frame. NO red gems, NO diamond-shaped jewels on the sides, NO massive ornate border, NO inner frames or boxes. The artwork fills the whole interior edge to edge. Inside the frame: a stylized fantasy game screen layout rendered as an ornate artifact — a carved wooden-and-brass panel with a row of square hotbar slots holding small glowing item and spell icons, small engraved keycap-like stones marked only with abstract rune symbols beneath the slots, a miniature parchment minimap in one corner, and two thin glowing bars (one crimson, one sapphire) like health and mana gauges. Rich painterly game-art rendering, warm candle-gold lighting with soft magical glows. The panel composition is placed in the upper 80% of the image. BOTTOM AREA: the bottom 19% of the image is NOT a separate panel — the same scene simply fades into a near-black dark-brown gradient band, separated from the artwork only by a thin faint horizontal line, with no frame, no border and no ornaments around it (the website overlays the category title there). NO text, NO letters, NO banner ribbon anywhere in the image. --ar 268:150
```

## 16. События
```
Fantasy MMORPG game UI cover art, 268:150 aspect ratio, matching the attached reference cover exactly in frame and layout. FRAME: a THIN antique frame, only about 1.5% of the image width, made of aged dull dark-bronze and tarnished gold with dark patina (not shiny, not polished, not bright yellow gold). Small curling filigree scroll ornaments ONLY in the four corners, each corner with one tiny blue sapphire; one small ornament at the top center of the frame. NO red gems, NO diamond-shaped jewels on the sides, NO massive ornate border, NO inner frames or boxes. The artwork fills the whole interior edge to edge. Inside the frame: a dramatic world-event scene viewed through the frame — a night-time fantasy valley under a swirling violet-and-amber sky, a towering glowing rift portal in the center spilling strange creatures, scattered glowing event relics on the ground, a large ornate hourglass with flowing golden sand standing in the foreground on one side and a waving war banner with a blank crest on the other side, small silhouettes of adventurers rushing toward the portal together. Rich painterly epic game-art rendering, dramatic contrasting lighting. The scene is placed in the upper 80% of the image. BOTTOM AREA: the bottom 19% of the image is NOT a separate panel — the same scene simply fades into a near-black dark-brown gradient band, separated from the artwork only by a thin faint horizontal line, with no frame, no border and no ornaments around it (the website overlays the category title there). NO text, NO letters, NO banner ribbon anywhere in the image. --ar 268:150
```

## 17. Магические навыки
```
Fantasy MMORPG game UI cover art, 268:150 aspect ratio, matching the attached reference cover exactly in frame and layout. FRAME: a THIN antique frame, only about 1.5% of the image width, made of aged dull dark-bronze and tarnished gold with dark patina (not shiny, not polished, not bright yellow gold). Small curling filigree scroll ornaments ONLY in the four corners, each corner with one tiny blue sapphire; one small ornament at the top center of the frame. NO red gems, NO diamond-shaped jewels on the sides, NO massive ornate border, NO inner frames or boxes. The artwork fills the whole interior edge to edge. Inside the frame: a 2x4 grid of eight glowing spell rune-circles on an alternating midnight-blue/deep-violet checkered background — a fireball, a crackling lightning bolt, an ice shard, a swirling poison cloud, a radiant healing hand with green light, a golden protective barrier dome, a dark curse sigil with chains, and a glowing passive aura emblem — each spell icon radiating its own colored magical light. Rich painterly game-art rendering, mystical arcane atmosphere. The icon grid is placed in the upper 80% of the image. BOTTOM AREA: the bottom 19% of the image is NOT a separate panel — the same scene simply fades into a near-black dark-brown gradient band, separated from the artwork only by a thin faint horizontal line, with no frame, no border and no ornaments around it (the website overlays the category title there). NO text, NO letters, NO banner ribbon anywhere in the image. --ar 268:150
```

## После генерации

Сохранить файлы (например) в `public/img/library/covers/<slug>.png` и проставить путь в `image` каждой `LibraryCategory`:

| id | Категория | slug | путь |
|---|---|---|---|
| 75 | Профессии | professii | `img/library/covers/professii.png` |
| 76 | Репутации | reputatsii | `img/library/covers/reputatsii.png` |
| 77 | Бестиарий | bestiarii | `img/library/covers/bestiarii.png` |
| 122 | Клан | klan | `img/library/covers/klan.png` |
| 123 | Боевые архетипы | boevye-arhetipy | `img/library/covers/boevye-arhetipy.png` |
| 129 | Мир Вечности | mir-vechnosti | `img/library/covers/mir-vechnosti.png` |
| 131 | Кузня | kuznya | `img/library/covers/kuznya.png` |
| 137 | Комиссионный магазин | komissionnyi-magazin | `img/library/covers/komissionnyi-magazin.png` |
| 149 | Травмы | travmy | `img/library/covers/travmy.png` |
| 157 | Артефакты | artefakty | `img/library/covers/artefakty.png` |
| 173 | Расы | rasy | `img/library/covers/rasy.png` |
| 236 | Карта | karta | `img/library/covers/karta.png` |
| 247 | Снаряжение | snariazhenie | `img/library/covers/snariazhenie.png` |
| 296 | Навыки | navyki | `img/library/covers/navyki.png` |
| 297 | Интерфейс | interfeis | `img/library/covers/interfeis.png` |
| 295 | События | sobytiya | `img/library/covers/sobytiya.png` |
| 298 | Магические навыки | magicheskie-navyki | `img/library/covers/magicheskie-navyki.png` |
