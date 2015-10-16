Ext.define('duscms.view.Viewport', {
    xtype: 'viewport',
    extend: 'Ext.container.Viewport',
    requires:[
        'Ext.tab.Panel',
        'Ext.layout.container.Border'
    ],
        
    layout: 'border',
    //инициализация общей структуры сайта (что где будет находиться)
    initComponent: function(){
        var me = this;        
        Ext.apply(me,{
            items: [{
                region: 'north',
                xtype: 'Header'        
            },
            {
                region: 'west',
                xtype: 'Leftmenu'
            },
            {
                region: 'center',
                xtype: 'mainContent'
            }]
        });
        
        me.callParent();
    }    
});
