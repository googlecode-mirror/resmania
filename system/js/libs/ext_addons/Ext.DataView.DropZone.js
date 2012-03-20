/*
* DragView JS
*
* JSLint.com Check: 27/01/2010
*/

Ext.DataView.DropZone = function(view, config){
    this.view = view;
    var ddGroup = config.ddGroup || 'DataViewDD';
    var dd;
    if (Ext.isArray(ddGroup)){
        dd = ddGroup.shift();
    } else {
        dd = ddGroup;
        ddGroup = null;
    }
    Ext.DataView.DropZone.superclass.constructor.call(this, this.view.getEl(), { containerScroll: true, ddGroup: dd });
    this.setDroppable(ddGroup);
};

Ext.extend(Ext.DataView.DropZone, Ext.dd.DropZone, {
    /**
     * Part of the Ext.dd.DropZone interface. If no target node is found, the
     * whole Element becomes the target, and this causes the drop gesture to append.
     */
    getTargetFromEvent : function(e) {
        var target = e.getTarget();
        return target;
    },

    // private
    getDropPoint : function(e, n, dd){
        if (n == this.view.dom) { return "below"; }
        var t = Ext.lib.Dom.getY(n), b = t + n.offsetHeight;
        var c = t + (b - t) / 2;
        var y = Ext.lib.Event.getPageY(e);
        if(y <= c) {
            return "above";
        }else{
            return "below";
        }
    },

    // private
    isValidDropPoint: function(pt, n, data) {
        if (!data.viewNodes || (data.viewNodes.length != 1)) {
            return true;
        }
        var d = data.viewNodes[0];
        if (d == n) {
            return false;
        }
        if ((pt == "below") && (n.nextSibling == d)) {
            return false;
        }
        if ((pt == "above") && (n.previousSibling == d)) {
            return false;
        }
        return true;
    },

    // override
    onNodeEnter : function(n, dd, e, data){
        return false;
    },

    // override
    onNodeOver : function(n, dd, e, data){
        var dragElClass = this.dropNotAllowed;
        var pt = this.getDropPoint(e, n, dd);
        if (this.isValidDropPoint(pt, n, data)) {
            // set the insert point style on the target node
            if (pt) {
                var targetElClass;
                if (pt == "above"){
                    dragElClass = n.previousSibling ? "x-tree-drop-ok-between" : "x-tree-drop-ok-above";
                    targetElClass = "x-view-drag-insert-above";
                } else {
                    dragElClass = n.nextSibling ? "x-tree-drop-ok-between" : "x-tree-drop-ok-below";
                    targetElClass = "x-view-drag-insert-below";
                }
                if (this.lastInsertClass != targetElClass){
                    Ext.fly(n).replaceClass(this.lastInsertClass, targetElClass);
                    this.lastInsertClass = targetElClass;
                }
            }
        }
        return dragElClass;
    },

    // private
    onNodeOut : function(n, dd, e, data){
        this.removeDropIndicators(n);
    },

    // private
    onNodeDrop : function(n, dd, e, data){
        var pt = this.getDropPoint(e, n, dd);
        if (n != this.view.getEl().dom){n = this.view.findItemFromChild(n);}
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
            if (data.sourceView) {
                data.sourceView.store.remove(r);
            }
            this.view.store.insert(dir == 'down' ? insertAt : insertAt++, r);
            var si = this.view.store.sortInfo;
            if(si){
                this.view.store.sort(si.field, si.direction);
            }
        }
        return true;
    },

    // private
    removeDropIndicators : function(n){
        if(n){
            Ext.fly(n).removeClass([
                "x-view-drag-insert-above",
                "x-view-drag-insert-left",
                "x-view-drag-insert-right",
                "x-view-drag-insert-below"]);
            this.lastInsertClass = "_noclass";
        }
    },

    // private
    setDroppable: function(ddGroup){
        if (!ddGroup) {return;}
        if (Ext.isArray(ddGroup)) {
            Ext.each(ddGroup, this.setDroppable, this);
            return;
        }
        this.addToGroup(ddGroup);
    }
});