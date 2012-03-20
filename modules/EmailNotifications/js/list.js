/*
* Notifications List JS
* This creates the admin GUI Notifications List Page
*
* JSLint.com Check: 18/03/2011
*/

RM.Pages.Functions.notifications_list_save = function(){
    var records = RM.Pages.Notifications_List_Json_Store.getModifiedRecords();
    if(!records.length) {
        return;
    }

    var myMask = new Ext.LoadMask('content-panel', {msg:RM.Translate.Common.PleaseWait});
    myMask.show();

    var data = [];
    Ext.each(records, function(r, i) {
        var o = r.getChanges();
        if(r.data.newRecord) {
            o.newRecord = true;
        }
        o.id = r.get('id');
        o.event_name = r.get('event_name');
        o.destination = r.get('destination');
        o.enabled = r.get('enabled');
        data.push(o);
    }, this);

    var o = {
         url:RM.Common.AssembleURL({
            controller : 'EmailNotifications',
            action: 'updatelistjson'
         })
        ,method:'post'
        ,scope:this
        ,params:{
            data: Ext.encode(data)
        }
        ,success: function(responseObject) {
            RM.Pages.Notifications_List_Json_Store.commitChanges();
            RM.Pages.Notifications_List_Json_Store.load();
            myMask.hide();
        },
        failure: function() {
            // todo: add error message
            myMask.hide();
        }
    };
    Ext.Ajax.request(o);
};

RM.Pages.Functions.notifications_list_delete = function(){
    var selections = RM.Pages.Notifications_List_Columns_SM.getSelections();
    if (selections.length === 0){
        Ext.Msg.alert(RM.Translate.Common.Status, RM.Translate.Common.NeedToSelectRows);
        return;
    }
    Ext.MessageBox.confirm(RM.Translate.Common.Delete, RM.Translate.Admin.Extras.Edit.DeleteAlert, function(buttonID){
        if (buttonID !== 'yes') {
            return;
        }

        var selections = RM.Pages.Notifications_List_Columns_SM.getSelections();
        var parametersJson = [];
        var i = 0;for (i; i < selections.length; i++){
            parametersJson[i] = {
                name : 'ids[]',
                value : selections[i].data.id
            };
        }

        var myMask = new Ext.LoadMask('content-panel', {msg:RM.Translate.Common.PleaseWait});
        myMask.show();
        var conn = new Ext.data.Connection();
        var request = {
            url: RM.Common.AssembleURL({
                controller : 'EmailNotifications',
                action: 'deletejson',
                parameters : parametersJson
            }),
            method: 'POST',
            success: function(responseObject) {
                RM.Pages.Notifications_List_Json_Store.commitChanges();
                RM.Pages.Notifications_List_Json_Store.load();
                myMask.hide();
            },
            failure: function() {
                Ext.MessageBox.alert(RM.Translate.Common.UnconfirmFailure);
                myMask.hide();
            }
        };
        conn.request(request);
    });
};

RM.Pages.Notifications_List_Grid_Create_Row = Ext.data.Record.create([
    {name: 'id', type: 'numeric'},
    {name: 'event_name', type: 'string'},
    {name: 'destination', type: 'numeric'},
    {name: 'enabled', type: 'boolean'}
]);

RM.Pages.Functions.notifications_list_new = function(){
    var newRow = new RM.Pages.Notifications_List_Grid_Create_Row({
        id: 0,
        event_name: RM.Translate.Admin.Notifications.List.SelectEvent,
        destination: 0,
        enabled: false
    });
    RM.Pages.Notifications_List_Json_Store.add(newRow);
};


RM.Pages.Functions.notifications_list_edit = function(){
    var selections = RM.Pages.Notifications_List_Columns_SM.getSelections();
    if (selections.length === 0){
        Ext.Msg.alert(RM.Translate.Common.Status, RM.Translate.Common.NeedToSelectRows);
        return;
    }

    if (selections[0].data.id === 0){
        Ext.Msg.alert(RM.Translate.Common.Status, RM.Translate.Admin.Notifications.List.SaveFirst);
        return;
    }

    var myMask = new Ext.LoadMask('rm_pages_notifications_list', {msg:RM.Translate.Common.PleaseWait});
    myMask.show();
    var conn = new Ext.data.Connection();
    var request = {
        url: RM.Common.AssembleURL({
            controller : 'EmailNotifications',
            action: 'editjson',
            parameters : [{
                name : 'id',
                value : selections[0].data.id
            }]
        }),
        method: 'POST',
        success: function(responseObject) {
            myMask.hide();
            RM.Pages.Functions.EmailNotifications_EditJson(responseObject);
        },
        failure: function() {
            myMask.hide();
            Ext.MessageBox.alert(RM.Translate.Common.ConfirmFailure);
        }
    };
    conn.request(request);
};

