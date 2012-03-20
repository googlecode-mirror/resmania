/*
* Daily Prices Config Page JS
*
* JSLint.com Check: 18/03/2011
*/
RM.Pages.Functions.daily_prices_config_save = function(){
    RM.Pages.DailyPrices_Config.getForm().submit({
        success: function(form, action) {
            Ext.getCmp('rm_pages_config_edit_statusbar').setStatus({
                text: RM.Translate.Common.Saved,
                iconCls: 'ok-icon'
            });
        },
        waitMsg: RM.Translate.Common.Saving,
        waitTitle: RM.Translate.Common.PleaseWait
    });
};

RM.Pages.DailyPrices_Config_Toolbar = {
    xtype : "panel",
    id : "rm_pages_daily_prices_config_toolbar",
    bodyBorder : false,
    html : RM.Common.getToolbar([{image: RM.BaseLargeImageURL+"save.gif", label: RM.Translate.Common.Save, link: "RM.Pages.Functions.daily_prices_config_save()"}])
};
RM.Toolbars.push(RM.Pages.DailyPrices_Config_Toolbar);

RM.Pages.Functions.DailyPrices_ConfigJson = function (responseObject) {
    Ext.getCmp('toolbar_panel').layout.setActiveItem('rm_pages_daily_prices_config_toolbar');

    for (i = 0; i < responseObject.fields.length; i++){
        field = responseObject.fields[i];

        field.metainfo.view.id = 'daily_prices_config_' + field.name;
        field.metainfo.view.name = 'daily_prices_config[' + field.name + ']';
        if (field.metainfo.view.hiddenName) {
            field.metainfo.view.hiddenName = 'daily_prices_config[' + field.name + ']';
        }

        RM.Common.RemoveOldFields(field.metainfo.view.id);
        
        eval("Ext.getCmp('rm_pages_daily_prices_config_fieldset').add(new "+field.metainfo.class_name+"(field.metainfo.view))");
        Ext.getCmp(field.metainfo.view.id).setValue(field.value);
    }    

    Ext.getCmp('rm_pages_daily_prices_config_fieldset').doLayout();
    Ext.getCmp('content-panel').layout.setActiveItem('rm_pages_daily_prices_config');

    RM.Help.Load('Admin.DailyPrices.Config.DefaultTimes');
};

RM.Pages.DailyPrices_Config_Fieldset = new Ext.form.FieldSet({
    id: "rm_pages_daily_prices_config_fieldset",
    title: RM.Translate.Admin.DailyPrices.Main.Settings,
    autoHeight: true,
    layout: 'form',
    bodyBorder : false,
    items: []
});

RM.Pages.DailyPrices_Config = new Ext.FormPanel({
    id : "rm_pages_daily_prices_config",
    title: RM.Translate.Admin.DailyPrices.Main.GlobalConfigTitle,
    bodyStyle : "padding:10px;",
    autoScroll: true,
    url : RM.Common.AssembleURL({
        controller: 'DailyPrices',
        action: 'configupdatejson'
    }),    
    items : [RM.Pages.DailyPrices_Config_Fieldset]
});

RM.Main.Pages.push(RM.Pages.DailyPrices_Config);