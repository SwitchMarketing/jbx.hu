Ext.define('JBXAdmin.view.settings.Settings', {
    extend: 'Ext.grid.Grid',
    xtype: 'app-settings',

    controller: 'settingscontroller',

    requires: [
        'JBXAdmin.store.SettingStore'
    ],

    title: 'Beallitasok',
    iconCls: 'x-fa fa-cog',

    store: {
        type: 'settingstore'
    },

    columns: [
        { text: 'Kulcs', dataIndex: 'setting_key', width: 220 },
        { text: 'Ertek', dataIndex: 'setting_value', flex: 1 },
        { text: 'Tipus', dataIndex: 'data_type', width: 110 },
        { text: 'Leiras', dataIndex: 'description', flex: 1 },
        {
            width: 110,
            cell: {
                tools: {
                    edit: {
                        iconCls: 'x-fa fa-edit',
                        tooltip: 'Szerkesztes',
                        handler: 'onEditItem'
                    },
                    delete: {
                        iconCls: 'x-fa fa-trash',
                        tooltip: 'Torles',
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
                text: 'Uj beallitas',
                iconCls: 'x-fa fa-plus',
                handler: 'onCreateItem'
            }
        ]
    }]
});
