/**
 * Személyes adatok cseréje kitalált értékekre a megkeresés / rendelés API-válaszokban,
 * mielőtt az admin megkapja őket. Az érték az azonosítóból determinisztikusan képződik,
 * így a lista és a részletnézet egyezik, és újrafuttatáskor a képek nem változnak.
 */

const LAST = ['Minta', 'Példa', 'Teszt', 'Próba', 'Kovács', 'Szabó', 'Tóth', 'Horváth', 'Varga', 'Kiss'];
const FIRST = ['Péter', 'Anna', 'Gábor', 'Katalin', 'László', 'Eszter', 'Zoltán', 'Judit', 'Tamás', 'Réka'];
const COMPANY = ['Példa Kft.', 'Minta Ipari Zrt.', 'Teszt Gépgyártó Kft.', 'Demo Logisztika Bt.', 'Próba Automatika Kft.'];
const CITY = [['1111', 'Budapest'], ['6720', 'Szeged'], ['4025', 'Debrecen'], ['7621', 'Pécs'], ['9021', 'Győr']];

const LEAD_MESSAGE = 'Érdeklődnék a termékeikről, kérem küldjenek árajánlatot. (Minta üzenet)';
const ORDER_NOTES = 'Kérem, szállítás előtt telefonáljanak. (Minta megjegyzés)';

export function fakePerson(id) {
    const n = Math.abs(Number(id) || 0);
    const [zip, city] = CITY[n % CITY.length];
    const tail = String(n % 10000).padStart(4, '0');

    return {
        name: `${LAST[n % LAST.length]} ${FIRST[Math.floor(n / LAST.length) % FIRST.length]}`,
        email: `pelda${n}@example.com`,
        phone: `+36 30 000 ${tail}`,
        company: COMPANY[n % COMPANY.length],
        address: `Minta utca ${(n % 90) + 1}.`,
        zip,
        city,
        taxNumber: `1234${tail}-2-42`
    };
}

/** Csak a nem üres mezőt írja felül, így az adat "alakja" (üres / kitöltött) megmarad. */
function put(obj, key, value) {
    if (obj[key]) {
        obj[key] = value;
    }
}

export function anonymizeLead(lead, id) {
    const out = { ...lead };
    const p = fakePerson(id ?? lead.id);

    put(out, 'name', p.name);
    put(out, 'email', p.email);
    put(out, 'phone', p.phone);
    put(out, 'company_name', p.company);
    put(out, 'company_address', `${p.zip} ${p.city}, ${p.address}`);
    put(out, 'tax_number', p.taxNumber);
    put(out, 'message', LEAD_MESSAGE);

    if (Array.isArray(out.files)) {
        out.files = out.files.map((f, i) => ({ ...f, filename: `minta-melleklet-${i + 1}.pdf` }));
    }

    return out;
}

export function anonymizeOrder(order, id) {
    const out = { ...order };
    const p = fakePerson(id ?? order.id);

    put(out, 'customer_name', p.name);
    put(out, 'email', p.email);
    put(out, 'phone', p.phone);
    put(out, 'company', p.company);
    put(out, 'contact_person', p.name);
    put(out, 'tax_number', p.taxNumber);
    put(out, 'notes', ORDER_NOTES);

    for (const key of ['billing_address', 'delivery_address']) {
        if (out[key] && typeof out[key] === 'object') {
            out[key] = { ...out[key], address: p.address, zip: p.zip, state: p.city };
        }
    }

    return out;
}

/** body: a BaseResourceController borítékja ({ success, message, data, total }) */
export function anonymizeResponse(url, body) {
    const match = new URL(url).pathname.match(/\/admin\/(leads|orders)(?:\/(\d+))?\/?$/);

    if (!match || !body || body.data == null) {
        return body;
    }

    const fn = match[1] === 'leads' ? anonymizeLead : anonymizeOrder;
    const id = match[2] ? Number(match[2]) : undefined;
    const data = Array.isArray(body.data)
        ? body.data.map((row) => fn(row))
        : fn(body.data, id);

    return { ...body, data };
}
