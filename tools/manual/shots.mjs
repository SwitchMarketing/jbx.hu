/**
 * Kezelői kézikönyv screenshotjainak generálása a futó lokális adminból.
 *
 *   ADMIN_USER=... ADMIN_PASS=... npm run manual:shots [-- képnév ...] [-- --selftest]
 *
 * - minden módosító API-kérés blokkolva (lib/guard.mjs)
 * - megkeresés / rendelés személyes adatai kitalált értékekre cserélve (lib/anonymize.mjs)
 */
import { chromium } from 'playwright';
import { mkdir } from 'node:fs/promises';
import path from 'node:path';
import { fileURLToPath } from 'node:url';
import { isAllowedRequest } from './lib/guard.mjs';
import { anonymizeResponse } from './lib/anonymize.mjs';
import { createHelpers } from './lib/helpers.mjs';
import { beforeLogin, scenarios } from './scenarios.mjs';

const ROOT = path.resolve(path.dirname(fileURLToPath(import.meta.url)), '../..');
const OUT_DIR = path.join(ROOT, 'public/admin/resources/help/img');
const ADMIN_URL = process.env.ADMIN_URL || 'http://localhost:3000/admin/';
const { ADMIN_USER, ADMIN_PASS } = process.env;

const args = process.argv.slice(2);
const only = args.filter((a) => !a.startsWith('--'));
const selftest = args.includes('--selftest');

if (!ADMIN_USER || !ADMIN_PASS) {
    console.error('Hiányzik az ADMIN_USER és/vagy ADMIN_PASS környezeti változó.');
    process.exit(2);
}

await mkdir(OUT_DIR, { recursive: true });

const browser = await chromium.launch();
const page = await browser.newPage({ viewport: { width: 1440, height: 900 } });
const h = createHelpers(page);
const blocked = [];
const failures = [];
let current = '(indítás)';

await page.route('**/admin/**', async (route) => {
    const req = route.request();

    // Fail closed: bármilyen hiba esetén a kérés blokkolva, feldolgozatlan válasz nem jut át.
    try {
        if (!isAllowedRequest(req.method(), req.url())) {
            blocked.push({ scenario: current, method: req.method(), url: req.url() });
            console.warn(`  ! blokkolt kérés: ${req.method()} ${req.url()} (${current})`);
            return await route.abort('blockedbyclient');
        }

        const isApiRead = req.method() === 'GET' && ['xhr', 'fetch'].includes(req.resourceType());
        if (!isApiRead) {
            return await route.continue();
        }

        const response = await route.fetch();
        const type = response.headers()['content-type'] || '';
        if (!type.includes('json')) {
            return await route.fulfill({ response });
        }

        return await route.fulfill({ response, json: anonymizeResponse(req.url(), await response.json()) });
    } catch (e) {
        const message = String((e && e.message) || e).split('\n')[0];
        blocked.push({ scenario: current, method: req.method(), url: req.url() });
        console.warn(`  ! hiba a kérés feldolgozásakor, blokkolva: ${req.method()} ${req.url()} (${current}): ${message}`);
        return route.abort('blockedbyclient').catch(() => {});
    }
});

async function capture(target, file) {
    if (target && target.kind === 'dialog') {
        const clip = await page.evaluate(() => {
            const dlg = Ext.ComponentQuery.query('dialog').filter((d) => d.isVisible()).pop();
            const r = dlg.el.dom.getBoundingClientRect();
            return { x: r.x, y: r.y, width: r.width, height: r.height };
        });
        await page.screenshot({ path: file, clip });
    } else {
        await page.screenshot({ path: file });
    }
}

async function run(list) {
    for (const scenario of list) {
        if (only.length && !only.includes(scenario.name)) {
            continue;
        }
        current = scenario.name;
        try {
            const target = await scenario.run(page, h);
            await capture(target, path.join(OUT_DIR, scenario.name + '.png'));
            console.log(`  ✓ ${scenario.name}`);
        } catch (e) {
            const msg = String(e.message || e).split('\n')[0];
            failures.push({ name: scenario.name, msg });
            console.error(`  ✗ ${scenario.name}: ${msg}`);
        } finally {
            await h.closeDialogs().catch(() => {});
        }
    }
}

async function login() {
    current = '(belépés)';
    await page.locator('input[name="uname"]').pressSequentially(ADMIN_USER);
    await page.locator('input[name="upass"]').pressSequentially(ADMIN_PASS);
    await page.locator('input[name="upass"]').press('Enter');
    try {
        await page.waitForFunction(() => Ext.ComponentQuery.query('app-main').length > 0, null, { timeout: 15000 });
    } catch (e) {
        throw new Error('Sikertelen belépés (hibás felhasználónév/jelszó, vagy nem fut az admin).');
    }
    await page.waitForTimeout(1500);
}

/** A fotózás alatt egy módosító kérésnek el kell akadnia. A settings/0 nem létezik, így
 *  akkor sem történne kár, ha az írásvédelem hibás lenne. */
async function writeProtectionSelfTest() {
    current = '(önteszt)';
    const before = blocked.length;
    await page.evaluate(() => new Promise((resolve) => {
        Ext.Ajax.request({ url: '/admin/settings/0', method: 'PUT', jsonData: { setting_value: 'x' }, callback: resolve });
    }));
    await h.closeDialogs();
    if (blocked.length === before) {
        throw new Error('Az írásvédelem NEM működik: a PUT kérés átment!');
    }
    console.log('  ✓ írásvédelem: a PUT kérés blokkolva');
}

let exitCode = 0;
try {
    console.log(`Admin: ${ADMIN_URL}`);
    await page.goto(ADMIN_URL);
    await page.waitForFunction(() => window.Ext && Ext.ComponentQuery && Ext.ComponentQuery.query('login').length > 0, null, { timeout: 30000 });
    // a browser-sync értesítője és az Ext toastok ("Nincs bejelentkezve" stb.) ne kerüljenek a képekre
    await page.addStyleTag({ content: '#__bs_notify__, .x-toast { display: none !important; }' });
    await page.waitForTimeout(800);

    await run(beforeLogin);
    await login();
    await run(scenarios);

    if (selftest) {
        await writeProtectionSelfTest();
    }
} catch (e) {
    console.error(`HIBA: ${e.message}`);
    exitCode = 1;
} finally {
    await browser.close();
}

console.log(`\nKész. Hibás forgatókönyv: ${failures.length}, blokkolt kérés: ${blocked.length}`);
if (failures.length) {
    failures.forEach((f) => console.log(`  - ${f.name}: ${f.msg}`));
    exitCode = 1;
}
process.exit(exitCode);
