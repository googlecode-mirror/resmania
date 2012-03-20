/**
* GMaps Edit settings Page
*
* This file renders the page to add the Google maps configuration information
*
* JSLint Validated: 18/03/2011
*
* @access   public
* @author   Rob
* @copyright    ResMania 2009 all rights reserved.
* @version  1.0
* @link http://developer.resmania.com
* @since    05-2009
*/

RM.Pages.Plugin_GMaps_Config_Toolbar = {
    xtype : "panel",
    id : "rm_pages_plugins_gmaps_toolbar",
    bodyBorder : false,
    html : RM.Common.getToolbar([{image: RM.BaseLargeImageURL+"save.gif", label: RM.Translate.Common.Save, link: "RM.Pages.Functions.plugins_gmaps_save()"}])
};
RM.Toolbars.push(RM.Pages.Plugin_GMaps_Config_Toolbar);

RM.Pages.Plugin_GMaps_Config_ZoomLevel_Selection = new Ext.data.SimpleStore({
    fields:[
    {
        name:'value',
        type:'string'
    },

    {
        name:'text',
        type:'string'
    }
    ],
    data: RM.Common.Combo_Number_Data(0,21,1)
});

RM.Pages.Plugin_GMaps_Config_MapType_Selection = new Ext.data.JsonStore({
    fields:[
    {
        name:'value',
        type:'string'
    },

    {
        name:'text',
        type:'string'
    }
    ],
    data: [
        {'value':'ROADMAP','text':'Road'},
        {'value':'SATELLITE','text':'Satellite'},
        {'value':'HYBRID','text':'Hybrid'},
        {'value':'TERRAIN','text':'Terrain'}
    ]

});

RM.Pages.Functions.plugins_gmaps_save = function(){
    RM.Pages.Plugin_GMaps_Panel.getForm().submit({
        params: {
            'id' : Ext.getCmp('rm_pages_plugins_gmaps_id').value,
            'zoomlevel' : Ext.getCmp('rm_pages_plugins_gmaps_zoomlevel').getValue(),
            'maptype' : Ext.getCmp('rm_pages_plugins_gmaps_maptype').getValue()
        },
        success: function(form, action) {
            RM.Pages.Units_StatusBar = Ext.getCmp('rm_pages_plugins_gmaps_statusbar');
            RM.Pages.Units_StatusBar.setStatus({
                text: RM.Translate.Admin.GMaps.Main.SaveOK,
                iconCls: 'ok-icon'
            });
        },
        failure: function() {
            RM.Pages.Units_StatusBar = Ext.getCmp('rm_pages_plugins_gmaps_statusbar');
            RM.Pages.Units_StatusBar.setStatus({
                text: RM.Translate.Common.Failed,
                iconCls: 'failed-icon'
            });
        },
        waitMsg: RM.Translate.Common.Saving,
        waitTitle: RM.Translate.Common.PleaseWait
    });
};

RM.Pages.Plugin_GMaps_ID_Field = new Ext.form.Hidden({
    id : "rm_pages_plugins_gmaps_id"
});

RM.Pages.Plugin_GMaps_ZoomLevel = {
    id : "rm_pages_plugins_gmaps_zoomlevel",
    name : "rm_pages_plugins_gmaps_zoomlevel_name",
    hiddenName : "rm_pages_plugins_gmaps_zoomlevel_hidden",
    xtype : "combo",
    fieldLabel : RM.Translate.Admin.GMaps.Main.ZoomLevel,
    store : RM.Pages.Plugin_GMaps_Config_ZoomLevel_Selection,
    mode: "local",
    triggerAction: 'all',
    selectOnFocus: true,
    valueField: 'value',
    displayField: 'text',
    allowBlank: false,
    width: 50
};

RM.Pages.Plugin_GMaps_MapType = {
    id : "rm_pages_plugins_gmaps_maptype",
    name : "rm_pages_plugins_gmaps_maptypel_name",
    hiddenName : "rm_pages_plugins_gmaps_maptype_hidden",
    xtype : "combo",
    fieldLabel : RM.Translate.Admin.GMaps.Main.MapType,
    store : RM.Pages.Plugin_GMaps_Config_MapType_Selection,
    mode: "local",
    triggerAction: 'all',
    selectOnFocus: true,
    valueField: 'value',
    displayField: 'text',
    allowBlank: false,
    width: 100
};

RM.Pages.Plugin_GMaps_Fieldset = new Ext.form.FieldSet({
    id: "rm_pages_plugins_gmaps_fieldset",
    title: RM.Translate.Admin.GMaps.Main.Settings,
    autoHeight: true,
    layout: 'form',
    bodyBorder : false,
    items: [
        RM.Pages.Plugin_GMaps_ZoomLevel,
        RM.Pages.Plugin_GMaps_MapType
    ]
});

RM.Pages.Plugin_GMaps_Panel = new Ext.FormPanel({
    id : 'rm_pages_plugins_gmaps_main_form',
    title: RM.Translate.Admin.GMaps.Main.Title,
    bodyStyle : "padding:10px;",
    items : [
        RM.Pages.Plugin_GMaps_Fieldset
    ],
    url : RM.Common.AssembleURL({
        controller: 'GMaps',
        action: 'updateJson'
    }),
    bbar: new Ext.ux.StatusBar({
        id: 'rm_pages_plugins_gmaps_statusbar',
        items: []
    })
});

RM.Pages.Functions.GMaps_ConfigJson = function(responseObject) {

    // create a mask assigned to rm_pages_plugins_gmaps_main_form
    var myMask = new Ext.LoadMask('rm_pages_plugins_gmaps_main_form', {
        msg: RM.Translate.Common.PleaseWait,
        msgCls: "RM_Loading_Mask_Msg_Hidden" // hide the msg
    });
    myMask.show();

    var settings = responseObject.settings[0];
    Ext.getCmp("rm_pages_plugins_gmaps_id").setValue(settings.id);
    Ext.getCmp("rm_pages_plugins_gmaps_zoomlevel").setValue(settings.zoomlevel);
    Ext.getCmp("rm_pages_plugins_gmaps_maptype").setValue(settings.maptype);

    Ext.getCmp('rm_pages_plugins_gmaps_main_form').render();
    Ext.getCmp('rm_pages_plugins_gmaps_main_form').doLayout();

    Ext.getCmp('toolbar_panel').layout.setActiveItem('rm_pages_plugins_gmaps_toolbar');
    Ext.getCmp('content-panel').layout.setActiveItem('rm_pages_plugins_gmaps_main_form');

    myMask.hide();

    RM.Help.Load('Admin.GMaps.Edit.Main');
};

RM.Main.Pages.push(RM.Pages.Plugin_GMaps_Panel);