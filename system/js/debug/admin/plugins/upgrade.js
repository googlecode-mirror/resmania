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
 * Plugins Upgrade JS
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
RM.Pages.Functions.plugins_upgrade_cancel = function(){
    RM.Pages.Functions.Plugins_ListJson_Request();
};

RM.Pages.Plugins_Upgrade_Toolbar = {
    xtype : "panel",
    id : "rm_pages_plugins_upgrade_toolbar",
    bodyBorder : false,
    html : RM.Common.getToolbar([
        {image: RM.BaseLargeImageURL+"back.gif", label: RM.Translate.Common.Back, link: "RM.Pages.Functions.Plugins_ListJson_Request()"},
        {image: RM.BaseLargeImageURL+"cancel.gif", label: RM.Translate.Common.Cancel, link: "RM.Pages.Functions.plugins_upgrade_cancel()"}
    ])
};
RM.Toolbars.push(RM.Pages.Plugins_Upgrade_Toolbar);

RM.Pages.Functions.Plugins_UpgradeJson = function(){
    RM.Pages.Plugins_Upgrade_Log.body.update('');
    Ext.getCmp('toolbar_panel').layout.setActiveItem('rm_pages_plugins_upgrade_toolbar');
    Ext.getCmp('content-panel').layout.setActiveItem('rm_pages_plugins_upgrade');
};

RM.Pages.Plugins_Upgrade_Form = new Ext.FormPanel({
    id : 'rm_pages_plugins_upgrade_form',
    fileUpload: true,
    labelWidth: 50,
    autoHeight: true,
    bodyStyle: 'padding: 10px 10px 0 10px;',
    bodyBorder : false,
    frame : false,
    url : RM.Common.AssembleURL({
        controller: 'Plugins',
        action: 'upgradejson'
    }),
    defaults: {
        anchor: '95%',
        allowBlank: false,
        msgTarget: 'side'
    },
    items: [{
        xtype : 'fileuploadfield',
        id: 'rm_pages_plugins_upgrade_form_upload',
        emptyText: RM.Translate.Admin.Plugins.Upgrade.SelectZipPluginFile,
        fieldLabel: RM.Translate.Admin.Plugins.Upgrade.Plugin,
        name: 'rm_pages_plugins_upgrade_form_upload'
    }],
    buttons: [{
        text: RM.Translate.Common.Upgrade,
        handler: function(){
            if(RM.Pages.Plugins_Upgrade_Form.getForm().isValid()){
                RM.Pages.Plugins_Upgrade_Form.getForm().submit({
                    success: function(form, action) {
                        Ext.Msg.alert(RM.Translate.Common.Success, RM.Translate.Admin.Plugins.Upgrade.UpgradeSuccess);
                        RM.Pages.Plugins_Upgrade_Form_LogShow(action.result.msg);
                    },
                    failure: function(form, action){
                        Ext.Msg.alert(RM.Translate.Common.Failed, RM.Translate.Admin.Plugins.Upgrade.UpgradeFailure);
                        RM.Pages.Plugins_Upgrade_Form_LogShow(action.result.msg);
                    },
                    waitMsg: RM.Translate.Common.Uploading,
                    waitTitle: RM.Translate.Common.PleaseWait
                });
            }
        }
    },{
        text: RM.Translate.Common.Reset,
        handler: function(){
            RM.Pages.Plugins_Upgrade_Form.getForm().reset();
        }
    }]
});

RM.Pages.Plugins_Upgrade_Form_LogShow = function(messages){
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
    RM.Pages.Plugins_Upgrade_Log.body.update(html);
};

RM.Pages.Plugins_Upgrade_Log = new Ext.Panel({
    html: '',
    bodyBorder : false,
    frame : false
});

RM.Pages.Plugins_Upgrade = new Ext.Panel({
    id : 'rm_pages_plugins_upgrade',
    title : RM.Translate.Admin.Plugins.Upgrade.UpgradePlugin,
    bodyBorder : false,
    frame : false,
    bodyStyle : "padding:20px",
    items : [
       RM.Pages.Plugins_Upgrade_Form,
       RM.Pages.Plugins_Upgrade_Log
    ]
});

RM.Main.Pages.push(RM.Pages.Plugins_Upgrade);