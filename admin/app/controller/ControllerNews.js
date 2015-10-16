Ext.define('duscms.controller.ControllerNews', {
    extend: 'Ext.app.Controller',
        
    stores: [
		//'StoreNews',
	],
	
	models: [
		//'duscms.model.ModelNews',
	],
	
	views: [
		//'duscms.view.NewsGrid',               
    ],
    
    refs: [
    ],
    
    init: function() {
		this.control({

			'tabpanel': {
				tabchange: this.onTabChange
			},
			'newsGrid': {
					//createnewbuttonclick: this.onNewsGridCreateNewButtonClick,
//					deletenewbuttonclick: this.onNewsGridDeleteNewButtonClick,
//					editnewbuttonclick: this.onNewsGridEditNewButtonClick
			}
        });        
      // Ext.getStore('StoreTeachers').addListener('load',this.onTeachersStoreAfterLoad,this);
	}
});
				
				
				
