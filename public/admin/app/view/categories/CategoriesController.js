Ext.define('JBXAdmin.view.categories.CategoriesController', {
    extend: 'Ext.app.ViewController',
    alias: 'controller.categoriescontroller',

    listen: {
        controller: {
            'main': {
                reloadCategories: 'onReloadCategories'
            }
        }
    },

    init: function (view) {
        view.on('painted', function () {
            view.getStore().load();
        }.bind(this), this);
    },

    onReloadCategories: function () {
        this.getView().getStore().reload();
    },

    onCreateItem: function () {
        this.showFormDialog(null);
    },

    onEditItem: function (grid, info) {
        this.showFormDialog(info.record);
    },

    onDeleteItem: function (grid, info) {
        var record = info.record;
        var store  = this.getView().getStore();

        Ext.Msg.confirm(
            'Törlés',
            'Biztosan törlöd ezt a kategóriát: "' + record.get('name') + '"?',
            function (choice) {
                if (choice !== 'yes') return;

                API.call({
                    url: 'categories/' + record.get('unas_id'),
                    method: 'DELETE'
                }).then(function (response) {
                    if (response.success) {
                        Ext.toast('Kategória törölve');
                        store.reload();
                    } else {
                        Ext.Msg.alert('Hiba', response.message);
                    }
                }.bind(this));
            }.bind(this)
        );
    },

    showFormDialog: function (record) {
        var me       = this;
        var isEdit   = !!record;
        var store    = this.getView().getStore();

        // Hidden native file input for image upload
        var fileInput = document.createElement('input');
        fileInput.type   = 'file';
        fileInput.accept = 'image/jpeg,image/jpg,image/png,image/webp,image/gif';
        fileInput.style.display = 'none';
        document.body.appendChild(fileInput);

        // Build parent options from the current store (tree order already loaded)
        var parentOptions = [{ text: '— Gyökér —', value: 0 }];
        store.each(function (r) {
            // Prevent a category from being set as its own parent on edit.
            if (isEdit && r.get('unas_id') === record.get('unas_id')) return;
            var indent = '';
            for (var i = 0; i < r.get('depth'); i++) indent += '— ';
            parentOptions.push({
                text: indent + r.get('name'),
                value: r.get('unas_id')
            });
        });

        var currentImage = isEdit ? (record.get('image') || '') : '';

        var dialog = Ext.create({
            xtype: 'dialog',
            title: isEdit ? 'Kategória szerkesztése' : 'Új kategória',
            width: 450,
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
                        value: isEdit ? record.get('name') : '',
                        required: true
                    },
                    {
                        xtype: 'textfield',
                        label: 'Slug',
                        name: 'slug',
                        value: isEdit ? record.get('slug') : '',
                        required: true
                    },
                    {
                        xtype: 'container',
                        layout: { type: 'hbox', align: 'middle' },
                        margin: '0 0 12 0',
                        items: [
                            {
                                xtype    : 'container',
                                reference: 'imageLabel',
                                flex     : 1,
                                html     : '<span style="color:#666;">' +
                                    (currentImage ? Ext.String.htmlEncode(currentImage) : 'Nincs kép') +
                                    '</span>'
                            },
                            {
                                xtype  : 'button',
                                text   : 'Kép feltöltése…',
                                margin : '0 0 0 8',
                                hidden : !isEdit,
                                handler: function () { fileInput.click(); }
                            }
                        ]
                    },
                    {
                        xtype: 'selectfield',
                        label: 'Szülő kategória',
                        name: 'parent_id',
                        value: isEdit ? (record.get('parent_id') || 0) : 0,
                        options: parentOptions,
                        queryMode: 'local'
                    },
                    {
                        xtype: 'numberfield',
                        label: 'Sorrend',
                        name: 'order',
                        value: isEdit ? (record.get('order') || 0) : 0,
                        minValue: 0
                    }
                ]
            }],
            buttons: {
                save: {
                    text: 'Mentés',
                    handler: function () {
                        var form = dialog.down('[reference=form]');
                        if (!form.validate()) return;

                        var values = form.getValues();

                        var url    = isEdit
                            ? 'categories/' + record.get('unas_id')
                            : 'categories';
                        var method = isEdit ? 'PUT' : 'POST';

                        API.call({
                            url: url,
                            method: method,
                            data: values
                        }).then(function (response) {
                            if (response.success) {
                                Ext.toast(isEdit ? 'Sikeres mentés' : 'Kategória létrehozva');
                                store.reload();
                                dialog.destroy();
                            } else {
                                Ext.Msg.alert('Hiba', response.message);
                            }
                        }.bind(this));
                    }
                },
                cancel: {
                    text: 'Mégse',
                    handler: function () {
                        dialog.destroy();
                    }
                }
            },
            listeners: {
                destroy: function () {
                    document.body.removeChild(fileInput);
                }
            }
        });

        // When a file is chosen, upload it immediately (edit mode only)
        fileInput.addEventListener('change', function () {
            var file = fileInput.files[0];
            if (!file || !isEdit) return;

            // Show local preview immediately
            var previewObjectUrl = URL.createObjectURL(file);
            var labelCmp = dialog.down('[reference=imageLabel]');
            if (labelCmp) {
                labelCmp.setHtml(
                    '<img src="' + previewObjectUrl + '" ' +
                    'style="max-height:80px;max-width:100%;display:block;margin:4px 0;" ' +
                    'onload="URL.revokeObjectURL(this.src)" />' +
                    '<span style="color:#666;font-size:11px;">' +
                    Ext.String.htmlEncode(file.name) + '</span>'
                );
            }

            // Use native XHR — Ext.Ajax.request interferes with
            // the multipart boundary when Content-Type is forced.
            var formData = new FormData();
            formData.append('image', file);

            var xhr = new XMLHttpRequest();
            xhr.open('POST', API.apiBase + 'categories/upload_image/' + record.get('unas_id'));
            xhr.onload = function () {
                var result = null;
                try { result = JSON.parse(xhr.responseText); } catch (e) {}
                if (result && result.success) {
                    var filename = (result.data && result.data.image) || file.name;
                    record.set('image', filename);
                    store.reload();
                    Ext.toast('Kép feltöltve');
                } else {
                    Ext.Msg.alert('Hiba', (result && result.message) || 'Ismeretlen hiba (' + xhr.status + ')');
                }
            };
            xhr.onerror = function () {
                Ext.Msg.alert('Hiba', 'A feltöltés nem sikerült (hálózati hiba).');
            };
            xhr.send(formData);
        });

        dialog.show();
    }
});