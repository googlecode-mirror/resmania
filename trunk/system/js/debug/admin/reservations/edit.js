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
 * Reservations Edit JS
 * This creates the admin GUI Reservations Edit Page
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

// This stores the units that should not be saved (have been removed from reservation)
RM.Pages.Reservations_Edit_UnitsRemoved = [];

// this stores other price module controls added by the reservation_edit.js
RM.Pages.Reservations_Edit_OtherInfo = [];
// this stores the other module form object names, used for recalling the values
RM.Pages.Reservations_Edit_OthersSystem_FormOjects = [];

// this stores the others module class names being used
RM.Pages.Reservations_Edit_OthersSystem_ClassName = [];


// timer used to reduce ajax requests on calendar hover
RM.Pages.Reservations_Edit_Timer = {};
RM.Pages.Reservations_Edit_Timer.Delay = 1000;
RM.Pages.Reservations_Edit_Timer.ID = null;
RM.Pages.Reservations_Edit_Timer.Set = function(dateval, unitid){
    window.clearTimeout(RM.Pages.Reservations_Edit_Timer.ID);
    RM.Pages.Reservations_Edit_Timer.ID = window.setTimeout('RM.Pages.Reservations_Edit_Timer.Start("'+dateval+'","'+unitid+'")', RM.Pages.Reservations_Edit_Timer.Delay);
};

RM.Pages.Reservations_Edit_Timer.Start = function (dateval, unitid){
    RM.Pages.Functions.Reservations_ShowBreakdown(dateval,unitid);
};

RM.Pages.Functions.Reservations_Edit_Get_Unit_IDs = function(){    
    return RM.Pages.Reservations_Edit_UnitIDs;
};

RM.Pages.Functions.Reservations_Edit_Get_Unit_Period = function(unit_id){    

    var StartDate = Ext.getCmp('reservation_edit_start_date_time_' + unit_id).getValue();
    var EndDate = Ext.getCmp('reservation_edit_end_date_time_' + unit_id).getValue();

    var returnVar;
    try{
        returnVar = {
            start: StartDate.format(RM.Common.MySQLDateFormat),
            end: EndDate.format(RM.Common.MySQLDateFormat)
        };
    } catch (err){
        returnVar = {
            start: StartDate,
            end: EndDate
        };
    }
    return returnVar;
};

RM.Pages.Functions.Reservations_Edit_Get_Unit_Persons = function(unit_id){

    var adultsFormObj,childrenFormObj,infantsFormObj;

    eval("adultsFormObj = Ext.getCmp('rm_pages_reservation_edit_unit_" + unit_id + "_persons_adults');");
    eval("childrenFormObj = Ext.getCmp('rm_pages_reservation_edit_unit_" + unit_id + "_persons_children');");
    eval("infantsFormObj = Ext.getCmp('rm_pages_reservation_edit_unit_" + unit_id + "_persons_infants');");
    
    var adultsValue = adultsFormObj.value;
    var childrenValue = childrenFormObj.value;
    var infantsValue = infantsFormObj.value;

    if (adultsValue === 0){ adultsValue = 1;}

    return {
        adults: adultsValue,
        children: childrenValue,
        infants: infantsValue
    };
};

/**
* process other info feilds
*
* RM.Pages.Reservations_Edit_OtherInfo defines the otherinfo items
* otherinfo items define other items that can be handled by the price
* system. At the moment only the hospitality prices uses otherinfo
* for the board_type. However other modules could use this as it's a
* flexible feild to extend the price system.
*
* This part constructs the json data to pass to the PHP for saving the
* otherinfo data.
*
* @params   array   unitid's
* @return   array   containing otherinfo items.
*/
RM.Pages.Functions.Reservations_getOtherInfoItems = function(UnitId){

    var otherInfoJson = [];
    for (p = 0; p < RM.Pages.Reservations_Edit_OtherInfo.length; p++) {
        var jsonString = "{"+RM.Pages.Reservations_Edit_OtherInfo[p]+":'"+Ext.getCmp('rm_pages_reservation_edit_unit_' + UnitId + '_otherInfo[' + RM.Pages.Reservations_Edit_OtherInfo[p] + ']').value+"'}";
        otherInfoJson.push(Ext.decode(jsonString));
    }
    return otherInfoJson;
};

// BEGIN: Toobars and Handlers
RM.Pages.Functions.Reservations_Edit_Save = function(){
    var user_id = RM.Pages.Reservations_Edit_Reservation_UserID;
    
    if (Ext.getCmp('edit_reservation_user_id').selectedIndex !== -1) {
        user_id = Ext.getCmp('edit_reservation_user_id').store.data.items[Ext.getCmp('edit_reservation_user_id').selectedIndex].id;
    }

    var reservation = {
        id: RM.Pages.Reservations_Edit_ID,
        user_id : user_id,
        confirmed : Ext.getCmp('edit_reservation_confirmed').getValue(),
        is_read: 1,
        notes: Ext.getCmp('edit_reservation[notes]').getValue()
    };


    
    var unitIDs = RM.Pages.Functions.Reservations_Edit_Get_Unit_IDs();
    var details = [];
    for (i = 0; i < unitIDs.length; i++) {
        var period = RM.Pages.Functions.Reservations_Edit_Get_Unit_Period(unitIDs[i]);
        var persons = RM.Pages.Functions.Reservations_Edit_Get_Unit_Persons(unitIDs[i]);

        var otherInfoJson = RM.Pages.Functions.Reservations_getOtherInfoItems(unitIDs[i]);

        // otherinfo will contain json for the otherinfo items. This is handled by
        // the php in the reservation controller.
        details.push({
            unit_id: unitIDs[i],
            reservation_id: RM.Pages.Reservations_Edit_ID,
            start_datetime: period.start,
            end_datetime: period.end,
            adults: persons.adults,
            children: persons.children,
            infants: persons.infants,
            otherinfo: Ext.util.JSON.encode(otherInfoJson)
        });
    }

    // handle others modules
    // these are not to be confused with otherInfo above, these are modules or plugins
    // that extend pricing but use the generic "Others" naming.
    var otherSystems = [];
    var otherSystemsData = [];
    // loop through all other systems
    for (os = 0; os < RM.Pages.Reservations_Edit_OthersSystem_ClassName.length; os++) {
        for (osFO = 0; osFO < RM.Pages.Reservations_Edit_OthersSystem_FormOjects.length; osFO++) {
            otherSystemsData.push({     
                name: RM.Pages.Reservations_Edit_OthersSystem_FormOjects[osFO],
                value: Ext.getCmp(RM.Pages.Reservations_Edit_OthersSystem_FormOjects[osFO]).getValue()
            });
        }
        otherSystems.push({
            systemClassName: RM.Pages.Reservations_Edit_OthersSystem_ClassName[os],
            data: otherSystemsData
        });
    }

    myMask = new Ext.LoadMask('content-panel', {msg:RM.Translate.Common.PleaseWait});
    myMask.show();
        
    var request = {
        url: RM.Common.AssembleURL({
            controller : 'Reservations',
            action: 'updatejson'            
        }),
        params: {
            details: Ext.util.JSON.encode(details),
            reservation: Ext.util.JSON.encode(reservation),
            deleted_unit_ids: Ext.util.JSON.encode(RM.Pages.Reservations_Edit_UnitsRemoved),
            other_systems: Ext.util.JSON.encode(otherSystems)
        },
        method: 'POST',        
        success: function(responseObject) {
            myMask.hide();
            
            //Ext.Msg.alert(RM.Translate.Common.Success, RM.Translate.Admin.Reservations.Edit.EditSuccess);
            Ext.getCmp('rm_pages_reservations_edit_statusbar').setStatus({
                text: RM.Translate.Common.Saved,
                iconCls: 'ok-icon'
            });

            var i = 0;for (i; i < RM.Pages.Reservations_Edit_UnitsRemoved.length; i++){
                Ext.getCmp('rm_pages_reservations_edit_tab').remove('rm_pages_reservation_edit_unit_details_' + RM.Pages.Reservations_Edit_UnitsRemoved[i]);
            }
            RM.Pages.Reservations_Edit_UnitsRemoved = [];
            RM.Pages.Functions.Reservations_EditJson_Request(RM.Pages.Reservations_Edit_ID);
        },
        failure: function() {
            myMask.hide();
            Ext.Msg.alert(RM.Translate.Common.Status, RM.Translate.Common.UnableToShow);            
        }
    };

    otherInfoJson = null;


    var conn = new Ext.data.Connection();
    conn.request(request);
};

