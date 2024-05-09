Ext.define('JBXAdmin.util.Common', {
	alternateClassName: ['Common'],
	singleton: true,
	toast: function(msg) {
		Ext.toast({
			message: msg, 
			timeout: 800,
			alignment : 'c-c'
		});
	},
	log: function(msg) {
		console.log(msg);
	}
});