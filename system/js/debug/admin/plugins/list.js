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
 * Plugins List JS
 * This creates the admin GUI Plugins List Page
 *
 * JSLint.com Check: 18/03/2011
 *
 * @access       public
 * @author       Rob/Valentin
 * @copyright    2011 ResMania Ltd.
 * @version      1.2.1
 * @link         http://docs.resmania.com/api/
 * @since        06-2011
 */
RM.Pages.Plugins_Install_Form = new Ext.FormPanel({
    id : 'rm_pages_plugins_install_form',
    fileUpload: true,
    labelWidth: 50,
    autoHeight: true,
    bodyStyle: 'padding: 10px 10px 0 10px;',
    bodyBorder : false,
    frame : true,
    url : RM.Common.AssembleURL({
        controller: 'Plugins',
        action: 'uploadjson'
    }),
    defaults: {
        anchor: '95%',
        allowBlank: false,
        msgTarget: 'side'
    },
    items: [{
        xtype : 'fileuploadfield',
        id: 'rm_pages_plugins_install_form_upload',
        emptyText: RM.Translate.Admin.Plugins.Install.SelectZipPluginFile,
        fieldLabel: RM.Translate.Admin.Plugins.Install.Plugin,
        name: 'rm_pages_plugins_install_form_upload'
    }],
    buttons: [{
        text: RM.Translate.Common.Install,
        handler: function(){
            if(RM.Pages.Plugins_Install_Form.getForm().isValid()){
                RM.Pages.Plugins_Install_Form.getForm().submit({
                    success: function(form, action) {
                        //Ext.Msg.alert(RM.Translate.Common.Success, RM.Translate.Admin.Plugins.Install.InstallSuccess);
                        RM.Pages.Plugins_Install_Form_LogShow(action.result.msg);
                        RM.Pages.Plugins_List_Json_Store.reload();
                    },
                    failure: function(form, action){
                        Ext.Msg.alert(RM.Translate.Common.Failed, RM.Translate.Admin.Plugins.Install.InstallFailure);
                        RM.Pages.Plugins_Install_Form_LogShow(action.result.msg);
                    },
                    waitMsg: RM.Translate.Common.Uploading,
                    waitTitle: RM.Translate.Common.PleaseWait
                });
            }
        }
    },{
        text: RM.Translate.Common.Cancel,
        handler: function(){
            RM.Pages.Plugins_Install_Window.hide();
        }
    }]
});

RM.Pages.Plugins_Install_Form_LogShow = function(messages){
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
    RM.Pages.Plugins_Install_Log.body.update(html);
};

RM.Pages.Plugins_Install_Log = new Ext.Panel({
    title: RM.Translate.Common.InstallationLog,
    height: 150,
    html: '',
    bodyBorder : false,
    frame : false
});

RM.Pages.Plugins_Install_Window = null;
RM.Pages.Functions.plugins_list_install = function(){
    if(!RM.Pages.Plugins_Install_Window){
        RM.Pages.Plugins_Install_Window = new Ext.Window({
            xtype : 'panel',
            title : RM.Translate.Admin.Plugins.Install.InstallNewPlugin,
            width : 500,
            height : 265,
            closeAction :'hide',
            layout: 'form',
            border: false,
            plain : true,
            items : [
                RM.Pages.Plugins_Install_Form,
                RM.Pages.Plugins_Install_Log
            ]
        });
    }
    RM.Pages.Plugins_Install_Window.show();
    RM.Pages.Plugins_Install_Window.doLayout();

    RM.Pages.Plugins_Install_Log.body.update('');
};

