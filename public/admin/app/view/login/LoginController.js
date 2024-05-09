Ext.define('JBXAdmin.view.login.LoginController', {
    extend: 'Ext.app.ViewController',
    alias: 'controller.logincontroller',

    init: function (view) {
        
        /**
         * 
         * bejelentkezési név focus
         * 
         */
        view.on('painted', function() {
            view.down('textfield[name="uname"]').focus();
        }, this);
        
	},

    /**
     * 
     * Az ENTER kezelése a felhasználó név beviteli mezőben
     *   
     * @param {*} input 
     * @param {*} e 
     */
    onUnameKeyup: function(input, e) {
        if (e.browserEvent.keyCode == 13 ) {
            e.stopEvent();
            if(input.getValue().length > 0)
                this.view.down('textfield[name="upass"]').focus();            
        }
        else {
            this.enableLoginButton();
        }
    },

    /**
     * 
     * Az ENTER kezelése a jelszó beviteli mezőben
     *   
     * @param {*} input 
     * @param {*} e 
     */
    onUpassKeyup: function(input, e) {
        if (e.browserEvent.keyCode == 13 ) {
            e.stopEvent();
            this.login();
        } else {
            this.enableLoginButton();
        }
    },

    /**
     * 
     * A bejelentkezés gomb lenyomása
     * 
     */
    onLoginTap: function() {
        this.login();
    },


    /**
     * 
     * a gomb kikapcsolása
     * 
     */
    enableLoginButton: function()
    {
        var disabled = !(this.view.down('textfield[name="uname"]').getValue() && this.view.down('textfield[name="upass"]').getValue());
        this.view.down('button').setDisabled(disabled);
    },

    /**
     * 
     * Bejelentkezés
     * 
     */
    login: function(encoded) {

        var form = this.getView();

        if(form && form.isValid())
        {
            form.submit({
                url: API.apiBase + 'login',
                method:'POST',
                params : {
                    encoded : encoded || false
                },
                success: function(frm, response) {
                    // remove login window
                    form.destroy();
                    //start application
                    JBXAdmin.getApplication().startApp();                    
                }
            });
        }

    }
    
});
