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
* New Reservation JS
*
* JSLint.com Check: 05/07/2011
*
* @access       public
* @author       Rob
* @copyright    2011 ResMania Ltd.
* @version      1.2
* @link         http://docs.resmania.com/api/
* @since        06-2011
*/

// global to store the new reservation ID
RM.Pages.Reservations_New_ReservationID = "";

// back button handler
RM.Pages.Functions.Reservations_New_Back = function () {
    RM.Pages.Functions.Reservations_ListJson({});
};

// refresh button handler
RM.Pages.Functions.Reservations_New_Refresh = function () {
    RM.Pages.Reservations_New_store.reload();
};

// toolbar
RM.Pages.Reservations_New_Toolbar = {
    xtype : "panel",
    id : "rm_pages_reservations_new_toolbar",
    bodyBorder : false,
    html : RM.Common.getToolbar([
    {
        image: RM.BaseLargeImageURL + "back.gif",
        label: RM.Translate.Common.Back,
        link: "RM.Pages.Functions.Reservations_New_Back()"
    },

    {
        image: RM.BaseLargeImageURL + "clear.gif",
        label: RM.Translate.Common.Refresh,
        link: "RM.Pages.Functions.Reservations_New_Refresh()"
    }
    ])
};
RM.Toolbars.push(RM.Pages.Reservations_New_Toolbar);

var today = new Date().clearTime();

Ext.Msg.minWidth = 300;

// data for the user selection combo
RM.Pages.Reservations_New_userComboData = new Ext.data.JsonStore({
    root: 'data',
    totalProperty: 'total',
    fields: [
    {
        name: 'id',
        type:'int'
    },

    {
        name: 'last_name',
        type:'string'
    },

    {
        name: 'first_name',
        type:'string'
    }
    ],
    url: RM.Common.AssembleURL({
        controller: 'Users',
        action: 'listJson'
    })
});
RM.Pages.Reservations_New_userComboData.load(); // preload

// get the calendar JSON (this is the unit calendars)
RM.Pages.Reservations_New_calendarStore = new Ext.data.JsonStore({
    storeId: 'calendarStore',
    url: RM.Common.AssembleURL({
        controller: 'Reservations_New',
        action: 'getcalendarsJson'
    }),
    root: 'calendars',
    idProperty: Ext.ensible.cal.CalendarMappings.CalendarId.mapping || 'id',
    fields: Ext.ensible.cal.CalendarRecord.prototype.fields.getRange(),
    remoteSort: true,
    sortInfo: {
        field: Ext.ensible.cal.CalendarMappings.Title.name,
        direction: 'ASC'
    }
});
RM.Pages.Reservations_New_calendarStore.load(); // preload

// setup the http proxy for the CRUD functions
RM.Pages.Reservations_New_proxy = new Ext.data.HttpProxy({
    disableCaching: false, // no need for cache busting when loading via Ajax
    api: {
        read: RM.Common.AssembleURL({
            controller: 'Reservations_New',
            action: 'getdataJson'
        }),
        create: RM.Common.AssembleURL({
            controller: 'Reservations_New',
            action: 'insertJson'
        }),
        update: RM.Common.AssembleURL({
            controller: 'Reservations_New',
            action: 'updateJson'
        }),
        destroy: {
            url:  RM.Common.AssembleURL({
                controller: 'Reservations_New',
                action: 'deleteJson'
            })
        }
    },
    listeners: {
        exception: function(proxy, type, action, o, res, arg){
            var msg = res.message ? res.message : Ext.decode(res.responseText).message;
            RM.Common.Message.msg(RM.Translate.Common.Error, msg, 8,  "RM_MessagePopup" );
        }
    }
});

/**
 * Add the user selection ID to the EventMapping
 */
Ext.ensible.cal.EventMappings.UserSelectionID = {
    name: 'UserSelectionID',
    mapping: 'uid',
    type: 'int'
};
Ext.ensible.cal.EventRecord.reconfigure();

RM.Pages.Reservations_New_reader = new Ext.data.JsonReader({
    totalProperty: 'total',
    successProperty: 'success',
    idProperty: 'id',
    root: 'data',
    messageProperty: 'message',
    fields: Ext.ensible.cal.EventRecord.prototype.fields.getRange()
});

RM.Pages.Reservations_New_writer = new Ext.data.JsonWriter({
    encode: true,
    writeAllFields: true
});

