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
 * User List JS
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

// New User handler...
RM.Pages.Functions.Users_New = function(){
     var request = {
        url: RM.Common.AssembleURL({
            controller : 'Users',
            action: 'newJson'
        }),
        method: 'POST',
        success: function(responseObject) {
            eval('RM.Pages.Functions.Users_newJson('+responseObject.responseText+', false);');
        },
        failure: function() {
            Ext.Msg.alert('Status', RM.Translate.Common.UnableToShow);
        }
    };

    var conn = new Ext.data.Connection();
    conn.request(request);
};

// Edit User Handler...

RM.Pages.Functions.Users_Edit = function(){
    var selections = RM.Pages.Users_List_Columns_SM.getSelections();
    if (selections.length === 0){
        Ext.Msg.alert(RM.Translate.Common.Status, RM.Translate.Common.PleaseMakeSelection);
        return;
    }
    var selected = RM.Pages.Users_List_Columns_SM.getSelected();
    RM.Pages.Functions.Users_EditJson_Request(selected.data.id);
};

RM.Pages.Functions.Users_List_Sync = function(){
    Ext.MessageBox.confirm(RM.Translate.Common.Sync, RM.Translate.Admin.Users.List.SyncAlert, function(buttonID){
        if (buttonID === 'yes') {
            RM.Pages.Functions.Users_List_Sync_Request();
        }
    });
};
RM.Pages.Functions.Users_List_Sync_Request = function(){
    var myMask = new Ext.LoadMask('rm_pages_users_list', {msg:RM.Translate.Common.PleaseWait});
    myMask.show();
    Ext.Ajax.request({
        url: RM.Common.AssembleURL({
            controller : 'Users',
            action: 'syncjson'
        }),
        success: function(responseObject) {
            myMask.hide();
            eval('var returnObject = ' + responseObject.responseText);
            if (returnObject.success) {
                Ext.Msg.alert(RM.Translate.Common.Success, returnObject.message);
                RM.Pages.Users_List_Grid_Json_Store.reload();
            } else {
                Ext.Msg.alert(RM.Translate.Common.Error, returnObject.message);
            }
        },
        failure: function() {
            myMask.hide();
            Ext.MessageBox.alert(RM.Translate.Common.UnableToShow);
        }
    });
};

// Delete User Handler...
RM.Pages.Functions.Users_List_Delete = function(){
    var selections = RM.Pages.Users_List_Columns_SM.getSelections();
    if (selections.length === 0){
        Ext.Msg.alert(RM.Translate.Common.Status, RM.Translate.Common.NeedToSelectRows);
        return;
    }

    Ext.MessageBox.confirm(RM.Translate.Common.Delete, RM.Translate.Admin.Users.List.DeleteAlert, function(buttonID){
        if (buttonID !== 'yes') {
            return;
        }

        var parametersJson = [];
        var i = 0;for (i; i < selections.length; i++){
            parametersJson[i] = {
                name : 'ids[]',
                value : selections[i].data.id
            };
        }

        var myMask = new Ext.LoadMask('rm_pages_users_list', {msg:RM.Translate.Common.PleaseWait});
        myMask.show();
        var conn = new Ext.data.Connection();
        var request = {
            url: RM.Common.AssembleURL({
                controller : 'Users',
                action: 'deletejson',
                parameters : parametersJson
            }),
            method: 'POST',
            success: function(responseObject) {
                eval('var returnObject = ' + responseObject.responseText);
                if (returnObject.success === false) {
                    Ext.Msg.alert(RM.Translate.Common.Error, returnObject.error);
                }
                myMask.hide();
                RM.Pages.Users_List_Grid_Json_Store.reload();
            },
            failure: function() {
                myMask.hide();
                Ext.MessageBox.alert(RM.Translate.Common.DeleteFailure);
            }
        };
        conn.request(request);
    });
};

