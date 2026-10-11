# Промпты для иконок подменю «Инфопортал» (Форум / Библиотека / Telegram)

Реальные иконки верхнего меню лежат в спрайт-атласе
`public/data/canvas/ui/main.png` (манифест `public/data/canvas/ui/main.json`,
ключи `top/*.png`) — круглый медальон: толстое чёрное кольцо-обводка,
цветной радиальный фон под тему действия, в центре painterly пиктограмма.
Размер кадра в атласе — **57×57px** (`top/character.png` 57×58,
`top/fights.png` 57×57, `top/backPack.png` 57×57, `top/infoPortal.png`
56×56 — сам родительский пункт «Инфопортал»). Рисовать стоит в более
высоком разрешении (напр. 512×512) под тот же круглый медальон — потом
даунскейлится в атлас.

Все 3 — на прозрачном фоне (по прямому указанию пользователя), а не
заливным кругом, как у исходных `top/*.png`: сама круглая медальон-форма
(чёрное кольцо + цветной радиальный круг внутри неё) рисуется как
самостоятельный объект, а всё ВОКРУГ круга — прозрачно, без
квадратной заливки/фона.

## Форум
```
Circular medallion game UI icon (the circle itself is the artwork), thick black ring border around the edge, vivid blue radial gradient fill inside the circle (bright center, dark vignette edge), centered painterly pictogram of two crossed speech-bubble banners or an open scroll with a quill. Isolated on a fully transparent background, PNG format, only the circular medallion itself, no square background fill, no additional elements outside the circle. Fantasy-game icon style matching a hand-painted RPG menu button, bold clear silhouette readable at small size, square canvas with the circular medallion centered and filling most of the frame.
```

## Библиотека
```
Circular medallion game UI icon (the circle itself is the artwork), thick black ring border around the edge, warm brown-parchment radial gradient fill inside the circle (bright center, dark vignette edge), centered painterly pictogram of a stack of closed leather-bound books with one open book on top. Isolated on a fully transparent background, PNG format, only the circular medallion itself, no square background fill, no additional elements outside the circle. Fantasy-game icon style matching a hand-painted RPG menu button, bold clear silhouette readable at small size, square canvas with the circular medallion centered and filling most of the frame.
```

## Telegram
```
Circular medallion game UI icon (the circle itself is the artwork), thick black ring border around the edge, bright cyan-blue radial gradient fill inside the circle (bright center, dark vignette edge), centered painterly pictogram of a stylized paper airplane in flight. Isolated on a fully transparent background, PNG format, only the circular medallion itself, no square background fill, no additional elements outside the circle. Fantasy-game icon style matching a hand-painted RPG menu button, bold clear silhouette readable at small size, square canvas with the circular medallion centered and filling most of the frame.
```
