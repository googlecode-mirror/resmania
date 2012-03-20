/*
* PayPal Log JS
*
* JSLint.com Check: 18/03/2011
*/
RM.Pages.Plugin_PayPal_Log_Toolbar = {
    xtype : "panel",
    id : "rm_pages_plugins_paypal_log_toolbar",
    bodyBorder : false,
    html : RM.Common.getToolbar([{image: RM.BaseLargeImageURL+"delete.gif", label: RM.Translate.Admin.PayPal.Main.ClearLog, link: "RM.Pages.Functions.plugins_paypal_clearlog()"}])
};
RM.Toolbars.push(RM.Pages.Plugin_PayPal_Log_Toolbar);

RM.Pages.Plugin_PayPal_Log_Fieldset = new Ext.form.FieldSet({
    id: "rm_pages_plugins_paypal_log_fieldset",
    title: RM.Translate.Admin.PayPal.Main.Log,
    autoHeight: true,
    layout: 'fit',
    bodyBorder : false,
    html: "Loading..."
});


RM.Pages.Plugin_PayPal_Log_Panel = new Ext.Panel({
    id: "rm_pages_plugins_paypal_log_panel",
    height : RM.Common.GetPanelHeight(76),
    title : RM.Translate.Admin.PayPal.Main.Log,
    bodyStyle : "padding:10px;",
    autoScroll : true,    
    containerScroll : true,
    items : [
        RM.Pages.Plugin_PayPal_Log_Fieldset
    ]
});

RM.Pages.Functions.plugins_paypal_clearlog = function(){
    Ext.MessageBox.confirm(RM.Translate.Common.Clear, RM.Translate.Admin.PayPal.Main.ConfirmClearLog, function(buttonID){
        if (buttonID !== 'yes') {
            return;
        }
        var myMask = new Ext.LoadMask('content-panel', {msg:RM.Translate.Common.PleaseWait});
        myMask.show();
        var conn = new Ext.data.Connection();
        var request = {
            url: RM.Common.AssembleURL({
                controller : 'PayPal',
                action: 'clearlogjson'
            }),
            method: 'POST',
            success: function(responseObject) {
                myMask.hide();
                var data = Ext.util.JSON.decode(responseObject.responseText);
                if (data.success === false) {
                    Ext.MessageBox.alert(data.message);
                } else {
                    Ext.getDom('rm_pages_plugins_paypal_log_fieldset').innerHTML = "";
                }
            },
            failure: function() {
                Ext.MessageBox.alert(RM.Translate.Common.DeleteFailure);
            }
        };
        conn.request(request);
    });
};

RM.Pages.Functions.PayPal_LogJson = function (responseObject) {
//I think we don't need this mask 'cause all ajax request are already made so assigning and layout is very quick
//    var myMask = new Ext.LoadMask('rm_pages_plugins_paypal_main_form', {
//        msg: RM.Translate.Common.PleaseWait,
//        msgCls: "RM_Loading_Mask_Msg_Hidden" // hide the msg
//    });
    
    Ext.getDom('rm_pages_plugins_paypal_log_fieldset').innerHTML = responseObject;

    Ext.getCmp('rm_pages_plugins_paypal_log_panel').render();
    Ext.getCmp('rm_pages_plugins_paypal_log_panel').doLayout();
    
    Ext.getCmp('toolbar_panel').layout.setActiveItem('rm_pages_plugins_paypal_log_toolbar');
    Ext.getCmp('content-panel').layout.setActiveItem('rm_pages_plugins_paypal_log_panel');

    RM.Help.Load('Admin.PayPal.Log.Main');

//    myMask.hide();
};

RM.Main.Pages.push(RM.Pages.Plugin_PayPal_Log_Panel);