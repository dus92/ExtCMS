Ext.define('duscms.controller.ControllerHeader', {
    extend: 'Ext.app.Controller',
        
    stores: [
        
	],
	
	models: [		
    	   	
	],
	
	views: [		
       'duscms.view.Header',
	   'duscms.view.Leftmenu',
       'duscms.view.MainContent'
    ],
    
    refs: [
    	{
            ref: 'Leftmenu',
            selector: 'Leftmenu'
        },{
            ref: 'mainContent',
            selector: 'mainContent'
        }
    ],
    
    init: function() {
		this.control({
            'Header': {
		          exitbuttonclick: this.onExitButtonClick,
		          tomainbuttonckick: this.gotoBeginAdministration
			}
        });        
	},
    
    /////////////////Exit button (logout) ////////////////////
    //выход из системы
    onExitButtonClick: function() {
        var myMask = new Ext.LoadMask(Ext.getBody(), {msg: 'Загрузка...'});
        Ext.Msg.show({
            title: 'Предупреждение',
            msg: 'Выйти из системы?',
                buttons: Ext.Msg.OKCANCEL,
                icon: Ext.Msg.WARNING,
                buttonText: { ok: 'Выйти', cancel: 'Отмена'},
                fn: function(state){                    
                    if (state == 'ok'){
                        myMask.show();      
                        Ext.Ajax.request({
                            url: 'api.php',
                            params: {				
				                act: 'logout'
                            },
                            success: function(response){                                
                                location.reload();
                                myMask.destroy();
                            }
                        });
                    }
                }
            });
    },
    //TODO применить эту ф-ю и для модуля и для кнопки "к началу адм-я"
    //к началу администрирования
    gotoBeginAdministration: function(){
    	var me = this;
		var leftmenu = me.getLeftmenu();
		var mainContent = me.getMainContent();
		leftmenu.setDisabled(true);
		leftmenu.getSelectionModel().deselectAll();
		mainContent.down('#mainPanel').removeAll();
		mainContent.down('#mainSendButton').hide();
                        
        if(mainContent.down('#validateMessage'))
            mainContent.remove('validateMessage');
		
		var myMask = new Ext.LoadMask(mainContent, {msg: 'Загрузка данных...'});        
        var store = createStore('ModelGeneralInfo', {
           action: 'getInfo' 
        });
        myMask.show();
                
        if(store){ //загружаем общую инфу о движке, сервере, версии php ...
            
            var info = '';
            var r_items = [];
            var info_items = [];
			store.load({
               callback: function(e){                    
					if(store.getCount() > 0){
                        store.each(function(rec){
                			if(!Array.isArray(rec.get('rights'))){ //все права
                				r_items = [{
                					xtype: 'label',
        							text: rec.get('rights'),
        							style: {
        								fontWeight: 'bold'
        							}
                				}];
                			}
                			else{ //есть права на отдельные модули
                				Ext.Array.each(rec.get('rights'), function(val, key){
                					r_items.push({
                						xtype: 'displayfield',
										fieldLabel: val.moduleName,
										value: val.moduleDesc		
                					});
                				});
                			}
							   
		   					mainContent.setTitle(rec.get('title'));
		   							   					
		   					//формирование элементов для отображения инфы
		   					info_items = [{
		   						xtype: 'displayfield',
								fieldLabel: 'httpd',
								value: rec.get('httpd')
		   					},{
		   						xtype: 'displayfield',
								fieldLabel: 'php',
								value: rec.get('php')	
		   					},{
		   						xtype: 'displayfield',
								fieldLabel: 'cms',
								value: rec.get('cms')	
		   					}];
		   					
		   					if(rec.get('text').moderation){ //статей ожидают модерации TODO: реализовать модерацию статей
		   						info_items.push({
		   							xtype: 'displayfield',
									fieldLabel: rec.get('text').moderation,
									value: rec.get('moderationCount')
		   						});
		   					}
		   					
		   					if(rec.get('text').feedback){ //запросов обр. связи к базе
		   						info_items.push({
		   							xtype: 'displayfield',
									fieldLabel: rec.get('text').feedback,
									value: rec.get('feedbackCount')									
		   						});
		   					}
							   
							mainContent.down('#mainPanel').add({
	                        	xtype: 'fieldset',
								collapsible: true,
								title: rec.get('text').rights,
						    	defaults: {
						    		flex: 1,
						    		labelWidth: 200,
						    		margin: 5
						    	},
								items: r_items
	                        },{ //информация о сервере, движке...
	                        	xtype: 'fieldset',
	                        	collapsible: true,
								title: rec.get('text').information,
						    	defaults: {
						    		flex: 1,
						    		labelWidth: 230,
						    		margin: 5,
						    		labelStyle: 'font-weight: bold !important'
						    	},
								items: info_items
							},{ //оставить сообщение другим администраторам
	                        	xtype: 'fieldset',
	                        	collapsible: true,
								title: rec.get('text').leaveMsg,
						    	defaults: {
						    		flex: 1,
						    		margin: 5
						    	},
								items: [{
									xtype: 'form',
									url: 'api.php?act=sendRemarks',
									border: false,
									id: 'frm_remarks',
									cls: 'frm_docked_background',
									layout: {
										type: 'fit'
									},
									buttonAlign: 'center',
									items: [{
										xtype: 'textarea',
										grow: true,
	        							name: 'remarks',
	        							maxlength: 4000,
	        							minHeight: 150,
	        							growMin: 150,
	        							growMax: 500,
	        							maxHeight: 500,
	        							value: rec.get('remarks'),
	        							allowBlank: false,
	        							blankText: 'Поле обязательно для заполнения',
	        							emptyText: 'Текст сообщения (максимальная длина сообщения - 4000 симв.)' //TODO: add in other languages (lang.php)
   									}],
   									buttons: [{
   										text: rec.get('text').send,
   										handler: function(btn){
   											mainContent.fireEvent('sendRemarksButtonClick', rec.get('remarks'));										
   										}
							   		}]
								}]
							});
	                        
                        });
                       	                       	
                    }
                    leftmenu.setDisabled(false);
                    myMask.destroy();
               }
            });
            
//            mainContent.add({
//           		xtype: 'panel',
//           		title: false,
//           		border: false,
//           		defaults: {
//           			layout: {
//			    		type: 'vbox',
//			    		align: 'stretch'
//			    	}
//           		}
//            });
        }
		
    }
});
				
				
				
