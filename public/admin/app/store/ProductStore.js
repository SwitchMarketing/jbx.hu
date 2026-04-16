Ext.define('JBXAdmin.store.ProductStore', {
    extend: 'Ext.data.Store',
    storeId: 'productstore',
    alias:'store.productstore',
    proxy: {
        type: 'ajax',
        url: API.apiBase + 'products',
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
    pageSize: 25,
    sorters: [
        {
            property: 'name',
            direction: 'ASC'
        }
    ],
});