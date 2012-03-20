/*
 * Edit Unit Types Module JS
 * This creates the admin GUI Edit Page for the Unit Types Module
 *
 * JSLint.com Check: 18/03/2011
 */

RM.Pages.Functions.Module_UnitType_Edit_Save = function(){
    RM.Pages.Module_UnitTypes_Edit_Form.getForm().submit({
        success: function(form, action) {
	    RM.Pages.Functions.Units_Edit_Unit_Request(RM.Pages.Units_Edit_UnitID);
        },
        params: {
            'unit_id' : RM.Pages.Units_Edit_UnitID
        },
        waitMsg: RM.Translate.Common.Saving,
        waitTitle: RM.Translate.Common.PleaseWait
    });
};

RM.Pages.Module_UnitType_Edit_Toolbar = {
    xtype : "panel",
    id : "rm_pages_module_unittype_edit_toolbar",
    bodyBorder : false,
    html : RM.Common.getToolbar([{image: RM.BaseLargeImageURL+"save.gif", label: RM.Translate.Admin.UnitTypeManager.UnitEdit.Save, link: "RM.Pages.Functions.Module_UnitType_Edit_Save()"}])
};
RM.Toolbars.push(RM.Pages.Module_UnitType_Edit_Toolbar);

RM.Pages.Functions.Module_UnitType_EditJson_Request = function(){
    var unit_id = RM.Pages.Units_Edit_UnitID;

    var myMask = new Ext.LoadMask('content-panel', {msg:RM.Translate.Common.PleaseWait});
    myMask.show();

    var conn = new Ext.data.Connection();
    var request = {
        url: RM.Common.AssembleURL({
            controller : 'UnitTypeManager',
            action: 'loadeditjson',
            parameters : [{
                name : 'id',
                value : unit_id
            }]
        }),
        method: 'POST',
        success: function(responseObject) {
            eval('RM.Pages.Functions.Module_UnitType_Edit_ConfigJson('+responseObject.responseText+');');
            myMask.hide();
        },
        failure: function() {
            Ext.Msg.alert(RM.Translate.Common.Status, RM.Translate.Common.UnableToShow);
            myMask.hide();
        }
    };
    conn.request(request);
};

RM.Pages.Functions.Module_UnitType_Edit_ConfigJson = function(ResponseObject){

    if (Ext.getCmp('rm_pages_module_unittype_selection')){
        RM.Common.RemoveOldFields('rm_pages_module_unittype_selection');
    }

    var SelectionData = new Ext.data.JsonStore({
        fields:[
        {
            name:'id',
            type:'string'
        },
        {
            name: Ext.getCmp('edit_units_iso').value,
            type:'string'
        }
        ],
        data: ResponseObject.allUnitTypes
    });

    Ext.getCmp("rm_pages_module_unittype_selection_fieldset").add({
        id : "rm_pages_module_unittype_selection",
        name : "rm_pages_module_unittype_selection_name",
        hiddenName : "rm_pages_module_unittype_selection_hidden",
        xtype : "combo",
        fieldLabel : RM.Translate.Admin.UnitTypeManager.UnitEdit.UnitType,
        store : SelectionData,
        forceSelection: true,
        mode: "local",
        triggerAction: 'all',
        selectOnFocus: true,
        valueField: 'id',
        displayField: Ext.getCmp('edit_units_iso').value,
        allowBlank: false,
        emptyText: RM.Translate.Common.PleaseSelect
    });

    // set the combo value
    Ext.getCmp("rm_pages_module_unittype_selection").setValue(ResponseObject.currentUnitType.id, undefined, true);
    Ext.getCmp("rm_pages_module_unittype_edit_form").doLayout();
};

RM.Pages.Module_UnitTypes_Edit_Form = new Ext.FormPanel({
    xtype : "panel",
    id: 'rm_pages_module_unittype_edit_form',
    height: RM.Common.GetPanelHeight(174),
    title : RM.Translate.Admin.UnitTypeManager.Main.name,
    bodyStyle : "padding:10px;",
    labelWidth: 190,
    bodyBorder: false,
    url : RM.Common.AssembleURL({
        controller: 'UnitTypeManager',
        action: 'updateunitjson'
    }),
    items : [{
        xtype : "fieldset",
        title: RM.Translate.Admin.UnitTypeManager.UnitEdit.Selection,
        autoHeight: true,
        layout: 'form',
        labelWidth: 190,
        id : "rm_pages_module_unittype_selection_fieldset",
        autoWidth: true,
        bodyBorder : false
    }],
    listeners: {
        'beforehide' : function(){
            Ext.getCmp('toolbar_panel').layout.setActiveItem('rm_pages_units_edit_toolbar');
            return true;
        },
        'beforeshow' : function(){
            Ext.getCmp('toolbar_panel').layout.setActiveItem('rm_pages_module_unittype_edit_toolbar');
            RM.Pages.Functions.Module_UnitType_EditJson_Request();
            return true;
        }
    }
});

RM.Pages.Units_Edit_TabPanel.add(RM.Pages.Module_UnitTypes_Edit_Form);