Ext.define('duscms.view.MainContent', {
    extend: 'Ext.panel.Panel',
    xtype: 'mainContent',
    itemId: 'mainContent',
    id: 'view-main',
    title: 'Идет загрузка модуля...',
    titleAlign: 'center',
    layout: {
        type: 'vbox',
        align: 'stretch'
    },
    overflow: 'auto',
    autoScroll: true,
    initComponent: function(){
        var me = this;
        var myMask = new Ext.LoadMask(Ext.getBody(), {msg: 'Загрузка данных...'});        
        var store = createStore({
           model: 'ModelGeneralInfo',
           proc: 'getInfo'
        });
                
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
							   
		   					me.setTitle(rec.get('title'));
		   							   					
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
							   
							me.down('#mainPanel').add({
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
									url: 'api.php/sendRemarks/save',
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
   											me.fireEvent('sendRemarksButtonClick', rec.get('remarks'));
   										}
							   		}]
								}]
							});
                        });
                    }
                    myMask.destroy();
               }
            });
            
            me.items = [
            //{
//                xtype: 'panel',
//                itemId: 'mainMessagePanel',
//                border: false,
//                left: -1000,
//                layout: {
//		    		type: 'vbox',
//		    		align: 'stretch'
//		    	}
//            },
            {
           		xtype: 'panel',
                itemId: 'mainPanel',
                flex: 1,
                margin: 5,
                overflow: 'auto',
                autoScroll: true,
           		title: false,
           		border: false,
           		defaults: {
           			layout: {
			    		type: 'vbox',
			    		align: 'stretch'
			    	}
           		}
            },{
                xtype: 'panel',
                itemId: 'mainSendButton',
                hidden: true,
                buttonAlign: 'center',
                eventName: null,
                layout: {
                    type: 'hbox',
                    pack: 'center',
                    align: 'middle'
                },
                buttons: [{
                    text: 'Отправить',
                    onButtonClick: function(event){
                        var me = this;
                        me.eventName = event;
                    },
                    handler: function(el){
                        me.fireEvent(el.eventName);
                    }
                }]
            }];
        }
        me.callParent(arguments);
    }
});