RM.Pages.Reservations_New_store = new Ext.ensible.cal.EventStore({
    id: 'event-store',
    restful: false,
    proxy: RM.Pages.Reservations_New_proxy,
    reader: RM.Pages.Reservations_New_reader,
    writer: RM.Pages.Reservations_New_writer,
    autoLoad: true,
    listeners: {
        'write': function (store, action, data, resp, rec) {
            switch(action){
                case 'create':
                    RM.Common.Message.msg(RM.Translate.Common.Success, RM.Translate.Admin.Reservations.New.SavedOK, 8,  "RM_MessagePopup" );
                    this.reload();
                    break;
                case 'update':
                    RM.Common.Message.msg(RM.Translate.Common.Success, RM.Translate.Admin.Reservations.New.UpdatedOK, 8,  "RM_MessagePopup" );
                    this.reload();
                    break;
                case 'destroy':
                    RM.Common.Message.msg(RM.Translate.Common.Success, RM.Translate.Admin.Reservations.New.DeletedOK, 8,  "RM_MessagePopup" );
                    this.reload();
                    break;
            }
        }
    }
});


/**
 * New User Window
 */
RM.Pages.Functions.Reservations_New_Users_New = function () {
    Ext.Ajax.request({
        url: RM.Common.AssembleURL({
            controller : 'Users',
            action: 'newJson'
        }),
        method: 'POST',
        success: function(responseObject) {
            eval('RM.Pages.Functions.Reservations_New_Users_Json(' + responseObject.responseText + ');');
        },
        failure: function() {
            Ext.Msg.alert('Status', RM.Translate.Common.UnableToShow);
        }
    });
};

RM.Pages.Functions.Reservations_New_Users_Save = function () {
    RM.Pages.Reservations_New_User_Window.disable();
    Ext.getCmp('rm_reservation_new_users_form').getForm().submit({
        success: function(form, action) {
            
            // reload the user combo selection data...
            RM.Pages.Reservations_New_userComboData.reload();

            // set the user ID
            Ext.getCmp("rm_pages_reservations_new_eventwindow_userID").setValue(action.result.id);

            var userIDcombo = Ext.getCmp("rm_pages_reservations_new_eventwindow_userID");

            // update the combo text value
            var index = RM.Pages.Reservations_New_userComboData.find('id',action.result.id);
            var comboCurrentData = RM.Pages.Reservations_New_userComboData.getAt(index);
            if (comboCurrentData !== undefined){
                userIDcombo.setRawValue(comboCurrentData.data.last_name + ', ' + comboCurrentData.data.first_name);
            }

            // hide the Window
            RM.Pages.Reservations_New_User_Window.hide();
            RM.Pages.Reservations_New_User_Window.enable();
            
        },
        failure: function(form, action) {
            RM.Pages.Reservations_New_User_Window.enable();
            switch (action.failureType) {
                case Ext.form.Action.CLIENT_INVALID:
                    Ext.Msg.alert(RM.Translate.Admin.Users.New.Failure, RM.Translate.Admin.Users.New.WrongFormValues);
                    break;
                case Ext.form.Action.CONNECT_FAILURE:
                    Ext.Msg.alert(RM.Translate.Admin.Users.New.Failure, RM.Translate.Admin.Users.New.AjaxFailed);
                    break;
                case Ext.form.Action.SERVER_INVALID:
                    Ext.Msg.alert(RM.Translate.Admin.Users.New.Failure, action.result.msg);
                    break;
            }
        },
        waitMsg: RM.Translate.Common.Saving,
        waitTitle: RM.Translate.Common.PleaseWait
    });
};

RM.Pages.Functions.Reservations_New_Users_Json = function (responseObject) {
    var field = {};
    var i;
    for (i = 0; i < responseObject.fields.length; i++){
        field = responseObject.fields[i];
        field.view.id = 'new_wizard_' + field.view.id;
        field.view.name = 'new_wizard_' + field.view.name;
        field.view.hiddenName = 'new_wizard_' + field.view.hiddenName;
        field.parent_id = 'rm_pages_reservation_new_users_' + field.parent_id;
        RM.Common.RemoveOldFields(field.view.id);
        eval("Ext.getCmp(field.parent_id).add(new "+field.class_name+"(field.view))");
    }
    RM.Pages.Users_New_Groups.reload();
    RM.Pages.Reservations_New_User_Window.center();
    RM.Pages.Reservations_New_User_Window.setPagePosition(230, 130);
    RM.Pages.Reservations_New_User_Window.show();
};

