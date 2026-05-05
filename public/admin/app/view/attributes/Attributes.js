Ext.define('JBXAdmin.view.attributes.Attributes', {
    extend: 'Ext.grid.Grid',
    xtype: 'app-attributes',

    controller: 'attributescontroller',

    requires: [
        'JBXAdmin.store.AttributeStore'
    ],

    title: 'Attribútumok',

    store: {
        type: 'attributestore'
    },

    columns: [
        { text: 'ID', dataIndex: 'id', width: 80 },
        { text: 'Sorrend', dataIndex: 'position', width: 100 },
        { text: 'Név', dataIndex: 'name', flex: 1 },
        {
            width: 110,
            cell: {
                tools: {
                    edit: {
                        iconCls: 'x-fa fa-edit',
                        tooltip: 'Átnevezés',
                        handler: 'onEditItem'
                    },
                    delete: {
                        iconCls: 'x-fa fa-trash',
                        tooltip: 'Törlés',
                        handler: 'onDeleteItem'
                    }
                }
            }
        }
    ],

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
