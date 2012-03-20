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
 * Unit Edit JS
 * This creates the admin GUI Unit Edit Pages
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

// timer used to reduce ajax requests on calendar hover
RM.Pages.Units_Edit_Timer = {};
RM.Pages.Units_Edit_Timer.Delay = 1000;
RM.Pages.Units_Edit_Timer.ID = null;
RM.Pages.Units_Edit_Timer.Set = function(dateval, unitid){
    window.clearTimeout(RM.Pages.Units_Edit_Timer.ID);
    RM.Pages.Units_Edit_Timer.ID = window.setTimeout('RM.Pages.Units_Edit_Timer.Start("'+dateval+'","'+unitid+'")', RM.Pages.Units_Edit_Timer.Delay);
};

RM.Pages.Units_Edit_Timer.Start = function (dateval, unitid){
    RM.Pages.Functions.Units_ShowBreakdown(dateval,unitid);
};

// this is the template used for the calendar tab time breakdown
RM.Pages.Units_Edit_TimeBreakdown_Template = new Ext.XTemplate(
    RM.Translate.Admin.Reservations.Edit.SelectedDay+": <br /><br />",
    '<tpl for="data">',
    RM.Translate.Admin.Reservations.Edit.BookingRef+': <b />{reservation_id}</b />&nbsp;{confirmed}<br />',
    RM.Translate.Admin.Reservations.Edit.UnitName+': <b />{unit_name} ({unit_id})</b /><br />',
    RM.Translate.Common.StartDatetime+': <b />{start_date}</b /><br />',
    RM.Translate.Common.EndDatetime+': <b />{end_date}</b /><br />',
    RM.Translate.Common.CustomerName+': <b />{title} {first_name} {last_name} ({user_id})</b /><br />',
    '<hr>',
    '</tpl>'
);

// this is used to get the data for the selected (hovered) date
RM.Pages.Functions.Units_ShowBreakdown = function(selectedDate, unitID){
    var conn = new Ext.data.Connection();
    var request = {
        url: RM.Common.AssembleURL({
            controller : 'Reservations',
            action: 'getreservationsJson'
        }),
        method: 'POST',
        params: {
            date: selectedDate,
            unitid: unitID

        },
        success: function(responseObject) {
            var jsonObject = RM.Common.JSON.decode(responseObject.responseText, true);
            var timebreakdown = Ext.getCmp('rm_pages_units_edit_calendar_timebreakdown');
            if (jsonObject.data!==undefined ){
                timebreakdown.body.update(
                    RM.Pages.Units_Edit_TimeBreakdown_Template.applyTemplate(jsonObject)
                );
            } else {
                timebreakdown.body.update(
                    RM.Translate.Admin.Units.Edit.NoReservationsForSelectedDay
                );
            }
        }
    };
    conn.request(request);
};

// language selection or page refresh
RM.Pages.Functions.units_edit_change_language = function(combo, record, index){
    var myMask = new Ext.LoadMask('rm_pages_units_edit_tab', {msg:RM.Translate.Common.PleaseWait});
    myMask.show();

    // this code means this function can be called with no passed values and will just act as a page refresh
    if (record===undefined) {record = {};ecord.data = {};ecord.data.field1 = false;} // set to false if record is not passed
    var isoValue = record.data.field1; //default value
    if (isoValue === false){isoValue = RM.DefaultLanguage;}

    var conn = new Ext.data.Connection();
    var request = {
        url: RM.Common.AssembleURL({
            controller : 'Units',
            action: 'editjson',
            parameters : [{
                name : 'id',
                value : RM.Pages.Units_Edit_UnitID
            }, {
                name : 'iso',
                value : isoValue
            }]
        }),
        method: 'POST',
        success: function(responseObject) {
           eval("RM.Pages.Functions.Units_EditJson("+responseObject.responseText+")");
           myMask.hide();
        },
        failure: function() {
           Ext.MessageBox.alert(RM.Translate.Admin.Units.Edit.ChangeLanguageFailure);
           myMask.hide();
        }
    };
    conn.request(request);
};

