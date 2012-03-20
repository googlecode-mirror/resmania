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
 * Reservations Unit Selection JS
 * This creates the admin GUI Reservations Unit Selection Page
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
RM.Pages.Functions.Reservations_UnitSelection_WindowClose_Actions = function (){
    //RM.Pages.Reservations_UnitSelection_Content_Mask.hide();
    //RM.Pages.Reservations_UnitSelection_Window.hide();
};

RM.Pages.Functions.Reservations_UnitSelection_Add_Unit = function(newUnit, startdate, enddate){
    RM.Pages.Reservations_UnitSelection_Mask.show();
    var request = {
        url : RM.Common.AssembleURL({
            controller: 'Reservations',
            action: 'getreservedperiodsJson',
            parameters : [{
                name : 'unit_id',
                value : newUnit.id
            },{
                name: 'reservation_id',
                value: RM.Pages.Reservations_Edit_ReservationID
            }]
        }),
        method: 'POST',
        success: function(responseObject) {            
            eval('var responseObject = '+responseObject.responseText+';');
            var detail = {
                unit: newUnit,
                reserved_period: {
                    start_date: startdate,
                    end_date: enddate
                },
                showtime: responseObject.showtime,
                pricesystem : responseObject.pricesystem,
                blocked_periods : responseObject.blocked_periods,
                persons: {
                    adults: 1,
                    children: 0,
                    infants: 0
                }
            };
            RM.Pages.Functions.Reservations_EditJson_ParseDetail(detail);
            RM.Pages.Reservations_Edit_UnitIDs.push(newUnit.id);
            RM.Pages.Functions.Reservations_SummaryUpdate(newUnit.id);
            RM.Pages.Reservations_UnitSelection_Mask.hide();
            RM.Pages.Reservations_UnitSelection_Window.hide();
        },
        failure: function() {
            RM.Pages.Reservations_UnitSelection_Mask.hide();
            Ext.Msg.alert('Status', RM.Translate.Common.UnableToShow);
        }
    };

    var conn = new Ext.data.Connection();
    conn.request(request);       
};

RM.Pages.Functions.Reservations_UnitSelection_GetSelected_Unit = function (){
    var unitsobj = Ext.getCmp("rm_reservations_unit_selection_list_grid").getSelectionModel().getSelections();

    var selection = null;
    var i = 0;for (i; i < unitsobj.length; i++){
        selection = unitsobj[i].data;
    }
    return selection;
};

