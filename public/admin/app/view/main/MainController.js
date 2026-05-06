/**
 * This class is the controller for the main view for the application. It is specified as
 * the "controller" of the Main view class.
 */
Ext.define('JBXAdmin.view.main.MainController', {
    extend: 'Ext.app.ViewController',

    alias: 'controller.main',

    onReload : function () { 
        var tabPanel = this.lookup('mainTabPanel');
        var activeTab = tabPanel.getActiveItem();

        if (activeTab.isXType('app-leads')) {
            this.fireEvent('reloadLeads');
        } else if (activeTab.isXType('app-orders')) {
            this.fireEvent('reloadOrders');
        } else if (activeTab.isXType('app-products')) {
            this.fireEvent('reloadProducts');
        } else if (activeTab.isXType('app-categories')) {
            this.fireEvent('reloadCategories');
        } else if (activeTab.isXType('app-attributes')) {
            activeTab.getStore().reload();
        } else if (activeTab.isXType('app-settings')) {
            this.fireEvent('reloadSettings');
        }
    },

    onConfirmLogout: function(sender) { 
        Ext.Msg.confirm('Kilépés', 'Biztosan kijelentkezel?', 'onLogout', this);
    },

    onLogout: function (choice) {
        if (choice === 'yes') {
            API.logout();
        }
    }
});
