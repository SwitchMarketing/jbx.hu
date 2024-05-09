Ext.define('JBXAdmin.store.LeadStore', {
    extend: 'Ext.data.Store',
    storeId: 'leadstore',
    alias:'store.leadstore',
    proxy: {
        type: 'ajax',
        url: API.apiBase + 'leads',
        reader: {
            type: 'json',
            rootProperty: 'data'
        },
        actionMethods: {
            read: 'GET'
        },
        showMask: false
    },
    autoLoad: false,
    sorters: [
        {
            property: 'created_at',
            direction: 'DESC'
        }
    ],
});