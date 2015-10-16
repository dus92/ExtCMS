//отображает верхнюю часть (хэдер) страницы
Ext.define('duscms.view.Header', {
    extend: 'Ext.Container',
    xtype: 'Header',
    id: 'header',
    height: 40,
    layout: {
        type: 'hbox',
        align: 'middle'
    },
	items: [
	{
		xtype: 'container',
        margin: 5,
        items: [{
            xtype: 'button',            
			scale: 'medium',
			margin: '0 10 0 0',
			text: 'Перейти к сайту',			
            tooltip: 'Перейти к сайту',
			handler: function() {
				var a=document.createElement('a');
				a.target='_blank';
				a.href='./';
				document.body.appendChild(a);
				a.click();
				document.body.removeChild(a);
			},
            iconCls: ''
		},{
            xtype: 'button',            
			scale: 'medium',
			margin: '0 10 0 0',
			text: 'На главную',//'К началу администрирования',			
            tooltip: 'Перейти к главной странице админ-панели',
			handler: function() {
				this.up('Header').fireEvent('tomainbuttonckick');
			},
            iconCls: ''
		}]
	},{
		xtype: 'container',
		id: 'logo',
		flex: 1,
		layout: {type: 'hbox', pack: 'end'},
		defaults: {
			xtype: 'button',
			width: 95,
			scale: 'medium',
			margin: '0 10 0 0'
		},
		items: [{		
			text: 'Выход',
			glyph: 115,
                        tooltip: 'Выйти',
			handler: function() {
				this.up('Header').fireEvent('exitbuttonclick');
			},
            iconCls: 'button-pictos'
		}]
	}]
	
//	initComponent: function() {
//		
//		this.callParent(arguments);
//	}
});
