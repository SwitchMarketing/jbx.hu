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
                const html = `<table class="lead">
                                <tr>
                                    <td>Név</td>
                                    <td>${product.name}</td>
                                </tr>
                                <tr>
                                    <td>SKU</td>
                                    <td>${product.sku}</td>
                                </tr>
                                <tr>
                                    <td>Kategória</td>
                                    <td>${product.category_name}</td>
                                </tr>
                                <tr>
                                    <td>Leírás</td>
                                    <td>${product.description || '-'}</td>
                                </tr>
                            </table>`;

                var dialog = Ext.create({
                    xtype: 'dialog',
                    title: product.name,
                    minWidth: 400,
                    maximizable: false,
                    closeable: true,
                    html: html,
                    buttons: {
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
                            { text: 'Aktív', value: 1 },
                            { text: 'Inaktív', value: 0 }
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