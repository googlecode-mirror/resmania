Ext.apply(Ext.form.VTypes, {
    ZendAlphanum:  function(val, field) {
        return /^[a-zA-Z0-9]+$/.test(val);
    },
    ZendAlphanumText: RM.Translate.Admin.VType.ZendAlphanumError,
    ZendAlphanumMask: /[a-zA-Z0-9]/i
});
