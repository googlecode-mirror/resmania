/*
* Daily Prices Unit Price List JS
*
* JSLint.com Check: 18/03/2011
*/
RM.Pages.Functions.unit_daily_prices_list_save = function(){
    var records = RM.Pages.Unit_DailyPrices_List_JsonStore.getModifiedRecords();
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
	if (o.min_stay) {
	   var index1 = 0;for (index1; index1 < RM.Common.DailyPrices_MinStay.length; index1++){
		if (RM.Common.DailyPrices_MinStay[index1][1] === o.min_stay) {
		    o.min_stay = RM.Common.DailyPrices_MinStay[index1][0];
		}
	    }
	}
	if (o.max_stay) {
	   var index2 = 0;for (index2; index2 < RM.Common.DailyPrices_MaxStay.length; index2++){
		if (RM.Common.DailyPrices_MaxStay[index2][1] === o.max_stay) {
		    o.max_stay = RM.Common.DailyPrices_MaxStay[index2][0];
		}
	    }
	}
        o.id = r.get('id');
        data.push(o);
    }, this);
    
    var o = {
         url:RM.Common.AssembleURL({
            controller : 'DailyPrices',
            action: 'updatejson'
         })
        ,method:'post'        
        ,scope:this
        ,params:{             
            data:Ext.encode(data),
            unit_id:RM.Pages.Units_Edit_UnitID
        }
        ,success: function(responseObject) {            
            myMask.hide();
            RM.Pages.Unit_DailyPrices_List_JsonStore.commitChanges();
            RM.Pages.Unit_DailyPrices_List_JsonStore.load();
            Ext.getCmp('rm_pages_reservations_edit_statusbar').setStatus({
                text: RM.Translate.Common.Saved,
                iconCls: 'ok-icon'
            });
        },
        failure: function() {
            Ext.Msg.alert(RM.Translate.Common.Status, RM.Translate.Common.UnableToShow);
            myMask.hide();
        }
    };
    Ext.Ajax.request(o);	  
};

RM.Pages.Unit_DailyPrices_List_Toolbar = {
    xtype : "panel",
    id : "rm_pages_unit_daily_prices_list_toolbar",
    bodyBorder : false,
    html : RM.Common.getToolbar([
        {image: RM.BaseLargeImageURL+"new.gif", label: RM.Translate.Admin.DailyPrices.UnitList.Add, link: "RM.Pages.Functions.unit_daily_prices_list_add()"},
        {image: RM.BaseLargeImageURL+"delete.gif", label: RM.Translate.Admin.DailyPrices.UnitList.Delete, link: "RM.Pages.Functions.unit_daily_prices_list_delete()"},
        {image: RM.BaseLargeImageURL+"save.gif", label: RM.Translate.Admin.DailyPrices.UnitList.Save, link: "RM.Pages.Functions.unit_daily_prices_list_save()"}
    ])
};
RM.Toolbars.push(RM.Pages.Unit_DailyPrices_List_Toolbar);

RM.Pages.Functions.unit_daily_prices_list_add = function(){
    var newRow = new RM.Pages.Unit_DailyPrices_List_Selection_Grid_Create_Row({
        id: 0
    });
    RM.Pages.Unit_DailyPrices_List_Selection_Grid.stopEditing();
    RM.Pages.Unit_DailyPrices_List_JsonStore.insert(0, newRow);
    RM.Pages.Unit_DailyPrices_List_Selection_Grid.startEditing(0, 0);
};

RM.Pages.Functions.unit_daily_prices_list_delete = function(){

    var selections = RM.Pages.Unit_DailyPrices_List_Columns_SM.getSelections();
    if (selections.length === 0){
        Ext.Msg.alert(RM.Translate.Common.Status, RM.Translate.Common.NeedToSelectRows);
        return;
    }

    Ext.MessageBox.confirm(RM.Translate.Common.Delete, RM.Translate.Admin.DailyPrices.UnitList.DeleteAlert, function(buttonID){
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

        var myMask = new Ext.LoadMask('content-panel', {msg:RM.Translate.Common.PleaseWait});
        myMask.show();
        var conn = new Ext.data.Connection();
        var request = {
            url: RM.Common.AssembleURL({
                controller : 'DailyPrices',
                action: 'deletejson',
                parameters : parametersJson
            }),
            method: 'POST',
            success: function(responseObject) {
                myMask.hide();
                RM.Pages.Functions.Unit_DailyPrices_ListJson_Request(RM.Pages.Units_Edit_UnitID);
            },
            failure: function() {
                Ext.MessageBox.alert(RM.Translate.Common.DeleteFailure);
            }
        };
        conn.request(request);
    });

};

