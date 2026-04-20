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

    iconCls: 'x-fa fa-shopping-cart',

    store: {
        type: 'productstore'
    },

    plugins: {
        pagingtoolbar: true
    },

    items: [{
        xtype: 'toolbar',
        docked: 'top',
        items: [
            {
                xtype: 'searchfield',
                reference: 'productSearch',
                placeholder: 'Keresés név, slug, leírás, kategória...',
                width: 340,
                listeners: {
                    change: {
                        fn: 'onSearch',
                        buffer: 300
                    }
                }
            }
        ]
    }],

    columns: [
        { 
            text: 'Név',
            dataIndex: 'name',
            flex : 2,
            cell: {
                userCls: 'bold'
            }
        }, 
        {
            text: 'Variációk',
            width : 100,
            dataIndex: 'variant_count',
            align : 'center'
        }, 
        {
            text: 'Kategória',
            flex : 1,
            dataIndex: 'category_path_names',
            renderer: (v, rec) => v || (rec && rec.get('category_name')) || ''
        },
        {
            text: 'Állapot',
            width : 120,
            dataIndex: 'state',
            renderer : (val) => {
                const labels = {
                    instock:   'Raktáron',
                    backorder: 'Rendelésre',
                    inquire:   'Ajánlatkérés',
                    inactive:  'Inaktív',
                    live:      'Raktáron'
                };
                return labels[val] || val || '';
            }
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
