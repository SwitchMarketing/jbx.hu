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
            view.getStore().load()
        }, this);
	},

    onReloadCategories : function () { 
        this.getView().getStore().reload()
    },

    onEditItem: function (grid, info) {
        let record = info.record;

        let dialog = Ext.create({
            xtype: 'dialog',
            title: 'Kategória szerkesztése',
            width: 400,
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
                        value: record.get('name'),
                        required: true
                    },
                    {
                        xtype: 'textfield',
                        label: 'Slug',
                        name: 'slug',
                        value: record.get('slug'),
                        required: true
                    }
                ]
            }],
            buttons: {
                save: {
                    text: 'Mentés',
                    handler: function () {
                        let form = dialog.lookup('form');
                        if (form.validate()) {
                            let values = form.getValues();

                            API.call({
                                url: 'categories/' + record.get('unas_id'),
                                method: 'PUT',
                                data: values
                            }).then((response) => {
                                if (response.success) {
                                    Ext.toast('Sikeres mentés');
                                    grid.getStore().reload();
                                    dialog.destroy();
                                } else {
                                    Ext.Msg.alert('Hiba', response.message);
                                }
                            });
                        }
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