RM.Pages.Functions.Reservations_UnitSelection_Page2Grid_Json = function (responseObject){

    // need to use a try here as it will throw an exception if we check and the grid is not present.
    var gridcheck = false;
    try{
        gridcheck = Ext.getCmp('rm_reservations_unit_selection_list_grid').isVisible();
    } catch (err){
        gridcheck = false;
    }

    if (!gridcheck) {

        // if gridcheck is false then create the json store and grid and add to form
//        if (responseObject.total === 0) {
//
//        }

        RM.Pages.Reservations_UnitSelection_Unit_Json_Store = new Ext.data.JsonStore({
            data: responseObject,
            id: 'rm_reservations_unit_selection_grid_json_store',
            root: 'data',
            fields: RM.Pages.Units_List_Json_Store_Fields,
            sortInfo: {field: 'id', direction: 'ASC'},
            remoteSort: true
        });

        RM.Pages.Wizard_Units_List_Columns_SM = new Ext.grid.CheckboxSelectionModel({
            singleSelect: true
        });
        RM.Pages.Wizard_Units_List_Columns_Rows = RM.Pages.Units_List_Columns_Rows;
        RM.Pages.Wizard_Units_List_Columns_Rows[0] = RM.Pages.Wizard_Units_List_Columns_SM;

        RM.Pages.Reservations_UnitSelection_Unit_List_Filters = new Ext.ux.grid.GridFilters({
            filters: RM.Pages.Units_List_Filters_Rows
        });

        RM.Pages.Reservations_UnitSelection_Unit_List_Columns = new Ext.grid.ColumnModel({
            id : 'rm_reservations_unit_selection_list_columns',
            defaultSortable : true,
            columns: RM.Pages.Wizard_Units_List_Columns_Rows
        });

        RM.Pages.Reservations_UnitSelection_Unit_List_Grid = new Ext.grid.GridPanel({            
            id : 'rm_reservations_unit_selection_list_grid',
            bbar : new Ext.PagingToolbar({
                store: RM.Pages.Reservations_UnitSelection_Unit_Json_Store,
                pageSize: 15,
                plugins: RM.Pages.Reservations_UnitSelection_Unit_List_Filters                
            }),
            enableColLock : false,
            loadMask : {
                msg: RM.Translate.Common.PleaseWait
            },
            height: 400,
            cm : RM.Pages.Reservations_UnitSelection_Unit_List_Columns,
            ds : RM.Pages.Reservations_UnitSelection_Unit_Json_Store,
            sm : RM.Pages.Wizard_Units_List_Columns_SM,
            viewConfig: {
                forceFit: true
            }
        });

        RM.Pages.Reservations_UnitSelection_Page2_Panel.add(RM.Pages.Reservations_UnitSelection_Unit_List_Grid);
    }

    Ext.getCmp('rm_reservation_unit_selection_window_panel').layout.setActiveItem('rm_pages_reservations_unit_selection_page2');

};
// units end

RM.Pages.Functions.Reservations_UnitSelection = function(startdate, enddate){    
    RM.Pages.Reservations_UnitSelection_Mask.show();
    var request = {
        url : RM.Common.AssembleURL({
            controller: 'Units',
            action: 'listJson'
        }),
        params: {
            'type': 'new',
            'showOnlyAvailable' : true,
            'reservationID' : RM.Pages.Reservations_Edit_ID,
            'start_datetime' : startdate,
            'end_datetime' : enddate
        },
        method: 'POST',
        success: function(responseObject) {
            RM.Pages.Reservations_UnitSelection_Mask.hide();
            eval('var jsonResponse = '+responseObject.responseText +';');
            if (jsonResponse.total === 0) {
                Ext.getCmp('rm_pages_reservations_unit_selection_next_page2_button').enable();
                Ext.getCmp('rm_pages_reservations_unit_selection_page1_statusbar').setStatus({
                    text: RM.Translate.Admin.Reservations.Edit.NoUnitsAvailable,
                    iconCls: 'failed-icon'
                });
            } else {
                Ext.getCmp('rm_pages_reservations_unit_selection_page1_statusbar').setStatus({
                    text: RM.Translate.Common.Ok,
                    iconCls: 'ok-error'
                });                
                RM.Pages.Functions.Reservations_UnitSelection_Page2Grid_Json(jsonResponse);
            }
        },
        failure: function() {
            RM.Pages.Reservations_UnitSelection_Mask.hide();
            Ext.Msg.alert('Status', RM.Translate.Common.UnableToShow);
        }
    };

    var conn = new Ext.data.Connection();
    conn.request(request);
};

RM.Pages.Reservations_UnitSelection_StartDate = {
    xtype : "panel",
    layout: 'form',
    id : "rm_pages_reservations_unit_selection_startdate",
    bodyBorder : false,
    items : [{
        xtype : "xdatetime",
        id : "reservation_unit_selection[start_datetime]",
        name : "reservation_unit_selection[start_datetime]",
        markNationalHolidays: false,
        fieldLabel : RM.Translate.Common.StartDatetime,
        width : 250,
        dateFormat: RM.Common.GUIDateFormat,
        listeners : {
            'afterdateclick' : function(picker, date, wasSelected){
                Ext.getCmp('reservation_unit_selection[end_datetime]').setValue(date);
            }
        }
    }]
};

