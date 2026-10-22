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
                const item = tabs.down(x);
                if (!item) {
                    throw new Error('Nincs ilyen fül: ' + x);
                }
                tabs.setActiveItem(item);
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

        /** A gomb kezelőjét közvetlenül hívja (nem egérkattintás: az a beágyazott dialógusokat
         *  nem megbízhatóan jeleníti meg, és a rejtett gombokat is el lehet érni vele). */
        async clickButton(text) {
            const ok = await page.evaluate((t) => {
                const dlg = Ext.ComponentQuery.query('dialog').filter((d) => d.isVisible()).pop();
                const scope = dlg || Ext.Viewport;
                const btn = scope.query('button').find((b) => (b.getText() || '').trim() === t);
                const fn = btn && btn.getHandler();
                if (!fn) {
                    return false;
                }
                fn.call((btn.getScope && btn.getScope()) || btn, btn);
                return true;
            }, text);
            if (!ok) {
                throw new Error(`Nem található gomb: "${text}"`);
            }
            await settle(300);
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
        },

        component(query, pad = 24) {
            return { kind: 'component', query, pad };
        }
    };
}
