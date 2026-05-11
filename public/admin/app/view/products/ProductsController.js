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
            mode : 'multi'
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

    /**
     * Build leaf-category options with full breadcrumb-path labels
     * ("Root › Sub › Leaf") from a loaded CategoryStore.
     *
     * @param {Ext.data.Store} categoryStore
     * @return {{ options: Array, pathById: Object }}
     */
    buildCategoryOptions: function (categoryStore) {
        var options = [];
        var pathById = {};
        if (!categoryStore) return { options: options, pathById: pathById };

        var byId = {};
        var parentIds = new Set();
        categoryStore.each(function (r) {
            byId[r.get('unas_id')] = r;
            parentIds.add(String(r.get('parent_id')));
        });

        categoryStore.each(function (r) {
            if (parentIds.has(String(r.get('unas_id')))) return;
            var ids = (r.get('pathIds') || '').toString().split('/').filter(Boolean);
            if (!ids.length) ids = [r.get('unas_id')];
            var text = ids.map(function (id) { return byId[id] ? byId[id].get('name') : id; }).join(' › ');
            pathById[r.get('unas_id')] = text;
            options.push({ text: text, value: r.get('unas_id') });
        });
        options.sort(function (a, b) { return a.text.localeCompare(b.text, 'hu'); });

        return { options: options, pathById: pathById };
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
                var variantsHtml = '<table class="variants-table"><thead><tr><th>SKU</th><th>Név</th><th>Ár (nettó, EUR)</th><th>Készlet</th><th>Állapot</th><th>Jellemzők</th></tr></thead><tbody>';
                if (product.variants && product.variants.length > 0) {
                    product.variants.forEach(function (v) {
                        var attrs = '';
                        if (v.attributes && v.attributes.length > 0) {
                            attrs = v.attributes.map(function (a) { return '<strong>' + a.name + '</strong>: ' + a.value; }).join(', ');
                        } else {
                            attrs = '-';
                        }
                        var stateLabel = stateLabels[v.state] || v.state || '-';
                        variantsHtml += '<tr><td>' + v.sku + '</td><td>' + (v.name || '-') + '</td><td>' + v.price + ' EUR</td><td>' + v.stock + '</td><td>' + stateLabel + '</td><td>' + attrs + '</td></tr>';
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
                    // width: 650,
                    // height: 550,
                    responsiveConfig: {
                        'width >= 768': { 
                            width: '80%', 
                            height: '80%' 
                        },
                        'width < 768':  { 
                            width: null, 
                            height: null 
                        }
                    },
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
                            ui: 'confirm',
                            handler: function () {
                                dialog.destroy();
                                this.onEditItem(grid, { record: record });
                            }.bind(this)
                        },
                        ok: {
                            text: 'Bezár',
                            ui: 'decline',
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

    onViewItem: function (grid, info) {
        if (!info || !info.record) return;
        this.onItemSelected(grid, info.record);
    },

    onBulkMoveProducts: function () {
        var me = this;
        var grid = this.getView();
        var selected = [];

        if (grid.getSelections && Ext.isFunction(grid.getSelections)) {
            selected = grid.getSelections() || [];
        } else if (grid.getSelection && Ext.isFunction(grid.getSelection)) {
            var s = grid.getSelection();
            selected = Ext.isArray(s) ? s : (s ? [s] : []);
        } else if (grid.getSelectable && Ext.isFunction(grid.getSelectable)) {
            var selModel = grid.getSelectable();
            if (selModel && selModel.getSelectedRecords && Ext.isFunction(selModel.getSelectedRecords)) {
                selected = selModel.getSelectedRecords() || [];
            }
        }

        if (!selected.length) {
            Ext.Msg.alert('Figyelem', 'Jelölj ki legalább egy terméket az áthelyezéshez.');
            return;
        }

        var categoryStore = Ext.getStore('categorystore') || Ext.create('JBXAdmin.store.CategoryStore');
        var loadCategories = new Promise(function (resolve) {
            if (categoryStore.getCount() > 0) { resolve(); return; }
            if (categoryStore.isLoading()) {
                categoryStore.on('load', function () { resolve(); }, { single: true });
            } else {
                categoryStore.load({ callback: function () { resolve(); } });
            }
        });

        loadCategories.then(function () {
            var catData = me.buildCategoryOptions(categoryStore);

            var dialog = Ext.create({
                xtype: 'dialog',
                title: 'Termékek áthelyezése kategóriába',
                width: 620,
                height: 520,
                closable: true,
                referenceHolder: true,
                layout: 'fit',
                platformConfig: {
                    phone: { maximized: true, width: null, height: null }
                },
                items: [{
                    xtype: 'formpanel',
                    reference: 'bulkMoveForm',
                    scrollable: true,
                    bodyPadding: 16,
                    items: [
                        {
                            xtype: 'selectfield',
                            label: 'Cél kategória (levél)',
                            name: 'category_id',
                            options: catData.options,
                            queryMode: 'local',
                            autoComplete: true,
                            forceSelection: true,
                            clearable: false,
                            required: true
                        },
                        {
                            xtype: 'component',
                            margin: '10 0 6 0',
                            html: '<strong>Kijelölt termékek (' + selected.length + ' db)</strong>'
                        },
                        {
                            xtype: 'component',
                            style: 'max-height:260px;overflow:auto;border:1px solid #ddd;padding:8px;border-radius:4px;background:#fff;',
                            html: selected.map(function (r) {
                                var n = Ext.String.htmlEncode(r.get('name') || ('#' + r.get('id')));
                                var c = Ext.String.htmlEncode(r.get('category_path_names') || r.get('category_name') || '-');
                                return '<div style="padding:4px 0;border-bottom:1px solid #f1f1f1;">' +
                                    '<div style="font-weight:600;">' + n + '</div>' +
                                    '<div style="font-size:12px;color:#666;">Jelenlegi: ' + c + '</div>' +
                                    '</div>';
                            }).join('')
                        }
                    ]
                }],
                buttons: {
                    move: {
                        text: 'Áthelyezés',
                        ui: 'action',
                        handler: function () {
                            var form = dialog.lookup('bulkMoveForm');
                            if (!form.validate()) return;

                            var values = form.getValues();
                            var ids = selected.map(function (r) { return r.get('id'); });

                            API.call({
                                url: 'products/bulk_move_category',
                                method: 'POST',
                                data: {
                                    ids: ids,
                                    category_id: values.category_id
                                }
                            }).then(function (response) {
                                if (!response.success) {
                                    Ext.Msg.alert('Hiba', response.message);
                                    return;
                                }
                                Ext.toast(response.message || 'Termékek áthelyezve');
                                dialog.destroy();
                                grid.getStore().reload();
                            });
                        }
                    },
                    cancel: {
                        text: 'Mégse',
                        handler: function () { dialog.destroy(); }
                    }
                }
            });

            dialog.show();
        });
    },

    onCreateProduct: function () {
        var me = this;
        var grid = this.getView();

        var categoryStore = Ext.getStore('categorystore') || Ext.create('JBXAdmin.store.CategoryStore');
        var loadCategories = new Promise(function (resolve) {
            if (categoryStore.getCount() > 0) { resolve(); return; }
            if (categoryStore.isLoading()) {
                categoryStore.on('load', function () { resolve(); }, { single: true });
            } else {
                categoryStore.load({ callback: function () { resolve(); } });
            }
        });

        loadCategories.then(function () {
            var catData = me.buildCategoryOptions(categoryStore);
            var presetCategoryId = null;
            var catFilter = me.lookup('productCategoryFilter');
            if (catFilter && catFilter.getValue()) {
                presetCategoryId = catFilter.getValue();
            }

            // last auto-generated slug — if the slug field still holds this value
            // (or is empty), we continue auto-filling from Név; once the user types
            // anything else into the slug field the auto-fill halts on its own.
            var lastAutoSlug = '';

            var dialog = Ext.create({
                xtype: 'dialog',
                title: 'Új termék',
                width: 520,
                height: 460,
                closable: true,
                referenceHolder: true,
                layout: 'fit',
                platformConfig: {
                    phone: { maximized: true, width: null, height: null }
                },
                items: [{
                    xtype: 'formpanel',
                    reference: 'createForm',
                    scrollable: true,
                    bodyPadding: 20,
                    items: [
                        {
                            xtype: 'selectfield',
                            label: 'Kategória',
                            name: 'category_id',
                            reference: 'categoryField',
                            value: presetCategoryId,
                            options: catData.options,
                            queryMode: 'local',
                            autoComplete: true,
                            forceSelection: true,
                            clearable: false,
                            required: true
                        },
                        {
                            xtype: 'textfield',
                            label: 'Név',
                            name: 'name',
                            reference: 'nameField',
                            autoComplete: false,
                            required: true,
                            listeners: {
                                change: function (field, newValue) {
                                    var slugField = dialog.lookup('slugField');
                                    if (!slugField) return;
                                    var current = slugField.getValue() || '';
                                    if (current === '' || current === lastAutoSlug) {
                                        var next = Slugify.toSlug(newValue);
                                        lastAutoSlug = next;
                                        slugField.setValue(next);
                                    }
                                }
                            }
                        },
                        {
                            xtype: 'textfield',
                            label: 'Slug',
                            name: 'slug',
                            reference: 'slugField',
                            autoComplete: false,
                            required: true
                        },
                        {
                            xtype: 'textfield',
                            label: 'Egység',
                            name: 'unit',
                            autoComplete: false,
                            placeholder: 'pl. db, m, csomag'
                        },
                        {
                            xtype: 'togglefield',
                            label: 'Aktív',
                            name: 'state',
                            value: false
                        }
                    ]
                }],
                buttons: {
                    create: {
                        text: 'Létrehozás',
                        ui: 'action',
                        handler: function () {
                            var form = dialog.lookup('createForm');
                            if (!form.validate()) return;

                            var values = form.getValues();
                            values.state = values.state ? 'active' : 'inactive';

                            API.call({
                                url: 'products',
                                method: 'POST',
                                data: values
                            }).then(function (response) {
                                if (!response.success) {
                                    Ext.Msg.alert('Hiba', response.message);
                                    return;
                                }
                                var newId = response.data && response.data.id;
                                Ext.toast('Termék létrehozva');
                                dialog.destroy();

                                var store = grid.getStore();
                                store.reload({
                                    callback: function () {
                                        // The new record may not be on the current page
                                        // (pagination + default sort can push it elsewhere).
                                        // onEditItem only needs the id, so fall back to a stub.
                                        var rec = store.getById(newId)
                                            || Ext.create(store.getModel(), { id: newId });
                                        me.onEditItem(grid, { record: rec });
                                    }
                                });
                            });
                        }
                    },
                    cancel: {
                        text: 'Mégse',
                        handler: function () { dialog.destroy(); }
                    }
                }
            });

            dialog.show();
        });
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
        var catData = this.buildCategoryOptions(categoryStore);
        var categoryOptions = catData.options;
        var categoryPathById = catData.pathById;

        var currentCategoryPath = categoryPathById[productData.category_id] || productData.category_path_names || '-';

        var dialog = Ext.create({
            xtype: 'dialog',
            title: 'Szerkesztés: ' + productData.name,
            // width: 1100,
            // height: 700,
            responsiveConfig: {
                'width >= 768': { 
                    width: '80%', 
                    height: '80%' 
                },
                'width < 768':  { 
                    width: null, 
                    height: null 
                }
            },
            closable: true,
            maximizable: true,
            referenceHolder: true,
            layout: 'fit',
            platformConfig: {
                phone: { maximized: true, width: null, height: null }
            },
            items: [{
                xtype: 'tabpanel',
                userCls: 'default-tabs',
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
                                autoComplete: false,
                                value: productData.name,
                                required: true
                            },
                            {
                                xtype: 'textfield',
                                label: 'Slug',
                                name: 'slug',
                                autoComplete: false,
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
                                autoComplete: false,
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
                            fields: ['id', 'unas_id', 'sku', 'name', 'price', 'discount_price', 'stock', 'position', 'state', 'attributes'],
                            data: productData.variants || [],
                            listeners: {
                                update: function (store, record, operation, modifiedFieldNames) {
                                    if (operation !== Ext.data.Model.EDIT) return;
                                    if (!record.get('id')) return;
                                    var editable = ['sku', 'name', 'price', 'discount_price', 'stock', 'position', 'state'];
                                    if (!modifiedFieldNames || !modifiedFieldNames.some(function (f) { return editable.indexOf(f) !== -1; })) return;
                                    this.onSaveVariant(record);
                                }.bind(this)
                            }
                        },
                        columns: [
                            { text: 'Poz.', dataIndex: 'position', width: 70, editable: true },
                            { text: 'UNAS ID', dataIndex: 'unas_id', width: 90, hidden: true },
                            { text: 'SKU', dataIndex: 'sku', width: 140, editable: true },
                            { text: 'Név', dataIndex: 'name', flex: 1, minWidth: 200, editable: true },
                            { text: 'Ár (Nettó, EUR)', dataIndex: 'price', width: 140, editable: true },
                            { text: 'Kedv. ár (Nettó, EUR)', dataIndex: 'discount_price', width: 180, editable: true },
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
                                    text: 'Alapert. jellemzok',
                                    iconCls: 'x-fa fa-sliders-h',
                                    handler: function () {
                                        this.onEditDefaultAttributes(productData.id, productData);
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
                    },
                    {
                        title: 'Kepek',
                        xtype: 'grid',
                        reference: 'imagesGrid',
                        userCls: 'jbx-themed-grid',
                        store: {
                            fields: ['id', 'filename', 'alt', 'position', 'variant_id'],
                            data: productData.images || [],
                            listeners: {
                                update: function (store, record, operation, modifiedFieldNames) {
                                    if (operation !== Ext.data.Model.EDIT) return;
                                    if (!record.get('id')) return;
                                    if (!modifiedFieldNames || !modifiedFieldNames.length) return;
                                    this.onSaveImage(record);
                                }.bind(this)
                            }
                        },
                        columns: [
                            { text: 'Poz.', dataIndex: 'position', width: 70, editable: true },
                            {
                                text: 'Fajl', dataIndex: 'filename', flex: 1, editable: true,
                                cell: {
                                    xtype: 'gridcell',
                                    encodeHtml: false,
                                    renderer: function (value) {
                                        if (!value) return '';
                                        var src = '/imgs/products/' + Ext.String.htmlEncode(value);
                                        return '<a href="' + src + '" target="_blank" ' +
                                            'style="color:#1677ff;text-decoration:underline;" ' +
                                            'onclick="event.stopPropagation();">' +
                                            Ext.String.htmlEncode(value) + '</a>';
                                    }
                                }
                            },
                            { text: 'ALT', dataIndex: 'alt', flex: 1, editable: true },
                            { text: 'Variant ID', dataIndex: 'variant_id', width: 100, editable: true },
                            {
                                width: 60,
                                cell: {
                                    tools: {
                                        delete: {
                                            iconCls: 'x-fa fa-trash',
                                            tooltip: 'Kep torlese',
                                            handler: function (gridRef, info) {
                                                this.onDeleteImage(info.record, gridRef.getStore());
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
                                    text: 'Uj kep',
                                    iconCls: 'x-fa fa-image',
                                    handler: function () {
                                        this.onCreateImage(productData.id, dialog.lookup('imagesGrid'));
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
                    ui: 'confirm',
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
                    ui: 'decline',
                    handler: function () {
                        dialog.destroy();
                    }
                }
            }
        });

        dialog.show();
    },

    onCreateVariant: function(masterId, variantsGrid) {
        var me = this;
        var dialog = Ext.create({
            xtype: 'dialog',
            title: 'Új variáció',
            width: 400,
            closable: true,
            referenceHolder: true,
            layout: 'fit',
            platformConfig: {
                phone: { maximized: true, width: null, height: null }
            },
            items: [{
                xtype: 'formpanel',
                reference: 'variantForm',
                scrollable: true,
                bodyPadding: 20,
                items: [
                    {
                        xtype: 'textfield',
                        label: 'SKU',
                        name: 'sku',
                        reference: 'skuField',
                        autoComplete: false,
                        required: true,
                        placeholder: 'pl. ABC-001'
                    },
                    {
                        xtype: 'textfield',
                        label: 'Név',
                        name: 'name',
                        reference: 'nameField',
                        autoComplete: false,
                        placeholder: 'Pl. Fekete, M méret'
                    },
                    {
                        xtype: 'textfield',
                        inputType: 'number',
                        label: 'Ár (Nettó, EUR)',
                        name: 'price',
                        reference: 'priceField',
                        autoComplete: false,
                        value: 0,
                        step: 0.01
                    },
                    {
                        xtype: 'textfield',
                        inputType: 'number',
                        label: 'Kedvezményes ár (Nettó, EUR)',
                        name: 'discount_price',
                        reference: 'discountPriceField',
                        autoComplete: false,
                        value: 0,
                        step: 0.01,
                        placeholder: '0 = nincs kedvezmény'
                    },
                    {
                        xtype: 'textfield',
                        inputType: 'number',
                        label: 'Készlet',
                        name: 'stock',
                        reference: 'stockField',
                        autoComplete: false,
                        value: 0,
                        step: 1
                    },
                    {
                        xtype: 'selectfield',
                        label: 'Állapot',
                        name: 'state',
                        reference: 'stateField',
                        value: 'backorder',
                        options: [
                            { text: 'Raktáron',    value: 'instock' },
                            { text: 'Rendelésre',  value: 'backorder' },
                            { text: 'Ajánlatkérés', value: 'inquire' },
                            { text: 'Inaktív',     value: 'inactive' }
                        ],
                        queryMode: 'local',
                        autoComplete: false,
                        clearable: false,
                        forceSelection: true
                    }
                ]
            }],
            buttons: {
                create: {
                    text: 'Létrehozás',
                    ui: 'action',
                    handler: function () {
                        var form = dialog.lookup('variantForm');
                        if (!form.validate()) return;

                        var values = form.getValues();
                        values.master_id = masterId;

                        API.call({
                            url: 'productvariants',
                            method: 'POST',
                            data: values
                        }).then(function (response) {
                            if (!response.success) {
                                Ext.Msg.alert('Hiba', response.message);
                                return;
                            }
                            var newId = response.data && response.data.id;
                            
                            // Fetch the master and extract the new variant with auto-applied defaults.
                            // productvariants/{id} is not exposed by the admin API.
                            API.call({
                                url: 'products/' + masterId
                            }).then(function (fetchResponse) {
                                if (fetchResponse.success && fetchResponse.data && Ext.isArray(fetchResponse.data.variants)) {
                                    var variantData = fetchResponse.data.variants.find(function (v) {
                                        return Number(v.id) === Number(newId);
                                    });

                                    if (!variantData) {
                                        variantData = {
                                            id: newId,
                                            unas_id: null,
                                            sku: values.sku,
                                            name: values.name || null,
                                            price: values.price || 0,
                                            discount_price: values.discount_price || 0,
                                            stock: values.stock || 0,
                                            position: (response.data && response.data.position) || 0,
                                            state: values.state,
                                            attributes: []
                                        };
                                    }

                                    variantsGrid.getStore().add({
                                        id: variantData.id,
                                        unas_id: variantData.unas_id || null,
                                        sku: variantData.sku,
                                        name: variantData.name || null,
                                        price: variantData.price || 0,
                                        discount_price: variantData.discount_price || 0,
                                        stock: variantData.stock || 0,
                                        position: variantData.position || 0,
                                        state: variantData.state,
                                        attributes: variantData.attributes || []
                                    });
                                    var attrCount = (variantData.attributes && variantData.attributes.length) || 0;
                                    var attrMsg = attrCount > 0 
                                        ? ' (' + attrCount + ' alapértelmezett jellemző alkalmazva)' 
                                        : '';
                                    Ext.toast('Variáció létrehozva: ' + variantData.sku + attrMsg);

                                    if (attrCount > 0) {
                                        var added = variantsGrid.getStore().getById(variantData.id);
                                        if (added) {
                                            me.onEditVariantAttributes(added);
                                        }
                                    }
                                } else {
                                    // Fallback if fetch fails
                                    variantsGrid.getStore().add({
                                        id: newId,
                                        unas_id: null,
                                        sku: values.sku,
                                        name: values.name || null,
                                        price: values.price || 0,
                                        discount_price: values.discount_price || 0,
                                        stock: values.stock || 0,
                                        position: (response.data && response.data.position) || 0,
                                        state: values.state,
                                        attributes: []
                                    });
                                    Ext.toast('Variáció létrehozva: ' + values.sku);
                                }
                                dialog.destroy();
                            });
                        });
                    }
                },
                cancel: {
                    text: 'Mégse',
                    handler: function () { dialog.destroy(); }
                }
            }
        });

        dialog.show();
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

    onToggleProductState: function(button, e) {
        var cell = button.ownerCmp;
        var record = cell && cell.getRecord();
        if (!record || !record.get('id')) return;
        var currentState = record.get('state');
        var newState = currentState === 'active' ? 'inactive' : 'active';

        button.setDisabled(true);

        API.call({
            url: 'products/' + record.get('id'),
            method: 'PUT',
            data: { state: newState }
        }).then(function(response) {
            button.setDisabled(false);
            if (response.success) {
                record.set('state', newState, { silent: true });
                record.commit();
                var sameRow = button.ownerCmp && button.ownerCmp.getRecord &&
                              button.ownerCmp.getRecord() &&
                              button.ownerCmp.getRecord().get('id') === record.get('id');
                if (sameRow) {
                    button.setActiveState(newState);
                }
                Ext.toast(newState === 'active' ? 'Termék aktiválva' : 'Termék inaktiválva');
            } else {
                Ext.Msg.alert('Hiba', response.message);
            }
        }).catch(function() {
            button.setDisabled(false);
            Ext.Msg.alert('Hiba', 'Hálózati hiba. Kérjük próbálja újra.');
        });
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
                discount_price: variantRecord.get('discount_price'),
                stock: variantRecord.get('stock'),
                position: variantRecord.get('position'),
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
                    ui: 'confirm',
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
                    ui: 'decline',
                    handler: function () {
                        attrDialog.destroy();
                    }
                }
            }
        });

        attrDialog.show();
    },

    onEditDefaultAttributes: function(masterId, productData) {
        var storeData = (productData.default_attributes || []).map(function (r) {
            return {
                attribute_id: r.attribute_id,
                name: r.name,
                value: r.value
            };
        });

        var dialog = Ext.create({
            xtype: 'dialog',
            title: 'Alapertelmezett jellemzok',
            width: 540,
            height: 420,
            closable: true,
            referenceHolder: true,
            layout: 'fit',
            items: [{
                xtype: 'grid',
                reference: 'defaultAttrGrid',
                userCls: 'jbx-themed-grid',
                store: {
                    fields: ['attribute_id', 'name', 'value'],
                    data: storeData
                },
                columns: [
                    {
                        text: 'Tipus',
                        dataIndex: 'attribute_id',
                        width: 200,
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
                    { text: 'Ertek', dataIndex: 'value', flex: 1, editable: true },
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
                    items: [{
                        text: 'Uj sor',
                        iconCls: 'x-fa fa-plus',
                        handler: function () {
                            dialog.lookup('defaultAttrGrid').getStore().add({ attribute_id: '', value: '' });
                        }
                    }]
                }]
            }],
            buttons: {
                save: {
                    text: 'Mentés',
                    ui: 'confirm',
                    handler: function () {
                        var rows = [];
                        dialog.lookup('defaultAttrGrid').getStore().each(function (r) {
                            var aid = r.get('attribute_id');
                            var value = r.get('value');
                            if (aid) {
                                rows.push({
                                    attribute_id: aid,
                                    value: value === null || value === undefined ? '' : String(value)
                                });
                            }
                        });

                        API.call({
                            url: 'products/save_default_attributes/' + masterId,
                            method: 'POST',
                            data: { attributes: Ext.encode(rows) }
                        }).then(function (response) {
                            if (!response.success) {
                                Ext.Msg.alert('Hiba', response.message);
                                return;
                            }

                            var attrStore = Ext.getStore('attributestore');
                            productData.default_attributes = rows.map(function (d) {
                                var rec = attrStore ? attrStore.getById(d.attribute_id) : null;
                                return {
                                    attribute_id: d.attribute_id,
                                    name: rec ? rec.get('name') : '',
                                    value: d.value
                                };
                            });

                            Ext.toast('Alapertelmezett jellemzok mentve');
                            dialog.destroy();
                        });
                    }.bind(this)
                },
                cancel: {
                    text: 'Mégse',
                    ui: 'decline',
                    handler: function () { dialog.destroy(); }
                }
            }
        });

        dialog.show();
    },

    onCreateImage: function(masterId, imagesGrid) {
        var me = this;

        // Native file input — attached to body so .click() works cross-browser
        var fileInput = document.createElement('input');
        fileInput.type   = 'file';
        fileInput.accept = 'image/jpeg,image/jpg,image/png,image/webp,image/gif';
        fileInput.style.display = 'none';
        document.body.appendChild(fileInput);

        var previewObjectUrl = null;

        var dialog = Ext.create({
            xtype      : 'dialog',
            title      : 'Új kép feltöltése',
            width      : 440,
            closable   : true,
            bodyPadding: 16,
            items: [{
                xtype: 'formpanel',
                items: [
                    {
                        xtype    : 'container',
                        reference: 'previewBox',
                        html     : '<div style="width:100%;height:180px;background:#f5f5f5;border:1px dashed #ccc;' +
                                   'display:flex;align-items:center;justify-content:center;' +
                                   'color:#aaa;font-size:13px;margin-bottom:12px;">' +
                                   'Előnézet</div>'
                    },
                    {
                        xtype : 'button',
                        text  : 'Fájl kiválasztása…',
                        handler: function () { fileInput.click(); }
                    },
                    {
                        xtype      : 'textfield',
                        label      : 'Alt szöveg',
                        reference  : 'altField',
                        placeholder: 'opcionális',
                        margin     : '12 0 0 0'
                    }
                ]
            }],
            buttons: {
                ok: {
                    text   : 'Feltöltés',
                    handler: function () {
                        var file = fileInput.files[0];
                        if (!file) {
                            Ext.Msg.alert('Hiba', 'Kérjük válasszon képfájlt.');
                            return;
                        }
                        var altCmp = dialog.down('[reference=altField]');
                        var alt = (altCmp ? altCmp.getValue() : '').trim();
                        var formData = new FormData();
                        formData.append('image',     file);
                        formData.append('master_id', masterId);
                        if (alt) formData.append('alt', alt);

                        // Use native XHR — Ext.Ajax.request interferes with
                        // the multipart boundary when Content-Type is forced.
                        var xhr = new XMLHttpRequest();
                        xhr.open('POST', API.apiBase + 'images/upload');
                        xhr.onload = function () {
                            var result = null;
                            try { result = JSON.parse(xhr.responseText); } catch (e) {}
                            if (result && result.success) {
                                me.reloadImages(masterId, imagesGrid);
                                Ext.toast('Kép feltöltve');
                                dialog.destroy();
                            } else {
                                Ext.Msg.alert('Hiba', (result && result.message) || 'Ismeretlen hiba (' + xhr.status + ')');
                            }
                        };
                        xhr.onerror = function () {
                            Ext.Msg.alert('Hiba', 'A feltöltés nem sikerült (hálózati hiba).');
                        };
                        xhr.send(formData);
                    }
                },
                cancel: {
                    text   : 'Mégse',
                    handler: function () { dialog.destroy(); }
                }
            },
            listeners: {
                destroy: function () {
                    if (previewObjectUrl) { URL.revokeObjectURL(previewObjectUrl); }
                    document.body.removeChild(fileInput);
                }
            }
        });

        fileInput.addEventListener('change', function () {
            var file = fileInput.files[0];
            if (!file) return;

            if (previewObjectUrl) { URL.revokeObjectURL(previewObjectUrl); }
            previewObjectUrl = URL.createObjectURL(file);

            var previewCmp = dialog.down('[reference=previewBox]');
            if (previewCmp) {
                previewCmp.setHtml(
                    '<img src="' + previewObjectUrl + '" ' +
                    'style="max-width:100%;max-height:180px;display:block;margin:0 auto;" />'
                );
            }
        });

        dialog.show();
    },

    onSaveImage: function(imageRecord) {
        API.call({
            url: 'images/' + imageRecord.get('id'),
            method: 'PUT',
            data: {
                filename: imageRecord.get('filename'),
                alt: imageRecord.get('alt'),
                position: imageRecord.get('position'),
                variant_id: imageRecord.get('variant_id')
            }
        }).then(function (response) {
            if (response.success) {
                imageRecord.commit();
                Ext.toast('Kep mentve');
            } else {
                Ext.Msg.alert('Hiba', response.message);
            }
        });
    },

    onDeleteImage: function(imageRecord, store) {
        Ext.Msg.confirm('Megerosites', 'Torlod ezt a kepet?', function (choice) {
            if (choice !== 'yes') return;

            API.call({
                url: 'images/' + imageRecord.get('id'),
                method: 'DELETE'
            }).then(function (response) {
                if (response.success) {
                    store.remove(imageRecord);
                    Ext.toast('Kep torolve');
                } else {
                    Ext.Msg.alert('Hiba', response.message);
                }
            });
        });
    },

    reloadImages: function(masterId, imagesGrid) {
        API.call({
            url: 'images',
            method: 'GET',
            data: { master_id: masterId }
        }).then(function (response) {
            if (response.success && imagesGrid && imagesGrid.getStore) {
                imagesGrid.getStore().loadData(response.data || []);
            }
        });
    }
});