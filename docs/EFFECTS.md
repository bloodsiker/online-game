# Промпты иконок статус-эффектов и скиллов

Референсы стиля: `images/data/artifacts/{shield,szhiganie_many_dajgon3,baf_puzatika14,000standardmagic,001standardheal,forinst_greencurse,forinst_regeneration}.gif` (w1.dwar.ru) — 60×60, живописный стиль с мягким свечением, единый мотив по центру.

Фон унифицирован под гамму интерфейса игры (пергамент/кремовый/золото-коричневый — см. `#F3D8B2`/`#ffe7c5`/`#FFFBD6`/`#c9a961` в `public/main/css/main.css`, `public/css/hero.css`, рамка `public/main/images/item_slot.png`): без чёрных/тёмных углов, светлый пергаментный градиент от центра к краям. Каждый эффект различается мотивом и цветом свечения, а не фоном.

Иконка скилла (сама способность) и иконка эффекта (статус на цели) — разные UI-слоты: эффект показывается в баффбаре независимо от источника, скилл — в логе боя/списке способностей. Ниже сначала скиллы, потом статус-эффекты.

## Скиллы

### Огненная искра

```
A vivid 60x60 game spell-icon, painterly digital art style (matching classic
browser-MMORPG spell/buff icons) — a bold, striking fireball rather than a
subtle casting moment.

Subject: a large, dense ball of fire filling most of the frame, bright
white-yellow at its blazing core and deepening through orange to a rich red at
the swirling outer edges. Sharp tongues of flame lick outward from the sphere
in a few dynamic points, with bright embers and sparks scattering around it.
A short, streaking comet-like tail trails off one side, giving it a strong
sense of motion and impact, as if it just launched forward.

Background: a warm parchment/cream-gold radial gradient — pale cream at the
center softly warming to golden-tan at the edges. No dark corners, no black
vignette, no cold void — stays light and warm throughout, matching an old
parchment/leather fantasy UI. The fireball's glow should dominate the frame and
read as the clear focal point.

Style: highly saturated, vivid, punchy and eye-catching — bold enough to feel
satisfying to cast, with strong light bloom at the core and crisp flame-tongue
silhouettes at the edges. No text, no numbers, no hand, no body, no face.
```

### Огненный залп

```
A vivid 60x60 game spell-icon, painterly digital art style (matching classic
browser-MMORPG spell/buff icons) — the explosive moment of impact, not a
projectile in flight.

Subject: a violent fire explosion at the center — a dense, blinding white-hot
core surrounded by a jagged burst of orange-red flame tearing outward in sharp,
irregular shards. A faint shockwave ring of light expands just ahead of the
flame burst, and several chunks of glowing ember debris are thrown outward at
different angles, some already cooling to a darker red at their edges. The
whole composition should feel denser, hotter and more violent than a simple
flying fireball — this is the moment of a heavy projectile detonating.

Background: a warm parchment/cream-gold radial gradient — pale cream at the
center softly warming to golden-tan at the edges. No dark corners, no black
vignette, no cold void — stays light and warm throughout, matching an old
parchment/leather fantasy UI. The explosion's white-hot core should be the
single brightest point in the frame.

Style: highly saturated, vivid, chaotic and powerful — this is a stronger,
later-tier spell than a starter fire spark, so it should read as heavier and
more destructive. Strong light bloom at the core, sharp jagged silhouettes on
the flame shards for contrast. No text, no numbers, no hand, no body, no face.
```

### Испепеляющий вихрь

```
A vivid 60x60 game spell-icon, painterly digital art style (matching classic
browser-MMORPG spell/buff icons) — a swirling vortex, not a single fireball or
explosion burst.

Subject: a tight spiraling funnel of fire and glowing ash, twisting upward like
a small tornado. The spiral is built from ribbons of orange-red flame mixed
with streaks of dark grey-black ash and drifting embers caught in the swirl,
giving it a scorched, apocalyptic feel rather than a clean bright flame. The
vortex's core glows hottest white-orange at its base, darkening toward
smoke-grey ash near the top of the spiral. A few loose embers are flung outward
from the funnel's edge by the spinning motion.

Background: a warm parchment/cream-gold radial gradient — pale cream at the
center softly warming to golden-tan at the edges. No dark corners, no black
vignette, no cold void — stays light and warm throughout, matching an old
parchment/leather fantasy UI. The vortex's dark ash tones should still read
clearly against this light warm base through strong value contrast.

Style: highly saturated where the fire glows, but with real ash-grey darkness
mixed in — this is the most powerful and destructive of the fire spells, so it
should feel heavier, wilder and more overwhelming than the earlier tiers. Bold
spiral silhouette that reads instantly at small size despite the swirling
complexity. No text, no numbers, no hand, no body, no face.
```

