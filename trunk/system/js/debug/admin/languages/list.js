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
 * Language List Page JS
 * This creates the admin GUI Language List Page
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

RM.Pages.Languages_List_Info_Grid_Edit_Window = null;
RM.Pages.Languages_List_Info_Grid_Edit_Form = null;
RM.Pages.Languages_List_Info_Grid_Edit_Show = function(text, iso, filename, type, name){
    if (RM.Pages.Languages_List_Info_Grid_Edit_Window) {
       RM.Pages.Languages_List_Info_Grid_Edit_Window.destroy();
    }
    if (RM.Pages.Languages_List_Info_Grid_Edit_Form) {
       RM.Pages.Languages_List_Info_Grid_Edit_Form.destroy();
    }

    RM.Pages.Languages_List_Info_Grid_Edit_Form = new Ext.FormPanel({
        id : 'language-edit-panel',
        frame: false,
        border: false,
        bodyStyle : "padding:10px",
        layout: 'form',
        items: {
            xtype : 'textarea',
            fieldLabel: RM.Translate.Admin.System.Language.FileContent,
            id: 'language-edit-panel-text',
            name: 'content',
            width: 450,
            height: 400
        }
    });

    RM.Pages.Languages_List_Info_Grid_Edit_Window = new Ext.Window({
        xtype : 'panel',
        title : RM.Translate.Admin.System.Language.EditWindow,
        width : 600,
        height : 500,
        closeAction :'hide',
        layout: 'form',
        plain : true,
        items : [
            RM.Pages.Languages_List_Info_Grid_Edit_Form,
            {
            xtype: 'button',
            style: "float: right;margin-right: 20px;margin-left: 20px;",
            frame: false,
            border: false,
            text: RM.Translate.Common.Save,
            handler: function(){
                if(RM.Pages.Languages_List_Info_Grid_Edit_Form.getForm().isValid()){
                    RM.Pages.Languages_List_Info_Grid_Edit_Form.getForm().submit({
                        url: RM.Common.AssembleURL({
                            controller: 'Language',
                            action: 'savefilejson'
                        }),
                        params: {
                            'iso': iso,
                            'filename': filename,
                            'type': type,
                            'name': name
                        },
                        success: function(form, action) {
                            RM.Pages.Languages_List_Info_Grid_Edit_Window.hide();
                            RM.Pages.Reservations_StatusBar.setStatus({
                                iconCls: 'ok-icon',
                                text: RM.Translate.Admin.System.Language.SavingSuccess
                            });
                            //Ext.Msg.alert(RM.Translate.Common.Success, RM.Translate.Admin.System.Language.SavingSuccess);
                        },
                        failure: function(form, action){
                            RM.Pages.Languages_List_Info_Grid_Edit_Window.hide();
                            RM.Pages.Reservations_StatusBar.setStatus({
                                iconCls: 'failed-icon',
                                text: RM.Translate.Admin.System.Language.SavingFailed
                            });
                            //Ext.Msg.alert(RM.Translate.Common.Failed, RM.Translate.Admin.System.Language.SavingFailed);
                        },
                        waitMsg: RM.Translate.Common.Saving,
                        waitTitle: RM.Translate.Common.PleaseWait
                    });
                }
            }
        }]
    });
    Ext.getCmp('language-edit-panel-text').setValue(text);
    RM.Pages.Languages_List_Info_Grid_Edit_Window.show();
    RM.Pages.Languages_List_Info_Grid_Edit_Form.doLayout();
};

