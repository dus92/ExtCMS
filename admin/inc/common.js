///////////////////////////////////////////////////////////////
/////////////Функции валидации, обработки форм...//////////////
///////////////////////////////////////////////////////////////
//функция валидации при отправке данных формы (выводит соотв. сообщение об ошибке или успехе) 
var myTimeout;
function validateFormMsg(panel, msg, success){
	
	if(myTimeout) clearTimeout(myTimeout);
	
	if(panel){
		if(panel.down('#validateMessage')){
			panel.remove('validateMessage');
		}
		
		panel.insert(0,{
			xtype: 'fieldcontainer',
			itemId: 'validateMessage',
			anchor: '100%',
			layout: {
			     type: 'hbox',
                 align: 'middle'
			},
			cls: success ? 'validateFieldSuccess' : 'validateFieldError',
			border: false,
			items: [{
				xtype: 'displayfield',
				value: msg,
				submitValue: false,
                flex: 1
			},{
			    xtype: 'box',
                autoEl: {
                    tag: 'div',
                    tooltip: 'Закрыть'
                },
                height: 16,
                width: 16,
                margin: '0 10 0 0',
                cls: success ? 'icon-success-close' : 'icon-error-close',
                border: false,
                listeners: {
                    afterrender: function( el, eOpts ){
                        el.getEl().on('click', function(e){
                            el.up('#validateMessage').hide();
                        });
                    }
                }
			}]
		});
	}
	
//	myTimeout = setTimeout(function(){
//		panel.down('#validateMessage').getEl().slideOut('t', {
//			easing: 'easeOut',
//	        duration: 300,
//	        remove: false,
//	        useDisplay: false,
//            callback: function(){
//                panel.remove('#validateMessage');
//            }
//		});
//	}, 2000);
}

//ajax запрос
function ajaxRequest(panel, config, hideMask){
	var hideMask = hideMask || 0;
	if(!hideMask){
		var myMask = new Ext.LoadMask(panel, {msg: 'Обработка данных...'});
		myMask.show();
	}
	
	Ext.Ajax.request({
		url: 'api.php?act='+config.url,
		method:'POST',
		params: config.params,
		success:  function(response, opts){							
			var result = Ext.JSON.decode(response.responseText);
			if(!hideMask) myMask.hide();
			
			if(result.success==true){
				if(config.success) config.success(result.data);
			}
			else{
				var msg = result.msg;
				if(msg)
					Ext.Msg.error('Ошибка', msg);
				else
					Ext.Msg.error('Ошибка', 'Ошибка');
				
				//if(config.nosuccess) config.nosuccess();
			}
		},
		failure:  function(response){
			var msg = response.result.msg;
			if(!hideMask) myMask.hide();
			
			if(msg) Ext.Msg.error('Ошибка', msg); else Ext.Msg.error('Ошибка', 'Ошибка');
		}
	});
}

//функция отправки данных формы
function onSubmitForm(f, config){
	if(f.xtype !== 'form'){
		Ext.Msg.error('Ошибка', 'Компонент не является формой!');
		return false;
	}
	var form = f.getForm();
	var values = f.getValues();
	var myMask = new Ext.LoadMask(f, {msg: 'Обработка данных...'});
		myMask.show();
	
	Ext.Ajax.request({
		url: 'api.php/act/'+config.url,//'api.php?act='+config.url,
		method:'POST',
		params: values,
		success:  function(response, opts){							
			var result = Ext.JSON.decode(response.responseText);
			myMask.hide();
			
			if(result.success==true){
				if(config.success) 
					config.success(result.data);
				else
					validateFormMsg(config.vMsgForm||f, 'Данные формы успешно отправлены', 1);
			}
			else{
				var msg = result.msg;
				if(msg)
					validateFormMsg(config.vMsgForm||f, msg, 0);
				else
					validateFormMsg(config.vMsgForm||f, 'Ошибка', 0);
				
				//if(config.nosuccess) config.nosuccess();
			}
		},
		failure:  function(response){
			var msg = response.result.msg;
			myMask.hide();
			
			if(msg) 
				validateFormMsg(config.vMsgForm||f, msg, 0); 
			else 
				validateFormMsg(config.vMsgForm||f, 'Ошибка', 0);
		}
	});
}

