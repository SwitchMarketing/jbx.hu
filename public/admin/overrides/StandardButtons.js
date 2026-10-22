/**
 * Egységes dialógusgombok.
 *
 * A szabványos gombkulcsok (dialog `buttons: { save: ..., cancel: ... }` és az Ext.Msg
 * gombjai) itt kapják meg a megjelenésüket és a sorrendjüket (weight):
 *  - balra a visszalépő gombok (Bezárás / Mégse / Nem): semleges `jbx-cancel`
 *  - jobbra a fő művelet (Mentés / OK / Igen / Alkalmaz): teli sárga `action`
 * A szövegeket az ext-locale-hu adja, a config összefésülődik.
 *
 * Egyedi kulcsú gomboknál (pl. create, move) a ui-t és a weight-et a dialógusban kell
 * megadni (fő művelet: ui 'action', weight 100). Törléshez: Common.confirmDelete().
 */
Ext.define('JBXAdmin.overrides.StandardButtons', {
    override: 'Ext.Panel',

    config: {
        standardButtons: {
            close : { ui: 'jbx-cancel', weight: 10 },
            cancel: { ui: 'jbx-cancel', weight: 20 },
            no    : { ui: 'jbx-cancel', weight: 30 },
            apply : { ui: 'action', weight: 80 },
            yes   : { ui: 'action', weight: 90 },
            ok    : { ui: 'action', weight: 100 },
            save  : { ui: 'action', weight: 100 },
            submit: { ui: 'action', weight: 100 }
        }
    }
});
