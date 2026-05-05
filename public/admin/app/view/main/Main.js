/**
 * This class is the main view for the application. It is specified in app.js as the
 * "mainView" property. That setting causes an instance of this class to be created and
 * added to the Viewport container.
 */
Ext.define('JBXAdmin.view.main.Main', {
    
    extend: 'Ext.Panel',
    xtype: 'app-main',

    requires: [
        'Ext.MessageBox',
        'Ext.Toast',
        'Ext.layout.Fit',
        'Ext.tab.Panel',
        'Ext.Responsive',
        'Ext.grid.plugin.CellEditing',
        'Ext.grid.cell.Widget',
        'JBXAdmin.view.leads.Leads',
        'JBXAdmin.view.products.Products',
        'JBXAdmin.view.categories.Categories',
        'JBXAdmin.view.attributes.Attributes',
        'JBXAdmin.view.settings.Settings'
    ],

    layout : 'fit',

    controller: 'main',
    viewModel: 'main',

    items: [
        {
            xtype: 'tabpanel',
            reference: 'mainTabPanel',
            tabBarPosition: 'top',
            userCls : 'main-tabs',
            items: [
                {
                    xtype: 'app-leads',
                    iconCls: 'x-fa fa-envelope'
                },
                {
                    xtype: 'app-products',
                    iconCls: 'x-fa fa-shopping-cart'
                },
                {
                    xtype: 'app-categories',
                    iconCls: 'x-fa fa-sitemap'
                },
                {
                    xtype: 'app-attributes',
                    iconCls: 'x-fa fa-tags'
                },
                {
                    xtype: 'app-settings',
                    iconCls: 'x-fa fa-cog'
                }
            ]
        },
        {
            xtype   : 'toolbar',
            docked  : 'bottom',
            defaults    : {
                iconAlign : 'left',
                textAlign : 'right'
            },
            items   : [
                {
                    text    : ' Frissít',
                    iconCls : 'x-fa fa-sync',
                    handler : 'onReload'
                },
                {
                    xtype   : 'spacer'
                },
                {
                    text    : 'Kilépés',
                    iconCls : 'x-fa fa-lock',
                    handler : 'onConfirmLogout'
                }
            ]
        }
    ]
});
