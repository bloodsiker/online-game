# Промпт для картинки компаса перемещения

Новый компас на странице локации (`app/Modules/Location/Presentation/Views/index.blade.php`,
класс `.compass`) — растровое фоновое изображение 180×180 (CSS
`background-size: contain`) + 4 CSS-кликабельные зоны (N/E/S/W) поверх
него, плюс отдельная пара кнопок ▲/▼ под компасом для «вверх»/«вниз».

Путь: `public/img/compass-rose.png` — положить файл сюда, в коде уже
подключено через `asset('img/compass-rose.png')`.

Прозрачный фон (чтобы ложился поверх тёмного фона страницы без
квадратной рамки), сама роза ветров — декоративный элемент, стороны
света кликаются НЕВИДИМЫМИ зонами поверх неё (текст/подпись рисует CSS,
не сама картинка) — поэтому на картинке не обязательны текстовые
подписи N/S/E/W, важнее читаемый силуэт и 4 выраженных луча по
горизонтали/вертикали, на которые визуально лягут кнопки.

```
An ornate antique brass and parchment compass rose, fantasy RPG map decoration, engraved metallic rays radiating from a central medallion, four main cardinal points (north/south/east/west) emphasized as longer, more ornate spikes than the intercardinal ones, aged weathered brass texture with warm golden-bronze tones, subtle parchment-colored accents, intricate engraved linework, painterly semi-realistic fantasy game asset style. Isolated on a fully transparent background, PNG format, no background elements, no text or letter labels on the rose itself. Square composition, the compass rose centered and filling most of the frame, symmetrical, bold readable silhouette.
```

## Кнопки «вверх»/«вниз» (.compass-dir-up / .compass-dir-down)

Уже загружены как `public/img/up.png`/`public/img/down.png` (подключены
через `background-image` в `.compass-dir-up`/`.compass-dir-down`,
38×38, диагонально справа от компаса).

Проблема текущей версии: наконечник нарисован на ОБОИХ концах
(остриё сверху и снизу у «up.png», остриё снизу и сверху у «down.png»)
— получился двусторонний кинжал/шип, направление движения
неочевидно, да и сам стержень слишком тонкий и ажурный, выглядит
хрупко. Переписанные промпты ниже требуют явно ОДНО остриё и
тупой/плоский хвост с противоположной стороны, более толстый и
массивный стержень.

### Вверх
```
A single sturdy antique brass arrow pointing straight up, ONE clear arrowhead only at the top — wide, solid, unmistakably pointing upward, engraved metallic texture matching a weathered compass rose, warm golden-bronze tones. The bottom end is a flat, blunt, thick stub (NOT another point, NOT a spike, NOT symmetrical) — direction must read instantly and unambiguously at a glance. Thick, heavy, solid shaft (not thin or delicate), bold simple readable silhouette, painterly semi-realistic fantasy game icon style. Isolated on a fully transparent background, PNG format, no background elements, no text. Square composition, the arrow centered and filling most of the frame.
```

### Вниз
```
A single sturdy antique brass arrow pointing straight down, ONE clear arrowhead only at the bottom — wide, solid, unmistakably pointing downward, engraved metallic texture matching a weathered compass rose, warm golden-bronze tones. The top end is a flat, blunt, thick stub (NOT another point, NOT a spike, NOT symmetrical) — direction must read instantly and unambiguously at a glance. Thick, heavy, solid shaft (not thin or delicate), bold simple readable silhouette, painterly semi-realistic fantasy game icon style. Isolated on a fully transparent background, PNG format, no background elements, no text. Square composition, the arrow centered and filling most of the frame.
```
