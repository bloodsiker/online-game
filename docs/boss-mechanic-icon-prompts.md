# Иконки механик боссов («Умения» на /info/m/catalog/{id})

Секция «Умения» уже отображается на странице монстра (`monster::info-partials.abilities`,
`$kind='skill'`) — она берёт `boss_mechanics.image`, а если он пуст, падает
на `BossMechanicType::iconUrl()` → `public/img/boss-mechanics/{type}.png`,
один файл на тип (общий для всех боссов, как `img/passives/*.png`). Файлов
пока нет — показывается общая заглушка `effects-unavailable.png`.

`BossMechanicType` — 15 типов. Первые 6 ниже уже реально навешаны на
Костяного Скорпиона/Азазеля (id 350/351), остальные 9 пока ни на ком не
стоят, но иконка общая на тип — есть смысл закрыть все сразу, а не
по одной при каждом новом боссе.

Стиль — по образцу уже загруженных иконок пассивок (`public/img/passives/vampirism.png`
и соседние): глянцевая 3D-подобная игровая RPG-иконка умения, символ в
центре с чёткими бликами, яркое радиальное магическое свечение/лучи от
центра, квадратная композиция. Единственное отличие от пассивок — фон:
у пассивок синий, у умений боссов — **зелёный** (так визуально различаются
на глаз два типа способностей).

## aoe_attack — Массовая атака

```text
Glossy 3D-style RPG skill icon, a jagged orange-red explosive shockwave ring bursting outward from the center, sharp glossy highlights on the ring's edge, radiant orange-yellow glow rays bursting outward behind it, small debris/spark particles caught in the blast. Dark green background with a radiant emerald-green ambient glow surrounding the icon (passives use a blue background — this is the skill/mechanic icon set, always green), square icon composition, no text, no watermark, no border.
```

## enrage — Ярость

```text
Glossy 3D-style RPG skill icon, a clenched glossy crimson-red fist wrapped in jagged cracking veins that glow hot orange from within, sharp specular highlights on the knuckles, radiant red-orange glow rays bursting outward behind it. Dark green background with a radiant emerald-green ambient glow surrounding the icon (passives use a blue background — this is the skill/mechanic icon set, always green), square icon composition, no text, no watermark, no border.
```

## shield — Щит

```text
Glossy 3D-style RPG skill icon, a glossy blue-and-gold round shield viewed face-on, ornate rim with a glowing cyan magical emblem at its center, sharp specular highlights on the metal, radiant blue-white glow rays bursting outward behind it. Dark green background with a radiant emerald-green ambient glow surrounding the icon (passives use a blue background — this is the skill/mechanic icon set, always green), square icon composition, no text, no watermark, no border.
```

## life_drain — Вампиризм (механика босса)

```text
Glossy 3D-style RPG skill icon, a glossy dark-violet skull with a single large blood-red teardrop suspended above its open jaw, sharp specular highlights, thin spectral tendrils of dark-red energy swirling toward the skull, radiant violet-red glow rays bursting outward behind it. Dark green background with a radiant emerald-green ambient glow surrounding the icon (passives use a blue background — this is the skill/mechanic icon set, always green), square icon composition, no text, no watermark, no border.
```

## summon_minions — Вызов миньонов

```text
Glossy 3D-style RPG skill icon, a glowing purple-green magical summoning circle with jagged runic symbols, three small dark humanoid silhouettes rising out of it with glossy highlighted edges, radiant purple-green glow rays bursting outward behind it. Dark green background with a radiant emerald-green ambient glow surrounding the icon (passives use a blue background — this is the skill/mechanic icon set, always green), square icon composition, no text, no watermark, no border.
```

## death_explosion — Взрыв при смерти

```text
Glossy 3D-style RPG skill icon, a glossy bone-white skull cracking apart at the center with a violent dark-red and black explosive burst erupting from inside it, sharp specular highlights on the bone fragments, radiant red-black glow rays bursting outward behind it. Dark green background with a radiant emerald-green ambient glow surrounding the icon (passives use a blue background — this is the skill/mechanic icon set, always green), square icon composition, no text, no watermark, no border.
```

## regeneration — Регенерация

