(function () {
    'use strict';

    // Extend the original atlas, without changing its font size, line height or baseline.
    window.extendMenuBitmapFont = function (px, font) {
        if (!font) return;

        const alias = (target, source) => {
            if (!font.chars[target]) font.chars[target] = { ...font.chars[source], kerning: {} };
        };
        alias(1110, 105); // і uses the original Latin i.
        alias(1030, 73); // І uses the original Latin I.

        const copy = (context, glyph, sx, sy, width, height, dx, dy) => {
            const frame = glyph.texture.frame;
            context.drawImage(glyph.texture.baseTexture.source,
                frame.x + sx, frame.y + sy, width, height, dx, dy, width, height);
        };

        for (const [target, source, dots] of [[1111, 105, 1105], [1031, 73, 1025]]) {
            if (font.chars[target]) continue;
            const stem = font.chars[source];
            const cap = font.chars[dots];
            const surface = document.createElement('canvas');
            surface.width = Math.max(6, stem.texture.frame.width);
            const top = Math.min(stem.yOffset, cap.yOffset);
            surface.height = stem.yOffset + stem.texture.frame.height - top;
            const context = surface.getContext('2d');
            const skip = source === 105 ? 3 : 0; // Replace i's single dot with the atlas's two dots.
            copy(context, stem, 0, skip, stem.texture.frame.width, stem.texture.frame.height - skip,
                Math.floor((surface.width - stem.texture.frame.width) / 2), stem.yOffset - top + skip);
            copy(context, cap, 0, 0, 3, 3, 0, 0);
            copy(context, cap, cap.texture.frame.width - 3, 0, 3, 3, surface.width - 3, 0);
            font.chars[target] = {
                ...stem, yOffset: top, xAdvance: Math.max(stem.xAdvance, surface.width - 1),
                kerning: {}, texture: px.Texture.fromCanvas(surface),
            };
        }

        for (const [target, source] of [[1108, 1101], [1028, 1069]]) {
            if (font.chars[target]) continue;
            const glyph = font.chars[source];
            const surface = document.createElement('canvas');
            surface.width = glyph.texture.frame.width;
            surface.height = glyph.texture.frame.height;
            const context = surface.getContext('2d');
            context.translate(surface.width, 0);
            context.scale(-1, 1);
            copy(context, glyph, 0, 0, surface.width, surface.height, 0, 0);
            font.chars[target] = { ...glyph, kerning: {}, texture: px.Texture.fromCanvas(surface) };
        }
    };
}());
