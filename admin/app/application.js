Ext.define('duscms.Application', {
    name: 'duscms',

    extend: 'Ext.app.Application',

    views: [
		
        // TODO: add views here
    ],
    
    models: [
	//	'iusproject.model.Task',
	//	'iusproject.model.Taskcategory'
    ],
    //подключение всех контроллеров к проекту
    controllers: [
        // TODO: add controllers here        
        'ControllerNews'
    ],
    stores: [
	//	'iusproject.store.StoreTaskCategory',
	//	'iusproject.store.StoreTasks'
     //   // TODO: add stores here
    ],
    init: function(){
		Ext.grid.RowEditor.prototype.saveBtnText = 'Обновить';
		Ext.grid.RowEditor.prototype.cancelBtnText = 'Отмена';
//        Ext.MessageBox.msgButtons['ok'].text = Ext.MessageBox.buttonText.ok;
//        Ext.MessageBox.msgButtons['cancel'].text = 'Отмена';
//        Ext.MessageBox.msgButtons['yes'].text = 'Да';
//        Ext.MessageBox.msgButtons['no'].text = 'Нет';                
	}

});
