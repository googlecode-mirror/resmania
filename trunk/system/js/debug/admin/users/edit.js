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
 * User Edit JS
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

RM.Pages.Functions.Edit_User_Save = function(){
    //statusbar = Ext.getCmp('rm_pages_users_edit_statusbar');
    //statusbar.showBusy();
    Ext.getCmp('edit_users[username]').enable();

    RM.Pages.Users_Edit_Form.getForm().submit({
        success: function(form, action) {
            //Ext.Msg.alert(RM.Translate.Common.Success, RM.Translate.Admin.Users.Edit.Success);
            //statusbar.clearStatus();
            RM.Pages.Functions.Users_ListJson_Request();
        },
        failure: function(form, action) {
            switch (action.failureType) {                
                case Ext.form.Action.CONNECT_FAILURE:
                    Ext.Msg.alert(RM.Translate.Common.Failed, RM.Translate.Common.UnableToShow);
                    break;
                case Ext.form.Action.SERVER_INVALID:
                   Ext.Msg.alert(RM.Translate.Common.Failed, action.result.msg);
                   break;
           }
        },
        waitMsg: RM.Translate.Common.Saving,
        waitTitle: RM.Translate.Common.PleaseWait
    });
};

RM.Pages.Functions.Edit_User_Cancel = function(){
    RM.Pages.Functions.Users_ListJson_Request();
};

RM.Pages.Users_Edit_Toolbar = {
    xtype : "panel",
    id : "rm_pages_users_edit_toolbar",
    bodyBorder : false,
    html : RM.Common.getToolbar([
     {image: RM.BaseLargeImageURL+"save.gif", label: RM.Translate.Common.Save, link: "RM.Pages.Functions.Edit_User_Save()"},
     {image: RM.BaseLargeImageURL+"cancel.gif", label: RM.Translate.Common.Cancel, link: "RM.Pages.Functions.Edit_User_Cancel()"}
    ])
};

RM.Toolbars.push(RM.Pages.Users_Edit_Toolbar);

if (RM.Common.EnableUserGroups === "1"){
     var params =  [{
        name: "returnall",
        value: true
    }];
}

RM.Pages.Users_Edit_Group_Membership = new Ext.data.JsonStore({
    fields:[
        {name:'id', type:'int'},
        {name:'name', type:'string'}
    ],
    url : RM.Common.AssembleURL({
        controller: 'UserGroups',
        action: 'getallJson',
        parameters: params
    })
});

RM.Pages.Users_Edit_Title = new Ext.data.JsonStore({
    fields:[
        {name:'id', type:'int'},
        {name:'title', type:'string'}
    ],
    data: RM.Translate.Common.JSON.Titles
});

RM.Pages.Functions.Users_EditJson_Request = function(user_id){

    var conn = new Ext.data.Connection();
    var request = {
        url: RM.Common.AssembleURL({
            controller : 'Users',
            action: 'editJson',
            parameters : [{
                name : 'id',
                value : user_id
            }]
        }),
        method: 'POST',
        success: function(responseObject) {
            eval('RM.Pages.Functions.Users_EditJson('+responseObject.responseText+');');
        },
        failure: function() {
            Ext.Msg.alert(RM.Translate.Common.Status, RM.Translate.Common.UnableToShow);
        }
    };
    conn.request(request);
};


RM.Pages.Functions.Users_EditJson = function (responseObject) {

    var myMask = new Ext.LoadMask('rm_pages_users_edit_tab', {
        msg: RM.Translate.Common.PleaseWait,
        msgCls: "RM_Loading_Mask_Msg_Hidden" // hide the msg
    });
    myMask.show();

    RM.Pages.Users_Edit_TabPanel.on('tabchange', function (page, currentTab){ currentTab.doLayout(); });

    Ext.getCmp('toolbar_panel').layout.setActiveItem('rm_pages_users_edit_toolbar');
    Ext.getCmp('content-panel').layout.setActiveItem('rm_users_edit_page');

    var tab = Ext.getCmp('rm_pages_users_edit_tab_1');

    var hiddenInputs = [];

    for (i = 0; i < responseObject.fields.length; i++){
        field = responseObject.fields[i];
        if (field.view.id === 'users[password]') {
            field.view.fieldLabel = RM.Translate.Common.New + " " + field.view.fieldLabel;
            field.view.allowBlank = true;
            field.view.id = 'edit_users[new_password]';
            field.view.name = 'edit_users[new_password]';
        } else {
            field.view.id = 'edit_' + field.view.id;
            field.view.name = 'edit_' + field.view.name;
        }

        //var $id = field.view.id;
                
        if (field.view.hiddenName) {
            var re_Query = new RegExp(/\[(.*)\]/); // load the name in the [ ] brackets
            var ActualHiddenName = field.view.hiddenName.match(re_Query)[1];
            hiddenInputs.push(ActualHiddenName);
            field.view.hiddenName = 'edit_' + field.view.hiddenName;
        }
        field.parent_id = 'rm_pages_users_edit_' + field.parent_id;

        RM.Common.RemoveOldFields(field.view.id);

        eval("Ext.getCmp(field.parent_id).add(new "+field.class_name+"(field.view))");
        
    }
   
    // load the data into the form...
    var fieldData = responseObject.users;
    var field_name;for (field_name in fieldData) {

        // there are extra rules here for Username and Password
        if (field_name!=='password'){
            eval('var field_value = responseObject.users.'+field_name+';');
            if(RM.Common.InArray(field_name, hiddenInputs)===true) {            
               Ext.getCmp('edit_users_'+field_name).setValue(field_value);
            } else {               
               Ext.getCmp('edit_users['+field_name+']').setValue(field_value);
            }
            if (field_name==='username'){
                Ext.getCmp('edit_users['+field_name+']').addClass('rm_disabled');
                Ext.getCmp('edit_users['+field_name+']').disable();
            }
        }
    }

    RM.Pages.Users_Edit_Group_Membership.reload({
        callback : function(){
            Ext.getCmp('edit_users_group_id').setValue(fieldData.group_id);
            }
    });

    RM.Pages.Users_Edit_Reservations_Store.load();
    
    Ext.getCmp('rm_pages_users_edit_tab').render();
    Ext.getCmp('rm_pages_users_edit_tab').doLayout();
    myMask.hide();
};

