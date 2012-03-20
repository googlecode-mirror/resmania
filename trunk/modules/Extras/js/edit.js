/*
* Extras Module Edit JS
* This creates the admin GUI Notifications List Page
*
* JSLint.com Check: 18/03/2011
*/
RM.Pages.Functions.Extras_Edit_Change_Language = function(combo, record, index){
    RM.Pages.Extras_Edit_Json_Store.load({params:{start: 0, limit: 15, iso: record.data.field1}});    
};

RM.Pages.Extras_Edit_Grid_Create_Row = Ext.data.Record.create([
    {name: 'id', type: 'string'}
]);

RM.Pages.Functions.extras_edit_save = function(){    
    var records = RM.Pages.Extras_Edit_Json_Store.getModifiedRecords();
    if(!records.length) {        
        return;
    }

    var myMask = new Ext.LoadMask('content-panel', {msg:RM.Translate.Common.PleaseWait});
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
            controller : 'Extras',
            action: 'updatejson'
         })
        ,method:'post'
        ,scope:this
        ,params:{
            data: Ext.encode(data),
            iso: Ext.getCmp('edit_extras_iso').value
        }
        ,success: function(responseObject) {
            myMask.hide();
            RM.Pages.Extras_Edit_Json_Store.load();            
        },
        failure: function() {           
            myMask.hide();
        }
    };
    Ext.Ajax.request(o);	  
};

RM.Pages.Functions.extras_edit_add = function(){
    var newRow = new RM.Pages.Extras_Edit_Grid_Create_Row({
        id: 0
    });
    RM.Pages.Extras_Edit_Grid.stopEditing();
    RM.Pages.Extras_Edit_Json_Store.insert(0, newRow);
    RM.Pages.Extras_Edit_Grid.startEditing(0, 0);
};

RM.Pages.Functions.extras_edit_delete = function(){    
    if (RM.Pages.Extras_Edit_Columns_SM.getSelections().length === 0) {
        Ext.MessageBox.alert(RM.Translate.Common.Error, RM.Translate.Common.NeedToSelectRows);
        return;
    }
    Ext.MessageBox.confirm(RM.Translate.Common.Delete, RM.Translate.Admin.Extras.Edit.DeleteAlert, function(buttonID){
        if (buttonID !== 'yes') {
            return;
        }

        var selections = RM.Pages.Extras_Edit_Columns_SM.getSelections();
        var parametersJson = [];
        var i = 0;for (i; i < selections.length; i++){
            parametersJson[i] = {
                name : 'ids[]',
                value : selections[i].data.id
            };
        }

        var myMask = new Ext.LoadMask('rm_pages_extras_edit', {msg:RM.Translate.Common.PleaseWait});
        myMask.show();
        var conn = new Ext.data.Connection();
        var request = {
            url: RM.Common.AssembleURL({
                controller : 'Extras',
                action: 'deletejson',
                parameters : parametersJson
            }),
            method: 'POST',
            success: function(responseObject) {
                RM.Pages.Extras_Edit_Json_Store.load();
                myMask.hide();
            },
            failure: function() {
                Ext.MessageBox.alert(RM.Translate.Common.UnconfirmFailure);
                myMask.hide();
            }
        };
        conn.request(request);
    });
};

RM.Pages.Extras_Edit_Toolbar = {
    xtype : "panel",
    id : "rm_pages_extras_edit_toolbar",
    bodyBorder : false,
    html : RM.Common.getToolbar([
        {image: RM.BaseLargeImageURL+"new.gif", label: RM.Translate.Common.Add, link: "RM.Pages.Functions.extras_edit_add()"},
        {image: RM.BaseLargeImageURL+"save.gif", label: RM.Translate.Common.Save, link: "RM.Pages.Functions.extras_edit_save()"},
        {image: RM.BaseLargeImageURL+"delete.gif", label: RM.Translate.Common.Delete, link: "RM.Pages.Functions.extras_edit_delete()"}
    ])
};
RM.Toolbars.push(RM.Pages.Extras_Edit_Toolbar);

RM.Pages.Functions.Extras_EditJson = function () {
    Ext.getCmp('toolbar_panel').layout.setActiveItem('rm_pages_extras_edit_toolbar');
    Ext.getCmp('content-panel').layout.setActiveItem('rm_pages_extras_edit');

    Ext.getCmp('edit_extras_iso').setValue(RM.Language);
    RM.Pages.Extras_Edit_Json_Store.load({params:{start: 0, limit: 15}});           
};

