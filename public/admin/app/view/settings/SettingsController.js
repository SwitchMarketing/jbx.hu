Ext.define('JBXAdmin.view.settings.SettingsController', {
    extend: 'Ext.app.ViewController',
    alias: 'controller.settingscontroller',

    listen: {
        controller: {
            'main': {
                reloadSettings: 'onReloadSettings'
            }
        }
    },

    init: function (view) {
        view.on('painted', function () {
            view.getStore().load();
        }.bind(this), this);
    },

    onReloadSettings: function () {
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
        var store = this.getView().getStore();

        Ext.Msg.confirm('Torles', 'Biztosan torlod ezt a beallitast?', function (choice) {
            if (choice !== 'yes') return;

            API.call({
                url: 'settings/' + record.get('id'),
                method: 'DELETE'
            }).then(function (response) {
                if (response.success) {
                    Ext.toast('Beallitas torolve');
                    store.reload();
                } else {
                    Ext.Msg.alert('Hiba', response.message);
                }
            });
        });
    },

    showFormDialog: function (record) {
        var isEdit = !!record;
        var store = this.getView().getStore();

        var dialog = Ext.create({
            xtype: 'dialog',
            title: isEdit ? 'Beallitas szerkesztese' : 'Uj beallitas',
            width: 500,
            closable: true,
            referenceHolder: true,
            bodyPadding: 20,
            items: [{
                xtype: 'formpanel',
                reference: 'form',
                items: [
                    {
                        xtype: 'textfield',
                        label: 'Kulcs',
                        name: 'setting_key',
                        value: isEdit ? record.get('setting_key') : '',
                        readOnly: isEdit,
                        required: true
                    },
                    {
                        xtype: 'textfield',
                        label: 'Ertek',
                        name: 'setting_value',
                        value: isEdit ? (record.get('setting_value') || '') : '',
                        required: true
                    },
                    {
                        xtype: 'selectfield',
                        label: 'Tipus',
                        name: 'data_type',
                        value: isEdit ? (record.get('data_type') || 'string') : 'string',
                        options: [
                            { text: 'string', value: 'string' },
                            { text: 'float', value: 'float' },
                            { text: 'int', value: 'int' },
                            { text: 'bool', value: 'bool' },
                            { text: 'json', value: 'json' }
                        ]
                    },
                    {
                        xtype: 'textfield',
                        label: 'Leiras',
                        name: 'description',
                        value: isEdit ? (record.get('description') || '') : ''
                    }
                ]
            }],
            buttons: {
                save: {
                    text: 'Mentes',
                    handler: function () {
                        var form = dialog.lookup('form');
                        if (!form.validate()) return;

                        var method = isEdit ? 'PUT' : 'POST';
                        var url = isEdit ? ('settings/' + record.get('id')) : 'settings';

                        API.call({
                            url: url,
                            method: method,
                            data: form.getValues()
                        }).then(function (response) {
                            if (response.success) {
                                Ext.toast(isEdit ? 'Beallitas frissitve' : 'Beallitas letrehozva');
                                store.reload();
                                dialog.destroy();
                            } else {
                                Ext.Msg.alert('Hiba', response.message);
                            }
                        });
                    }
                },
                cancel: {
                    text: 'Megse',
                    handler: function () {
                        dialog.destroy();
                    }
                }
            }
        });

        dialog.show();
    }
});
