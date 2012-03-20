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
 * This is a re-useable unit selector.
 *
 * JSLint.com Check: 18/03/2011
 *
 * @access       public
 * @author       Rob
 * @copyright    2011 ResMania Ltd.
 * @version      1.3
 * @link         http://docs.resmania.com/api/
 * @since        06-2011
 * @usage        call RM.Pages.Functions.Units_Selector_List_Grid for just the unit selection grid or RM.Pages.Functions.Units_Selector for the all unit selector and unit list grid
 */

RM.Pages.Functions.Units_Selector_List_Json_Store = new Ext.ux.grid.livegrid.Store({
    //autoLoad : true,
    url: RM.Common.AssembleURL({
        controller: 'Units',
        action: 'listJson'
    }),
    reader: new Ext.ux.grid.livegrid.JsonReader({
            totalProperty: 'total',
            root: 'data',
            idIndex: 0
        },[{name: "id"},{name: "name"}]
    ),
    id: 'rm_units_selector_list_grid_json_store',
    sortInfo: {field: 'id', direction: 'ASC'},
    bufferSize : 30
});

RM.Pages.Functions.Units_Selector_List_Columns_SM = new Ext.grid.CheckboxSelectionModel();

RM.Pages.Functions.Units_Selector_List_Columns_Rows = [
    RM.Pages.Functions.Units_Selector_List_Columns_SM,
    {dataIndex: 'id', header: RM.Translate.Common.Id},
    {dataIndex: 'name', header: RM.Translate.Common.Name}
];

RM.Pages.Functions.Units_Selector_List_Columns = new Ext.grid.ColumnModel({
    columns: RM.Pages.Functions.Units_Selector_List_Columns_Rows,
    defaults: {
        sortable: true
    }
});

RM.Pages.Functions.Units_Selector_List_Filters = new Ext.ux.grid.GridFilters({
    filters: [
        {dataIndex: 'id', type: 'numeric'},
        {dataIndex: 'name', type: 'string'}
    ]
});

// rendering function
RM.Pages.Functions.Units_Selector_Render = function () {
    Ext.ux.menu.RangeMenu.prototype.icons = {
        gt: 'img/greater_then.png',
        lt: 'img/less_then.png',
        eq: 'img/equals.png'
    };
    Ext.ux.grid.filter.StringFilter.prototype.icon = 'img/find.png';
    Ext.state.Manager.setProvider(new Ext.state.CookieProvider());

    RM.Pages.Functions.Units_Selector_List_Json_Store.load({params:{start: 0, limit: 30}});
};

RM.Pages.Functions.Units_Selector_GetSelection = function(){

    var allUnitsSelection = Ext.getCmp("rm_unit_selector_all_units").getValue();
    if (allUnitsSelection){
        return "0";
    }

    var selections = RM.Pages.Functions.Units_Selector_List_Columns_SM.getSelections();
    if (selections.length === 0){
        return false;
    }

    var selectionString = "";

    var i = 0;for(i; i < selections.length; i++){
            selectionString = selectionString + selections[i].id + ",";
    }
    return selectionString;
}

RM.Pages.Functions.Units_Selector_SetSelection = function(selection,totalunits){

    // 0 = all units
    if (selection === "0"){
        Ext.getCmp("rm_unit_selector_all_units").setValue("1");
        Ext.getCmp("rm_units_selector_list_grid").disable();
    } else {
        // reset the select all units
        Ext.getCmp("rm_unit_selector_all_units").setValue("0");
        Ext.getCmp("rm_units_selector_list_grid").enable();
    }

    RM.Pages.Functions.Units_Selector_List_Columns_SM.clearSelections(); // clear the selection

    var SelectionArray = RM.Common.explode(",",selection);
    var item = 0;for(item; item < SelectionArray.length; item++){
        var idx = 0;for(idx; idx < totalunits; idx++){

            var rec = Ext.getCmp("rm_units_selector_list_grid").getStore().getAt(idx);

            if (rec !=undefined){
                if (rec.id == SelectionArray[item]){
                    Ext.getCmp("rm_units_selector_list_grid").getSelectionModel().selectRow(idx,true);
                }
            }
        }
    }
}

RM.Pages.Functions.Units_Selector_AllUnits_Checkbox = {
    xtype: "form",
    height: 30,
    bodyBorder: false,
    frame : false,
    items: [{
        xtype : "xcheckbox",
        id: "rm_unit_selector_all_units",
        name: "rm_unit_selector_all_units",
        inputValue : "1",
        fieldLabel : RM.Translate.Admin.Unit.Selector.SelectAllUnits,
        listeners:{
            check: function(checkbox,value){
                if (value){
                    Ext.getCmp("rm_units_selector_list_grid").disable();
                } else {
                    Ext.getCmp("rm_units_selector_list_grid").enable();
                }
            }
        }
    }]
};

RM.Pages.Functions.Units_Selector = new Ext.Panel({
    id: "rm_unit_selector",
    autoHeight: true,
    layout: "form",
    items:[
        RM.Pages.Functions.Units_Selector_AllUnits_Checkbox,
        new Ext.ux.grid.livegrid.GridPanel({
            id : 'rm_units_selector_list_grid',
            plugins: RM.Pages.Functions.Units_Selector_List_Filters,
            enableColLock : false,
            loadMask : {
                msg: RM.Translate.Common.PleaseWait
            },
            height : 100,
            cm : RM.Pages.Functions.Units_Selector_List_Columns,
            store : RM.Pages.Functions.Units_Selector_List_Json_Store,
            selModel : RM.Pages.Functions.Units_Selector_List_Columns_SM,
            view: RM.Pages.Functions.Units_Selector_List_Grid_View,
            viewConfig: {
                forceFit: true
            },
            autoExpandColumn: 'name',
            listeners: {
                'afterlayout': function(){
                    RM.Pages.Functions.Units_Selector_Render();
                }
            }
        })
    ]
});