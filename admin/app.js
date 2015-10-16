/*
    This file is generated and updated by Sencha Cmd. You can edit this file as
    needed for your application, but these edits will have to be merged by
    Sencha Cmd when upgrading.
*/

// DO NOT DELETE - this directive is required for Sencha Cmd packages to work.
//@require @packageOverrides

Ext.application({
    name: 'duscms',
    
    controllers: [
        'Main',
        'ControllerNews',
        'ControllerHeader',
        'ControllerLeftmenu',
        'ControllerGeneral'
    ],
    
    appFolder: './admin/app',

    //extend: '/admin/app/application',
    
    //autoCreateViewport: true,
    
    launch: function() {        
        var me = this;
        if(!LOGGED_IN){
        	var myMask = new Ext.LoadMask(Ext.getBody(), {msg: 'Обработка данных...'});
			myMask.destroy();
			this.loginUser();
        }
        else{
        	me.showViewport();
        	
			//me.showChat();
         }
        ///////////////////////////////////////////////////        
    },
    //форма входа в систему
    loginUser: function(){
        var me = this;
        var required = '<span style="color:red;font-weight:bold" data-qtip="Необходимое поле">*</span>';
        var win = Ext.create('Ext.Window', {
            id: 'login-form',
			title: 'Авторизация пользователя',		
			width: 350,
			draggable: true,
			resizable: false,
			modal: true,
            closable:  false,
            defaultFocus: 'email',
            items: [{
				xtype: 'form',             
				border: false,
				defaults: {
					labelWidth: 105,
                    anchor: '100%'
				},
                url: 'api.php?act=login',
                timeout: 1800,
				defaultType: 'textfield',                
				items: [{	                    
                    xtype: 'textfield',
                    margin: '5',
                    //width: 330,
                    fieldLabel: 'Логин',
                    name: 'username',
                    itemId: 'email',
                    blankText: 'Необходимое поле',                    
                    afterLabelTextTpl: required,
                    allowBlank: false,
                    //value: LOGGED_IN,
                    enableKeyEvents: true,
                    listeners: { //обработка клавиши ENTER
                        keydown: function(txtField, e){
                            var key = e.getKey();
                            if(key == Ext.EventObject.ENTER){
                                me.submitLoginForm(this, win, me);
                            }
                        }
                    }
				},{
				    xtype: 'textfield',
                    margin: '5',
                    //width: 330,
                    fieldLabel: 'Пароль',                                                                                           
                    name: 'password',
                    inputType: 'password',
                    blankText: 'Необходимое поле',
                    afterLabelTextTpl: required,
                    allowBlank: false,
                    enableKeyEvents: true,
                    listeners: { //обработка клавиши ENTER
                        keydown: function(txtField, e){
                            var key = e.getKey();
                            if(key == Ext.EventObject.ENTER){
                                me.submitLoginForm(this, win, me);   
                            } 
                        }
                    }
				},{
				    xtype: 'checkbox',
                    name: 'remember',
                    inputValue: 1,
                    fieldLabel: 'Запомнить меня',
                    labelAlign: 'right',
                    labelWidth: 107
				}],
				buttons: [{				    
					margin: '0 125 0 0',
                    //buttonAlign: 'center',
                    text: 'Войти',
					handler: function() {                       
                        me.submitLoginForm(this, win, me);   
                    }
				}]
           }]
        });
        win.show();    
    },
    
    //отправка формы входа, обработка данных
    submitLoginForm: function(th, win, me){
        var me = this;
        var form = th.up('form').getForm();
        var vals = new Object();
        var err = 0;
        var myMask = new Ext.LoadMask(win, {msg: 'Обработка данных...'});                 
        if (form.isValid() && !err){					                                                                                                
            myMask.show();
            form.submit({
                //params: vals,
                success: function(form, action) {                                                                        
                    //TODO обработать права
                    me.showViewport();
                    
                    //me.showChat();
                    
                    myMask.destroy();        
                    win.destroy();
                },
                failure: function(form, action) {                                                                                                                                                                     
                    myMask.destroy();
                    Ext.Msg.show({
                        title:'Ошибка',
                        msg: action.result ? action.result.msg : 'Нет ответа от сервера',
                        buttons: Ext.Msg.OK,
                        icon: Ext.Msg.ERROR,                                            
                    });                        
                    form.setValues({password: ''});                                                                                                                                                                                                                      									                                                                                
                }
            });                            
        }    
    },
    
    showViewport: function(){
        var me = this;
        Ext.Ajax.request({
            url: 'api.php?act=getModules',
            method: 'POST',                    
            success: function(response){
                var res = Ext.JSON.decode(response.responseText);
                if(res.success && res.data.length > 0){                            
                    me.rights = res.data; //массив прав
                    if(me.rights.length > 0)
                        Ext.create("duscms.view.Viewport");
                }
                else{
                	var myMask = new Ext.LoadMask(Ext.getBody(), {msg: 'Обработка данных...'});
					myMask.destroy();
					Ext.Msg.show({
                        title:'Ошибка',
                        msg: 'У вас нет прав на управление модулями',
                        buttons: Ext.Msg.OK,
                        icon: Ext.Msg.ERROR,
                        buttonText: { ok: 'Сменить пользователя'},
                        fn: function(btn) {
                        	if(btn == 'ok'){
                        		//window.location.reload();
                        		me.loginUser();
                        	}
                        }
                    });
                }
            }
        });
    },
    
    showChat: function(){
    	Ext.create('Ext.Button', {
		    text: '',
		    cls: 'btn-chat',
		    border: false,
		    tooltip: 'Открыть онлайн-чат',
		    renderTo: Ext.getBody(),
		    handler: function() {
		        alert('You clicked the button!');
		    },
		    listeners: {
		    	render: function(el){
		    		el.setPosition(Ext.getBody().getViewSize().width-55, Ext.getBody().getViewSize().height-40);
		    	}
		    }
		});
		
		Ext.create('Ext.window.Window', {
			width: 200,
			height: 400,
			title: 'Онлайн-чат',
			iconCls: 'btn-chat',
			closable: false,
			resizable: false,
			draggable: false,			
			collapsed: true,
			collapsible: true,
			expandOnShow: false,
			listeners: {
		    	render: function(el){
		    		el.setPosition(Ext.getBody().getViewSize().width-200, Ext.getBody().getViewSize().height-36);
		    	}
		    }
		}).show();
    }
});
