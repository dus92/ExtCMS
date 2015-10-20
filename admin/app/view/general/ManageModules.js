/////////////////////////////////////////////////////
//модуль: Основное управление - Управление модулями//
/////////////////////////////////////////////////////

Ext.define('duscms.view.general.ManageModules', {
    extend: 'Ext.grid.Panel',
    
    xtype: 'manageModules',
    border: false,
    height: 'auto',
    overflow: 'auto',
    autoScroll: true,
    layout: 'fit',
    bodyPadding: 5,
    initComponent: function(){
        var me = this;
        
        
        me.callParent(arguments);
    }
});
