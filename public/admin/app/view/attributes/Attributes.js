Ext.define('JBXAdmin.view.attributes.Attributes', {
    extend: 'Ext.grid.Grid',
    xtype: 'app-attributes',

    controller: 'attributescontroller',

    requires: [
        'JBXAdmin.store.AttributeStore',
        'JBXAdmin.widget.ActionColumn'
    ],

    title: 'Attribútumok',

    store: {
        type: 'attributestore'
    },

    columns: [
        { text: 'ID', dataIndex: 'id', width: 80 },
        { text: 'Sorrend', dataIndex: 'position', width: 100 },
        { text: 'Név', dataIndex: 'name', flex: 1 },
        { xtype: 'jbx-actioncolumn', action: 'edit', tooltip: 'Átnevezés', handler: 'onEditItem' },
        { xtype: 'jbx-actioncolumn', action: 'delete', tooltip: 'Törlés', handler: 'onDeleteItem' }
    ],

    listeners: {
        childdoubletap: 'onItemDoubleTap'
    },

    items: [{
        xtype: 'toolbar',
        docked: 'top',
        items: [
            {
                xtype: 'textfield',
                width: 120,
                placeholder: 'Sorrend',
                reference: 'newAttrPosition',
                value: 0,
                inputType: 'number'
            },
            {
                xtype: 'textfield',
                placeholder: 'Új attribútum neve...',
                reference: 'newAttrName'
            },
            {
                text: 'Hozzáad',
                iconCls: 'x-fa fa-plus',
                handler: 'onAddAttribute'
            }
        ]
    }]
});