RM.Pages.Functions.Reservations_New_GetTimes = function (unitID) {
    var request = {
        url: RM.Common.AssembleURL({
            controller : 'Reservations_New',
            action: 'getdefaulttimesJson',
            parameters : [{
                name : 'uid',
                value : unitID
            }]
        }),
        method: 'POST',
        success: function(responseObject) {
            var data = Ext.util.JSON.decode(responseObject.responseText);
            if (data.defaultstarttime !== "00:00 PM" || data.defaultendtime !== "00:00 AM"){
            var drf = Ext.getCmp('rm_pages_reservations_new_eventwindow_reservationDateRange');
                drf.startTime.setValue(data.defaultstarttime);
                drf.endTime.setValue(data.defaultendtime);
                Ext.getCmp("rm_pages_reservations_new_eventwindow_reservationDateRange-allday").setValue(false);
                Ext.getCmp("rm_pages_reservations_new_eventwindow_mainpanel").doLayout();
            } else {
                Ext.getCmp("rm_pages_reservations_new_eventwindow_reservationDateRange-allday").setValue(true);
            }
        },
        failure: function() {
            Ext.Msg.alert('Status', RM.Translate.Common.UnableToShow);
        }
    };

    var conn = new Ext.data.Connection();
    conn.request(request);
}

/**
 * Override event edit window
 */