RM.Pages.Units_EditJson_SaveHandlers = [];
RM.Pages.Functions.units_edit_save = function(){
    var newUnitName = Ext.getDom('edit_unit[name]').value;
    var unitMenuID = "Units_EditJson_"+RM.Pages.Units_Edit_UnitID;

    if (RM.Pages.Units_Edit_Tab1.getForm().isValid() === false){
        return;
    }

    var i = 0;for (i; i < RM.Pages.Units_EditJson_SaveHandlers.length; i++){
        RM.Pages.Units_EditJson_SaveHandlers[i](true);
    }

    var selectedISO = Ext.getCmp('edit_units_iso').value;

    var decriptionText,summaryText;
    try {
        decriptionText = Ext.getCmp('edit_unit[description]').getValue();
    } catch (e) {
        decriptionText = "";
    }

    try {
        summaryText = Ext.getCmp('edit_unit[summary]').getValue();
    } catch (e) {
        summaryText = "";
    }


    RM.Pages.Units_Edit_Tab1.getForm().submit({
        params: {
            'edit_unit[iso]' : selectedISO,
            color:  Ext.getCmp('edit_unit[color]').value,
            'edit_unit[description]': decriptionText,
            'edit_unit[summary]': summaryText
        },
        success: function(form, action) {

            // update the new menu title...
            //Ext.getCmp("rm_main_tree_menu").root.findChild("id", "Units_ListJson_NoAjax").findChild("id",unitMenuID).setText(newUnitName);
            //Ext.getCmp("rm_main_tree_menu").root.findChild("id", "Units_ListJson_NoAjax").reload();
            //Ext.getCmp('rm_main_tree_menu').root.reload();
            //RM.Pages.Admin_Menu_TreeLoader.load();

            // TODO: need to add a unit root to reload just the units
            try {
                var currentNode = Ext.getCmp('rm_main_tree_menu').getSelectionModel().getSelectedNode().getPath();
                Ext.getCmp("rm_main_tree_menu").root.reload();
                Ext.getCmp("rm_main_tree_menu").expandPath(currentNode); // restore current node
            } catch (e){
                // the menu position can't be restored so just reload
                Ext.getCmp("rm_main_tree_menu").root.reload();
            }

            var record = [];
            record.data = [];
            record.data.field1 = selectedISO;

            RM.Pages.Functions.units_edit_change_language(null,record,null);

            // update the status bar...
            RM.Pages.Units_StatusBar = Ext.getCmp('rm_pages_unit_edit_statusbar');
            RM.Pages.Units_StatusBar.setStatus({
                text: RM.Translate.Admin.Units.Edit.EditSuccess,
                iconCls: 'ok-icon'
            });

            var i = 0;for (i; i < RM.Pages.Units_EditJson_SaveHandlers.length; i++){
                RM.Pages.Units_EditJson_SaveHandlers[i](false);
            }
        },
        waitMsg: RM.Translate.Common.Saving,
        waitTitle: RM.Translate.Common.PleaseWait
    });
};

RM.Pages.Functions.units_edit_delete = function(){
    Ext.MessageBox.confirm(RM.Translate.Common.Delete, RM.Translate.Admin.Units.Edit.DeleteAlert, function(buttonID){
        if (buttonID === Ext.MessageBox.OK) {
            var conn = new Ext.data.Connection();
            var request = {
                url: RM.Common.AssembleURL({
                    controller : 'Units',
                    action: 'deletejson',
                    parameters : [{
                        name : 'id',
                        value : RM.Pages.Units_Edit_UnitID
                    }]
                }),
                method: 'POST',
                success: function(responseObject) {
                   RM.Pages.Functions.Units_ListJson();
                },
                failure: function() {
                   Ext.MessageBox.alert(RM.Translate.Common.DeleteFailure);
                }
            };
            conn.request(request);
        }
    });
};

RM.Pages.Functions.units_edit_cancel = function(){
    RM.Pages.Functions.Units_ListJson();
};

RM.Pages.Units_Edit_Toolbar = {
    xtype : "panel",
    id : "rm_pages_units_edit_toolbar",
    bodyBorder : false,
    html : RM.Common.getToolbar([
        {image: RM.BaseLargeImageURL+"save.gif", label: RM.Translate.Common.Save, link: "RM.Pages.Functions.units_edit_save()"},
        {image: RM.BaseLargeImageURL+"cancel.gif", label: RM.Translate.Common.Cancel, link: "RM.Pages.Functions.units_edit_cancel()"}
    ])
};
RM.Toolbars.push(RM.Pages.Units_Edit_Toolbar);

