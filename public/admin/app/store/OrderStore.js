Ext.define('JBXAdmin.store.OrderStore', {
    extend: 'Ext.data.Store',
    storeId: 'orderstore',
    alias:'store.orderstore',
    proxy: {
        type: 'ajax',
        url: API.apiBase + 'orders',
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
