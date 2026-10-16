import test from 'node:test';
import assert from 'node:assert/strict';
import fs from 'node:fs';
import path from 'node:path';
import vm from 'node:vm';

const root = new URL('../../', import.meta.url);
const read = file => fs.readFileSync(new URL(file, root), 'utf8');

function javascriptFromBlade(code) {
    let start;
    while ((start = code.search(/@(?:json|js)\(/)) >= 0) {
        let i = code.indexOf('(', start) + 1;
        let depth = 1;
        let quote = null;
        for (; i < code.length && depth; i++) {
            const c = code[i];
            if (quote) {
                if (c === '\\') i++;
                else if (c === quote) quote = null;
            } else if (c === '"' || c === "'") quote = c;
            else if (c === '(') depth++;
            else if (c === ')') depth--;
        }
        code = code.slice(0, start) + '"translated"' + code.slice(i);
    }
    return code.replace(/{{--[\s\S]*?--}}/g, '')
        .replace(/\{\{[\s\S]*?\}\}|\{!![\s\S]*?!!\}/g, '0')
        .replace(/@if[^\n]+/g, 'if (true) {')
        .replace(/@else\b/g, '} else {')
        .replace(/@endif\b/g, '}');
}

test('gameplay inline scripts remain valid after replacing translated labels', () => {
    const roots = ['Backpack', 'Item', 'Battle', 'Location'];
    for (const module of roots) {
        const dir = new URL(`app/Modules/${module}/Presentation/Views/`, root);
        for (const file of fs.readdirSync(dir, { recursive: true })) {
            if (!file.endsWith('.blade.php') || file === 'index2.blade.php') continue;
            const html = fs.readFileSync(path.join(dir.pathname, file), 'utf8');
            for (const script of html.matchAll(/<script\b[^>]*>([\s\S]*?)<\/script>/gi)) {
                try {
                    new vm.Script(javascriptFromBlade(script[1]), { filename: `${module}/${file}` });
                } catch (error) {
                    assert.fail(error.stack);
                }
            }
        }
    }
});

test('client translator substitutes parameters and uses a safe source fallback', () => {
    const partial = read('app/Modules/Interface/Presentation/Views/partials/game-translations.blade.php');
    const code = partial.match(/<script>([\s\S]*?)<\/script>/)[1]
        .replace(/@json\([^;]+\)/, JSON.stringify(JSON.parse(read('lang/uk.json'))));
    const context = { window: {} };
    vm.runInNewContext(code, context);
    assert.equal(context.window.gameTranslate('Количество использований'), 'Кількість використань');
    assert.equal(context.window.gameTranslate('Непереведённый текст'), 'Непереведённый текст');
    assert.ok(context.window.gameTranslate('Сундук уже пытается открыть :count игрок(а). Вы всё равно можете начать взлом.', { count: 2 }).includes('2 гравець'));
    assert.equal(context.window.gameTranslate('toString'), 'toString');
});

test('item tooltips obtain UI labels from the current frame language', () => {
    const context = { window: { gameTranslate: key => JSON.parse(read('lang/uk.json'))[key] ?? key } };
    const source = read('public/js/item_tooltip.js');
    const helper = source.match(/function itemTooltipTranslate\(text\) \{[\s\S]*?\n\}/)[0];
    vm.runInNewContext(helper, context);
    assert.equal(context.itemTooltipTranslate('Требования:'), 'Вимоги:');
    assert.equal(context.itemTooltipTranslate('Количество использований'), 'Кількість використань');
    delete context.window.gameTranslate;
    assert.equal(context.itemTooltipTranslate('Требования:'), 'Требования:');
});
