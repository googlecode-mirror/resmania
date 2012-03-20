/*
* Categories Unit Edit Page JS
*
* JSLint.com Check: 18/03/2011
*/
RM.Pages.Categories_Unit_Tree_Loader = new Ext.tree.TreeLoader({
    dataUrl: RM.Common.AssembleURL({
        controller : 'Categories',
        action: 'treeunitjson'
    }),
    baseParams: {}
});

RM.Pages.Categories_Unit_Tree_Loader.on("beforeload", function(treeLoader, node) {    
    RM.Pages.Categories_Unit_Tree_Loader.baseParams.unit_id = RM.Pages.Units_Edit_UnitID;
}, this);

RM.Pages.Categories_Unit_Tree_Loader.on("load", function(treeLoader, node) {
    RM.Pages.Categories_Unit_Tree.expandAll();
}, this);

RM.Pages.Categories_Unit_Tree = new Ext.ux.tree.CheckTreePanel({   
    id : 'rm_pages_categories_unit_tree',
    title: " ",
    autoWidth: true,
    height: RM.Common.GetPanelHeight(145),
    xtype : 'checktreepanel',
    useArrows: true,
    bubbleCheck: 'none',
    deepestOnly: true,
    autoScroll: true,
    animate: true,
    containerScroll: true,
    rootVisible:false,
    loader: RM.Pages.Categories_Unit_Tree_Loader,
    root: {
        nodeType: 'async',
        text: RM.Translate.Admin.Categories.Edit.Tree,
        id: 'root',
        expanded: true
    },

    tools: [{
        id:'plus',
        handler: function() {
            RM.Pages.Categories_Unit_Tree.expandAll();
        }
    },{
        id:'minus',
        handler: function() {
            RM.Pages.Categories_Unit_Tree.collapseAll();
        }
    },{
        id:'refresh',
        handler: function() {
            RM.Pages.Categories_Unit_Tree.getRootNode().reload();
        }
    }]
});

RM.Pages.Categories_Unit_Tree.on('checkchange', function(node, checked){
    var unit_id = RM.Pages.Units_Edit_UnitID;
    
    var myMask = new Ext.LoadMask('rm_pages_categories_unit_tree', {msg:RM.Translate.Common.PleaseWait});
    myMask.show();

    var conn = new Ext.data.Connection();
    var request = {
        url: RM.Common.AssembleURL({
            controller : 'Categories',
            action: 'assignjson'
        }),
        method: 'POST',
        success: function(responseObject) {
            myMask.hide();
        },
        failure: function() {
            myMask.hide();
            Ext.Msg.alert('Status', RM.Translate.Common.UnableToShow);
        },
        params: {
            'category_id' : node.attributes.id,
            'unit_id' : unit_id,
            'checked' : node.attributes.checked
        }
    };
    conn.request(request);
    return true;
});

RM.Pages.Categories_Unit = new Ext.Panel({
    title: RM.Translate.Admin.Categories.Unit.Tabtitle,
    items: [RM.Pages.Categories_Unit_Tree],
    listeners: {
        'show' : function(){
            RM.Pages.Categories_Unit_Tree.getRootNode().reload();
        }
    }
});

RM.Pages.Units_Edit_TabPanel.add(RM.Pages.Categories_Unit);

