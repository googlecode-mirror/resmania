Ext.namespace('Ext.rm');

Ext.rm.DropZone = function(view, config){
    Ext.rm.DropZone.superclass.constructor.call(this, view, config);
    this.onNodeDropEvent = config.onNodeDropEvent;
    this.removeNodeFromSource = config.removeNodeFromSource;
};

Ext.extend(Ext.rm.DropZone, Ext.DataView.DropZone, {
    removeNodeFromSource: true,

    onNodeDrop : function(n, dd, e, data){
        this._onNodeDrop(n, dd, e, data);
        //Ext.rm.DropZone.superclass.onNodeDrop.call(this, n, dd, e, data);
        this.onNodeDropEvent(n, dd, e, data);
        return true;
    },

    _onNodeDrop: function(n, dd, e, data){
        var pt = this.getDropPoint(e, n, dd);
        if (n != this.view.getEl().dom)
            n = this.view.findItemFromChild(n);
        var insertAt = (n == this.view.getEl().dom) ? this.view.store.getCount() : this.view.indexOf(n);
        if (pt == "below") {
            insertAt++;
        }

        var dir = false;

        // Validate if dragging within the same MultiSelect
        if (data.sourceView == this.view) {
            // If the first element to be inserted below is the target node, remove it
            if (pt == "below") {
                if (data.viewNodes[0] == n) {
                    data.viewNodes.shift();
                }
            } else {  // If the last element to be inserted above is the target node, remove it
                if (data.viewNodes[data.viewNodes.length - 1] == n) {
                    data.viewNodes.pop();
                }
            }

            // Nothing to drop...
            if (!data.viewNodes.length) {
                return false;
            }

            // If we are moving DOWN, then because a store.remove() takes place first,
            // the insertAt must be decremented.
            if (insertAt > this.view.store.indexOf(data.records[0])) {
                dir = 'down';
                insertAt--;
            }
        }

        for (var i = 0; i < data.records.length; i++) {
            var r = data.records[i];            
            if (data.sourceView && this.removeNodeFromSource ) { //we need to add this removeNodeFromSource parameter to prevent removing node if we need it
                data.sourceView.store.remove(r);
            }            
            this.view.store.insert(dir == 'down' ? insertAt : insertAt++, r);
            var si = this.view.store.sortInfo;
            if(si){
                this.view.store.sort(si.field, si.direction);
            }
        }
        return true;
    }
});