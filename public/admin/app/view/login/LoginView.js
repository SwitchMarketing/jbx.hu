Ext.define('JBXAdmin.view.login.LoginView', {
    fullscreen: true,
	extend: 'Ext.form.Panel',
	xtype: 'login',
	id: 'loginview',
	reference: 'loginview',
	controller: 'logincontroller',

    viewModel: {
        type: 'main'
    },	

    layout: {
        type: 'vbox',
        align : 'center',
        pack: 'center'
    },

    cls: 'loginview',

    bodyPadding: 10,

    items: [
        {
            xtype : 'fieldset',

            platformConfig: {
                desktop: {
                    minWidth: 300,
                    maxWidth: 400
                },
                phone: {
                    minWidth: '90%',
                    maxWidth: '100%'
                }
            },

            bind : {
                title : '{appTitle} - Belépés'
            },

            defaults : {
                labelAlign   : 'top',
                autoComplete : false,
                allowBlank   : false
             },

            items : [
                {
                    xtype           : 'textfield',
                    name            : 'uname',
                    label           : 'Felhasználó',
                    listeners   : {
                        keyup : 'onUnameKeyup'
                    }
                },
                {
                    xtype           : 'textfield',
                    name            : 'upass',
                    inputType       : 'password',
                    label           : 'Jelszó',
                    listeners   : {
                        keyup          : 'onUpassKeyup'
                    }
                },
                {
                    xtype   : 'container',
                    layout  : 'hbox',
                    margin  : '10 0 0 0',
                    items   : [
                        {
                            xtype           : 'button',
                            text            : 'Bejelentkezés',
                            iconAlign       : 'left',
                            iconCls         : 'x-fa fa-unlock',
                            flex            : 1,
                            ui              : 'confirm',
                            handler         : 'onLoginTap',
                            disabled        : true
                        }
                    ]
                }
            ]
        }
    ]
    
});
