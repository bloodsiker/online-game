# Промпт для иконки эффекта «Ожог» (debuff, id=1)

Сейчас у эффекта нет иконки (`image=null`), было только описание-заглушка (совпадало с названием). В игре уже есть два ДРУГИХ «Ожога» со своими иконками — id=11 (горящий лист, generic) и id=16 «Ожог от существа» (горящий след лапы, от укуса). Этот эффект (id=1) реально используется в двух местах: заклинание **«Огненный шар»** (magic_skill_effects) и базовая атака монстра «Мышь» (monster_effects) — источник ожога универсальный/магический, не растительный и не от когтей, поэтому визуал должен отличаться от обеих существующих иконок.

Стиль как у остальных эффектов (тёплый пергамент/золото с виньеткой, без прозрачности и без белого свечения).

```
A burn debuff icon for a fantasy game, matching the game's established effect-icon style. Background: a full-bleed aged parchment/old paper texture in warm golden-yellow tones, subtly darkened with a soft vignette toward the four edges — no transparency, no border, no frame. Centered on top of that background: a jagged scorched wound mark on skin, glowing ember-orange cracks radiating from the center like a fireball impact scar, small wisps of dark smoke curling upward, a few stray embers drifting off. Distinct from a burning leaf or a burning paw print — this is a raw scorch/impact burn, not tied to any specific creature or plant. Square composition, symbol filling most of the frame, --ar 1:1. Bold clear silhouette, painterly game-art rendering, strong value contrast against the warm parchment background — must read clearly as a small icon.
```
