// Склейка старых CSS из public/ в один файл на страницу (меньше запросов — быстрее загрузка).
// Относительные url(../img/x.png) переписываются в абсолютные (/main/img/x.png): картинки остаются в public/.
// Запуск: npm run build (после vite build). Результат: public/build/legacy/<имя>-<hash>.css + legacy-manifest.json.
import { createHash } from 'node:crypto';
import { mkdirSync, readFileSync, writeFileSync } from 'node:fs';
import { dirname, join, posix } from 'node:path';

const publicDir = 'public';
const outDir = join(publicDir, 'build', 'legacy');

// Состав бандлов (порядок важен — как подключалось на странице): resources/legacy-css.json
const bundles = JSON.parse(readFileSync('resources/legacy-css.json', 'utf8'));

function rebase(css, file) {
    const base = '/' + posix.dirname(file);
    return css.replace(/url\(\s*(['"]?)([^'")]+)\1\s*\)/g, (match, quote, url) => {
        const value = url.trim();
        if (/^(?:[a-z]+:|\/|#)/i.test(value)) return match; // абсолютные, data:, http(s):, #id
        return `url(${quote}${posix.normalize(posix.join(base, value))}${quote})`;
    });
}

mkdirSync(outDir, { recursive: true });
const manifest = {};

for (const [name, files] of Object.entries(bundles)) {
    const css = files
        .map((file) => `/* ${file} */\n` + rebase(readFileSync(join(publicDir, file), 'utf8'), file))
        .join('\n');
    const hash = createHash('md5').update(css).digest('hex').slice(0, 10);
    const target = `build/legacy/${name}-${hash}.css`;
    writeFileSync(join(publicDir, target), css);
    manifest[name] = { file: target, sources: files };
    console.log(`legacy css: ${name} ← ${files.length} файлов → ${target} (${Math.round(css.length / 1024)} KB)`);
}

writeFileSync(join(outDir, 'legacy-manifest.json'), JSON.stringify(manifest, null, 2));
