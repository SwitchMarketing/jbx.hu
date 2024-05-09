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


        view.setSelectable({
            mode : 'single'
        });

        view.getStore().on('load', (store) => {
            view.setTitle('Megkeresések: ' + store.getTotalCount())
        });
        
	},

    onReloadLeads : function () { 
        this.view.getStore().reload()
    },

    onItemSelected: function (sender, record) {
        
        
        API.call({
            url: 'leads/' + record.get('id')
        }).then((response) => {

            if(response.success) {

                let files = '';
                if(response.data.files) {
                    files = response.data.files.map((file) => {
                        return `<a href="${API.apiBase}download/${file.filename}" target="_blank">${file.filename}</a>`
                    }).join('<br>')
                    files = `<tr>
                                <td>Fájlok</td>
                                <td>${files}</td>
                            </tr>`;
                }

                const html = `<table class="lead">
                                <tr>
                                    <td>Név</td>
                                    <td>${response.data.name}</td>
                                </tr>
                                <tr>
                                    <td>Email</td>
                                    <td><a href="mailto:${response.data.email}">${response.data.email}</a></td>
                                </tr>
                                <tr>
                                    <td>Telefon</td>
                                    <td><a href="tel:${response.data.email}">${response.data.phone}</a></td>
                                </tr>
                                <tr>
                                    <td>Üzenet</td>
                                    <td>${response.data.message}</td>
                                </tr>
                                <tr>
                                    <td>Forrás</td>
                                    <td>${response.data.utm_source || '-'}</td>
                                </tr>
                                <tr>
                                    <td>Dátum</td>
                                    <td>${response.data.created_at}</td>
                                </tr>
                                ${files}
                            </table>`;

                

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

        }).catch((result) => {
            
            console.error(result);

        });

    }

});