Ext.override(Ext.ensible.cal.EventEditWindow,{

    // these are the translations (all may not be required)
    titleTextAdd: RM.Translate.Admin.Reservations.New.Add,
    titleTextEdit: RM.Translate.Admin.Reservations.New.Edit,
    detailsLinkText: RM.Translate.Admin.Reservations.New.EditDetails,
    savingMessage: RM.Translate.Admin.Reservations.New.Saving,
    deletingMessage: RM.Translate.Admin.Reservations.New.Deleting,
    saveButtonText: RM.Translate.Common.Save,
    deleteButtonText: RM.Translate.Common.Delete,
    cancelButtonText: RM.Translate.Common.Cancel,
    titleLabelText: RM.Translate.Admin.Reservations.New.Title,
    datesLabelText: RM.Translate.Admin.Reservations.New.When,
    calendarLabelText: RM.Translate.Common.Unit,

    // component initialisaton...
    initComponent: function () {

        this.addEvents({
            eventadd: true,
            eventupdate: true,
            eventdelete: true,
            eventcancel: true,
            editdetails: true
        });

        this.fbar = ['->',{
            id: 'rm_pages_reservations_new_eventwindow_edit_reservation_button',
            text:RM.Translate.Admin.Reservations.New.Edit,
            hidden: true,
            scope:this
        },{
            text:this.saveButtonText,
            disabled:false,
            handler:this.onSave,
            scope:this
        },{
            id:this.id+'-delete-btn',
            text:this.deleteButtonText,
            disabled:false,
            handler:this.onDelete,
            scope:this,
            hideMode:'offsets'
        },{
            text:this.cancelButtonText,
            disabled:false,
            handler:this.onCancel,
            scope:this
        }];

        Ext.ensible.cal.EventEditWindow.superclass.initComponent.call(this);
    },

    // on rendering of the component...
    onRender : function(ct, position) {

        this.deleteBtn = Ext.getCmp(this.id+'-delete-btn');

        this.titleField = new Ext.form.Hidden({
            id: 'rm_pages_reservations_new_eventwindow_reservationID',
            name: Ext.ensible.cal.EventMappings.Title.name
        });
        this.dateRangeField = new Ext.ensible.cal.DateRangeField({
            id: 'rm_pages_reservations_new_eventwindow_reservationDateRange',
            //labelStyle: 'width:110px',
            anchor: '95%',
            fieldLabel: this.datesLabelText,
            dateFormat: RM.Common.GUIDateFormat
        });
        
        var items = [this.titleField, this.dateRangeField];

        if(this.calendarStore){
            this.calendarField = new Ext.ensible.cal.CalendarCombo({
                name: Ext.ensible.cal.EventMappings.CalendarId.name,
                anchor: '100%',
                fieldLabel: this.calendarLabelText,
                store: this.calendarStore
            });

            this.calendarField.on("select",function(){
                RM.Pages.Functions.Reservations_New_GetTimes(this.value);
            });

            items.push(this.calendarField);
        }

        // this hidden field is just for storing the user ID as the combo submits
        // the text value, I think this is just an extjs quirk!?
        this.uid = new Ext.form.Hidden({
            id: 'rm_pages_reservations_new_eventwindow_userID',
            name: Ext.ensible.cal.EventMappings.UserSelectionID.name
        });
        items.push(this.uid);
        this.userSelection = new Ext.form.ComboBox({
            id: 'rm_pages_reservations_new_eventwindow_userID_combo',
            typeAhead: true,
            fieldLabel: RM.Translate.Admin.Reservations.New.User,
            triggerAction: 'all',
            ref: '../userCombobox',
            selectOnFocus: true,
            valueField: 'id',
            minChars: 1,
            pageSize: 10,
            resizable: true,
            minListWidth: 200,
            allowBlank: true,
            value: Ext.getCmp("rm_pages_reservations_new_eventwindow_userID").getValue(),
            emptyText: RM.Translate.Admin.Reservations.New.AssignSystem,
            store: RM.Pages.Reservations_New_userComboData,
            tpl:'<tpl for="."><div class="x-combo-list-item">{last_name}, {first_name} ({id})</div></tpl>',
            listeners:{
                select: function (combo, record, index) {
                    Ext.getCmp("rm_pages_reservations_new_eventwindow_userID").setValue(record.get('id'));
                    this.setRawValue(record.get('last_name') + ', ' + record.get('first_name'));
                },
                keypress:{
                    buffer:50,
                    fn:function () {
                        if(!this.getRawValue()) {
                            this.doQuery('', true);
                        }
                    }
                }
            }
        });

        // optional fieldset
        this.optionalFieldSet = new Ext.form.FieldSet({
            title: RM.Translate.Admin.Reservations.New.UserAssignment,
            layout: "column",
            bodyBorder : false,
            items:[
            new Ext.Panel({
                layout: 'form',
                width:350,
                border: false,
                items:[
                this.userSelection
                ],
                unstyled: true
            }),
            new Ext.Panel({
                width:100,
                border: false,
                bodyStyle : "padding:5px;",
                html: "<a href='javascript:RM.Pages.Functions.Reservations_New_Users_New();'>" + RM.Translate.Admin.Reservations.New.NewUser + "</a>",
                unstyled: true
            })
                
            ]
        });
        items.push(this.optionalFieldSet);

        // the main panel
        this.formPanel = new Ext.FormPanel({
            id: 'rm_pages_reservations_new_eventwindow_mainpanel',
            labelWidth: this.labelWidth,
            frame: false,
            bodyBorder: false,
            border: false,
            items: items
        });

        /**
         * Override the delete button function and use an ajax call
         * this is because for the calendar we have to use an int value for the id
         * this is a shortened version of the reservation id, but it is impossible
         * to link this back to the reservation so instead we use the value in the
         * titleField (rm_pages_reservations_new_eventwindow_reservationID)
         */
        Ext.getCmp(this.id+'-delete-btn').setHandler(function(){
            var conn = new Ext.data.Connection();
            var request = {
                url: RM.Common.AssembleURL({
                    controller : 'Reservations_New',
                    action: 'deleteJson'
                }),
                params: {
                    reservationID: RM.Pages.Reservations_New_ReservationID
                },
                method: 'POST',
                success: function (responseObject) {

                    RM.Pages.Reservations_New_store.reload();
                    RM.Common.Message.msg(RM.Translate.Common.Success, RM.Translate.Admin.Reservations.New.DeletedOK, 8,  "RM_MessagePopup" );
                    Ext.getCmp("ext-cal-editwin").hide();
                },
                failure: function () {
                    Ext.MessageBox.alert(RM.Translate.Common.DeleteFailure);
                }
            };
            conn.request(request);
        });

        this.add(this.formPanel);

        // we use the delete button to show the edit reservation button. the delete
        // button is only shown on exisiting reservations, so only show the edit button
        // at the same time.
        Ext.getCmp(this.id+'-delete-btn').on('show', function(){

            var button = Ext.getCmp("rm_pages_reservations_new_eventwindow_edit_reservation_button");
            
            button.setHandler(function () {
                RM.Pages.Functions.Reservations_EditJson_Request(RM.Pages.Reservations_New_ReservationID);

                // This creates a little window with a back button so that when
                // you edit the reservation you can choose to go back to the planner easily
                var backbuttonWindow = new Ext.Window({
                    id: "rm_pages_reservations_new_eventwindow_backtoplanner_button",
                    width: 120,
                    height: 70,
                    border: false,
                    bodyStyle : "padding:10px",
                    x: Ext.getCmp("root_gui_container").getWidth() - 200,
                    y: RM.Common.GetPanelHeight(100),
                    items: {
                        xtype: 'button',
                        text: RM.Translate.Admin.Reservations.New.BacktoPlanner,
                        handler: function () {
                            RM.Pages.Functions.Reservations_New();
                            Ext.getCmp("rm_pages_reservations_new_eventwindow_backtoplanner_button").close();
                        }
                    }
                });
                backbuttonWindow.show();
                Ext.getCmp("ext-cal-editwin").hide();
            });
            button.show();
            Ext.getCmp("rm_pages_reservations_new_eventwindow_mainpanel").doLayout();
        });

        // when the form is hidden hide the edit button
        Ext.getCmp(this.id+'-delete-btn').on('hide', function () {
            Ext.getCmp("rm_pages_reservations_new_eventwindow_edit_reservation_button").hide();
            Ext.getCmp("rm_pages_reservations_new_eventwindow_mainpanel").doLayout();
        });

        // on the window show event..
        this.on('show',function(){

            // set the window title...
            var titleExploded = RM.Common.explode(">",Ext.getCmp("rm_pages_reservations_new_eventwindow_reservationID").getValue());
            var titleExploded = RM.Common.explode("&nbsp;",titleExploded[1]);

            RM.Pages.Reservations_New_ReservationID = titleExploded[0];
            this.setTitle(RM.Pages.Reservations_New_ReservationID);

            // user combo
            RM.Pages.Reservations_New_userComboData.reload();

            // set the raw values
            var userIDcombo = Ext.getCmp("rm_pages_reservations_new_eventwindow_userID");
            var index = RM.Pages.Reservations_New_userComboData.find('id',userIDcombo.getValue());
            var comboCurrentData = RM.Pages.Reservations_New_userComboData.getAt(index);
            if (comboCurrentData !== undefined){
                userIDcombo.setRawValue(comboCurrentData.data.last_name + ', ' + comboCurrentData.data.first_name);
            } else {
                userIDcombo.setRawValue(RM.Translate.Admin.Reservations.New.AssignSystem);
            }

            RM.Pages.Functions.Reservations_New_GetTimes(1);
        });

        Ext.ensible.cal.EventEditWindow.superclass.onRender.call(this, ct, position);
    }
});

