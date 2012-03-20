/*
 * Edit Locations Module JS
 * This creates the admin GUI Edit Page for the Unit Locations Module
 *
 * JSLint.com Check: 18/03/2011
 */

RM.Pages.Functions.Locations_Unit_Save = function(){

    // get the google maps lng/lat settings if they are present and update the address form with this info
    var mif = Ext.getCmp("rm_pages_locations_gmaps_iframe").getFrameDocument();
    var coordinates = mif.forms[0];
    var i = 0;for (i; i < coordinates.length; i++ ){
       if (mif.forms[0][i].value!==""){
           switch (mif.forms[0][i].id){
               case("new_latitude"):
                   Ext.getCmp("rm_locations_edit[latitude]").setValue(mif.forms[0][i].value);
                   break;
               case("new_longitude"):
                   Ext.getCmp("rm_locations_edit[longitude]").setValue(mif.forms[0][i].value);
                   break;
           }
       }
    }

    RM.Pages.Locations_Unit_Edit_Left_Column.getForm().submit({
        success: function(form, action) {
            // update the status bar...
            RM.Pages.Units_StatusBar = Ext.getCmp('rm_pages_unit_edit_statusbar');
            RM.Pages.Units_StatusBar.setStatus({
                text: RM.Translate.Admin.Locations.LocationsSaved,
                iconCls: 'ok-icon'
            });
            RM.Pages.Functions.Locations_Unit_EditJson_Request();
            RM.Pages.Locations_Refresh_Gmap();
        },
        waitMsg: RM.Translate.Common.Saving,
        waitTitle: RM.Translate.Common.PleaseWait
    });
};

RM.Pages.Locations_Unit_Toolbar = {
    xtype : "panel",
    id : "rm_pages_locations_unit_toolbar",
    bodyBorder : false,
    html : RM.Common.getToolbar([{image: RM.BaseLargeImageURL+"save.gif", label: RM.Translate.Common.Save, link: "RM.Pages.Functions.Locations_Unit_Save()"}])
};
RM.Toolbars.push(RM.Pages.Locations_Unit_Toolbar);

RM.Pages.Locations_Address_Fieldset = new Ext.form.FieldSet({
    id: "rm_pages_locations_address_fieldset",
    title: RM.Translate.Admin.Locations.Address,
    autoHeight: true,
    layout: 'form',
    bodyBorder : false,
    defaults: {width: 300},
    defaultType: 'textfield',
    labelWidth: 190,
    items: [
            {
                fieldLabel: RM.Translate.Admin.Locations.Address,
                id: 'rm_locations_edit[address1]',
                allowBlank:true
            },{
                fieldLabel: RM.Translate.Admin.Locations.AddressCont,
                id: 'rm_locations_edit[address2]',
                allowBlank:true
            },{
                fieldLabel: RM.Translate.Admin.Locations.City,
                id: 'rm_locations_edit[city]',
                allowBlank:true
            },{
                fieldLabel: RM.Translate.Admin.Locations.State,
                id: 'rm_locations_edit[state]',
                allowBlank:true
            },{
                fieldLabel: RM.Translate.Admin.Locations.Postcode,
                id: 'rm_locations_edit[postcode]',
                allowBlank:true
            },{
                fieldLabel: RM.Translate.Admin.Locations.Country,
                id: 'rm_locations_edit[country]',
                allowBlank:true
            }
    ]
});

RM.Pages.Locations_Coordinates_Fieldset = new Ext.form.FieldSet({
    id: "rm_pages_locations_coordinates_fieldset",
    title: RM.Translate.Admin.Locations.Coordinates,
    autoHeight: true,
    layout: 'form',
    bodyBorder : false,
    defaults: {width: 300},
    defaultType: 'textfield',
    labelWidth: 190,
    items: [
            {
                fieldLabel: RM.Translate.Admin.Locations.Latitude,
                id: 'rm_locations_edit[latitude]',
                allowBlank:true
            },{
                fieldLabel: RM.Translate.Admin.Locations.Longitude,
                id: 'rm_locations_edit[longitude]',
                allowBlank:true
            }
    ]
});

RM.Pages.Locations_OtherInfo_Fieldset = new Ext.form.FieldSet({
    id: "rm_pages_locations_otherinfo_fieldset",
    title: RM.Translate.Admin.Locations.OtherInfo,
    autoHeight: true,
    layout: 'form',
    bodyBorder : false,
    defaults: {width: 300},
    labelWidth: 190,
    items: [
        {
            xtype: "textarea",
            fieldLabel: RM.Translate.Admin.Locations.Directions,
            id: 'rm_locations_edit[directions]'
        }
    ]
});

// load the values
RM.Pages.Functions.Locations_Unit_EditJson_Request = function(){
    var unit_id = RM.Pages.Units_Edit_UnitID;

    var myMask = new Ext.LoadMask('content-panel', {msg:RM.Translate.Common.PleaseWait});
    myMask.show();

    // add a listener to the edit_unit[id] feild to detect change and refresh the address form
//    Ext.getCmp('edit_unit[id]').on('change',function(){
//        RM.Pages.Functions.Locations_Unit_EditJson_Request(unit_id);
//    });

    var conn = new Ext.data.Connection();
    var request = {
        url: RM.Common.AssembleURL({
            controller : 'Locations',
            action: 'loadjson',
            parameters : [{
                name : 'id',
                value : unit_id
            }]
        }),
        method: 'POST',
        success: function(responseObject) {
            eval('RM.Pages.Functions.Locations_Unit_ConfigJson('+responseObject.responseText+');');
            myMask.hide();
        },
        failure: function() {
            Ext.Msg.alert(RM.Translate.Common.Status, RM.Translate.Common.UnableToShow);
            myMask.hide();
        }
    };
    conn.request(request);
};

