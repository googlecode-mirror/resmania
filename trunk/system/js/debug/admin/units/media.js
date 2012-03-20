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
 * Media Manager JS
 * This creates the admin GUI Media Manager Pages
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

RM.Pages.Functions.Units_Edit_Images_JSON_Store_Reload = function(){
    var myMask = new Ext.LoadMask('rm_units_media_drop_dataview', {msg:RM.Translate.Common.PleaseWait});
    myMask.show();
    RM.Pages.Units_Edit_Images_JSON_Store.load({
        params: {
            unit_id : RM.Pages.Units_Edit_UnitID
        },
        callback : function(){
            myMask.hide();
        }
    });
};

/*TODO:
 *
 * 1/ the organizerDD should be a unique rm_* id
 *
 * 2/ The media will only have thumbs when a media image has been dragged from
 * the media manager library to the unit media: this means that the order tree
 * can only show thumbs from the Unit images not the media manager library. We
 * need to change the behaviour slightly, images can be dragged to Unit Media,
 * this will create thumbnails, then images can be dragged from unit Media to
 * the sort and display tree
 *
 * 3/ the upload here should upload only to unit media and not the media manager
 * and not the media manager library this will be important later when we have
 * unit managers and the need to not share all images with all users.
 *
 */

RM.Pages.Functions.Units_Edit_Media_Classification = function(){    
    //1. get unit_id
    var unit_id = RM.Pages.Units_Edit_UnitID;

    //2. get info in format
    //info[{type_id: <type_id>, data: [<file_name>]}] second dimenstion key will be the file id order
    
    var info = [];
    var tree = RM.Pages.Units_Edit_Media_Tree;

    var i = 0;for (i; i < tree.root.childNodes.length; i++){
        element = {};
        element.type_id = tree.root.childNodes[i].attributes.type_id;
        element.data = [];
        var j = 0;for (j; j < tree.root.childNodes[i].childNodes.length; j++){
            element.data.push(tree.root.childNodes[i].childNodes[j].attributes.text);
        }
        info.push(element);
    }    

    //3. post changes to server
    myMask = new Ext.LoadMask('content-panel', {msg:RM.Translate.Common.PleaseWait});
    myMask.show();
    var conn = new Ext.data.Connection();
    var request = {
        url: RM.Common.AssembleURL({
            controller : 'UnitMedia',
            action: 'classificationmediaJson'
        }),
        params : {
            'unit_id' : unit_id,
            'info' : Ext.util.JSON.encode(info)
        },
        method: 'POST',
        success: function(responseObject) {
            myMask.hide();
            RM.Pages.Functions.Units_Edit_Media_Tree_Loader_Reload();
            //RM.Pages.Functions.Units_Edit_Images_JSON_Store_Reload();
        },
        failure: function() {
            myMask.hide();
            Ext.Msg.alert(RM.Translate.Common.Status, RM.Translate.Common.UnableToShow);
        }
    };
    conn.request(request);
    //4. get response from server, enable page
    
};

RM.Pages.Functions.Units_Edit_Media_Add = function(filename){
    //0. disable page with loading message
    myMask = new Ext.LoadMask('content-panel', {msg:RM.Translate.Common.PleaseWait});
    myMask.show();

    //1. get unit_id
    
    //2. get filename from media manager content area
    
    //3. send request to server
    var conn = new Ext.data.Connection();
    var request = {
        url: RM.Common.AssembleURL({
            controller : 'UnitMedia',
            action: 'addjson',
            parameters : [{
                name : 'unit_id',
                value : RM.Pages.Units_Edit_UnitID
            }, {
                name : 'filename',
                value : filename
            }]
        }),
        method: 'POST',
        success: function(responseObject) {           
            myMask.hide();
            RM.Pages.Functions.Units_Edit_Images_JSON_Store_Reload();
        },
        failure: function() {
            myMask.hide();
            Ext.Msg.alert(RM.Translate.Common.Status, RM.Translate.Common.UnableToShow);
        }
    };
    conn.request(request);

    //4. get response from server and enable page
};