//формирование стора по заданной модели
function createStore(model, config){
    if(model){
        var start = config.pager ? 'start' : false;
        var limit = config.pager ? 'limit' : false;
        var store = Ext.create('Ext.data.Store', {
            storeId: 'allowedModulesStore',
            model: 'duscms.model.' + model,
            groupField: config.groupField ? config.groupField : false,
            pageSize: config.pageSize ? config.pageSize : false,
            autoLoad: config.autoLoad ? config.autoLoad : false,
            proxy: {
                type: 'ajax',
        		url: 'api.php',
                noCache: config.noCache ? config.noCache : false,
                limitParam: limit,
                startParam: start,
                pageParam: config.pager ? 1 : false, 
        		extraParams: {
        			act: config.action
        		},
                reader: {
                    type: 'json',
                    root: 'data'
                },
                actionMethods: {
			        create: 'POST',
			        destroy: 'DELETE',
			        read: 'POST',
			        update: 'POST'
			    }
            },
            storeId: config.storeId ? config.storeId : 'Store' + model.substr(5) 
        });
        return store;
    }
    else
        return false;
        //TODO 
        //обработать случай, когда не задана модель (добавить возможность создания модели в ф-и)
}


///////////////////////////////////////////////////////////////
//override fields, defining new components
//integer number field
Ext.define('duscms.form.field.integer', {
    extend: 'Ext.form.field.Number',
    alias: ['widget.integer'],
    allowDecimals: false,
    allowNegative: false,
    minValue: 0,
    maxValue: 2147483647,
	enableKeyEvents: true,
	minText: 'Минимальное значение в данном поле: {0}',
	maxText: 'Максимальное значение в данном поле: {0}',
	listeners: {
        specialkey: function (f, e) {
            if (e.getKey() == e.ENTER) {
                if (this.filter)
                    this.filter();
            }
        }
    }
});

