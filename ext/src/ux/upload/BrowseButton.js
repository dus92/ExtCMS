/**
 * A "browse" button for selecting multiple files for upload.
 * 
 */
Ext.define('Ext.ux.upload.BrowseButton', {
    extend : 'Ext.form.field.File',
    alias: ['widget.browseButton', 'widget.browsebutton'],

    buttonOnly : true,

    iconCls : 'ux-mu-icon-action-browse',
    buttonText : 'Обзор',

    initComponent : function() {

        this.addEvents({
            'fileselected' : true
        });

        Ext.apply(this, {
            buttonConfig : {
                iconCls : this.iconCls,
                text : this.buttonText
            }
        });

        this.on('afterrender', function() {
            /*
             * Fixing the issue when adding an icon to the button - the text does not render properly. OBSOLETE - from
             * ExtJS v4.1 the internal implementation has changed, there is no button object anymore.
             */
        	/*
            if (this.iconCls) {
                // this.button.removeCls('x-btn-icon');
                // var width = this.button.getWidth();
                // this.setWidth(width);
            }
            */

            // Allow picking multiple files at once.
            this.setMultipleInputAttribute();

        }, this);

        this.on('change', function(field, value, options) {
            var files = this.fileInputEl.dom.files;
            
			this.setGridPosition(); //animated show grid
			if (files) {
                this.fireEvent('fileselected', this, files);
            }
        }, this);

        this.callParent(arguments);
    },

    reset : function() {
        this.callParent(arguments);
        this.setMultipleInputAttribute();
    },

    setMultipleInputAttribute : function(inputEl) {
        inputEl = inputEl || this.fileInputEl;
        
		if(this.multiple)
			inputEl.dom.setAttribute('multiple', '1');
    },
    setGridPosition: function(){
    	if(this.up('fileupload')){
			var gridPanel = this.up('fileupload').down('uploadgrid');
	    	var grid = gridPanel.down('grid'); 
			if(grid.getStore().getCount() == 0){
				gridPanel.show();
				gridPanel.getEl().fadeOut({
	                endOpacity: 1,
	                easing: 'easeOut',
	                duration: 10,
	                callback: function(){
	                	gridPanel.setPosition(0,0);
	                    gridPanel.getEl().fadeIn({
	                        endOpacity: 1,
	                        easing: 'easeOut',
	                        duration: 500
	                    });	
	                }
	            });
			}
		}
		else
			return false;
    }

    // OBSOLETE - the method is not used by the superclass anymore
    /*
    createFileInput : function() {
        this.callParent(arguments);
        this.fileInputEl.dom.setAttribute('multiple', '1');
    }
    */

}
);
