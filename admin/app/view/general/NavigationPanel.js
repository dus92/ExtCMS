/////////////////////////////////////////////////////
//модуль: Основное управление - Управление модулями//
/////////////////////////////////////////////////////

Ext.define('duscms.view.general.NavigationPanel', {
    extend: 'Ext.form.Panel',
    xtype: 'navigationPanel',
    height: 'auto',
    overflow: 'auto',
    autoScroll: true,
    margin: 0,
    flex: 1,
    initComponent: function(){
        var me = this;
        var items_arr = [];
        
        if(me.result && me.result.length > 0){
            items_arr.push({
               xtype: 'container',
               layout: {
                    type: 'hbox'
               },
               margin: 5,
               defaults: {
                    xtype: 'displayfield',
                    margin: '0 5 0 5'
               },
               items: [{
                    value: '<span style="color:#1D5F8F; font-weight: bold;">Ссылка</span>',
                    width: 300                    
               },{
                    value: '<span style="color:#1D5F8F; font-weight: bold;">Заголовок</span>'
               }]
            });
            
            for(var i in me.result){
                items_arr.push({
                   xtype: 'container',
                   layout: {
                        type: 'hbox'
                   },
                   margin: 5,
                   defaults: {
                        xtype: 'textfield',
                        margin: 5
                   },
                   items: [{
                        value: me.result[i].url,
                        width: 300,
                        name: 'urls['+i+']'
                   },{
                        value: me.result[i].name,
                        name: 'names['+i+']'
                   },{
                        xtype: 'checkbox',
                        boxLabel: 'Открыть в новом окне',
                        checked: me.result[i].checked,
                        inputValue: 1,
                        name: 'ext['+i+']'
                   }]
                });
            }
            
            items_arr.push({
               xtype: 'container',
               anchor: '100%',
               flex: 1,
               margin: '20 0 0 0',
               html: '<div style="padding: 10px; background: rgba(169, 224, 245, 0.27);">Если вы хотите удалить элемент - оставьте ссылку пустой, если добавить - то заполнить последнюю строку.</div>'+
                '<div style="padding: 10px; background: rgba(169, 224, 245, 0.27);">Вы можете использовать модификаторы для создания ссылки к некоторым частям сайта, для этого введите МОДИФИКАТОР:ОПЦИИ в колонке "Ссылка".'+ 
                'Чтобы переопределить заголовок генерируемый модификатором введите свой в поле "Заголовок", иначе оставьте его пустым. Ниже приведены описания доступных модификаторов.</div>'+
                '<div style="padding: 10px; background: rgba(169, 224, 245, 0.27);"><span style="font-weight: bold; padding-right: 20px;">module</span>	Используется для создания ссылки на модуль, вам достаточно лишь ввести его ID после символа ":".</div>'
            });
            
            me.items = items_arr;
        }
        
        me.callParent();
    }
    
});
