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
      'duscms.view.MainContent',
      'duscms.view.general.ManageModules',
      'duscms.view.general.NavigationPanel',
      'duscms.view.general.Menus'
    ],
    
    refs: [{
        ref: 'siteConfig',
        selector: 'config'
    },{
        ref: 'mainContent',
        selector: 'mainContent'
    },{
        ref: 'manageModules',
        selector: 'manageModules'
    },{
        ref: 'navigationPanel',
        selector: 'navigationPanel'
    },{
        ref: 'menus',
        selector: 'menus'
    }],
    
    init: function() {
	//	var myMask = new Ext.LoadMask(Ext.getBody(), {msg: 'Загрузка данных...'});
     //   myMask.show();
        //this.getMainContent().down('#mainSendButton').down('button').fireEvent('saveSiteConfig');
        this.control({
            'mainContent': {
       			 saveSiteConfig: this.saveSiteConfig,
                 saveNavigation: this.saveNavigation,
                 saveMenus: this.saveMenus
            },
            'manageModules': {
                manageModulesSave: this.manageModulesSave
            },
            'menus': {
                onUcmEdit: this.onUcmEdit,
                onUcmDelete: this.onUcmDelete
            }
        });
	},
	//сохранение настроек сайта (в config.ini)
	saveSiteConfig: function(){
		var me = this;
		var view = me.getSiteConfig();
		var form = view.down('#configForm');
        var filesGrid = form.down('fileuploadgrid').down('grid');
		
		if(form.isValid()){
            if(filesGrid.getStore().getCount() > 0){
                var url = filesGrid.getStore().getAt(0).get('url'); 
                if(url && url != ''){
                    form.down('#watermark').setValue(url);
                }
                else
                    form.down('#watermark').reset();
            }
			
			onSubmitForm(form, {
				url: 'siteConfig',
                vMsgForm: me.getMainContent()
			});
    	}
	},
    manageModulesSave: function(record, checked){
        var me = this;
        var disabled = checked ? 1 : 0;
        if(record){
            ajaxRequest(me.getManageModules(), {
                url: 'switchModuleState/save',
                params: {
                    moduleId: record.get('id'),
                    disabled: disabled
                },
                success: function(result){
                    record.set('checked', checked);
                    record.commit();
                }
            });
        }
    },
    //Сохранение информации для панели навигации
    saveNavigation: function(){
        var me = this;
        var form = me.getNavigationPanel();
        
        onSubmitForm(form, {
			url: 'saveNavigation',
            vMsgForm: me.getMainContent()
		});
    },
    //Сохранение активных модулей меню
    saveMenus: function(){
        var me = this;
        var formMenus = me.getMenus();
        var menuParams = [];
        formMenus.down('#modulesTree').getRootNode().cascadeBy(function(rec){
            if(!rec.isRoot()){
                menuParams.push(rec.get('id'));
            }
        });
        
        ajaxRequest(formMenus, {
            url: 'saveCurrentMenus/save',
            params: {
                'menu[]': menuParams.length==0 ? '' : menuParams
            },
            success: function(result){
                
            }
        });
    },
    //Редактирование модуля меню
    onUcmEdit: function(record){
        var win = Ext.create('ExtCMS.window', {
           width: 700,
           title: 'Редактировние модуля меню',
           formVisible: true,
           formDefaults: {
                xtype: 'textfield',
                width: 300,
                labelWidth: 100
           },
           fieldsGet: function(){
                return [{
                    fieldLabel: 'MenuID',
                    name: ''
                },{
                    fieldLabel: 'Заголовок',
                    name: ''
                },{
                    xtype: 'combobox',
                    fieldLabel: 'Выравнивание',
                    store: '',
                    editable: false,
                    name: ''
                },{
                    xtype: 'htmleditor',
                    fieldLabel: 'Текст',
                    anchor: '100%'
                }];
           }
        });
        
        win.show();
    },
    //Удаление модуля меню
    onUcmDelete: function(record){
        
    }
    
});
				
				
				
