Ext.define('JBXAdmin.store.CategoryStore', {
    extend: 'Ext.data.Store',
    storeId: 'categorystore',
    alias:'store.categorystore',
    proxy: {
        type: 'ajax',
        url: API.apiBase + 'categories',
        reader: {
            type: 'json',
            rootProperty: 'data'
        },
        actionMethods: {
            read: 'GET'
        },
        showMask: false
    },
    autoLoad: false
});