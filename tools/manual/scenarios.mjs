/**
 * Képenkénti forgatókönyvek. A `name` a kép fájlneve
 * (public/admin/resources/help/img/<name>.png) — a fejezetek ezen a néven hivatkoznak rá.
 * A run() a fotózandó célt adja vissza: h.full() vagy h.dialog().
 * Írni nem lehet: a futtató minden módosító kérést blokkol (lib/guard.mjs).
 */

export const beforeLogin = [
    { name: 'belepes', run: async (page, h) => h.full() }
];

export const scenarios = [
    {
        name: 'foablak',
        run: async (page, h) => {
            await h.tab('app-leads');
            return h.full();
        }
    }
];