// this is for the reservations grid...

RM.Pages.Users_Edit_Reservations_Record = Ext.data.Record.create([
        {name:"reservation_id", type:'string'},
        {name:"unit_id", type:'string'},
        {name:"start_date", type:'string'},
        {name:"end_date", type:'string'}
    ]);

RM.Pages.Users_Edit_Reservations_Reader = new Ext.data.JsonReader({
    root:'data',
    totalProperty:'total',
    id:'rm_users_edit_grid_reader'
},RM.Pages.Users_Edit_Reservations_Record);

// TODO: this does not work, it works on revision 263 and the code has not changed however this no longer operates
RM.Pages.Users_Edit_Reservations_Store = new Ext.data.GroupingStore({
    id: 'rm_users_edit_grid_json_store',
    url : RM.Common.AssembleURL({
        controller: 'Users',
        action: 'listresJson'
    }),
    reader:RM.Pages.Users_Edit_Reservations_Reader,
    sortInfo: {field: 'reservation_id', direction: 'ASC'},
    remoteSort: true,
    groupField:'reservation_id'
});

RM.Pages.Users_Edit_Reservations_Store.on("beforeload", function(gridLoader, node) {
    RM.Pages.Users_Edit_Reservations_Store.baseParams.id = Ext.getCmp('edit_users[id]').value; // returns the ID value used for the reservations grid.
}, this);

RM.Pages.Users_Edit_Reservations_Grid = new Ext.grid.GridPanel({
    id : 'rm_users_edit_grid',
    stripeRows: true,
    autoHeight: true,
    collapsible: true,
    cm : new Ext.grid.ColumnModel([
        {dataIndex:'reservation_id', header:RM.Translate.Common.Reference, align:'left', sortable:true},
        {dataIndex:'unit_id', header:RM.Translate.Common.Unit, align:'left', sortable:true},
        {dataIndex:'start_date', header:RM.Translate.Common.StartDate, align:'left', sortable:true},
        {dataIndex:'end_date', header:RM.Translate.Common.EndDate, align:'left', sortable:true}
    ]),
    ds : RM.Pages.Users_Edit_Reservations_Store,
    view: new Ext.grid.GroupingView({
        forceFit:true,
        groupTextTpl: '{text} ({[values.rs.length]} {[values.rs.length > 1 ? "'+RM.Translate.Common.Unit+'" : "'+RM.Translate.Common.Units+'"]})'
    }),
    iconCls: 'icon-grid'
});

// reservations grid finish 

RM.Pages.Users_Edit_Tab1 = {
    xtype : "panel",
    layout: 'form',
    title : RM.Translate.Admin.Users.Edit.UserInformation,
    id : "rm_pages_users_edit_tab_1",
    bodyStyle : "padding:10px;",
    autoScroll: true,
    labelWidth: 190,
    items : []
};

RM.Pages.Users_Edit_Tab2 = {
    xtype : "panel",
    layout: 'form',
    title : RM.Translate.Common.Reservations,
    id : "rm_pages_users_edit_tab_2",
    bodyStyle : "padding:10px;",
    autoScroll: true,
    items : [
        RM.Pages.Users_Edit_Reservations_Grid
    ]
};


RM.Pages.Users_Edit_TabPanel = new Ext.TabPanel({
    id: "rm_pages_users_edit_tab",
    activeTab : 0,
    height: RM.Common.GetPanelHeight(104),
    items : [
        RM.Pages.Users_Edit_Tab1,
        RM.Pages.Users_Edit_Tab2
    ],
    bbar: new Ext.ux.StatusBar({
        id: 'rm_pages_users_edit_statusbar',
        items: []
    })
});

RM.Pages.Users_Edit_StatusBar = Ext.getCmp('rm_pages_users_edit_statusbar');
RM.Pages.Users_Edit_StatusBar.setStatus({
    text: RM.Translate.Admin.Users.Edit.Status,
    iconCls: 'ok-icon'
});

RM.Pages.Users_Edit_Form = new Ext.FormPanel({
    id : "rm_users_edit_page",
    xtype : "form",
    url : RM.Common.AssembleURL({
        controller: 'Users',
        action: 'updateJson'
    }),
    containerScroll : true,
    frame : false,
    title : RM.Translate.Admin.Users.Edit.PageTitle,
    iconCls: "RM_users_root_icon",
    items : [
        RM.Pages.Users_Edit_TabPanel
    ]
});

RM.Main.Pages.push(RM.Pages.Users_Edit_Form);