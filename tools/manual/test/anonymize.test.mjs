import { test } from 'node:test';
import assert from 'node:assert/strict';
import { fakePerson, anonymizeLead, anonymizeOrder, anonymizeResponse } from '../lib/anonymize.mjs';

const lead = {
    id: 63, name: 'Valódi Ernő', company_name: 'Igazi Kft.', email: 'erno@igazi.hu',
    phone: '06301234567', products: 'X-Guard', utm_source: 'google', message_length: 120,
    created_at: '2026-10-06 08:33:14'
};

const leadDetail = {
    name: 'Valódi Ernő', company_name: 'Igazi Kft.', company_address: '1234 Igazváros, Fő u. 1.',
    tax_number: '11111111-1-11', email: 'erno@igazi.hu', phone: '06301234567',
    message: 'Valódi üzenet', utm_source: 'google', created_at: '2026-10-06 08:33:14',
    files: [{ filename: 'igazi-rajz.pdf' }]
};

const orderDetail = {
    id: 3, customer_name: 'Valódi Anna', email: 'anna@igazi.hu', phone: '+36709999999',
    company: 'Igazi Zrt.', contact_person: 'Valódi Béla', tax_number: '22222222-2-22',
    billing_address: { address: 'Igazi u. 5.', zip: '1111', state: 'Igazváros' },
    delivery_address: { address: 'Igazi u. 6.', zip: '1112', state: 'Igazváros' },
    notes: 'Valódi megjegyzés', emailed_at: null, created_at: '2026-10-16 15:45:04',
    items: [{ id: 1, sku: '1152', name: 'X-Tray Mini', price: '7635.60', qty: 1, attributes: [] }]
};

const ORIGINALS = ['Valódi', 'Igazi', 'igazi', 'erno@', 'anna@', '0630123', '+3670999', '11111111', '22222222', 'Igazváros'];

function assertNoLeak(obj) {
    const json = JSON.stringify(obj);
    for (const s of ORIGINALS) {
        assert.equal(json.includes(s), false, `kiszivárgott: ${s} — ${json}`);
    }
}

test('fakePerson determinisztikus és id-nként eltér', () => {
    assert.deepEqual(fakePerson(63), fakePerson(63));
    assert.notEqual(fakePerson(1).name, fakePerson(2).name);
    assert.match(fakePerson(63).email, /@example\.com$/);
});

test('megkeresés lista-sor: személyes adat cserélve, többi marad', () => {
    const out = anonymizeLead(lead);
    assertNoLeak(out);
    assert.equal(out.id, 63);
    assert.equal(out.products, 'X-Guard');
    assert.equal(out.utm_source, 'google');
    assert.equal(out.created_at, lead.created_at);
    assert.equal(out.name, fakePerson(63).name);
    assert.equal(lead.name, 'Valódi Ernő', 'az eredeti objektum nem módosulhat');
});

test('üres mező üres marad', () => {
    const out = anonymizeLead({ ...lead, phone: '', company_name: null });
    assert.equal(out.phone, '');
    assert.equal(out.company_name, null);
});

test('megkeresés részletek: id az URL-ből, fájlnevek cserélve', () => {
    const out = anonymizeLead(leadDetail, 63);
    assertNoLeak(out);
    assert.equal(out.name, fakePerson(63).name);
    assert.deepEqual(out.files, [{ filename: 'minta-melleklet-1.pdf' }]);
});

test('rendelés részletek: címek és kapcsolattartó cserélve, tételek maradnak', () => {
    const out = anonymizeOrder(orderDetail);
    assertNoLeak(out);
    assert.equal(out.billing_address.zip, fakePerson(3).zip);
    assert.deepEqual(out.items, orderDetail.items);
});

test('anonymizeResponse: lista, részlet, és érintetlen egyéb végpont', () => {
    const list = anonymizeResponse('http://localhost:3000/admin/leads?_dc=1&page=1', { success: true, data: [lead], total: 1 });
    assertNoLeak(list);
    assert.equal(list.total, 1);

    const detail = anonymizeResponse('http://localhost:3000/admin/leads/63?_dc=1', { success: true, data: leadDetail });
    assert.equal(detail.data.name, fakePerson(63).name);

    const order = anonymizeResponse('http://localhost:3000/admin/orders/3', { success: true, data: orderDetail });
    assertNoLeak(order);

    const products = { success: true, data: [{ id: 1, name: 'Valódi termék' }] };
    assert.deepEqual(anonymizeResponse('http://localhost:3000/admin/products', products), products);

    const failed = { success: false, message: 'Hiba', data: null };
    assert.deepEqual(anonymizeResponse('http://localhost:3000/admin/leads/9', failed), failed);
});

test('ismeretlen leads/orders al-végpont hibát dob, a hasonló nevű útvonal nem', () => {
    const body = { success: true, data: [lead] };
    assert.throws(() => anonymizeResponse('http://localhost:3000/admin/leads/63/files', body), /Ismeretlen személyes adatot/);
    assert.throws(() => anonymizeResponse('http://localhost:3000/admin/orders/export', body), /Ismeretlen személyes adatot/);
    assert.deepEqual(anonymizeResponse('http://localhost:3000/admin/leadsx', body), body);
});

test('szöveges számlázási cím is cserélve, nem szivárog', () => {
    const out = anonymizeOrder({ ...orderDetail, billing_address: 'Igazi u. 5., 1111 Igazváros' });
    assertNoLeak(out);
    assert.equal(out.billing_address, `${fakePerson(3).zip} ${fakePerson(3).city}, ${fakePerson(3).address}`);
});

test('rendelés lista válasz nem szivárogtat személyes adatot', () => {
    const out = anonymizeResponse('http://localhost:3000/admin/orders', { success: true, data: [orderDetail], total: 1 });
    assertNoLeak(out);
    assert.equal(out.total, 1);
});
