RM.Pages.FormDesigner_Pages_List_Toolbar = {
    xtype : "panel",
    id : "rm_formdesigner_pages_list_toolbar",
    bodyBorder : false,
    html : RM.Common.getToolbar([
        {image: RM.BaseLargeImageURL+"resetformdeslayouts.gif", label: RM.Translate.Admin.Formdesigner.ControlPanel.ResetLayoutButton, link: "RM.Pages.Functions.FormDesigner_resetFormLayout()"},
        {image: RM.BaseLargeImageURL+"clearformdeslayouts.gif", label: RM.Translate.Admin.Formdesigner.ControlPanel.ClearLayoutButton, link: "RM.Pages.Functions.FormDesigner_clearFormLayout()"},
        {image: RM.BaseLargeImageURL+"editcss.gif", label: RM.Translate.Admin.Formdesigner.ControlPanel.EditCSS, link: "RM.Pages.Functions.FormDesigner_editCSS_ButtonHandler()"}

    ])
};
RM.Toolbars.push(RM.Pages.FormDesigner_Pages_List_Toolbar);

// reset form layout
RM.Pages.Functions.FormDesigner_resetFormLayout = function(){

    Ext.Msg.show({
       title: RM.Translate.Admin.Formdesigner.ControlPanel.ResetLayout,
       msg: RM.Translate.Admin.Formdesigner.ControlPanel.ResetLayoutConfirmMSG,
       buttons: Ext.Msg.YESNO,
       animEl: 'elId',
       icon: Ext.MessageBox.QUESTION,
       fn: function(buttonID){

            if (buttonID === 'cancel') {
                return;
            }

            // reset the form designer layout
            var myMask = new Ext.LoadMask('rm_formdesigner_pages_list', {msg:RM.Translate.Common.PleaseWait});
            myMask.show();

            var parametersJson = [];
            var request = {
                url: RM.Common.AssembleURL({
                    controller : 'FormDesigner',
                    action: 'resetformsjson',
                    parameters : parametersJson
                }),
                method: 'POST',
                success: function(responseObject) {
                    myMask.hide();
                },
                failure: function() {
                    myMask.hide();
                    Ext.MessageBox.alert(RM.Translate.Common.DeleteFailure);
                }
            };
            var conn = new Ext.data.Connection();
            conn.request(request);
       }
    });
};

// clear form layout
RM.Pages.Functions.FormDesigner_clearFormLayout = function(){
    Ext.Msg.show({
       title: RM.Translate.Admin.Formdesigner.ControlPanel.ClearLayout,
       msg: RM.Translate.Admin.Formdesigner.ControlPanel.ClearLayoutConfirmMSG,
       buttons: Ext.Msg.YESNO,
       animEl: 'elId',
       icon: Ext.MessageBox.QUESTION,
       fn: function(buttonID){

            if (buttonID === 'cancel') {
                return;
            }

            // clear the form designer layout
            var myMask = new Ext.LoadMask('rm_formdesigner_pages_list', {msg:RM.Translate.Common.PleaseWait});
            myMask.show();

            var parametersJson = [];
            var request = {
                url: RM.Common.AssembleURL({
                    controller : 'FormDesigner',
                    action: 'clearformsjson',
                    parameters : parametersJson
                }),
                method: 'POST',
                success: function(responseObject) {
                    myMask.hide();
                },
                failure: function() {
                    myMask.hide();
                    Ext.MessageBox.alert(RM.Translate.Common.DeleteFailure);
                }
            };
            var conn = new Ext.data.Connection();
            conn.request(request);

       }
    });
};

