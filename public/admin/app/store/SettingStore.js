Ext.define('JBXAdmin.store.SettingStore', {
    extend: 'Ext.data.Store',
    storeId: 'settingstore',
    alias: 'store.settingstore',
    proxy: {
        type: 'ajax',
        url: API.apiBase + 'settings',
        reader: {
            type: 'json',
            rootProperty: 'data',
            totalProperty: 'total'
        },
        actionMethods: {
            read: 'GET'
        },
        showMask: false
    },
    autoLoad: false,
    sorters: [
        {
            property: 'setting_key',
            direction: 'ASC'
        }
    ],
});
