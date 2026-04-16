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

    onItemSelected: function (grid, records) {
        if (!records || records.length === 0) return;
        
        let record = records[0];
        
        API.call({
            url: 'products/' + record.get('id')
        }).then((response) => {
            if(response.success) {
                const product = response.data;
                
                // Create variants HTML
                let variantsHtml = '<table class="variants-table"><thead><tr><th>SKU</th><th>Ár</th><th>Készlet</th><th>Jellemzők</th></tr></thead><tbody>';
                if (product.variants && product.variants.length > 0) {
                    product.variants.forEach(v => {
                        let attrs = '';
                        if (v.attributes && v.attributes.length > 0) {
                            attrs = v.attributes.map(a => `<strong>${a.name}</strong>: ${a.value}`).join('<br>');
                        } else {
                            attrs = '-';
                        }
                        variantsHtml += `<tr><td>${v.sku}</td><td>${v.price} Ft</td><td>${v.stock}</td><td>${attrs}</td></tr>`;
                    });
                } else {
                    variantsHtml += '<tr><td colspan="4" style="text-align:center; padding: 20px;">Nincsenek variációk ehhez a termékhez.</td></tr>';
                }
                variantsHtml += '</tbody></table>';

                const html = `<div class="product-details">
                                <table class="lead">
                                    <tr><td><strong>Név</strong></td><td>${product.name}</td></tr>
                                    <tr><td><strong>Kategória</strong></td><td>${product.category_name || '-'}</td></tr>
                                    <tr><td><strong>Leírás</strong></td><td>${product.description || '-'}</td></tr>
                                </table>
                                <h3 style="margin-top: 20px;">Variációk (${product.variants ? product.variants.length : 0})</h3>
                                ${variantsHtml}
                              </div>`;

                var dialog = Ext.create({
                    xtype: 'dialog',
                    title: product.name,
                    width: '90%',
                    height: '90%',
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
                                this.onEditItem(grid, { record: record });
                            }
                        },
                        ok: {
                            text: 'Bezár',
                            handler: function () {
                                dialog.destroy();
                            }
                        }
                    }
                });
                dialog.show();
            }
        });
    },

    onEditItem: function (grid, info) {
        let record = info.record;

        API.call({
            url: 'products/' + record.get('id')
        }).then((response) => {
            if(response.success) {
                const product = response.data;
                this.showEditDialog(grid, record, product);
            }
        });
    },

    showEditDialog: function(grid, masterRecord, productData) {
        let dialog = Ext.create({
            xtype: 'dialog',
            title: 'Termék szerkesztése: ' + productData.name,
            width: '90%',
            height: '90%',
            closable: true,
            layout: 'fit',
            items: [{
                xtype: 'tabpanel',
                items: [
                    {
                        title: 'Alapadatok',
                        xtype: 'formpanel',
                        reference: 'mainForm',
                        bodyPadding: 20,
                        items: [
                            {
                                xtype: 'textfield',
                                label: 'Név',
                                name: 'name',
                                value: productData.name,
                                required: true
                            },
                            {
                                xtype: 'textfield',
                                label: 'Slug',
                                name: 'slug',
                                value: productData.slug,
                                required: true
                            },
                            {
                                xtype: 'selectfield',
                                label: 'Állapot',
                                name: 'state',
                                value: productData.state,
                                options: [
                                    { text: 'Aktív', value: 'live' },
                                    { text: 'Inaktív', value: 'draft' }
                                ]
                            },
                            {
                                xtype: 'textareafield',
                                label: 'Leírás',
                                name: 'description',
                                value: productData.description,
                                maxRows: 10
                            }
                        ]
                    },
                    {
                        title: 'Variációk',
                        xtype: 'grid',
                        store: {
                            data: productData.variants
                        },
                        columns: [
                            { text: 'SKU', dataIndex: 'sku', width: 150, editable: true },
                            { text: 'Ár (Nettó)', dataIndex: 'price', width: 120, editable: true, formatter: 'number("0,000")' },
                            { text: 'Készlet', dataIndex: 'stock', width: 100, editable: true },
                            { 
                                text: 'Jellemzők', 
                                flex: 1, 
                                renderer: (v, rec) => {
                                    return rec.get('attributes').map(a => `${a.name}: ${a.value}`).join(', ');
                                } 
                            },
                            {
                                width: 50,
                                hideable: false,
                                sortable: false,
                                cell: {
                                    tools: {
                                        save: {
                                            iconCls: 'x-fa fa-save',
                                            handler: (grid, info) => {
                                                this.onSaveVariant(info.record);
                                            }
                                        }
                                    }
                                }
                            }
                        ],
                        plugins: {
                            gridcellediting: true
                        }
                    }
                ]
            }],
            buttons: {
                save: {
                    text: 'Főadatok Mentése',
                    handler: () => {
                        let form = dialog.lookup('mainForm');
                        if (form.validate()) {
                            let values = form.getValues();

                            API.call({
                                url: 'products/' + productData.id,
                                method: 'PUT',
                                data: values
                            }).then((response) => {
                                if (response.success) {
                                    Ext.toast('Alapadatok elmentve');
                                    grid.getStore().reload();
                                } else {
                                    Ext.Msg.alert('Hiba', response.message);
                                }
                            });
                        }
                    }
                },
                close: {
                    text: 'Bezárás',
                    handler: function () {
                        dialog.destroy();
                    }
                }
            }
        });

        dialog.show();
    },

    onSaveVariant: function(variantRecord) {
        API.call({
            url: 'productvariants/' + variantRecord.get('id'),
            method: 'PUT',
            data: {
                sku: variantRecord.get('sku'),
                price: variantRecord.get('price'),
                stock: variantRecord.get('stock')
            }
        }).then((response) => {
            if (response.success) {
                Ext.toast('Variáció elmentve: ' + variantRecord.get('sku'));
                variantRecord.commit();
            } else {
                Ext.Msg.alert('Hiba', response.message);
            }
        });
    }
});