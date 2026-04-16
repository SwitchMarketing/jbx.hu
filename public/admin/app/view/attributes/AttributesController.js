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