// clear the old form values
RM.Pages.Functions.Locations_Unit_ClearForm = function (){
    Ext.getCmp('rm_locations_edit[id]').setValue("");
    Ext.getCmp('rm_locations_edit[address1]').setValue("");
    Ext.getCmp('rm_locations_edit[address2]').setValue("");
    Ext.getCmp('rm_locations_edit[state]').setValue("");
    Ext.getCmp('rm_locations_edit[city]').setValue("");
    Ext.getCmp('rm_locations_edit[postcode]').setValue("");
    Ext.getCmp('rm_locations_edit[country]').setValue("");
    Ext.getCmp('rm_locations_edit[latitude]').setValue("");
    Ext.getCmp('rm_locations_edit[longitude]').setValue("");
    Ext.getCmp('rm_locations_edit[directions]').setValue("");
    Ext.getCmp('rm_locations_edit[unit_id]').setValue("");
};

RM.Pages.Functions.Locations_Unit_ConfigJson = function (responseObject) {

    RM.Pages.Functions.Locations_Unit_ClearForm();

    // load the new values
    if (responseObject[0] !== undefined) {
        var fieldName;
        for (fieldName in responseObject[0]) {
            var inputName = 'rm_locations_edit[' + fieldName + ']';
            if (Ext.getCmp(inputName)) {
                Ext.getCmp(inputName).setValue(responseObject[0][fieldName]);
            }
        }        
    }

    Ext.getCmp('rm_locations_edit[unit_id]').setValue(RM.Pages.Units_Edit_UnitID);
    
    Ext.getCmp("rm_pages_locations_unit_edit_form").doLayout(); // force the layout
};

/**
 * This is a MIF (Managed iFrame) used to render Gmaps
 */
RM.Pages.Locations_Unit_Edit_GMaps_iFrame = ({
    xtype: 'iframepanel',
    id: 'rm_pages_locations_gmaps_iframe',
    title: RM.Translate.Admin.Locations.Map,
    closable: false,
    loadMask: true,
    frame: true,
    layout: 'fit',
    height: 405,
    width: 380
});


/**
 * This is the main form on this page and where the information is held
 */
RM.Pages.Locations_Unit_Edit_Left_Column = new Ext.FormPanel({
    // this is the left column (address, coordinates, other info),
    id: "rm_pages_locations_unit_edit_form_left_column",
    autoScroll: true,
    region : "center",
    url : RM.Common.AssembleURL({
        controller: 'Locations',
        action: 'updatejson'
    }),
    items: [
        {
            xtype: "hidden",
            id: 'rm_locations_edit[unit_id]'
        },{
            xtype: "hidden",
            id: 'rm_locations_edit[id]'
        },
        RM.Pages.Locations_Address_Fieldset,
        RM.Pages.Locations_Coordinates_Fieldset,
        RM.Pages.Locations_OtherInfo_Fieldset
    ]
});

RM.Pages.Locations_Unit_Edit_Right_Column = new Ext.FormPanel({
    // this is the right column (google map)
    autoScroll: false,
    region : "east",
    width: 410,
    items: [
        RM.Pages.Locations_Unit_Edit_GMaps_iFrame
    ],
    listeners: {
        // this causes the map to reload when the tab is change or another user is loaded
        'afterlayout': function(){
            RM.Pages.Locations_Refresh_Gmap();
        }
    }
});

RM.Pages.Locations_Refresh_Gmap = function(){
    var URL = RM.Common.AssembleURL({
        controller: 'GMaps',
        action: 'getadminmap',
        parameters : [{
            name : 'unit_id',
            value : RM.Pages.Units_Edit_UnitID
        },{
            name : 'time', //Prevent IE of using it's cache
            value : new Date().getTime()
        }]
    });
    // set the new source url for the iframe containing the gmap
    Ext.getCmp("rm_pages_locations_gmaps_iframe").setSrc(URL);
};

/**
 * This is the main page. This renders the panels and adds the listeners to load/refresh the toolbar
 *
 * Note: the main form on this page is actually the left column (RM.Pages.Locations_Unit_Edit_Left_Column)
 */
RM.Pages.Locations_Unit_Edit_Form = new Ext.Panel({
    layout:'border',
    defaults: {
        split: true
    },
    containerScroll : true,
    bodyBorder : false,
    bodyStyle : "padding:10px",
    id : "rm_pages_locations_unit_edit_form",
    title: RM.Translate.Admin.Locations.Title,
    frame : true,
    items : [
        RM.Pages.Locations_Unit_Edit_Left_Column,
        RM.Pages.Locations_Unit_Edit_Right_Column
    ],
    listeners: {
        'beforehide' : function(){
            Ext.getCmp('toolbar_panel').layout.setActiveItem('rm_pages_units_edit_toolbar');
            return true;
        },
        'beforeshow' : function(){
            Ext.getCmp('toolbar_panel').layout.setActiveItem('rm_pages_locations_unit_toolbar');            
            RM.Pages.Functions.Locations_Unit_EditJson_Request();
            return true;
        },
        'show' : function(){
            RM.Pages.Locations_Unit_Edit_Right_Column.setWidth(410);
            this.doLayout()
        }
    }
});

RM.Pages.Units_Edit_TabPanel.add(RM.Pages.Locations_Unit_Edit_Form);