//file upload form
Ext.define('duscms.form.fileUpload',{
    extend: 'Ext.form.Panel',
    alias: ['widget.fileUpload__', 'widget.fileupload__'],
	//title: 'Форма Запроса',
    border: false,
    //width: 400,
    //height:150,
    name: 'filename',
    fieldLabel: 'Выберите файл:',
    buttonText: 'Обзор...',
    labelWidth: 100,
    multiple: true,
    fileSize: 10,
    fileFormat: 'all', //'img', 'file', 'all'  
    
    initComponent: function(){
    	var me = this;
    	me.items = [{
	        xtype: me.multiple ? 'multifilefield' : 'fileuploadfield',
	        extend: 'Ext.ux.form.FileUploadField',
	        itemId: 'fileUpload',
	        name: me.name,
	        fieldLabel: me.fieldLabel,
	        labelWidth: me.labelWidth,
	        buttonText: me.buttonText,
	        buttonOnly: true,
	        allowBlank: false,
	        fileSize: me.fileSize, //Max size of file (Mb)
	        fileFormat: me.fileFormat,
	        
	        listeners: {
	        	change: function( el, value, eOpts ){	        		
					var grid = me.down('grid')
					var store = grid.getStore();
					var form = me.getForm();
     				var upload = this.fileInputEl.dom;
        			var files = upload.files;
        			var oldFile = 0; //0 -file is new, 1 - file already added
        			        			        			
        			//add to function
  			      	if(grid.getStore().getCount() == 0){
	        			grid.show();
						grid.getEl().fadeOut({
	                        endOpacity: 1,
	                        easing: 'easeOut',
	                        duration: 10,
	                        callback: function(){
	                        	grid.setPosition(0,0);
			                    grid.getEl().fadeIn({
		                            endOpacity: 1,
		                            easing: 'easeOut',
		                            duration: 500
			                    });	
	                        }
	                    });
					}
					//////////
					
					
					if(files.length > 0){
						if(!me.multiple){
							store.removeAll(); //можно загрузить только один файл, если multiple = false
						}
						
        				for (var i = 0; i < files.length; i++){
							oldFile = 0;
							if(store.getCount() > 0){
								store.each(function(rec){
									if(rec.get('fileName') == files[i].name && rec.get('fileType') == files[i].type && rec.get('fileSize') == files[i].size){
										oldFile = 1;
									}	
								});
							}
							if(!oldFile){
								store.add({
	        						id: store.getCount()+1,
									fileName: files[i].name,
									fileType: files[i].type,
									fileSize: files[i].size,
									ext: files[i].name.substr(files[i].name.lastIndexOf('.')+1),
									uploaded: 0 //0 - файл добавлен, но не загружен на сервер, 1 - в противном случае
	        					});
        					}
        				}
        			}
        			


					var file = this.getEl().down('input[type=file]').dom.files[0];
					
					//var upload = this.fileInputEl.dom;
        			//var files = upload.files;
					//console.log(files);
             		var reader = new FileReader();
             		//reader.onload = function (oFREvent) {
//					    //console.log(oFREvent.target.result);
//					    var file_arr = [];
//					};
					//this.reset();
					this.fileInputEl.set({ multiple: me.multiple });
             		//filecontent=reader.readAsBinaryString(file);
	        	}
	        }
	    },{
		    xtype: 'grid',
		    margin: '0 0 5 0',
		    hidden: true,
		    flex: 1,
		    //height: 200,
		    store: new Ext.data.Store({
			     fields: ['id', 'fileName', 'fileType', 'ext', 'fileSize', 'uploaded'],
			     storeId: 'filesStore'
			}),
			viewConfig: {
		        getRowClass: function(record, index, rowParams, store) {
		            return me.validateFile(record).error ? 'gridfiles-row-notValid' : 'gridfiles-row-valid';
		        },		        
                listeners: {
                    render: me.createTooltip
                }
		    },		    
			columns: [{ 
				text: 'Имя файла',
				dataIndex: 'fileName', 
				flex: 1 
			},{ 
				text: 'Тип',
			 	dataIndex: 'fileType',
			 	width: 150,
				maxWidth: 200,
				minWidth: 150,
				renderer: function (value, metaData) {
					if(!value || value == '')
						return '<span style="color: silver;">Не определен</span>';
					return value;
				}
			},{ 
				text: 'Расширение',
			 	dataIndex: 'ext',
			 	width: 100,
			 	align: 'center',
				resizable: false,
				menuDisabled: true 
			},{
			 	text: 'Размер',
			  	dataIndex: 'fileSize',
				width: 100,
				resizable: false,
				menuDisabled: true,
				renderer: function (value, metaData) {
					var size = value/1048576; //mb
					if(size >=1024){
						return parseFloat(Ext.util.Format.number(size/1024, '0.00')) + ' Gb';
					}
					if(size >= 1){
						return parseFloat(Ext.util.Format.number(size, '0.00')) + ' Mb';
					}
					else if(size<1 && size>0.001){
						return parseFloat(Ext.util.Format.number(size*1000, '0.00')) + ' Kb';
					}
					else{
						return value + ' Bytes';
					}
				}
	  		},{ 
	        	xtype: 'actioncolumn',
	            width: 60,
	            resizable: false,
	            menuDisabled: true,
	            items: [{
	                iconCls: 'icon_download',
	                tooltip: 'Загрузить',
	                handler: function (grid, rowIndex, colIndex) {
	                    var rec = grid.getStore().getAt(rowIndex);
	                    var form = me.getForm();
	                    
	                    if(!me.validateFile(rec).error){
	       		            Ext.form.field.File.superclass.setValue.call(me.down('#fileUpload'), rec.get('fileName'));
		   					if (form.isValid()) {
								form.submit({
				                    url: 'api.php?act=filesBeforeUpload',
				                    enctype:'multipart/form-data',
				                    waitMsg: 'Загрузка...',
				                    success: function(fp, o){
				                        Ext.Msg.alert('Загрузка прошла успешно', 'Файл ' +o.result.file +" загружен");
				                    }
				                });
				            }
			            }
	                },
	                isDisabled: function(view, rowIndex, colIndex, item, record) {									
						if(me.validateFile(record).error)
							return true;
						return false;
					}
				},{
					iconCls: 'x-tbar-delbut del-but-margin',
					tooltip: 'Удалить',
	                handler: function (grid, rowIndex, colIndex) {
	                    var store = grid.getStore(); 
						var rec = store.getAt(rowIndex);
						if(rec.get('uploaded') == 0){
							store.remove(rec);
						}
						
						if(store.getCount() == 0){
							me.setPosition(0,0);
							me.down('grid').getEl().fadeOut({
		                        endOpacity: 0,
		                        easing: 'easeOut',
		                        duration: 500,
								callback: function(){me.down('grid').hide();}
		                    });
						}
	                }
				}]
			}],
			listeners: {
				render: function(el){
					el.setPosition(-1000,0);
				}
			}
	    }];
    		
    	me.callParent();
    },
    validateFile: function(record){
    	var res = new Object();
    	var me = this.up('form');
    	res.errStr = '';
    	res.error = 0;
    	var imgFormats = ['jpg', 'jpeg', 'png', 'gif', 'bmp'];
    	var fileFormats = ['doc', 'docx', 'xls', 'xlsx', 'pdf', 'txt', 'odt', 'rar', 'zip', 'tar'];
    	var allFormats = imgFormats.concat(fileFormats);
    	var format = [];
    	var validFormat = 0; //0 - valid, 1 - not valid (error)
    	
    	//validate size
		var size = record.get('fileSize')/1048576; //mb
    	if(parseInt(size) > this.fileSize){    		
    		
			var formatSize = parseFloat(Ext.util.Format.number(((size >= 1024) ? (size/1024) : size), '0.00'));
			var sizeStr = ' '+((parseInt(size) >= 1024)?'Gb':'Mb');
			
			res.errStr = 'Файл слишком большого размера ('+formatSize+sizeStr+'). Допустимый размер файла '+this.fileSize+' Mb <br />';
    	}
    	//validate format
    	switch(this.fileFormat){
    		case 'img':
    			format = imgFormats;
    			break;
   			case 'file':
   				format = fileFormats;
    			break;
   			case 'all':
   				format = allFormats;
    			break;
    	}
    	
    	for(var f in format) {
    		if(format[f] === record.get('ext').toLowerCase()){
    			validFormat = 1;
				break;	
    		}
    	}
    	if(!validFormat){
    		res.errStr += 'Неверное расширение файла ('+record.get('ext')+'). Допустимые расширения: '+format.toString()+'.';
    	}

    	
    	if(res.errStr !== '')
   			res.error = 1;
    	
    	return res;
    },
    createTooltip: function(view) {
        var me = this.up('form');
		view.tip = Ext.create('Ext.tip.ToolTip', {
            tpl: new Ext.XTemplate(
                    '<div class="eventTip">',
                    '{validateStr}',
                    '</div'                    
            ).compile(),
            dismissDelay: 15000,
            target: view.el,
            delegate: view.itemSelector,
            trackMouse: true,
            cls: 'gridfiles-row-tip',
            renderTo: Ext.getBody(),
            listeners: {
                beforeshow: function(tip) {
                    var record = view.getRecord(tip.triggerElement);
                    this.removeCls('notValid');
                    this.removeCls('valid');
                    
                    if (record && me.validateFile(record).error == 1) {
                    	this.addCls('notValid');
						record.data.validateStr = me.validateFile(record).errStr;
                        tip.update(record.data);
                    }
					else if(record){
						this.addCls('valid');
						record.data.validateStr = 'Файл готов к загрузке на сервер';
                        tip.update(record.data);
					}
                }
            }
		});
    }
});

