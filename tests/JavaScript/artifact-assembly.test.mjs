import assert from 'node:assert/strict';
import { readFileSync } from 'node:fs';
import test from 'node:test';
import vm from 'node:vm';

const source = readFileSync(new URL('../../public/js/artifact-assembly.js', import.meta.url), 'utf8');

test('landing always stops on backend-selected item for every pool position', () => {
    const context = { window: {}, document: { addEventListener() {} } };
    vm.runInNewContext(source, context);
    for (let length = 1; length <= 12; length++) {
        for (let current = 0; current < length; current++) {
            for (let target = 0; target < length; target++) {
                const steps = context.window.ArtifactAssembly.landingSteps(length, current, target);
                assert.equal((current + steps) % length, target);
                assert.ok(steps >= 8);
            }
        }
    }
});

test('animation moves sequentially and decelerates to selected item', async () => {
    const queue = [];
    const delays = [];
    const indices = [];
    const context = {
        window: {}, document: { addEventListener() {} },
        setTimeout(callback, delay) { queue.push(callback); delays.push(delay); },
    };
    vm.runInNewContext(source, context);
    const result = context.window.ArtifactAssembly.animateLanding(4, 1, 3, index => indices.push(index));
    while (queue.length) queue.shift()();
    assert.equal(await result, 3);
    assert.equal(indices.at(-1), 3);
    indices.forEach((index, step) => assert.equal(index, (step + 2) % 4));
    delays.forEach((delay, step) => { if (step > 0) assert.ok(delay >= delays[step - 1]); });
    assert.ok(delays.at(-1) > delays[0] * 4);
});