RM.Pages.Plugins_Upgrade_Upload_Window = null;
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
                        RM.Pages.Plugins_Upgrade_Upload_Window.hide();
                        //Ext.Msg.alert(RM.Translate.Common.Success, RM.Translate.Admin.Plugins.Upgrade.UpgradeSuccess);
                        RM.Pages.Plugins_List_Json_Store.reload();
                    },
                    failure: function(form, action){
                        Ext.Msg.alert(RM.Translate.Common.Failed, RM.Translate.Admin.Plugins.Upgrade.UpgradeFailure);
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

RM.Pages.Functions.plugins_list_upgrade = function(){
    if(!RM.Pages.Plugins_Upgrade_Upload_Window){
        RM.Pages.Plugins_Upgrade_Upload_Window = new Ext.Window({
            xtype : 'panel',
            title : RM.Translate.Admin.Plugins.Upgrade.UpgradePlugin,
            width : 500,
            height : 110,
            closeAction :'hide',
            layout: 'form',
            border: false,
            plain : true,
            items : [
                RM.Pages.Plugins_Upgrade_Form
            ]
        });
    }
    RM.Pages.Plugins_Upgrade_Upload_Window.show();
    RM.Pages.Plugins_Upgrade_Form.doLayout();

    //RM.Pages.Functions.Plugins_UpgradeJson();
};

RM.Pages.Functions.plugins_list_auto_upgrade = function(){
    var selections = RM.Pages.Plugins_List_Columns_SM.getSelections();
    if (selections.length === 0){
        Ext.Msg.alert(RM.Translate.Common.Status, RM.Translate.Common.NeedToSelectRows);
        return;
    }
    var parametersJson = [];
    var i = 0;for (i; i < selections.length; i++){
        parametersJson[i] = {
            name : 'ids[]',
            value : selections[i].data.id
        };
    }

    var myMask = new Ext.LoadMask('rm_pages_plugins_list', {msg:RM.Translate.Common.PleaseWait});
    myMask.show();
    var conn = new Ext.data.Connection();
    var request = {
        url: RM.Common.AssembleURL({
            controller : 'Plugins',
            action: 'autoupgradeJson',
            parameters : parametersJson
        }),
        method: 'POST',
        success: function(responseObject) {
            myMask.hide();
            var data = Ext.util.JSON.decode(responseObject.responseText);

            if (data.success) {
                Ext.Msg.show({
                   title:RM.Translate.Common.Success,
                   msg: RM.Translate.Admin.Plugins.List.AutoUpgradeSuccess,
                   buttons: Ext.Msg.YESNO,
                   animEl: 'elId',
                   icon: Ext.MessageBox.QUESTION,
                   fn: function(buttonID){

                        if (buttonID === 'yes'){
                            window.location.reload();
                        } else {

                            RM.Pages.Plugins_List_Json_Store.reload();
                        }

                   }
                });
            } else {
                Ext.Msg.alert(RM.Translate.Common.Failed, data.msg.join(', '));
            }
        },
        failure: function() {
            myMask.hide();
            Ext.MessageBox.alert(RM.Translate.Common.AjaxFailed);
        }
    };
    conn.request(request);
};

RM.Pages.Functions.plugins_list_uninstall = function(){
    var selections = RM.Pages.Plugins_List_Columns_SM.getSelections();
    if (selections.length === 0){
        Ext.Msg.alert(RM.Translate.Common.Status, RM.Translate.Common.NeedToSelectRows);
        return;
    }

    Ext.MessageBox.confirm(RM.Translate.Common.Delete, RM.Translate.Admin.Plugins.List.UninstallAlert, function(buttonID){
        if (buttonID !== 'yes') {
            return;
        }

        var parametersJson = [];
        var i = 0;for (i; i < selections.length; i++){
            parametersJson[i] = {
                name : 'ids[]',
                value : selections[i].data.id
            };
        }

        var myMask = new Ext.LoadMask('rm_pages_plugins_list', {msg:RM.Translate.Common.PleaseWait});
        myMask.show();
        var conn = new Ext.data.Connection();
        var request = {
            url: RM.Common.AssembleURL({
                controller : 'Plugins',
                action: 'uninstalljson',
                parameters : parametersJson
            }),
            method: 'POST',
            success: function(responseObject) {
                myMask.hide();
                var data = Ext.util.JSON.decode(responseObject.responseText);
                if (data.success) {
                    Ext.Msg.alert(RM.Translate.Common.Success, RM.Translate.Admin.Plugins.List.UninstallSuccess);
                    window.location.reload();
                } else {
                    Ext.Msg.alert(RM.Translate.Common.Failed, data.msg.join(', '));
                }
            },
            failure: function() {
                myMask.hide();
                Ext.MessageBox.alert(RM.Translate.Common.DeleteFailure);
            }
        };
        conn.request(request);
    });
};
RM.Pages.Functions.plugins_list_enable = function(){
    var selections = RM.Pages.Plugins_List_Columns_SM.getSelections();
    if (selections.length === 0){
        Ext.Msg.alert(RM.Translate.Common.Status, RM.Translate.Common.NeedToSelectRows);
        return;
    }


    Ext.Msg.show({
       title:RM.Translate.Admin.Plugins.List.Enable,
       msg: RM.Translate.Admin.Plugins.List.EnableAlert,
       buttons: Ext.Msg.YESNOCANCEL,
       animEl: 'elId',
       icon: Ext.MessageBox.QUESTION,
       fn: function(buttonID){

            if (buttonID === 'cancel') {
                return;
            }

            var selections = RM.Pages.Plugins_List_Columns_SM.getSelections();
            var parametersJson = [];
            var i = 0;for (i; i < selections.length; i++){
                parametersJson[i] = {
                    name : 'ids[]',
                    value : selections[i].data.id
                };
            }

            var myMask = new Ext.LoadMask('rm_pages_plugins_list', {msg:RM.Translate.Common.PleaseWait});
            myMask.show();
            var conn = new Ext.data.Connection();
            var request = {
                url: RM.Common.AssembleURL({
                    controller : 'Plugins',
                    action: 'enablejson',
                    parameters : parametersJson
                }),
                method: 'POST',
                success: function(responseObject) {
                    myMask.hide();
                    if (responseObject.error) {
                        //TODO: some alert with errors
                    }

                    if (buttonID === 'yes'){
                        window.location.reload();
                    } else {
                        RM.Pages.Functions.Plugins_ListJson_Request();
                    }
                },
                failure: function() {
                    myMask.hide();
                    Ext.MessageBox.alert(RM.Translate.Common.ConfirmFailure);
                }
            };
            conn.request(request);
        }
    });
};
RM.Pages.Functions.plugins_list_disable = function(){
    var selections = RM.Pages.Plugins_List_Columns_SM.getSelections();
    if (selections.length === 0){
        Ext.Msg.alert(RM.Translate.Common.Status, RM.Translate.Common.NeedToSelectRows);
        return;
    }

    Ext.Msg.show({
       title:RM.Translate.Admin.Plugins.List.Disable,
       msg: RM.Translate.Admin.Plugins.List.DisableAlert,
       buttons: Ext.Msg.YESNOCANCEL,
       animEl: 'elId',
       icon: Ext.MessageBox.QUESTION,
       fn: function(buttonID){

            if (buttonID === 'cancel') {
                return;
            }

            var selections = RM.Pages.Plugins_List_Columns_SM.getSelections();
            var parametersJson = [];
            var i = 0;for (i; i < selections.length; i++){
                parametersJson[i] = {
                    name : 'ids[]',
                    value : selections[i].data.id
                };
            }

            var myMask = new Ext.LoadMask('rm_pages_plugins_list', {msg:RM.Translate.Common.PleaseWait});
            myMask.show();
            var conn = new Ext.data.Connection();
            var request = {
                url: RM.Common.AssembleURL({
                    controller : 'Plugins',
                    action: 'disablejson',
                    parameters : parametersJson
                }),
                method: 'POST',
                success: function(responseObject) {
                    myMask.hide();
                    var data = Ext.util.JSON.decode(responseObject.responseText);
                    if (data.success) {
                        if (buttonID === 'yes') {
                            window.location.reload();
                        } else {
                            RM.Pages.Functions.Plugins_ListJson_Request();
                        }
                    } else {
                        Ext.Msg.alert(RM.Translate.Common.Failed, data.msg.join(', '));
                    }
                },
                failure: function() {
                    myMask.hide();
                    Ext.MessageBox.alert(RM.Translate.Common.UnconfirmFailure);
                }
            };
            conn.request(request);
        }
    });
};

RM.Pages.Functions.Plugins_ListJson_Request = function(){
    RM.Pages.Functions.Plugins_ListJson();
};

RM.Pages.Plugins_List_Toolbar = {
    xtype : "panel",
    id : "rm_pages_plugins_list_toolbar",
    bodyBorder : false,
    html : RM.Common.getToolbar([
        {image: RM.BaseLargeImageURL+"plugin_install.png", label: RM.Translate.Common.Install, link: "RM.Pages.Functions.plugins_list_install()"},
        {image: RM.BaseLargeImageURL+"plugin_uninstall.png", label: RM.Translate.Common.Uninstall, link: "RM.Pages.Functions.plugins_list_uninstall()"},
        {image: RM.BaseLargeImageURL+"plugin_upgrade.png", label: RM.Translate.Common.Upgrade, link: "RM.Pages.Functions.plugins_list_upgrade()"},
        {image: RM.BaseLargeImageURL+"plugin_upgrade.png", label: RM.Translate.Common.AutoUpgrade, link: "RM.Pages.Functions.plugins_list_auto_upgrade()"},
        {image: RM.BaseLargeImageURL+"publish.gif", label: RM.Translate.Common.Enable, link: "RM.Pages.Functions.plugins_list_enable()"},
        {image: RM.BaseLargeImageURL+"unpublish.gif", label: RM.Translate.Common.Disable, link: "RM.Pages.Functions.plugins_list_disable()"}
    ])
};
RM.Toolbars.push(RM.Pages.Plugins_List_Toolbar);

RM.Pages.Functions.Plugins_ListJson = function () {
    RM.Pages.Plugins_List.on('tabchange', function (page, currentTab){
        currentTab.doLayout();
    });

    Ext.getCmp('toolbar_panel').layout.setActiveItem('rm_pages_plugins_list_toolbar');

    Ext.state.Manager.setProvider(new Ext.state.CookieProvider());

    Ext.getCmp('content-panel').layout.setActiveItem('rm_pages_plugins_list');
    RM.Pages.Plugins_List_Json_Store.load({params:{start: 0, limit: 15}});
    RM.Pages.Plugins_List_Grid.render();

    RM.Help.Load('Admin.Plugins.List.Main');
};

RM.Pages.Plugins_List_Json_Store_Fields = [
    {name: "id"},
    {name: "name"},
    {name: "core", type: "boolean"},
    {name: "sort_order"},
    {name: "upgrade"},
    {name: "enabled", type: "boolean"},
    {name: "author"},
    {name: "creation_date", type: "date", dateFormat: RM.Common.MySQLDateFormat},
    {name: "copyright"},
    {name: "license"},
    {name: "author_email"},
    {name: "author_url"},
    {name: "version"},
    {name: "description"}
];

RM.Pages.Plugins_List_Json_Store = new Ext.ux.grid.livegrid.Store({
    url: RM.Common.AssembleURL({
        controller: 'Plugins',
        action: 'listjson'
    }),
    reader: new Ext.ux.grid.livegrid.JsonReader({
            totalProperty: 'total',
            root: 'data'
        },
        RM.Pages.Plugins_List_Json_Store_Fields
    ),
    id: 'rm_plugins_list_grid_json_store',
    sortInfo: {field: 'core', direction: 'DESC'},
    bufferSize : 300
});



RM.Pages.Plugins_List_Columns_SM = new Ext.rm.grid.CheckboxSelectionModel({
    singleSelect: true,
    header: '',
    checkOnly: true
});
RM.Pages.Plugins_List_Columns_Rows = [
    RM.Pages.Plugins_List_Columns_SM,
    {dataIndex: 'id', header: RM.Translate.Common.Id, hidden: true},
    {dataIndex: 'name', header: RM.Translate.Common.Name},
    {dataIndex: 'core', header: RM.Translate.Common.Core, renderer: function(value, metaData, record, rowIndex, colIndex, store) {if (value == "1") { metaData.css = 'RM_list_enabled_icon';}}},
    {dataIndex: 'sort_order', header: RM.Translate.Admin.Plugins.List.SortOrder, hidden: true},
    {dataIndex: 'enabled', header: RM.Translate.Admin.Plugins.List.Enabled, renderer: function(value, metaData, record, rowIndex, colIndex, store) {if (value=="1") {metaData.css = 'RM_list_enabled_icon';} else {metaData.css = 'RM_list_disabled_icon'; }}},
    {dataIndex: 'upgrade', header: RM.Translate.Admin.Plugins.List.Upgrade, renderer: function(value, metaData, record, rowIndex, colIndex, store) {if (value=="1") {metaData.css = 'RM_list_enabled_icon';}}},
    {dataIndex: 'author', header: RM.Translate.Admin.Plugins.List.Author},
    {dataIndex: 'creation_date', header: RM.Translate.Admin.Plugins.List.CreationDate, renderer: Ext.util.Format.dateRenderer('F Y')},
    {dataIndex: 'copyright', header: RM.Translate.Admin.Plugins.List.Copyright},
    {dataIndex: 'license', header: RM.Translate.Admin.Plugins.List.License},
    {dataIndex: 'author_email', header: RM.Translate.Admin.Plugins.List.AuthorEmail},
    {dataIndex: 'author_url', header: RM.Translate.Admin.Plugins.List.AuthorUrl},
    {dataIndex: 'version', header: RM.Translate.Admin.Plugins.List.Version},
    {dataIndex: 'description', header: RM.Translate.Admin.Plugins.List.Description}
];

RM.Pages.Plugins_List_Columns = new Ext.grid.ColumnModel({
    columns: RM.Pages.Plugins_List_Columns_Rows,
    defaults: {
        sortable: true
    }
});
RM.Pages.Plugins_List_Filters = new Ext.ux.grid.GridFilters({
    filters: [
        {dataIndex: 'id', type: 'numeric'},
        {dataIndex: 'name', type: 'string'},
        {dataIndex: 'core', type: 'boolean'},
        {dataIndex: 'sort_order', type: 'numeric'},
        {dataIndex: 'enabled', type: 'boolean'},
        {dataIndex: 'upgrade', type: 'boolean'},
        {dataIndex: 'author', type: 'string'},
        {dataIndex: 'creation_date', type: 'date'},
        {dataIndex: 'copyright', type: 'string'},
        {dataIndex: 'license', type: 'string'},
        {dataIndex: 'author_email', type: 'string'},
        {dataIndex: 'author_url', type: 'string'},
        {dataIndex: 'version', type: 'string'},
        {dataIndex: 'description', type: 'string'}
    ]
});

RM.Pages.Plugins_List_Grid_View = new Ext.ux.grid.livegrid.GridView({
    nearLimit : 100,
    loadMask  : {
        msg :  'Buffering. Please wait...'
    }
});

RM.Pages.Plugins_List_Grid = new Ext.ux.grid.livegrid.GridPanel({
    id : 'rm_plugins_list_grid',
    plugins: RM.Pages.Plugins_List_Filters,
    bbar     : new Ext.ux.grid.livegrid.Toolbar({
            view        : RM.Pages.Plugins_List_Grid_View,
            displayInfo : true
    }),
    enableColLock : false,
    loadMask : {
        msg: RM.Translate.Common.PleaseWait
    },
    height : RM.Common.GetPanelHeight(104),
    cm : RM.Pages.Plugins_List_Columns,
    store : RM.Pages.Plugins_List_Json_Store,
    selModel : RM.Pages.Plugins_List_Columns_SM,
    view: RM.Pages.Plugins_List_Grid_View,
    viewConfig: {
        forceFit: true
    }
});

RM.Pages.Plugins_List = new Ext.Panel({
    id : 'rm_pages_plugins_list',
    title : RM.Translate.Common.List,
    iconCls: "RM_config_plugins_icon",
    items : [RM.Pages.Plugins_List_Grid]
});

RM.Main.Pages.push(RM.Pages.Plugins_List);