### Малое исцеление плоти

```
A vivid 60x60 game spell-icon, painterly digital art style (matching classic
browser-MMORPG spell/buff icons) — a wound sealing shut, accented with small
healing crosses, not a single generic healing cross alone.

Subject: a jagged crack running across the center, but instead of breaking
apart, it is visibly mending — warm golden-white light pours out from along the
seam as the two edges draw back together and fuse. A soft cluster of tiny
sparkling light motes rises gently from the healed seam, along with a few small
glowing white-gold plus-shaped crosses drifting upward around the crack like
sparks of restorative magic — small and secondary to the mending crack itself,
not a single large centered cross. The light is soft and warm rather than
sharp or explosive.

Background: a warm parchment/cream-gold radial gradient — pale cream at the
center softly warming to golden-tan at the edges. No dark corners, no black
vignette, no cold void — stays light and warm throughout, matching an old
parchment/leather fantasy UI. The healing light should feel like the natural
brightest, warmest point of the whole scene rather than clashing with it.

Style: soft, warm, gentle glow — this is the smallest tier of a healing spell,
so the effect should read as modest and comforting rather than a dazzling burst.
Painterly brushwork, soft bloom along the light seam. Bold, simple silhouette
(the mending crack) readable at small size. No text, no numbers, no anatomy, no
depiction of a wound on a body — only the abstract crack-and-light motif.
```

### Тлеющая рана

```
A vivid 60x60 game spell-icon, painterly digital art style (matching classic
browser-MMORPG spell/buff icons) — built around the idea of ticking damage
over time, not a static wound or crater.

Subject: a small hourglass silhouette at the center, but instead of sand,
glowing embers trickle down through its narrow neck from the upper chamber to
the lower one — a few embers caught mid-fall, glowing hot orange-red, with a
faint trail of smoke rising off each one as it falls. The upper chamber holds a
handful of embers still waiting to fall; the lower chamber already has a small
smoldering pile glowing at the bottom, with thin smoke curling up from it.

Background: a warm parchment/cream-gold radial gradient — pale cream at the
center softly warming to golden-tan at the edges. No dark corners, no black
vignette, no cold void — stays light and warm throughout, matching an old
parchment/leather fantasy UI.

Style: deep maroon-red ember glow against a simple dark hourglass silhouette —
reads instantly as "burning damage measured out over time" rather than a single
moment. Painterly, soft smoke wisps, bold silhouette readable at small size. No
text, no numbers, no anatomy, no depiction of a wound on a body.
```

## Статус-эффекты

### Ожог от существа

```
A vivid 60x60 game status-effect icon, painterly digital art style (matching
classic browser-MMORPG spell/buff icons) — but built around a "burned into the
material" concept rather than a floating glowing symbol.

Subject: a scorched clawed paw-print pressed directly into the parchment
background itself, as if a fiery creature just stepped through — the print's
edges are charred dark brown-black, with a warm ember-orange glow still pulsing
along the rim of each toe/claw mark, and a few thin wisps of grey-white smoke
curling up from it. The print should read as an imprint IN the surface, not an
object floating on top of it.

Background: the same warm parchment/cream-gold as the rest of the set — pale
cream at the center softly warming to golden-tan at the edges — but visibly
scorched and darkened immediately around the paw-print, as if the heat singed
the parchment itself. No dark corners, no black vignette elsewhere — the burn
damage stays localized to the print, not the whole frame.

Style: saturated where the embers glow, otherwise painterly and warm. Strong
sense of "something just happened here" rather than an ongoing flame. Bold,
simple silhouette (the paw shape) readable at small size. No text, no numbers,
no creature shown directly, no depiction of a body being burned.
```

### Оглушение

