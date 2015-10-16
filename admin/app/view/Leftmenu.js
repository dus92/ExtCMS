//отображает панель фильтров (слева), по умолчанию скрыта
Ext.define('duscms.view.Leftmenu', {
    extend: 'Ext.grid.Panel',
    //requires: ['Ext.grid.Panel'],
    xtype: 'Leftmenu',
	//vals: [],
	//hidden: true,
	title: 'Панель управления',
	border: true,
	width: 300,
	minWidth: 300,
    maxWidth: 400,
	split: true,
	//stateful: true,
	//stateId: 'mainnav.west',
	collapsible: true,
	animCollapse: false,
	collapsed: false,
	titleCollapse: true,
    hideHeaders: true,
    
    initComponent: function(){
        var me = this;
        var rights = duscms.getApplication().rights;
        var data = [];
        
        var store = duscms.getApplication().getController('ControllerLeftmenu').getAllowedModulesStore();
        
        var groupingFeature = Ext.create('Ext.grid.feature.Grouping',{
            groupHeaderTpl: '<span style="color:#1D5F8F;">{name}</span> ({rows.length} модул{[(values.rows.length == 2 || values.rows.length == 3 || values.rows.length == 4) ? "я" : "ей"]})'
        });
        
        me.store = store;
        me.columns = [{             
            text: 'Модуль',  
            dataIndex: 'name',
            flex: 1
        }];
        me.features = [groupingFeature];
                  
        me.callParent(arguments);
    }
	
});