RM.Pages.Languages_List_Info_Grid_Edit = function(iso, filename, type, name){
    var myMask = new Ext.LoadMask('content-panel', {msg:RM.Translate.Common.PleaseWait});
    myMask.show();
    var conn = new Ext.data.Connection();
    var request = {
        url: RM.Common.AssembleURL({
            controller : 'Language',
            action: 'editfilejson'            
        }),
        params: {            
            'iso' : iso,
            'filename' : filename,
            'type' : type,
            'name' : name
        },
        method: 'POST',
        success: function(responseObject) {
            myMask.hide();            
            RM.Pages.Languages_List_Info_Grid_Edit_Show(responseObject.responseText, iso, filename, type, name);
        },
        failure: function() {
            myMask.hide();
            RM.Pages.Reservations_StatusBar.setStatus({
                iconCls: 'failed-icon',
                text: RM.Translate.Admin.System.Language.EditFailed
            });
            //Ext.MessageBox.alert(RM.Translate.Admin.System.Language.EditFailed);
        }
    };
    conn.request(request);
};

RM.Pages.Languages_List_Upload_Window = null;
RM.Pages.Languages_List_Upload_Form = new Ext.FormPanel({
    id : 'language-upload-panel',
    url : RM.Common.AssembleURL({
        controller: 'Language',
        action: 'uploadjson'
    }),
    fileUpload: true,
    height: 80,
    labelWidth: 150,
    frame: true,
    border: false,    
    items: [{
        xtype: 'fileuploadfield',
        id: 'language-upload-panel-file',
        allowBlank : false,
        emptyText: RM.Translate.Admin.System.Language.ChooseZip,
        fieldLabel: RM.Translate.Admin.System.Language.SelectZip,
        height: 40,
        name: 'language_upload',
        width: 220
    }],
    buttons:[{               
        text: RM.Translate.Common.Upload,
        handler: function(){
            if(RM.Pages.Languages_List_Upload_Form.getForm().isValid()){
                RM.Pages.Languages_List_Upload_Form.getForm().submit({
                    success: function(form, action) {
                        RM.Pages.Languages_List_Upload_Window.hide();
                        RM.Pages.Languages_List_Info_Grid_Store.removeAll();
                        RM.Pages.Languages_List_Main_Grid_Store.reload();
                        window.location.reload();
                    },
                    failure: function(form, action){
                        RM.Pages.Languages_List_Upload_Window.hide();
                        switch (action.failureType) {
                            case Ext.form.Action.CLIENT_INVALID:
                                Ext.Msg.alert(RM.Translate.Common.Failure, 'Form fields may not be submitted with invalid values');
                                break;
                            case Ext.form.Action.CONNECT_FAILURE:
                                Ext.Msg.alert(RM.Translate.Common.Failure, 'Ajax communication failed');
                                break;
                            case Ext.form.Action.SERVER_INVALID:
                                Ext.Msg.alert(RM.Translate.Common.Failure, action.result.msg);
                                break;
                        }                        
                    },
                    waitMsg: RM.Translate.Common.Uploading,
                    waitTitle: RM.Translate.Common.PleaseWait
                });
            }
        }
    }]
});

RM.Pages.Functions.Languages_List_Install = function(){   
    if(!RM.Pages.Languages_List_Upload_Window){
        RM.Pages.Languages_List_Upload_Window = new Ext.Window({
            xtype : 'panel',
            title : RM.Translate.Admin.System.Language.UploadWindow,
            width : 500,
            height : 110,
            closeAction :'hide',
            layout: 'form',
            border: false,
            plain : true,
            items : [
                RM.Pages.Languages_List_Upload_Form
            ]
        });
    }   
    RM.Pages.Languages_List_Upload_Window.show();
    RM.Pages.Languages_List_Upload_Form.doLayout();
};

RM.Pages.Functions.Languages_List_Enable = function(){
    var selected = RM.Pages.Functions.Languages_List_GetSelected();
    if (selected === null) {
        return;
    }
    RM.Pages.Functions.Languages_List_Action(selected, 'enablejson', true);
};

RM.Pages.Functions.Languages_List_Disable = function(){
    var selected = RM.Pages.Functions.Languages_List_GetSelected();
    if (selected === null) {
        return;
    }
    RM.Pages.Functions.Languages_List_Action(selected, 'disablejson', true);
};

