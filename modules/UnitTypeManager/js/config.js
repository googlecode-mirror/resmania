/*
 * Unit Types Configuration
 *
 * JSLint.com Check: 18/03/2011
 */

RM.Pages.Functions.Module_UnitTypeManager_Config_Save = function(){
    RM.Pages.Module_UnitTypeManager_Config.getForm().submit({
        success: function(form, action) {
            //TODO: update the status bar
        },
        waitMsg: RM.Translate.Common.Saving,
        waitTitle: RM.Translate.Common.PleaseWait
    });
};

RM.Pages.Module_UnitTypeManager_Config_Toolbar = {
    xtype : "panel",
    id : "rm_pages_module_UnitTypeManager_edit_toolbar",
    bodyBorder : false,
    html : RM.Common.getToolbar([{image: RM.BaseLargeImageURL+"save.gif", label: RM.Translate.Common.Save, link: "RM.Pages.Functions.Module_UnitTypeManager_Config_Save()"}])
};
RM.Toolbars.push(RM.Pages.Module_UnitTypeManager_Config_Toolbar);


// the combo shows the price module types available...
RM.Pages.Modules_UnitTypeManager_PriceSystem_Combo = {
    id : "rm_pages_module_UnitTypeManager_config_paymentselection",
    name : "prices",
    hiddenName : "prices",
    xtype : "combo",
    forceSelection: true,
    mode: "local",
    triggerAction: 'all',
    selectOnFocus: true,
    valueField: 'value',
    displayField: 'text',
    allowBlank: false,
    emptyText: RM.Translate.Common.PleaseSelect,
    cls: ''
};

RM.Pages.Module_UnitTypeManager_Config_Fieldset = new Ext.form.FieldSet({
    id: "rm_pages_module_UnitTypeManager_config_fieldset",
    title: RM.Translate.Admin.UnitTypeManager.Config.PriceModuleAssignment,
    autoHeight: true,
    bodyBorder : false,
    items: []
});

RM.Pages.Module_UnitTypeManager_Default_Fieldset = new Ext.form.FieldSet({
    id: "rm_pages_module_UnitTypeManager_default_fieldset",
    title: RM.Translate.Admin.UnitTypeManager.Config.DefaultSystemUnitType,
    autoHeight: true,
    bodyBorder : false,
    items: [{
        id : "rm_default_system_unit_type",
        name : "rm_default_system_unit_type",
        hiddenName : "rm_default_system_unit_type",
        xtype : "combo",
        forceSelection: true,
        triggerAction: 'all',
        selectOnFocus: true,
        mode: "local",
        fieldLabel: RM.Translate.Admin.UnitTypeManager.Config.UnitType,
        width: 183,        
        valueField: 'id',
        allowBlank: false,
        displayField: 'name',
        store: new Ext.data.JsonStore({
            fields: ['id', 'name', 'price'],
            root: 'unitTypes',
            idProperty: 'id'
        })
    }]
});

RM.Pages.Functions.UnitTypeManager_ConfigJson = function (responseObject) {
    Ext.getCmp('rm_default_system_unit_type').getStore().loadData(responseObject);
    Ext.getCmp('rm_default_system_unit_type').setValue(responseObject.defaultUnitType);

    var i = 0;
    for (i; i < responseObject.unitTypes.length; i++){
        var unitType = responseObject.unitTypes[i];
        RM.Pages.Functions.UnitTypeManager_ConfigJson_RenderType(unitType, responseObject.systems);
    }

    Ext.getCmp('toolbar_panel').layout.setActiveItem('rm_pages_module_UnitTypeManager_edit_toolbar');
    Ext.getCmp('content-panel').layout.setActiveItem('rm_pages_module_UnitTypeManager_config');

    Ext.getCmp('rm_pages_module_UnitTypeManager_config_fieldset').doLayout();
    RM.Help.Load('Admin.UnitTypesManager.Config.Main');
};


RM.Pages.Functions.UnitTypeManager_ConfigJson_RenderType = function(unitType, systems){
    var unitTypePriceCombo = RM.Common.Clone(RM.Pages.Modules_UnitTypeManager_PriceSystem_Combo);
    unitTypePriceCombo.id = unitTypePriceCombo.id + "["+unitType.id+"]";
    unitTypePriceCombo.fieldLabel = unitType.name;
    unitTypePriceCombo.name = unitTypePriceCombo.name + "["+unitType.id+"]";
    unitTypePriceCombo.hiddenName = unitTypePriceCombo.hiddenName + "["+unitType.id+"]";
    unitTypePriceCombo.store = new Ext.data.JsonStore({
        fields:[
            {name:'value', type:'string'},
            {name:'text', type:'string'}
        ],
        data: systems
    });

    RM.Common.RemoveOldFields(unitTypePriceCombo.id);
    Ext.getCmp('rm_pages_module_UnitTypeManager_config_fieldset').add(
        new Ext.form.ComboBox(unitTypePriceCombo)
    );

    if (unitType.price !== "") {
        Ext.getCmp(unitTypePriceCombo.id).setValue(unitType.price);
    }
};

RM.Pages.Module_UnitTypeManager_Config = new Ext.FormPanel({
    id : "rm_pages_module_UnitTypeManager_config",
    title: RM.Translate.Admin.UnitTypeManager.Config.Settings,
    autoScroll: true,
    bodyStyle : "padding:10px;",
    url : RM.Common.AssembleURL({
        controller: 'UnitTypeManager',
        action: 'configupdatejson'
    }),
    items : [
        RM.Pages.Module_UnitTypeManager_Config_Fieldset,
        RM.Pages.Module_UnitTypeManager_Default_Fieldset
    ]
});

RM.Main.Pages.push(RM.Pages.Module_UnitTypeManager_Config);