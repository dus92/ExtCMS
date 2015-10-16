//Основное управление
Ext.define('duscms.controller.ControllerGeneral', {
    extend: 'Ext.app.Controller',
        
    stores: [
        //'StoreModulesLocal'
	],
	
	models: [
		
	],
	
	views: [
      'duscms.view.general.Config',
      'duscms.view.MainContent'
    ],
    
    refs: [
        {
            ref: 'siteConfig',
            selector: 'config'
        },
        {
            ref: 'mainContent',
            selector: 'mainContent'
        }
    ],
    
    init: function() {
	//	var myMask = new Ext.LoadMask(Ext.getBody(), {msg: 'Загрузка данных...'});
     //   myMask.show();
        //this.getMainContent().down('#mainSendButton').down('button').fireEvent('saveSiteConfig');
        console.log('init');
        this.control({
            'mainContent': {
       			 saveSiteConfig: this.saveSiteConfig
            }
        });
	},
	//сохранение настроек сайта (в config.ini)
	saveSiteConfig: function(){
		var me = this;
		var view = me.getSiteConfig();
		var form = view.down('#configForm');
        var filesGrid = form.down('fileuploadgrid').down('grid');
		//var myMask = new Ext.LoadMask(me.getMainContent(), {msg: 'Обработка данных...'});    	
		
		if(form.isValid() && form.isDirty()){
			//myMask.show();
			form.submitEmptyText = false;
            if(filesGrid.getStore().getCount() > 0){
                var url = filesGrid.getStore().getAt(0).get('url'); 
                if(url && url != ''){
                    form.down('#watermark').setValue(url);
                }
                else
                    form.down('#watermark').reset();
            }
			
			onSubmitForm(form, {
				url: 'saveSiteConfig',
                vMsgForm: me.getMainContent()
			});
			
    		//form.submit({
//    			success: function(f, action) {			       	
//			       	validateFormMsg(form, action.result.msg, 1);
//					myMask.destroy();
//			    },
//			    failure: function(form, action) {
//			    	Ext.Msg.show({
//					    title: 'Ошибка',
//					    msg: action.result.msg,
//					    buttons: Ext.Msg.OK,					    					    
//					    icon: Ext.Msg.ERROR
//					});
//					myMask.destroy();
//			    }
//    		});
    	}
    	else{
			validateFormMsg(me.getMainContent(), 'Поля формы не были изменены!', 0);
    	}
	}
    
});
				
				
				
