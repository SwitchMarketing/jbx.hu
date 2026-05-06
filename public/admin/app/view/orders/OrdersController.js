Ext.define('JBXAdmin.view.orders.OrdersController', {
    extend: 'Ext.app.ViewController',
    alias: 'controller.orderscontroller',

    listen: {
        controller: {
            'main': {
                reloadOrders: 'onReloadOrders'
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
            view.setTitle('Megrendelések: ' + store.getTotalCount())
        }.bind(this));
        
	},

    onReloadOrders : function () { 
        this.view.getStore().reload()
    },

    onItemSelected: function (sender, record) {
        
        
        API.call({
            url: 'orders/' + record.get('id')
        }).then(function (response) {

            if(response.success) {

                var orderItemsHtml = '';
                if(response.data.items && response.data.items.length > 0) {
                    orderItemsHtml = '<tr><td colspan="2"><strong>Tételek</strong><table class="order-items" style="width:100%; border-collapse: collapse;">' +
                        '<tr style="border-bottom: 1px solid #ddd;">' +
                        '<th style="text-align: left; padding: 5px;">SKU</th>' +
                        '<th style="text-align: left; padding: 5px;">Megnevezés</th>' +
                        '<th style="text-align: right; padding: 5px;">Ár</th>' +
                        '<th style="text-align: center; padding: 5px;">Menny.</th>' +
                        '</tr>';
                    
                    response.data.items.forEach(function (item) {
                        orderItemsHtml += '<tr style="border-bottom: 1px solid #eee;">' +
                            '<td style="padding: 5px;">' + item.sku + '</td>' +
                            '<td style="padding: 5px;">' + item.name + '</td>' +
                            '<td style="text-align: right; padding: 5px;">' + item.price + ' Ft</td>' +
                            '<td style="text-align: center; padding: 5px;">' + item.qty + '</td>' +
                            '</tr>';
                    });
                    
                    orderItemsHtml += '</table></td></tr>';
                }

                var billingAddressStr = '-';
                if(response.data.billing_address) {
                    var addr = response.data.billing_address;
                    billingAddressStr = (addr.address || '') + ', ' + (addr.zip || '') + ' ' + (addr.state || '');
                }

                var deliveryAddressStr = '-';
                if(response.data.delivery_address) {
                    var addr = response.data.delivery_address;
                    deliveryAddressStr = (addr.address || '') + ', ' + (addr.zip || '') + ' ' + (addr.state || '');
                }

                var emailedAtStr = response.data.emailed_at ? response.data.emailed_at : '<span style="color: #f00;">Küldésre vár</span>';

                var html = '<table class="order" style="width: 100%; border-collapse: collapse;">' +
                                '<tr style="border-bottom: 1px solid #ddd;">' +
                                    '<td style="padding: 5px; font-weight: bold; width: 150px;">Sorszám</td>' +
                                    '<td style="padding: 5px;">' + response.data.id + '</td>' +
                                '</tr>' +
                                '<tr style="border-bottom: 1px solid #ddd;">' +
                                    '<td style="padding: 5px; font-weight: bold;">Vevő neve</td>' +
                                    '<td style="padding: 5px;">' + response.data.customer_name + '</td>' +
                                '</tr>' +
                                '<tr style="border-bottom: 1px solid #ddd;">' +
                                    '<td style="padding: 5px; font-weight: bold;">Email</td>' +
                                    '<td style="padding: 5px;"><a href="mailto:' + response.data.email + '">' + response.data.email + '</a></td>' +
                                '</tr>' +
                                '<tr style="border-bottom: 1px solid #ddd;">' +
                                    '<td style="padding: 5px; font-weight: bold;">Telefon</td>' +
                                    '<td style="padding: 5px;"><a href="tel:' + response.data.phone + '">' + response.data.phone + '</a></td>' +
                                '</tr>' +
                                '<tr style="border-bottom: 1px solid #ddd;">' +
                                    '<td style="padding: 5px; font-weight: bold;">Cég</td>' +
                                    '<td style="padding: 5px;">' + (response.data.company || '-') + '</td>' +
                                '</tr>' +
                                '<tr style="border-bottom: 1px solid #ddd;">' +
                                    '<td style="padding: 5px; font-weight: bold;">Kapcsolattartó</td>' +
                                    '<td style="padding: 5px;">' + (response.data.contact_person || '-') + '</td>' +
                                '</tr>' +
                                '<tr style="border-bottom: 1px solid #ddd;">' +
                                    '<td style="padding: 5px; font-weight: bold;">Adószám</td>' +
                                    '<td style="padding: 5px;">' + (response.data.tax_number || '-') + '</td>' +
                                '</tr>' +
                                '<tr style="border-bottom: 1px solid #ddd;">' +
                                    '<td style="padding: 5px; font-weight: bold;">Számlázási cím</td>' +
                                    '<td style="padding: 5px;">' + billingAddressStr + '</td>' +
                                '</tr>' +
                                '<tr style="border-bottom: 1px solid #ddd;">' +
                                    '<td style="padding: 5px; font-weight: bold;">Szállítási cím</td>' +
                                    '<td style="padding: 5px;">' + deliveryAddressStr + '</td>' +
                                '</tr>' +
                                '<tr style="border-bottom: 1px solid #ddd;">' +
                                    '<td style="padding: 5px; font-weight: bold;">Megjegyzés</td>' +
                                    '<td style="padding: 5px;">' + (response.data.notes || '-') + '</td>' +
                                '</tr>' +
                                '<tr style="border-bottom: 1px solid #ddd;">' +
                                    '<td style="padding: 5px; font-weight: bold;">Létrehozva</td>' +
                                    '<td style="padding: 5px;">' + response.data.created_at + '</td>' +
                                '</tr>' +
                                '<tr style="border-bottom: 1px solid #ddd;">' +
                                    '<td style="padding: 5px; font-weight: bold;">Kiküldve</td>' +
                                    '<td style="padding: 5px;">' + emailedAtStr + '</td>' +
                                '</tr>' +
                                orderItemsHtml +
                            '</table>';

                

                var dialog = Ext.create({
                    xtype: 'dialog',
                    title: 'Megrendelés #' + response.data.id,

                    platformConfig : {
                        desktop: { 
                            minWidth: 600
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
