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
        view.on('painted', function () {
            view.getStore().load()
        }.bind(this), this);

        view.setSelectable({
            mode : 'single'
        });

        // Populate the category filter with leaf categories only (products can
        // only live in leaves per the update validation). Labels use the full
        // breadcrumb path so the dropdown is self-describing.
        var categoryStore = Ext.getStore('categorystore') || Ext.create('JBXAdmin.store.CategoryStore');
        var me = this;
        var populate = function () {
            var byId = {};
            var parentIds = new Set();
            categoryStore.each(function (r) {
                byId[r.get('unas_id')] = r;
                parentIds.add(String(r.get('parent_id')));
            });
            var options = [];
            categoryStore.each(function (r) {
                if (parentIds.has(String(r.get('unas_id')))) return;
                var ids = (r.get('pathIds') || '').toString().split('/').filter(Boolean);
                if (!ids.length) ids = [r.get('unas_id')];
                var text = ids.map(function (id) { return byId[id] ? byId[id].get('name') : id; }).join(' › ');
                options.push({ text: text, value: r.get('unas_id') });
            });
            options.sort(function (a, b) { return a.text.localeCompare(b.text, 'hu'); });

            var f = me.lookup('productCategoryFilter');
            if (f) f.setOptions(options);
        };

        if (categoryStore.getCount() > 0) {
            populate();
        } else {
            categoryStore.on('load', populate, this, { single: true });
            if (!categoryStore.isLoading()) categoryStore.load();
        }
	},

    onReloadProducts : function () {
        this.getView().getStore().reload()
    },

    onFilterChange: function() {
        var store = this.getView().getStore();
        var proxy = store.getProxy();

        var search = this.lookup('productSearch');
        var catFilter = this.lookup('productCategoryFilter');
        var stateFilter = this.lookup('productStateFilter');

        var term  = (search ? (search.getValue() || '') : '').trim();
        var catId = catFilter ? catFilter.getValue() : null;
        var state = stateFilter ? stateFilter.getValue() : null;

        var filters = [];
        if (term)  filters.push({ property: 'qstring', value: term });
        if (catId) filters.push({ property: 'product_masters.category_id', operator: 'eq', value: String(catId) });
        if (state) filters.push({ property: 'variant_state', operator: 'eq', value: state });

        proxy.setExtraParam('filter', filters.length ? Ext.encode(filters) : null);
        store.loadPage(1);
    },

    onItemSelected: function (grid, record) {
        if (!record || !Ext.isFunction(record.get)) return;

        API.call({
            url: 'products/' + record.get('id')
        }).then(function (response) {
            if(response.success) {
                var product = response.data;
                
                // Create variants HTML
                var stateLabels = {
                    instock:   'Raktáron',
                    backorder: 'Rendelésre',
                    inquire:   'Ajánlatkérés',
                    inactive:  'Inaktív'
                };
                var variantsHtml = '<table class="variants-table"><thead><tr><th>SKU</th><th>Név</th><th>Ár</th><th>Készlet</th><th>Állapot</th><th>Jellemzők</th></tr></thead><tbody>';
                if (product.variants && product.variants.length > 0) {
                    product.variants.forEach(function (v) {
                        var attrs = '';
                        if (v.attributes && v.attributes.length > 0) {
                            attrs = v.attributes.map(function (a) { return '<strong>' + a.name + '</strong>: ' + a.value; }).join(', ');
                        } else {
                            attrs = '-';
                        }
                        var stateLabel = stateLabels[v.state] || v.state || '-';
                        variantsHtml += '<tr><td>' + v.sku + '</td><td>' + (v.name || '-') + '</td><td>' + v.price + ' Ft</td><td>' + v.stock + '</td><td>' + stateLabel + '</td><td>' + attrs + '</td></tr>';
                    });
                } else {
                    variantsHtml += '<tr><td colspan="6" style="text-align:center; padding: 20px;">Nincsenek variációk ehhez a termékhez.</td></tr>';
                }
                variantsHtml += '</tbody></table>';

                var html = '<div class="jbx-paper product-details">' +
                                '<table class="lead">' +
                                    '<tr><td style="width:140px;"><strong>Név</strong></td><td>' + product.name + '</td></tr>' +
                                    '<tr><td><strong>Kategória</strong></td><td>' + (product.category_path_names || product.category_name || '-') + '</td></tr>' +
                                    '<tr><td><strong>Leírás</strong></td><td>' + (product.description || '-') + '</td></tr>' +
                                '</table>' +
                                '<h3>Variációk (' + (product.variants ? product.variants.length : 0) + ')</h3>' +
                                variantsHtml +
                              '</div>';

                var dialog = Ext.create({
                    xtype: 'dialog',
                    title: product.name,
                    width: 650,
                    height: 550,
                    maximizable: true,
                    closeable: true,
                    layout: 'fit',
                    platformConfig: {
                        phone: { maximized: true, width: null, height: null }
                    },
                    items: [{
                        xtype: 'panel',
                        scrollable: true,
                        padding: 20,
                        html: html
                    }],
                    buttons: {
                        edit: {
                            text: 'Szerkesztés',
                            handler: function () {
                                dialog.destroy();
                                this.onEditItem(grid, { record: record });
                            }.bind(this)
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
        }.bind(this));
    },

    onEditItem: function (grid, info) {
        var record = info.record;
        if (!record) return;

        // Highlight the row via a CSS class on the DOM row element so the user
        // keeps visual track of which product is being edited. Avoids touching
        // the Modern selection model (which throws on some setSelection paths).
        var view = this.getView();
        var rowEl = null;
        if (view && view.el) {
            var prev = view.el.dom.querySelector('.jbx-editing-row');
            if (prev) prev.classList.remove('jbx-editing-row');

            var id = record.getId ? record.getId() : record.get('id');
            if (info && info.cell && info.cell.el && info.cell.el.dom) {
                rowEl = info.cell.el.dom.closest('.x-gridrow, .x-listitem');
            }
            if (!rowEl) {
                rowEl = view.el.dom.querySelector('[data-recordid="' + id + '"]');
            }
            if (rowEl) rowEl.classList.add('jbx-editing-row');
        }

        var me = this;

        // Ensure categories are loaded so the parent selectfield has options.
        var categoryStore = Ext.getStore('categorystore') || Ext.create('JBXAdmin.store.CategoryStore');
        var loadCategories = new Promise(function (resolve) {
            if (categoryStore.getCount() > 0 || categoryStore.isLoading()) {
                categoryStore.on('load', function () { resolve(); }, { single: true });
                if (!categoryStore.isLoading()) resolve();
            } else {
                categoryStore.load({ callback: function () { resolve(); } });
            }
        });

        Promise.all([
            API.call({ url: 'products/' + record.get('id') }),
            loadCategories
        ]).then(function (results) {
            var response = results[0];
            if (response.success) {
                me.showEditDialog(grid, record, response.data, categoryStore);
            }
        });
    },

    showEditDialog: function(grid, masterRecord, productData, categoryStore) {
        // Build category options with full breadcrumb path ("Root › Sub › Leaf")
        // so the selected value is self-describing both open and closed.
        var categoryOptions = [];
        var categoryPathById = {};
        if (categoryStore) {
            var byId = {};
            categoryStore.each(function (r) { byId[r.get('unas_id')] = r; });

            var pathOf = function (rec) {
                var ids = (rec.get('pathIds') || '').toString().split('/').filter(Boolean);
                if (!ids.length) ids = [rec.get('unas_id')];
                return ids.map(function (id) { return byId[id] ? byId[id].get('name') : id; }).join(' › ');
            };

            categoryStore.each(function (r) {
                var full = pathOf(r);
                categoryPathById[r.get('unas_id')] = full;
                categoryOptions.push({
                    text: full,
                    value: r.get('unas_id')
                });
            });
            categoryOptions.sort(function (a, b) { return a.text.localeCompare(b.text, 'hu'); });
        }

        var currentCategoryPath = categoryPathById[productData.category_id] || productData.category_path_names || '-';

        var dialog = Ext.create({
            xtype: 'dialog',
            title: 'Szerkesztés: ' + productData.name,
            width: 1100,
            height: 700,
            closable: true,
            maximizable: true,
            referenceHolder: true,
            layout: 'fit',
            platformConfig: {
                phone: { maximized: true, width: null, height: null }
            },
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
                                xtype: 'container',
                                margin: '0 0 8 0',
                                items: [
                                    {
                                        xtype: 'component',
                                        html: '<div style="font-size:12px;color:rgba(0,0,0,.54);padding:0 0 4px 0;">Jelenlegi kategória</div>' +
                                              '<div style="font-size:13px;padding:6px 10px;background:#21465b;color:#fff;border-radius:4px;">' +
                                              Ext.String.htmlEncode(currentCategoryPath) +
                                              '</div>'
                                    }
                                ]
                            },
                            {
                                xtype: 'selectfield',
                                label: 'Kategória áthelyezése',
                                name: 'category_id',
                                value: productData.category_id,
                                options: categoryOptions,
                                queryMode: 'local',
                                autoComplete: true,
                                forceSelection: true,
                                clearable: false,
                                required: true
                            },
                            {
                                xtype: 'textfield',
                                label: 'Egység',
                                name: 'unit',
                                value: productData.unit,
                                placeholder: 'pl. db, m, csomag'
                            },
                            {
                                xtype: 'togglefield',
                                label: 'Aktív',
                                name: 'state',
                                value: productData.state !== 'inactive',
                                reference: 'masterStateToggle'
                            },
                            {
                                xtype: 'container',
                                reference: 'descEditor',
                                padding: '10 0 0 0',
                                items: [
                                    {
                                        xtype: 'component',
                                        html: '<div style="font-size:12px;color:rgba(0,0,0,.54);padding:0 0 4px 0;">Leírás</div>'
                                    },
                                    {
                                        xtype: 'component',
                                        cls: 'jbx-quill-wrap',
                                        html: '<div class="jbx-quill-host" style="height:280px;background:#fff;"></div>'
                                    }
                                ],
                                listeners: {
                                    painted: function () {
                                        if (this.quillInstance) return;
                                        var hostEl = this.el.down('.jbx-quill-host');
                                        if (!hostEl || typeof Quill === 'undefined') return;

                                        this.quillInstance = new Quill(hostEl.dom, {
                                            theme: 'snow',
                                            modules: {
                                                toolbar: [
                                                    [{ 'header': [1, 2, 3, false] }],
                                                    ['bold', 'italic', 'underline', 'strike'],
                                                    [{ 'list': 'ordered' }, { 'list': 'bullet' }],
                                                    ['link'],
                                                    ['clean']
                                                ]
                                            }
                                        });
                                        this.quillInstance.root.innerHTML = productData.description || '';
                                    }
                                }
                            }
                        ]
                    },
                    {
                        title: 'Variációk',
                        xtype: 'grid',
                        reference: 'variantsGrid',
                        userCls: 'jbx-themed-grid',
                        store: {
                            fields: ['id', 'unas_id', 'sku', 'name', 'price', 'stock', 'state', 'attributes'],
                            data: productData.variants || [],
                            listeners: {
                                update: function (store, record, operation, modifiedFieldNames) {
                                    if (operation !== Ext.data.Model.EDIT) return;
                                    if (!record.get('id')) return;
                                    var editable = ['sku', 'name', 'price', 'stock', 'state'];
                                    if (!modifiedFieldNames || !modifiedFieldNames.some(function (f) { return editable.indexOf(f) !== -1; })) return;
                                    this.onSaveVariant(record);
                                }.bind(this)
                            }
                        },
                        columns: [
                            { text: 'UNAS ID', dataIndex: 'unas_id', width: 90, hidden: true },
                            { text: 'SKU', dataIndex: 'sku', width: 140, editable: true },
                            { text: 'Név', dataIndex: 'name', flex: 1, minWidth: 200, editable: true },
                            { text: 'Ár (Nettó)', dataIndex: 'price', width: 120, editable: true },
                            { text: 'Készlet', dataIndex: 'stock', width: 90, editable: true },
                            {
                                text: 'Állapot',
                                dataIndex: 'state',
                                width: 140,
                                editable: true,
                                renderer: function (v) {
                                    var labels = {
                                        instock:   'Raktáron',
                                        backorder: 'Rendelésre',
                                        inquire:   'Ajánlatkérés',
                                        inactive:  'Inaktív'
                                    };
                                    return labels[v] || v || '';
                                },
                                editor: {
                                    xtype: 'selectfield',
                                    queryMode: 'local',
                                    autoComplete: false,
                                    clearable: false,
                                    options: [
                                        { text: 'Raktáron',    value: 'instock' },
                                        { text: 'Rendelésre',  value: 'backorder' },
                                        { text: 'Ajánlatkérés', value: 'inquire' },
                                        { text: 'Inaktív',     value: 'inactive' }
                                    ]
                                }
                            },
                            {
                                text: 'Jellemzők',
                                flex: 1, 
                                renderer: function(v, rec) {
                                    if (!rec) return '-';
                                    var attrs = rec.get('attributes');
                                    if (!attrs || !Ext.isArray(attrs)) return '-';
                                    return attrs.map(function (a) { return a.name + ': ' + a.value; }).join(', ');
                                } 
                            },
                            {
                                width: 100,
                                cell: {
                                    tools: {
                                        attributes: {
                                            iconCls: 'x-fa fa-list',
                                            tooltip: 'Jellemzők szerkesztése',
                                            handler: function (grid, info) {
                                                this.onEditVariantAttributes(info.record);
                                            }.bind(this)
                                        },
                                        delete: {
                                            iconCls: 'x-fa fa-trash',
                                            tooltip: 'Variáció törlése',
                                            handler: function (grid, info) {
                                                this.onDeleteVariant(info.record, grid.getStore());
                                            }.bind(this)
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
                                    text: 'Új variáció',
                                    iconCls: 'x-fa fa-plus',
                                    ui: 'action',
                                    handler: function () {
                                        this.onCreateVariant(productData.id, dialog.lookup('variantsGrid'));
                                    }.bind(this)
                                },
                                {
                                    text: 'Jellemzők a névből',
                                    iconCls: 'x-fa fa-magic',
                                    tooltip: 'Attribútumok kinyerése a variációk nevéből egy minta alapján',
                                    hidden: true,
                                    handler: function () {
                                        this.onParseVariantNames(productData.id, dialog.lookup('variantsGrid'));
                                    }.bind(this)
                                }
                            ]
                        }]
                    }
                ]
            }],
            buttons: {
                save: {
                    text: 'Főadatok Mentése',
                    handler: function () {
                        var form = dialog.lookup('mainForm');
                        if (form.validate()) {
                            var values = form.getValues();

                            var editor = dialog.lookup('descEditor');
                            if (editor && editor.quillInstance) {
                                var html = editor.quillInstance.root.innerHTML;
                                values.description = (html === '<p><br></p>') ? '' : html;
                            }

                            // togglefield submits a boolean; controller expects 'active'/'inactive'
                            values.state = values.state ? 'active' : 'inactive';

                            API.call({
                                url: 'products/' + productData.id,
                                method: 'PUT',
                                data: values
                            }).then(function (response) {
                                if (response.success) {
                                    Ext.toast('Alapadatok elmentve');
                                    grid.getStore().reload();
                                } else {
                                    Ext.Msg.alert('Hiba', response.message);
                                }
                            });
                        }
                    }.bind(this)
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

    onCreateVariant: function(masterId, variantsGrid) {
        Ext.Msg.prompt('Új variáció', 'SKU:', function (btn, sku) {
            if (btn !== 'ok') return;
            sku = (sku || '').trim();
            if (!sku) { Ext.Msg.alert('Hiba', 'A SKU megadása kötelező'); return; }

            API.call({
                url: 'productvariants',
                method: 'POST',
                data: { master_id: masterId, sku: sku }
            }).then(function (response) {
                if (!response.success) {
                    Ext.Msg.alert('Hiba', response.message);
                    return;
                }
                var newId = response.data && response.data.id;
                variantsGrid.getStore().add({
                    id: newId,
                    unas_id: null,
                    sku: sku,
                    name: null,
                    price: 0,
                    stock: 0,
                    attributes: []
                });
                Ext.toast('Új variáció létrehozva: ' + sku);
            });
        }.bind(this));
    },

    onDeleteVariant: function(variantRecord, store) {
        if (!variantRecord || !variantRecord.get('id')) {
            if (store && variantRecord) store.remove(variantRecord);
            return;
        }

        Ext.Msg.confirm(
            'Megerősítés',
            'Biztosan törlöd a(z) "' + (variantRecord.get('sku') || variantRecord.get('id')) + '" variációt?',
            function (choice) {
                if (choice !== 'yes') return;
                API.call({
                    url: 'productvariants/' + variantRecord.get('id'),
                    method: 'DELETE'
                }).then(function (response) {
                    if (response.success) {
                        store.remove(variantRecord);
                        Ext.toast(response.message || 'Variáció törölve');
                    } else {
                        Ext.Msg.alert('Hiba', response.message);
                    }
                });
            }.bind(this)
        );
    },

    onSaveVariant: function(variantRecord) {
        if (!variantRecord || !Ext.isFunction(variantRecord.get)) return;
        
        API.call({
            url: 'productvariants/' + variantRecord.get('id'),
            method: 'PUT',
            data: {
                sku: variantRecord.get('sku'),
                name: variantRecord.get('name'),
                price: variantRecord.get('price'),
                stock: variantRecord.get('stock'),
                state: variantRecord.get('state')
            }
        }).then(function (response) {
            if (response.success) {
                Ext.toast('Variáció elmentve: ' + variantRecord.get('sku'));
                variantRecord.commit();
            } else {
                Ext.Msg.alert('Hiba', response.message);
            }
        });
    },

    onParseVariantNames: function(masterId, variantsGrid) {
        var me = this;

        var dialog = Ext.create({
            xtype: 'dialog',
            title: 'Jellemzők kinyerése a variáció-nevekből',
            width: 780,
            height: 580,
            closable: true,
            referenceHolder: true,
            layout: 'vbox',
            items: [
                {
                    xtype: 'component',
                    padding: '12 16 0 16',
                    html: '<div style="font-size:12px;line-height:1.5;">' +
                          'Adj meg egy mintát <code>{{Jellemző}}</code> helyőrzőkkel. Például:<br>' +
                          '<code>Kerítéselem, {{Szélesség}}x{{Magasság}} mm</code><br>' +
                          'Az egyező variációkból kinyert értékek lesznek a jellemzők. Az <em>Előnézet</em> ' +
                          'csak mutatja az eredményt, az <em>Alkalmaz</em> írja adatbázisba (a meglévő jellemzőket felülírja).' +
                          '</div>'
                },
                {
                    xtype: 'textfield',
                    reference: 'patternField',
                    label: 'Minta',
                    labelAlign: 'top',
                    padding: '8 16',
                    placeholder: 'pl. Kerítéselem, {{Szélesség}}x{{Magasság}} mm'
                },
                {
                    xtype: 'grid',
                    reference: 'previewGrid',
                    userCls: 'jbx-themed-grid',
                    flex: 1,
                    store: {
                        fields: ['variant_id', 'name', 'matched', 'extracted']
                    },
                    columns: [
                        {
                            text: '',
                            width: 40,
                            dataIndex: 'matched',
                            renderer: function (v) {
                                return v
                                    ? '<span style="color:#2e7d32;">●</span>'
                                    : '<span style="color:#c62828;">○</span>';
                            }
                        },
                        { text: 'Név', dataIndex: 'name', flex: 1 },
                        {
                            text: 'Kinyert jellemzők',
                            flex: 1,
                            dataIndex: 'extracted',
                            renderer: function (v) {
                                if (!v || typeof v !== 'object') return '';
                                var keys = Object.keys(v);
                                if (!keys.length) return '<em style="opacity:.6;">nincs találat</em>';
                                return keys.map(function (k) { return '<strong>' + k + '</strong>: ' + v[k]; }).join(', ');
                            }
                        }
                    ]
                }
            ],
            buttons: {
                preview: {
                    text: 'Előnézet',
                    iconCls: 'x-fa fa-eye',
                    handler: function () {
                        var pattern = dialog.lookup('patternField').getValue();
                        if (!pattern) { Ext.Msg.alert('Hiba', 'Add meg a mintát'); return; }

                        API.call({
                            url: 'productvariants/parse_names/' + masterId,
                            method: 'POST',
                            data: { pattern: pattern, apply: 0 }
                        }).then(function (response) {
                            if (!response.success) {
                                Ext.Msg.alert('Hiba', response.message);
                                return;
                            }
                            var store = dialog.lookup('previewGrid').getStore();
                            store.loadData(response.data.matches || []);

                            var attrs = response.data.attributes || {};
                            var newAttrs = Object.keys(attrs).filter(function (k) { return !attrs[k].existing; });
                            if (newAttrs.length) {
                                Ext.toast('Új attribútumok lesznek létrehozva: ' + newAttrs.join(', '));
                            }
                        });
                    }.bind(this)
                },
                apply: {
                    text: 'Alkalmaz',
                    iconCls: 'x-fa fa-check',
                    ui: 'action',
                    handler: function () {
                        var pattern = dialog.lookup('patternField').getValue();
                        if (!pattern) { Ext.Msg.alert('Hiba', 'Add meg a mintát'); return; }

                        Ext.Msg.confirm(
                            'Megerősítés',
                            'Az egyező variációk meglévő jellemzőit felülírja. Folytatod?',
                            function (choice) {
                                if (choice !== 'yes') return;
                                API.call({
                                    url: 'productvariants/parse_names/' + masterId,
                                    method: 'POST',
                                    data: { pattern: pattern, apply: 1 }
                                }).then(function (response) {
                                    if (!response.success) {
                                        Ext.Msg.alert('Hiba', response.message);
                                        return;
                                    }
                                    Ext.toast(response.message);
                                    dialog.destroy();

                                    // Refresh the variants grid inside the edit dialog by re-fetching the master
                                    if (variantsGrid) {
                                        API.call({ url: 'products/' + masterId }).then(function (r) {
                                            if (r.success && r.data && variantsGrid.getStore) {
                                                variantsGrid.getStore().loadData(r.data.variants || []);
                                            }
                                        });
                                    }
                                });
                            }.bind(this)
                        );
                    }.bind(this)
                },
                cancel: {
                    text: 'Mégse',
                    handler: function () { dialog.destroy(); }
                }
            }
        });

        dialog.show();
    },

    onEditVariantAttributes: function(variantRecord) {
        var attrs = variantRecord.get('attributes') || [];
        
        var attrDialog = Ext.create({
            xtype: 'dialog',
            title: 'Jellemzők szerkesztése: ' + variantRecord.get('sku'),
            width: 500,
            height: 400,
            closable: true,
            referenceHolder: true,
            layout: 'fit',
            items: [{
                xtype: 'grid',
                reference: 'attrGrid',
                userCls: 'jbx-themed-grid',
                store: {
                    fields: ['attribute_id', 'name', 'value'],
                    data: attrs
                },
                columns: [
                    {
                        text: 'Típus',
                        dataIndex: 'attribute_id',
                        width: 150,
                        editable: true,
                        renderer: function (v, record) {
                            if (record && record.get('name')) return record.get('name');
                            var store = Ext.getStore('attributestore');
                            var rec = store && v ? store.getById(v) : null;
                            return rec ? rec.get('name') : (v || '');
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
                                    handler: function (grid, info) {
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
                            handler: function () {
                                attrDialog.lookup('attrGrid').getStore().add({ attribute_id: '', value: '' });
                            }
                        }
                    ]
                }]
            }],
            buttons: {
                save: {
                    text: 'Mentés',
                    handler: function () {
                        var store = attrDialog.lookup('attrGrid').getStore();
                        var data = [];
                        var skipped = 0;
                        store.each(function (r) {
                            var aid = r.get('attribute_id');
                            var val = r.get('value');
                            if (aid && val !== null && val !== '') {
                                data.push({ attribute_id: aid, value: val });
                            } else if (aid || (val !== null && val !== '')) {
                                skipped++;
                            }
                        });
                        if (skipped) {
                            Ext.Msg.alert('Hiba', 'Van ' + skipped + ' hiányos sor (típus vagy érték nincs megadva).');
                            return;
                        }

                        API.call({
                            url: 'productvariants/save_attributes/' + variantRecord.get('id'),
                            method: 'POST',
                            data: { attributes: Ext.encode(data) }
                        }).then(function (response) {
                            if (response.success) {
                                Ext.toast('Jellemzők elmentve');
                                var attrStore = Ext.getStore('attributestore');
                                var enriched = data.map(function (d) {
                                    var rec = attrStore ? attrStore.getById(d.attribute_id) : null;
                                    return {
                                        attribute_id: d.attribute_id,
                                        name: rec ? rec.get('name') : '',
                                        value: d.value
                                    };
                                });
                                variantRecord.set('attributes', enriched);
                                variantRecord.commit();
                                attrDialog.destroy();
                            } else {
                                Ext.Msg.alert('Hiba', response.message);
                            }
                        });
                    }.bind(this)
                },
                cancel: {
                    text: 'Mégse',
                    handler: function () {
                        attrDialog.destroy();
                    }
                }
            }
        });

        attrDialog.show();
    }
});