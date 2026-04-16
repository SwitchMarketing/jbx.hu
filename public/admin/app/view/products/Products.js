Ext.define('JBXAdmin.view.products.Products', {
    extend: 'Ext.grid.Grid',
    xtype: 'app-products',

    controller: 'productscontroller',

    requires: [
        'JBXAdmin.store.ProductStore',
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
            dataIndex: 'category_name'
        },
        { 
            text: 'Állapot',
            width : 100,
            dataIndex: 'state',
            renderer : (val) => {
                return (val == 'live') ? 'Aktív' : 'Inaktív';
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
