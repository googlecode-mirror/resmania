Ext.namespace('Ext.rm');

Ext.rm.DragZone = function(view, config){
    Ext.rm.DragZone.superclass.constructor.call(this, view, config);    
};

Ext.extend(Ext.rm.DragZone, Ext.DataView.DragZone, {
    onEndDrag: function(data, e) {
        return true;
    }        
});