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
RM.Pages.System_MediaManager_Toolbar = {
    xtype : "panel",
    id : "rm_pages_system_media_manager_toolbar",
    bodyBorder : false,
    html : RM.Common.getToolbar(
    [{image: RM.BaseLargeImageURL+"delete.gif", label: RM.Translate.Common.Delete, link: "RM.Pages.Functions.Media_Manager_Delete_Images()"}])
};
RM.Toolbars.push(RM.Pages.System_MediaManager_Toolbar);

RM.Pages.System_MediaManager_Selected_Images = [];
RM.Pages.Functions.Media_Manager_Delete_Images = function (){
    if (RM.Pages.System_MediaManager_Selected_Images.length === 0) {return;}

    Ext.MessageBox.confirm(
        RM.Translate.Common.Delete,
        RM.Translate.Admin.System.MediaManager.DeleteImage+' ('+RM.Pages.System_MediaManager_Selected_Images.length+')',
        function(buttonID){
            if (buttonID !== 'yes') {
                return;
            }

            selectedImages = [];
            var i = 0;for(i; i < RM.Pages.System_MediaManager_Selected_Images.length; i++){
                selectedImages.push(RM.Pages.System_MediaManager_Selected_Images[i].id);
            }

            var myMask = new Ext.LoadMask('rm_pages_system_media_manager', {msg: RM.Translate.Common.PleaseWait});
            myMask.show();            
            Ext.Ajax.request({
                url: RM.Common.AssembleURL({
                    controller : 'System',
                    action: 'deleteselectedimagejson'
                }),
                params: {
                    'images[]': selectedImages
                },
                method: 'POST',
                success: function(responseObject) {
                    myMask.hide();
                    RM.Pages.System_MediaManager_Selected_Images = [];
                    Ext.getCmp('rm_pages_system_media_manager_info').body.slideOut('r', {stopFx:true,duration:0.2});
                    RM.Pages.System_MediaManager_JSON_Store.load();
                    RM.Pages.System_MediaManager_DataView.refresh();
                },
                failure: function() {
                    myMask.hide();
                    Ext.MessageBox.alert(RM.Translate.Common.DeleteFailure);
                }
            });
        }
    );
};

RM.Pages.System_MediaManager_JSON_Store = new Ext.data.JsonStore({
    autoLoad: false,
    url: RM.Common.AssembleURL({
        controller : 'System',
        action: 'thumbsJson'
    }),
    root: 'images',
    fields: ['name', 'url', 'largeimageurl', {name:'size', type: 'float'}, {name:'lastmod', type:'date', dateFormat:'timestamp'}]
});

RM.Pages.System_MediaManager_Template = new Ext.XTemplate(
    '<tpl for=".">',
        '<div class="thumb-wrap" id="{name}">',
        '<div class="thumb"><img src="{url}" title="{name}"></div>',
        '</div>',
    '</tpl>'
);

RM.Pages.System_MediaManager_Template_details = new Ext.XTemplate(
    '<div class="details">',
        '<tpl for=".">',
            '<a href="javascript:RM.Pages.System_MediaManager_Preview(\'{largeimageurl}\')"><img src="{url}"><div class="details-info"></a>',
            '<span class="RM_Admin_Image_ClickPreviewText">'+RM.Translate.Admin.System.MediaManager.ClickToPreview+'</span>',
            '<br><b>Image Name: </b>',
            '<span>{name}</span>',
            '<br><b>Size: </b>',
            '<span>{sizeString}</span>',
            '<br><b>Last Modified: </b>',
            '<span>{dateString}</span></div>',
        '</tpl>',
    '</div>'
);