RM.Pages.Functions.Languages_List_Delete = function(){
    var selected = RM.Pages.Functions.Languages_List_GetSelected();
    if (selected === null) {
        return;
    }

    if (selected === "fr_FR" || selected === "en_GB" || selected === RM.DefaultLanguage) {
        Ext.MessageBox.alert(RM.Translate.Common.Error, RM.Translate.Admin.System.Language.CantDeleteDefaultLanguage);
        return;
    }

    Ext.MessageBox.confirm(RM.Translate.Common.Delete, RM.Translate.Admin.System.Language.DeleteAlert, function(buttonID){
        if (buttonID !== 'yes') {
            return;
        }
        RM.Pages.Functions.Languages_List_Action(selected, 'deletejson', true);
    });
};

RM.Pages.Functions.Languages_List_Action = function(iso, current_action, reload){
    myMask = new Ext.LoadMask('rm_pages_languages_list', {msg:RM.Translate.Common.PleaseWait});
    myMask.show();
    var conn = new Ext.data.Connection();
    var request = {
        url: RM.Common.AssembleURL({
            controller : 'Language',
            action: current_action,
            parameters : [{
                name : 'iso',
                value : iso
            }]
        }),
        method: 'POST',
        success: function(responseObject) {
            myMask.hide();
            if (reload) {
                myMask = new Ext.LoadMask('root_gui_container', {msg:RM.Translate.Common.PleaseWait});
                myMask.show();
                window.location.reload();
            } else {
                RM.Pages.Languages_List_Info_Grid_Store.removeAll();
                RM.Pages.Languages_List_Main_Grid_Store.reload();
            }
        },
        failure: function() {
            myMask.hide();
            Ext.MessageBox.alert(RM.Translate.Common.DeleteFailure);
        }
    };
    conn.request(request);
};

RM.Pages.Functions.Languages_List_GetSelected = function(){
    var selections = RM.Pages.Languages_List_Main_Grid_Columns_SM.getSelections();
    if (selections.length === 0){
        Ext.Msg.alert(RM.Translate.Common.Status, RM.Translate.Common.NeedToSelectRows);
        return null;
    }    

    return selections[0].data.iso;
};

/**
 * Ajax call the PHP method to clear the language cache
 */
RM.Pages.Functions.Languages_List_ClearCache = function(){

    Ext.MessageBox.confirm(RM.Translate.Common.Confirm, RM.Translate.Admin.Config.Edit.ClearCacheConfirm, function(buttonID){
        if (buttonID !== 'yes') {
            return;
        }

        myMask = new Ext.LoadMask('rm_pages_languages_list', {msg:RM.Translate.Common.PleaseWait});
        myMask.show();
        var conn = new Ext.data.Connection();
        var request = {
            url: RM.Common.AssembleURL({
                controller : 'Language',
                action: 'clearcacheJson'
            }),
            method: 'POST',
            success: function(responseObject) {
                window.location.reload();
            },
            failure: function() {
                myMask.hide();
                Ext.MessageBox.alert(RM.Translate.Common.DeleteFailure);
            }
        };
        conn.request(request);
    });
};

RM.Pages.Functions.Languages_List_Config = function(){
    RM.Pages.Functions.Config_Load('showlanguage');
};

RM.Pages.Languages_List_Toolbar = {
    xtype : "panel",
    id : "rm_pages_languages_list_toolbar",
    bodyBorder : false,
    html : RM.Common.getToolbar([
        {image: RM.BaseLargeImageURL+"clearcache.gif", label: RM.Translate.Admin.Config.Edit.ClearCache, link: 'RM.Pages.Functions.Languages_List_ClearCache()'},
        {image: RM.BaseLargeImageURL+"configoption.gif", label: RM.Translate.Admin.Config.Edit.LanguageSettings, link: 'RM.Pages.Functions.Languages_List_Config()'},
        {image: RM.BaseLargeImageURL+"install.gif", label: RM.Translate.Common.Install, link: "RM.Pages.Functions.Languages_List_Install()"},
        {image: RM.BaseLargeImageURL+"new.gif", label: RM.Translate.Common.Enable, link: "RM.Pages.Functions.Languages_List_Enable()"},
        {image: RM.BaseLargeImageURL+"cancel.gif", label: RM.Translate.Common.Disable, link: "RM.Pages.Functions.Languages_List_Disable()"},
        {image: RM.BaseLargeImageURL+"delete.gif", label: RM.Translate.Common.Delete, link: "RM.Pages.Functions.Languages_List_Delete()"}
    ])
};
RM.Toolbars.push(RM.Pages.Languages_List_Toolbar);

