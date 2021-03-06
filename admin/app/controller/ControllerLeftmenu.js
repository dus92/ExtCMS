Ext.define('duscms.controller.ControllerLeftmenu', {
    extend: 'Ext.app.Controller',
        
    stores: [
        'StoreModulesLocal'
	],
	
	models: [		
    	'ModelModulesLocal'   	
	],
	
	views: [		
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
            'Leftmenu': {
                beforerender: function(grid, eOpts){
                    //this.getAllowedModulesStore();
                },
                //загрузка соответствующего модуля при выборе в меню
                select: this.loadModule
            }
        });
    },    
    //формирование списка прав, добавление в store для последующего отображения в меню (отображение доступных модулей)
    getAllowedModulesStore: function(){
        var me = this;        
        var rights = duscms.getApplication().rights;
        var data = new Object();
        var j = 0;
        var group_title = '';
        var o = new Array();
        
        if(rights.length > 0){
            Ext.Array.each(rights, function(val, key){               
                var keys = Object.keys(val);
                
                if(keys.length > 1){
                    Ext.Array.each(keys, function(v, k){
                        if(k==0)
                            group_title = val[v];
                        else{
                            //data[j] = new Object();
                            var data = new Object();                    
                            data['group_title'] = group_title;
                            data['name'] = val[v];
                            data['module'] = v;
                            
                            o.push(data);                                                                                  
                            j++;  
                        }                                                                              
                    });
                }                                    
            });
            
            var obj = new Object();
            obj['data'] = o;                        
                        
            Ext.define('modelModules', {
                extend: 'Ext.data.Model',
                fields: [
                    {name: 'group_title', type: 'string'},
                    {name: 'name',  type: 'string'},
                    {name: 'module', type: 'string'}
                ]
            });
            
            var store = Ext.create('Ext.data.Store', {
                storeId: 'allowedModulesStore',
                model: 'modelModules',
                groupField: 'group_title',
                data: obj,
                proxy: {
                    type: 'memory',
                    reader: {
                        type: 'json',
                        root: 'data'
                    }
                }
            });
            return store;
        }
        return false; 
    },
    //загрузка соответствующего модуля при выборе в меню
    loadModule: function(th, record, index, eOpts){
    	var me = this;
    	var mainContent = me.getMainContent();
    	var leftmenu = me.getLeftmenu();
    	
    	leftmenu.setDisabled(true);
        if(mainContent.down('#validateMessage'))
            mainContent.remove('validateMessage');
    	
		switch(record.get('module')){
			case 'config':
				ajaxRequest(mainContent, {
					url: 'getSiteSettingsData/store',
					success: function(res){
						mainContent.setTitle(record.get('name'));
						mainContent.down('#mainPanel').removeAll();
						mainContent.down('#mainPanel').add(Ext.create('duscms.view.general.Config', {userData: res}));
						mainContent.down('#mainSendButton').down('button').onButtonClick('saveSiteConfig');
                        
						leftmenu.setDisabled(false);
                        
                        mainContent.down('#mainSendButton').show();
					}
				});
				break;
            case 'module-dis':
				var store = createStore({
		            proc: 'manageModules',
                    autoLoad: true,
                    model: function(){
                        return [{
                           name: 'id',
                           type: 'string'   
                        },{
                           name: 'title',
                           type: 'string' 
                        },{
                           name: 'copyright',
                           type: 'string' 
                        },{
                           name: 'rights',
                           type: 'object' 
                        },{
                            name: 'checked',
                            type: 'bool'
                        }];
                    }
				});
                
                mainContent.setTitle(record.get('name'));
				mainContent.down('#mainPanel').removeAll();
				mainContent.down('#mainPanel').add(Ext.create('duscms.view.general.ManageModules', {store: store}));
                
				leftmenu.setDisabled(false);
                mainContent.down('#mainSendButton').hide();
                break;
            case 'navigation':
                ajaxRequest(mainContent, {
					url: 'navigation/store',
					success: function(res){
				        mainContent.setTitle(record.get('name'));
        				mainContent.down('#mainPanel').removeAll();
        				mainContent.down('#mainPanel').add(Ext.create('duscms.view.general.NavigationPanel', {result: res}));
                        mainContent.down('#mainSendButton').down('button').onButtonClick('saveNavigation');
                        
                        leftmenu.setDisabled(false);
                        mainContent.down('#mainSendButton').show();	   
					}
                });
                break;
            case 'menus':
                var storeCurrent = createTreeStore({
		            proc: 'getCurrentMenus',
                    autoLoad: true,
                    model: function(){
                        return [{
                            name: 'id',
                            type: 'string'
                        },{
                            name: 'name',
                            type: 'string'
                        },{
                            name: 'isParent',
                            type: 'bool'
                        },{
                            name: 'ucm',
                            type: 'bool'
                        }]
                    }
				}, true);
                
                var storeUnused = createStore({
		            proc: 'getUnusedMenus',
                    autoLoad: true,
                    model: function(){
                        return [{
                            name: 'id',
                            type: 'string'
                        },{
                            name: 'name',
                            type: 'string'
                        },{
                            name: 'isParent',
                            type: 'bool'
                        },{
                            name: 'ucm',
                            type: 'bool'
                        }]
                    }
				}, true);
                
                mainContent.setTitle(record.get('name'));
				mainContent.down('#mainPanel').removeAll();
				mainContent.down('#mainPanel').add(Ext.create('duscms.view.general.Menus', {storeCurrent: storeCurrent, storeUnused: storeUnused}));
                
				leftmenu.setDisabled(false);
                mainContent.down('#mainSendButton').show();
                mainContent.down('#mainSendButton').down('button').onButtonClick('saveMenus');
                break;
			default:
				leftmenu.setDisabled(false);
				// items: [Ext.create('iusproject.view.GroupsGrid', false)]    	
		}
    }    
});
				
				
				
