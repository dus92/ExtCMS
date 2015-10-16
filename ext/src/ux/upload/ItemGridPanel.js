/**
 * The grid displaying the list of uploaded files (queue).
 * 
 * @class Ext.ux.upload.ItemGridPanel
 * @extends Ext.grid.Panel
 */
Ext.define('Ext.ux.upload.ItemGridPanel', {
    extend : 'Ext.grid.Panel',

    requires : [
        'Ext.selection.CheckboxModel', 'Ext.ux.upload.Store'
    ],

    //layout : 'fit',
    overflow: 'auto',
    maxHeight: 200,
    minHeight: 70,    
    autoScroll: true,
    border : false,

    config : {
        queue : null,

        textFilename : 'Filename',
        textSize : 'Size',        
        textType : 'Type',
        textExt: 'Ext',
        textStatus : 'Status',
        textProgress : '%',
        emptyText: 'Нет файлов, доступных для загрузки на сервер',
        
        fileSize: 10,
        fileFormat: 'all',
        viewConfig: {
	        scrollOffset : 40
	    }
    },

    constructor : function(config) {
        this.initConfig(config);

        return this.callParent(arguments);
    },

    initComponent : function() {
		var me = this;
        
		if (this.queue) {
            this.queue.on('queuechange', this.onQueueChange, this);
            this.queue.on('itemchangestatus', this.onQueueItemChangeStatus, this);
            this.queue.on('itemprogressupdate', this.onQueueItemProgressUpdate, this);
        }

        Ext.apply(this, {
            store : Ext.create('Ext.ux.upload.Store'),
            selModel : Ext.create('Ext.selection.CheckboxModel', {
                //checkOnly : true
            }),
            multiSelect: true,
            columns : [
                {
                    xtype : 'rownumberer',
                    width : 40,
                    resizable: false,
					menuDisabled: true
                }, {
                    dataIndex : 'filename',
                    header : this.textFilename,
                    flex : 1,
                    minWidth: 100,
                    renderer: function (value, metaData, record, row, col, store, gridView) {
                    	//TODO change to getExt() <--> fileFormat
						if(record.get('url') && record.get('url')!=''){
                    		if(me.fileFormat == 'img')
								return value + '<br /><img src="'+record.get('url')+'" width="100" border="0" />';
							else
								return '<a href="download.php?att_file='+record.get('url')+'" title="Скачать">'+value+'</a>';
                    	}
                    	return value;
                    }
                }, {
                    dataIndex : 'size',
                    header : this.textSize,
                    width : 100,
                    resizable: false,
					menuDisabled: true,
                    renderer : function(value) {
                        return Ext.util.Format.fileSize(value);
                    }
                }, {
                    dataIndex : 'type',
                    header : this.textType,
                    width : 150,
                    minWidth: 80,
					menuDisabled: true,
					renderer: function(value){
						if(!value || value == '')
							return '<span style="color: silver;">Не определен</span>';
						return value;
					}
                }, {
                    dataIndex : 'ext',
                    header : this.textExt,
                    width : 110,
                    align: 'center',
                    resizable: false,
					menuDisabled: true 
                }, {
                    dataIndex : 'progress',
                    header : this.textProgress,
                    width : 60,
                    resizable: false,
					menuDisabled: true,
					sortable: false,
                    align : 'right',
                    renderer : function(value) {
                        if (!value) {
                            value = 0;
                        }
                        return value + '%';
                    }
                }, {
                    dataIndex : 'status',
                    //header : this.textStatus,
                    width : 40,
                    resizable: false,
					menuDisabled: true,
					sortable: false,
                    align : 'right',
                    renderer : this.statusRenderer
                }, {
                    dataIndex : 'message',
                    width : 1,
                    hidden : true
                }
            ]
        });
        
        me.on({
        	select: function( el, record, index, eOpts ){        						
				me.up('fileupload').down('uploadgrid').down('toolbar').down('#button_remove_selected').enable();
        	},
        	deselect: function( el, record, index, eOpts ){
        		var grid = me;
				var btn_remove_selected = grid.up('fileupload').down('uploadgrid').down('toolbar').down('#button_remove_selected'); 
				if(!grid.getSelectedRecords().length)
        			btn_remove_selected.disable();
       			else
       				btn_remove_selected.enable();
        	}
        });
        
        me.getStore().on({
        	datachanged: function( store, eOpts ){
        		var panel = me.up('panel');
        		var disabled = true;
				if(store.getCount() > 0){
        			store.each(function(rec){
        				if(!panel.validateFile(rec).error){        					
							disabled = false;	
						}
						else{
							rec.set('status', 'uploaderror');
        					rec.commit();
						}
        			});
        		}
        		
        		panel.down('toolbar').down('#button_upload').setDisabled(disabled);
        			
        	}
        });

        this.callParent(arguments);
    },

    onQueueChange : function(queue) {
		this.loadQueueItems(queue.getItems());
    },

    onQueueItemChangeStatus : function(queue, item, status) {
        this.updateStatus(item);
    },

    onQueueItemProgressUpdate : function(queue, item) {
        this.updateStatus(item);
    },

    /**
     * Loads the internal store with the supplied queue items.
     * 
     * @param {Array} items
     */
    loadQueueItems : function(items) {
        var data = [];
        var i;
        var err = 0;
		
        for (i = 0; i < items.length; i++) {
//            err = 0;
//			if(this.store.getCount() > 0){
//				this.store.each(function(rec){
//					if(rec.get('filename') == items[i].getFilename() && rec.get('size') == items[i].getSize()){
//						err = 1;			
//					}
//				});
//			}
//			if(err)
//				continue;
			
			data.push([
                items[i].getFilename(),
                items[i].getSize(),
                items[i].getType(),
                items[i].getExt(),
                items[i].getProgressPercent(),
                items[i].getStatus(),
                items[i].getUrl()
            ]);
        }

        this.loadStoreData(data);
    },

    loadStoreData : function(data, append) {
		this.store.loadData(data, append);		
		//this.store.add(data);
    },

    getSelectedRecords : function() {
        return this.getSelectionModel().getSelection();
    },

    updateStatus : function(item) {
        var record = this.getRecordByFilename(item.getFilename());
        if (!record) {
            return;
        }

        var itemStatus = item.getStatus();
        // debug.log('[' + item.getStatus() + '] [' + record.get('status') + ']');
        if (itemStatus != record.get('status')) {
            this.scrollIntoView(record);

            record.set('status', item.getStatus());
            if (item.isUploadError()) {
                record.set('tooltip', item.getUploadErrorMessage());
            }
        }

        record.set('progress', item.getProgressPercent());
        record.commit();
    },

    getRecordByFilename : function(filename) {
        var index = this.store.findExact('filename', filename);
        if (-1 == index) {
            return null;
        }

        return this.store.getAt(index);
    },

    getIndexByRecord : function(record) {
        return this.store.findExact('filename', record.get('filename'));
    },

    statusRenderer : function(value, metaData, record, rowIndex, colIndex, store) {
        var iconCls = 'ux-mu-icon-upload-' + value;
        var tooltip = record.get('tooltip');
        if (tooltip) {
            value = tooltip;
        } else {
            'upload_status_' + value;
        }
        //value = '<span class="ux-mu-status-value ' + iconCls + '" data-qtip="' + value + '" />';
        value = '<span class="ux-mu-status-value ' + iconCls + '" />';
        return value;
    },

    scrollIntoView : function(record) {

        var index = this.getIndexByRecord(record);
        if (-1 == index) {
            return;
        }

        this.getView().focusRow(index);
        return;
        var rowEl = Ext.get(this.getView().getRow(index));
        // var rowEl = this.getView().getRow(index);
        if (!rowEl) {
            return;
        }

        var gridEl = this.getEl();

        // debug.log(rowEl.dom);
        // debug.log(gridEl.getBottom());

        if (rowEl.getBottom() > gridEl.getBottom()) {
            rowEl.dom.scrollIntoView(gridEl);
        }
    }
});