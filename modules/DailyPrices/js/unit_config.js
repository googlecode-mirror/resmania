/*
* Daily Prices Unit Config Page JS
*
* JSLint.com Check: 18/03/2011
*/
RM.Pages.Functions.unit_daily_prices_config_save = function(){
    var myMask = new Ext.LoadMask('content-panel', {msg:RM.Translate.Common.PleaseWait});
    myMask.show();
    
    var jsonParameters = [];
    jsonParameters.push({
        name : 'id',
        value : Ext.getCmp('unit_daily_prices_config[unit_id]').getValue()
    });
    var i = 0;for (i; i < RM.Pages.Functions.Unit_DailyPrices_Config_Inputs.length; i++){
        jsonParameters.push({
            name : RM.Pages.Functions.Unit_DailyPrices_Config_Inputs[i].name,
            value : Ext.getCmp(RM.Pages.Functions.Unit_DailyPrices_Config_Inputs[i].id).getValue()
        });
    }

    var conn = new Ext.data.Connection();
    var request = {
        url: RM.Common.AssembleURL({
            controller : 'DailyPrices',
            action: 'updateunitconfigjson',
            parameters : jsonParameters
        }),
        method: 'POST',
        success: function(responseObject) {
            Ext.getCmp('rm_pages_reservations_edit_statusbar').setStatus({
                text: RM.Translate.Common.Saved,
                iconCls: 'ok-icon'
            });
            myMask.hide();
        },
        failure: function() {
            Ext.Msg.alert(RM.Translate.Common.Status, RM.Translate.Common.UnableToShow);
            myMask.hide();
        }
    };
    conn.request(request);
};

RM.Pages.Unit_DailyPrices_Config_Toolbar = {
    xtype : "panel",
    id : "rm_pages_unit_daily_prices_config_toolbar",
    bodyBorder : false,
    html : RM.Common.getToolbar([{image: RM.BaseLargeImageURL+"save.gif", label: RM.Translate.Common.Save, link: "RM.Pages.Functions.unit_daily_prices_config_save()"}])
};
RM.Toolbars.push(RM.Pages.Unit_DailyPrices_Config_Toolbar);

RM.Pages.Functions.Unit_DailyPrices_Config_EditJson_Request = function(){
    var unit_id = RM.Pages.Units_Edit_UnitID;
    var myMask = new Ext.LoadMask('content-panel', {msg:RM.Translate.Common.PleaseWait});
    myMask.show();

    var conn = new Ext.data.Connection();
    var request = {
        url: RM.Common.AssembleURL({
            controller : 'DailyPrices',
            action: 'unitconfigjson',
            parameters : [{
                name : 'id',
                value : unit_id
            }]
        }),
        method: 'POST',
        success: function(responseObject) {
            eval('RM.Pages.Functions.Unit_DailyPrices_ConfigJson('+responseObject.responseText+');');
            myMask.hide();
        },
        failure: function() {
            Ext.Msg.alert(RM.Translate.Common.Status, RM.Translate.Common.UnableToShow);
            myMask.hide();
        }
    };
    conn.request(request);
};

RM.Pages.Functions.Unit_DailyPrices_Config_Inputs = [];
RM.Pages.Functions.Unit_DailyPrices_ConfigJson = function (responseObject) {
    RM.Pages.Functions.Unit_DailyPrices_Config_Inputs = [];

    Ext.getCmp('unit_daily_prices_config[unit_id]').setValue(responseObject.unit_id);

    for (i = 0; i < responseObject.fields.length; i++){
        var field = responseObject.fields[i];

        field.metainfo.view.id = 'unit_daily_prices_config_' + field.name;
        field.metainfo.view.name = 'unit_daily_prices_config[' + field.name + ']';
        if (field.metainfo.view.hiddenName) {
            field.metainfo.view.hiddenName = 'unit_daily_prices_config[' + field.name + ']';
        }

        RM.Common.RemoveOldFields(field.metainfo.view.id);


        RM.Pages.Functions.Unit_DailyPrices_Config_Inputs.push({
            id: field.metainfo.view.id,
            name: field.metainfo.view.name
        });        
        
        eval("Ext.getCmp('rm_pages_unit_daily_prices_config').add(new "+field.metainfo.class_name+"(field.metainfo.view))");
        Ext.getCmp(field.metainfo.view.id).setValue(field.value);
    }

    Ext.getCmp("rm_pages_unit_daily_prices_config").doLayout(); // force the layout

    RM.Help.Load('Admin.DailyPrices.Config.DefaultTimes');
};

RM.Pages.Unit_DailyPrices_Config = new Ext.form.FieldSet({
    title: RM.Translate.Admin.DailyPrices.UnitConfig.Settings,
    autoHeight: true,
    layout: 'form',
    autoWidth: true,
    bodyBorder : false,
    items:[{
        layout: 'form',
        id : "rm_pages_unit_daily_prices_config",
        bodyStyle : "padding:10px",
        bodyBorder : false,
        items : [{
            xtype: "hidden",
            id: "unit_daily_prices_config[unit_id]",
            name: "unit_daily_prices_config[unit_id]"
        }]
    }]
});

RM.Pages.Unit_DailyPrices_Config_Panel = new Ext.Panel({
    id: "rm_pages_unit_DailyPrices_panel",
    title: RM.Translate.Admin.DailyPrices.UnitConfig.Tabtitle,
    height: 100,
    bodyStyle : "padding:10px",
    autoScroll: true,
    items: [
        RM.Pages.Unit_DailyPrices_Config
    ],
    listeners: {
        'beforerender' : function(){                        
            RM.Pages.Functions.Unit_DailyPrices_Config_EditJson_Request();
            return true;
        },
        'beforehide' : function(){
            Ext.getCmp('toolbar_panel').layout.setActiveItem('rm_pages_units_edit_toolbar');
            return true;
        },
        'beforeshow' : function(){
            Ext.getCmp('toolbar_panel').layout.setActiveItem('rm_pages_unit_daily_prices_config_toolbar');
            RM.Pages.Functions.Unit_DailyPrices_Config_EditJson_Request();
            return true;
        }
    }
});

RM.Pages.Units_Edit_TabPanel.add(RM.Pages.Unit_DailyPrices_Config_Panel);