RM.Pages.Functions.Units_Edit_Initialize = function(responseObject) {
    var i = 0;for (i; i < responseObject.prices.length; i++){
        if (responseObject.prices[i] === responseObject.price) {
            RM.Pages.Units_Edit_TabPanel.unhideTabStripItem('rm_pages_unit_'+responseObject.price+'_panel');
            RM.Pages.Units_Edit_TabPanel.unhideTabStripItem('rm_pages_unit_'+responseObject.price+'_list');

            // this support groups (if installed/enabled), this disables the price tab on sub units.
            if (RM.Pages.Units_Edit_IsTemplateUnit !== 0){
                Ext.getCmp('rm_pages_unit_'+responseObject.price+'_panel').setDisabled(false);
                Ext.getCmp('rm_pages_unit_'+responseObject.price+'_list').setDisabled(false);
            } else {
                Ext.getCmp('rm_pages_unit_'+responseObject.price+'_panel').setDisabled(true);
                Ext.getCmp('rm_pages_unit_'+responseObject.price+'_list').setDisabled(true);
            }

        } else {
            RM.Pages.Units_Edit_TabPanel.hideTabStripItem('rm_pages_unit_'+responseObject.prices[i]+'_panel');
            RM.Pages.Units_Edit_TabPanel.hideTabStripItem('rm_pages_unit_'+responseObject.prices[i]+'_list');
        }
    }
};

RM.Pages.Units_Edit_UnitID = 0;
RM.Pages.Units_Edit_IsTemplateUnit = 0; // this is used for group to determine if it's a root/template unit

RM.Pages.Functions.Units_Edit_Remove_All_Details = function(){

    var tab = Ext.getCmp('units_edit_tab_1_details');
    if(tab.items !== undefined){
        tab.items.each(function(item){
            tab.remove(item, true);
        });
    }
    tab.doLayout();

    var tab = Ext.getCmp('units_edit_tab_1');
    if(tab.items !== undefined){
        tab.items.each(function(item){
            tab.remove(item, true);
        });
    }
    tab.doLayout();
};
RM.Pages.Units_EditJson_Handlers = [];
RM.Pages.Functions.Units_EditJson = function (responseObject) {

    RM.Pages.Units_Edit_UnitID = responseObject.unit.id;
    RM.Pages.Units_Edit_IsTemplateUnit = parseInt(responseObject.isgrouptemplate,10);

    // groups code - this disables tabs that are not required by the sub unit.
    if (RM.Pages.Units_Edit_IsTemplateUnit !== 0){
        RM.Pages.Units_Edit_Tab2.setDisabled(false);
        RM.Pages.Categories_Unit.setDisabled(false);
        RM.Pages.Locations_Unit_Edit_Form.setDisabled(false);
        RM.Pages.Module_UnitTypes_Edit_Form.setDisabled(false);
    } else {
        RM.Pages.Units_Edit_Tab2.setDisabled(true);
        RM.Pages.Categories_Unit.setDisabled(true);
        RM.Pages.Locations_Unit_Edit_Form.setDisabled(true);
        RM.Pages.Module_UnitTypes_Edit_Form.setDisabled(true);
    }


    RM.Pages.Functions.Units_Edit_Initialize(responseObject);

    RM.Pages.Units_Edit.on('tabchange', function (page, currentTab){
        currentTab.doLayout();
    });

    Ext.getCmp('toolbar_panel').layout.setActiveItem('rm_pages_units_edit_toolbar');
    Ext.getCmp('content-panel').layout.setActiveItem('rm_pages_units_edit');

    var languageCombobox = Ext.getCmp('edit_units_iso');
    languageCombobox.on('select', RM.Pages.Functions.units_edit_change_language);
    languageCombobox.setValue(responseObject.language);

    //var tab = Ext.getCmp('units_edit_tab_1_details');

    var hiddenInputs = [];

    RM.Pages.Functions.Units_Edit_Remove_All_Details();
    var i = 0;for (i; i < responseObject.fields.length; i++){
        field = responseObject.fields[i];

        field.view.id = 'edit_' + field.view.id;
        field.view.name = 'edit_' + field.view.name;
        if (field.view.hiddenName) {
            var re_Query = new RegExp(/\[(.*)\]/); // load the name in the [ ] brackets
            var ActualHiddenName = field.view.hiddenName.match(re_Query)[1];
            hiddenInputs.push(ActualHiddenName);
            field.view.hiddenName = 'edit_' + field.view.hiddenName;
        }
        field.parent_id = 'units_edit_' + field.parent_id;
        eval("Ext.getCmp(field.parent_id).add(new "+field.class_name+"(field.view))");
    }

    var field_name;for (field_name in responseObject.unit) {
        eval('var field_value = responseObject.unit.'+field_name+';');
        if(RM.Common.InArray(field_name, hiddenInputs)===true) {
           Ext.getCmp('edit_unit_'+field_name).setValue(field_value);
        } else {
            if (field_name==="color"){
                Ext.getCmp('edit_unit['+field_name+']').value = field_value;
            } else {
                if (Ext.getCmp('edit_unit['+field_name+']')!==undefined){
                    Ext.getCmp('edit_unit['+field_name+']').setValue(field_value);
                }
            }
        }
    }

    // calendar load data
    var calendar = Ext.getCmp("rm_pages_units_edit_calendar");
    calendar.clearView();
    calendar.markEventPeriods(responseObject.periods,RM.Translate.Admin.Units.Edit.CalendarHoverText);

    RM.Pages.Units_Edit_Images_JSON_Store.load({
        params: {
            unit_id : RM.Pages.Units_Edit_UnitID
        }
    });

    RM.Pages.Functions.Units_Edit_Media_Tree_Loader_Reload();
    RM.Pages.Units_Edit_MediaManager_JSON_Store.load();

    RM.Pages.Units_StatusBar.setStatus({
        text: RM.Translate.Admin.Units.Edit.Status,
        iconCls: 'ok-icon'
    });
    Ext.getCmp("rm_pages_units_edit_tab").setActiveTab('units_edit_tab_1');
    Ext.getCmp('rm_pages_units_edit_tab').doLayout(false,true);

    var p = 0;for (p; p < RM.Pages.Units_EditJson_Handlers.length; p++){
        RM.Pages.Units_EditJson_Handlers[p](responseObject);
    }
};