Ext.define('Ext.ux.form.MultiFile', {
    extend: 'Ext.form.field.File',
    alias: 'widget.multifilefield',

    initComponent: function () {
        var me = this;

        me.on('render', function () {
            me.fileInputEl.set({ multiple: true });
        });

        me.callParent(arguments);
    },

    onFileChange: function (button, e, value) {
        this.duringFileSelect = true;		
		
        var me = this,
        upload = me.fileInputEl.dom,
        files = upload.files,
        names = [];

        if (files) {
            for (var i = 0; i < files.length; i++)
                names.push(files[i].name);
            value = names.join(', ');
        }
        Ext.form.field.File.superclass.setValue.call(this, value);

        delete this.duringFileSelect;
    }
});



//////////////////////////////////////////////////////
////////////example uploader//////////////////////////
//////////////////////////////////////////////////////
Ext.Loader.setPath({
    'Ext.ux' : './ext/src/ux'
});

Ext.define('Ext.ux.form.UploadGrid', {
	extend: 'Ext.ux.upload.Panel', 	
    alias: ['widget.fileUploadGrid', 'widget.fileuploadgrid', 'widget.uploadgrid'],
    uploader : 'Ext.ux.upload.uploader.FormDataUploader',
    uploaderOptions : {
        url : 'api.php?act=filesUpload'
    },
    config : {
        synchronous : true,
        uploadUrl : '',        

        // strings
        textOk : 'OK',
        textUpload : 'Загрузить все',
        textBrowse : 'Обзор',
        textAbort : 'Прервать',
        textRemoveSelected : 'Удалить выбранные',
        textRemoveAll : 'Удалить все',

        // grid strings
        textFilename : 'Имя файла',
        textSize : 'Размер',
        textType : 'Тип',
        textExt: 'Расширение',
        textStatus : 'Статус',
        textProgress : '%',

        // status toolbar strings
        selectionMessageText : 'Выбрано {0} файла(ов), {1}',
        uploadMessageText : 'Загружено {0}% ({1} of {2}',

        // browse button
        buttonText : 'Обзор'
    },
    fileFormat: '',
    style: 'border: 1px solid #157fcc !important',
    initComponent: function(){
		var me = this;
				  		
  		me.relayEvents(me, [
    		'uploadcomplete'
    	]);
		
		me.on({
			render: function(el){
				el.setPosition(-1000,0);
			}
		});
		
		me.callParent();
	},
	validateFile: function(record, size, ext){
    	var res = new Object();
    	//var me = this.up('form');
    	res.errStr = '';
    	res.error = 0;
    	var imgFormats = ['jpg', 'jpeg', 'png', 'gif', 'bmp'];
    	var fileFormats = ['doc', 'docx', 'xls', 'xlsx', 'pdf', 'txt', 'odt', 'rar', 'zip', 'tar'];
    	var allFormats = imgFormats.concat(fileFormats);
    	var format = [];
    	var validFormat = 0; //0 - valid, 1 - not valid (error)
    	
    	//validate size
		var size = (record ? record.get('size') : size)/1048576; //mb
    	if(parseInt(size) > this.fileSize){    		
    		
			var formatSize = parseFloat(Ext.util.Format.number(((size >= 1024) ? (size/1024) : size), '0.00'));
			var sizeStr = ' '+((parseInt(size) >= 1024)?'Gb':'Mb');
			
			res.errStr = 'Файл слишком большого размера ('+formatSize+sizeStr+'). Допустимый размер файла '+this.fileSize+' Mb <br />';
    	}
    	//validate format
    	switch(this.fileFormat){
    		case 'img':
    			format = imgFormats;
    			break;
   			case 'file':
   				format = fileFormats;
    			break;
   			case 'all':
   				format = allFormats;
    			break;
    	}
    	
    	for(var f in format) {
    		if(format[f] === (record ? record.get('ext') : ext).toLowerCase()){
    			validFormat = 1;
				break;	
    		}
    	}
    	if(!validFormat){
    		res.errStr += 'Неверное расширение файла ('+(record ? record.get('ext') : ext)+'). Допустимые расширения: '+format.toString()+'.';
    	}

    	
    	if(res.errStr !== '')
   			res.error = 1;
    	
    	return res;
    },
    createTooltip: function(view) {
        var me = this;
		view.tip = Ext.create('Ext.tip.ToolTip', {
            tpl: new Ext.XTemplate(
                    '<div class="eventTip">',
                    '{validateStr}',
                    '</div'                    
            ).compile(),
            dismissDelay: 15000,
            target: view.el,
            delegate: view.itemSelector,
            trackMouse: true,
            cls: 'gridfiles-row-tip',
            renderTo: Ext.getBody(),
            listeners: {
                beforeshow: function(tip) {
                    var record = view.getRecord(tip.triggerElement);
                    this.removeCls('notValid');
                    this.removeCls('valid');
                    
                    if (record && me.validateFile(record).error == 1) {
                    	this.addCls('notValid');
						record.data.validateStr = me.validateFile(record).errStr;
                        tip.update(record.data);
                    }
					else if(record){
						this.addCls('valid');
						record.data.validateStr = 'Файл готов к загрузке на сервер';
                        tip.update(record.data);
					}
                }
            }
		});
    }
});