RM.Pages.Functions.Units_Edit_Media_Edit = function(){
    //0. disable page with loading message
    var myMask = new Ext.LoadMask('content-panel', {msg:RM.Translate.Common.PleaseWait});
    myMask.show();

    //1. get file id
    //2. get details
    //3. get caption
    //4. get notes
    //5. send request to server
    //6. after response enable page
};

RM.Pages.Functions.Units_Edit_Media_Delete = function(){

    var filename = Ext.getCmp('rm_pages_units_edit_selected_images').getValue();
    if (!filename){
        return;
    }

    Ext.MessageBox.confirm(RM.Translate.Common.Delete, RM.Translate.Admin.System.MediaManager.DeleteImage+" "+filename+"?", function(buttonID){
        if (buttonID !== 'yes') {
            return;
        }

        var myMask = new Ext.LoadMask('content-panel', {msg:RM.Translate.Common.DeletingPleaseWait});
        myMask.show();

        var conn = new Ext.data.Connection();
        var request = {
            url: RM.Common.AssembleURL({
                controller : 'UnitMedia',
                action: 'deletejson'
            }),
            params: {
                'filename': filename,
                'unit_id': RM.Pages.Units_Edit_UnitID
            },
            method: 'POST',
            success: function(responseObject) {
                RM.Pages.Functions.Units_Edit_Media_Show_Thumb_Hide();
                RM.Pages.Functions.Units_Edit_Media_Show_Info_Hide();
                myMask.hide();
                Ext.getCmp('rm_pages_units_edit_selected_images').setValue("");
                RM.Pages.Functions.Units_Edit_Images_JSON_Store_Reload();
                RM.Pages.Functions.Units_Edit_Media_Tree_Loader_Reload();                
            },
            failure: function() {
                myMask.hide();
                Ext.MessageBox.alert(RM.Translate.Common.DeleteFailure);
            }
        };
        conn.request(request);
    });
};

//RM.Pages.Functions.Units_Edit_Media_Show_Details
RM.Pages.Functions.Units_Edit_Media_Show_Thumb_Hide = function(){
    Ext.getCmp('rm_pages_units_edit_media_info').body.hide();
};
RM.Pages.Functions.Units_Edit_Media_Show_Thumb = function(data,dv,selNode){
    var detailEl = Ext.getCmp('rm_pages_units_edit_media_info').body;
    if(selNode && selNode.length > 0){
        selNode = selNode[0];
        data = data.store.data.items[dv].data;
        detailEl.hide();
        RM.Pages.Units_Edit_MediaManager_Template_Details.overwrite(detailEl, data);
        detailEl.slideIn('l', {stopFx:true,duration:0.2});
    }
};

RM.Pages.Functions.Units_Edit_Media_Show_Info_Hide = function(){
    Ext.getCmp('rm_pages_units_edit_media_additional_info_area').body.hide();
};

RM.Pages.Functions.Units_Edit_Media_Show_Info = function(data,dv,selNode){
    var detailEl = Ext.getCmp('rm_pages_units_edit_media_additional_info_area').body;
    if(selNode && selNode.length > 0){
        selNode = selNode[0];
        data = data.store.data.items[dv].data;
        detailEl.hide();
        RM.Pages.Units_Edit_MediaManager_Template_Image_InfoPanel.overwrite(detailEl, data);
        detailEl.slideIn('l', {stopFx:true,duration:0.2});
    }
};

RM.Pages.Units_Edit_Media_Info_Area = {
    bodyStyle : "padding:10px",
    region: 'north',
    autoScroll: true,
    height: 150,
    items: {
        xtype: 'panel',
        id : "rm_pages_units_edit_media_info",
        layout: 'form',
        frame: false,
        border: false,
        bodyStyle : "padding:10px"
    }
};

Ext.apply(Ext.form.VTypes, {    
    image: function(val, field) {
        return /.*\.(jpg|jpeg|png|gif)/i.test(val);
    },    
    imageText: RM.Translate.Admin.System.MediaManager.FileMustHaveExtensions + ' jpg, jpeg, png, gif'
});

