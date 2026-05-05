Ext.define('JBXAdmin.view.categories.Categories', {
    extend: 'Ext.grid.Grid',
    xtype: 'app-categories',

    controller: 'categoriescontroller',

    requires: [
        'JBXAdmin.store.CategoryStore'
    ],

    title: 'Kategóriák',

    iconCls: 'x-fa fa-sitemap',

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
            text: 'Kep',
            width : 220,
            dataIndex: 'image',
            cell: {
                xtype: 'gridcell',
                encodeHtml: false,
                renderer: function (value) {
                    if (!value) return '<span style="color:#999;">Nincs kép</span>';
                    var src = '/imgs/products/' + Ext.String.htmlEncode(value);
                    return '<a href="' + src + '" target="_blank" onclick="event.stopPropagation();" ' +
                        'style="display:flex;align-items:center;gap:8px;color:#1677ff;text-decoration:none;">' +
                        '<img src="' + src + '" style="width:36px;height:36px;object-fit:cover;border-radius:4px;border:1px solid #ddd;" />' +
                        '<span style="text-decoration:underline;">' + Ext.String.htmlEncode(value) + '</span>' +
                        '</a>';
                }
            }
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
            text: 'Sorrend',
            width : 80,
            align : 'center',
            dataIndex: 'order'
        },
        {
            width: 110,
            hideable: false,
            sortable: false,
            cell: {
                tools: {
                    edit: {
                        iconCls: 'x-fa fa-edit',
                        tooltip: 'Szerkesztés',
                        handler: 'onEditItem'
                    },
                    image: {
                        iconCls: 'x-fa fa-image',
                        tooltip: 'Borítókép feltöltése',
                        handler: 'onUploadImageItem'
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
                text: 'Új kategória',
                iconCls: 'x-fa fa-plus',
                handler: 'onCreateItem'
            }
        ]
    }]
});
