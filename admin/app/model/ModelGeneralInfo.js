Ext.define('duscms.model.ModelGeneralInfo',{
	extend: 'Ext.data.Model',    
	fields: [{
		name: 'title',
		type: 'string'
	},{
		name: 'rights',
		type: 'object'
	},{
		name: 'moduleName',
		type: 'string'
	},{
		name: 'moduleDesc',
		type: 'string'
	},{
		name: 'text',
		type: 'object'
	},{
		name: 'httpd',
		type: 'string'
	},{
		name: 'php',
		type: 'string'
	},{
		name: 'cms',
		type: 'string'
	},{
		name: 'moderationCount',
		type: 'int'
	},{
		name: 'moderation',
		type: 'string'
	},{
		name: 'feedbackCount',
		type: 'int'
	},{
		name: 'feedback',
		type: 'string'
	},{
		name: 'leaveMsg',
		type: 'string'
	},{
		name: 'remarks',
		type: 'string'
	}]
});