/**
* edit the usercss stylesheet
*/
RM.Pages.Functions.FormDesigner_editCSS = function(data){
    if (RM.Pages.FormDesigner_editCSS_Edit_Window) {
       RM.Pages.FormDesigner_editCSS_Edit_Window.destroy();
    }
    if (RM.Pages.FormDesigner_editCSS_Edit_Form) {
       RM.Pages.FormDesigner_editCSS_Edit_Form.destroy();
    }
    RM.Pages.FormDesigner_editCSS_Edit_Form = new Ext.FormPanel({
        id : 'formdesigner-edit-panel',
        frame: false,
        border: false,
        bodyStyle : "padding:10px",
        layout: 'form',
        items: {
            xtype : 'textarea',
            fieldLabel: RM.Translate.Admin.Formdesigner.EditCSS.FileContents,
            id: 'formdesigner-edit-panel-text',
            name: 'content',
            width: 450,
            height: 400
        }
    });

    RM.Pages.FormDesigner_editCSS_Edit_Window = new Ext.Window({
        xtype : 'panel',
        title : RM.Translate.Admin.Formdesigner.EditCSS.Title,
        width : 600,
        height : 500,
        closeAction :'hide',
        layout: 'form',
        plain : true,
        items : [
            RM.Pages.FormDesigner_editCSS_Edit_Form,
            {
            xtype: 'button',
            style: "float: right;margin-right: 20px;margin-left: 20px;",
            frame: false,
            border: false,
            text: RM.Translate.Common.Save,
            handler: function(){
                if(RM.Pages.FormDesigner_editCSS_Edit_Form.getForm().isValid()){
                    RM.Pages.FormDesigner_editCSS_Edit_Form.getForm().submit({
                        url: RM.Common.AssembleURL({
                            controller: 'FormDesigner',
                            action: 'savecssjson'
                        }),
                        params: {
                            'contents': Ext.getCmp("formdesigner-edit-panel-text").getValue()
                        },
                        success: function(form, action) {
                            RM.Pages.FormDesigner_editCSS_Edit_Window.hide();
                        },
                        failure: function(form, action){
                            RM.Pages.FormDesigner_editCSS_Edit_Form_Window.hide();
                            Ext.Msg.alert(RM.Translate.Common.Failed, Admin.Formdesigner.EditCSS.SaveFailed);
                        },
                        waitMsg: RM.Translate.Common.Saving,
                        waitTitle: RM.Translate.Common.PleaseWait
                    });
                }
            }
        }]
    });



    Ext.getCmp('formdesigner-edit-panel-text').setValue(data.text);
    RM.Pages.FormDesigner_editCSS_Edit_Window.show();
    RM.Pages.FormDesigner_editCSS_Edit_Form.doLayout();
};

RM.Pages.Functions.FormDesigner_editCSS_ButtonHandler = function(){
    var myMask = new Ext.LoadMask('content-panel', {msg:RM.Translate.Common.PleaseWait});
    myMask.show();
    var conn = new Ext.data.Connection();
    var request = {
        url: RM.Common.AssembleURL({
            controller : 'FormDesigner',
            action: 'editcssjson'
        }),
        method: 'POST',
        success: function(responseObject) {
            myMask.hide();
            var data = Ext.util.JSON.decode(responseObject.responseText);
            RM.Pages.Functions.FormDesigner_editCSS(data);
        },
        failure: function() {
            myMask.hide();
            RM.Pages.Reservations_StatusBar.setStatus({
                iconCls: 'failed-icon',
                text: RM.Translate.Admin.System.Language.SavingFailed
            });
            //Ext.MessageBox.alert(RM.Translate.Admin.System.Language.EditFailed);
        }
    };
    conn.request(request);

    
};