```text
Glossy 3D-style RPG skill icon, a glossy emerald-green crystal shaped like a closing wound or budding leaf, pulsing with soft inner light, sharp specular highlights, a few small healing sparkle particles swirling around it, radiant green glow rays bursting outward behind it. Dark green background with a radiant emerald-green ambient glow surrounding the icon (passives use a blue background — this is the skill/mechanic icon set, always green), square icon composition, no text, no watermark, no border.
```

## teleport — Телепортация

```text
Glossy 3D-style RPG skill icon, a glossy violet-blue swirling portal ring made of jagged crystalline shards, a bright white-violet point of light at its center, sharp specular highlights on the shard edges, radiant violet glow rays bursting outward behind it. Dark green background with a radiant emerald-green ambient glow surrounding the icon (passives use a blue background — this is the skill/mechanic icon set, always green), square icon composition, no text, no watermark, no border.
```

## buff_self — Усиление

```text
Glossy 3D-style RPG skill icon, a glossy golden upward-pointing arrow wrapped in ascending ribbons of bright light, sharp specular highlights on the metal, small golden sparkle particles rising around it, radiant gold-white glow rays bursting outward behind it. Dark green background with a radiant emerald-green ambient glow surrounding the icon (passives use a blue background — this is the skill/mechanic icon set, always green), square icon composition, no text, no watermark, no border.
```

## debuff_player — Ослабление игрока

```text
Glossy 3D-style RPG skill icon, a glossy sickly dark-green downward-pointing arrow wrapped in withering thorned vines, sharp specular highlights, small toxic dripping particles around it, radiant dark-green glow rays bursting outward behind it. Dark green background with a radiant emerald-green ambient glow surrounding the icon (passives use a blue background — this is the skill/mechanic icon set, always green), square icon composition, no text, no watermark, no border.
```

## reflect_damage — Отражение урона

```text
Glossy 3D-style RPG skill icon, a glossy silver mirror shard angled to catch light, a sharp jagged crack running across its glassy surface with a bright white-blue reflection glinting off it, sharp specular highlights, radiant silver-blue glow rays bursting outward behind it. Dark green background with a radiant emerald-green ambient glow surrounding the icon (passives use a blue background — this is the skill/mechanic icon set, always green), square icon composition, no text, no watermark, no border.
```

## immunity — Иммунитет

```text
Glossy 3D-style RPG skill icon, a glossy translucent white-gold hexagonal barrier dome with faint glowing runic lines across its surface, sharp specular highlights, soft holy light radiating from within, radiant white-gold glow rays bursting outward behind it. Dark green background with a radiant emerald-green ambient glow surrounding the icon (passives use a blue background — this is the skill/mechanic icon set, always green), square icon composition, no text, no watermark, no border.
```

## berserk — Берсерк

```text
Glossy 3D-style RPG skill icon, a glossy dark-red battle axe with cracked glowing orange veins running through its blade, dripping with aggressive energy, sharp specular highlights on the metal, radiant red-orange glow rays bursting outward behind it. Dark green background with a radiant emerald-green ambient glow surrounding the icon (passives use a blue background — this is the skill/mechanic icon set, always green), square icon composition, no text, no watermark, no border.
```

## mirror_image — Зеркальное отражение

```text
Glossy 3D-style RPG skill icon, three overlapping glossy translucent violet silhouettes of the same humanoid figure fanned out side by side, each with a thin glowing outline, sharp specular highlights, radiant violet glow rays bursting outward behind it. Dark green background with a radiant emerald-green ambient glow surrounding the icon (passives use a blue background — this is the skill/mechanic icon set, always green), square icon composition, no text, no watermark, no border.
```

## damage_to_heal — Конвертация урона в лечение

```text
Glossy 3D-style RPG skill icon, a glossy crystal orb split exactly in half, one half glowing dark crimson-red like raw damage, the other half glowing soft emerald-green like healing energy, a bright dividing line of light between them, sharp specular highlights, radiant red-green glow rays bursting outward behind it. Dark green background with a radiant emerald-green ambient glow surrounding the icon (passives use a blue background — this is the skill/mechanic icon set, always green), square icon composition, no text, no watermark, no border.
```
