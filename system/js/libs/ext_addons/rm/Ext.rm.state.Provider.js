Ext.namespace('Ext.rm', 'Ext.rm.state');

Ext.rm.state.Provider = function(config){
    Ext.rm.state.Provider.superclass.constructor.call(this);    
    Ext.apply(this, config);    
};

Ext.extend(Ext.rm.state.Provider , Ext.state.Provider, {});