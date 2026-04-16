Ext.define('JBXAdmin.view.products.ProductsController', {
    extend: 'Ext.app.ViewController',
    alias: 'controller.productscontroller',

    listen: {
        controller: {
            'main': {
                reloadProducts: 'onReloadProducts'
            }
        }
    },

    init: function (view) {
        view.on('painted', () => {
            view.getStore().load()
        }, this);

        view.setSelectable({
            mode : 'single'
        });
	},

    onReloadProducts : function () { 
        this.getView().getStore().reload()
    },

    onItemSelected: function (sender, records) {
        let record = records[0];
        
        API.call({
            url: 'products/' + record.get('id')
        }).then((response) => {
            if(response.success) {
                const product = response.data;
                
                // Create variants HTML
                let variantsHtml = '<table class="variants-table"><tr><th>SKU</th><th>Ár</th><th>Készlet</th><th>Jellemzők</th></tr>';
                if (product.variants && product.variants.length > 0) {
                    product.variants.forEach(v => {
                        let attrs = v.attributes.map(a => `${a.name}: ${a.value}`).join(', ');
                        variantsHtml += `<tr><td>${v.sku}</td><td>${v.price} Ft</td><td>${v.stock}</td><td>${attrs}</td></tr>`;
                    });
                } else {
                    variantsHtml += '<tr><td colspan="4">Nincsenek variációk</td></tr>';
                }
                variantsHtml += '</table>';

                const html = `<div class="product-details">
                                <table class="lead">
                                    <tr><td>Név</td><td>${product.name}</td></tr>
                                    <tr><td>Kategória</td><td>${product.category_name || '-'}</td></tr>
                                    <tr><td>Leírás</td><td>${product.description || '-'}</td></tr>
                                </table>
                                <h3>Variációk</h3>
                                ${variantsHtml}
                              </div>`;

                var dialog = Ext.create({
                    xtype: 'dialog',
                    title: product.name,
                    width: 700,
                    height: 500,
                    maximizable: true,
                    closeable: true,
                    layout: 'fit',
                    scrollable: true,
                    html: html,
                    buttons: {
                        edit: {
                            text: 'Szerkesztés',
                            handler: () => {
                                dialog.destroy();
                                this.onEditItem(sender.view, { record: record });
                            }
                        },
                        ok: function () {
                            dialog.destroy();
                        }
                    }
                });
                dialog.show();
            }
        });
    },

    onEditItem: function (grid, info) {
        let record = info.record;

        let dialog = Ext.create({
            xtype: 'dialog',
            title: 'Termék szerkesztése',
            width: 500,
            closable: true,
            bodyPadding: 20,
            items: [{
                xtype: 'formpanel',
                reference: 'form',
                items: [
                    {
                        xtype: 'textfield',
                        label: 'Név',
                        name: 'name',
                        value: record.get('name'),
                        required: true
                    },
                    {
                        xtype: 'textfield',
                        label: 'Slug',
                        name: 'slug',
                        value: record.get('slug'),
                        required: true
                    },
                    {
                        xtype: 'selectfield',
                        label: 'Állapot',
                        name: 'state',
                        value: record.get('state'),
                        options: [
                            { text: 'Aktív', value: 'live' },
                            { text: 'Inaktív', value: 'draft' }
                        ]
                    },
                    {
                        xtype: 'textareafield',
                        label: 'Leírás',
                        name: 'description',
                        value: record.get('description'),
                        maxRows: 10
                    }
                ]
            }],
            buttons: {
                save: {
                    text: 'Mentés',
                    handler: function () {
                        let form = dialog.lookup('form');
                        if (form.validate()) {
                            let values = form.getValues();

                            API.call({
                                url: 'products/' + record.get('id'),
                                method: 'PUT',
                                data: values
                            }).then((response) => {
                                if (response.success) {
                                    Ext.toast('Sikeres mentés');
                                    grid.getStore().reload();
                                    dialog.destroy();
                                } else {
                                    Ext.Msg.alert('Hiba', response.message);
                                }
                            });
                        }
                    }
                },
                cancel: {
                    text: 'Mégse',
                    handler: function () {
                        dialog.destroy();
                    }
                }
            }
        });

        dialog.show();
    }
});