RM.Pages.System_MediaManager_Preview = function(filename){    
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

RM.Pages.System_MediaManager_DataView = new Ext.DataView({
    id: "rm_pages_system_media_manager_dataview",
    store: RM.Pages.System_MediaManager_JSON_Store,
    tpl: RM.Pages.System_MediaManager_Template,
    autoHeight:true,
    multiSelect: true,
    //overClass:'x-view-over',
    itemSelector: 'div.thumb-wrap',

    emptyText: 'No images to display',

    plugins: [
        new Ext.DataView.DragSelector()
    ],

    prepareData: function(data){
        data.shortName = Ext.util.Format.ellipsis(data.name, 15);
        data.sizeString = Ext.util.Format.fileSize(data.size);
        data.dateString = data.lastmod.format("m/d/Y g:i a");
        return data;
    },

    listeners: {
        'selectionchange': {
            fn: function(dv,nodes){
                var l = nodes.length;
                var s = l !== 1 ? 's' : '';
                Ext.getCmp('rm_pages_system_media_manager_content').setTitle(RM.Translate.Admin.System.MediaManager.Images + ' ('+l+' item'+s+' selected)');
                RM.Pages.System_MediaManager_Selected_Images = nodes;                
            }
        },
        'click': {
            fn: function(data,dv){
                RM.Pages.System_MediaManager_Show_Details(data,dv);
            }
        }
    }
});
RM.Pages.System_MediaManager_Show_Details = function(data,dv){

    var selNode = Ext.getCmp('rm_pages_system_media_manager_dataview').getSelectedNodes();
    var detailEl = Ext.getCmp('rm_pages_system_media_manager_info').body;
    if(selNode && selNode.length > 0){
        selNode = selNode[0];
        //Ext.getCmp('ok-btn').enable();
        data = data.store.data.items[dv].data;
        detailEl.hide();        
        RM.Pages.System_MediaManager_Template_details.overwrite(detailEl, data);
        detailEl.slideIn('l', {stopFx:true,duration:0.2});
    }
};

RM.Pages.Functions.System_MediaManagerJson = function (responseObject) {    
    RM.Pages.Functions.System_MediaManagerJson_Main();

    if (responseObject.newImages === 0) {
        RM.Pages.System_MediaManager_JSON_Store.load();
        return;
    }

    var newImageMessage = responseObject.newImages+RM.Translate.Admin.System.MediaManager.NewImages;
    if (responseObject.newImages === 1){
        newImageMessage = RM.Translate.Admin.System.MediaManager.NewImage;
    }
    var myMask = new Ext.LoadMask(
        'rm_pages_system_media_manager',
        {msg: newImageMessage+' '+RM.Translate.Common.PleaseWait}
    );
    myMask.show();
    Ext.Ajax.request({
        url: RM.Common.AssembleURL({
            controller: 'System',
            action: 'mediamanagerreccanJson'
        }),
        success: function(responseObject) {
            myMask.hide();
            RM.Pages.System_MediaManager_JSON_Store.load();
        }
    });
};

RM.Pages.Functions.System_MediaManagerJson_Main = function(){
    RM.Pages.System_MediaManager_Selected_Images = [];
    Ext.getCmp('toolbar_panel').layout.setActiveItem('rm_pages_system_media_manager_toolbar');
    Ext.getCmp('content-panel').layout.setActiveItem('rm_pages_system_media_manager');
    RM.Help.Load('Admin.System.MediaManager.Main');
};

RM.Pages.System_MediaManager_Content_Area = {
    id:'rm_pages_system_media_manager_content',
    region:'center',
    height: RM.Common.GetPanelHeight(174),
    layout:'fit',
    title: RM.Translate.Admin.System.MediaManager.MediaManager,
    autoScroll: true,
    items : [
        RM.Pages.System_MediaManager_DataView
    ]
};

RM.Pages.System_MediaManager_Upload_Area = {
    id : "rm_pages_system_media_manager_upload",
    region:'north',
    bodyStyle : "padding:10px",
    layout:'fit',
    collapsible: true,
    title: RM.Translate.Admin.System.MediaManager.UploadTitle,
    items: [{
        xtype: 'panel',
        layout: 'column',
        height: 50,
        items: [{
              xtype: 'panel',
              layout: 'form',
              columnWidth: 0.70,
              frame: false,
              border: false,
              items: {
                    id : 'img-upload-panel',
                    url : RM.Common.AssembleURL({
                        controller: 'System',
                        action: 'mediamanageruploadjson'
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
                        id: 'img-upload-panel-file',
                        emptyText: RM.Translate.Admin.System.MediaManager.ChooseImage,
                        fieldLabel: RM.Translate.Admin.System.MediaManager.SelectImage,
                        name: 'media_manager_upload'
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
                        if(Ext.getCmp('img-upload-panel').getForm().isValid()){
                            Ext.getCmp('img-upload-panel').getForm().submit({
                                success: function(form, action) {
                                    Ext.getCmp('img-upload-panel-file').setValue(RM.Translate.Admin.System.MediaManager.ChooseImage);
                                    RM.Pages.System_MediaManager_JSON_Store.load();
                                },
                                failure: function(form, action){
                                    Ext.Msg.alert(RM.Translate.Common.Success, RM.Translate.Admin.System.MediaManager.UploadFailure);
                                },
                                waitMsg: RM.Translate.Common.Uploading,
                                waitTitle: RM.Translate.Common.PleaseWait
                            });
                        }
                    }
                }
            }]
        }]
};

RM.Pages.System_MediaManager_Info_Area = {
    region:'east',
    bodyStyle : "padding:10px",
    autoScroll: true,
    minWidth: 200,
    minSize: 200,
    items: {
        xtype: 'panel',
        id : "rm_pages_system_media_manager_info",
        layout: 'form',
        frame: false,
        border: false,
        bodyStyle : "padding:10px",
        width: "200"
    }
};

RM.Pages.System_MediaManager = new Ext.Panel({
    id:'rm_pages_system_media_manager',
    layout:'border',
    defaults: {
        split: true,
        useShim : true
    },
    autoScroll : true,
    containerScroll : true,
    title: RM.Translate.Admin.System.MediaManager.MediaManager,
    items : [
        RM.Pages.System_MediaManager_Content_Area,
        RM.Pages.System_MediaManager_Upload_Area,
        RM.Pages.System_MediaManager_Info_Area
    ]
});

RM.Main.Pages.push(RM.Pages.System_MediaManager);