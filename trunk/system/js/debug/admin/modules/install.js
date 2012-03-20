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
 * Modules Install JS
 * This creates the admin GUI Modules Install
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

RM.Pages.Functions.modules_install_cancel = function(){
    RM.Pages.Functions.Modules_ListJson_Request();
};

RM.Pages.Modules_Install_Toolbar = {
    xtype : "panel",
    id : "rm_pages_modules_install_toolbar",
    bodyBorder : false,
    html : RM.Common.getToolbar([
        {image: RM.BaseLargeImageURL+"back.gif", label: RM.Translate.Common.Back, link: "RM.Pages.Functions.Modules_ListJson_Request()"},
        {image: RM.BaseLargeImageURL+"cancel.gif", label: RM.Translate.Common.Cancel, link: "RM.Pages.Functions.modules_install_cancel()"}
    ])
};
RM.Toolbars.push(RM.Pages.Modules_Install_Toolbar);

RM.Pages.Functions.Modules_InstallJson_Request = function(){
    RM.Pages.Modules_Install_Log.body.update('');

    var myMask = new Ext.LoadMask('rm_pages_modules_install', {msg:RM.Translate.Common.PleaseWait});
    myMask.show();

    var conn = new Ext.data.Connection();
    var request = {
        url: RM.Common.AssembleURL({
            controller : 'Modules',
            action: 'installjson'
        }),
        method: 'POST',
        success: function(responseObject) {
            eval('RM.Pages.Functions.Modules_InstallJson('+responseObject.responseText+');');
            myMask.hide();
        },
        failure: function() {
            Ext.Msg.alert(RM.Translate.Common.Status, RM.Translate.Common.UnableToShow);
            myMask.hide();
        }
    };
    conn.request(request);
};

RM.Pages.Functions.Modules_InstallJson = function(responseObject){
    RM.Pages.Modules_Install_Log.body.update('');

    Ext.getCmp('toolbar_panel').layout.setActiveItem('rm_pages_modules_install_toolbar');
    Ext.getCmp('content-panel').layout.setActiveItem('rm_pages_modules_install');    
};

RM.Pages.Modules_Install_Form = new Ext.FormPanel({
    id : 'rm_pages_modules_install_form',
    fileUpload: true,
    labelWidth: 50,    
    autoHeight: true,
    bodyBorder : false,
    frame : false,
    bodyStyle: 'padding: 10px 10px 0 10px;',    
    url : RM.Common.AssembleURL({
        controller: 'Modules',
        action: 'uploadjson'
    }),
    defaults: {
        anchor: '95%',
        allowBlank: false,
        msgTarget: 'side'
    },
    items: [{
        xtype : 'fileuploadfield',        
        id: 'rm_pages_modules_install_form_upload',
        emptyText: RM.Translate.Admin.Modules.Install.SelectZipModuleFile,
        fieldLabel: RM.Translate.Admin.Modules.Install.Module,
        name: 'rm_pages_modules_install_form_upload'        
    }],
    buttons: [{
        text: RM.Translate.Common.Install,
        handler: function(){
            if(RM.Pages.Modules_Install_Form.getForm().isValid()){
                RM.Pages.Modules_Install_Form.getForm().submit({                    
                    success: function(form, action) {                        
                       // Ext.Msg.alert(RM.Translate.Common.Success, RM.Translate.Admin.Modules.Install.InstallSuccess);
                        RM.Pages.Modules_Install_Form_LogShow(action.result.msg);
                    },
                    failure: function(form, action){
                        //Ext.Msg.alert(RM.Translate.Common.Failed, RM.Translate.Admin.Modules.Install.InstallFailure);
                        RM.Pages.Modules_Install_Form_LogShow(action.result.msg);
                    },
                    waitMsg: RM.Translate.Common.Uploading,
                    waitTitle: RM.Translate.Common.PleaseWait
                });
            }
        }
    },{
        text: RM.Translate.Common.Reset,
        handler: function(){
            RM.Pages.Modules_Install_Form.getForm().reset();
        }
    }]
});

RM.Pages.Modules_Install_Form_LogShow = function(messages){
    var html = '<ul type="disc">';
    var i=0;for(i; i<messages.length; i++) {
        var message = messages[i];
        if (message.error) {
            html += "<li style='color: red'>"+message.text+"</li>";
        } else {
            html += "<li style='color: green'>"+message.text+"</li>";
        }
    }
    html+='</ul>';
    RM.Pages.Modules_Install_Log.body.update(html);
};

RM.Pages.Modules_Install_Log = new Ext.Panel({
    html: '',
    bodyBorder : false,
    frame : false
});

RM.Pages.Modules_Install = new Ext.Panel({
    id : 'rm_pages_modules_install',
    title : RM.Translate.Admin.Modules.Install.InstallNewModule,
    bodyBorder : false,
    frame : false,
    bodyStyle : "padding:20px",
    items : [
       RM.Pages.Modules_Install_Form,
       RM.Pages.Modules_Install_Log
    ]
});

RM.Main.Pages.push(RM.Pages.Modules_Install);