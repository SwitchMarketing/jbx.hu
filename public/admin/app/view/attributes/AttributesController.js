Ext.define('JBXAdmin.view.attributes.AttributesController', {
    extend: 'Ext.app.ViewController',
    alias: 'controller.attributescontroller',

    onAddAttribute: function() {
        let field = this.lookup('newAttrName');
        let name = field.getValue();

        if (!name) return;

        API.call({
            url: 'attributes',
            method: 'POST',
            data: { name: name }
        }).then((response) => {
            if (response.success) {
                Ext.toast('Attribútum hozzáadva');
                this.getView().getStore().reload();
                field.setValue('');
            } else {
                Ext.Msg.alert('Hiba', response.message);
            }
        });
    },

    onEditItem: function(grid, info) {
        let record = info.record;
        let store  = this.getView().getStore();

        let dialog = Ext.create({
            xtype: 'dialog',
            title: 'Attribútum átnevezése',
            width: 400,
            closable: true,
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
                        let form = dialog.lookup('form');
                        if (!form.validate()) return;

                        API.call({
                            url: 'attributes/' + record.get('id'),
                            method: 'PUT',
                            data: form.getValues()
                        }).then((response) => {
                            if (response.success) {
                                Ext.toast('Attribútum frissítve');
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
    },

    onDeleteItem: function(grid, info) {
        let record = info.record;
        Ext.Msg.confirm('Törlés', 'Biztosan törlöd ezt az attribútumot?', (choice) => {
            if (choice === 'yes') {
                API.call({
                    url: 'attributes/' + record.get('id'),
                    method: 'DELETE'
                }).then((response) => {
                    if (response.success) {
                        Ext.toast('Attribútum törölve');
                        grid.getStore().reload();
                    } else {
                        Ext.Msg.alert('Hiba', response.message);
                    }
                });
            }
        });
    }
});