RM.Pages.Units_Edit_Media_Upload_Area = {
    id : "rm_pages_units_edit_media_manager_upload",
    region:'north',
    bodyStyle : "padding:10px",
    layout:'fit',
    height: 100,
    title: RM.Translate.Admin.System.MediaManager.UploadTitle,
    collapsible: true,
    titleCollapse: true,
    items: [{
        xtype: 'panel',
        layout: 'column',
        height: 100,
        items: [{
              xtype: 'panel',
              layout: 'form',
              columnWidth: 0.70,
              frame: false,
              border: false,
              items: {
                    id : 'unit-img-upload-panel',
                    url : RM.Common.AssembleURL({
                        controller: 'UnitMedia',
                        action: 'uploadjson'
                    }),
                    fileUpload: true,
                    trackResetOnLoad: true,
                    xtype: 'form',
                    autoWidth: true,
                    frame: false,
                    border: false,
                    bodyStyle : "padding:10px",
                    items: {
                        xtype : 'fileuploadfield',
                        allowBlank : false,
                        id: 'unit-img-upload-panel-file',
                        emptyText: RM.Translate.Admin.System.MediaManager.ChooseImage,
                        fieldLabel: RM.Translate.Admin.System.MediaManager.SelectImage,
                        name: 'unit_media_upload',
                        vtype: 'image'
                    }
            }
        },{
            xtype: 'panel',
            layout: 'form',
            columnWidth: 0.20,
            frame: false,
            border: false,
            bodyStyle : "padding:10px",
            items: {
                xtype: 'button',
                frame: false,
                border: false,
                text: RM.Translate.Common.Upload,
                handler: function(){
                    if(Ext.getCmp('unit-img-upload-panel').getForm().isValid()){
                        Ext.getCmp('unit-img-upload-panel').getForm().submit({
                            url: RM.Common.AssembleURL({
                                controller: 'UnitMedia',
                                action: 'uploadjson',
                                parameters : [{
                                    name : 'unit_id',
                                    value : RM.Pages.Units_Edit_UnitID
                                }]
                            }),
                            success: function(){
                                Ext.getCmp('unit-img-upload-panel-file').reset();
                                RM.Pages.Functions.Units_Edit_Images_JSON_Store_Reload();                                
                            },
                            failure: function(form, action){
                                Ext.Msg.alert(RM.Translate.Admin.System.MediaManager.UploadFailure, action.result.error);
                            },
                            waitMsg: RM.Translate.Common.Uploading,
                            waitTitle: RM.Translate.Common.PleaseWait
                        });
                    }
                }
            }
        },{
            xtype: "hidden",
            name: 'rm_pages_units_edit_media_manager_selected_images',
            id: 'rm_pages_units_edit_media_manager_selected_images',
            hideLabel: true,
            readOnly: false
        },{
            xtype: "hidden",
            name: 'rm_pages_units_edit_selected_images',
            id: 'rm_pages_units_edit_selected_images',
            hideLabel: true,
            readOnly: false
        }]
    }]
};

RM.Pages.Units_Edit_MediaManager_Template = new Ext.XTemplate(
    '<tpl for=".">',
        '<div class="thumb-wrap" id="{name}">',
        '<div class="thumb"><img src="{url}" title="{name}"></div>',
        '</div>',
    '</tpl>'
);

RM.Pages.Units_Edit_MediaManager_Template_Image_InfoPanel = new Ext.XTemplate(
    '<div class="media-source">',
        '<tpl for=".">',
            '<b>Image Name: </b>',
            '<span>{name}</span>',
            '<br><b>Size: </b>',
            '<span>{sizeString}</span>',
            '<br><b>Last Modified: </b>',
            '<span>{dateString}</span></div>',
        '</tpl>',
    '</div>'
);

RM.Pages.Units_Edit_MediaManager_Template_Details = new Ext.XTemplate(
    '<div class="media-source">',
        '<tpl for=".">',
            '<a href="javascript:RM.Pages.Functions.Units_Edit_MediaManager_Preview(\'{largeimageurl}\')"><img src="{url}"><div class="details-info"></a>',
            '<span class="RM_Admin_Image_ClickPreviewText">'+RM.Translate.Admin.System.MediaManager.ClickToPreview+'</span>',
        '</tpl>',
    '</div>'
);