Ext.define('Ext.ux.form.UploadPanel', {
	//extend: 'Ext.ux.upload.Panel',
 	extend: 'Ext.form.Panel',
    alias: ['widget.fileUpload', 'widget.fileupload'],
    border: false,
    //width: 400,
    //height:150,
    name: 'filename',
    fieldLabel: 'Выберите файл:',
    buttonText: 'Обзор...',
    labelWidth: 100,
    multiple: true,
    fileSize: 10,
    fileFormat: 'all', //'img', 'file', 'all'      
    
    synchronous: true,
    uploadUrl: '',
    uploadParams : {},
    uploadExtraHeaders : {},
    uploadTimeout : 60000,
    browseBtnIconCls: '',
    tooltip: null,
    
    initComponent: function(){
    	var me = this;
    	me.items = [{
	        xtype: 'browsebutton',
	        itemId : 'button_browse',
	        name: me.name,
	        iconCls: me.browseBtnIconCls,
	        multiple: me.multiple,
	        fieldLabel: me.fieldLabel,
	        labelWidth: me.labelWidth,
	        buttonText: me.buttonText,
	        buttonOnly: true,
	        allowBlank: true,
	        fileSize: me.fileSize, //Max size of file (Mb)
	        fileFormat: me.fileFormat,
	        tooltip: me.tooltip,
	        cls: 'browsebutton'
     	},{
     		xtype: 'uploadgrid',
		    margin: '0 0 5 0',
		    hidden: true,
		    flex: 1,
		    fileSize: me.fileSize, //Max size of file (Mb)
	        fileFormat: me.fileFormat,
	        multiple: me.multiple
     	}];
     	
     	me.on({
     		'afterrender': function(panel, eOpts){
   				var uploadgrid = panel.down('uploadgrid');
			 	panel.down('browsebutton').on('fileselected', uploadgrid.onFileSelection, uploadgrid);
     		}
     	});
	        
     	me.callParent();
    }
    
//	initComponent: function(){
//		var me = this;
		
//		var uploadPanel = Ext.create('Ext.ux.upload.Panel', {
//            uploader : 'Ext.ux.upload.uploader.FormDataUploader',
//            uploaderOptions : {
//                url : 'upload_multipart.php'
//            },
//            synchronous : true//appPanel.syncCheckbox.getValue()
//        });
//
//        var uploadDialog = Ext.create('Ext.ux.upload.Dialog', {
//            dialogTitle : 'My Upload Dialog',
//            panel : uploadPanel
//        });
//
//        this.mon(uploadDialog, 'uploadcomplete', function(uploadPanel, manager, items, errorCount) {
//            this.uploadComplete(items);
//            if (!errorCount) {
//                uploadDialog.close();
//            }
//        }, this);
//
//        uploadDialog.show();
//		
//		me.callParent();

//		me.addEvents({
//            'uploadcomplete' : true
//        });
//				
//		me.panel = Ext.create('Ext.ux.upload.Panel', {
//	        synchronous : me.synchronous,
//	        uploadUrl : me.uploadUrl,
//	        uploadParams : me.uploadParams,
//	        uploadExtraHeaders : me.uploadExtraHeaders,
//	        uploadTimeout : me.uploadTimeout
//  		});
//  		
//  		me.relayEvents(me.panel, [
//    		'uploadcomplete'
//    	]);
//		
//		
//		me.callParent();
//	}
});