RM.Pages.Functions.Reservations_Edit_Cancel = function(){
    RM.Pages.Functions.Reservations_ListJson_Request();
};

RM.Pages.Functions.Reservations_Edit_UnitAssignment = function(){
    RM.Pages.Functions.Reservations_Edit_UnitSelection_Request();
};

RM.Pages.Reservations_Edit_Toolbar = {
    xtype : "panel",
    id : "rm_pages_reservations_edit_toolbar",
    bodyBorder : false,
    html : RM.Common.getToolbar([
        {image: RM.BaseLargeImageURL+"newunit.gif", label: RM.Translate.Common.Units, link: "RM.Pages.Functions.Reservations_Edit_UnitAssignment()"},
        {image: RM.BaseLargeImageURL+"save.gif", label: RM.Translate.Common.Save, link: "RM.Pages.Functions.Reservations_Edit_Save()"},
        {image: RM.BaseLargeImageURL+"cancel.gif", label: RM.Translate.Common.Cancel, link: "RM.Pages.Functions.Reservations_Edit_Cancel()"}
    ])
};
RM.Toolbars.push(RM.Pages.Reservations_Edit_Toolbar);

//END: toolbar

//BEGIN: first main tab
RM.Pages.Reservations_Edit_UserAssignment = new Ext.data.JsonStore({
    id:'id',
    root:'data',
    totalProperty:'total',
    //autoLoad: true,
    fields:[
        {name:'id', type:'int'},
        {name:'last_name', type:'string'},
        {name:'first_name', type:'string'}
    ],
    url : RM.Common.AssembleURL({
        controller: 'Users',
        action: 'listJson'
    })
});

RM.Pages.Reservation_ConfirmedSelection = new Ext.data.JsonStore({
    fields:[
        {name:'id', type:'int'},
        {name:'selection', type:'string'}
    ],
    data: RM.Translate.Common.JSON.ConfirmedSelection
});

RM.Pages.Reservations_Common_Information = {
    xtype : "panel",
    layout: 'form',
    id : "rm_pages_reservations_edit_common_info",
    bodyBorder : false,
    items : []
};

RM.Pages.Reservations_Edit_General_Info_Fieldset = {
    xtype : "fieldset",
    title: RM.Translate.Common.Information,
    autoHeight: true,
    layout: 'form',
    bodyBorder : false,
    items: [
        RM.Pages.Reservations_Common_Information
    ]
};

RM.Pages.Reservations_Edit_General_Summary_Fieldset = new Ext.form.FieldSet({
    id: "rm_reservations_edit_general_summary_fieldset",
    title: RM.Translate.Common.Summary,
    autoHeight: true,
    layout: 'form',
    bodyBorder : false,
    html: ""
});

RM.Pages.Reservations_Edit_General_OtherInfo_Fieldset = {
    id: "rm_pages_reservations_edit_other_information",
    xtype : "fieldset",
    title: RM.Translate.Admin.Reservations.Edit.OtherInformation,
    autoHeight: true,
    layout: 'form',
    bodyBorder : false,
    html: ""
};

RM.Pages.Reservations_Edit_Tab_1 = {
    xtype : "panel",
    layout: "form",
    autoScroll : true,
    containerScroll : true,
    bodyBorder : false,
    frame : false,
    title : RM.Translate.Common.General,
    id : "rm_pages_reservations_edit_tab_1",
    bodyStyle : "padding:10px",
    items : [
        RM.Pages.Reservations_Edit_General_Info_Fieldset,
        RM.Pages.Reservations_Edit_General_Summary_Fieldset,
        RM.Pages.Reservations_Edit_General_OtherInfo_Fieldset
    ]
};
//END: first main tab

// START: Templates

RM.Pages.Reservations_Edit_NewSelectionSummary_Template = new Ext.XTemplate(
    '<div class="RM_reservation_edit_summary_container">',
        '<tpl for="details">',
            RM.Translate.Common.Unit+':<b> {values.unit.name} ({values.unit.id}) </b>&nbsp;&nbsp;',
            RM.Translate.Common.StartDatetime+':<b> {values.period.start} </b>&nbsp;&nbsp;',
            RM.Translate.Common.EndDatetime+':<b> {values.period.end} </b>&nbsp;&nbsp;',
            RM.Translate.Admin.Reservations.Edit.SubTotal+':<b> {price} </b>&nbsp;&nbsp;',
        '</tpl>',
    '</div>'
);