RM.Pages.Functions.Units_Edit_MediaManager_Preview = function(filename){

    var preview = new Ext.Window({
        renderTo: "content-panel",
        layout: 'fit',
        width: 500,
        height: 500,
        resizable: true,
        autoScroll: true,
        plain: true,
        title: filename,
        items: [{
                xtype:'box',
                anchor:'',
                isFormField:true,
                fieldLabel:'Image',
                autoEl:{
                    tag:'div', children:[{
                        tag:'img',
                        src: filename
                    },{
                        tag:'div',
                        style:'margin:0 0 4px 0',
                        html:'Image Caption'
                    }]
                }
        }]
    });

    preview.show();

};

RM.Pages.Units_Edit_MediaManager_JSON_Store = new Ext.data.JsonStore({
    //autoload: true,
    url: RM.Common.AssembleURL({
        controller : 'System',
        action: 'thumbsJson'
    }),
    root: 'images',
    fields: ['name', 'url', 'largeimageurl', {name:'size', type: 'float'}, {name:'lastmod', type:'date', dateFormat:'timestamp'}]
});

// media manager (bottom panel)
RM.Pages.Units_Edit_MediaManager_DataView = new Ext.DataView({
    id: "rm_pages_units_media_manager_dataview",
    store: RM.Pages.Units_Edit_MediaManager_JSON_Store,
    tpl: RM.Pages.Units_Edit_MediaManager_Template,
    multiSelect: true,
    overClass:'x-view-over',
    itemSelector: 'div.thumb-wrap',
    style:'overflow:auto',
    emptyText: 'No images to display',
    deferEmptyText: false,
    isFormField:true,
    prepareData: function(data){
        data.shortName = Ext.util.Format.ellipsis(data.name, 15);
        data.sizeString = Ext.util.Format.fileSize(data.size);
        data.dateString = data.lastmod.format("m/d/Y g:i a");
        return data;
    },

    listeners: {
        'selectionchange': {
            fn: function(dv, nodes){
                var l = nodes.length;
                var s = l !== 1 ? 's' : '';
                Ext.getCmp('rm_pages_units_edit_media_manager_selected_images').setValue(""); // clear the value
                if (nodes[0]!==undefined) {
                    var filename = nodes[0].textContent || nodes[0].innerText || '';
                    Ext.getCmp('rm_pages_units_edit_media_manager_selected_images').setValue(filename);
                    Ext.getCmp('rm_pages_units_edit_media_manager_content').setTitle(RM.Translate.Admin.Unit.Media.ImagesFromMediaManager + " (" + RM.Translate.Admin.Unit.Media.SelectedImage + ': '+ filename + ")");
                }
            }
        },
        'click': {
            fn: function(data,dv){
                var selNode = Ext.getCmp('rm_pages_units_media_manager_dataview').getSelectedNodes();
                if (data && dv && selNode){
                    RM.Pages.Functions.Units_Edit_Media_Show_Thumb(data,dv,selNode);
                    RM.Pages.Functions.Units_Edit_Media_Show_Info(data,dv,selNode);
                }
            }
        },
        'afterrender': {
            fn: function(){
                new Ext.rm.DragZone(RM.Pages.Units_Edit_MediaManager_DataView, {
                    removeNodeFromSource: false
                });
            }
        }
    }
});

RM.Pages.Units_Edit_MediaManager_Content_Area = {
    id: 'rm_pages_units_edit_media_manager_content',
    region:'south',
    height: 200,
    layout:'fit',
    title: RM.Translate.Admin.Unit.Media.ImagesFromMediaManager,
    collapsible: true,
    collapsed: true,
    items : [
        RM.Pages.Units_Edit_MediaManager_DataView
    ],
    plugins: {
        init: function(p) {
            if (p.collapsible) {
                var r = p.region;
                if ((r === 'north') || (r === 'south')) {
                    p.on('render', function() {
                            var ct = p.ownerCt;
                        ct.on('afterlayout', function() {
                            p.collapsedTitleEl = ct.layout[r].collapsedEl.createChild({
                                    tag: 'span',
                                    cls: 'x-panel-header-text',
                                    html: "&nbsp;"+RM.Translate.Admin.Unit.Media.ImagesFromMediaManagerCollapsed
                            });
                            p.setTitle = Ext.Panel.prototype.setTitle.createSequence(function(t) {
                                    p.collapsedTitleEl.dom.innerHTML = t;
                            });
                        }, false, {single:true});
                    });
                }
            }
        }
    }
};

