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
 * User New JS
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

// toolbar actions
RM.Pages.Functions.New_User_Save = function(){
    RM.Pages.Users_New_Form_Statusbar.showBusy();
    RM.Pages.Users_New_Form.getForm().submit({
        success: function(form, action) {
            RM.Pages.Users_New_Form_Statusbar.clearStatus();            
            RM.Pages.Functions.Users_ListJson_Request();
        },
        failure: function(form, action) {
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

RM.Pages.Functions.New_User_Cancel = function(){
    RM.Pages.Functions.Users_ListJson_Request();
};

// toolbars
RM.Pages.Users_New_Toolbar = {
    xtype : "panel",
    id : "rm_pages_users_new_toolbar",
    bodyBorder : false,
    html : RM.Common.getToolbar([
        {image: RM.BaseLargeImageURL+"save.gif", label: RM.Translate.Common.Save, link: "RM.Pages.Functions.New_User_Save()"},
        {image: RM.BaseLargeImageURL+"cancel.gif", label: RM.Translate.Common.Cancel, link: "RM.Pages.Functions.New_User_Cancel()"}
    ])
};

RM.Toolbars.push(RM.Pages.Users_New_Toolbar);

if (RM.Common.EnableUserGroups === "1"){
     var params =  [{
        name: "returnall",
        value: true
    }];
}

// json stores
RM.Pages.Users_New_Groups = new Ext.data.JsonStore({
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

RM.Pages.Users_New_Title = new Ext.data.JsonStore({
    fields:[
        {name:'id', type:'int'},
        {name:'title', type:'string'}
    ],
    data: RM.Translate.Common.JSON.Titles
});

// form items
RM.Pages.Users_New_Tab1 = {
    xtype : "panel",
    layout: 'form',
    title : RM.Translate.Admin.Users.New.UserInformation,
    id : "rm_pages_users_new_tab_1",
    bodyStyle : "padding:10px;",
    items : [],
    autoScroll: true,
    labelWidth: 190
};

RM.Pages.Users_New_TabPanel = new Ext.TabPanel({
    id: "rm_pages_users_new_tab",
    activeTab : 0,
    height: RM.Common.GetPanelHeight(106),
    items : [
        RM.Pages.Users_New_Tab1
    ],
    bbar: new Ext.ux.StatusBar({
        id: 'rm_pages_users_new_statusbar',
        items: []
    })
});

// main form
RM.Pages.Users_New_Form = new Ext.FormPanel({
    id : "rm_users_new_page",
    xtype : "form",
    url : RM.Common.AssembleURL({
        controller: 'Users',
        action: 'insertJson'
    }),
    frame : false,
    bodyBorder : false,
    title : RM.Translate.Admin.Users.New.PageTitle,
    iconCls: "RM_users_root_icon",
    items : [
        RM.Pages.Users_New_TabPanel
    ],
    autoScroll: true
});

// main function
RM.Pages.Functions.Users_newJson = function (responseObject) {
    
    // if we aren't using this from the reservation wizard show a loading mask
    var myMask = new Ext.LoadMask('rm_pages_users_new_tab', {
        msg: RM.Translate.Common.PleaseWait,
        msgCls: "RM_Loading_Mask_Msg_Hidden" // hide the msg
    });
    myMask.show();

    // get the fields from the DB...

    for (i = 0; i < responseObject.fields.length; i++){
        field = responseObject.fields[i];

        field.view.id = 'new_' + field.view.id;
        field.view.name = 'new_' + field.view.name;
        field.view.hiddenName = 'new_' + field.view.hiddenName;
        field.parent_id = 'rm_pages_users_new_' + field.parent_id;

        RM.Common.RemoveOldFields(field.view.id);
        
        eval("Ext.getCmp(field.parent_id).add(new "+field.class_name+"(field.view))");
    }

    RM.Pages.Users_New_Groups.reload(); //pre load the groups information

    RM.Pages.Users_New_Form.on('tabchange', function (page, currentTab){
        currentTab.doLayout();
    });

    Ext.getCmp('toolbar_panel').layout.setActiveItem('rm_pages_users_new_toolbar');
    Ext.getCmp('content-panel').layout.setActiveItem('rm_users_new_page');

    RM.Pages.Users_New_Form_Statusbar = Ext.getCmp('rm_pages_users_new_statusbar');
    RM.Pages.Users_New_Form_Statusbar.setStatus({
        text: RM.Translate.Admin.Users.New.Status,
        iconCls: 'ok-icon'
    });

    Ext.getCmp('rm_pages_users_new_tab').render();
    Ext.getCmp('rm_pages_users_new_tab').doLayout();

    myMask.hide();
};

RM.Main.Pages.push(RM.Pages.Users_New_Form);