# Промпты для иконок боевых пассивок (CombatPassiveType)

7 иконок — по одной на каждый тип `App\Modules\Share\Domain\Enums\CombatPassiveType`. Используются и для рун/встроенных пассивок предметов, и для пассивок монстров (`/info/m/{id}`, блок «Пассивные способности») — enum общий, иконка тоже общая.

Файл кладётся в `public/img/passives/<value>.png` (значение case'а enum'а), подхватывается автоматически через `CombatPassiveType::iconUrl()` — пока файла нет, показывается общая заглушка `main/images/effects-unavailable.png`.

Стиль — как у активных скилов игры (см. `magic-skills/*.webp`: тёплое оранжевое радиальное свечение, без прозрачности, мальованный символ на весь кадр), но в светло-голубой палитре — чтобы пассивки визуально отличались от активных скилов с первого взгляда.

## vampirism.png (Вампиризм)
```
A fantasy game skill icon, painterly digital art style. Background: a radial glow, bright pale sky-blue at the center fading to a darker cool blue toward the edges, like the active-skill icons in this game (warm orange glow convention, but in cool light-blue instead). Centered symbol filling most of the frame: a crimson droplet of blood with a faint fang-shaped glint inside it, rimmed with a subtle icy-blue shimmer. Bold clear silhouette, strong value contrast, full-bleed square composition, no transparency, no text, no border frame.
```

## chain_attack.png (Цепная атака)
```
A fantasy game skill icon, painterly digital art style. Background: a radial glow, bright pale sky-blue at the center fading to a darker cool blue toward the edges, same convention as this game's active-skill icons but in light-blue instead of orange. Centered symbol filling most of the frame: a jagged electric-blue chain link connecting two small spark bursts, crackling with pale energy. Bold clear silhouette, strong value contrast, full-bleed square composition, no transparency, no text, no border frame.
```

## stun.png (Оглушение)
```
A fantasy game skill icon, painterly digital art style. Background: a radial glow, bright pale sky-blue at the center fading to a darker cool blue toward the edges, same convention as this game's active-skill icons but in light-blue instead of orange. Centered symbol filling most of the frame: a cluster of small stars and spiral swirls circling above a cracked impact mark, classic "dazed" symbol, rendered in icy white-blue tones. Bold clear silhouette, strong value contrast, full-bleed square composition, no transparency, no text, no border frame.
```

## double_strike.png (Двойной удар)
```
A fantasy game skill icon, painterly digital art style. Background: a radial glow, bright pale sky-blue at the center fading to a darker cool blue toward the edges, same convention as this game's active-skill icons but in light-blue instead of orange. Centered symbol filling most of the frame: two crossed steel swords striking at the same point, a faint motion-blur duplicate of each blade trailing behind, cool blue highlights on the metal. Bold clear silhouette, strong value contrast, full-bleed square composition, no transparency, no text, no border frame.
```

## reflect.png (Отражение)
```
A fantasy game skill icon, painterly digital art style. Background: a radial glow, bright pale sky-blue at the center fading to a darker cool blue toward the edges, same convention as this game's active-skill icons but in light-blue instead of orange. Centered symbol filling most of the frame: a round steel shield viewed head-on with a mirrored arrow bouncing off its polished surface, a faint blue glint across the metal. Bold clear silhouette, strong value contrast, full-bleed square composition, no transparency, no text, no border frame.
```

## rage.png (Ярость)
```
A fantasy game skill icon, painterly digital art style. Background: a radial glow, bright pale sky-blue at the center fading to a darker cool blue toward the edges, same convention as this game's active-skill icons but in light-blue instead of orange. Centered symbol filling most of the frame: a clenched armored fist wreathed in jagged cold blue energy lines instead of flame, crackling outward. Bold clear silhouette, strong value contrast, full-bleed square composition, no transparency, no text, no border frame.
```

## shield.png (Щит)
```
A fantasy game skill icon, painterly digital art style. Background: a radial glow, bright pale sky-blue at the center fading to a darker cool blue toward the edges, same convention as this game's active-skill icons but in light-blue instead of orange. Centered symbol filling most of the frame: a solid round steel shield viewed head-on with a bold emblem in its center, cool blue metallic highlights. Bold clear silhouette, strong value contrast, full-bleed square composition, no transparency, no text, no border frame.
```