```
A vivid 60x60 game status-effect icon, painterly digital art style (matching
classic browser-MMORPG spell/buff icons) — built around a "just struck, still
ringing" moment rather than a floating symbol swirling in place.

Subject: a bronze-gold bell at the center, freshly cracked down one side, still
visibly vibrating from a blow. Glowing golden sound-wave rings radiate outward
from the crack in a few concentric arcs, fading as they expand. A couple of
small bright metal fragments are flying off the crack point, caught mid-air.
Thin dark outline on the bell's silhouette so it stays crisp against a light
background.

Background: a warm parchment/cream-gold radial gradient — pale cream at the
center softly warming to golden-tan at the edges. No dark corners, no black
vignette, no cold void — stays light and warm throughout, matching an old
parchment/leather fantasy UI. The golden sound-wave rings should read clearly
against this warm base.

Style: saturated, vivid, painterly, with a strong sense of a single sudden
impact still echoing outward — not a calm, static object. Bold silhouette
(bell + rings) readable at small size. No text, no numbers, no anatomy, no
creature.
```

### Ожог

```
A vivid 60x60 game status-effect icon, painterly digital art style (matching
classic browser-MMORPG spell/buff icons) — a deliberate visual counterpart to
the leaf used for the Regeneration icon, but consumed by fire instead of alive.

Subject: a single leaf caught mid-burn — one edge already curling into black
ash and crumbling away, the other half still intact but licked by bright
orange-red flame eating across its surface. A few embers and small flakes of
ash drifting off it. The leaf's remaining veins glow faint orange from the heat
before they too are consumed.

Background: a warm parchment/cream-gold radial gradient — pale cream at the
center softly warming to golden-tan at the edges. No dark corners, no black
vignette, no cold void — stays light and warm throughout, matching an old
parchment/leather fantasy UI. The fire's orange-red should read as the hottest,
brightest point in the frame.

Style: saturated, vivid, painterly, with a real sense of decay and destruction
in progress — not a static flame, but a leaf actively being consumed. Bold
silhouette (the leaf's shape still recognizable despite the burning) readable
at small size. No text, no numbers, no anatomy, no creature.
```

### Отравление от укуса

```
A vivid 60x60 game status-effect icon, painterly digital art style (matching
classic browser-MMORPG spell/buff icons) — composed as a dynamic action scene
rather than a centered glowing symbol.

Subject: a coiled viper caught mid-strike, jaws open wide and fangs bared,
lunging diagonally toward the viewer. Dark scales with a subtle sheen, deep
toxic-green glinting along the fangs and dripping in a single bead of venom.
A faint sickly-green mist trails off its coiled body, and a couple of sharp
diagonal motion-streaks behind it sell the suddenness of the strike.

Background: a warm parchment/cream-gold sky-burst along the same diagonal as
the strike — pale golden light near the viper's head, warming to a deeper
amber-tan at the corners. No dark corners, no black vignette, no cold void —
stays light and warm throughout, matching an old parchment/leather fantasy UI.
The toxic green of the venom should pop clearly against this warm base.

Style: saturated, vivid, painterly — dynamic diagonal composition, strong sense
of sudden aggression. Bold silhouette that reads instantly at small size even
with the coiled pose. No text, no numbers, no other creatures, no depiction of
the strike's target.
```

### Кровотечение

```
A vivid 60x60 game status-effect icon, painterly digital art style with soft glow
(matching classic browser-MMORPG spell/buff icons).

Subject: a single jagged, torn streak of red light — shaped like a cracked
lightning bolt with rough, irregular edges. Bright crimson-red core, glowing
brighter toward the center, fading to deep maroon at the tips, with a thin dark
outline along the streak so it stays crisp against a light background. A few
small red ember particles drifting near the streak.

Background: a warm parchment/cream-gold radial gradient — pale cream at the
center (like aged parchment paper) softly warming to a golden-tan at the edges.
No dark corners, no black vignette, no cold void — the whole background stays
light and warm throughout, matching an old parchment/leather fantasy UI.

Style: saturated, vivid, glowing red motif against the light warm base.
Painterly brushwork with soft blur at the edges of the background, but a crisp
silhouette on the streak itself. No text, no numbers, no anatomy, no depiction
of injury — an abstract energy/light motif.
```

### Слабость

```
A vivid 60x60 game status-effect icon, painterly digital art style with soft glow
(matching classic browser-MMORPG spell/buff icons).

Subject: a small ghostly spirit hovering in mid-air — translucent, wispy
humanoid-ish silhouette with no legs, dissolving into faint smoky tendrils
below. Rendered in dim violet-grey with a thin darker violet outline so it reads
clearly against a light background; two small dim points of light for eyes.

Background: a warm parchment/cream-gold radial gradient — pale cream at the
center (like aged parchment paper) softly warming to a golden-tan at the edges.
No dark corners, no black vignette, no cold void — the whole background stays
light and warm throughout, matching an old parchment/leather fantasy UI.

Style: painterly with soft glow, ghostly and semi-transparent, but with enough
outline/contrast to stay legible on the light base. Bold, simple silhouette. No
text, no numbers, no realistic anatomy, no gore.
```

