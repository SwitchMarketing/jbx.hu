import { test } from 'node:test';
import assert from 'node:assert/strict';
import { isAllowedRequest } from '../lib/guard.mjs';

const BASE = 'http://localhost:3000/admin/';

test('olvasó kérések átmennek', () => {
    assert.equal(isAllowedRequest('GET', BASE + 'products?page=1'), true);
    assert.equal(isAllowedRequest('get', BASE + 'leads/5'), true);
    assert.equal(isAllowedRequest('HEAD', BASE + 'resources/help/index.json'), true);
    assert.equal(isAllowedRequest('OPTIONS', BASE + 'products'), true);
});

test('a belépés POST-ja átmegy', () => {
    assert.equal(isAllowedRequest('POST', BASE + 'login'), true);
    assert.equal(isAllowedRequest('POST', BASE + 'login/'), true);
});

test('minden módosító kérés blokkolva', () => {
    assert.equal(isAllowedRequest('PUT', BASE + 'products/12'), false);
    assert.equal(isAllowedRequest('POST', BASE + 'products'), false);
    assert.equal(isAllowedRequest('DELETE', BASE + 'categories/3'), false);
    assert.equal(isAllowedRequest('POST', BASE + 'images/upload'), false);
    assert.equal(isAllowedRequest('PATCH', BASE + 'settings/1'), false);
    assert.equal(isAllowedRequest('POST', BASE + 'loginx'), false);
});