RM.Pages.Functions.Notifications_Edit_Msg_Request = function(msg_id){
    var myMask = new Ext.LoadMask('content-panel', {msg:RM.Translate.Common.PleaseWait});
    myMask.show();

    var conn = new Ext.data.Connection();
    var request = {
        url: RM.Common.AssembleURL({
            controller : 'EmailNotifications',
            action: 'editjson',
            parameters : [{
                name : 'id',
                value : msg_id
            }]
        }),
        method: 'POST',
        success: function(responseObject) {
            eval('RM.Pages.Functions.EmailNotifications_EditJson('+responseObject.responseText+');');
            myMask.hide();
        },
        failure: function() {
            Ext.Msg.alert(RM.Translate.Common.Status, RM.Translate.Common.UnableToShow);
            myMask.hide();
        }
    };
    conn.request(request);
};

RM.Pages.Functions.Notifications_ListJson_Request = function(){
    var myMask = new Ext.LoadMask('rm_pages_notifications_list', {msg:RM.Translate.Common.PleaseWait});
    myMask.show();

    var conn = new Ext.data.Connection();
    var request = {
        url: RM.Common.AssembleURL({
            controller : 'EmailNotifications',
            action: 'listjson'
        }),
        method: 'POST',
        success: function(responseObject) {
            eval('RM.Pages.Functions.EmailNotifications_ListJson('+responseObject.responseText+');');
            myMask.hide();
        },
        failure: function() {
            Ext.Msg.alert('Status', RM.Translate.Common.UnableToShow);
            myMask.hide();
        }
    };
    conn.request(request);
};

RM.Pages.Notifications_List_Toolbar = {
    xtype : "panel",
    id : "rm_pages_notifications_list_toolbar",
    bodyBorder : false,
    html : RM.Common.getToolbar([
        {image: RM.BaseLargeImageURL+"new.gif", label: RM.Translate.Common.New, link: "RM.Pages.Functions.notifications_list_new()"},
        {image: RM.BaseLargeImageURL+"save.gif", label: RM.Translate.Common.Save, link: "RM.Pages.Functions.notifications_list_save()"},
        {image: RM.BaseLargeImageURL+"edit.gif", label: RM.Translate.Admin.Notifications.List.EditMsg, link: "RM.Pages.Functions.notifications_list_edit()"},
        {image: RM.BaseLargeImageURL+"delete.gif", label: RM.Translate.Common.Delete, link: "RM.Pages.Functions.notifications_list_delete()"}
    ])
};
RM.Toolbars.push(RM.Pages.Notifications_List_Toolbar);

RM.Pages.Functions.EmailNotifications_ListJson= function (responseObject) {
    RM.Pages.Notifications_List.on('tabchange', function (page, currentTab){
        currentTab.doLayout();
    });

    Ext.getCmp('toolbar_panel').layout.setActiveItem('rm_pages_notifications_list_toolbar');

    Ext.state.Manager.setProvider(new Ext.state.CookieProvider());

    Ext.getCmp('content-panel').layout.setActiveItem('rm_pages_notifications_list');
    RM.Pages.Notifications_List_Json_Store.load({params:{start: 0, limit: 15}});
    RM.Pages.Notifications_List_Grid.render();

    //RM.Help.Load('Admin.Notifications.List.Main');
};

RM.Pages.Notifications_List_Json_Store = new Ext.data.JsonStore({
    url: RM.Common.AssembleURL({
        controller: 'EmailNotifications',
        action: 'listjson'
    }),
    id: 'rm_notifications_list_grid_json_store',
    totalProperty: 'total',
    fields: [
        {name: "id"},
        {name: "event_name"},
        {name: "destination"},
        {name: "enabled"}
    ],
    sortInfo: {field: 'id', direction: 'ASC'},
    remoteSort: true
});

RM.Pages.Notifications_List_EventName_Selection = new Ext.data.JsonStore({
    url: RM.Common.AssembleURL({
        controller: 'EmailNotifications',
        action: 'listeventnamesjson'
    }),
    id: 'rm_notifications_list_eventnames_store',
    fields: [
        {name: "name"}
    ],
    autoLoad: true
});

RM.Pages.Notifications_List_UserGroup_Selection = new Ext.data.JsonStore({
    url: RM.Common.AssembleURL({
        controller: 'UserGroups',
        action: 'getalljson',
        parameters : [{
            name : 'returnall',
            value : true
        }]
    }),
    id: 'rm_notifications_list_usergroups_store',
    fields: [
        {name: "id"},
        {name: "name"}
    ],
    autoLoad: true
});

