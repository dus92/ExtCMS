/////////////////////////////////////////////////////
//модуль: Основное управление - Управление модулями//
/////////////////////////////////////////////////////

Ext.define('duscms.view.general.Menus', {
    extend: 'Ext.form.Panel',
    xtype: 'menus',
    height: 'auto',
    overflow: 'auto',
    autoScroll: true,
    margin: 0,
    flex: 1,
    minHeight: $(window).height()/2,
    layout: {
        type: 'hbox',
        align: 'stretch'
    },
    initComponent: function(){
        var me = this;
        var items_arr = [];
                
        var group = this.id + '-ddgroup';
        
        //top buttons
        me.tbar = [{ 
            xtype: 'button', 
            text: 'Добавить',
            iconCls: 'x-tbar-add'
        }];
        
        /////////////
        var actionColumns = [{
            xtype:'actioncolumn',
            width: 20,
            iconCls: 'icon-edit',
            tooltip: 'Редактировать',
            getClass: function(v, meta, rec) {
                if(!rec.get('ucm'))
                    return false;
                return 'icon-edit';
            },
            handler: function(grid, rowIndex, colIndex){
				me.fireEvent('onUcmEdit', grid.getStore().getAt(rowIndex));
			}
       },{
            xtype:'actioncolumn',
            width: 25,
            iconCls: 'icon-delete',
            tooltip: 'Удалить',
            getClass: function(v, meta, rec) {
                if(!rec.get('ucm'))
                    return false;
                return 'icon-delete';
            },
            handler: function(grid, rowIndex, colIndex){
				me.fireEvent('onUcmDelete', grid.getStore().getAt(rowIndex));
			}
       }];
       
       var columnsTree = [{
            xtype : 'treecolumn',
            text : "Модуль",
            flex: 1,
            dataIndex : 'name',
            sortable : false,
            menuDisabled : true,
            resizable: false
       }];
       
       var columnsGrid = [{
            text : "Неиспользуемые модули",
            flex: 1,
            dataIndex : 'name',
            sortable : false,
            menuDisabled : true,
            resizable: false
       }];
       
       columnsTree.push(actionColumns[0], actionColumns[1]);
       columnsGrid.push(actionColumns[0], actionColumns[1]);
        
        me.items = [{
           xtype: 'treepanel',
           itemId: 'modulesTree',
           flex: 1,
           root: {
                id: 'root',
                name: "Модули"
           },
           folderSort: true,	
           loadMask: false,
           store: me.storeCurrent,
           hideHeaders: true,
           viewConfig: {
                plugins: {
                    ptype: 'treeviewdragdrop',
                    ddGroup: group,
                    //appendOnly: true,
                    sortOnDrop: true,
                    enableSort : true,
                    containerScroll: true
                },
                listeners: {
                    nodedragover: function(targetNode, position, dragData){
                        var record = dragData.records[0];
                        
                        if(!record.get('isParent') && targetNode.get('id') == 'root'){
                            return false;
                        }
                        else if(record.get('isParent') && targetNode.get('isParent')){
                            return false;
                        }
                        else if(record.get('isParent') && targetNode.isLeaf()){
                            return false;
                        }
                    },
                    beforedrop: function(node, data, overModel, dropPosition, dropHandlers){
                        var record = data.records[0];
                        
                        if(!record.hasChildNodes){
                            record.set('leaf', true);
                            record.commit();  
                        }
                    },
                    drop: function(node, data, dropRec, dropPosition) {
                        var record = data.records[0];
                        
                        if((dropRec.isLeaf() && dropRec.get('isParent')) || !dropRec.isLeaf()){
                            dropRec.insertChild(0, record);
                            dropRec.set({
                                leaf: false
                            });
                            dropRec.commit();
                            dropRec.expand();
                        }
                        
                        me.down('grid').getStore().remove(data.records[0]);
                    }
                }
           },
           columns: columnsTree,
           listeners: {
                itemdblclick: function( tree, record, item, index, e, eOpts ){
                    if(record.get('ucm'))
                        me.fireEvent('onUcmEdit', record);
                }
           }
        },{
            xtype: 'grid',
            store: me.storeUnused,
            flex: 1,
            viewConfig: {
                plugins: {
                    ptype: 'gridviewdragdrop',
                    ddGroup: group
                },
                listeners: {
                    drop: function(node, data, dropRec, dropPosition) {
                        var record = data.records[0];
                        
                        if(record.get('isParent')){
                            if(record.hasChildNodes()){
                                var i = 0;
                                record.eachChild(function(rec){
                                    me.down('grid').getStore().insert(me.down('grid').getStore().indexOf(record)+i+1, rec);
                                    i++;
                                });
                            }
                        }
                        record.removeAll();
                        record.remove();
                    }
                }
            },
            columns: columnsGrid,
            listeners: {
                itemdblclick: function( tree, record, item, index, e, eOpts ){
                    if(record.get('ucm'))
                        me.fireEvent('onUcmEdit', record);
                }
           }
        }];
        
          
        me.callParent();
    }
    
});
