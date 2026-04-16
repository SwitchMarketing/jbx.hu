Ext.define('JBXAdmin.store.AttributeStore', {
    extend: 'Ext.data.Store',
    storeId: 'attributestore',
    alias:'store.attributestore',
    proxy: {
        type: 'ajax',
        url: API.apiBase + 'attributes',
        reader: {
            type: 'json',
            rootProperty: 'data'
        },
        actionMethods: {
            read: 'GET'
        }
    },
    autoLoad: true,
    sorters: [{ property: 'name', direction: 'ASC' }]
});