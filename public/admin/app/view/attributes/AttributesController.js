Ext.define('JBXAdmin.view.attributes.AttributesController', {
    extend: 'Ext.app.ViewController',
    alias: 'controller.attributescontroller',

    onAddAttribute: function() {
        var field = this.lookup('newAttrName');
        var name = field.getValue();

        if (!name) return;

        API.call({
            url: 'attributes',
            method: 'POST',
            data: { name: name }
        }).then(function (response) {
            if (response.success) {
                Ext.toast('Attribútum hozzáadva');
                this.getView().getStore().reload();
                field.setValue('');
            } else {
                Ext.Msg.alert('Hiba', response.message);
            }
        }.bind(this));
    },

    onEditItem: function(grid, info) {
        var record = info.record;
        var store  = this.getView().getStore();

        var dialog = Ext.create({
            xtype: 'dialog',
            title: 'Attribútum átnevezése',
            width: 400,
            closable: true,
            referenceHolder: true,
            bodyPadding: 20,
            items: [{
                xtype: 'formpanel',
                reference: 'form',
                items: [{
                    xtype: 'textfield',
                    label: 'Név',
                    name: 'name',
                    value: record.get('name'),
                    required: true
                }]
            }],
            buttons: {
                save: {
                    text: 'Mentés',
                    handler: function () {
                        var form = dialog.lookup('form');
                        if (!form.validate()) return;

                        API.call({
                            url: 'attributes/' + record.get('id'),
                            method: 'PUT',
                            data: form.getValues()
                        }).then(function (response) {
                            if (response.success) {
                                Ext.toast('Attribútum frissítve');
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
            }
        });

        dialog.show();
    },

    onDeleteItem: function(grid, info) {
        var record = info.record;
        Ext.Msg.confirm('Törlés', 'Biztosan törlöd ezt az attribútumot?', function (choice) {
            if (choice === 'yes') {
                API.call({
                    url: 'attributes/' + record.get('id'),
                    method: 'DELETE'
                }).then(function (response) {
                    if (response.success) {
                        Ext.toast('Attribútum törölve');
                        grid.getStore().reload();
                    } else {
                        Ext.Msg.alert('Hiba', response.message);
                    }
                }.bind(this));
            }
        }.bind(this));
    }
});