Ext.namespace('Ext.rm', 'Ext.rm.grid');

Ext.rm.grid.CheckboxSelectionModel = Ext.extend(Ext.grid.CheckboxSelectionModel, {
    renderer : function(v, p, record){        
        if (record.json !== undefined && record.json.core == 1) {
            return '<div>&#160;</div>';
        } else {
            return '<div class="x-grid3-row-checker">&#160;</div>';
        }
    }
})

Ext.reg('rmcheckboxselectionmodel', Ext.rm.grid.CheckboxSelectionModel);