RM.Pages.Reservations_Edit_General_Summary_Template = new Ext.XTemplate(
    '<div class="RM_reservation_edit_summary_container">',
        '<tpl for="prices">',
            RM.Translate.Common.Unit+':<b> {values.unit.name} ({values.unit.id}) </b>&nbsp;&nbsp;',
            RM.Translate.Common.StartDatetime+':<b> {[RM.Pages.Reservations_Edit_Render_Date(values.reserved_period.start_date, values.showtime)]} </b>&nbsp;&nbsp;',
            RM.Translate.Common.EndDatetime+':<b> {[RM.Pages.Reservations_Edit_Render_Date(values.reserved_period.end_date, values.showtime)]} </b>&nbsp;&nbsp;',
            RM.Translate.Admin.Reservations.Edit.SubTotal+':<b> {values.total_sub} </b>',
            '<tpl for="summary_rows">',
                '&nbsp;&nbsp;{[values.name]}: <b>{[values.total_amount]}</b>',
            '</tpl>',
            '<br/>',
        '</tpl>',
        '<hr>'+RM.Translate.Admin.Reservations.Edit.ReservationTotal+':&nbsp;<b>{reservationTotal}</b>',
        '<tpl for="reservation_summary_rows">',
            '&nbsp;&nbsp;{[values.name]}: <b>{[values.total_amount]}</b>',
        '</tpl>',
    '</div>'
);

RM.Pages.Reservations_Edit_Billings_Summary_Template = new Ext.XTemplate(
    '<table width="100%" border="0" cellpadding="5" cellspacing="0">',
    '<tr>',
    '<td class="RM_admin_table_header" width="30%">'+RM.Translate.Common.Unit+'</td>',
    '<td class="RM_admin_table_header" width="20%">'+RM.Translate.Common.StartDatetime+'</td>',
    '<td class="RM_admin_table_header" width="20%">'+RM.Translate.Common.EndDatetime+'</td>',
    '<td class="RM_admin_table_header" width="30%" align="left">'+RM.Translate.Common.Totals+'</td>',
    '</tr>',
    '<tpl for="prices">',
        '<tr>',
            '<td>({[values.unit.id]}) {[values.unit.name]}</td>',
            '<td>{[RM.Pages.Reservations_Edit_Render_Date(values.reserved_period.start_date, values.showtime)]}</td>',
            '<td>{[RM.Pages.Reservations_Edit_Render_Date(values.reserved_period.end_date, values.showtime)]}</td>',
            '<td align="left">{[values.total_sub]}</td>',
        '</tr>',
        '<tpl for="summary_rows">',
        '<tr>',
            '<td align="right" colspan="3">{[values.name]} {[this.renderValue(values.value)]}:&nbsp;&nbsp;</td>',
            '<td align="left">{[values.total_amount]}</td>',
        '</tr>',
    '</tpl>',
    '</tpl>',
    '<tpl for="reservation_summary_rows">',
        '<tr>',
            '<td align="right" colspan="3">{[values.name]} {[this.renderValue(values.value)]}:&nbsp;&nbsp;</td>',
            '<td align="left">{[values.total_amount]}</td>',
        '</tr>',
    '</tpl>',
    '<tr>',
        '<td align="right" colspan="3"><b>'+RM.Translate.Admin.Reservations.Edit.ReservationTotal+'</b>:&nbsp;&nbsp;</td>',
        '<td align="left"><b>{reservationTotal}</b></td>',
    '</tr>',
    '</table>',
    {
        renderValue: function(value) {
            if (value <= 1) {
                return "";
            }
            return "("+value+")";
        }
    }
);

RM.Pages.Reservations_Edit_Render_Date = function(date, showTime){
    var dateObject = RM.Common.ConvertToDate(date);
    if (showTime) {
        return dateObject.format(RM.Common.GUIDateFormatFull);
    } else {
        return dateObject.format(RM.Common.GUIDateFormat);
    }
};

RM.Pages.Reservations_Edit_Billings_Payments_Template = new Ext.XTemplate(
    '<table width="100%" border="0" cellpadding="3" cellspacing="0">',
    '<tr>',
    '<td class="RM_admin_table_header">'+RM.Translate.Common.Date+'</td>',
    '<td class="RM_admin_table_header">'+RM.Translate.Admin.Reservations.Edit.PaymentProvider+'</td>',
    '<td class="RM_admin_table_header">'+RM.Translate.Admin.Reservations.Edit.TransactionID+'</td>',
    '<td class="RM_admin_table_header">'+RM.Translate.Common.Status+'</td>',
    '<td class="RM_admin_table_header">'+RM.Translate.Admin.Reservations.Edit.TotalPaid+'</td>',
    '<td class="RM_admin_table_header"></td>',
    '</tr>',
    '<tpl for="payments">',
        '<tr><td>{values.date}</td><td>{values.provider}</td><td>{values.transactionid}</td><td>{values.status}</td><td>{values.total_paid}</td><td><a href="javascript:void(0);" onclick="RM.Pages.Reservations_Edit_Billings_Payments_DeletePayment(\'{values.total_paid}\');"><img src="'+RM.BaseSmallImageURL+'cross.gif" border="0"></a></td></tr>',
    '</tpl>',
    '</table>'
);

RM.Pages.Reservations_Edit_Billings_Payments_DeletePayment = function (totalPaid){

    Ext.MessageBox.confirm(RM.Translate.Common.Delete, RM.Translate.Admin.Reservations.Edit.DeleteBillingPayment, function(buttonID){

        if (buttonID !== 'yes') {
            return;
        }

        myMask = new Ext.LoadMask('content-panel', {msg:RM.Translate.Common.PleaseWait});
        myMask.show();

        var request = {
            url: RM.Common.AssembleURL({
                controller : 'Reservations',
                action: 'deletepaymentJson'
            }),
            params: {
                reservation_id: RM.Pages.Reservations_Edit_ID,
                total_paid: totalPaid
            },
            method: 'POST',
            success: function(responseObject) {
                myMask.hide();
                var jsonObject = Ext.util.JSON.decode(responseObject.responseText);
                if (jsonObject.success == true){
                    RM.Pages.Functions.Reservations_EditJson_Request(RM.Pages.Reservations_Edit_ID);
                }
            },
            failure: function() {
                myMask.hide();
                Ext.Msg.alert(RM.Translate.Common.Status, RM.Translate.Common.UnableToShow);
            }
        };

        var conn = new Ext.data.Connection();
        conn.request(request);
    });
}

RM.Pages.Reservations_Edit_TimeBreakdown_Template = new Ext.XTemplate(
    RM.Translate.Admin.Reservations.Edit.SelectedDay+": <br /><br />",
    '<tpl for="data">',
    RM.Translate.Admin.Reservations.Edit.BookingRef+': <b>{reservation_id}</b>&nbsp;{confirmed}<br />',
    RM.Translate.Admin.Reservations.Edit.UnitName+': <b>{unit_name} ({unit_id})</b><br />',
    RM.Translate.Common.StartDatetime+': <b>{start_date}</b><br />',
    RM.Translate.Common.EndDatetime+': <b>{end_date}</b><br />',
    RM.Translate.Common.CustomerName+': <b>{title} {first_name} {last_name} ({user_id})</b><br />',
    '<hr>',
    '</tpl>'
);

// END: Templates

