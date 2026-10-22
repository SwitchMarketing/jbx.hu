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
	/**
	 * Ext.Msg.confirm párja törléshez: piros "Törlés" és semleges "Mégse" gomb.
	 * A callback ugyanúgy a lenyomott gomb azonosítóját kapja ('yes' = törlés).
	 */
	confirmDelete: function(title, message, fn, scope) {
		Ext.Msg.show({
			title: title,
			message: message,
			buttons: {
				cancel: 'me.onClick',
				yes: { text: 'Törlés', ui: 'jbx-danger', handler: 'me.onClick' }
			},
			fn: fn,
			scope: scope
		});
	},
	log: function(msg) {
		console.log(msg);
	}
});