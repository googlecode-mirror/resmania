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
 * Unit List JS
 * This creates the admin GUI unit list
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

RM.Pages.Units_List_Toolbar = {
    xtype : "panel",
    id : "rm_pages_units_list_toolbar",
    bodyBorder : false,
    html : RM.Common.getToolbar([
        {image: RM.BaseLargeImageURL+"new.gif", label: RM.Translate.Common.New, link: "RM.Pages.Functions.Units_List_New()"},
        {image: RM.BaseLargeImageURL+"edit.gif", label: RM.Translate.Common.Edit, link: "RM.Pages.Functions.Units_Edit_Unit()"},
        {image: RM.BaseLargeImageURL+"copy.gif", label: RM.Translate.Common.Copy, link: "RM.Pages.Functions.Units_List_Copy()"},
        {image: RM.BaseLargeImageURL+"delete.gif", label: RM.Translate.Common.Delete, link: "RM.Pages.Functions.Units_List_Delete()"}
    ])
};
RM.Toolbars.push(RM.Pages.Units_List_Toolbar);

RM.Pages.Functions.Units_List_New = function(){

     var request = {
        url: RM.Common.AssembleURL({
            controller : 'Units',
            action: 'newJson'
        }),
        method: 'POST',
        success: function(responseObject) {
            eval('RM.Pages.Functions.Units_newJson('+responseObject.responseText+');');
        },
        failure: function() {
            Ext.Msg.alert('Status', RM.Translate.Common.UnableToShow);
        }
    };

    var conn = new Ext.data.Connection();
    conn.request(request);
};

RM.Pages.Functions.Units_Edit_Unit_Request = function(unit_id){
    var myMask = new Ext.LoadMask('content-panel', {msg:RM.Translate.Common.PleaseWait});
    myMask.show();

    var conn = new Ext.data.Connection();
    var request = {
        url: RM.Common.AssembleURL({
            controller : 'Units',
            action: 'editjson',
            parameters : [{
                name : 'id',
                value : unit_id
            }]
        }),
        method: 'POST',
        success: function(responseObject) {
            eval('RM.Pages.Functions.Units_EditJson('+responseObject.responseText+');');
            myMask.hide();
        },
        failure: function() {
            Ext.Msg.alert(RM.Translate.Common.Status, RM.Translate.Common.UnableToShow);
            myMask.hide();
        }
    };
    conn.request(request);
};

RM.Pages.Functions.Units_Edit_Unit = function(){   
    var selections = RM.Pages.Units_List_Columns_SM.getSelections();
    if (selections.length === 0){
        Ext.Msg.alert(RM.Translate.Common.Status, RM.Translate.Common.NeedToSelectRows);
        return;
    }

    RM.Pages.Functions.Units_Edit_Unit_Request(selections[0].data.id);
};

RM.Pages.Functions.Units_List_Copy = function(){
    var selections = RM.Pages.Units_List_Columns_SM.getSelections();
    if (selections.length === 0){
        Ext.Msg.alert(RM.Translate.Common.Status, RM.Translate.Common.NeedToSelectRows);
        return;
    }
    Ext.MessageBox.confirm(RM.Translate.Common.Copy, RM.Translate.Admin.Units.List.CopyAlert, function(buttonID){
        if (buttonID === 'yes') {
            RM.Pages.Functions.Units_List_Copy_Request(selections);
        }
    });
};
RM.Pages.Functions.Units_List_Copy_Request = function(selections){
    var parametersJson = [];
    var i = 0;for (i; i < selections.length; i++){
        parametersJson[i] = {
            name : 'ids[]',
            value : selections[i].data.id
        };
    }
    var myMask = new Ext.LoadMask('rm_pages_units_list', {msg:RM.Translate.Common.PleaseWait});
    myMask.show();
    Ext.Ajax.request({
        url: RM.Common.AssembleURL({
            controller : 'Units',
            action: 'copyjson',
            parameters : parametersJson
        }),
        method: 'POST',
        success: function(responseObject) {
            myMask.hide();
            var responseStatus = Ext.util.JSON.decode(responseObject.responseText);
            if (responseStatus.success) {
                RM.Pages.Units_List_Json_Store.reload();
                Ext.getCmp('rm_main_tree_menu').root.reload();
                Ext.MessageBox.alert(RM.Translate.Common.Success, responseStatus.message);
            } else {
                Ext.MessageBox.alert(RM.Translate.Common.Failed, responseStatus.message);
            }
        },
        failure: function() {
            myMask.hide();
            Ext.MessageBox.alert(RM.Translate.Common.AjaxFailure);
        }
    });
};

