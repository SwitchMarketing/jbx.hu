/**
 * This class is the controller for the main view for the application. It is specified as
 * the "controller" of the Main view class.
 */
Ext.define('JBXAdmin.view.main.MainController', {
    extend: 'Ext.app.ViewController',

    alias: 'controller.main',

    onReload : function () { 
        this.fireEvent('reloadLeads');
    },

    onConfirmLogout: function(sender) { 

        Ext.Msg.confirm('Kilépés', 'Biztosan kijelentkezel?', 'onLogout', this);

    },

    onItemSelected: function (sender, record) {
        Ext.Msg.confirm('Confirm', 'Are you sure?', 'onConfirm', this);
    },

    onLogout: function (choice) {
        if (choice === 'yes') {
            API.logout();
        }
    }
});