RM.Pages.Functions.FormDesigner_controlpanelJson = function(responseObject) {

    Ext.getCmp('content-panel').layout.setActiveItem('rm_formdesigner_pages_list');
    Ext.getCmp('toolbar_panel').layout.setActiveItem('rm_formdesigner_pages_list_toolbar');

    Ext.state.Manager.setProvider(new Ext.state.CookieProvider());

    RM.Pages.FormDesigner_Pages_List_Json_Store.load({params:{start: 0, limit: 30}});
    RM.Pages.FormDesigner_Pages_List.render();

    // expand the menu
    var rootNode = Ext.getCmp("rm_main_tree_menu").getRootNode();
    Ext.getCmp("rm_main_tree_menu").expandPath("/"+rootNode.id+"/Modules_InfoJson/FormDesigner_controlpanelJson_EditJson"); // restore current node
    
    RM.Help.Load('Admin.FormDesigner.main'); // load the help
};

RM.Pages.FormDesigner_Pages_List_Json_Store_Fields = [
    {name: "id"},
    {name: "name"}
];

RM.Pages.FormDesigner_Pages_List_Json_Store  = new Ext.ux.grid.livegrid.Store({
    url: RM.Common.AssembleURL({
        controller: 'FormDesigner',
        action: 'controlpanelpagelistJson'
    }),
    reader: new Ext.ux.grid.livegrid.JsonReader({
            totalProperty: 'total',
            root: 'data'
        },
        RM.Pages.FormDesigner_Pages_List_Json_Store_Fields
    ),
    id: 'rm_formdesigner_pages_list_grid_json_store',
    sortInfo: {field: 'id', direction: 'DESC'},
    bufferSize : 300
});

//RM.Pages.FormDesigner_Pages_List_Columns_SM = new Ext.rm.grid.CheckboxSelectionModel({
//    singleSelect: false,
//    header: '',
//    checkOnly: true
//});
RM.Pages.FormDesigner_Pages_List_Columns_Rows = [
    RM.Pages.Plugins_List_Columns_SM,
    {dataIndex: 'id', header: RM.Translate.Common.Id, hidden: true},
    {dataIndex: 'name', header: RM.Translate.Common.Name}
];
RM.Pages.FormDesigner_Pages_List_Columns = new Ext.grid.ColumnModel({
    columns: RM.Pages.FormDesigner_Pages_List_Columns_Rows,
    defaults: {
        sortable: true
    }
});
RM.Pages.FormDesigner_Pages_List_Filters = new Ext.ux.grid.GridFilters({
    filters: [
        {dataIndex: 'id', type: 'string'},
        {dataIndex: 'name', type: 'string'}
    ]
});

RM.Pages.FormDesigner_Pages_List_Grid_View = new Ext.ux.grid.livegrid.GridView({
    nearLimit : 100,
    loadMask  : {
        msg :  'Buffering. Please wait...'
    }
});

RM.Pages.FormDesigner_Pages_List_Grid = new Ext.ux.grid.livegrid.GridPanel({
    id : 'rm_formdesigner_pages_list_grid',
    plugins: RM.Pages.FormDesigner_Pages_List_Filters,
    bbar     : new Ext.ux.grid.livegrid.Toolbar({
        view        : RM.Pages.FormDesigner_Pages_List_Grid_View,
        displayInfo : true
    }),
    enableColLock : false,
    loadMask : {
        msg: RM.Translate.Common.PleaseWait
    },
    height: RM.Common.GetPanelHeight(104),
    cm : RM.Pages.FormDesigner_Pages_List_Columns,
    store : RM.Pages.FormDesigner_Pages_List_Json_Store,
    selModel : RM.Pages.FormDesigner_Pages_List_Columns_SM,
    view     : RM.Pages.FormDesigner_Pages_List_Grid_View,
    viewConfig: {
        forceFit: true
    },
    autoExpandColumn: 2
});

RM.Pages.FormDesigner_Pages_List = new Ext.Panel({
  id : 'rm_formdesigner_pages_list',
  title : RM.Translate.Admin.Formdesigner.ControlPanel.FormsInstalled,
  iconCls: "RM_units_default_root_icon",
  items : [
      RM.Pages.FormDesigner_Pages_List_Grid
  ]
});

RM.Main.Pages.push(RM.Pages.FormDesigner_Pages_List);