RM.Pages.Functions.Units_List_Delete = function(){
    
    var selections = RM.Pages.Units_List_Columns_SM.getSelections();
    if (selections.length === 0){
        Ext.Msg.alert(RM.Translate.Common.Status, RM.Translate.Common.NeedToSelectRows);
        return;
    }

    Ext.MessageBox.confirm(RM.Translate.Common.Delete, RM.Translate.Admin.Units.List.DeleteAlert, function(buttonID){
        if (buttonID !== 'yes') {
            return;
        }

        var myMask = new Ext.LoadMask('rm_pages_units_list', {msg:RM.Translate.Common.PleaseWait});
        myMask.show();

        var parametersJson = [];
        var i = 0;for (i; i < selections.length; i++){
            parametersJson[i] = {
                name : 'ids[]',
                value : selections[i].data.id
            };
        }
        var request = {
            url: RM.Common.AssembleURL({
                controller : 'Units',
                action: 'deletejson',
                parameters : parametersJson
            }),
            method: 'POST',
            success: function(responseObject) {                
                myMask.hide();
                RM.Pages.Units_List_Json_Store.reload();
                Ext.getCmp('rm_main_tree_menu').root.reload();
                return;
            },
            failure: function() {
                myMask.hide();
                Ext.MessageBox.alert(RM.Translate.Common.DeleteFailure);
            }
        };

        var conn = new Ext.data.Connection();
        conn.request(request);
    });
};

RM.Pages.Functions.Units_ListJson = function () {
    RM.Help.Load('Admin.Units.List.Main'); // load the help

    Ext.getCmp('toolbar_panel').layout.setActiveItem('rm_pages_units_list_toolbar');
    Ext.getCmp('content-panel').layout.setActiveItem('rm_pages_units_list');

    Ext.ux.menu.RangeMenu.prototype.icons = {
        gt: 'img/greater_then.png',
        lt: 'img/less_then.png',
        eq: 'img/equals.png'
    };
    Ext.ux.grid.filter.StringFilter.prototype.icon = 'img/find.png';

    Ext.state.Manager.setProvider(new Ext.state.CookieProvider());

    RM.Pages.Units_List.render();
    RM.Pages.Units_List_Json_Store.load({params:{start: 0, limit: 30}});    
};

    RM.Pages.Units_List_Filters = new Ext.ux.grid.GridFilters({
        id : 'rm_pages_units_list_filters',
        filters: RM.Pages.Units_List_Filters_Rows
    });

    RM.Pages.Units_List_Json_Store = new Ext.ux.grid.livegrid.Store({
        //autoLoad : true,
        url: RM.Common.AssembleURL({
            controller: 'Units',
            action: 'listJson'
        }),
        reader: new Ext.ux.grid.livegrid.JsonReader({
                totalProperty: 'total',
                root: 'data'
            },
            RM.Pages.Units_List_Json_Store_Fields
        ),
        sortInfo: {field: 'id', direction: 'ASC'},
        id: 'rm_units_list_grid_json_store',
        bufferSize : 300
    });

    RM.Pages.Units_List_Columns_SM = new Ext.grid.CheckboxSelectionModel(); // for checkboxes
    RM.Pages.Units_List_Columns_Rows[0] = RM.Pages.Units_List_Columns_SM;
    RM.Pages.Units_List_Columns = new Ext.grid.ColumnModel({
        columns: RM.Pages.Units_List_Columns_Rows,
        defaults: {
            sortable: true
        }
    });

    RM.Pages.Units_List_Filters = new Ext.ux.grid.GridFilters({
        filters: RM.Pages.Units_List_Filters_Rows
    });

    RM.Pages.Units_List_Grid_View = new Ext.ux.grid.livegrid.GridView({
        nearLimit : 100,
        loadMask  : {
            msg :  'Buffering. Please wait...'
        }
    });

    RM.Pages.Units_List_Grid = new Ext.ux.grid.livegrid.GridPanel({
        id : 'rm_pages_units_list_grid',
        plugins: RM.Pages.Units_List_Filters,
        bbar     : new Ext.ux.grid.livegrid.Toolbar({
                view        : RM.Pages.Units_List_Grid_View,
                displayInfo : true
            }),
        enableColLock : false,
        loadMask : {
            msg: RM.Translate.Common.PleaseWait
        },
        height: RM.Common.GetPanelHeight(107),
        cm : RM.Pages.Units_List_Columns,
        store : RM.Pages.Units_List_Json_Store,
        selModel : RM.Pages.Units_List_Columns_SM,
        view     : RM.Pages.Units_List_Grid_View,
        viewConfig: {
            forceFit: true
        },
        autoExpandColumn: 'name'
    });



RM.Pages.Units_List = new Ext.Panel({
  id : 'rm_pages_units_list',
  title : RM.Translate.Common.List,
  iconCls: "RM_units_default_root_icon",
  items : [RM.Pages.Units_List_Grid]
});

RM.Main.Pages.push(RM.Pages.Units_List);