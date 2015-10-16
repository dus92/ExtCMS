Ext.define('duscms.store.StoreModulesLocal', {
    extend: 'Ext.data.Store',
    model: 'duscms.model.ModelModulesLocal',
    proxy: {
		type: 'ajax',
		url: 'json.json',
		reader: {
			type: 'json',
			root: 'data'
		}
	},
    autoload: true,
    storeId: 'StoreModulesLocal'
});