Ext.define('JBXAdmin.view.categories.Categories', {
    extend: 'Ext.grid.Grid',
    xtype: 'app-categories',

    controller: 'categoriescontroller',

    requires: [
        'JBXAdmin.store.CategoryStore'
    ],

    title: 'Kategóriák',

    iconCls: 'x-fa fa-tags',

    store: {
        type: 'categorystore'
    },

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
            text: 'Slug',
            width : 200,
            dataIndex: 'slug' 
        }, 
        { 
            text: 'Elérési út',
            flex : 2,
            dataIndex: 'path'
        },
        { 
            text: 'Mélység',
            width : 80,
            align : 'center',
            dataIndex: 'depth'
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
    ]
});