RM.Pages.Reservations_UnitSelection_EndDate = {
    xtype : "panel",
    layout: 'form',
    id : "rm_pages_reservations_unit_selection_enddate",
    bodyBorder : false,
    items : [{
        xtype : "xdatetime",
        id : "reservation_unit_selection[end_datetime]",
        markNationalHolidays: false,
        name : "reservation_unit_selection[end_datetime]",
        fieldLabel : RM.Translate.Common.EndDatetime,
        width : 250,
        dateFormat: RM.Common.GUIDateFormat
    }]
};

RM.Pages.Reservations_UnitSelection_Datetime_Selection_Message = {
    xtype : "panel",
    border: false,
    frame: false,
    html: "<div class='RM_Reservation_Edit_UnitSelection_DateTime_Message_Icon'><img src='"+RM.BaseSmallImageURL+"exclamation-white.png' border='0'></div><div class='RM_Reservation_Edit_UnitSelection_DateTime_Message'>"+RM.Translate.Admin.Reservations.Edit.DateTimeSelectionMessage+"</div>"
};

RM.Pages.Functions.Reservations_Edit_UnitSelection_Request = function(){
    RM.Pages.Reservations_UnitSelection_Content_Mask = new Ext.LoadMask('content-panel', {
        msg: RM.Translate.Common.PleaseWait,
        msgCls: "RM_Loading_Mask_Msg_Hidden" // hide the msg
    });
    RM.Pages.Reservations_UnitSelection_Content_Mask.show();

    RM.Pages.Functions.Reservations_UnitSelection_Json();
};


