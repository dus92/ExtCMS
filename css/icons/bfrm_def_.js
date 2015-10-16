
Ext.require([
'Ext.form.*'
]);

Ext.define('Form_bfrm_photo', 
    {
        extend:  'Ext.form.Panel',
        alias: 'widget.f_bfrm_photo',
        initComponent:function () 
            {
                this.addEvents('create');
                Ext.apply(this, 
                    {}
                ); //Ext.apply
                this.callParent();
            }, //initComponent 
       
        onReset: function()
            {
                 this.ownerCt.close();
            }
    }
); // 'Ext.Define


function DefineInterface_bfrm_def_(id,mystore){

var p1 ; 
     function onSave(){
		if(CheckOperation('BFRM.edit')!=0){
			var active = p1.activeRecord,
			form = p1.getForm();
			if (!active) {
				return;
			}
			if (form.isValid()) {
				form.updateRecord(active);
				// combobox patch
				var form_values = form.getValues(); var field_name = '';  for(field_name in form_values){active.set(field_name, form_values[field_name]);}
				Ext.Ajax.request({
					url: 'index.php/c_bfrm_def/setRow',
					method:  'POST',
					params: { 
						instanceid: p1.instanceid
						,bfrm_defid: active.get('bfrm_defid')
						,isphizical: active.get('isphizical') 
						,name: active.get('name') 
						,nameshort: active.get('nameshort') 
						,name2: active.get('name2') 
						,name3: active.get('name3') 
						,name4: active.get('name4') 
						,theadrfuct: active.get('theadrfuct') 
						,thecommentadrfuct: active.get('thecommentadrfuct') 
						,theadrfuct2: active.get('theadrfuct2') 
						,theadrfuct3: active.get('theadrfuct3') 
						,thephone: active.get('thephone') 
						,thephone2: active.get('thephone2') 
						,thephone3: active.get('thephone3') 
						,thephone4: active.get('thephone4') 
						,fax: active.get('fax') 
						,lastname: active.get('lastname') 
						,firstname: active.get('firstname') 
						,patronymic: active.get('patronymic') 
						,thecomment: active.get('thecomment') 
						,operationmode: active.get('operationmode') 
						,logo_file: active.get('logo_file') 
					}
					, success: function(response){
					var text = response.responseText;
					var res =Ext.decode(text);
					if(res.success==false){
						Ext.MessageBox.show({
							title:  'Ошибка',
							msg:    res.msg,
							buttons: Ext.MessageBox.OK,
							icon:   Ext.MessageBox.ERROR
							});
					}else{
						if(active.get('bfrm_defid')==''){
							active.set('bfrm_defid',res.data['bfrm_defid']);
						}
						Ext.MessageBox.show({
							title:  'Подтверждение',
							msg:    'Изменения сохранены',
							buttons: Ext.MessageBox.OK,
							icon:   Ext.MessageBox.INFO
						});
					}
				  }
				});
			}else{
					Ext.MessageBox.show({
					title:  'Ошибка',
					msg:    'Не все обязательные поля заполнены!',
					buttons: Ext.MessageBox.OK,
					icon:   Ext.MessageBox.ERROR
					});
			}
		
		
		
				
			
		}else{
				Ext.MessageBox.show({
				title:  'Контроль прав.',
				msg:    'Изменение данных не разрешено!',
				buttons: Ext.MessageBox.OK,
			   icon:   Ext.MessageBox.WARNING
				});
		}
    };
    function onReset(){
        p1.setActiveRecord(null,null);
    }
p1=new Ext.form.Panel(
    {
        itemId: id,
        autoScroll:true,
        border:0,
        activeRecord: null,
        defaultType:  'textfield',
        id:'bfrm_def',
        itemId: 'bfrm_def',
        fieldDefaults: {
         labelAlign:  'right',
         labelWidth: 110
        },
        items: [
            {
                xtype:'fieldset',
                layout: 'hbox',
                width: 769,
                items: [
                    {
                        xtype: 'fieldset',
                        width: 250,
                        border: 0,
                        layout: {
                            type: 'vbox',
                            align: 'center'
                        },
                        items: [
                            {
                                xtype: 'textfield',
                                name: 'logo_file',
                                hidden: true,
                                listeners: {
                                    change: function ( obj, newValue, oldValue, eOpts )
                                    {
                                        this.up('#bfrm_def').down('#logo_file_img').setSrc(newValue);
                                    }
                                }
                            },
                            {
                                width: 220,
                                height: 220,
                                xtype: 'image',
                                border: '1',
                                itemId: 'logo_file_img',
                                flex: 1
                            },
                            {
                                xtype: 'button',
                                text: 'Изменить логотип',
                                handler: function()
                                {
                                    if(CheckOperation('BFRM.edit')!=0){
					
					
									var logo_file = this.up('#bfrm_def').getForm().findField('logo_file');
									var edit;
                                    edit = Ext.create('Ext.window.Window',                                        
                                        {
                                            height: 90,
                                            width: 600,
                                            layout:  'absolute',
                                            autoShow: true,
                                            modal: true,
                                            closeAction: 'destroy',
                                            iconCls:  'icon-application_form',
                                            title:  'Изменить логотип',
                                            items:[
                                                {
                                                    xtype: 'form',
                                                    itemId: 'img_upload_form_bfrm',
													layout:  'absolute',
                                                    url:'index.php/app2/setSPPhoto',
                                                    items:[
                                                        {
                                                            xtype:'filefield',
                                                            name:'photo',
                                                            fieldLabel:'Фото',
                                                            allowBlank:false,
                                                            buttonText: 'Выберите фото...',
                                                            width:550,
															x:5,
															y:5
                                                        }
                                                    ], 
                                                    
                                                    dockedItems: 
                                                    [
                                                        {
                                                            xtype:  'toolbar',
                                                            dock:   'bottom',
                                                            ui:     'footer',
                                                            items: ['->', 
                                                                {
                                                                    iconCls:  'icon-accept',
                                                                    itemId:  'save',
                                                                    text:   'OK',
                                                                    disabled: false,
                                                                //    scope:  this,
                                                                    handler:function()
                                                                    {
																		
																			var form = this.up('#img_upload_form_bfrm').getForm();
																			if(form.isValid()){
																				form.submit(
																				{
																			
																					waitMsg: 'Загрузка фото...',
																					success: function(f,response){
																						var text = response.result.result;
																						if(text == "OK"){
																							logo_file.setValue(response.result.fn);
																							//console.log(response.result);
																							var wn = this.form.owner.ownerCt;
																							wn.close();
																						}else{
																							Ext.MessageBox.show({
																								title:  'Ошибка',
																								msg:    text,
																								buttons: Ext.MessageBox.OK,
																								icon:   Ext.MessageBox.ERROR
																							});
																						}
																					}
																					,
																					failure: function(f,response) {
																						var text = response.result.msg;
																						Ext.MessageBox.show({
																						title:  'Ошибка',
																						msg:    text,
																						buttons: Ext.MessageBox.OK,
																						icon:   Ext.MessageBox.ERROR
																						});
																					}
																				}
																				);
																			}
																			
																	
                                                                        
                                                                    }
                                                                }, 
                                                                {
                                                                    iconCls:  'icon-cancel',
                                                                    text:   'Закрыть',
                                                                    scope:  this,
                                                                    handler : function(){
																		edit.close();
																	}
                                                                }
                                                            ]
                                                        }
                                                    ]                                                
                                                }
                                            ]
                                        }
                                    );  

                                    edit.show(); 
					
										
									}else{
											Ext.MessageBox.show({
											title:  'Контроль прав.',
											msg:    'Изменение данных не разрешено!',
											buttons: Ext.MessageBox.OK,
										   icon:   Ext.MessageBox.WARNING
											});
									}
									
									
									                                 
                                }
                            }
                        ]
                    },
                    { 
                        xtype:'fieldset', 
                        id:'bfrm_def-0',
                        border:1, 
                        flex: 1,
                        layout:{
                            type: 'vbox', 
                            align: 'stretch'
                        },
                        items: [
                            {
                                xtype:  'hidden',
                                name:   'isphizical',
                                fieldLabel:  'Физическое лицо'
                            }
                            ,
                            {
                                xtype:  'textfield',
                                value:  '',
                                name:   'name',
                                fieldLabel:  'Название',
                                allowBlank:true
                            }
                            ,
                            {
                                xtype:  'textfield',
                                value:  '',
                                name:   'nameshort',
                                fieldLabel:  'Краткое название',
                                allowBlank:true
                            }
                            ,
                            {
                                xtype:  'hidden',
                                name:   'name2',
                                fieldLabel:  'Иначе называется'
                            }
                            ,
                            {
                                xtype:  'hidden',
                                name:   'name3',
                                fieldLabel:  'Название 3'
                            }
                            ,
                            {
                                xtype:  'hidden',
                                name:   'name4',
                                fieldLabel:  'Название 4'
                            }
                            ,
                            {
                                xtype: 'textarea', 
                                height: 80, 
                                value:  '',
                                name:   'theadrfuct',
                                fieldLabel:  'Адрес',
                                allowBlank:true
                            }
                            ,
                            {
                                xtype:  'hidden',
                                name:   'thecommentadrfuct',
                                fieldLabel:  'Примечание к адресу'
                            }
                            ,
                            {
                                xtype:  'hidden',
                                name:   'theadrfuct2',
                                fieldLabel:  'Факт. Адрес 2'
                            }
                            ,
                            {
                                xtype:  'hidden',
                                name:   'theadrfuct3',
                                fieldLabel:  'Факт. Адрес 3'
                            }
                            ,
                            {
                                xtype: 'fieldcontainer',
                                layout: 'hbox',
                                defaultType: 'textfield',
                                fieldDefaults:{
                                    labelAlign: 'right'
                                },
                                defaults:
                                {
                                    flex: 1
                                }
                                ,
                                items: [
                                    {
                                        plugins: [new Ext.ux.InputTextMask('9(999)999-99-99')], 
                                        xtype:  'textfield',
                                        value:  '',
                                        name:   'thephone',
                                        fieldLabel:  'Телефон',
                                        allowBlank:true
                                    }
                                /*    ,
                                    {
                                        xtype:  'hidden',
                                        name:   'thephone2',
                                        fieldLabel:  'Телефон 2'
                                    }
                                    ,
                                    {
                                        xtype:  'hidden',
                                        name:   'thephone3',
                                        fieldLabel:  'Телефон 3'
                                    }
                                    ,
                                    {
                                        xtype:  'hidden',
                                        name:   'thephone4',
                                        fieldLabel:  'Телефон 4'
                                    } */
                                    ,
                                    {
                                        plugins: [new Ext.ux.InputTextMask('9(999)999-99-99')], 

                                        xtype:  'textfield',
                                        value:  '',
                                        name:   'fax',
                                        fieldLabel:  'Факс',
                                        allowBlank:true
                                    }
                              /*      ,
                                    {
                                        xtype:  'hidden',
                                        name:   'operationmode',
                                        fieldLabel:  'Режим работы'
                                    } */
                                ]
                            }
                        ]
                    }
                ]
            }
            ,
            { 
                xtype:'panel', 
                closable:false,
                collapsible:true,
                titleCollapse : true,
                x: 0, 
                layout:'absolute', 
                id:'bfrm_def-1',
                title:      'Ответсвенное лицо',
                defaultType:  'textfield',
                items: [
                    {
                        minWidth: 249,
                        width: 249,
                        maxWidth: 249,
                        x: 5, 
                        y: 5, 

                        xtype:  'textfield',
                        value:  '',
                        name:   'lastname',
                        fieldLabel:  'Фамилия',
                        allowBlank:true
                    }
                    ,
                    {
                        minWidth: 249,
                        width: 249,
                        maxWidth: 249,
                        x: 258, 
                        y: 5, 

                        xtype:  'textfield',
                        value:  '',
                        name:   'firstname',
                        fieldLabel:  'Имя',
                        allowBlank:true
                    }
                    ,
                    {
                        minWidth: 249,
                        width: 249,
                        maxWidth: 249,
                        x: 511, 
                        y: 5, 

                        xtype:  'textfield',
                        value:  '',
                        name:   'patronymic',
                        fieldLabel:  'Отчество',
                        allowBlank:true
                    }
                ], 
                width: 769,
                height: 65 
            } // group
            ,
            { 
                xtype:'panel', 
                closable:false,
                collapsible:true,
                titleCollapse : true,
                x: 0, 
                layout:'absolute', 
                id:'bfrm_def-2',
                title:      'Примечание',
                defaultType:  'textfield',
                items: [
                    {
                        minWidth: 739,
                        xtype: 'textarea', 
                        x: 5, 
                        y: 5, 
                        height: 80, 

                        xtype:  'textarea',
                        value:  '',
                        name:   'thecomment',
                        fieldLabel:  'Примечание',
                        allowBlank:true
                    }
                ], 
                width: 769,
                height: 120 
            } // group
        ], //items = part panel

        instanceid:'',
        dockedItems: [
            {
                xtype:  'toolbar',
                dock:   'bottom',
                ui:     'footer',
                items: [
                     
                    {
                        iconCls:  'icon-accept',
                        itemId:  'save',
                        text:   'Сохранить',
                        disabled:true,
                        scope:  this,
                        handler : onSave
                    }
                ]
            }
        ] // dockedItems
        ,
        setActiveRecord: function(record,instid){
            p1.activeRecord = record;
            p1.instanceid = instid;
            if (record) {
                p1.down('#save').enable();
                p1.getForm().loadRecord(record);
            } else {
                p1.down('#save').disable();
                p1.getForm().reset();
            }
        }
    }); // 'Ext.Define

return p1;
};
function DefineForms_bfrm_def_(){


Ext.define('Form_bfrm_def', {
extend:  'Ext.form.Panel',
alias: 'widget.f_bfrm_def',
initComponent: function(){
    this.addEvents('create');
    Ext.apply(this,{
        activeRecord: null,
        defaultType:  'textfield',
        id:'bfrm_def',
        x: 0, 
        fieldDefaults: {
         labelAlign:  'top' //,
        },
        items: [
        { 
        xtype:'fieldset', 
        id:'bfrm_def-0',
        layout:'absolute', 
        border:false, 
        items: [
{
xtype:  'hidden',
name:   'isphizical',
fieldLabel:  'Физическое лицо'
}
,
{
         minWidth:200,
        x: 5, 
        y: 0, 

xtype:  'textfield',
value:  '',
name:   'name',
fieldLabel:  'Название',
allowBlank:true
}
,
{
         minWidth:200,
        x: 240, 
        y: 0, 

xtype:  'textfield',
value:  '',
name:   'nameshort',
fieldLabel:  'Краткое название',
allowBlank:true
}
,
{
xtype:  'hidden',
name:   'name2',
fieldLabel:  'Иначе называется'
}
,
{
xtype:  'hidden',
name:   'name3',
fieldLabel:  'Название 3'
}
,
{
xtype:  'hidden',
name:   'name4',
fieldLabel:  'Название 4'
}
,
{
         minWidth:200,
        x: 475, 
        y: 0, 

value:  '',
name:   'theadrfuct',
fieldLabel:  'Адрес',
allowBlank:true
}
,
{
xtype:  'hidden',
name:   'thecommentadrfuct',
fieldLabel:  'Примечание к адресу'
}
,
{
xtype:  'hidden',
name:   'theadrfuct2',
fieldLabel:  'Факт. Адрес 2'
}
,
{
xtype:  'hidden',
name:   'theadrfuct3',
fieldLabel:  'Факт. Адрес 3'
}
,
{
         minWidth:200,
        x: 5, 
        y: 46, 

xtype:  'textfield',
value:  '',
name:   'thephone',
fieldLabel:  'Телефон',
allowBlank:true
}
,
{
xtype:  'hidden',
name:   'thephone2',
fieldLabel:  'Телефон 2'
}
,
{
xtype:  'hidden',
name:   'thephone3',
fieldLabel:  'Телефон 3'
}
,
{
xtype:  'hidden',
name:   'thephone4',
fieldLabel:  'Телефон 4'
}
,
{
         minWidth:200,
        x: 240, 
        y: 46, 

xtype:  'textfield',
value:  '',
name:   'fax',
fieldLabel:  'Факс',
allowBlank:true
}
,
{
xtype:  'hidden',
name:   'operationmode',
fieldLabel:  'Режим работы'
}
       ], width: 705,
       height: 132 
        }
,
        { 
        xtype:'panel', 
        closable:false,
        collapsible:true,
        titleCollapse : true,
        layout:'absolute', 
        x: 0, 
        id:'bfrm_def-1',
title:      'Ответсвенное лицо',
defaultType:  'textfield',
            items: [
{
         minWidth:200,
        x: 5, 
        y: 0, 

xtype:  'textfield',
value:  '',
name:   'lastname',
fieldLabel:  'Фамилия',
allowBlank:true
}
,
{
         minWidth:200,
        x: 240, 
        y: 0, 

xtype:  'textfield',
value:  '',
name:   'firstname',
fieldLabel:  'Имя',
allowBlank:true
}
,
{
         minWidth:200,
        x: 475, 
        y: 0, 

xtype:  'textfield',
value:  '',
name:   'patronymic',
fieldLabel:  'Отчество',
allowBlank:true
}
       ], width: 705,
       height: 86 
        } //group
,
        { 
        xtype:'panel', 
        closable:false,
        collapsible:true,
        titleCollapse : true,
        layout:'absolute', 
        x: 0, 
        id:'bfrm_def-2',
title:      'Примечание',
defaultType:  'textfield',
            items: [
{
         minWidth:200,
        x: 5, 
        y: 0, 

xtype:  'textarea',
value:  '',
name:   'thecomment',
fieldLabel:  'Примечание',
allowBlank:true
}
       ], width: 705,
       height: 86 
        } //group
          ],//items = part panel
        instanceid:'',
        dockedItems: [{
            xtype:  'toolbar',
            dock:   'bottom',
            ui:     'footer',
                items: ['->', {
                    iconCls:  'icon-accept',
                    itemId:  'save',
                    text:   'Сохранить',
                    disabled: true,
                    scope:  this,
                    handler : this.onSave
                }
              ]
            }] // dockedItems
        }); //Ext.apply
        this.callParent();
    }, //initComponent 
    setActiveRecord: function(record,instid){
        this.activeRecord = record;
        this.instanceid = instid;
        if (record) {
            this.down('#save').enable();
            this.getForm().loadRecord(record);
        } else {
            this.down('#save').disable();
            this.getForm().reset();
        }
    },
    onSave: function(){
        var active = this.activeRecord,
            form = this.getForm();
        if (!active) {
            return;
        }
        if (form.isValid()) {
            form.updateRecord(active);
            // combobox patch
            var form_values = form.getValues(); var field_name = '';  for(field_name in form_values){active.set(field_name, form_values[field_name]);}
            Ext.Ajax.request({
                url: 'index.php/c_bfrm_def/setRow',
                method:  'POST',
                params: { 
                    instanceid: this.instanceid
                    ,bfrm_defid: active.get('bfrm_defid')
                    ,isphizical: active.get('isphizical') 
                    ,name: active.get('name') 
                    ,nameshort: active.get('nameshort') 
                    ,name2: active.get('name2') 
                    ,name3: active.get('name3') 
                    ,name4: active.get('name4') 
                    ,theadrfuct: active.get('theadrfuct') 
                    ,thecommentadrfuct: active.get('thecommentadrfuct') 
                    ,theadrfuct2: active.get('theadrfuct2') 
                    ,theadrfuct3: active.get('theadrfuct3') 
                    ,thephone: active.get('thephone') 
                    ,thephone2: active.get('thephone2') 
                    ,thephone3: active.get('thephone3') 
                    ,thephone4: active.get('thephone4') 
                    ,fax: active.get('fax') 
                    ,lastname: active.get('lastname') 
                    ,firstname: active.get('firstname') 
                    ,patronymic: active.get('patronymic') 
                    ,thecomment: active.get('thecomment') 
                    ,operationmode: active.get('operationmode') 
                    ,logo_file: active.get('logo_file') 
                }
                , success: function(response){
                var text = response.responseText;
                var res =Ext.decode(text);
	            if(res.success==false){
	       	        Ext.MessageBox.show({
	       		        title:  'Ошибка',
	       		        msg:    res.msg,
	       		        buttons: Ext.MessageBox.OK,
	       		        icon:   Ext.MessageBox.ERROR
	       	            });
	            }else{
                    if(active.get('bfrm_defid')==''){
               			active.set('bfrm_defid',res.data['bfrm_defid']);
                    }
        		    Ext.MessageBox.show({
                        title:  'Подтверждение',
                        msg:    'Изменения сохранены',
                        buttons: Ext.MessageBox.OK,
                        icon:   Ext.MessageBox.INFO
        		    });
                }
              }
            });
        }else{
        		Ext.MessageBox.show({
                title:  'Ошибка',
                msg:    'Не все обязательные поля заполнены!',
                buttons: Ext.MessageBox.OK,
                icon:   Ext.MessageBox.ERROR
        		});
        }
    },
    onReset: function(){
        if(this.activeRecord.get('bfrm_defid')==''){
                this.activeRecord.store.reload();
        }
        this.setActiveRecord(null,null);
        this.ownerCt.close();
    }
}); // 'Ext.Define

Ext.define('EditWindow_bfrm_def', {
    extend:  'Ext.window.Window',
    maxHeight: 419,
    maxWidth: 805,
    minHeight:374,
    minWidth: 745,
    layout:  'absolute',
    autoShow: true,
    modal: true,
    closeAction: 'destroy',
    iconCls:  'icon-application_form',
    title:  'Описание',
    items:[{
        xtype:  'f_bfrm_def'
	}]
	});
}