### Обморожение

```
A vivid 60x60 game status-effect icon, painterly digital art style (matching
classic browser-MMORPG spell/buff icons) — built around a "dying warmth" story
rather than a floating ice-shard symbol.

Subject: a single small ember of fire at the center, its glow already fading,
being rapidly overtaken by creeping frost — sharp white-blue ice crystals
growing inward from the edges like frozen tendrils, closing in on the last
warm orange-red light of the ember. Where the frost touches the ember, the
color visibly dims and cools, as if the cold is winning. A thin wisp of
grey-white vapor rises where warmth and cold meet.

Background: the same warm parchment/cream-gold as the rest of the set — pale
cream center warming to golden-tan edges — but with a faint cold blue-white haze
spreading in from one side, as if the frost is bleeding into the frame itself,
not contained to the ember alone. No dark corners, no black vignette.

Style: strong warm/cold contrast as the visual hook — the dying ember's last
orange glow against the advancing ice crystals' cold light. Painterly, sharp
crystalline detail on the frost, soft fading glow on the ember. Bold silhouette
readable at small size. No text, no numbers, no anatomy, no creature.
```

### Разрыв брони

```
A vivid 60x60 game status-effect icon, painterly digital art style with soft glow
(matching classic browser-MMORPG spell/buff icons).

Subject: a fractured plate of armor at the center, cracked through the middle
with jagged shard fragments breaking away. A burst of hot orange-yellow sparks
glowing along the crack, as if just struck and shattered. Cold steel-grey metal
with a thin dark outline so it stays crisp against a light background.

Background: a warm parchment/cream-gold radial gradient — pale cream at the
center (like aged parchment paper) softly warming to a golden-tan at the edges.
No dark corners, no black vignette, no cold void — the whole background stays
light and warm throughout, matching an old parchment/leather fantasy UI.

Style: saturated, vivid, glowing. Painterly brushwork, sharp bright highlights
on the spark burst and metal shards. Bold, simple silhouette readable at small
size. No text, no numbers, no anatomy.
```

### Регенерация

```
A vivid 60x60 game status-effect icon, painterly digital art style with soft glow
(matching classic browser-MMORPG spell/buff icons).

Subject: a single glowing green leaf at the center, vibrant and radiant, with
delicate veins picked out in a brighter lime-green light. A soft cluster of tiny
sparkling light particles drifting around it, like new life/growth energy. Thin
dark-green outline along the leaf's silhouette so it stays crisp against a light
background.

Background: a warm parchment/cream-gold radial gradient — pale cream at the
center (like aged parchment paper) softly warming to a golden-tan at the edges.
No dark corners, no black vignette, no cold void — the whole background stays
light and warm throughout, matching an old parchment/leather fantasy UI. The
vivid green of the leaf should glow warmly against this light base rather than
needing a dark backdrop for contrast.

Style: saturated, vivid, glowing — this is a positive/healing effect, so the
overall feel should read bright, warm and life-affirming rather than harsh.
Painterly brushwork with soft blur at the background edges, sharp bright
highlights on the leaf's veins. Bold, simple silhouette readable at small size.
No text, no numbers, no anatomy, no creature.
```

### Атака ястреба

```
A vivid 60x60 game status-effect icon, painterly digital art style (matching
classic browser-MMORPG spell/buff icons) — but composed as a dynamic action
scene rather than a centered glowing symbol.

Subject: a diving hawk seen from a dramatic low angle, wings swept back and
talons thrust forward toward the viewer, caught mid-strike. Rich amber-brown
and rust-orange plumage with sharp golden rim-lighting along the wing edges.
Behind it, a few sharp diagonal speed-streaks trailing off its wingtips to sell
the sense of sudden, explosive motion.

Background: a warm parchment/cream-gold sky-burst — pale golden light radiating
from behind the hawk along the same diagonal as its dive, warming to a deeper
amber-tan at the corners. No dark corners, no black vignette, no cold void —
stays light and warm throughout, matching an old parchment/leather fantasy UI.

Style: saturated, vivid, painterly — dynamic diagonal composition instead of a
centered static motif, strong sense of speed and aggression. Bold silhouette
that reads instantly at small size even with the angled pose. No text, no
numbers, no other creatures, no depiction of the strike's target.
```
