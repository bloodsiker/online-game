# Промпты логотипа Eternum

Выбраны два варианта:

| Вариант | Что это | Где используется |
|---|---|---|
| 1 | Уроборос (знак) | фавикон, иконка игры, OG-картинка |
| 7 | Надпись ETERNUM | шапка публичных страниц инфо: `public/img/logo/eternum.png` |

Оба промпта готовы целиком: квадрат 1:1, прозрачный фон.

- **Настоящий прозрачный PNG** делает ChatGPT (генерация картинок).
- **Midjourney** прозрачность не поддерживает: там фон белый, его нужно удалить отдельно (например, remove.bg).

Палитра игры:

| Цвет | Код |
|---|---|
| Золото | `#edcb86` |
| Розовая бронза | `#B59387` |
| Тёмно-коричневый | `#281b10` |

---

## 1. Уроборос

### ChatGPT / DALL·E

```
Square 1:1 game logo emblem on a fully transparent background, PNG with alpha channel. A golden ouroboros dragon biting its own tail, forming a perfect ring, ancient runes engraved along its scales, a glowing amber gem in the center. Polished gold and bronze metal, warm rim light, rich high detail, crisp clean edges, centered and symmetrical, fantasy MMORPG style. Colors: gold #edcb86, rose-bronze #B59387, dark brown #281b10. No text, no letters, no watermark, no background, no shadow on the background.
```

### Midjourney

```
golden ouroboros dragon biting its own tail forming a perfect ring, ancient runes engraved on the scales, glowing amber gem in the center, polished gold and bronze metal, warm rim light, fantasy MMORPG logo emblem, centered, symmetrical, crisp clean edges, isolated on plain white background --ar 1:1 --style raw --v 6.1 --no text, letters, watermark, shadow, scenery
```

---

## 7. Надпись ETERNUM

### ChatGPT / DALL·E

```
Square 1:1 fantasy MMORPG title logo on a fully transparent background, PNG with alpha channel. The word "ETERNUM" in large engraved golden letters with an ornate medieval serif, ancient metal texture with fine scratches, a small golden infinity symbol above the word, faint glowing runes behind the letters, decorative flourishes at both ends. The word is centered and spelled exactly E-T-E-R-N-U-M. Warm gold and bronze, cinematic game title, high detail, crisp edges. Colors: gold #edcb86, rose-bronze #B59387, dark brown #281b10. No other text, no watermark, no background.
```

### Midjourney

```
fantasy MMORPG title logo, the word "ETERNUM" in engraved golden ornate medieval serif letters, ancient metal texture with fine scratches, small golden infinity symbol above the word, faint glowing runes behind, decorative flourishes on both sides, cinematic game title, centered, crisp edges, isolated on plain white background --ar 1:1 --style raw --v 6.1 --no watermark, shadow, scenery
```

Генерация может исказить буквы. Если в слове ошибка, перегенерируйте несколько раз.

---

## Что делать с результатом

- **Надпись (7).** Обрезать прозрачные поля вокруг слова, иначе на странице оно будет мелким. Сейчас используется `public/img/logo/Logoo500.webp` (500×167). Фрагмент `resources/views/partials/info-logo.blade.php` подхватит файл сам: на высоту до 60px, на ширину до 340px.
- **Знак (1).** Нарезать фавиконы 16, 32, 180 и 512 px и иконку для вкладки браузера.
