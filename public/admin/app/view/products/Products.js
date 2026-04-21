Ext.define('JBXAdmin.view.products.Products', {
    extend: 'Ext.grid.Grid',
    xtype: 'app-products',

    controller: 'productscontroller',

    requires: [
        'JBXAdmin.store.ProductStore',
        'JBXAdmin.store.AttributeStore',
        'Ext.grid.plugin.PagingToolbar'
    ],

    title: 'Termékek',

    store: {
        type: 'productstore'
    },

    plugins: {
        pagingtoolbar: true
    },

    items: [{
        xtype: 'toolbar',
        docked: 'top',
        platformConfig: {
            phone: {
                layout: { type: 'vbox', align: 'stretch' }
            }
        },
        items: [
            {
                text: 'Új termék',
                iconCls: 'x-fa fa-plus',
                ui: 'action',
                handler: 'onCreateProduct',
                platformConfig: {
                    phone: { width: null, flex: null }
                }
            },
            {
                xtype: 'searchfield',
                reference: 'productSearch',
                placeholder: 'Keresés név, slug, leírás...',
                width: 280,
                responsiveConfig: {
                    'width >= 768': { flex: 1, width: null },
                    'width < 768':  { flex: null, width: 280 }
                },
                platformConfig: {
                    phone: { width: null, flex: null }
                },
                listeners: {
                    change: {
                        fn: 'onFilterChange',
                        buffer: 300
                    }
                }
            },
            {
                xtype: 'selectfield',
                reference: 'productCategoryFilter',
                width: 320,
                clearable: true,
                queryMode: 'local',
                autoComplete: true,
                forceSelection: true,
                placeholder: 'Szűrés kategóriára',
                responsiveConfig: {
                    'width >= 768': { flex: 1, width: null },
                    'width < 768':  { flex: null, width: 320 }
                },
                platformConfig: {
                    phone: { width: null, flex: null }
                },
                listeners: {
                    change: 'onFilterChange'
                }
            },
            {
                xtype: 'selectfield',
                reference: 'productStateFilter',
                width: 200,
                clearable: true,
                placeholder: 'Szűrés variáció-állapotra',
                options: [
                    { text: 'Raktáron',    value: 'instock' },
                    { text: 'Rendelésre',  value: 'backorder' },
                    { text: 'Ajánlatkérés', value: 'inquire' },
                    { text: 'Inaktív',     value: 'inactive' }
                ],
                platformConfig: {
                    phone: { width: null, flex: null }
                },
                listeners: {
                    change: 'onFilterChange'
                }
            }
        ]
    }],

    columns: [
        {
            text: 'Név',
            dataIndex: 'name',
            flex : 1,
            cell: {
                userCls: 'bold'
            }
        },
        {
            text: 'Variációk',
            width : 100,
            dataIndex: 'variant_count',
            align : 'center',
            platformConfig: { phone: { hidden: true } }
        },
        {
            text: 'Kategória',
            flex : 1,
            dataIndex: 'category_path_names',
            renderer: function (v, rec) {
                return v || (rec && rec.get('category_name')) || '';
            },
            platformConfig: { phone: { hidden: true } }
        },
        {
            text: 'Aktív',
            width : 90,
            dataIndex: 'state',
            renderer : function (val) {
                var labels = {
                    active:   'Aktív',
                    inactive: 'Inaktív'
                };
                return labels[val] || val || '';
            },
            platformConfig: { phone: { hidden: true } }
        },
        {
            width: 80,
            hideable: false,
            sortable: false,
            cell: {
                tools: {
                    edit: {
                        iconCls: 'x-fa fa-edit',
                        handler: 'onEditItem'
                    }
                }
            }
        }
    ],

    listeners: {
        select: 'onItemSelected'
    }
});
