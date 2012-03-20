/*
* Categories Edit Main JS
*
* JSLint.com Check: 18/03/2011
*/

RM.Pages.Functions.Categories_Edit_New = function(){
    var myMask = new Ext.LoadMask('rm_pages_categories_edit', {
        msg:RM.Translate.Common.PleaseWait
        });
    myMask.show();

    var selectedNode = RM.Pages.Categories_Edit_Tree.getSelectionModel().getSelectedNode();
    if (selectedNode === null) {
        selectedNodeID = 0;
    } else {
        selectedNodeID = selectedNode.id;
    }

    var conn = new Ext.data.Connection();
    var request = {
        url: RM.Common.AssembleURL({
            controller : 'Categories',
            action: 'insertjson',
            parameters: [{
                name : "parent_id",
                value : selectedNodeID
            }]
        }),
        method: 'POST',
        success: function(responseObject) {
            myMask.hide();
            RM.Pages.Categories_Edit_Tree.getRootNode().reload();
        },
        failure: function() {
            myMask.hide();
            Ext.Msg.alert('Status', RM.Translate.Common.UnableToShow);
        }
    };
    conn.request(request);
};

RM.Pages.Functions.Categories_Edit_Delete = function(id)
{
    Ext.MessageBox.confirm(RM.Translate.Common.Delete, RM.Translate.Admin.Categories.Edit.DeleteAlert, function(buttonID){
        if (buttonID !== 'yes') {
            return;
        }
        var myMask = new Ext.LoadMask('rm_pages_categories_edit', {
            msg:RM.Translate.Common.PleaseWait
            });
        myMask.show();
        
        var conn = new Ext.data.Connection();
        var request = {
            url: RM.Common.AssembleURL({
                controller : 'Categories',
                action: 'deletejson',
                parameters : [{
                        name : "id",
                        value : id
                }]
            }),
            method: 'POST',
            success: function(responseObject) {
                myMask.hide();
                RM.Pages.Categories_Edit_Window.hide();
                RM.Pages.Categories_Edit_Tree.getRootNode().reload();
            },
            failure: function() {
                myMask.hide();
                Ext.MessageBox.alert(RM.Translate.Common.DeleteFailure);
            }
        };
        conn.request(request);
    });
};

RM.Pages.Functions.Categories_EditJson_Request = function(){
    var myMask = new Ext.LoadMask('rm_pages_categories_edit', {
        msg:RM.Translate.Common.PleaseWait
    });
    myMask.show();

    var conn = new Ext.data.Connection();
    var request = {
        url: RM.Common.AssembleURL({
            controller : 'Categories',
            action: 'editjson'
        }),
        method: 'POST',
        success: function(responseObject) {
            eval('RM.Pages.Functions.Categories_EditJson('+responseObject.responseText+');');
            myMask.hide();
        },
        failure: function() {
            Ext.Msg.alert('Status', RM.Translate.Common.UnableToShow);
        }
    };
    conn.request(request);
};

RM.Pages.Categories_Edit_Toolbar = {
    xtype : "panel",
    id : "rm_pages_categories_edit_toolbar",
    bodyBorder : false,
    html : RM.Common.getToolbar([
    {
        image: RM.BaseLargeImageURL+"new.gif",
        label: RM.Translate.Common.New,
        link: "RM.Pages.Functions.Categories_Edit_New()"
    }
    ])
};
RM.Toolbars.push(RM.Pages.Categories_Edit_Toolbar);

RM.Pages.Functions.Categories_EditJson = function (responseObject) {   
    RM.Pages.Categories_Edit_Tree.getRootNode().reload();

    var i = 0;for (i; i < responseObject.languages.length; i++){
        var language = responseObject.languages[i];      
        var textID = 'rm_category[' + language.iso + ']';

        if (Ext.getCmp(textID)){
            RM.Common.RemoveOldFields(Ext.getCmp(textID).id);
        }
        
        Ext.getCmp('rm_pages_categories_edit_form').add(new Ext.form.TextField({
            id : textID,
            fieldLabel: language.name,
            labelWidth: 190
        }));
    }
    
    Ext.getCmp('toolbar_panel').layout.setActiveItem('rm_pages_categories_edit_toolbar');
    Ext.getCmp('content-panel').layout.setActiveItem('rm_pages_categories_edit');

    // expand the tree
    Ext.getCmp("rm_pages_categories_edit_tree").expandAll();

    // help
    RM.Help.Load('Admin.Categories.MainHelp');
};

RM.Pages.Categories_Edit_Form = new Ext.FormPanel({
    id : "rm_pages_categories_edit_form",
    border: false,
    width : '100%',
    bodyStyle : "padding:10px",
    url: RM.Common.AssembleURL({
        controller : 'Categories',
        action: 'updatejson'
    }),
    items: [{
        xtype: "hidden",
        id: "rm_category[id]",
        name: "rm_category[id]",
        hidden: true,
        hideLabel : true
    }]
    
});

