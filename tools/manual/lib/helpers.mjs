/**
 * Ext-alapú navigációs segédek a forgatókönyvekhez. Mindent az Ext API-n keresztül
 * csinálunk (fülváltás, controller-metódusok), nem pixelkoordinátákkal, így egy kis
 * elrendezés-változás nem töri el a forgatókönyveket.
 */
export function createHelpers(page) {
    const settle = (ms = 700) => page.waitForTimeout(ms);

    return {
        async tab(xtype) {
            await page.evaluate((x) => {
                const tabs = Ext.ComponentQuery.query('app-main')[0].lookup('mainTabPanel');
                tabs.setActiveItem(tabs.down(x));
            }, xtype);
            await page.waitForFunction((x) => {
                const cmp = Ext.ComponentQuery.query(x)[0];
                const store = cmp && cmp.getStore && cmp.getStore();
                return !store || (store.isLoaded() && !store.isLoading());
            }, xtype, { timeout: 15000 });
            await settle(500);
        },

        async call(xtype, method) {
            await page.evaluate(({ x, m }) => {
                Ext.ComponentQuery.query(x)[0].getController()[m]();
            }, { x: xtype, m: method });
        },

        async callWithRecord(xtype, method, pick = { index: 0 }) {
            await page.evaluate(({ x, m, p }) => {
                const grid = Ext.ComponentQuery.query(x)[0];
                const items = grid.getStore().getRange();
                let rec = items[p.index || 0];
                if (p.minVariants) {
                    rec = items.find((r) => (r.get('variant_count') || 0) >= p.minVariants) || rec;
                }
                if (!rec) {
                    throw new Error('Nincs rekord a gridben: ' + x);
                }
                const ctrl = grid.getController();
                if (m === 'onItemSelected') {
                    ctrl[m](grid, rec);
                } else {
                    ctrl[m](grid, { record: rec });
                }
            }, { x: xtype, m: method, p: pick });
        },

        async select(xtype, indexes) {
            await page.evaluate(({ x, idx }) => {
                const grid = Ext.ComponentQuery.query(x)[0];
                const recs = idx.map((i) => grid.getStore().getAt(i)).filter(Boolean);
                grid.getSelectable().select(recs);
            }, { x: xtype, idx: indexes });
            await settle(300);
        },

        async waitDialog(count = 1) {
            await page.waitForFunction(
                (n) => Ext.ComponentQuery.query('dialog').filter((d) => d.isVisible()).length >= n,
                count,
                { timeout: 15000 }
            );
            await settle();
        },

        async dialogTab(index) {
            await page.evaluate((i) => {
                const dlg = Ext.ComponentQuery.query('dialog').filter((d) => d.isVisible()).pop();
                dlg.down('tabpanel').setActiveItem(i);
            }, index);
            await settle();
        },

        async clickButton(text) {
            const id = await page.evaluate((t) => {
                const dlg = Ext.ComponentQuery.query('dialog').filter((d) => d.isVisible()).pop();
                const scope = dlg || Ext.Viewport;
                const btn = scope.query('button').find((b) => b.isVisible() && (b.getText() || '').trim() === t);
                return btn ? btn.el.dom.id : null;
            }, text);
            if (!id) {
                throw new Error(`Nem található gomb: "${text}"`);
            }
            await page.click('#' + id);
        },

        async editFirstVariantAttributes() {
            await page.evaluate(() => {
                const dlg = Ext.ComponentQuery.query('dialog').filter((d) => d.isVisible()).pop();
                const variants = dlg.lookup('variantsGrid');
                const products = Ext.ComponentQuery.query('app-products')[0];
                products.getController().onEditVariantAttributes(variants.getStore().getAt(0));
            });
        },

        async closeDialogs() {
            await page.evaluate(() => {
                Ext.ComponentQuery.query('dialog').forEach((d) => {
                    if (d === Ext.Msg) {
                        d.hide();
                    } else if (!d.destroyed) {
                        d.destroy();
                    }
                });
            });
            await settle(300);
        },

        full() {
            return { kind: 'full' };
        },

        dialog() {
            return { kind: 'dialog' };
        }
    };
}
