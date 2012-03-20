/**
 * ResMania - Reservation System Framework http://resmania.com
 * Copyright (C) 2011  ResMania Ltd.
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.

 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 *
 *
 *
 * Reservations List JS
 * This creates the admin GUI Reservations list
 *
 * JSLint.com Check: 18/03/2011
 *
 * @access       public
 * @author       Rob/Valentin
 * @copyright    2011 ResMania Ltd.
 * @version      1.2
 * @link         http://docs.resmania.com/api/
 * @since        06-2011
 */
RM.Pages.Functions.reservations_list_new = function(){
    RM.Pages.Functions.Reservations_New();
};

RM.Pages.Functions.reservations_list_new_wizard = function(){
    RM.Pages.Functions.Reservations_NewWizardJson_Request();
};

RM.Pages.Functions.reservations_list_block_wizard = function(){
    RM.Pages.Functions.Reservations_Block();
    //RM.Pages.Functions.Reservations_BlockWizardJson_Request();
};

// update the reservation is_read flag...
RM.Pages.Functions.reservations_list_ReadMarker_Request = function(state){
    
    if (RM.Pages.Reservations_List_Columns_SM.getSelections().length === 0){
        Ext.Msg.alert(RM.Translate.Common.Status, RM.Translate.Common.NeedToSelectRows);
        return;
    }

    var myMask = new Ext.LoadMask('rm_pages_reservations_list', {msg:RM.Translate.Common.PleaseWait});
    myMask.show();

    var selections = RM.Pages.Reservations_List_Columns_SM.getSelections();

    var parametersJson = [{
        name: 'state',
        value: state
    }];

    var i = 0;
    for (i; i < selections.length; i++){
        parametersJson[i+1] = {
            name : 'ids[]',
            value : selections[i].data.reservation_id
        };
    }
    
    var conn = new Ext.data.Connection();
    var request = {
        url: RM.Common.AssembleURL({
            controller : 'Reservations',
            action: 'readmarkerJson',
            parameters : parametersJson
        }),
        method: 'POST',
        success: function(responseObject) {
            RM.Pages.Reservations_List_Json_Store.reload();
            RM.Pages.Reservations_List_Columns_SM.clearSelections();
            myMask.hide();
        }
    };
    conn.request(request);
};

RM.Pages.Functions.reservations_list_edit = function(){
    if (RM.Pages.Reservations_List_Columns_SM.getSelections().length === 0) {
        Ext.Msg.alert(RM.Translate.Common.Error, RM.Translate.Common.NeedToSelectRows);
        return;
    }
    var selected = RM.Pages.Reservations_List_Columns_SM.getSelected();
    RM.Pages.Functions.Reservations_EditJson_Request(selected.data.reservation_id);
};
RM.Pages.Functions.reservations_list_delete = function(){    
    if (RM.Pages.Reservations_List_Columns_SM.getSelections().length === 0){
        Ext.Msg.alert(RM.Translate.Common.Status, RM.Translate.Common.NeedToSelectRows);
        return;
    }

    Ext.MessageBox.confirm(RM.Translate.Common.Delete, RM.Translate.Admin.Reservations.List.DeleteAlert, function(buttonID){
               
        if (buttonID !== 'yes') {
            return;
        }

        var selections = RM.Pages.Reservations_List_Columns_SM.getSelections();

        var parametersJson = [];
        var i = 0;
        for (i; i < selections.length; i++){
            parametersJson[i] = {
                name : 'ids[]',
                value : selections[i].data.reservation_id
            };
        }

        var myMask = new Ext.LoadMask('rm_pages_reservations_list', {msg:RM.Translate.Common.PleaseWait});
        myMask.show();
        var conn = new Ext.data.Connection();
        var request = {
            url: RM.Common.AssembleURL({
                controller : 'Reservations',
                action: 'deletejson',
                parameters : parametersJson
            }),
            method: 'POST',
            success: function(responseObject) {
                myMask.hide();
                RM.Pages.Reservations_List_Json_Store.reload();
            },
            failure: function() {
                myMask.hide();
                Ext.MessageBox.alert(RM.Translate.Common.DeleteFailure);
            }
        };
        conn.request(request);
    });
};