RM.Pages.Categories_Edit_Window = new Ext.Window({
    id : "rm_pages_categories_edit_window",
    title: RM.Translate.Admin.Categories.Edit.EditCategory,
    height: 300,
    width : 400,
    autoScroll: true,
    closeAction: 'hide',
    items: [
        RM.Pages.Categories_Edit_Form
    ],
    buttons: [{
        text: RM.Translate.Common.Delete,
        handler: function(){
            RM.Pages.Functions.Categories_Edit_Delete(Ext.getCmp("rm_category_tracking[id]").getValue());
        }
    },{
        text: RM.Translate.Common.Cancel,
        handler: function(){
            RM.Pages.Categories_Edit_Window.hide();
        }
    },{
        text: RM.Translate.Common.Save,
        handler: function(){
            // rm_category[id] is used to save the value rm_category_tracking[id] is use to store and track the value
            Ext.getCmp("rm_category[id]").setValue(Ext.getCmp("rm_category_tracking[id]").getValue());
            RM.Pages.Categories_Edit_Form.getForm().submit({
                success: function(form, action) {
                    RM.Pages.Categories_Edit_Tree.getRootNode().reload();
                    Ext.getCmp('rm_pages_categories_edit_statusbar').setStatus({
                        text: RM.Translate.Common.Saved,
                        iconCls: 'ok-icon'
                    });
                },
                waitMsg: RM.Translate.Common.Saving,
                waitTitle: RM.Translate.Common.PleaseWait
            });
            RM.Pages.Categories_Edit_Window.hide();
        }
    }]
});

RM.Pages.Categories_Edit_Tree = new Ext.tree.TreePanel({
        title: RM.Translate.Admin.Categories.Edit.Tree,
        id: "rm_pages_categories_edit_tree",
        layout: 'fit',
        useArrows:true,
        autoScroll:true,
        animate:true,
        enableDD:true,
        containerScroll: true,
        rootVisible: false,
        frame: false,
        root: {
            nodeType: 'async'
        },
        dataUrl: RM.Common.AssembleURL({
            controller : 'Categories',
            action: 'treejson'
        }),
        listeners: {
            'enddrag' : function( tp, node, event ){
                var conn = new Ext.data.Connection();
                var request = {
                    url: RM.Common.AssembleURL({
                        controller : 'Categories',
                        action: 'treejson',
                        parameters : [{
                            name : "cmd",
                            value : "moveTreeNode"
                        },{
                            name : "target",
                            value : node.parentNode.id
                        },{
                            name : "id",
                            value : node.id
                        },{
                            name : "point",
                            value : node.events.append
                        }]
                    }),
                    method: 'POST',
                    success: function(responseObject) {
                        RM.Pages.Categories_Edit_Tree.getRootNode().reload();
                    },
                    failure: function() {
                        Ext.Msg.alert('Status', RM.Translate.Common.UnableToShow);
                    }
                };
                conn.request(request);
            }
        }
});

RM.Pages.Categories_Edit_Tree.on('dblclick', function(node, e){
    if (node.id < 1) {
        return true;
    }
    var myMask = new Ext.LoadMask('rm_pages_categories_edit', {msg:RM.Translate.Common.PleaseWait});
    myMask.show();

    Ext.getCmp('rm_category_tracking[id]').setValue(node.id);
    var conn = new Ext.data.Connection();
    var request = {
        url: RM.Common.AssembleURL({
            controller : 'Categories',
            action: 'getjson',
            parameters : [{
                name : "id",
                value : node.id
            }]
        }),
        method: 'POST',
        success: function(responseObject) {
            myMask.hide();
            RM.Pages.Categories_Edit_Window.show();
            Ext.getCmp('rm_pages_categories_edit_form').doLayout();
            var json = Ext.util.JSON.decode(responseObject.responseText);
            var i = 0;for (i; i < json.length; i++){
                var language = json[i];
                var textID = 'rm_category[' + language.iso + ']';
                Ext.getCmp(textID).setValue(language.value);
            }
        },
        failure: function() {
            myMask.hide();
            Ext.Msg.alert('Status', RM.Translate.Common.UnableToShow);
        }
    };
    conn.request(request);
    return false;
});


RM.Pages.Categories_Edit = new Ext.Panel({
    id : 'rm_pages_categories_edit',
    title : RM.Translate.Common.Edit,
    layout : 'fit',
    iconCls : "RM_categories_root_icon",    
    bodyStyle : "padding:10px",    
    items : [
        RM.Pages.Categories_Edit_Tree
    ,{
        xtype: "hidden",
        id: "rm_category_tracking[id]",
        name: "rm_category_tracking[id]",
        hidden: true,
        value: 0,
        hideLabel : true,
        hideMode: "display",
        labelSeparator: ""
    }],
    bbar: new Ext.ux.StatusBar({
        id: 'rm_pages_categories_edit_statusbar',
        items: []
    })
});

RM.Main.Pages.push(RM.Pages.Categories_Edit);