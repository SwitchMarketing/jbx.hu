/**
 * Kapcsoló (togglefield) be/ki megjelenésének javítása.
 *
 * Az Ext a be/ki állapot jelölését (x-on / x-off osztály, a gomb "toggle-on/off" ui-ja)
 * csak koppintáskor frissíti. Programból beállított értéknél (pl. a szerkesztő form
 * betöltésekor) ez elmaradt, így a kikapcsolt kapcsoló is "bekapcsolt" színű volt.
 * Itt minden értékváltozáskor szinkronizáljuk.
 */
Ext.define('JBXAdmin.overrides.ToggleSlider', {
    override: 'Ext.slider.Toggle',

    updateValue: function () {
        var me = this,
            value, isOn, onCls, offCls;

        me.callParent(arguments);

        if (!me.thumbs || !me.thumbs[0]) {
            return;
        }

        value = me.getValue();
        isOn = (Ext.isArray(value) ? value[0] : value) > 0;
        onCls = me.maxValueCls;
        offCls = me.minValueCls;

        me.element.addCls(isOn ? onCls : offCls);
        me.element.removeCls(isOn ? offCls : onCls);
        me.thumbs[0].setUi(isOn ? me.getOnThumbUi() : me.getOffThumbUi());
    }
});
