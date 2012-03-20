/*
* Taxes JS
*
* JSLint.com Check: 18/03/2011
*/
RM.Pages.Functions.Taxes_Edit_Change_Language = function(combo, record, index){    
    RM.Pages.Taxes_Edit_Json_Store.load({params:{start: 0, limit: 15, iso: record.data.field1}});
};

RM.Pages.Functions.Taxes_Edit_Add = function(){
    var newRow = new RM.Pages.Taxes_Edit_Selection_Grid_Create_Row({
        id: 0
    });
    RM.Pages.Taxes_Edit_Selection_Grid.stopEditing();
    RM.Pages.Taxes_Edit_Json_Store.insert(0, newRow);
    RM.Pages.Taxes_Edit_Selection_Grid.startEditing(0, 0);
};

RM.Pages.Functions.Taxes_Edit_Delete = function(){
    var selections = RM.Pages.Taxes_Edit_Columns_SM.getSelections();
    if (selections.length === 0){
        Ext.Msg.alert(RM.Translate.Common.Status, RM.Translate.Common.NeedToSelectRows);
        return;
    }
    Ext.MessageBox.confirm(RM.Translate.Common.Delete, RM.Translate.Admin.Taxes.Edit.DeleteAlert, function(buttonID){
        if (buttonID !== 'yes') {
            return;
        }

        var parametersJson = [];
        var i = 0;for (i; i < selections.length; i++){
            parametersJson.push({
                name : 'ids[]',
                value : selections[i].data.id
            });
        }

        var myMask = new Ext.LoadMask('content-panel', {
            msg:RM.Translate.Common.PleaseWait
        });
        myMask.show();
        var conn = new Ext.data.Connection();
        var request = {
            url: RM.Common.AssembleURL({
                controller : 'Taxes',
                action: 'deletejson',
                parameters : parametersJson
            }),
            method: 'POST',
            success: function(responseObject) {
                RM.Pages.Taxes_Edit_Json_Store.load();
                myMask.hide();
            },
            failure: function() {
                Ext.Msg.alert(RM.Translate.Common.Status, RM.Translate.Common.UnableToShow);
                myMask.hide();
            }
        };
        conn.request(request);
    });
};

RM.Pages.Functions.Taxes_Edit_Save = function(){    
    var records = RM.Pages.Taxes_Edit_Json_Store.getModifiedRecords();
    if(!records.length) {
        return;
    }

    var myMask = new Ext.LoadMask('content-panel', {msg: RM.Translate.Common.PleaseWait});
    myMask.show();
    
    var data = [];
    Ext.each(records, function(r, i) {
        var o = r.getChanges();
        if(r.data.newRecord) {
            o.newRecord = true;
        }
        o.id = r.get('id');
        data.push(o);
    }, this);

    var o = {
        url:RM.Common.AssembleURL({
            controller : 'Taxes',
            action: 'updatejson'
        }),
        method:'post',
        scope:this,
        params:{
            data:Ext.encode(data),
            iso: Ext.getCmp('edit_taxes_iso').value
        },
        success: function(responseObject) {
            myMask.hide();
            RM.Pages.Taxes_Edit_Json_Store.load();            
        },
        failure: function() {
            Ext.Msg.alert(RM.Translate.Common.Status, RM.Translate.Common.UnableToShow);
            myMask.hide();
        }
    };
    Ext.Ajax.request(o);
};

RM.Pages.Taxes_Edit_Toolbar = {
    xtype : "panel",
    id : "rm_pages_taxes_config_toolbar",
    bodyBorder : false,
    html : RM.Common.getToolbar([
        {image: RM.BaseLargeImageURL+"new.gif", label: RM.Translate.Common.Add, link: "RM.Pages.Functions.Taxes_Edit_Add()"},
        {image: RM.BaseLargeImageURL+"delete.gif", label: RM.Translate.Common.Delete, link: "RM.Pages.Functions.Taxes_Edit_Delete()"},
        {image: RM.BaseLargeImageURL+"save.gif", label: RM.Translate.Admin.Taxes.Edit.Save, link: "RM.Pages.Functions.Taxes_Edit_Save()"}
    ])
};
RM.Toolbars.push(RM.Pages.Taxes_Edit_Toolbar);

