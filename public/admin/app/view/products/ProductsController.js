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

    onItemSelected: function (grid, record) {
        if (!record || !Ext.isFunction(record.get)) return;
        
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
                            attrs = v.attributes.map(a => `<strong>${a.name}</strong>: ${a.value}`).join(', ');
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
                    width: 650,
                    height: 550,
                    maximizable: true,
                    closeable: true,
                    layout: 'fit',
                    items: [{
                        xtype: 'panel',
                        scrollable: true,
                        padding: 20,
                        html: html
                    }],
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
        if (!record) return;

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
            title: 'Szerkesztés: ' + productData.name,
            width: 850,
            height: 650,
            closable: true,
            layout: 'fit',
            items: [{
                xtype: 'tabpanel',
                items: [
                    {
                        title: 'Alapadatok',
                        xtype: 'formpanel',
                        reference: 'mainForm',
                        scrollable: true,
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
                        reference: 'variantsGrid',
                        store: {
                            fields: ['id', 'sku', 'price', 'stock', 'attributes'],
                            data: productData.variants || []
                        },
                        columns: [
                            { text: 'SKU', dataIndex: 'sku', width: 160, editable: true },
                            { text: 'Ár (Nettó)', dataIndex: 'price', width: 130, editable: true },
                            { text: 'Készlet', dataIndex: 'stock', width: 100, editable: true },
                            { 
                                text: 'Jellemzők', 
                                flex: 1, 
                                renderer: function(v, rec) {
                                    if (!rec) return '-';
                                    const attrs = rec.get('attributes');
                                    if (!attrs || !Ext.isArray(attrs)) return '-';
                                    return attrs.map(a => `${a.name}: ${a.value}`).join(', ');
                                } 
                            },
                            {
                                width: 110,
                                cell: {
                                    tools: {
                                        attributes: {
                                            iconCls: 'x-fa fa-list',
                                            tooltip: 'Jellemzők szerkesztése',
                                            handler: (grid, info) => {
                                                this.onEditVariantAttributes(info.record);
                                            }
                                        },
                                        save: {
                                            iconCls: 'x-fa fa-save',
                                            tooltip: 'Mentés',
                                            handler: (owner) => {
                                                this.onSaveVariant(owner.getRecord());
                                            }
                                        }
                                    }
                                }
                            }
                        ],
                        plugins: {
                            cellediting: true
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
        if (!variantRecord || !Ext.isFunction(variantRecord.get)) return;
        
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
    },

    onEditVariantAttributes: function(variantRecord) {
        let attrs = variantRecord.get('attributes') || [];
        
        let attrDialog = Ext.create({
            xtype: 'dialog',
            title: 'Jellemzők szerkesztése: ' + variantRecord.get('sku'),
            width: 500,
            height: 400,
            closable: true,
            layout: 'fit',
            items: [{
                xtype: 'grid',
                reference: 'attrGrid',
                store: {
                    fields: ['attribute_id', 'name', 'value'],
                    data: attrs
                },
                columns: [
                    { 
                        text: 'Típus', 
                        dataIndex: 'attribute_id', 
                        width: 150,
                        renderer: (v) => {
                            let store = Ext.getStore('attributestore');
                            if (!store) return v;
                            let rec = store.getById(v);
                            return rec ? rec.get('name') : v;
                        },
                        editor: {
                            xtype: 'selectfield',
                            store: 'attributestore',
                            valueField: 'id',
                            displayField: 'name',
                            queryMode: 'local'
                        }
                    },
                    { text: 'Érték', dataIndex: 'value', flex: 1, editable: true },
                    {
                        width: 50,
                        cell: {
                            tools: {
                                delete: {
                                    iconCls: 'x-fa fa-trash',
                                    handler: (grid, info) => {
                                        grid.getStore().remove(info.record);
                                    }
                                }
                            }
                        }
                    }
                ],
                plugins: {
                    cellediting: true
                },
                items: [{
                    xtype: 'toolbar',
                    docked: 'top',
                    items: [
                        {
                            text: 'Új sor',
                            iconCls: 'x-fa fa-plus',
                            handler: () => {
                                attrDialog.lookup('attrGrid').getStore().add({ attribute_id: '', value: '' });
                            }
                        }
                    ]
                }]
            }],
            buttons: {
                save: {
                    text: 'Mentés',
                    handler: () => {
                        let store = attrDialog.lookup('attrGrid').getStore();
                        let data = [];
                        store.each(r => {
                            if (r.get('attribute_id') && r.get('value')) {
                                data.push({
                                    attribute_id: r.get('attribute_id'),
                                    value: r.get('value')
                                });
                            }
                        });

                        API.call({
                            url: 'productvariants/save_attributes/' + variantRecord.get('id'),
                            method: 'POST',
                            data: { attributes: data }
                        }).then((response) => {
                            if (response.success) {
                                Ext.toast('Jellemzők elmentve');
                                variantRecord.set('attributes', data);
                                variantRecord.commit();
                                attrDialog.destroy();
                            } else {
                                Ext.Msg.alert('Hiba', response.message);
                            }
                        });
                    }
                },
                cancel: {
                    text: 'Mégse',
                    handler: () => {
                        attrDialog.destroy();
                    }
                }
            }
        });

        attrDialog.show();
    }
});