// the main calendar panel
RM.Pages.Reservations_New_cp = new Ext.ensible.cal.CalendarPanel({
    id: 'rm_pages_reservations_new_calendar',
    eventStore: RM.Pages.Reservations_New_store,
    calendarStore: RM.Pages.Reservations_New_calendarStore,
    title: RM.Translate.Admin.Reservations.New.Title
});

// the main function...
RM.Pages.Functions.Reservations_New = function () {

    RM.Pages.Reservations_New_calendarStore.reload();
    RM.Pages.Reservations_New_store.reload();

    Ext.getCmp('content-panel').layout.setActiveItem('rm_pages_reservations_new_calendar');
    Ext.getCmp('toolbar_panel').layout.setActiveItem('rm_pages_reservations_new_toolbar');
    RM.Help.Load('Admin.Reservations.New.Main');

    RM.Pages.Reservations_New_User_Window = new Ext.Window({
        id: 'rm_reservation_new_user_window',
        title : RM.Translate.Admin.Reservations.New.WizardCreateUser,
        layout: 'fit',
        width: 600,
        height: 400,
        renderTo: 'content-panel',
        modal: true,
        autoDestroy: true,
        closeAction: 'hide',
        items: [
        new Ext.FormPanel({
            id : "rm_reservation_new_users_form",
            width: 500,
            xtype : "form",
            bodyBorder : false,
            frame : false,
            autoScroll: true,
            url : RM.Common.AssembleURL({
                controller: 'Users',
                action: 'insertJson',
                parameters: [{
                    name: 'wizard',
                    value: 1
                }]
            }),
            items : [
            {
                xtype : "panel",
                layout: 'form',
                id : "rm_pages_reservation_new_users_tab_1",
                bodyStyle : "padding:10px;",
                items : [],
                labelWidth: 190,
                width: 490,
                bodyBorder : false,
                frame : false
            }
            ]
        })
        ],
        buttons: [
        {
            text: RM.Translate.Common.Save,
            handler: function(){
                RM.Pages.Functions.Reservations_New_Users_Save();
            }
        },{
            text: RM.Translate.Common.Cancel,
            handler: function(){
                RM.Pages.Reservations_New_User_Window.hide();
                RM.Pages.Reservations_New_FormPanel.grid.eventStore.remove(RM.Pages.Reservations_New_Record);
            }
        }
        ]
    });

};

RM.Main.Pages.push(RM.Pages.Reservations_New_cp);