Ext.namespace('Ext.rm');
Ext.rm.LoadMask = function(){
    return new Ext.LoadMask('content-panel', {msg: RM.Translate.Common.PleaseWait});
};