RM.Pages.Taxes_Edit_Fields = [{
    id: "id",
    name: "id"
},{
    id: "name",
    name: "name"
},{
    id: "units",
    name: "units"
},{
    id: "amount",
    name: "amount"
},{
    id: "type",
    name: "type"
},{
    id: "enabled",
    name: "enabled"
}];

RM.Pages.Taxes_Edit_Columns_SM = new Ext.grid.CheckboxSelectionModel();
RM.Pages.Taxes_Edit_Columns = [
    RM.Pages.Taxes_Edit_Columns_SM,
{
    dataIndex: "id",
    header: RM.Translate.Common.Id
},{
    id: 'name',
    dataIndex: "name",
    header: RM.Translate.Admin.Taxes.Fields.Name,
    sortable: false,
    editor: new Ext.form.TextField({
        allowBlank: false
    })
},{
    dataIndex: "units",
    header: RM.Translate.Admin.Taxes.Fields.SelectedUnits,
    editor: new Ext.ux.form.CheckboxCombo({
		width: 250,
		mode: 'local',
		store: new Ext.data.JsonStore({
            url: RM.Common.AssembleURL({
                controller: 'Taxes',
                action: 'unitlistJson'
            }),
            autoLoad: true,
            sortInfo: { field: 'name', direction: "ASC"},
            idProperty : 'id',
            fields : [
                {name:'id', type:'string'},
                {name:'name', type:'string'}
            ]
        }),
		valueField: 'id',
		displayField: 'name',
		allowBlank: false
    }),
    renderer: function(value, metaData, record, rowIndex, colIndex, store){
        if (value=="0"){
            return RM.Translate.Common.AllUnits;
        } else {
            return value;
        }
    }
},{
    dataIndex: "amount",
    header: RM.Translate.Admin.Taxes.Fields.Amount,
    editor: new Ext.form.NumberField({
        allowBlank: false,
        allowNegative: false
    })
},{
    dataIndex: "type",
    header: RM.Translate.Admin.Taxes.Fields.Type,
    editor: new Ext.form.ComboBox({
        mode: 'local',
        displayField: "type",
        triggerAction: 'all',
        typeAhead: true,
        resizable: true,
        minListWidth: 220,
        store: [
            ['percentage', RM.Translate.Admin.Taxes.Type.Percentage],
            ['amount', RM.Translate.Admin.Taxes.Type.Amount],
            ['daily', RM.Translate.Admin.Taxes.Type.Daily],
            ['dailyperperson', RM.Translate.Admin.Taxes.Type.DailyPerPerson]
        ]
    })
},{
    dataIndex: "enabled",
    header: RM.Translate.Admin.Taxes.Fields.Enabled,
    editor: new Ext.form.ComboBox({
        mode: 'local',
        displayField: "enabled",
        triggerAction: 'all',
        typeAhead: true,
        resizable: true,
        minListWidth: 220,
        store: [
            ['0', RM.Translate.Common.MessageNo],
            ['1', RM.Translate.Common.MessageYes]
        ]
    }),
    renderer: function(value, metaData, record, rowIndex, colIndex, store) {if (value=="1") {metaData.css = 'RM_list_enabled_icon';} else {metaData.css = 'RM_list_disabled_icon'; }}
}];

RM.Pages.Taxes_Edit_Json_Store = new Ext.data.JsonStore({
    url : RM.Common.AssembleURL({
        controller: 'Taxes',
        action: 'listJson'
    }),
    id: 'rm_taxes_edit_grid_json_store',
    root: 'data',
    fields: RM.Pages.Taxes_Edit_Fields,
    pruneModifiedRecords: true,
    sortInfo: {
        field: 'id',
        direction: 'ASC'
    },
    remoteSort: true,
    listeners: {
        "beforeload": function(gridLoader, node) {
            RM.Pages.Taxes_Edit_Json_Store.baseParams.iso = Ext.getCmp('edit_taxes_iso').value;
        }
    }
});

