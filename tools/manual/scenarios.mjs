/**
 * Képenkénti forgatókönyvek. A `name` a kép fájlneve
 * (public/admin/resources/help/img/<name>.png) — a fejezetek ezen a néven hivatkoznak rá.
 * A run() a fotózandó célt adja vissza: h.full() vagy h.dialog().
 * Írni nem lehet: a futtató minden módosító kérést blokkol (lib/guard.mjs).
 */

export const beforeLogin = [
    { name: 'belepes', run: async (page, h) => h.component('login fieldset') }
];

/** Termékszerkesztő megnyitása egy legalább 2 variációs termékre. */
async function openProductEditor(h) {
    await h.tab('app-products');
    await h.callWithRecord('app-products', 'onEditItem', { minVariants: 2 });
    await h.waitDialog();
}

export const scenarios = [
    {
        name: 'foablak',
        run: async (page, h) => {
            await h.tab('app-leads');
            return h.full();
        }
    },
    {
        name: 'ikonoszlopok',
        run: async (page, h) => {
            await h.tab('app-categories');
            return h.full();
        }
    },
    {
        name: 'megkeresesek-lista',
        run: async (page, h) => {
            await h.tab('app-leads');
            return h.full();
        }
    },
    {
        name: 'megkeresesek-reszletek',
        run: async (page, h) => {
            await h.tab('app-leads');
            await h.callWithRecord('app-leads', 'onItemSelected', { index: 0 });
            await h.waitDialog();
            return h.dialog();
        }
    },
    {
        name: 'megrendelesek-lista',
        run: async (page, h) => {
            await h.tab('app-orders');
            return h.full();
        }
    },
    {
        name: 'megrendelesek-reszletek',
        run: async (page, h) => {
            await h.tab('app-orders');
            await h.callWithRecord('app-orders', 'onItemSelected', { index: 0 });
            await h.waitDialog();
            return h.dialog();
        }
    },
    {
        name: 'termekek-lista',
        run: async (page, h) => {
            await h.tab('app-products');
            return h.full();
        }
    },
    {
        name: 'uj-termek-dialog',
        run: async (page, h) => {
            await h.tab('app-products');
            await h.call('app-products', 'onCreateProduct');
            await h.waitDialog();
            return h.dialog();
        }
    },
    {
        name: 'termek-elonezet',
        run: async (page, h) => {
            await h.tab('app-products');
            await h.callWithRecord('app-products', 'onViewItem', { minVariants: 2 });
            await h.waitDialog();
            return h.dialog();
        }
    },
    {
        name: 'termek-alapadatok',
        run: async (page, h) => {
            await openProductEditor(h);
            return h.dialog();
        }
    },
    {
        name: 'termek-variaciok',
        run: async (page, h) => {
            await openProductEditor(h);
            await h.dialogTab(1);
            return h.dialog();
        }
    },
    {
        name: 'uj-variacio-dialog',
        run: async (page, h) => {
            await openProductEditor(h);
            await h.dialogTab(1);
            await h.clickButton('Új variáció');
            await h.waitDialog(2);
            return h.dialog();
        }
    },
    {
        name: 'variacio-jellemzok',
        run: async (page, h) => {
            await openProductEditor(h);
            await h.dialogTab(1);
            await h.editFirstVariantAttributes();
            await h.waitDialog(2);
            return h.full();
        }
    },
    {
        name: 'alapertelmezett-jellemzok',
        run: async (page, h) => {
            await openProductEditor(h);
            await h.dialogTab(1);
            await h.clickButton('Alapert. jellemzok');
            await h.waitDialog(2);
            return h.dialog();
        }
    },
    {
        name: 'termek-kepek',
        run: async (page, h) => {
            await openProductEditor(h);
            await h.dialogTab(2);
            return h.dialog();
        }
    },
    {
        name: 'uj-kep-dialog',
        run: async (page, h) => {
            await openProductEditor(h);
            await h.dialogTab(2);
            await h.clickButton('Uj kep');
            await h.waitDialog(2);
            return h.dialog();
        }
    },
    {
        name: 'tobb-termek-athelyezese',
        run: async (page, h) => {
            await h.tab('app-products');
            await h.select('app-products', [0, 1]);
            await h.call('app-products', 'onBulkMoveProducts');
            await h.waitDialog();
            return h.dialog();
        }
    },
    {
        name: 'kategoriak-lista',
        run: async (page, h) => {
            await h.tab('app-categories');
            return h.full();
        }
    },
    {
        name: 'kategoria-szerkesztese',
        run: async (page, h) => {
            await h.tab('app-categories');
            await h.callWithRecord('app-categories', 'onEditItem', { index: 1 });
            await h.waitDialog();
            return h.dialog();
        }
    },
    {
        name: 'uj-kategoria-dialog',
        run: async (page, h) => {
            await h.tab('app-categories');
            await h.call('app-categories', 'onCreateItem');
            await h.waitDialog();
            return h.dialog();
        }
    },
    {
        name: 'attributumok-lista',
        run: async (page, h) => {
            await h.tab('app-attributes');
            return h.full();
        }
    },
    {
        name: 'attributum-atnevezese',
        run: async (page, h) => {
            await h.tab('app-attributes');
            await h.callWithRecord('app-attributes', 'onEditItem', { index: 0 });
            await h.waitDialog();
            return h.dialog();
        }
    },
    {
        name: 'beallitasok-lista',
        run: async (page, h) => {
            await h.tab('app-settings');
            return h.full();
        }
    },
    {
        name: 'beallitas-szerkesztese',
        run: async (page, h) => {
            await h.tab('app-settings');
            await h.callWithRecord('app-settings', 'onEditItem', { index: 1 });
            await h.waitDialog();
            return h.dialog();
        }
    },
    {
        name: 'torles-megerositese',
        run: async (page, h) => {
            await h.tab('app-attributes');
            await h.callWithRecord('app-attributes', 'onDeleteItem', { index: 0 });
            await h.waitDialog();
            return h.dialog();
        }
    }
];