RM.Pages.Extras_Edit_Json_Store = new Ext.data.JsonStore({
    url: RM.Common.AssembleURL({
        controller: 'Extras',
        action: 'listjson'
    }),
    id: 'rm_extras_edit_grid_json_store',
    totalProperty: 'total',
    root: 'data',
    pruneModifiedRecords: true,
    fields: [
        {name: "id"},
        {name: "name"},
        {name: "type" },
        {name: "units"},
        {name: "min" },
        {name: "max" },
        {name: "value"},
        {name: "rule"},
        {name: "enabled"}
    ],
    sortInfo: {field: 'id', direction: 'ASC'},
    remoteSort: true,
    listeners: {
        "beforeload": function(gridLoader, node) {            
            RM.Pages.Extras_Edit_Json_Store.baseParams.iso = Ext.getCmp('edit_extras_iso').value;
        }
    }
});


RM.Pages.Extras_Edit_Columns_Types = [
    ['day', RM.Translate.Admin.Extras.Type.Day],
    ['percentage', RM.Translate.Admin.Extras.Type.Percentage],
    ['single', RM.Translate.Admin.Extras.Type.Single]
];
RM.Pages.Extras_Edit_Columns_Rules = [
    ['RoundUp', RM.Translate.Admin.Extras.Rules.RoundUp],
    ['RoundDown', RM.Translate.Admin.Extras.Rules.RoundDown],
    ['Hourly', RM.Translate.Admin.Extras.Rules.Hourly]    
];
RM.Pages.Extras_Edit_Columns_SM = new Ext.grid.CheckboxSelectionModel();
RM.Pages.Extras_Edit_Columns_Rows = [
    RM.Pages.Extras_Edit_Columns_SM,
    {dataIndex: 'id', header: RM.Translate.Common.Id},
    {
        id: 'name',
        dataIndex: 'name',
        header: RM.Translate.Admin.Extras.Edit.Name,
        sortable: false,
        editor: new Ext.form.TextField({
            allowBlank: false
        })
    },    
    {
        dataIndex: "type",
        header: RM.Translate.Admin.Extras.Edit.Type,
        renderer: function(value, metaData, record, rowIndex, colIndex, store) {
            var i = 0;for (i; i < RM.Pages.Extras_Edit_Columns_Types.length; i++){
                if (RM.Pages.Extras_Edit_Columns_Types[i][0] === value) {return RM.Pages.Extras_Edit_Columns_Types[i][1];}
            }
            return value;
        },
        editor: new Ext.form.ComboBox({
            mode: 'local',
            displayField: "min_stay",
            triggerAction: 'all',
            typeAhead: true,
            resizable: true,
            minListWidth: 220,
            store: RM.Pages.Extras_Edit_Columns_Types
        })
    },
    {
        dataIndex: "units",
        header: RM.Translate.Admin.Extras.Edit.SelectedUnits,
        editor: new Ext.ux.form.CheckboxCombo({
            width: 250,
            mode: 'local',
            store: new Ext.data.JsonStore({
                url: RM.Common.AssembleURL({
                    controller: 'Extras',
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
    },
    {
        dataIndex: 'value',
        header: RM.Translate.Admin.Extras.Edit.Value,
        editor: new Ext.form.NumberField({
            allowBlank: false,
            allowNegative: false
        })
    },
    {
        dataIndex: 'min',
        header: RM.Translate.Admin.Extras.Edit.Min,
        editor: new Ext.form.NumberField({
            allowBlank: false
        })
    },
    {
        dataIndex: 'max',
        header: RM.Translate.Admin.Extras.Edit.Max,
        editor: new Ext.form.NumberField({
            allowBlank: false
        })
    },{
        dataIndex: "rule",
        header: RM.Translate.Admin.Extras.Edit.Rule,
        renderer: function(value, metaData, record, rowIndex, colIndex, store) {
            var i = 0;for (i; i < RM.Pages.Extras_Edit_Columns_Rules.length; i++){
                if (RM.Pages.Extras_Edit_Columns_Rules[i][0] === value) {return RM.Pages.Extras_Edit_Columns_Rules[i][1];}
            }
            return value;
        },
        width: 250,
        editor: new Ext.form.ComboBox({
            mode: 'local',
            displayField: "rule",
            triggerAction: 'all',
            typeAhead: true,
            resizable: true,
            minListWidth: 300,
            width: 250,
            store: RM.Pages.Extras_Edit_Columns_Rules
        })
    },{
        dataIndex: "enabled",
        header: RM.Translate.Admin.Extras.Edit.Enabled,
        editor: new Ext.form.ComboBox({
            mode: 'local',
            displayField: "enabled",
            triggerAction: 'all',
            typeAhead: true,
            resizable: true,
            minListWidth: 120,
            store: [
                ['0', RM.Translate.Common.MessageNo],
                ['1', RM.Translate.Common.MessageYes]
            ]
        }),
        renderer: function(value, metaData, record, rowIndex, colIndex, store){
            if (value=="1") {
                metaData.css = 'RM_list_enabled_icon';
            } else {
                metaData.css = 'RM_list_disabled_icon';
            }
        }
    }
];
RM.Pages.Extras_Edit_Columns = new Ext.grid.ColumnModel({
    columns: RM.Pages.Extras_Edit_Columns_Rows,
    defaults: {
        sortable: true
    }
});
RM.Pages.Extras_Edit_Columns.defaultSortable = true;

//RM.Pages.Extras_Edit_Filters = new Ext.grid.GridFilters({
//    filters: [
//        {dataIndex: 'id', type: 'numeric'},
//        {dataIndex: 'name', type: 'string'},
//        {dataIndex: 'type', type: 'list', options: ['day', 'single', 'percentage']},
//        {dataIndex: 'value', type: 'numeric'}
//    ]
//})

RM.Pages.Extras_Edit_Grid = new Ext.grid.EditorGridPanel({
    id : 'rm_extras_edit_grid',
    clicksToEdit: 1,
    stripeRows: true,
    height: RM.Common.GetPanelHeight(142),
    //plugins: RM.Pages.Extras_Edit_Filters,
    bbar : new Ext.PagingToolbar({
        store: RM.Pages.Extras_Edit_Json_Store,
        pageSize: 15
        //,plugins: RM.Pages.Extras_Edit_Filters
    }),
    remoteSort: true,
    enableColLock : false,
    loadMask : {
        msg: RM.Translate.Common.PleaseWait
    },
    cm : RM.Pages.Extras_Edit_Columns,
    store : RM.Pages.Extras_Edit_Json_Store,
    sm : RM.Pages.Extras_Edit_Columns_SM,
    viewConfig: {
        forceFit: true
    },
    autoExpandColumn: "name"
});


RM.Pages.Extras_Edit_Language_Combo = new Ext.form.ComboBox({
    id : "edit_extras_iso",
    hiddenName : "edit_extras[iso]",
    xtype : "combo",
    //cls: 'RM_language_selection_combo',
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
        'select': RM.Pages.Functions.Extras_Edit_Change_Language
    }
});

RM.Pages.Extras_Edit_Language = new Ext.Panel({
    id : "rm_pages_extras_edit_language",
    layout: 'form',
    frame : false,
    bodyBorder : false,
    items : [
        RM.Pages.Extras_Edit_Language_Combo
    ]
});

RM.Pages.Extras_Edit_Title = {
    xtype : "panel",
    id : "rm_pages_extras_edit_title",
    bodyBorder : false,
    frame : false,
    html : "<span class='RM_Title_Language_Bar_img'><img src='"+RM.BaseMenuImageURL+"package.png'></span>&nbsp;<span class='RM_Title_Language_Bar_text'>"+RM.Translate.Admin.Extras.Main.name+"</span>"
};

RM.Pages.Extras_Edit_Language_Form = new Ext.FormPanel({
    id : "rm_pages_extras_edit_language_selection",
    xtype : "form",
    layout: 'column',
    url : RM.Common.AssembleURL({
        controller: 'Extras',
        action: 'listjson'
    }),
    frame : false,
    baseCls: "x-panel-header",
    items : [
        RM.Pages.Extras_Edit_Title,
        RM.Pages.Extras_Edit_Language
    ]
});

RM.Pages.Extras_Edit = new Ext.Panel({
    id : 'rm_pages_extras_edit',
    layout : 'form',    
    iconCls: "RM_config_extras_icon",
    autoScroll: true,
    items : [
        RM.Pages.Extras_Edit_Language_Form,
        RM.Pages.Extras_Edit_Grid
    ]
});

RM.Main.Pages.push(RM.Pages.Extras_Edit);