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
        view.on('painted', function () {
            view.getStore().load()
        }.bind(this), this);


        view.setSelectable({
            mode : 'single'
        });

        view.getStore().on('load', function (store) {
            view.setTitle('Megkeresések: ' + store.getTotalCount())
        }.bind(this));
        
	},

    onReloadLeads : function () { 
        this.view.getStore().reload()
    },

    onItemSelected: function (sender, record) {
        
        
        API.call({
            url: 'leads/' + record.get('id')
        }).then(function (response) {

            if(response.success) {

                var files = '';
                if(response.data.files) {
                    files = response.data.files.map(function (file) {
                        return '<a href="' + API.apiBase + 'download/' + file.filename + '" target="_blank">' + file.filename + '</a>';
                    }).join('<br>')
                    files = '<tr>' +
                                '<td>Fájlok</td>' +
                                '<td>' + files + '</td>' +
                            '</tr>';
                }

                var html = '<table class="lead">' +
                                '<tr>' +
                                    '<td>Név</td>' +
                                    '<td>' + response.data.name + '</td>' +
                                '</tr>' +
                                '<tr>' +
                                    '<td>Email</td>' +
                                    '<td><a href="mailto:' + response.data.email + '">' + response.data.email + '</a></td>' +
                                '</tr>' +
                                '<tr>' +
                                    '<td>Telefon</td>' +
                                    '<td><a href="tel:' + response.data.email + '">' + response.data.phone + '</a></td>' +
                                '</tr>' +
                                '<tr>' +
                                    '<td>Cég</td>' +
                                    '<td>' + (response.data.company_name || '-') + '</td>' +
                                '</tr>' +
                                '<tr>' +
                                    '<td>Székhely</td>' +
                                    '<td>' + (response.data.company_address || '-') + '</td>' +
                                '</tr>' +
                                '<tr>' +
                                    '<td>Adószám</td>' +
                                    '<td>' + (response.data.tax_number || '-') + '</td>' +
                                '</tr>' +
                                '<tr>' +
                                    '<td>Üzenet</td>' +
                                    '<td>' + response.data.message + '</td>' +
                                '</tr>' +
                                '<tr>' +
                                    '<td>Forrás</td>' +
                                    '<td>' + (response.data.utm_source || '-') + '</td>' +
                                '</tr>' +
                                '<tr>' +
                                    '<td>Dátum</td>' +
                                    '<td>' + response.data.created_at + '</td>' +
                                '</tr>' +
                                files +
                            '</table>';

                

                var dialog = Ext.create({
                    xtype: 'dialog',
                    title: response.data.name,

                    platformConfig : {
                        desktop: { 
                            minWidth: 400
                        }
                    },
               
                    maximizable: false,
                    closeable: true,
                    html: html,
               
                    buttons: {
                        ok: function () {  // standard button (see below)
                            dialog.destroy();
                        }
                    }
                });
               
                dialog.show();

            } 

        }.bind(this)).catch(function (result) {
            
            console.error(result);

        }.bind(this));

    }

});