RM.Pages.Functions.Units_Edit_Media_Tree_Loader_Reload = function(){
    myMask = new Ext.LoadMask('content-panel', {msg:RM.Translate.Common.PleaseWait});
    myMask.show();

    RM.Pages.Units_Edit_Media_Tree_Loader.load(RM.Pages.Units_Edit_Media_Tree_Root, function(){
        myMask.hide();
    });
};

RM.Pages.Units_Edit_Tab2 = new Ext.Panel({
    xtype : "panel",
    //height : RM.Common.GetPanelHeight(159),
    title : RM.Translate.Admin.Units.Edit.Media,
    bodyStyle : "padding:10px;",
    autoscroll : true,
    layout:'border',
    defaults: {
        split: true,
        useShim : true
    },
    containerScroll : true,
    items : [
        RM.Pages.Units_Edit_Media_Center,
        RM.Pages.Units_Edit_Media_East
    ],
    listeners: {
        'beforehide' : function(){
            Ext.getCmp('toolbar_panel').layout.setActiveItem('rm_pages_units_edit_toolbar');
            return true;
        },
        'beforeshow' : function(){
            Ext.getCmp('toolbar_panel').layout.setActiveItem('rm_pages_units_edit_media_toolbar');
            return true;
        }
    }
});

RM.Pages.Units_Edit_Tab2.on('afterlayout', function(){
    RM.Help.Load('Admin.Unit.Media.Main');
    return true;
});

rmCalendarBodyHeight = Ext.getBody().getViewSize().height;
rmCalendarBodyWidth = Ext.getBody().getViewSize().width;
rmCalendarRows = 3;
rmCalendarCols = 4;
if (rmCalendarBodyHeight < 710) {
    rmCalendarRows = 1;
} else if (rmCalendarBodyHeight < 900) {
    rmCalendarRows = 2;
}
if (rmCalendarBodyWidth < 1150) {
    rmCalendarCols = 2;
} else if (rmCalendarBodyWidth < 1360) {
    rmCalendarCols = 3;
}
RM.Pages.Units_Edit_Calendar = {
    id: 'rm_pages_units_edit_calendar',
    defaults: {anchor:'100%'},
    xtype: 'rmcalendar',
    title : RM.Translate.Common.Calendar,
    allowBlank: true,
    readOnly: true,
    disableMonthPicker: true,
    showActiveDate: true,
    format: 'Y-m-d',
    noOfMonth : rmCalendarCols * rmCalendarRows,
    noOfMonthPerRow : rmCalendarCols,
    summarizeHeader : true,
    multiSelection: false,
    multiSelectByCTRL: false,
    showWeekNumber: true,
    useQuickTips: false,
    renderOkUndoButtons: false,
    markNationalHolidays: false,
    styleDisabledDates: true,
    eventDatesSelectable: true,
    listeners: {
        'mouseover': function(d){
            var unitid = RM.Pages.Units_Edit_UnitID;
            RM.Pages.Units_Edit_Timer.Set(d, unitid);
        }
    }
};

