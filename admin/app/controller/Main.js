Ext.define('duscms.controller.Main', {
    extend: 'Ext.app.Controller',
        
    stores: [
        //'StoreModulesLocal'
	],
	
	models: [
		'ModelGeneralInfo'
	],
	
	views: [
	  'duscms.view.Header',
	  //'duscms.view.Leftmenu',
      'duscms.view.Viewport',
      'duscms.view.MainContent'
    ],
    
    refs: [
        {
            ref: 'viewport',
            selector: 'viewport'
        },{
            ref: 'mainContent',
            selector: 'mainContent'
        }
    ],
    
    init: function() {
		var myMask = new Ext.LoadMask(Ext.getBody(), {msg: 'Загрузка данных...'});
        myMask.show();
        
        this.control({
            'viewport': {
                beforerender: function(){
                    //myMask.destroy();
                }
            },
            'mainContent': {
                afterrender: this.loadContent,
                sendRemarksButtonClick: this.sendRemarks
            }
        });
      // Ext.getStore('StoreTeachers').addListener('load',this.onTeachersStoreAfterLoad,this);
	},
    
    loadContent: function(){
        var me = this;
        var panel = me.getMainContent();
                                
    },
    //отправка сообщения другим администраторам
    sendRemarks: function(defaultValue){
    	var me = this;
		var form = this.getMainContent().down('form').getForm();
    	var myMask = new Ext.LoadMask(this.getMainContent().down('form'), {msg: 'Обработка данных...'});
    	var message = this.getMainContent().down('form').down('[name=remarks]').getValue();
		
		if(form.isValid() && form.isDirty()){
			myMask.show();
    		form.submit({
    			success: function(form, action) {			       	
			       	validateFormMsg(me.getMainContent().down('panel'), action.result.msg, 1);
					myMask.destroy();
			    },
			    failure: function(form, action) {
			    	Ext.Msg.show({
					    title: 'Ошибка',
					    msg: action.result.msg,
					    buttons: Ext.Msg.OK,					    					    
					    icon: ERROR
					});
					myMask.destroy();
			    }
    		});
    	}
    	else{
			validateFormMsg(this.getMainContent().down('panel'), 'Сообщение пустое или не было изменено!', 0);
    	}
    }
    
});
				
				
				
