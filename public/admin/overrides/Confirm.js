Ext.define('GeboApp.Confirm', {
	extend: 'Ext.MessageBox',
    alias: 'widget.GeboConfirm',

    hideAnimation: null,

    constructor : function(){
        //call parent class constructor
        this.callParent(arguments);
    },

    show : function(msgBoxOptions, options)
    {
        
        
        msgBoxOptions.buttons =  [
            {
                text: 'DA',
                localized : {
                    text : 'Label.yes'
                },
                itemId: 'yes',
                ui : 'confirm'
            }, 
            {
                text: 'NU',
                localized : {
                    text : 'Label.no'
                },
                itemId: 'no',
                ui : 'decline'
            }
        ];

        this.callParent(arguments);
        
    }
    

});