RM.Pages.Functions.reservations_list_mark_paid = function(){
    if (RM.Pages.Reservations_List_Columns_SM.getSelections().length === 0) {
        Ext.Msg.alert(RM.Translate.Common.Error, RM.Translate.Common.NeedToSelectRows);
        return;
    }

    Ext.MessageBox.confirm(RM.Translate.Common.Delete, RM.Translate.Admin.Reservations.List.MarkPaidAlert, function(buttonID){
        if (buttonID !== 'yes') {
            return;
        }
        var selections = RM.Pages.Reservations_List_Columns_SM.getSelections();
        var parametersJson = [];
        var i = 0;
        for (i; i < selections.length; i++){
            parametersJson[i] = {
                name : 'ids[]',
                value : selections[i].data.reservation_id
            };
        }
        var myMask = new Ext.LoadMask('rm_pages_reservations_list', {msg:RM.Translate.Common.PleaseWait});
        myMask.show();
        Ext.Ajax.request({
            url: RM.Common.AssembleURL({
                controller : 'Reservations',
                action: 'markpaidjson',
                parameters : parametersJson
            }),
            method: 'POST',
            success: function(responseObject) {
                myMask.hide();
                RM.Pages.Reservations_List_Json_Store.reload();
            },
            failure: function() {
                myMask.hide();
                Ext.MessageBox.alert(RM.Translate.Common.Failed);
            }
        });
    });
};

RM.Pages.Functions.reservations_list_confirm = function(){
    if (RM.Pages.Reservations_List_Columns_SM.getSelections().length === 0) {
        Ext.Msg.alert(RM.Translate.Common.Error, RM.Translate.Common.NeedToSelectRows);
        return;
    }

    var selections = RM.Pages.Reservations_List_Columns_SM.getSelections();
    var parametersJson = [];
    var i = 0;
    for (i; i < selections.length; i++){
        parametersJson[i] = {
            name : 'ids[]',
            value : selections[i].data.reservation_id
        };
    }

    var myMask = new Ext.LoadMask('rm_pages_reservations_list', {msg:RM.Translate.Common.PleaseWait});
    myMask.show();
    var conn = new Ext.data.Connection();
    var request = {
        url: RM.Common.AssembleURL({
            controller : 'Reservations',
            action: 'confirmjson',
            parameters : parametersJson
        }),
        method: 'POST',
        success: function(responseObject) {
            myMask.hide();
            RM.Pages.Reservations_List_Json_Store.reload();
        },
        failure: function() {
            myMask.hide();
            Ext.MessageBox.alert(RM.Translate.Common.ConfirmFailure);
        }
    };
    conn.request(request);
};
RM.Pages.Functions.reservations_list_unconfirm = function(){
    if (RM.Pages.Reservations_List_Columns_SM.getSelections().length === 0) {
        Ext.Msg.alert(RM.Translate.Common.Error, RM.Translate.Common.NeedToSelectRows);
        return;
    }

    var selections = RM.Pages.Reservations_List_Columns_SM.getSelections();
    var parametersJson = [];
    var i = 0;
    for (i; i < selections.length; i++){
        parametersJson[i] = {
            name : 'ids[]',
            value : selections[i].data.reservation_id
        };
    }

    var myMask = new Ext.LoadMask('rm_pages_reservations_list', {msg:RM.Translate.Common.PleaseWait});
    myMask.show();
    var conn = new Ext.data.Connection();
    var request = {
        url: RM.Common.AssembleURL({
            controller : 'Reservations',
            action: 'unconfirmjson',
            parameters : parametersJson
        }),
        method: 'POST',
        success: function(responseObject) {
            myMask.hide();
            RM.Pages.Reservations_List_Json_Store.reload();
        },
        failure: function() {
            myMask.hide();
            Ext.MessageBox.alert(RM.Translate.Common.UnconfirmFailure);
        }
    };
    conn.request(request);
};

RM.Pages.Functions.Reservations_ListJson_Request = function(){
    RM.Pages.Functions.Reservations_ListJson({});    
};