// this is used on the billings and payments page
RM.Pages.Reservations_Edit_Billings_Summary_Fieldset = new Ext.form.FieldSet({
    title: RM.Translate.Common.Summary,
    autoHeight: true,
    layout: 'form',
    bodyBorder : false,
    html: ""
});

RM.Pages.Reservations_Edit_Billings_Payments_Fieldset = new Ext.form.FieldSet({
    title: RM.Translate.Admin.Reservations.Edit.PaymentsReceived,
    autoHeight: true,
    layout: 'form',
    bodyBorder : false,
    html: ""
});

RM.Pages.Reservations_Edit_Add_Payment_Window = new Ext.Window({
    id: 'rm_reservation_edit_add_payment_window',
    title: RM.Translate.Admin.Reservations.Edit.AddPayment,
    layout: 'fit',
    width: 400,
    height: 200,
    autoDestroy: true,
    closeAction : 'hide',
    items: [{
        xtype: 'form',
        labelWidth: 150,
        bodyStyle : "padding:10px;",
        id: 'rm_reservation_edit_add_payment_form',
        url: RM.Common.AssembleURL({
            controller : 'Reservations',
            action: 'addpaymentJson'
        }),
        items: [{
            xtype: 'textfield',
            name: 'provider',
            value: 'Administrator',
            fieldLabel: 'Provider'
        },{
            xtype: 'textfield',
            name: 'transaction_id',
            value: '0',
            fieldLabel: 'Transaction ID'
        },{
            xtype: 'xdatetime',
            name: 'transaction_date',
            allowBlank: false,
            fieldLabel: 'Transaction date',
            dateFormat: RM.Common.GUIDateFormat,
            timeFormat: 'H:i',
            value: new Date().format(RM.Common.GUIDateFormat)
        },{
            xtype: 'numberfield',
            name: 'total',
            allowBlank: false,
            allowNegative: false,
            fieldLabel: 'Total'
        }],
        buttons: [{
            text: RM.Translate.Common.Add,
            handler: function(){
                if (Ext.getCmp('rm_reservation_edit_add_payment_form').getForm().isValid() === false) {
                    return false;
                }
                RM.Pages.Reservations_Edit_Add_Payment_Window.hide();
                var myMask = new Ext.LoadMask('content-panel', {msg:RM.Translate.Common.PleaseWait});
                myMask.show();
                Ext.getCmp('rm_reservation_edit_add_payment_form').getForm().submit({
                    params: {
                        reservation_id: RM.Pages.Reservations_Edit_ID
                    },
                    success: function(form, action){
                        myMask.hide();
                        Ext.getCmp('rm_pages_reservations_edit_statusbar').setStatus({
                            text: RM.Translate.Admin.Reservations.Edit.AddPaymentSuccessfully,
                            iconCls: 'ok-icon'
                        });
                        RM.Pages.Functions.Reservations_EditJson_Request(RM.Pages.Reservations_Edit_ID);
                    },
                    failure: function(form, action){
                        myMask.hide();
                        switch (action.failureType) {
                            case Ext.form.Action.CONNECT_FAILURE:
                                Ext.getCmp('rm_pages_reservations_edit_statusbar').setStatus({
                                    iconCls: 'failed-icon',
                                    text: RM.Translate.Common.ErrorSaving
                                });
                                break;
                            case Ext.form.Action.SERVER_INVALID:
                                Ext.getCmp('rm_pages_reservations_edit_statusbar').setStatus({
                                    iconCls: 'failed-icon',
                                    text: action.result.msg
                                });
                                break;
                        }
                    }
                });
            }
        },{
            text: RM.Translate.Common.Cancel,
            handler: function(){
                RM.Pages.Reservations_Edit_Add_Payment_Window.hide();
            }
        }]
    }]
});

RM.Pages.Reservations_Edit_EditTotal_Window = new Ext.Window({
    id: 'rm_reservation_edit_edittotal_window',
    title: RM.Translate.Admin.Reservations.Edit.EditTotal,
    layout: 'fit',
    width: 400,
    height: 200,
    closeAction : 'hide',
    items: [{
        xtype: 'form',
        labelWidth: 150,
        bodyStyle : "padding:10px;",
        id: 'rm_reservation_edit_edit_total_form',
        url: RM.Common.AssembleURL({
            controller : 'Reservations',
            action: 'edittotalJson'
        }),
        items: [{
            xtype: 'textfield',
            name: 'rm_reservation_edit_edit_total',
            id: 'rm_reservation_edit_edit_total',
            value: '',
            fieldLabel: 'Override Total'
        }],
        buttons: [{
            text: RM.Translate.Common.Save,
            handler: function(){
                if (Ext.getCmp('rm_reservation_edit_edit_total_form').getForm().isValid() === false) {
                    return false;
                }
                RM.Pages.Reservations_Edit_Add_Payment_Window.hide();
                Ext.getCmp('rm_reservation_edit_edit_total_form').getForm().submit({
                    params: {
                        reservation_id: RM.Pages.Reservations_Edit_ID
                    },
                    success: function(form, action){
                        RM.Pages.Functions.Reservations_EditJson_Request(RM.Pages.Reservations_Edit_ID);
                        RM.Pages.Reservations_Edit_EditTotal_Window.hide();
                    },
                    failure: function(form, action){
                        myMask.hide();
                        switch (action.failureType) {
                            case Ext.form.Action.CONNECT_FAILURE:
                                Ext.getCmp('rm_pages_reservations_edit_statusbar').setStatus({
                                    iconCls: 'failed-icon',
                                    text: RM.Translate.Common.ErrorSaving
                                });
                                break;
                            case Ext.form.Action.SERVER_INVALID:
                                Ext.getCmp('rm_pages_reservations_edit_statusbar').setStatus({
                                    iconCls: 'failed-icon',
                                    text: action.result.msg
                                });
                                break;
                        }
                    }
                });
            }
        },{
            text: RM.Translate.Common.Cancel,
            handler: function(){
                RM.Pages.Reservations_Edit_EditTotal_Window.hide();
            }
        }]
    }],
    listeners:{
        show: function(){

            myMask = new Ext.LoadMask('rm_reservation_edit_edittotal_window', {msg:RM.Translate.Common.PleaseWait});
            myMask.show();

            var conn = new Ext.data.Connection();
            var request = {
                url: RM.Common.AssembleURL({
                    controller : 'Reservations',
                    action: 'getsavedpricesJson',
                    parameters : [{
                        name : 'reservation_id',
                        value : RM.Pages.Reservations_Edit_ID
                    }]
                }),
                success: function(responseObject) {
                    var data = Ext.util.JSON.decode(responseObject.responseText);
                    Ext.getCmp("rm_reservation_edit_edit_total").setValue(data.subtotal);
                    myMask.hide();
                }
            };
            conn.request(request);
        }
    }
});