RM.Pages.Customer_List_Toolbar_Items = [
    {image: RM.BaseLargeImageURL+"new.gif", label: RM.Translate.Common.New, link: "RM.Pages.Functions.Users_New()"},
    {image: RM.BaseLargeImageURL+"edit.gif", label: RM.Translate.Common.Edit, link: "RM.Pages.Functions.Users_Edit()"},
    {image: RM.BaseLargeImageURL+"delete.gif", label: RM.Translate.Common.Delete, link: "RM.Pages.Functions.Users_List_Delete()"}
];
if (RM.CMSIntegration) {
    RM.Pages.Customer_List_Toolbar_Items.push({
        image: RM.BaseLargeImageURL+"copy.gif",
        label: RM.Translate.Common.Sync,
        link: "RM.Pages.Functions.Users_List_Sync()"
    });
}
RM.Pages.Customer_List_Toolbar = {
    xtype : "panel",
    id : "rm_pages_users_list_toolbar",
    bodyBorder : false,
    html : RM.Common.getToolbar(RM.Pages.Customer_List_Toolbar_Items)
};
RM.Toolbars.push(RM.Pages.Customer_List_Toolbar);


RM.Pages.Functions.Users_ListJson = function (responseObject) {

    RM.Help.Load('Admin.Users.List.Main');

    RM.Pages.Users_List.on('tabchange', function (page, currentTab){
        currentTab.doLayout();
    });

    Ext.getCmp('toolbar_panel').layout.setActiveItem('rm_pages_users_list_toolbar');

    Ext.state.Manager.setProvider(new Ext.state.CookieProvider());

    Ext.getCmp('content-panel').layout.setActiveItem('rm_pages_users_list');
    RM.Pages.Users_List_Grid_Json_Store.load({params:{start: 0, limit: 30}});
    RM.Pages.Users_List_Grid.render();
};

RM.Pages.Users_List_Grid_Json_Store = new Ext.ux.grid.livegrid.Store({
    //autoLoad : true,
    url: RM.Common.AssembleURL({
        controller: 'Users',
        action: 'listJson'
    }),
    reader: new Ext.ux.grid.livegrid.JsonReader({
            totalProperty: 'total',
            root: 'data'
        },
        RM.Pages.Users_List_Json_Store_Fields
    ),
    sortInfo: {field: 'id', direction: 'ASC'},
    id: 'rm_users_list_grid_json_store',
    bufferSize : 300
});

RM.Pages.Users_List_Columns_SM = new Ext.grid.CheckboxSelectionModel(); // for checkboxes
RM.Pages.Users_List_Columns_Rows[0] = RM.Pages.Units_List_Columns_SM;
RM.Pages.Users_List_Columns = new Ext.grid.ColumnModel({
    columns: RM.Pages.Users_List_Columns_Rows,
    defaults: {
        sortable: true
    }
});

RM.Pages.Users_List_Filters = new Ext.ux.grid.GridFilters({
    filters: RM.Pages.Users_List_Filters_Rows
});

RM.Pages.Users_List_Grid_View = new Ext.ux.grid.livegrid.GridView({
    nearLimit : 100,
    loadMask  : {
        msg :  'Buffering. Please wait...'
    }
});

RM.Pages.Users_List_Grid = new Ext.ux.grid.livegrid.GridPanel({
    id : 'rm_users_list_grid',
    plugins: RM.Pages.Users_List_Filters,
    bbar     : new Ext.ux.grid.livegrid.Toolbar({
            view        : RM.Pages.Users_List_Grid_View,
            displayInfo : true
        }),
    enableColLock : false,
    loadMask : {
        msg: RM.Translate.Common.PleaseWait
    },
    height: RM.Common.GetPanelHeight(108),
    cm : RM.Pages.Users_List_Columns,
    store : RM.Pages.Users_List_Grid_Json_Store,
    selModel : RM.Pages.Users_List_Columns_SM,
    view     : RM.Pages.Users_List_Grid_View,
    viewConfig: {
        forceFit: true
    },
    autoExpandColumn: 'email'
});

RM.Pages.Functions.Users_ListJson_Request = function(){
    RM.Pages.Functions.Users_ListJson({});
};

RM.Pages.Users_List = new Ext.Panel({
  id : 'rm_pages_users_list',
  title : RM.Translate.Common.List,
  items : RM.Pages.Users_List_Grid,
  iconCls: "RM_users_root_icon"
});

RM.Main.Pages.push(RM.Pages.Users_List);