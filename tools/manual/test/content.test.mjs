import { test } from 'node:test';
import assert from 'node:assert/strict';
import { readFile, readdir, access } from 'node:fs/promises';
import path from 'node:path';
import { fileURLToPath } from 'node:url';
import { beforeLogin, scenarios } from '../scenarios.mjs';

const ROOT = path.resolve(path.dirname(fileURLToPath(import.meta.url)), '../../..');
const HELP = path.join(ROOT, 'public/admin/resources/help');
const SHOT_NAMES = new Set([...beforeLogin, ...scenarios].map((s) => s.name));

async function chapters() {
    return JSON.parse(await readFile(path.join(HELP, 'index.json'), 'utf8'));
}

test('index.json: érvényes, egyedi, szabályos azonosítók', async () => {
    const list = await chapters();
    assert.ok(Array.isArray(list) && list.length > 0);
    const ids = list.map((c) => c.id);
    assert.equal(new Set(ids).size, ids.length, 'duplikált id');
    for (const c of list) {
        assert.match(c.id, /^[a-z0-9-]+$/);
        assert.ok(c.title && c.title.trim(), `hiányzó cím: ${c.id}`);
    }
});

test('minden fejezetnek van .md fájlja, és nincs árva .md', async () => {
    const ids = (await chapters()).map((c) => c.id);
    const mdFiles = (await readdir(HELP)).filter((f) => f.endsWith('.md')).map((f) => f.slice(0, -3));
    assert.deepEqual([...mdFiles].sort(), [...ids].sort());
});

test('képek: létező forgatókönyv, létező fájl', async () => {
    for (const { id } of await chapters()) {
        const md = await readFile(path.join(HELP, id + '.md'), 'utf8');
        for (const [, img] of md.matchAll(/!\[[^\]]*\]\(img\/([\w-]+)\.png\)/g)) {
            assert.ok(SHOT_NAMES.has(img), `${id}.md: "${img}" nincs a scenarios.mjs-ben`);
            await access(path.join(HELP, 'img', img + '.png')).catch(() => {
                assert.fail(`${id}.md: hiányzó kép img/${img}.png — futtasd: npm run manual:shots`);
            });
        }
    }
});

test('fejezetek közti linkek létező fejezetre mutatnak', async () => {
    const list = await chapters();
    const ids = new Set(list.map((c) => c.id));
    for (const { id } of list) {
        const md = await readFile(path.join(HELP, id + '.md'), 'utf8');
        for (const [, target] of md.matchAll(/\]\(([\w-]+)\.md(?:#[^)]*)?\)/g)) {
            assert.ok(ids.has(target), `${id}.md: ismeretlen fejezet-link: ${target}.md`);
        }
    }
});
