Ext.namespace('Ext.rm', 'Ext.rm.form');

Ext.rm.form.DateTime = Ext.extend(Ext.ux.form.DateTime, {
    initComponent : function(){
        Ext.rm.form.DateTime.superclass.initComponent.call(this);
    }

    ,hideTime: function(){
        this.tf.hide();
    }

    ,showTime: function(){
        this.tf.show();
    }
});

Ext.reg('rmdatetime', Ext.rm.form.DateTime);