RM.Pages.Notifications_List_EventName_Enabled_Selection = [
    ['0', RM.Translate.Common.MessageNo],
    ['1', RM.Translate.Common.MessageYes]
];

RM.Pages.Notifications_List_Columns_SM = new Ext.grid.CheckboxSelectionModel({
    singleSelect: true,
    header: ''
});

RM.Pages.Notifications_List_Columns_Rows = [
    RM.Pages.Notifications_List_Columns_SM,
    {
        dataIndex: 'id', header: RM.Translate.Common.Id, hidden: true
    },{
        id: "event_name",
        dataIndex: "event_name",
        header: RM.Translate.Admin.Notifications.List.EventName,
        renderer: function(value, metaData, record, rowIndex, colIndex, store) {
            var i = 0;for (i; i < RM.Pages.Notifications_List_EventName_Selection.length; i++){
                if (RM.Pages.Notifications_List_EventName_Selection[i][0] === value) {
                    return RM.Pages.Notifications_List_EventName_Selection[i][0];
                }
            }
            return value;
        },
        editor: new Ext.form.ComboBox({
            mode: 'local',
            displayField: "name",
            triggerAction: 'all',
            typeAhead: true,
            resizable: true,
            minListWidth: 220,
            store: RM.Pages.Notifications_List_EventName_Selection
        })
    },{
        dataIndex: "destination",
        header: RM.Translate.Admin.Notifications.List.Destination,
        renderer: function(value, metaData, record, rowIndex, colIndex, store) {
            var i = 0;for (i; i < RM.Pages.Notifications_List_UserGroup_Selection.data.items.length; i++){
                if (RM.Pages.Notifications_List_UserGroup_Selection.data.items[i].data.id === value) {
                    return RM.Pages.Notifications_List_UserGroup_Selection.data.items[i].data.name;
                }
            }
            return value;
        },
        editor: new Ext.form.ComboBox({
            mode: 'local',
            valueField: "id",
            displayField: "name",
            triggerAction: 'all',
            typeAhead: true,
            resizable: true,
            minListWidth: 220,
            store: RM.Pages.Notifications_List_UserGroup_Selection
        })
    },{
        dataIndex: "enabled",
        header: RM.Translate.Admin.Notifications.List.Enabled,
        renderer: function(value, metaData, record, rowIndex, colIndex, store) {if (value=="1") {metaData.css = 'RM_list_enabled_icon';} else {metaData.css = 'RM_list_disabled_icon'; }},
        editor: new Ext.form.ComboBox({
            mode: 'local',
            displayField: "name",
            triggerAction: 'all',
            typeAhead: true,
            resizable: true,
            minListWidth: 220,
            store: RM.Pages.Notifications_List_EventName_Enabled_Selection
        })
    }
];

RM.Pages.Notifications_List_Columns = new Ext.grid.ColumnModel({
    columns: RM.Pages.Notifications_List_Columns_Rows,
    defaults: {
        sortable: true
    }
});

RM.Pages.Notifications_List_Filters = new Ext.ux.grid.GridFilters({
    filters: [
        {dataIndex: 'id', type: 'numeric'},
        {dataIndex: 'event_name', type: 'string'},
        {dataIndex: 'destination', type: 'string'},
        {dataIndex: 'enabled', type: 'boolean'}
    ]
});

RM.Pages.Notifications_List_Grid = new Ext.grid.EditorGridPanel({
    id : 'rm_notifications_list_grid',
    clicksToEdit: 1,
    stripeRows: true,
    height : RM.Common.GetPanelHeight(106),
    bbar : new Ext.PagingToolbar({
        store: RM.Pages.Notifications_List_Json_Store,
        pageSize: 15,
        plugins: RM.Pages.Notifications_List_Filters
    }),
    remoteSort: true,
    enableColLock : false,
    loadMask : {
        msg: RM.Translate.Common.PleaseWait
    },
    cm : RM.Pages.Notifications_List_Columns,
    store : RM.Pages.Notifications_List_Json_Store,
    sm : RM.Pages.Notifications_List_Columns_SM,
    viewConfig: {
        forceFit: true
    },
    autoExpandColumn: 'event_name'
});

RM.Pages.Notifications_List = new Ext.Panel({
    id : 'rm_pages_notifications_list',
    title : RM.Translate.Common.List,
    iconCls: "RM_config_notifications_icon",
    items : [RM.Pages.Notifications_List_Grid]
});

RM.Main.Pages.push(RM.Pages.Notifications_List);