/////////////////////////////////////////////////
//модуль: Основное управление - Настройка сайта//
/////////////////////////////////////////////////

Ext.define('duscms.view.general.Config', {
    extend: 'Ext.panel.Panel',
    
    xtype: 'config',
    id: 'config',
    border: false,
    height: 'auto',
    overflow: 'auto',
    autoScroll: true,
    layout: 'fit',
    bodyPadding: 5,
    initComponent: function(){
        var me = this;
        var it = [], it1 = [], it2 = [], it3 = [];
        var keys = [];
        //var myMask = new Ext.LoadMask(Ext.getBody(), {msg: 'Загрузка данных...'});
        //console.log(me.userData);
        
        if(me.userData){
        	var data = me.userData[0];
        	keys = Object.keys(data.text);
			Ext.Array.each(keys, function(val, key){
				switch(val){
					case 'hide_title':
					case 'enable_rss':
					case 'logging':
					case 'enable_ids':
					case 'wmh':
					case 'enable_wms':
					case 'regconf':
					case 'detect_lang':
					case 'allowchskin':
					case 'allowchlang':						
						it.push(me.createItem({
			        		xtype: 'checkbox',
			        		name: 'nconfig['+val+']',
			        		inputValue: 1,
			        		fieldLabel: data.text[val],
			        		checked: parseInt(data[val]) ? true : false
			        	}));
						break;
					case 'num_of_latest':
					case 'perpage':
					case 'adm_perpage':
					case 'pr_flood':
					case 'registered_accesslevel':
						it.push(me.createItem({
			        		xtype: 'integer',
			        		name: 'nconfig['+val+']',
			        		minValue: 1,
			        		maxValue: 500,
			        		fieldLabel: data.text[val],
			        		value: parseInt(data[val])
			        	}));
			        	break;
     				case 'welcome_mesg':
     				case 'meta_tags':
     					it.push(me.createItem({
			        		xtype: 'textarea',
			        		name: val,			        		
			        		fieldLabel: data.text[val],
			        		value: data[val],
			        		minHeight: 100,
			        		maxHeight: 300,
			        		grow: true,
			        		growMax: 300,
			        		anchor: '100%',
			        		emptyText: val == 'welcome_mesg' ? 'Поддерживаются html теги' : 'Укажите meta теги через запятую'
			        	}));
			        	break;
	        		case 'index_module':
	        		case 'default_skin':
	        		case 'default_lang':
	        		case 'timezone':
	        			var list = '';
						switch(val){
	        				case 'index_module':
					 			list = 'available_modules';
								break;
					 		case 'default_skin':
					 			list = 'skins';
								break;
							case 'default_lang':
					 			list = 'langs';
								break;
							case 'timezone':
								list = 'tz';
								break;
	        			}
						var k = Object.keys(data[list]);
							
	        			var d = [];
	        			var def_val = '';
	        			var i = 0;
	        			Ext.Array.each(k, function(name, id){
							if(val !== 'timezone')
	        					id = name;
							
							d[i] = {
	        					id: id,
	        					name: data[list][name]
							};
							
							if(name == data[val]){
								def_val = id;
							}
							
							i++;
	        			});
	        			
						var store = Ext.create('Ext.data.Store', {
						    fields: ['id', 'name'],
						    data : d
						});
						
						it.push(me.createItem({
			        		xtype: 'combobox',
			        		name: 'nconfig['+val+']',			        		
			        		fieldLabel: data.text[val],
			        		value: def_val,
			        		valueField: 'id',
			        		displayField: 'name',
			        		queryMode: 'local',
			        		editable: false,
			        		store: store
			        	}));
						break;
					case 'imageSize':
						it.push(me.createItem({
			        		xtype: 'container',
			        		layout: {
			        			type: 'hbox'
			        		},
			        		defaults: {
			        			xtype: 'integer',
			        			minValue: 50,
			        			maxValue: 500
			        		},
			        		items: [{
			        			fieldLabel: data.text[val],
			        			value: data['th_width'],
			        			name: 'nconfig[th_width]',
			        			labelWidth: 400,
       							width: 600
			        		},{
			        			value: data['th_height'],
			        			name: 'nconfig[th_height]',
       							width: 195,
       							margin: '0 0 0 5'
			        		}]
			        	}));
						break;
					case 'watermark':
						it.push(me.createItem({
			        		xtype: 'fileUpload',
			        		multiple: false,
			        		fileFormat: 'img',
			        		fieldLabel: data.text[val],
			        		value: data[val],
			        		name: 'wm[]',
			        		tooltip: 'Возможно добавление только одной фотографии'
			        	}));
			        	break;
					default:
						it.push(me.createItem({
			        		xtype: 'textfield',
			        		fieldLabel: data.text[val],
			        		value: data[val],
			        		name: 'nconfig['+val+']'
			        	}));
				}
	        });
	        
	        for(var i=0; i<it.length; i++){
	        	if(i>0 && i<16){
	        		it1.push(it[i]);
	        	}
	        	else if(i>16 && i<21){
	        		it2.push(it[i]);
	        	}
	        	else if(i>21 && i<31){
	        		it3.push(it[i]);
	        	}
	        }
        }
                            
        me.items = [{
       		xtype: 'form',
       		itemId: 'configForm',
       		url: 'api.php?act=saveSiteConfig',
       		cls: 'frm_docked_background',
       		title: false,
       		border: false,
       		buttonAlign: 'center',
       		defaults: {       			
       			anchor: '100%',
       			collapsible: true,
       			defaults: {
       				labelWidth: 400,
       				width: 800
       			}
       		},
       		items: [{
       			xtype: 'fieldset',
       			title: data.text['generalParams'],
       			items: it1
       		},{
       			xtype: 'fieldset',
       			title: data.text['iboptions'],
       			items: it2
       		},{
       			xtype: 'fieldset',
       			title: data.text['interactionUser'],
       			items: it3
       		},{
        	    xtype: 'hidden',
                name: 'nconfig[watermark]',
                itemId: 'watermark'
        	}]
//       		buttons: [{
//				text: data.text['send'],
//				handler: function(btn){
//					me.fireEvent('saveSiteConfig');										
//				}
//	   		}]
        }];
        me.callParent(arguments);
    },
    createItem: function(config){
		var it = config;
    	return it;
    }    
});