RM.Pages.Reservations_List_Toolbar = {
    xtype : "panel",
    id : "rm_pages_reservations_list_toolbar",
    bodyBorder : false,
    html : RM.Common.getToolbar([
        {image: RM.BaseLargeImageURL+"new.gif", label: RM.Translate.Common.Planner, link: "RM.Pages.Functions.reservations_list_new()"},
        {image: RM.BaseLargeImageURL+"edit.gif", label: RM.Translate.Common.Edit, link: "RM.Pages.Functions.reservations_list_edit()"},
        {image: RM.BaseLargeImageURL+"delete.gif", label: RM.Translate.Common.Delete, link: "RM.Pages.Functions.reservations_list_delete()"},
        {image: RM.BaseLargeImageURL+"publish.gif", label: RM.Translate.Common.Confirm, link: "RM.Pages.Functions.reservations_list_confirm()"},
        {image: RM.BaseLargeImageURL+"unpublish.gif", label: RM.Translate.Common.Unconfirm, link: "RM.Pages.Functions.reservations_list_unconfirm()"},
        {image: RM.BaseLargeImageURL+"read.gif", label: RM.Translate.Admin.Reservations.List.MarkRead, link: "RM.Pages.Functions.reservations_list_ReadMarker_Request(1)"},
        {image: RM.BaseLargeImageURL+"unread.gif", label: RM.Translate.Admin.Reservations.List.MarkUnRead, link: "RM.Pages.Functions.reservations_list_ReadMarker_Request(0)"},
        {image: RM.BaseLargeImageURL+"apply.gif", label: RM.Translate.Admin.Reservations.List.MarkPaid, link: "RM.Pages.Functions.reservations_list_mark_paid()"}            
    ])
};
RM.Toolbars.push(RM.Pages.Reservations_List_Toolbar);

RM.Pages.Functions.Reservations_ListJson = function (responseObject) {    
    Ext.getCmp('toolbar_panel').layout.setActiveItem('rm_pages_reservations_list_toolbar');
    Ext.state.Manager.setProvider(new Ext.state.CookieProvider());
    Ext.getCmp('content-panel').layout.setActiveItem('rm_pages_reservations_list');    
    RM.Pages.Reservations_List_Json_Store.load();
    RM.Pages.Reservations_List_Columns_SM.clearSelections();
    RM.Help.Load('Admin.Reservations.List.Main');
    RM.Pages.Reservations_List_Grid.render();
};

RM.Pages.Reservations_List_Json_Store = new Ext.ux.grid.livegrid.Store({
    //autoLoad : true,
    url: RM.Common.AssembleURL({
        controller: 'Reservations',
        action: 'listjson'
    }),
    reader: new Ext.ux.grid.livegrid.JsonReader({
            totalProperty: 'total',
            root: 'data'
        },
        RM.Pages.Reservations_List_Json_Store_Fields
    ),
    sortInfo: {field: 'updatetime', direction: 'DESC'},
    id: 'rm_reservations_list_grid_json_store',
    bufferSize : 60
});

RM.Pages.Reservations_List_Columns_SM = new Ext.grid.CheckboxSelectionModel(); // for checkboxes
//RM.Pages.Reservations_List_Columns_SM = new Ext.ux.grid.livegrid.RowSelectionModel(); // for line selection
RM.Pages.Reservations_List_Columns_Rows[0] = RM.Pages.Reservations_List_Columns_SM;
RM.Pages.Reservations_List_Columns = new Ext.grid.ColumnModel({
    columns: RM.Pages.Reservations_List_Columns_Rows,
    defaults: {
        sortable: true
    }
});

RM.Pages.Reservations_List_Filters = new Ext.ux.grid.GridFilters({
    filters: RM.Pages.Reservations_List_Filters_Rows
});

RM.Pages.Reservations_List_Grid_View = new Ext.ux.grid.livegrid.GridView({
    nearLimit : parseInt(RM.Common.reservationsListBufferSize,10),
    loadMask  : {
        msg :  'Buffering. Please wait...'
    }
});

RM.Pages.Reservations_List_Grid = new Ext.ux.grid.livegrid.GridPanel({
    id : 'rm_reservations_list_grid',
    plugins: RM.Pages.Reservations_List_Filters,
    bbar     : new Ext.ux.grid.livegrid.Toolbar({
            view        : RM.Pages.Reservations_List_Grid_View,
            displayInfo : true
        }),
    enableColLock : false,
    loadMask : {
        msg: RM.Translate.Common.PleaseWait
    },
    height: RM.Common.GetPanelHeight(108),
    cm : RM.Pages.Reservations_List_Columns,
    store : RM.Pages.Reservations_List_Json_Store,
    selModel : RM.Pages.Reservations_List_Columns_SM,
    view     : RM.Pages.Reservations_List_Grid_View,
    viewConfig: {
        forceFit: true
    },
    autoExpandColumn: 'name'
});

RM.Pages.Reservations_List_Grid.getView().getRowClass = function(record, index){
        if (record.data.is_read==="0"){
            return 'RM_reservation_list_unread';
        }  else {
            return '';
        }
};

RM.Pages.Reservations_List = new Ext.Panel({
    id : 'rm_pages_reservations_list',
    title : RM.Translate.Common.List,
    iconCls: "RM_reservations_root_icon",
    items : [RM.Pages.Reservations_List_Grid]
});

RM.Main.Pages.push(RM.Pages.Reservations_List);