RM.Pages.Functions.Reservations_UnitSelection_Json_NotLoad = true;
// main function
RM.Pages.Functions.Reservations_UnitSelection_Json = function (){
    if (RM.Pages.Functions.Reservations_UnitSelection_Json_NotLoad) {
        RM.Pages.Functions.Reservations_UnitSelection_Json_NotLoad = false;

    // Page 1
    RM.Pages.Reservations_UnitSelection_Page1_Panel = new Ext.Panel({
        id : 'rm_pages_reservations_unit_selection_page1',
        height: 500,
        bodyBorder : false,
        bodyStyle : "padding:10px",
        title: RM.Translate.Admin.Reservations.Edit.SelectRequiredDates,
        items: [
            RM.Pages.Reservations_UnitSelection_StartDate,
            RM.Pages.Reservations_UnitSelection_EndDate,
            RM.Pages.Reservations_UnitSelection_Datetime_Selection_Message,
            {
                xtype: "hidden",
                name: 'rm_pages_reservations_unit_selection_start_date_field',
                id: 'rm_pages_reservations_unit_selection_start_date_field'
            },{
                xtype: "hidden",
                name: 'rm_pages_reservations_unit_selection_end_date_field',
                id: 'rm_pages_reservations_unit_selection_end_date_field'
            }
        ],
        bbar: new Ext.ux.StatusBar({
            id: 'rm_pages_reservations_unit_selection_page1_statusbar',
            items: []
        }),
        buttons: [{
            text: RM.Translate.Common.Cancel,
            handler: function(){
                RM.Pages.Reservations_UnitSelection_Window.hide();
            }
        },{
            id: 'rm_pages_reservations_unit_selection_next_page2_button',
            text: RM.Translate.Common.NextStep,
            handler: function(){                                
                var startdatetime = Ext.getCmp('reservation_unit_selection[start_datetime]').getValue();
                var enddatetime = Ext.getCmp('reservation_unit_selection[end_datetime]').getValue();

                if (startdatetime === 0 || enddatetime === 0){
                    Ext.Msg.alert(RM.Translate.Common.Status, RM.Translate.Admin.Reservations.Edit.NeedToSelectStartEndDates);
                    return true;
                }                

                if (startdatetime.getTime() > enddatetime.getTime()){
                    Ext.Msg.alert(RM.Translate.Common.Status, RM.Translate.Admin.Reservations.Edit.StartAfterEnd);
                    return true;
                }

                Ext.getCmp('rm_pages_reservations_unit_selection_next_page2_button').disable();
                RM.Pages.Functions.Reservations_UnitSelection(
                    startdatetime.format("Y-m-d H:i:s"),
                    enddatetime.format("Y-m-d H:i:s")
                );                
            }
        }]
    });

    // Page 2
    RM.Pages.Reservations_UnitSelection_Page2_Panel = new Ext.Panel({
        id : 'rm_pages_reservations_unit_selection_page2',
        height: 485,
        bodyBorder : false,
        title: RM.Translate.Admin.Reservations.Edit.SelectUnits,
        items: [],
        buttons: [{
            id: '',
            text: RM.Translate.Common.BackStep,
            handler: function(){                
                Ext.getCmp('rm_pages_reservations_unit_selection_next_page2_button').enable();
                Ext.getCmp('rm_reservation_unit_selection_window_panel').layout.setActiveItem('rm_pages_reservations_unit_selection_page1');
            }
        },{
            text: RM.Translate.Common.Cancel,
            handler: function(){
                RM.Pages.Reservations_UnitSelection_Window.hide();
            }
        },{
            id: 'rm_pages_reservations_unit_selection_next_page3_button',
            text: RM.Translate.Common.Save,
            handler: function(){                                
                var unit = RM.Pages.Functions.Reservations_UnitSelection_GetSelected_Unit();
                if (unit === null) {
                    Ext.Msg.alert(RM.Translate.Common.Status, RM.Translate.Admin.Reservations.Edit.NeedToSelectUnit);
                    return true;
                }

                var startdatetime = Ext.getCmp('reservation_unit_selection[start_datetime]').getValue();
                var enddatetime = Ext.getCmp('reservation_unit_selection[end_datetime]').getValue();

                RM.Pages.Functions.Reservations_UnitSelection_Add_Unit(                    
                    unit,
                    startdatetime.format("Y-m-d H:i:s"),
                    enddatetime.format("Y-m-d H:i:s")
                );                                   
            }
        }]
    });

    RM.Pages.Reservations_UnitSelection_Window_Panel = {
        id: 'rm_reservation_unit_selection_window_panel',
        activeItem: 0,
        layout: 'card',
        bodyBorder : false,
        items : [
            RM.Pages.Reservations_UnitSelection_Page1_Panel,
            RM.Pages.Reservations_UnitSelection_Page2_Panel
        ]
    };

    // Window
    RM.Pages.Reservations_UnitSelection_Window = new Ext.Window({
        id: 'rm_reservation_unit_selection_window',
        title: RM.Translate.Admin.Reservations.Edit.AddUnitToReservation,
        renderTo: "content-panel",
        layout: 'fit',
        width: 600,
        height: 400,
        plain: true,
        autoDestroy: false,
        closeAction : 'hide',
        items: [
            RM.Pages.Reservations_UnitSelection_Window_Panel
        ]
        ,listeners: {
            'beforehide': function(){
                Ext.getCmp('rm_pages_reservations_unit_selection_next_page2_button').enable();
                RM.Pages.Reservations_UnitSelection_Content_Mask.hide();
                return true;
            }
        }
    });

    RM.Pages.Reservations_UnitSelection_Window.center();    
    } else {
        RM.Pages.Reservations_UnitSelection_Window.show();
    }

    Ext.getCmp('rm_reservation_unit_selection_window_panel').layout.setActiveItem('rm_pages_reservations_unit_selection_page1');

    RM.Pages.Reservations_UnitSelection_Mask = new Ext.LoadMask('rm_reservation_unit_selection_window_panel', {
        msg: RM.Translate.Common.PleaseWait
    });

    RM.Pages.Reservations_UnitSelection_Window.show();
};