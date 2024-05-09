Ext.define('JBXAdmin.view.login.LeadsController', {
    extend: 'Ext.app.ViewController',
    alias: 'controller.leadscontroller',

    listen: {
        controller: {
            'main': {
                reloadLeads: 'onReloadLeads'
            }
        }
    },

    init: function (view) {
        
        /**
         * 
         * a tároló betöltése
         * 
         */
        view.on('painted', () => {
            view.getStore().load()
        }, this);


        view.getStore().on('load', (store) => {
            view.setTitle('Megkeresések: ' + store.getTotalCount())
        });
        
	},

    onReloadLeads : function () { 
        this.view.getStore().reload()
    }
});