RM.Pages.Units_Edit_Media_Additional_Info_Area = {
    id: 'rm_pages_units_edit_media_additional_info_area',
    region:'center',
    Height: 200,
    layout:'fit',
    title: RM.Translate.Admin.System.MediaManager.Info,
    items : []
};

RM.Pages.Units_Edit_Media_Tree_Loader = new Ext.tree.TreeLoader({
    requestMethod : 'POST',
    dataUrl : RM.Common.AssembleURL({
        controller : 'UnitMedia',
        action: 'unittypetreeJson'  
    }),
    listeners: {
        'beforeload' : function(treeLoader, node){
            this.baseParams.unit_id = RM.Pages.Units_Edit_UnitID;
        }
    }
});

// set up the Album tree
RM.Pages.Units_Edit_Media_Tree = new Ext.tree.TreePanel({
    // tree
    id: 'rm_pages_units_edit_media_tree',
    animate: true,
    enableDD: true,
    containerScroll: false,
    ddGroup: 'DataViewDD',
    rootVisible: false,
    region: 'south',
    height: 300,
    split: true,
    title: RM.Translate.Admin.Unit.Media.SelectionOrdering,
    autoScroll: true,
    loader :  RM.Pages.Units_Edit_Media_Tree_Loader ,
    listeners: {
        'nodedrop': function(){
            RM.Pages.Functions.Units_Edit_Media_Classification();
            return true;
        },
        'click': function(node, event){
            //TODO: we need to show an image information just like for dataview when we click on an image in image tree            
//            var selNode = Ext.getCmp('rm_units_media_drop_dataview').getSelectedNodes();
//            if (data && dv && selNode){
//                RM.Pages.Functions.Units_Edit_Media_Show_Thumb(data,dv,selNode);
//                RM.Pages.Functions.Units_Edit_Media_Show_Info(data,dv,selNode);
//            }
        }
    }
});

// create the node object
RM.Pages.Units_Edit_Media_Tree_Root = new Ext.tree.TreeNode({
    text: 'Albums',
    allowDrag:false,
    allowDrop:false
});

// set the root node
RM.Pages.Units_Edit_Media_Tree.setRootNode(RM.Pages.Units_Edit_Media_Tree_Root);

// add an inline editor for the nodes
//ge = new Ext.tree.TreeEditor(RM.Pages.Units_Edit_Media_Tree, {/* fieldconfig here */ }, {
//    allowBlank:false,
//    blankText:'A name is required',
//    selectOnFocus:true
//});

// Set up images view
RM.Pages.Units_Edit_Images_JSON_Store = new Ext.data.JsonStore({
    url: RM.Common.AssembleURL({
        controller : 'UnitMedia',
        action: 'unitlistjson'
    }),
    root: 'images',
    id: 'name',
    fields: ['name', 'url', 'largeimageurl', 'id', {name:'size', type: 'float'}, {name:'lastmod', type:'date', dateFormat:'timestamp'}]
});