RM.Pages.Reservations_Edit_Send_Invoice_Window = new Ext.Window({
    id: 'rm_reservation_edit_send_invoice_window',
    title: RM.Translate.Admin.Reservations.Edit.SendInvoice,    
    layout: 'fit',
    width: 550,
    height: 150,
    autoDestroy: true,
    closeAction : 'hide',
    items: [{
        xtype: 'form',
        id: 'rm_reservation_edit_send_invoice_form',
        labelWidth: 300,
        url: RM.Common.AssembleURL({
            controller : 'Reservations',
            action: 'emailinvoiceJson'
        }),
        items: [{
            id: "rm_reservation_edit_send_invoice_comfirm",
            xtype: "combo",
            name: "rm_reservation_edit_send_invoice_confirm",
            hiddenName : "rm_reservation_edit_send_invoice_confirm",
            forceSelection: true,
            typeAhead: true,
            triggerAction: 'all',
            selectOnFocus: true,
            store: [[0, RM.Translate.Common.MessageNo], [1, RM.Translate.Common.MessageYes]],
            fieldLabel: RM.Translate.Admin.Reservations.Edit.SendInvoiceByEmailToCustomer
        },{
            id: "rm_reservation_edit_send_invoice_email",
            xtype: "textfield",
            name: "rm_reservation_edit_send_invoice_email",
            fieldLabel: RM.Translate.Admin.Reservations.Edit.SendInvoiceToEmailAddress
        }],
        buttons: [{            
            text: RM.Translate.Admin.Reservations.Edit.Send,
            handler: function(){
                RM.Pages.Reservations_Edit_Send_Invoice_Window.hide();
                var myMask = new Ext.LoadMask('content-panel', {msg:RM.Translate.Common.PleaseWait});
                myMask.show();
                Ext.getCmp('rm_reservation_edit_send_invoice_form').getForm().submit({
                    params: {
                        reservation_id: RM.Pages.Reservations_Edit_ID
                    },
                    success: function(form, action){
                        myMask.hide();
                        Ext.getCmp('rm_pages_reservations_edit_statusbar').setStatus({
                            text: RM.Translate.Admin.Reservations.Edit.SendInvoiceSuccessfully,
                            iconCls: 'ok-icon'
                        });
                    },
                    failure: function(form, action){
                        myMask.hide();
                        switch (action.failureType) {
                            case Ext.form.Action.CONNECT_FAILURE:
                                Ext.getCmp('rm_pages_reservations_edit_statusbar').setStatus({
                                    iconCls: 'failed-icon',
                                    text: RM.Translate.Common.ErrorSaving
                                });
                                break;
                            case Ext.form.Action.SERVER_INVALID:
                                Ext.getCmp('rm_pages_reservations_edit_statusbar').setStatus({
                                    iconCls: 'failed-icon',
                                    text: action.result.msg
                                });
                                break;
                        }
                    }
                });
            }
        },{            
            text: RM.Translate.Common.Cancel,
            handler: function(){
                RM.Pages.Reservations_Edit_Send_Invoice_Window.hide();
            }
        }]
    }],
    listeners: {
        'beforeshow': function(){
            RM.Pages.Reservations_Edit_Send_Invoice_Window.center();
            Ext.getCmp('rm_reservation_edit_send_invoice_comfirm').setValue(1);
            Ext.getCmp('rm_reservation_edit_send_invoice_email').setValue('');
            return true;
        },
        'beforehide': function(){
            return true;
        }
    }
});

RM.Pages.Reservations_Edit_Billings_Tools_Fieldset = {
    xtype : "fieldset",
    title: RM.Translate.Admin.Reservations.Edit.BillingsTools,
    autoHeight: true,
    layout: 'column',
    bodyBorder : false,    
    items: [{
        xtype: 'button',
        text: RM.Translate.Admin.Reservations.Edit.SendInvoice,
        handler: function(){
            RM.Pages.Reservations_Edit_Send_Invoice_Window.show();
        }
    },{
        xtype: 'button',
        text: RM.Translate.Admin.Reservations.Edit.PrintInvoice,
        handler: function(){            
            var new_window = window.open(RM.Common.AssembleURL({
                controller : 'Reservations',
                action: 'printinvoicejson',
                parameters : [{
                    name : 'reservation_id',
                    value : RM.Pages.Reservations_Edit_ID
                }]
            }));
            new_window.print();
        }
    },{
        xtype: 'button',
        text: RM.Translate.Admin.Reservations.Edit.AddPayment,
        handler: function(){
            RM.Pages.Reservations_Edit_Add_Payment_Window.show();
        }
    },{
        xtype: 'button',
        text: RM.Translate.Admin.Reservations.Edit.EditTotal,
        handler: function(){
            RM.Pages.Reservations_Edit_EditTotal_Window.show();
        }
    }]
};

//BEGIN: last billings tab
RM.Pages.Reservations_Edit_Tab_Billing = {
    xtype : "panel",
    title : RM.Translate.Admin.Reservations.Edit.BillingsAndPayments,
    id : "rm_pages_reservations_edit_tab_billing",
    autoScroll: true,
    bodyStyle : "padding:20px",
    items: [
        RM.Pages.Reservations_Edit_Billings_Tools_Fieldset,
        RM.Pages.Reservations_Edit_Billings_Summary_Fieldset,
        RM.Pages.Reservations_Edit_Billings_Payments_Fieldset        
    ]
};
// billings and payments end
//END: last billings tab

//BEGIN: reservation detail tab (JSON object with configuration)

// START: reservation details
//
// this splits the left and right columns...
RM.Pages.Reservations_Details_Left_Column = {
    region:'center',
    autoScroll: true,
    id: 'rm_pages_reservations_edit_details_left_col_',
    bodyStyle : "padding:10px",
    items : []
};
RM.Pages.Reservations_Details_Right_Column = {
    region : "east",
    id: 'rm_pages_reservations_edit_details_right_col_',
    bodyStyle : "padding:10px",
    autoScroll: true,
    collapsed: false,
    collapsible: true,
    width: 250,
    items : []
};

// this is the top area that displays the current booked dates.
RM.Pages.Reservations_Details_Selected_Dates = {
    xtype : "fieldset",
    title: RM.Translate.Admin.Reservations.Edit.CurrentSelectedDates,
    autoHeight: true,
    layout: 'form',
    autoWidth: true,
    id : "rm_pages_reservations_edit_selecteddates_",
    bodyBorder : false
};

// this is the right column which shows the selected days time breakdown
RM.Pages.Reservations_Details_TimeBreakDown = {
    xtype : "fieldset",
    id: 'rm_pages_reservations_edit_details_timebreakdown_',
    title: RM.Translate.Admin.Reservations.Edit.TimeBreakDown,
    autoHeight: true,
    autoWidth: true,
    bodyBorder : false,
    html: RM.Translate.Admin.Reservations.Edit.CalendarHoverHelp
};

