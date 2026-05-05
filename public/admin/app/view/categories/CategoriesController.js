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

    onUploadImageItem: function (grid, info) {
        this.pickAndUploadCategoryImage(info.record);
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

    pickAndUploadCategoryImage: function (record) {
        if (!record) return;
        var store = this.getView().getStore();
        var fileInput = document.createElement('input');
        fileInput.type = 'file';
        fileInput.accept = 'image/jpeg,image/jpg,image/png,image/webp,image/gif';
        fileInput.style.display = 'none';
        document.body.appendChild(fileInput);

        fileInput.addEventListener('change', function () {
            var file = fileInput.files[0];
            if (!file) return;

            this.uploadCategoryImage(record.get('unas_id'), file)
                .then(function (result) {
                    var filename = (result.data && result.data.image) || file.name;
                    record.set('image', filename);
                    store.reload();
                    Ext.toast('Kép feltöltve');
                    document.body.removeChild(fileInput);
                })
                .catch(function (message) {
                    Ext.Msg.alert('Hiba', message);
                    document.body.removeChild(fileInput);
                });
        }.bind(this), { once: true });

        fileInput.click();
    },

    uploadCategoryImage: function (categoryId, file) {
        return new Promise(function (resolve, reject) {
            if (!categoryId) {
                reject('Hiányzó kategória azonosító');
                return;
            }
            if (!file) {
                reject('Nincs kiválasztott kép');
                return;
            }

            var formData = new FormData();
            formData.append('image', file);

            var xhr = new XMLHttpRequest();
            xhr.open('POST', API.apiBase + 'categories/upload_image/' + categoryId);
            xhr.onload = function () {
                var result = null;
                try { result = JSON.parse(xhr.responseText); } catch (e) {}
                if (result && result.success) {
                    resolve(result);
                } else {
                    reject((result && result.message) || ('Ismeretlen hiba (' + xhr.status + ')'));
                }
            };
            xhr.onerror = function () {
                reject('A feltöltés nem sikerült (hálózati hiba).');
            };
            xhr.send(formData);
        });
    },

    showFormDialog: function (record) {
        var me       = this;
        var isEdit   = !!record;
        var store    = this.getView().getStore();
        var selectedImageFile = null;
        var previewObjectUrl = null;

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
                                text   : 'Borítókép kiválasztása…',
                                margin : '0 0 0 8',
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
                        xtype: 'textfield',
                        inputType: 'number',
                        label: 'Sorrend',
                        name: 'order',
                        value: isEdit ? (record.get('order') || 0) : 0,
                        autoComplete: false
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
                                var categoryId = isEdit
                                    ? record.get('unas_id')
                                    : (response.data && response.data.unas_id);

                                var finish = function (message) {
                                    Ext.toast(message || (isEdit ? 'Sikeres mentés' : 'Kategória létrehozva'));
                                    store.reload();
                                    dialog.destroy();
                                };

                                if (selectedImageFile && categoryId) {
                                    me.uploadCategoryImage(categoryId, selectedImageFile)
                                        .then(function (uploadResponse) {
                                            if (isEdit && record) {
                                                record.set('image', (uploadResponse.data && uploadResponse.data.image) || selectedImageFile.name);
                                            }
                                            finish((isEdit ? 'Sikeres mentés' : 'Kategória létrehozva') + ' + borítókép feltöltve');
                                        })
                                        .catch(function (message) {
                                            Ext.Msg.alert('Hiba', 'A kategória mentve, de a kép feltöltése nem sikerült: ' + message);
                                            finish(isEdit ? 'Sikeres mentés' : 'Kategória létrehozva');
                                        });
                                } else {
                                    finish();
                                }
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
                    if (previewObjectUrl) {
                        URL.revokeObjectURL(previewObjectUrl);
                    }
                    document.body.removeChild(fileInput);
                }
            }
        });

        // Keep the selected file for upload after save (works for create and edit)
        fileInput.addEventListener('change', function () {
            var file = fileInput.files[0];
            if (!file) return;
            selectedImageFile = file;

            // Show local preview immediately
            if (previewObjectUrl) {
                URL.revokeObjectURL(previewObjectUrl);
            }
            previewObjectUrl = URL.createObjectURL(file);
            var labelCmp = dialog.down('[reference=imageLabel]');
            if (labelCmp) {
                labelCmp.setHtml(
                    '<img src="' + previewObjectUrl + '" ' +
                    'style="max-height:80px;max-width:100%;display:block;margin:4px 0;" />' +
                    '<span style="color:#666;font-size:11px;">' +
                    Ext.String.htmlEncode(file.name) + '</span>'
                );
            }
        });

        dialog.show();
    }
});