/////////////////////////////////////////////////////
//модуль: Основное управление - Управление модулями//
/////////////////////////////////////////////////////

Ext.define('duscms.view.general.ManageModules', {
    extend: 'Ext.grid.Panel',
    xtype: 'manageModules',
    //border: false,
    height: 'auto',
    overflow: 'auto',
    autoScroll: true,
    margin: 0,
    flex: 1,
    //store: null,
    columns: [{
        text: 'Модуль',
        dataIndex: 'title',
        flex: 1,
        maxWidth: 350,
        menuDisabled: true
    },{
        text: 'Создатель',
        dataIndex: 'copyright',
        width: 200,
        align: 'center',
        menuDisabled: true
    },{
        text: 'Права',
        dataIndex: 'rights',
        flex: 1,
        menuDisabled: true,
        sortable: false,
        renderer: function(value, metadata, record){
            if(value.length == 0)
                return '<span style="color: silver;">Права не указаны</span>';
            else{
                var str = '';
                for(var i in value){
                    str += value[i] + '<br />';
                }
                return str;
            }
        }
    },{
        xtype: 'checkcolumn',
        dataIndex: 'checked',
        text: 'Отключить',
        width: 100,
        resizable: false,
        sortable: false,
        menuDisabled: true,
        listeners: {
            checkchange: function( ch, rowIndex, checked, eOpts ){
                var grid = ch.ownerCt.grid;
                grid.fireEvent('manageModulesSave', grid.getStore().getAt(rowIndex), checked);
            }
        }
    }],
    listeners: {
        itemclick: function( el, record, item, index, e, eOpts ){
            this.fireEvent('manageModulesSave', record, !record.get('checked'));
        }
    }
});