RM.Pages.Reservation_Details_Start_Date = {
    fieldLabel: RM.Translate.Common.StartDate,
    //width : 190,
    format: RM.Common.GUIDateFormat,
    xtype: "datefield",
    listeners: {
        'blur' : function(){
            var unitid = this.id.replace("reservation_edit_start_date_time_","");
            unitid = unitid.replace("-date","");
            RM.Pages.Functions.Reservations_SummaryUpdate(unitid);
        }
    }
};
RM.Pages.Reservation_Details_End_Date = {
    fieldLabel: RM.Translate.Common.EndDate,
    //width : 190,
    format: RM.Common.GUIDateFormat,
    xtype: "datefield",
    listeners: {
        'blur' : function(){
            // the unit id is in the id of the formobj
            var unitid = this.id.replace("reservation_edit_end_date_time_","");
            unitid = unitid.replace("-date","");
            RM.Pages.Functions.Reservations_SummaryUpdate(unitid);
        }
    }
};
RM.Pages.Reservation_Details_Start_DateTime = {
    fieldLabel: RM.Translate.Common.StartDatetime,
    width : 250,
    dateFormat: RM.Common.GUIDateFormat,
    xtype: "xdatetime",
    timeFormat:'H:i',
    timeWidth: 120,
    listeners: {
        'blur' : function(){
            // the unit id is in the id of the formobj
            var unitid = this.id.replace("reservation_edit_start_date_time_","");
            unitid = unitid.replace("-date","");
            RM.Pages.Functions.Reservations_SummaryUpdate(unitid);
        }
    }
};
RM.Pages.Reservation_Details_End_DateTime = {
    fieldLabel: RM.Translate.Common.EndDatetime,    
    width : 250,
    dateFormat: RM.Common.GUIDateFormat,
    xtype: "xdatetime",
    timeFormat:'H:i',
    timeWidth: 120,
    listeners: {
        'blur' : function(){
            // the unit id is in the id of the formobj
            var unitid = this.id.replace("reservation_edit_end_date_time_","");
            unitid = unitid.replace("-date","");
            RM.Pages.Functions.Reservations_SummaryUpdate(unitid);
        }
    }
};
RM.Pages.Reservation_Details_Update_Button = {
    xtype: 'panel',
    border: false,
    frame: false,
    html: '<div class="RM_Reservation_Edit_Refresh_Icon"><a href="javascript:void(0)"><img src="'+RM.BaseSmallImageURL+'clear.gif" border="0">&nbsp;'+RM.Translate.Admin.Reservations.Edit.RefreshPreview+'</a></div>'
};


// the calendar
RM.Pages.Reservations_Details_Info_Calendar = {
    id: "rm_pages_reservations_edit_calendar_", //we need to add here a unit_id
    defaults: {anchor:'100%'},
    xtype: 'rmcalendar',
    title : RM.Translate.Common.Calendar,
    allowBlank: true,
    readOnly: true,
    disableMonthPicker: true,
    showActiveDate: false,
    format: 'Y-m-d',
    noOfMonth : 3,
    summarizeHeader : true,
    noOfMonthPerRow : 3,    
    multiSelection: false,
    multiSelectByCTRL: false,
    showWeekNumber: false,
    useQuickTips: false,
    renderOkUndoButtons: false,
    markNationalHolidays: false,
    styleDisabledDates: true,
    eventDatesSelectable: false,
    listeners: {
        'mouseover': function(d){
            var unitid = this.id.replace("rm_pages_reservations_edit_calendar_","");
            RM.Pages.Reservations_Edit_Timer.Set(d, unitid);
        }
    }
};

RM.Pages.Reservations_Details_Calendar_Container = {
    xtype : "fieldset",
    title: RM.Translate.Common.Calendar,
    autoHeight: true,
    layout: 'form',
    id : "rm_pages_reservations_edit_calendar_area_",
    bodyBorder : false,
    collapsible: true,
    collapsed: true,
    items: []
};

// live price calculation summary, this is used when new dates are selected.
// it will show the new reservation total
RM.Pages.Reservations_Details_Reservation_Summary  = {
    xtype : "fieldset",
    id: "rm_pages_reservations_edit_newselectedsummary_",
    title: RM.Translate.Admin.Reservations.Edit.NewSelectionSummary,
    autoHeight: true,
    layout: 'form',
    bodyBorder : false,
    html: RM.Translate.Admin.Reservations.Edit.NoNewSelectedDates
};

// this contains all the items on the unit reservation tab..
RM.Pages.Reservations_Details_Page_Container = {
    id: "rm_pages_reservation_edit_unit_details_",
    xtype : "panel",
    layout:'border',
    defaults: {
        split: true
    },
    autoScroll : true,
    containerScroll : true,
    bodyBorder : false,
    frame : false,
    bodyStyle : "padding:10px",
    items : []
};
//END: reservation detail tab

RM.Pages.Reservations_Edit_TabPanel = new Ext.TabPanel({
    id: "rm_pages_reservations_edit_tab",
    activeTab : 0,
    height: RM.Common.GetPanelHeight(82),
    bodyBorder : false,
    frame : false,
    items : [RM.Pages.Reservations_Edit_Tab_1],
    bbar: new Ext.ux.StatusBar({
        id: 'rm_pages_reservations_edit_statusbar',
        items: []
    }),
    autoDestroy: true
});

RM.Pages.Reservations_Edit_ID = 0;
RM.Pages.Functions.Reservations_EditJson_Request = function(reservation_id){
    RM.Pages.Reservations_Edit_ID = reservation_id;
    var myMask = new Ext.LoadMask('content-panel', {msg:RM.Translate.Common.PleaseWait});
    myMask.show();
    var conn = new Ext.data.Connection();
    var request = {
        url: RM.Common.AssembleURL({
            controller : 'Reservations',
            action: 'editjson',
            parameters : [{
                name : 'id',
                value : reservation_id
            }]
        }),
        method: 'POST',
        success: function(responseObject) {
            eval('RM.Pages.Functions.Reservations_EditJson('+responseObject.responseText+');');
            myMask.hide();
        },
        failure: function() {
            Ext.Msg.alert(RM.Translate.Common.Status, RM.Translate.Common.UnableToShow);
            myMask.hide();
        }
    };
    conn.request(request);
};