Ext.define('Ext.ux.form.UploadWindow', {
	extend: 'Ext.ux.upload.Dialog',
    alias: 'widget.uploadwindow',
    
    initComponent: function(){
    	var me = this;
    	
    	var appPanel = Ext.create('Ext.window.Window', {
            title : 'Files',
            width : 600,
            height : 400,
            closable : true,
            modal : true,
            bodyPadding : 5,

            uploadComplete : function(items) {
                var output = 'Uploaded files: <br>';
                Ext.Array.each(items, function(item) {
                    output += item.getFilename() + ' (' + item.getType() + ', '
                        + Ext.util.Format.fileSize(item.getSize()) + ')' + '<br>';
                });

                this.update(output);
            }
        });

        appPanel.syncCheckbox = Ext.create('Ext.form.field.Checkbox', {
            inputValue : true,
            checked : true
        });

        appPanel.addDocked({
            xtype : 'toolbar',
            dock : 'top',
            items : [
                {
                    xtype : 'button',
                    text : 'Raw PUT/POST Upload',
                    scope : appPanel,
                    handler : function() {

                        var uploadPanel = Ext.create('Ext.ux.upload.Panel', {
                            uploaderOptions : {
                                url : 'upload.php'
                            },
                            filenameEncoder : 'Ext.ux.upload.header.Base64FilenameEncoder',
                            synchronous : appPanel.syncCheckbox.getValue()
                        });

                        var uploadDialog = Ext.create('Ext.ux.upload.Dialog', {
                            dialogTitle : 'My Upload Dialog',
                            panel : uploadPanel
                        });

                        this.mon(uploadDialog, 'uploadcomplete', function(uploadPanel, manager, items, errorCount) {
                            this.uploadComplete(items);
                            if (!errorCount) {
                                uploadDialog.close();
                            }
                        }, this);

                        uploadDialog.show();
                    }
                }, '-', {
                    xtype : 'button',
                    text : 'Multipart Upload',
                    scope : appPanel,
                    handler : function() {

                        var uploadPanel = Ext.create('Ext.ux.upload.Panel', {
                            uploader : 'Ext.ux.upload.uploader.FormDataUploader',
                            uploaderOptions : {
                                url : 'upload_multipart.php'
                            },
                            synchronous : appPanel.syncCheckbox.getValue()
                        });

                        var uploadDialog = Ext.create('Ext.ux.upload.Dialog', {
                            dialogTitle : 'My Upload Dialog',
                            panel : uploadPanel
                        });

                        this.mon(uploadDialog, 'uploadcomplete', function(uploadPanel, manager, items, errorCount) {
                            this.uploadComplete(items);
                            if (!errorCount) {
                                uploadDialog.close();
                            }
                        }, this);

                        uploadDialog.show();
                    }
                }, '-', {
                    xtype : 'button',
                    text : 'Dummy upload',
                    scope : appPanel,
                    handler : function() {

                        var uploadPanel = Ext.create('Ext.ux.upload.Panel', {
                            uploader : 'Ext.ux.upload.uploader.DummyUploader',
                            synchronous : appPanel.syncCheckbox.getValue()
                        });

                        var uploadDialog = Ext.create('Ext.ux.upload.Dialog', {
                            dialogTitle : 'My Upload Dialog',
                            panel : uploadPanel
                        });

                        this.mon(uploadDialog, 'uploadcomplete', function(uploadPanel, manager, items, errorCount) {
                            this.uploadComplete(items);
                            if (!errorCount) {
                                uploadDialog.close();
                            }
                        }, this);

                        uploadDialog.show();
                    }
                }, '->', appPanel.syncCheckbox, 'Synchronous upload'
            ]
        });
        
        appPanel.show();
		
		me.callParent(arguments);
    }
});
    
