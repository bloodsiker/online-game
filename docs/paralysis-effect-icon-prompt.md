# Промпт для иконки эффекта «Паралич» (debuff, id=3)

Сейчас у эффекта нет иконки вообще (`image=null`). Механика: `active_type=paralysis`, категория «control» (как «Оглушение») — пропускает раунд действия игрока, эмодзи-маркер ⚡.

Стиль как у остальных эффектов игры (тёплый текстурный фон пергамента/золота с виньеткой по краям, без прозрачности и без белого свечения — см. `docs/silence-curse-effect-icon-prompt.md` про разницу с иконками предметов).

```
A paralysis debuff icon for a fantasy game, matching the game's established effect-icon style. Background: a full-bleed aged parchment/old paper texture in warm golden-yellow tones, subtly darkened with a soft vignette toward the four edges — no transparency, no border, no frame. Centered on top of that background: a humanoid silhouette rigid and frozen mid-motion, crackling pale-blue electric energy arcing across its body and limbs, small jagged lightning bolts radiating outward from the figure, muscles visibly locked and tense. Square composition, symbol filling most of the frame, --ar 1:1. Bold clear silhouette, painterly game-art rendering, strong value contrast against the warm parchment background — must read clearly as a small icon.
```