RM.Pages.Reservations_Edit_ReservationID = 0;
RM.Pages.Reservations_Edit_UnitIDs = [];
RM.Pages.Functions.Reservations_EditJson = function (responseObject) {
    RM.Pages.Reservations_Edit_UnitIDs = [];
    RM.Pages.Reservations_Edit_ReservationID = responseObject.data.id;    
    RM.Pages.Reservations_Edit_Reservation_UserID = responseObject.user.id;

    var tabPanel = Ext.getCmp("rm_pages_reservations_edit_tab");
    if(tabPanel.items !== undefined){
        tabPanel.items.each(function(item){
            if (item.id.search('rm_pages_reservation_edit_unit_details_') !== -1) {
                tabPanel.remove(item, true);
            }            
        });
    }
    tabPanel.doLayout();

    RM.Pages.Reservations_Edit_UserAssignment.reload({
        callback : function(){
            if (responseObject.user){
                Ext.getCmp('edit_reservation_user_id').setValue(responseObject.user.id);
                Ext.getCmp('edit_reservation_user_id').setRawValue(responseObject.user.last_name+", "+responseObject.user.first_name);
            }
        }
    });

    //Create billings and Payments
    RM.Pages.Reservations_Edit_Billings_Summary_Fieldset.doLayout();
    tabPanel.add(new Ext.Panel(RM.Pages.Reservations_Edit_Tab_Billing)).show();
    
    Ext.getCmp('rm_pages_reservations_edit_tab_billing').doLayout(); // call doLayout so it's rendered    
    RM.Pages.Reservations_Edit_Billings_Summary_Fieldset.body.update(
        RM.Pages.Reservations_Edit_Billings_Summary_Template.applyTemplate(responseObject.pricedata)
    );
    RM.Pages.Reservations_Edit_Billings_Payments_Fieldset.body.update(
        RM.Pages.Reservations_Edit_Billings_Payments_Template.applyTemplate(responseObject.paymentdata)
    );
    
    //Add information on the summary tab
    RM.Pages.Reservations_Edit_General_Summary_Fieldset.body.update(
        RM.Pages.Reservations_Edit_General_Summary_Template.applyTemplate(responseObject.pricedata)
    );

    //Get information about every details
    var i = 0;for (i; i < responseObject.details.length; i++){
        RM.Pages.Reservations_Edit_UnitIDs.push(responseObject.details[i].unit.id);
        RM.Pages.Functions.Reservations_EditJson_ParseDetail(responseObject.details[i]);
    }

    //Create inputs and assign a value for them
    RM.Pages.Functions.Reservations_EditJson_ParseFields(responseObject.fields, responseObject.data);

    Ext.getCmp('toolbar_panel').layout.setActiveItem('rm_pages_reservations_edit_toolbar');
    Ext.getCmp('content-panel').layout.setActiveItem('rm_pages_reservations_edit');

    // BEGIN: help
    Ext.getCmp("rm_pages_reservations_edit_tab_billing").on('afterlayout', function(){
        RM.Help.Load('Admin.Reservation.Edit.BillingTab');
        return true;
    });
    // END: help

    // set the general tab as the current tab
    RM.Pages.Reservations_Edit_TabPanel.setActiveTab("rm_pages_reservations_edit_tab_1");
    Ext.getCmp('rm_pages_reservations_edit_tab_1').doLayout();

    // help for tab1
    Ext.getCmp('rm_pages_reservations_edit_tab_1').on('afterlayout', function(){
        RM.Help.Create('rm_pages_reservations_edit_common_info', 'Admin.Reservation.Edit.CommonInfo');
        RM.Help.Create('rm_reservations_edit_general_summary_fieldset', 'Admin.Reservation.Edit.Summary');
        return true;
    });

    if (responseObject.user){
        Ext.getCmp('edit_reservation_user_id').setValue(responseObject.user.id);
        Ext.getCmp('edit_reservation_user_id').setRawValue(responseObject.user.last_name+", "+responseObject.user.first_name);
    }

    Ext.getCmp('rm_pages_reservations_edit').doLayout();
};

RM.Pages.Functions.Reservations_EditJson_ParseFields = function (fields, data) {
    var hiddenInputs = [];

    //1.Create all inputs for reservation information
    var availableFieldsIDs = [];
    var i = 0;for (i; i < fields.length; i++){
        var field = fields[i];        
        field.view.id = 'edit_' + field.view.id;
        field.view.name = 'edit_' + field.view.name;
        field.parent_id = 'rm_pages_reservations_edit_' + field.parent_id;
        if (field.view.hiddenName) {
            var re_Query = new RegExp(/\[(.*)\]/); // load the name in the [ ] brackets
            var ActualHiddenName = field.view.hiddenName.match(re_Query)[1];
            hiddenInputs.push(ActualHiddenName);
            field.view.hiddenName = 'edit_' + field.view.hiddenName;
            availableFieldsIDs.push(field.view.hiddenName);
        } else {
            availableFieldsIDs.push(field.view.name);
        }

        RM.Common.RemoveOldFields(field.view.id);

        eval("Ext.getCmp(field.parent_id).add(new "+field.class_name+"(field.view));");
    }

    //2.Assign data to reservation inputs
    var field_name;for (field_name in data) {
        var name = 'edit_reservation['+field_name+']';
        var name_tmp = 'edit_reservation['+field_name+']';
        if(RM.Common.InArray(field_name, hiddenInputs) === true) {
            name = 'edit_reservation_'+field_name;
        }
        if (RM.Common.InArray(name_tmp, availableFieldsIDs)) {
            eval('var field_value = data.'+field_name+';');
            Ext.getCmp(name).setValue(field_value);
        }
    }
};

/**
 * @param details object in format
 *
 * details.unit.id int
 * details.unit.name string
 * details.reserved_period array with objects: {start: <start_data>, end: <end_date>}
 * details.blocked_periods array with objects: {start: <start_data>, end: <end_date>} 
 */
