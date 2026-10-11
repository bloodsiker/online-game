# Промпт для иконки эффекта «Подземный Яд» (debuff, id=46)

Сейчас у эффекта нет иконки (`image=null`). Механика: `active_type=poison`, `damage_scaling_type=target_max_hp` — 60% от макс. HP цели за 60 секунд (6 тиков по 10 сек), вешается на удар Обсидиановым Скорпионом/Пещерным Пауком/Чёрной Гадюкой на Заваленной Штольне, эмодзи-маркер ☠️.

Стиль как у остальных эффектов игры (тёплый текстурный фон пергамента/золота с виньеткой по краям, без прозрачности и без белого свечения — см. `docs/paralysis-effect-icon-prompt.md`).

```
A poison debuff icon for a fantasy game, matching the game's established effect-icon style. Background: a full-bleed aged parchment/old paper texture in warm golden-yellow tones, subtly darkened with a soft vignette toward the four edges — no transparency, no border, no frame. Centered on top of that background: a stylized skull symbol with three sickly yellow-green venom droplets dripping from its jaw, thin cracks of the same sickly green glowing faintly across the skull's surface like poison spreading through bone, a single small drop falling below the symbol. Square composition, symbol filling most of the frame, --ar 1:1. Bold clear silhouette, painterly game-art rendering, strong value contrast against the warm parchment background — must read clearly as a small icon.
```