RM.Pages.Taxes_Edit_Columns = new Ext.grid.ColumnModel({
    columns: RM.Pages.Taxes_Edit_Columns,
    defaults: {
        sortable: true
    }
});
RM.Pages.Taxes_Edit_Selection_Grid_Create_Row = Ext.data.Record.create([{
    name: 'id',
    type: 'string'
}]);

RM.Pages.Taxes_Edit_Selection_Grid = new Ext.grid.EditorGridPanel({
    id : 'rm_taxes_edit_grid',
    clicksToEdit: 1,
    stripeRows: true,
    height: RM.Common.GetPanelHeight(112),
    cm : RM.Pages.Taxes_Edit_Columns,
    sm : RM.Pages.Taxes_Edit_Columns_SM,
    store : RM.Pages.Taxes_Edit_Json_Store,
    bbar : new Ext.PagingToolbar({        
        store: RM.Pages.Taxes_Edit_Json_Store,
        pageSize: 15
    }),
    viewConfig: {
        forceFit: true
    },
    autoExpandColumn: 'name'
});

RM.Pages.Functions.Taxes_EditJson = function() {
    Ext.getCmp('edit_taxes_iso').setValue(RM.Language);
    RM.Pages.Taxes_Edit_Json_Store.load({params: { start: 0, limit: 15 }});
    
    Ext.getCmp('toolbar_panel').layout.setActiveItem('rm_pages_taxes_config_toolbar');
    Ext.getCmp('content-panel').layout.setActiveItem('rm_pages_taxes_edit');
};

RM.Pages.Taxes_Edit_Language_Combo = new Ext.form.ComboBox({
    id : "edit_taxes_iso",
    hiddenName : "edit_taxes[iso]",
    xtype : "combo",
    cls: 'RM_language_selection_combo',
    typeAhead: true,
    fieldLabel: RM.Translate.Common.Languages,
    forceSelection: true,
    triggerAction: 'all',
    selectOnFocus: true,
    store: RM.Languages,
    bodyBorder : false,
    frame : false,
    width: 180,
    listeners: {
        'select': RM.Pages.Functions.Taxes_Edit_Change_Language
    }
});

RM.Pages.Taxes_Edit_Language = new Ext.Panel({
    id : "rm_pages_taxes_edit_language",
    layout: 'form',
    frame : false,
    bodyBorder : false,
    items : [
        RM.Pages.Taxes_Edit_Language_Combo
    ]
});

RM.Pages.Taxes_Edit_Title = {
    xtype : "panel",
    id : "rm_pages_taxes_edit_title",
    bodyBorder : false,
    frame : false,
    html : "<span class='RM_Title_Language_Bar_img'><img src='"+RM.BaseMenuImageURL+"money-coin.png'></span>&nbsp;<span class='RM_Title_Language_Bar_text'>"+RM.Translate.Admin.Taxes.Edit.EditTaxes+"</span>"
};

RM.Pages.Taxes_Edit_Language_Form = new Ext.FormPanel({
    id : "rm_pages_taxes_edit_language_selection",
    xtype : "form",
    layout: 'column',
    url : RM.Common.AssembleURL({
        controller: 'Taxes',
        action: 'listjson'
    }),
    frame : false,
    baseCls: "x-panel-header",
    items : [
        RM.Pages.Taxes_Edit_Title,
        RM.Pages.Taxes_Edit_Language
    ]
});

RM.Pages.Taxes_Edit = new Ext.Panel({
    id : 'rm_pages_taxes_edit',    
    items : [
        RM.Pages.Taxes_Edit_Language_Form,
        RM.Pages.Taxes_Edit_Selection_Grid
    ]
});

RM.Main.Pages.push(RM.Pages.Taxes_Edit);