RM.Pages.Functions.Reservations_EditJson_ParseDetail = function(details){

    var selectedDatesConfig = RM.Common.Clone(RM.Pages.Reservations_Details_Selected_Dates);
    selectedDatesConfig.id += details.unit.id;

    var calendarConfig = RM.Common.Clone(RM.Pages.Reservations_Details_Info_Calendar);
    calendarConfig.id += details.unit.id;

    var newselectionSummaryConfig = RM.Common.Clone(RM.Pages.Reservations_Details_Reservation_Summary);
    newselectionSummaryConfig.id += details.unit.id;

    var calendarContainerConfig = RM.Common.Clone(RM.Pages.Reservations_Details_Calendar_Container);
    calendarContainerConfig.id += details.unit.id;

    calendarContainerConfig.items = [
        calendarConfig
    ];
    
    var detailsTimeBreakDown = RM.Common.Clone(RM.Pages.Reservations_Details_TimeBreakDown);
    detailsTimeBreakDown.id += details.unit.id;

    var detailsLeftColumn = RM.Common.Clone(RM.Pages.Reservations_Details_Left_Column);
    detailsLeftColumn.id += details.unit.id;

    /**
     * price system person selection.
     *
     * This loads a the form included in reservation_edit.js for each price system
     */
    var priceSystemPanel = false;
    eval ("priceSystemPanel = RM.Pages.Functions."+details.pricesystem+"_ReservationEdit("+details.unit.id+",'rm_pages_reservation_edit_unit_'+details.unit.id+'_', details);");


    if (priceSystemPanel){
        detailsLeftColumn.items = [
            selectedDatesConfig,
            priceSystemPanel,
            calendarContainerConfig,
            newselectionSummaryConfig
        ];
    } else {
        detailsLeftColumn.items = [
            selectedDatesConfig,
            calendarContainerConfig,
            newselectionSummaryConfig
        ];
    }

    /**
     * This will call the other systems unit specific reservation edit panels.
     */
    try {
    var otherSystem = false;
    var os = 0;for (os; os < details.othersystems.length; os++ ){
        eval ("otherSystem = RM.Pages.Functions."+details.othersystems+"_ReservationEdit("+details.unit.id+",'rm_pages_reservation_pudo_edit_unit_'+details.unit.id+'_', details);");
        if (otherSystem) {
            detailsLeftColumn.items.push(otherSystem);
        }
    }
    } catch (e){}

    var detailsRightColumn = RM.Common.Clone(RM.Pages.Reservations_Details_Right_Column);
    detailsRightColumn.id += details.unit.id;

    detailsRightColumn.items = [
        detailsTimeBreakDown
    ];

    var panelConfig = RM.Common.Clone(RM.Pages.Reservations_Details_Page_Container);
    panelConfig.id += details.unit.id;
    panelConfig.title = RM.Translate.Common.Unit+": "+details.unit.name;
    panelConfig.items = [
        detailsLeftColumn,
        detailsRightColumn
    ];
    panelConfig.closable = true;
    
    // get the form items for this tab
    if (details.showtime === 0){
        var startDate_formObj = RM.Common.Clone(RM.Pages.Reservation_Details_Start_Date);
    } else {
        var startDate_formObj = RM.Common.Clone(RM.Pages.Reservation_Details_Start_DateTime);
    }

    startDate_formObj.id = "reservation_edit_start_date_time_"+details.unit.id;
    startDate_formObj.value = RM.Common.ConvertToDate(details.reserved_period.start_date);

    if (details.showtime === 0){
        var endDate_formObj = RM.Common.Clone(RM.Pages.Reservation_Details_End_Date);        
    } else {
        var endDate_formObj = RM.Common.Clone(RM.Pages.Reservation_Details_End_DateTime);
    }
    endDate_formObj.id = "reservation_edit_end_date_time_"+details.unit.id;
    endDate_formObj.value = RM.Common.ConvertToDate(details.reserved_period.end_date);
    
    selectedDatesConfig.items = [
        startDate_formObj,
        endDate_formObj,
        RM.Pages.Reservation_Details_Update_Button
    ];

    Ext.getCmp("rm_pages_reservations_edit_tab").add(new Ext.Panel(panelConfig));

    Ext.getCmp(panelConfig.id).on('beforeclose', function(){        
        Ext.MessageBox.confirm(
            RM.Translate.Admin.Reservations.Edit.UnitAssigment,
            RM.Translate.Admin.Reservations.Edit.ConfirmRemoveUnit,
            function(buttonID, tab){
                if (buttonID === 'yes') {
                    //Ext.getCmp(panelConfig.id).disable();
                    var myMask = new Ext.LoadMask(
                        Ext.getCmp(panelConfig.id).id,
                        {
                            msg: RM.Translate.Admin.Reservations.Edit.UnitRemovedMessage,
                            msgCls: "RM_Mask_Msg_NoIcon"
                        }
                    );
                    myMask.show();

                    // add the unit id to the don't save array, then when click is saved
                    // we will not save this unit to the reservation
                    var unitid = panelConfig.id.replace("rm_pages_reservation_edit_unit_details_","");
                    RM.Pages.Reservations_Edit_UnitsRemoved.push(unitid);
                    return false;
                }
            }
        );
        return false; // if this is not here then the tab will be removed
    });

    //2. add into calendar reserved dates
    var calendar = Ext.getCmp(calendarConfig.id);
    calendar.clearView();
    calendar.addEventPeriod(details.reserved_period);

    //3. add into calendar reserved dates to other reservations
    calendar.setDisabledPeriods(details.blocked_periods);
};

RM.Pages.Functions.Reservations_SummaryUpdate = function(unitID) {
    //1. add ajax request to reservation controller to get price for input period and unit
    var periods = RM.Pages.Functions.Reservations_Edit_Get_Unit_Period(unitID);
    var persons = RM.Pages.Functions.Reservations_Edit_Get_Unit_Persons(unitID);

    var otherInfoJson = RM.Pages.Functions.Reservations_getOtherInfoItems(unitID);

    if (periods === false) { return; }

    var conn = new Ext.data.Connection();
    var request = {
        url: RM.Common.AssembleURL({
            controller : 'Reservations',
            action: 'getpriceJson'
        }),
        method: 'POST',
        params: {
            periods: Ext.util.JSON.encode(periods),
            persons: Ext.util.JSON.encode(persons),
            unit_id: unitID,
            otherinfo: Ext.util.JSON.encode(otherInfoJson)
        },
        success: function(responseObject) {
            var jsonObject = RM.Common.JSON.decode(responseObject.responseText, true);
            var newSelectedSummary = Ext.getCmp('rm_pages_reservations_edit_newselectedsummary_'+unitID);
            try{
                if (jsonObject.success === true ){
                    newSelectedSummary.body.update(
                        RM.Pages.Reservations_Edit_NewSelectionSummary_Template.applyTemplate(jsonObject)
                    );
                } else {
                    newSelectedSummary.body.update(
                        jsonObject.error
                        //RM.Translate.Admin.Reservations.Edit.NoNewSelectedDates
                    );
                }
            } catch (e){}
        }
    };
    conn.request(request);
};

RM.Pages.Functions.Reservations_ShowBreakdown = function(selectedDate, unitID){
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
            var timebreakdown = Ext.getCmp('rm_pages_reservations_edit_details_timebreakdown_'+unitID);
            if (jsonObject.data !==undefined ){
                timebreakdown.body.update(
                    RM.Pages.Reservations_Edit_TimeBreakdown_Template.applyTemplate(jsonObject)
                );
            } else {
                timebreakdown.body.update(
                    RM.Translate.Admin.Reservations.Edit.NoReservationsForSelectedDay
                );
            }
        }
    };
    conn.request(request);
};

RM.Pages.Reservations_Edit = new Ext.Panel({
    id : "rm_pages_reservations_edit",
    items : [
        RM.Pages.Reservations_Edit_TabPanel
    ],
    listeners: {
        'show' : function(){
            this.doLayout(false,true);
        }
    }
});

RM.Main.Pages.push(RM.Pages.Reservations_Edit);