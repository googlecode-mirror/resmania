Ext.apply(Ext.form.VTypes, {
    RM_PhoneVType:  function(v) {
        return /[0-9|\s|\+|\-|\(|\)]{6,20}/.test(v);
    },
    RM_PhoneVTypeText: RM.Translate.Admin.VType.PhoneError,
    RM_PhoneVTypeMask: /[0-9|\s|\+|\-|\(|\)]/i
});