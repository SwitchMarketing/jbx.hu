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
        view.on('painted', () => {
            view.getStore().load();
        }, this);
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
        let record = info.record;
        let store  = this.getView().getStore();

        Ext.Msg.confirm(
            'Törlés',
            'Biztosan törlöd ezt a kategóriát: "' + record.get('name') + '"?',
            (choice) => {
                if (choice !== 'yes') return;

                API.call({
                    url: 'categories/' + record.get('unas_id'),
                    method: 'DELETE'
                }).then((response) => {
                    if (response.success) {
                        Ext.toast('Kategória törölve');
                        store.reload();
                    } else {
                        Ext.Msg.alert('Hiba', response.message);
                    }
                });
            }
        );
    },

    showFormDialog: function (record) {
        let me       = this;
        let isEdit   = !!record;
        let store    = this.getView().getStore();

        // Build parent options from the current store (tree order already loaded)
        let parentOptions = [{ text: '— Gyökér —', value: 0 }];
        store.each((r) => {
            // Prevent a category from being set as its own parent on edit.
            if (isEdit && r.get('unas_id') === record.get('unas_id')) return;
            let indent = '';
            for (let i = 0; i < r.get('depth'); i++) indent += '— ';
            parentOptions.push({
                text: indent + r.get('name'),
                value: r.get('unas_id')
            });
        });

        let dialog = Ext.create({
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
                        let form = dialog.lookup('form');
                        if (!form.validate()) return;

                        let values = form.getValues();

                        let url    = isEdit
                            ? 'categories/' + record.get('unas_id')
                            : 'categories';
                        let method = isEdit ? 'PUT' : 'POST';

                        API.call({
                            url: url,
                            method: method,
                            data: values
                        }).then((response) => {
                            if (response.success) {
                                Ext.toast(isEdit ? 'Sikeres mentés' : 'Kategória létrehozva');
                                store.reload();
                                dialog.destroy();
                            } else {
                                Ext.Msg.alert('Hiba', response.message);
                            }
                        });
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