RM.Pages.Functions.Language_ListJson = function (responseObject){
    Ext.getCmp('toolbar_panel').layout.setActiveItem('rm_pages_languages_list_toolbar');
    Ext.getCmp('content-panel').layout.setActiveItem('rm_pages_languages_list');
    RM.Pages.Languages_List_Main_Grid.store.reload();
    RM.Help.Load('Admin.Language.List.Main');
};

RM.Pages.Languages_List_Main_Grid_Store_Fields = [
    {name: 'iso'},
    {name: 'name'},    
    {name: 'enabled'}
];

RM.Pages.Languages_List_Main_Grid_Columns_Image = function(value){
    if (value == 1) {
        return '<img src="' + RM.BaseSmallImageURL + 'tick.gif"/>';
    } else {
        return '<img src="' + RM.BaseSmallImageURL + 'cross.gif"/>';
    }
};

RM.Pages.Languages_List_Main_Grid_Columns_SM = new Ext.grid.CheckboxSelectionModel({
    header : "",
    singleSelect : true,
    listeners: {
        'rowselect' : function(grid, number, event){
            var selectedISO = event.data.iso;
            RM.Pages.Languages_List_Info_Grid_Store.baseParams = {
                iso : selectedISO
            };
            RM.Pages.Languages_List_Info_Grid_Store.reload();
        }
    }
});

RM.Pages.Languages_List_Main_Grid_Columns_Rows = [
    RM.Pages.Languages_List_Main_Grid_Columns_SM,
    {id:'iso', header: RM.Translate.Admin.System.Language.ISO, width: 50, sortable: true, dataIndex: 'iso', hideable: false},
    {id: 'name', header: RM.Translate.Admin.System.Language.Name, width: 75, sortable: true, dataIndex: 'name', hideable: false},
    {header: RM.Translate.Admin.System.Language.Enabled, width: 75, sortable: true, renderer: RM.Pages.Languages_List_Main_Grid_Columns_Image, dataIndex: 'enabled', hideable: false}
];
RM.Pages.Languages_List_Main_Grid_Columns = new Ext.grid.ColumnModel(RM.Pages.Languages_List_Main_Grid_Columns_Rows);
RM.Pages.Languages_List_Main_Grid_Store = new Ext.data.JsonStore({
    url: RM.Common.AssembleURL({
        controller: 'Language',
        action: 'maingridjson'
    }),
    id: 'rm_language_list_main_grid_store',
    totalProperty: 'total',
    root: 'data',
    fields: RM.Pages.Languages_List_Main_Grid_Store_Fields    
});

RM.Pages.Languages_List_Main_Grid = new Ext.grid.GridPanel({
    id : 'rm_language_list_main_grid',
    enableColumnHide: false,
    enableColumnMove: false,
    region: 'west',
    autoExpandColumn: 'name',
    enableColLock : false,
    loadMask : {
        msg: RM.Translate.Common.PleaseWait
    },
    height: 150,
    cm : RM.Pages.Languages_List_Main_Grid_Columns,
    ds : RM.Pages.Languages_List_Main_Grid_Store,
    sm : RM.Pages.Languages_List_Main_Grid_Columns_SM,
    viewConfig: {
        forceFit: true
    }
});

