Ext.define('JBXAdmin.util.API', {
	alternateClassName: ['API'],
	singleton: true,

	apiBase : '/admin/',
	
	/**
	 * 
	 * API hívás kezelése
	 * 
	 * @param {*} params 
	 */
	call: function(params)
	{
		var me = this;
		
		if(!Ext.isObject(params))
		{
			Ext.Msg.alert('HIBA', 'params not set!');
			return false;
		}

		if(!params.url)
		{
			Ext.Msg.alert('HIBA', 'params.url not set!');
			return false;
		}

		return new Ext.Promise(function (resolve, reject) {

			Ext.Ajax.request({
				url			: me.apiBase+params.url,
				method 		: params.method || 'GET',
				showMask 	: true,
				params 		: params.data || '',
				success: function(response, opts) {
					var resp = Ext.util.JSON.decode(response.responseText);
					resolve(resp);
				},
				failure: function(operation) {
					reject(operation);
				}
			});
			
    	});
	},

	getSession: function()
	{
		return this.call({
			url : 'sessiondata'
		});
	},

	logout: function()
	{
		return this.call({
			url : 'logout'
		}).then(function(result){
			window.location.reload();
		});
    }
});