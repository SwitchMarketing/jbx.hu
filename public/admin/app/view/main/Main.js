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
        'Ext.layout.Fit'
    ],

    layout : 'fit',

    controller: 'main',
    viewModel: 'main',

    items: [
        {
            xtype : 'app-leads'
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