RM.Pages.Functions.Unit_DailyPrices_ListJson_Request = function(unit_id){
    var myMask = new Ext.LoadMask('content-panel', {msg:RM.Translate.Common.PleaseWait});
    myMask.show();

    var conn = new Ext.data.Connection();
    var request = {
        url: RM.Common.AssembleURL({
            controller : 'DailyPrices',
            action: 'listjson',
            parameters : [{
                name : 'id',
                value : unit_id
            }]
        }),
        method: 'POST',
        success: function(responseObject) {
            eval('RM.Pages.Functions.Unit_DailyPrices_ListJson('+responseObject.responseText+');');
            myMask.hide();
        },
        failure: function() {
            Ext.Msg.alert(RM.Translate.Common.Status, RM.Translate.Common.UnableToShow);
            myMask.hide();
        }
    };
    conn.request(request);
};

RM.Pages.Functions.Unit_DailyPrices_ListJson = function(data) {
    //We need to remove old grid
    if (Ext.getCmp('rm_unit_daily_prices_list_grid')) {
        Ext.getCmp('rm_unit_daily_prices_list_grid').destroy();
    }

    var fields = [];
    var columns = [];
    RM.Pages.Unit_DailyPrices_List_Columns_SM = new Ext.grid.CheckboxSelectionModel();
    columns.push(RM.Pages.Unit_DailyPrices_List_Columns_SM);

    var i = 0;for (i; i < data.fields.length; i++){
        fields.push(data.fields[i].field);
        columns.push(data.fields[i].column);
    }

    // this is the unit selection store...
    RM.Pages.Unit_DailyPrices_List_JsonStore = new Ext.data.JsonStore({
        url : RM.Common.AssembleURL({
            controller: 'DailyPrices',
            action: 'listpricesJson'
        }),
        id: 'rm_unit_daily_prices_list_grid_json_store',
        root: 'data',
        fields: fields,
        sortInfo: {field: 'id', direction: 'ASC'},
        remoteSort: true
    });

    RM.Pages.Unit_DailyPrices_List_JsonStore.on("beforeload", function(gridLoader, node) {
        RM.Pages.Unit_DailyPrices_List_JsonStore.baseParams.id = RM.Pages.Units_Edit_UnitID;
    }, this);

    RM.Pages.Unit_DailyPrices_List_Columns = new Ext.grid.ColumnModel({
        columns: columns,
        defaults: {
            sortable: true
        }
    });    

    RM.Pages.Unit_DailyPrices_List_Selection_Grid_Create_Row = Ext.data.Record.create([
        {name: 'id', type: 'string'}
    ]);

    RM.Pages.Unit_DailyPrices_List_Selection_Grid = new Ext.grid.EditorGridPanel({
        id : 'rm_unit_daily_prices_list_grid',
        clicksToEdit: 1,
        stripeRows: true,
        height: RM.Common.GetPanelHeight(168),
        cm : RM.Pages.Unit_DailyPrices_List_Columns,
        sm : RM.Pages.Unit_DailyPrices_List_Columns_SM,
        store : RM.Pages.Unit_DailyPrices_List_JsonStore,
        bbar : new Ext.PagingToolbar({
            store: RM.Pages.Unit_DailyPrices_List_JsonStore,
            pageSize: 50
        }),
        viewConfig: {
            forceFit: true
        }
    });

    Ext.getCmp('rm_pages_unit_DailyPrices_list').add(RM.Pages.Unit_DailyPrices_List_Selection_Grid);
    Ext.getCmp('rm_pages_unit_DailyPrices_list').doLayout();
    RM.Pages.Unit_DailyPrices_List_JsonStore.load({params:{start: 0, limit: 15}});
};

RM.Pages.DailyPrices_UnitList = new Ext.Panel({
    id : 'rm_pages_unit_DailyPrices_list',
    title: RM.Translate.Admin.DailyPrices.UnitList.Tabtitle,
    items : [],    
    listeners: {
        'beforehide' : function(){
            Ext.getCmp('toolbar_panel').layout.setActiveItem('rm_pages_units_edit_toolbar');
            return true;
        },
        'beforeshow' : function(){
            Ext.getCmp('toolbar_panel').layout.setActiveItem('rm_pages_unit_daily_prices_list_toolbar');
            var unit_id = RM.Pages.Units_Edit_UnitID;            
            RM.Pages.Functions.Unit_DailyPrices_ListJson_Request(unit_id);
            return true;
        }
    }
});

RM.Pages.Units_Edit_TabPanel.add(RM.Pages.DailyPrices_UnitList);