RM.Pages.Units_Edit_Tab3 = {
    title : RM.Translate.Common.Calendar,
    xtype: "panel",
    layout:'border',
    defaults: {
        split: true
    },
    autoScroll : true,
    containerScroll : true,
    bodyBorder : false,
    frame : false,
    bodyStyle : "padding:10px",
    items: [{
        region:'center',
        bodyStyle : "padding:10px",
        autoScroll : true,
        items : [
            RM.Pages.Units_Edit_Calendar
        ]
    },{
        region : "east",
        bodyStyle : "padding:10px",
        autoScroll: true,
        collapsible: true,
        minWidth: 250,
        maxWidth: 250,
        width: 250,
        items : [{
            xtype : "fieldset",
            id: 'rm_pages_units_edit_calendar_timebreakdown',
            title: RM.Translate.Admin.Units.Edit.TimeBreakDown,
            autoHeight: true,
            autoWidth: true,
            bodyBorder : false,
            html: RM.Translate.Admin.Units.Edit.CalendarHoverHelp
        }]
    }]
};

RM.Pages.Units_Edit_Tab1_details = new Ext.FormPanel({
    id : "units_edit_tab_1_details",
    title : RM.Translate.Admin.Units.Edit.Details,
    xtype : "form",
    layout: 'form',
    url : RM.Common.AssembleURL({
        controller: 'Units',
        action: 'updatejson'
    }),
    frame : true,
    autoScroll: true,
//    bodyStyle : "padding:10px",
    listeners: {
        show: function(){
            this.doLayout(false, true);
        }
    }
});

RM.Pages.Units_Edit_Tab1 = new Ext.FormPanel({
    id : "units_edit_tab_1",
    title : RM.Translate.Admin.Units.Edit.General,
    xtype : "form",
    layout: 'form',
    url : RM.Common.AssembleURL({
        controller: 'Units',
        action: 'updatejson'
    }),
    frame : true,
    autoScroll: true,
    bodyStyle : "padding:10px"
});

RM.Pages.Units_Edit_TabPanel = new Ext.TabPanel({
    id: "rm_pages_units_edit_tab",
    activeTab : 0,
    frame : true,
    height: RM.Common.GetPanelHeight(109),
    items : [
        RM.Pages.Units_Edit_Tab1,
        RM.Pages.Units_Edit_Tab1_details,
        RM.Pages.Units_Edit_Tab2,
        RM.Pages.Units_Edit_Tab3
    ],
    bbar: new Ext.ux.StatusBar({
        id: 'rm_pages_unit_edit_statusbar',
        items: []
    })
});

RM.Pages.Units_StatusBar = Ext.getCmp('rm_pages_unit_edit_statusbar');
RM.Pages.Units_StatusBar.setStatus({
    text: RM.Translate.Admin.Units.Edit.Status,
    iconCls: 'ok-icon'
});

RM.Pages.Units_Edit_Language_Combo = new Ext.form.ComboBox({
    id : "edit_units_iso",
    hiddenName : "edit_unit[iso]",
    xtype : "combo",
    //cls: 'RM_language_selection_combo',
    typeAhead: true,
    fieldLabel: RM.Translate.Admin.Units.Edit.UnitLanguage,
    forceSelection: true,
    triggerAction: 'all',
    selectOnFocus: true,
    store: RM.Languages,
    bodyBorder : false,
    frame : false,
    width: 180
});

RM.Pages.Units_Edit_Language = new Ext.Panel({
    id : "rm_pages_units_edit_language",
    layout: 'form',
    frame : false,
    bodyBorder : false,
    items : [
        RM.Pages.Units_Edit_Language_Combo
    ]
});

RM.Pages.Units_Edit_Title = {
    xtype : "panel",
    id : "rm_pages_units_edit_title",
    bodyBorder : false,
    frame : false,
    html : "<span class='RM_Title_Language_Bar_img'><img src='"+RM.BaseMenuImageURL+"unit.png'></span>&nbsp;<span class='RM_Title_Language_Bar_text'>"+RM.Translate.Admin.Units.Edit.Title+"</span>"
};

RM.Pages.Units_Edit_Language_Form = new Ext.FormPanel({
    id : "rm_pages_units_edit_language_selection",
    xtype : "form",
    layout: 'column',
    url : RM.Common.AssembleURL({
        controller: 'Units',
        action: 'updatejson'
    }),
    frame : false,
    baseCls: "x-panel-header",
    items : [
        RM.Pages.Units_Edit_Title,
        RM.Pages.Units_Edit_Language
    ]
});

RM.Pages.Units_Edit = new Ext.Panel({
    id : "rm_pages_units_edit",
    items : [
        RM.Pages.Units_Edit_Language_Form,
        RM.Pages.Units_Edit_TabPanel
    ]
});

RM.Main.Pages.push(RM.Pages.Units_Edit);