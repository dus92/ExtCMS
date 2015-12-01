/////////////////////////////////////////////////////////////////////////
/////////////Новые компоненты (переопределенные/дополненные старые)...///
/////////////////////////////////////////////////////////////////////////
Ext.define('ExtCMS.window', {
	extend: 'Ext.window.Window',
	closeAction:'destroy',
	modal : true,
	resizable : false,
	labelAlign: 'right',	
	buttonOkCaption: 'Сохранить',
	buttonOkDisabled: false,
	buttonOkHidden: false,	
	buttonOkIconCls: 'icon-accept',		
	buttonCancelCaption: 'Отмена',
	buttonCancelDisabled: false,
	buttonCancelHidden: false,
	buttonCancelIconCls: 'icon-cancel',
	constrainHeader:true,
	formVisible: false,
	formDefaults: null,
	fileUpload: false,
	formAutoHeight: true,
	formBodyPadding: 5,
	initComponent:function() 
	{
		var me = this;
		
		if(me.formVisible) 
		{
			Ext.apply(me, 
			{		
				items: me.formGet()
			});
		}
		
		Ext.apply(me, 
		{		
			buttons: me.buttonsGet()
		});
		
		me.callParent();
	},
    formGet: function(){
		var me = this;
		
		var form ={
			xtype:'form',
			itemId:'form',
			autoHeight: me.formAutoHeight,
			border: false,
			bodyPadding: me.formBodyPadding,
			defaultType:'textfield',
			defaults:me.formDefaults,
			fileUpload: me.fileUpload,
			items: me.fieldsGet()
		}
				
		return form;
	},
	buttonsGet : function () {
		var me = this;
		
		var buttons =[{
            id: me.id+'_buttonOk',
            iconCls:  me.buttonOkIconCls,
            text: me.buttonOkCaption,
            disabled: me.buttonOkDisabled,
            hidden: me.buttonOkHidden,
            handler: Ext.bind(me.onButtonOk, me)
		},{
			id: me.id+'_buttonCancel',
			iconCls:  me.buttonCancelIconCls,
			text: me.buttonCancelCaption,
			disabled: me.buttonCancelDisabled,
			hidden: me.buttonCancelHidden,
			handler: Ext.bind(me.onButtonCancel, me)
		}];

		return buttons;
	}
	,fieldsGet: function(){return [];}
	,formSave: function(){
		var me = this;
		var form =  me.down('#form').getForm();
		
		me.formBeforeSave(form);
		
		if(form.isValid())
		{
			form.submit(
			{
				url: me.url+'/save',
				waitMsg: 'Сохранение...',
				success: function(f, response){	
					var result = response.result;
					
					if(result.success==false)
					{
						Ext.Msg.error('Ошибка', 'Ошибка');
					}
					else
					{
						me.formAfterSave(result.data);
						me.close();	
					};
				},
				failure: function(f,response) {
					Ext.Msg.error('Ошибка', response.result.msg);
				}
			});
		}
	},
	formBeforeSave: function(form){},	
	formAfterSave: function(result){},	
	onButtonOk: function(){this.formSave()},
	onButtonCancel: function(){this.close();}
});
