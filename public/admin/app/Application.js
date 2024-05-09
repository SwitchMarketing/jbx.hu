/**
 * The main application class. An instance of this class is created by app.js when it
 * calls Ext.application(). This is the ideal place to handle application launch and
 * initialization details.
 */
Ext.define('JBXAdmin.Application', {
    extend: 'Ext.app.Application',

    name: 'JBXAdmin',

    quickTips: false,
    platformConfig: {
        desktop: {
            quickTips: true
        }
    },

    init: function() {
        this.splashScreen = Ext.get('splash');
    },

    /**
     * 
     * az app indítása
     * 
     */
    launch: function () {

		API.getSession().then((result) => {
			if(result.success)
			{
				this.startApp();
			}
			else
			{
				this.showLogin();
			}	
		});        

	},

    /**
	 * 
	 * a bejelentkezási képernyő
	 * 
	 */
	showLogin: function() {
        
		this.splashScreen.hide();
        Ext.Viewport.add([{ xtype: 'login' }]);

    },

    /**
	 * 
	 * az alkalmazás indítása
	 * 
	 */
	startApp: function () {

		this.splashScreen.remove();
			
        Ext.Viewport.add (
            Ext.create({
                xtype: 'app-main'
            })
        );       

    },

    onAppUpdate: function () {
        Ext.Msg.confirm('Application Update', 'This application has an update, reload?',
            function (choice) {
                if (choice === 'yes') {
                    window.location.reload();
                }
            }
        );
    }
});
