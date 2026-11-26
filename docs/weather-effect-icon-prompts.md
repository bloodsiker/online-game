# Промпты для иконок погодных эффектов

Эффекты погоды (`effects.slug`): `weather_cold` «Простуда» (debuff, гроза), `weather_rainbow` «Радуга» (buff, после грозы), `weather_fog` «Туман» (debuff, туман). Сейчас у всех `image=null` — показывается заглушка.

Фон по смыслу эффекта: бафф — зелёный, «Простуда» — красный, «Туман» — серый. Текстура старинной бумаги/холста с мягкой виньеткой, без прозрачности и рамки. Без людей и фигур — только предметы и природные символы. В тексте промптов нет слов про оружие, удары и силу: генератор отклонял прошлую версию «Радуги» как «насилие».

## Простуда (weather_cold) — красный фон
```
Fantasy game status-effect icon. Background: a full-bleed textured aged-paper background in deep warm crimson red, softly darkened with a vignette toward the four edges — no transparency, no border, no frame. Centered: an old glass thermometer with bright red liquid rising high, wrapped in a knitted woolen scarf covered in frost, a few icy-blue raindrops and tiny snowflakes falling around it, a small dark storm cloud above with a thin pale lightning streak. Square composition, the symbol filling most of the frame, --ar 1:1. Bold clear shapes, painterly game-art rendering, strong contrast against the red background — must read clearly as a small icon. No people, no faces, no text.
```

## Радуга (weather_rainbow) — зелёный фон
```
Fantasy game status-effect icon. Background: a full-bleed textured aged-paper background in rich fresh emerald green, softly darkened with a vignette toward the four edges — no transparency, no border, no frame. Centered: a clear faceted crystal prism catching a warm sunbeam and splitting it into a bright vivid rainbow arc, a sprouting four-leaf clover at the base of the prism, a few sparkling dewdrops glittering in the air, the last small grey cloud melting away in the corner. Cheerful, fresh, uplifting mood. Square composition, the symbol filling most of the frame, --ar 1:1. Bold clear shapes, painterly game-art rendering, strong contrast against the green background — must read clearly as a small icon. No people, no faces, no text.
```

## Туман (weather_fog) — серый фон
```
Fantasy game status-effect icon. Background: a full-bleed textured aged-paper background in cool muted slate grey, softly darkened with a vignette toward the four edges — no transparency, no border, no frame. Centered: an old brass lantern with a dim, flickering amber flame, half swallowed by thick swirling white-grey mist curling around it, and a small antique compass beside it whose needle spins aimlessly, both fading into the haze. Mysterious, disorienting mood. Square composition, the symbol filling most of the frame, --ar 1:1. Bold clear shapes, painterly game-art rendering, the warm lantern glow contrasting with the cold grey fog — must read clearly as a small icon. No people, no faces, no text.
```