RM.Pages.Languages_List_Info_Grid_Columns_Type = function(value){
    var temp_array = value.split(",");
    var type = temp_array[0];
    var name = temp_array[1];
    if (type === 'main') {
        return RM.Translate.Admin.System.Language.Main;
    }
    return name;
};
RM.Pages.Languages_List_Info_Grid_Columns_Actions = function(value){
    var temp_array = value.split(",");
    var iso = temp_array[0];
    var filename = temp_array[1];
    var type = temp_array[2];
    var name = temp_array[3];
    return '<a href="javascript:RM.Pages.Languages_List_Info_Grid_Edit(\''+iso+'\', \''+filename+'\', \''+type+'\', \''+name+'\')"><img src="' + RM.BaseSmallImageURL + 'edit.gif"/></a>';
};
RM.Pages.Languages_List_Info_Grid_Columns = [
    {id:'filename', header: RM.Translate.Admin.System.Language.Filename, width: 160, sortable: true, dataIndex: 'filename', hideable: false, groupable: false},
    {id:'type', header: RM.Translate.Admin.System.Language.Type, width: 160, sortable: false, renderer: RM.Pages.Languages_List_Info_Grid_Columns_Type, dataIndex: 'type', hidden: true},
    {id:'action', header: RM.Translate.Admin.System.Language.Action, width: 75, sortable: false, renderer: RM.Pages.Languages_List_Info_Grid_Columns_Actions, dataIndex: 'action', hideable: false, groupable: false}
];
RM.Pages.Languages_List_Info_Grid_Store_Reader = new Ext.data.ArrayReader({}, [   
   {name: 'filename', type: 'string'},
   {name: 'type', type: 'string'},
   {name: 'action', type: 'string'}
]);
RM.Pages.Languages_List_Info_Grid_Store = new Ext.data.GroupingStore({
    reader: RM.Pages.Languages_List_Info_Grid_Store_Reader,
    sortInfo:{field: 'type', direction: "ASC"},
    url: RM.Common.AssembleURL({
        controller: 'Language',
        action: 'infogridjson'
    }),
    groupField: 'type',
    id: 'rm_language_list_info_grid_store',
    totalProperty: 'total',
    root: 'data'    
});

RM.Pages.Languages_List_Info_Grid = new Ext.grid.GridPanel({
    id : 'rm_language_list_info_grid',   
    region: 'east',
    enableColumnHide: false,
    enableColumnMove: false,
    autoExpandColumn: 'action',
    height: RM.Common.GetPanelHeight(294),
    autoScroll: true,
    enableColLock : false,
    loadMask : {
        msg: RM.Translate.Common.PleaseWait
    },
    columns : RM.Pages.Languages_List_Info_Grid_Columns,
    ds : RM.Pages.Languages_List_Info_Grid_Store,
    view: new Ext.grid.GroupingView({
        forceFit: true,
        showGroupName: false,
        groupTextTpl: '{text} ({[values.rs.length]} {[values.rs.length > 1 ? "'+RM.Translate.Admin.System.Language.Items+'" : "'+RM.Translate.Admin.System.Language.Item+'"]})'
    })
});

RM.Pages.Languages_List = new Ext.Panel({
    id : 'rm_pages_languages_list',
    title : RM.Translate.Common.List,
    iconCls: "RM_units_default_root_icon",
    autoScroll: true,
    height: RM.Common.GetPanelHeight(104),
    defaults: {
        split: true
    },
    items : [        
        RM.Pages.Languages_List_Main_Grid,
        RM.Pages.Languages_List_Info_Grid
    ],
    bbar: new Ext.ux.StatusBar({
        id: 'rm_pages_languages_statusbar',
        items: []
    })
});

RM.Pages.Reservations_StatusBar = Ext.getCmp('rm_pages_languages_statusbar');
RM.Pages.Reservations_StatusBar.setStatus({
    iconCls: 'ok-icon'
});

RM.Main.Pages.push(RM.Pages.Languages_List);