// unit media (top panel)
RM.Pages.Units_Edit_Images_DataView = new Ext.DataView({
    id: "rm_units_media_drop_dataview",
    itemSelector: 'div.thumb-wrap',
    style:'overflow:auto',
    multiSelect: true,
    store: RM.Pages.Units_Edit_Images_JSON_Store,
    plugins: new Ext.DataView.DragSelector({dragSafe:true}),
    overClass: 'x-view-over',
    emptyText: 'No images to display',
    deferEmptyText: false,
    isFormField:true,
    prepareData: function(data){
        data.shortName = Ext.util.Format.ellipsis(data.name, 15);
        data.sizeString = Ext.util.Format.fileSize(data.size);
        //data.dateString = data.lastmod.format("m/d/Y g:i a");
        return data;
    },

    tpl: new Ext.XTemplate(
        '<tpl for=".">',
        '<div class="media-target">',
        '<div class="thumb-wrap" id="{id}">',
        '<div class="thumb"><img src="{url}" class="thumb-img"></div>',
        '<span>{name}</span></div>',
        '</div>',
        '</tpl>'
    ),
    listeners: {
        'selectionchange': {
            fn: function(dv, nodes){
                var l = nodes.length;
                var s = l !== 1 ? 's' : '';
                Ext.getCmp('rm_pages_units_edit_selected_images').setValue(""); // clear the value
                if (nodes[0]!==undefined) {
                    var filename = nodes[0].textContent || nodes[0].innerText || '';
                    filename = filename.replace(/(^\s+)|(\s+$)/g, "");
                    Ext.getCmp('rm_pages_units_edit_selected_images').setValue(filename);
                    Ext.getCmp('rm_pages_units_edit_content').setTitle(RM.Translate.Admin.Unit.Media.UnitMedia+" ("+RM.Translate.Admin.Unit.Media.SelectedImage + ': '+ filename+")");
                }
            }
        },
        'click': {
            fn: function(data, dv){
                var selNode = Ext.getCmp('rm_units_media_drop_dataview').getSelectedNodes();
                if (data && (dv !== null) && selNode){
                    RM.Pages.Functions.Units_Edit_Media_Show_Thumb(data,dv,selNode);
                    RM.Pages.Functions.Units_Edit_Media_Show_Info(data,dv,selNode);
                }
            }
        },
        'afterrender': {
            fn: function(){
                new Ext.rm.ImageDragZone(RM.Pages.Units_Edit_Images_DataView, {
                    containerScroll:true,
                    ddGroup: 'DataViewDD',
                    onNodeDropEvent: function(n, dd, e, data){
                        //RM.Pages.Functions.Units_Edit_Media_Classification();
                    }
                });

                new Ext.rm.DropZone(RM.Pages.Units_Edit_Images_DataView, {
                    onNodeDropEvent: function(n, dd, e, data){
                        //TODO: add option to be able to drag&drop multiple media images.
                        var filename = data.records[0].data.name;
                        RM.Pages.Functions.Units_Edit_Media_Add(filename);
                    }
                });

            }
        }
    }
});


RM.Pages.Units_Edit_Media_Images = new Ext.Panel({
    id: 'rm_pages_units_edit_content',
    title: RM.Translate.Admin.Unit.Media.UnitMedia,
    region:'center',
    items: RM.Pages.Units_Edit_Images_DataView,
    autoScroll: true,
    tbar: [{
        text: RM.Translate.Admin.Unit.Media.DeleteSelected,
        handler: function(){
            RM.Pages.Functions.Units_Edit_Media_Delete();
        }
    }]
});

RM.Pages.Units_Edit_Media_Center = new Ext.Panel({
    region: 'center',
    layout: 'border',
    split: true,
    width: 0.8,    
    defaults: {
        split: true,
        useShim : true
    },
    items: [
       RM.Pages.Units_Edit_Media_Upload_Area,
       RM.Pages.Units_Edit_Media_Images,
       RM.Pages.Units_Edit_MediaManager_Content_Area
    ]
});

RM.Pages.Unit_Edit_Media_Toolbar = {
    xtype : "panel",
    id : "rm_pages_units_edit_media_toolbar",
    bodyBorder : false,
    html : ""
};
RM.Toolbars.push(RM.Pages.Unit_Edit_Media_Toolbar);

RM.Pages.Units_Edit_Media_East = {
    xtype: 'panel',
    region: 'east',
    layout: 'border',
    split: true,
    width: 200,
    defaults: {
        split: true,
        useShim : true
    },
    items: [
       RM.Pages.Units_Edit_Media_Info_Area,
       RM.Pages.Units_Edit_Media_Additional_Info_Area,
       RM.Pages.Units_Edit_Media_Tree
    ]
};