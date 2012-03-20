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
 * Configuration Page JS
 * This creates the admin GUI Configuration Page
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

RM.Pages.Config_PageOffset = 78; // this is defined here as it is used allot in this page
RM.Pages.Config_PageTabOffset = 134;

RM.Pages.Functions.config_edit_itemselector_getValue = function(name){
    var value_array = [];
    var i = 0;for (i; i < Ext.getCmp(name).store.data.items.length; i++){
        value_array.push(Ext.getCmp(name).store.data.items[i].data.elementid);
    }
    return value_array.join(',');
};

RM.Pages.Functions.SubmitConfig = function(imageResize, reload){
    RM.Pages.Config_Edit_Form.getForm().submit({
        params: {
            image_resize: imageResize
        },        
        success: function(form, action) {
            if (reload) {
                new Ext.LoadMask('root_gui_container', {msg:RM.Translate.Common.PleaseWait}).show();
                window.location.reload();
                return true;
            }
            RM.Pages.Functions.MainTreeMenuHandler('Config', 'EditJson', null, null);
            RM.Pages.Config_Edit_StatusBar.setStatus({
                text: RM.Translate.Admin.Config.Edit.Saved,
                iconCls: 'tick-icon'
            });
        },
        waitMsg: RM.Translate.Common.Saving,
        waitTitle: RM.Translate.Common.PleaseWait
    });
};

RM.Pages.Functions.config_edit_save = function(){

    // if images setting have changed prompt for resize
    var input_names = [
        "rm_config_image_settings_aspect",
        "rm_config_image_thumb_settings_aspect",
        "rm_config_image_settings_quality",
        "rm_config_image_thumb_settings_quality",
        "rm_config_image_settings_x_res",
        "rm_config_image_settings_y_res",
        "rm_config_image_thumb_settings_x_res",
        "rm_config_image_thumb_settings_y_res"
    ];
    
    var isChanged = false;
    var i = 0;for (i; i < input_names.length; i++){
        if (RM.Pages.Config_Edit_Form.getForm().findField(input_names[i]).isDirty()) {
            RM.Pages.Config_Edit_Form.getForm().findField(input_names[i]).originalValue = RM.Pages.Config_Edit_Form.getForm().findField(input_names[i]).getValue();
            isChanged = true;
            break;
        }
    }

    var reload = false;
    if (RM.Pages.Config_Edit_Form.getForm().findField('rm_config_language_default_back').isDirty()) {
        reload = true;
    }

    if ( isChanged === false ){
        RM.Pages.Functions.SubmitConfig(null, reload);
        return;
    }
    
    Ext.MessageBox.confirm(
        RM.Translate.Admin.Config.Edit.ImageSettings,
        RM.Translate.Admin.Config.Edit.ImageResizeConfirmation,
        function(buttonID){
            var ImageResizeValue = 0;
            if (buttonID === 'yes'){
                ImageResizeValue = 1;
            } 
            RM.Pages.Functions.SubmitConfig(ImageResizeValue, reload);
        }
    );
};

RM.Pages.Functions.config_edit_clear_cache = function() {
    myMask = new Ext.LoadMask('rm_pages_config_edit', {msg:RM.Translate.Common.PleaseWait});
    myMask.show();
    var conn = new Ext.data.Connection();
    var request = {
        url: RM.Common.AssembleURL({
            controller : 'Config',
            action: 'clearcacheJson'
        }),
        method: 'GET',
        success: function(responseObject) {
            window.location.reload();
        },
        failure: function() {
            myMask.hide();
            Ext.MessageBox.alert(RM.Translate.Common.DeleteFailure);
        }
    };
    conn.request(request);
};

RM.Pages.Config_Edit_Toolbar = {
    xtype : "panel",
    id : "rm_pages_config_edit_toolbar",
    bodyBorder : false,
    html : RM.Common.getToolbar([{
        image: RM.BaseLargeImageURL+"save.gif",
        label: RM.Translate.Common.Save,
        link: "RM.Pages.Functions.config_edit_save()"
    },{
        image: RM.BaseLargeImageURL+"clearcache.gif",
        label: RM.Translate.Admin.Config.Edit.ClearCache,
        link: 'RM.Pages.Functions.config_edit_clear_cache()'
    }])
};

RM.Toolbars.push(RM.Pages.Config_Edit_Toolbar);


// this function is used by the dashboard link
RM.Pages.Functions.Config_Load = function(tabPageToShow){
    RM.Pages.Functions.MainTreeMenuHandler('Config', 'EditJson', tabPageToShow);
};


// radio button notes:
// in order for the radio buttons to work the db id should equal the form name value ie:
// id: rm_config_minimum_rental_valuetype
// value: day
// the db values should be combined like this:-
// rm_config_minimum_rental_valuetype + _ + day = rm_config_minimum_rental_valuetype_day
// the result is the name of the id of this field. During the loop below when values are
// set if the id of the radio = rm_config_minimum_rental_valuetype_day then then the
// property checked is set to true using setValue(true).
//
// So it is important that when using radio buttons:-
//  the db id = form name value
//  the db value = the checked value
//  the form name should be equal to the db id and the radio name for all the radios should be the same
//  the form id should be the db id + _ + the value to make this checked.
RM.Pages.Functions.Config_Edit_Itemselectors = [];
RM.Pages.Functions.Config_Language_Default_Back = "";
RM.Pages.Functions.Config_EditJson = function (responseObject) {

    RM.Help.Load('Admin.Config.Site.Main'); // load the help

    RM.Pages.Functions.Config_Edit_Itemselectors = [];
    var i = 0;for (i; i < responseObject.fields.length; i++ ){
        field = responseObject.fields[i];

        if (field.id === "rm_config_language_default_back"){
            RM.Pages.Functions.Config_Language_Default_Back = field.value;
        }

        element = Ext.getCmp(field.id);
        xtype = field.xtype;
        switch(xtype){
            case "notxtype":
                break;
            case "radio":
                radioId = field.id+"_"+field.value;
                element = Ext.getCmp(radioId); // overwrite this value with the id used for radios see not above
                if (field.id+"_"+field.value===element.id){
                    element.setValue(true);
                }
                break;
            case "slider":
                Ext.getCmp(field.id+"_slider").setValue(field.value);
                element.setValue(field.value, undefined, true);
                break;
            default:
                element.setValue(field.value, undefined, true);
        }

    }

    Ext.getCmp('toolbar_panel').layout.setActiveItem('rm_pages_config_edit_toolbar');

    Ext.getCmp('rm_pages_config_edit_page_configuration_tabs').on('tabchange', function (page, currentTab){
        currentTab.doLayout();
    });

    Ext.getCmp('rm_pages_config_edit_main_tabpanel').on('tabchange', function (page, currentTab){
        currentTab.doLayout();
    });

    new Ext.ToolTip({
        target: 'rm_config_admin_help_panel_enable',
        html: RM.Translate.Admin.Config.Edit.HelpPanelTip
    });
    Ext.QuickTips.init();
    
    Ext.getCmp('content-panel').layout.setActiveItem('rm_pages_config_edit');

    if (responseObject.licensing===true){
             Ext.getCmp('rm_pages_config_edit_main_tabpanel').activate("rm_pages_config_edit_tab6");
    }
    
    if (responseObject.language===true){
             Ext.getCmp('rm_pages_config_edit_main_tabpanel').activate("rm_pages_config_edit_tab7");
    }
    
    Ext.getCmp('rm_pages_config_edit').doLayout(false,true);
    Ext.getCmp('rm_pages_config_edit_main_tabpanel').doLayout();
};

RM.Pages.Config_Edit_UnitListOrder = new Ext.data.JsonStore({
    fields:[{
        name:'value',
        type:'string'
    },{
        name:'text',
        type:'string'
    }],
    data: RM.Translate.Common.JSON.Order
});

RM.Pages.Config_Edit_DateFormat = new Ext.data.JsonStore({
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
    data: RM.Translate.Common.JSON.DateFormats
});

RM.Pages.Config_Edit_UnitsMeasurement = new Ext.data.JsonStore({
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
    data: RM.Translate.Common.JSON.UnitsMeasurement
});

RM.Pages.Config_Edit_MinimumRental = new Ext.data.SimpleStore({
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
    data: RM.Common.Combo_Number_Data(0,24,1)
});

RM.Pages.Config_Edit_Calendar_Details_MonthstoShow = new Ext.data.SimpleStore({
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
    data: RM.Common.Combo_Number_Data(1,12,1)
});

RM.Pages.Config_Edit_Calendar_Details_Columns = new Ext.data.SimpleStore({
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
    data: RM.Common.Combo_Number_Data(1,4,1)
});

RM.Pages.Config_Edit_CarouselThumbsSelection = new Ext.data.SimpleStore({
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
    data: RM.Common.Combo_Number_Data(1,10,1)
});

RM.Pages.Config_Edit_Mailer = new Ext.data.JsonStore({
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
    data: RM.Translate.Common.JSON.MailerChoices
});

RM.Pages.Config_Edit_CHMOD_Options = new Ext.data.JsonStore({
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
    data: RM.Translate.Common.JSON.CHMODoptions
});

RM.Pages.Config_Edit_Editor_Options = new Ext.data.JsonStore({
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
    data: RM.Translate.Common.JSON.EditorOptions
});

// admin config
RM.Pages.Config_Edit_Tab1 = new Ext.Panel({
    xtype : "panel",
    height: RM.Common.GetPanelHeight(RM.Pages.Config_PageTabOffset),
    id: 'rm_pages_config_edit_tab1',
    bodyStyle : "padding:10px;",
    layout: 'form',
    labelWidth: 190,
    bodyBorder: false,
    autoScroll: true,
    title: RM.Translate.Admin.Config.Edit.Admin,
    items : [{
        xtype : "fieldset",
        title: RM.Translate.Admin.Config.Edit.Help,
        autoHeight: true,
        layout: 'form',
        id : "rm_pages_config_edit_help_fieldset",
        autoWidth: true,
        bodyBorder : false,
        items : [{
            id : "rm_config_admin_help_panel_enable",
            xtype : "xcheckbox",
            name : "rm_config_admin_help_panel_enable",
            inputValue : "1",
            fieldLabel : RM.Translate.Admin.Config.Edit.HelpPanel,
            listeners: {
                'check': function(f,v){
                    var enblHelpPanel = false;
                    if (RM.Config.rm_config_admin_help_panel_enable === "1"){enblHelpPanel = true;}
                    if (v!==enblHelpPanel){
                        Ext.MessageBox.confirm(RM.Translate.Admin.Config.Edit.ReloadRequired,RM.Translate.Admin.Config.Edit.ReloadRequiredMSG, function(buttonID){
                            if (buttonID !== 'yes') {
                                return;
                            }

                            // save and reload
                            RM.Pages.Functions.SubmitConfig(false,true);
                        });
                    }
                }
            }
        }]
    },{
        xtype : "fieldset",
        title: RM.Translate.Admin.Config.Edit.AdminConsoleTuning,
        autoHeight: true,
        layout: 'form',
        id : "rm_pages_config_edit_adminconsoletuning_fieldset",
        autoWidth: true,
        bodyBorder : false,
        items : [{
            id : "rm_config_enable_units_on_treemenu",
            xtype : "xcheckbox",
            name : "rm_config_enable_units_on_treemenu",
            inputValue : "1",
            fieldLabel : RM.Translate.Admin.Config.Edit.EnableUnitsOnTree,
            listeners: {
                'check': function(f,v){
                    var enableTreeUnitsVar = false;
                    if (RM.Common.EnableUnitsOnTreeMenu === "1"){enableTreeUnitsVar = true;}
                    if (v!==enableTreeUnitsVar){
                        Ext.MessageBox.confirm(RM.Translate.Admin.Config.Edit.ReloadRequired,RM.Translate.Admin.Config.Edit.ReloadRequiredMSG, function(buttonID){
                            if (buttonID !== 'yes') {
                                return;
                            }
                            // save and reload
                            RM.Pages.Functions.SubmitConfig(false,true);
                        });
                    }
                }
            }
       },{
            id : "rm_config_reservations_list_buffersize",
            xtype : "textfield",
            name : "rm_config_reservations_list_buffersize",
            fieldLabel : RM.Translate.Admin.Config.Edit.ReservationListBuffer 
       }]
    },{
        xtype : "fieldset",
        title: RM.Translate.Admin.Config.Edit.Users,
        autoHeight: true,
        layout: 'form',
        id : "rm_pages_config_edit_users_fieldset",
        autoWidth: true,
        bodyBorder : false,
        items : [{
            id : "rm_config_enable_user_groups",
            xtype : "xcheckbox",
            name : "rm_config_enable_user_groups",
            inputValue : "1",
            fieldLabel : RM.Translate.Admin.Config.Edit.UserGroups,
            listeners: {
                'check': function(f,v){
                    var usrGrpVar = false;
                    if (RM.Common.EnableUserGroups === "1"){usrGrpVar = true;}
                    if (v!==usrGrpVar){
                        Ext.MessageBox.confirm(RM.Translate.Admin.Config.Edit.ReloadRequired,RM.Translate.Admin.Config.Edit.ReloadRequiredMSG, function(buttonID){
                            if (buttonID !== 'yes') {
                                return;
                            }

                            // save and reload
                            RM.Pages.Functions.SubmitConfig(false,true);
                        });
                    }
                }
            }
       }]
    },{
        xtype : "fieldset",
        title: RM.Translate.Admin.Config.Edit.OtherOptions,
        autoHeight: true,
        layout: 'form',
        id : "rm_pages_config_edit_otherOptions_fieldset",
        autoWidth: true,
        bodyBorder : false,
        items : [{
            id : "rm_config_editor",
            hiddenName : "rm_config_editor_hidden",
            name : "rm_config_editor_name",
            xtype : "combo",
            fieldLabel : RM.Translate.Admin.Config.Edit.Editor,
            store : RM.Pages.Config_Edit_Editor_Options,
            forceSelection: true,
            mode: "local",
            triggerAction: 'all',
            selectOnFocus: true,
            valueField: 'value',
            displayField: 'text',
            allowBlank: false,
            emptyText: RM.Translate.Common.PleaseSelect,
            cls: "RM_Configuration_Combo_Fix"
        }]
    }]
});

// help for tab 1
RM.Pages.Config_Edit_Tab1.on('afterlayout', function(){
    RM.Help.Create('rm_config_admin_help_panel_enable', 'Admin.Config.Site.AdminHelpPanel');
    return true;
});

// Site config
RM.Pages.Config_Edit_Tab2 = new Ext.Panel({
    xtype : "panel",
    height: RM.Common.GetPanelHeight(RM.Pages.Config_PageTabOffset),
    id: 'rm_pages_config_edit_tab2',
    bodyStyle : "padding:10px;",
    layout: 'form',
    labelWidth: 190,
    bodyBorder: false,
    autoScroll: true,
    title: RM.Translate.Admin.Config.Edit.Site,
    items : [{
        xtype : "fieldset",
        title: RM.Translate.Admin.Config.Edit.Prices,
        autoHeight: true,
        layout: 'form',
        id : "rm_pages_config_edit_prices_fieldset",
        autoWidth: true,
        bodyBorder : false,
        items : [{
            id : "rm_config_prices_with_tax",
            xtype : "xcheckbox",
            name : "rm_config_prices_with_tax",
            inputValue : "1",
            fieldLabel : RM.Translate.Admin.Config.Edit.PricesWithTax
        }]
    },{
        xtype : "fieldset",
        title: RM.Translate.Admin.Config.Edit.CurrencyFormat,
        autoHeight: true,
        layout: 'form',
        id : "rm_pages_config_edit_currencyformats_fieldset",
        autoWidth: true,
        bodyBorder : false,
        items : [{
            xtype : "textfield",
            id : "rm_config_currency_symbol",
            name : "rm_config_currency_symbol",
            fieldLabel : RM.Translate.Admin.Config.Edit.CurrencySymbol,
            width : 40
        },{
            xtype : "textfield",
            id : "rm_config_currency_iso",
            name : "rm_config_currency_iso",
            fieldLabel : RM.Translate.Admin.Config.Edit.CurrencyISO,
            width : 40
        }]
    },{
        xtype : "fieldset",
        title: RM.Translate.Admin.Config.Edit.UnitsAndFormats,
        autoHeight: true,
        layout: 'form',
        id : "rm_pages_config_edit_unitsandformats_fieldset",
        autoWidth: true,
        bodyBorder : false,
        items : [{
            id : "rm_config_dateformat",
            hiddenName : "rm_config_dateformat_hidden",
            name : "rm_config_dateformat_name",
            xtype : "combo",
            fieldLabel : RM.Translate.Admin.Config.Edit.DateFormat,
            store : RM.Pages.Config_Edit_DateFormat,
            forceSelection: true,
            mode: "local",
            triggerAction: 'all',
            selectOnFocus: true,
            valueField: 'value',
            displayField: 'text',
            allowBlank: false,
            emptyText: RM.Translate.Common.PleaseSelect,
            cls: "RM_Configuration_Combo_Fix"
        },{
            id : "rm_config_unit_of_measure",
            name : "rm_config_unit_of_measure_name",
            hiddenName : "rm_config_unit_of_measure_hidden",
            xtype : "combo",
            fieldLabel : RM.Translate.Admin.Config.Edit.UnitOfMeasure,
            store : RM.Pages.Config_Edit_UnitsMeasurement,
            forceSelection: true,
            mode: "local",
            triggerAction: 'all',
            selectOnFocus: true,
            valueField: 'value',
            displayField: 'text',
            allowBlank: false,
            emptyText: RM.Translate.Common.PleaseSelect
        }]
    },{
        xtype : "fieldset",
        title: RM.Translate.Admin.Config.Edit.ReservationSystem,
        autoHeight: true,
        layout: 'form',
        id : "rm_pages_config_edit_reservationsystem_fieldset",
        autoWidth: true,
        bodyBorder : false,
        items: [{
            id : "rm_config_availability_checking",
            xtype : "xcheckbox",
            name : "rm_config_availability_checking",
            inputValue : "1",
            fieldLabel : RM.Translate.Admin.Config.Edit.AvailabilityCheckingEnabled
        },{
            xtype : "fieldset",
            title: RM.Translate.Admin.Config.Edit.MinimumRental,
            autoHeight: true,
            layout: 'form',
            id : "rm_pages_config_edit_rentalperiod_fieldset",
            autoWidth: true,
            bodyBorder : false,
            items: [{
                id : "rm_config_minimal_rental_duration",
                name : "rm_config_minimal_rental_duration_name",
                hiddenName : "rm_config_minimal_rental_duration_hidden",
                xtype : "combo",
                fieldLabel : RM.Translate.Admin.Config.Edit.MinimumRentalDuration,
                store : RM.Pages.Config_Edit_MinimumRental,
                forceSelection: true,
                mode: "local",
                triggerAction: 'all',
                selectOnFocus: true,
                valueField: 'value',
                displayField: 'text',
                allowBlank: false,
                emptyText: RM.Translate.Common.PleaseSelect
            },{
                id : "rm_config_minimum_rental_valuetype_day",
                xtype : "radio",
                name : "rm_config_minimum_rental_valuetype",
                inputValue : "day",
                fieldLabel : RM.Translate.Admin.Config.Edit.Day
            },{
                id : "rm_config_minimum_rental_valuetype_hour",
                xtype : "radio",
                name : "rm_config_minimum_rental_valuetype",
                inputValue : "hour",
                fieldLabel : RM.Translate.Admin.Config.Edit.Hours
            }]
        }]
    },{
        xtype : "fieldset",
        title: RM.Translate.Admin.Config.Edit.HostingSpecific,
        autoHeight: true,
        layout: 'form',
        id : "rm_pages_config_edit_hostingspecfic_fieldset",
        autoWidth: true,
        bodyBorder : false,
        items : [{
            fieldLabel: RM.Translate.Admin.Config.Edit.FileFolderPermissions,
            id : "rm_config_chmod_value",
            name : "rm_config_chmod_value_name",
            hiddenName : "rm_config_chmod_value_hidden",
            xtype: "combo",
            mode: 'local',
            triggerAction: 'all',
            typeAhead: true,
            resizable: false,
            valueField: 'value',
            displayField: 'text',
            store: RM.Pages.Config_Edit_CHMOD_Options
        }]
    },{
        xtype : "fieldset",
        title: RM.Translate.Admin.Config.Edit.Branding,
        autoHeight: true,
        layout: 'form',
        id : "rm_pages_config_edit_branding_options",
        autoWidth: true,
        bodyBorder : false,
        items : [{
            id : "rm_config_show_poweredby_logo",
            xtype : "xcheckbox",
            name : "rm_config_show_poweredby_logo",
            inputValue : "1",
            fieldLabel : RM.Translate.Admin.Config.Edit.ShowPoweredBy
       }]
    }]
});

RM.Pages.Config_Edit_Tab2.on('afterlayout', function(){
    RM.Help.Create('rm_config_currency_symbol', 'Admin.Config.Site.CurrencySymbol');
    RM.Help.Create('rm_config_currency_iso', 'Admin.Config.Site.CurrencyISO');
    RM.Help.Create('rm_config_dateformat', 'Admin.Config.Site.DateFormat');
    RM.Help.Create('rm_config_unit_of_measure', 'Admin.Config.Site.UnitofMeasure');
    RM.Help.Create('rm_config_availability_checking', 'Admin.Config.Site.AvailabilityChecking');
    RM.Help.Create('rm_config_minimal_rental_duration', 'Admin.Config.Site.MinimumRentalDuration');
    RM.Help.Create('rm_config_minimum_rental_valuetype_day', 'Admin.Config.Site.MinimumRentalDay');
    RM.Help.Create('rm_config_minimum_rental_valuetype_hour', 'Admin.Config.Site.MinimumRentalHour');
    return true;
});

// setup the drag and drop grids...
// See http://examples.extjs.eu/ddgrids.js for more information

RM.Pages.Functions.Config_Edit_GridDropZone = function(grid, config) {
    this.grid = grid;
    RM.Pages.Functions.Config_Edit_GridDropZone.superclass.constructor.call(this, grid.view.scroller.dom, config);
};

Ext.extend(RM.Pages.Functions.Config_Edit_GridDropZone, Ext.dd.DropZone, {

    onContainerOver:function(dd, e, data) {
        return dd.grid !== this.grid ? this.dropAllowed : this.dropNotAllowed;
    },
    onContainerDrop:function(dd, e, data) {
        if(dd.grid !== this.grid) {
            this.grid.store.add(data.selections);
            Ext.each(data.selections, function(r) {
                dd.grid.store.remove(r);
            });
            this.grid.onRecordsDrop(dd.grid, data.selections);
            return true;
        } else {
            return false;
        }
    }
});


// Page Config
RM.Pages.Config_Edit_Tab3 = new Ext.Panel({
    xtype : "panel",
    id: 'rm_pages_config_edit_tab3',
    height: RM.Common.GetPanelHeight(RM.Pages.Config_PageTabOffset),
    title : RM.Translate.Admin.Config.Edit.PageConfiguration,
    bodyBorder: false,
    autoScroll: true,
    layout: 'form',
    labelWidth: 190,
    items : [{
        xtype : "tabpanel",
        id : "rm_pages_config_edit_page_configuration_tabs",
        activeTab : 0,
        bodyStyle : "padding:10px;",
        bodyBorder: false,
        height: RM.Common.GetPanelHeight(RM.Pages.Config_PageTabOffset),
        items : [{
            xtype : "panel",
            layout: 'form',
            title : RM.Translate.Common.List,
            autoWidth: true,
            bodyBorder: false,
            items: [{
                xtype : "fieldset",
                title : RM.Translate.Admin.Config.Edit.ListOptions,
                autoHeight: true,
                layout: 'form',
                id : "rm_pages_config_edit_listoptions_fieldset",
                autoWidth: true,
                bodyBorder : false,
                labelWidth: 190,
                items: [{
                    xtype : "combo",
                    id : "rm_config_unit_list_order",
                    name : "rm_config_unit_list_order_name",
                    hiddenName : "rm_config_unit_list_order_hidden",
                    triggerAction: 'all',
                    fieldLabel : RM.Translate.Admin.Config.Edit.UnitListOrder,
                    store : RM.Pages.Config_Edit_UnitListOrder,
                    mode: 'local',
                    typeAhead: true,
                    resizable: false,
                    valueField: 'value',
                    displayField: 'text'
                }]
            }]
        },{
            xtype : "panel",
            title : RM.Translate.Common.Calendar,
            autoWidth: true,
            bodyBorder: false,
            items : [{
                xtype : "fieldset",
                title : RM.Translate.Admin.Config.Edit.CalendarOptions,
                autoHeight: true,
                layout: 'form',
                id : "rm_pages_config_edit_calendaroptions_fieldset",
                autoWidth: true,
                bodyBorder : false,
                labelWidth: 190,
                items : [{
                    xtype : "label",
                    width : 164,
                    text : RM.Translate.Admin.Config.Edit.ItemsToShowOnCalendar
                },{
                    xtype : "xcheckbox",
                    id : "rm_config_calendar_confirmed",
                    name : "rm_config_calendar_confirmed",
                    inputValue : "1",
                    fieldLabel : RM.Translate.Common.Confirmed
                },{
                    xtype : "xcheckbox",
                    id : "rm_config_calendar_nonconfirmed",
                    name : "rm_config_calendar_nonconfirmed",
                    inputValue : "1",
                    fieldLabel : RM.Translate.Admin.Config.Edit.NonConfirmed
                },{
                    xtype : "combo",
                    id : "rm_config_calendar_startday",
                    name : "rm_config_calendar_startday_name",
                    hiddenName : "rm_config_calendar_startday_hidden",
                    triggerAction: 'all',
                    fieldLabel : RM.Translate.Admin.Config.Edit.CalendarStartDay,
                    store : RM.Translate.Common.JSON.WeekDaysLongNumbered
                },{
                    id : "rm_config_calendar_user_details_columns",
                    name : "rm_config_calendar_user_details_columns_name",
                    hiddenName : "rm_config_calendar_user_details_columns_hidden",
                    xtype : "combo",
                    fieldLabel : RM.Translate.Admin.Config.Edit.CalendarDetailsColumns,
                    store : RM.Pages.Config_Edit_Calendar_Details_Columns,
                    mode: "local",
                    triggerAction: 'all',
                    selectOnFocus: true,
                    valueField: 'value',
                    displayField: 'text',
                    allowBlank: false
                },{
                    id : "rm_config_calendar_user_details_months",
                    name : "rm_config_calendar_user_details_months_name",
                    hiddenName : "rm_config_calendar_user_details_months_hidden",
                    xtype : "combo",
                    fieldLabel : RM.Translate.Admin.Config.Edit.CalendarDetailsMonths,
                    store : RM.Pages.Config_Edit_Calendar_Details_MonthstoShow,
                    mode: "local",
                    triggerAction: 'all',
                    selectOnFocus: true,
                    valueField: 'value',
                    displayField: 'text',
                    allowBlank: false
                }]
            }]
        },{
            xtype : "panel",
            id: "rm_pages_config_tab_pageconfig_general",
            title : RM.Translate.Admin.Config.Edit.General,
            bodyBorder : false,
            items : [{
                xtype : "fieldset",
                title : RM.Translate.Admin.Config.Edit.reCaptchaOptions,
                autoHeight: true,
                layout: 'form',
                id : "rm_pages_config_edit_general_fieldset",
                autoWidth: true,
                bodyBorder : false,
                autoScroll: true,
                labelWidth: 190,
                items : [{
                    xtype : "xcheckbox",
                    id : "rm_config_recaptcha_enabled",
                    name : "rm_config_recaptcha_enabled",
                    inputValue : "1",
                    fieldLabel : RM.Translate.Admin.Config.Edit.ReCaptchaEnable
                },{
                    xtype : "xcheckbox",
                    id : "rm_config_recaptcha_ssl",
                    name : "rm_config_recaptcha_ssl",
                    inputValue : "1",
                    fieldLabel : RM.Translate.Admin.Config.Edit.ReCaptchaSSL
                },{
                    xtype : "textfield",
                    id : "rm_config_recaptcha_privatekey",
                    name : "rm_config_recaptcha_privatekey",
                    fieldLabel : RM.Translate.Admin.Config.Edit.ReCaptchaPrivateKey,
                    width : 400
                },{
                    xtype : "textfield",
                    id : "rm_config_recaptcha_publickey",
                    name : "rm_config_recaptcha_publickey",
                    fieldLabel : RM.Translate.Admin.Config.Edit.ReCaptchaPublicKey,
                    width : 400
                },{
                    fieldLabel: '',
                    html: '<div id="rm_pages_admin_config_recaptcha_signuplink"><a href="http://www.google.com/recaptcha/whyrecaptcha" target="_blank">'+RM.Translate.Admin.Config.Edit.ReCaptchaSignUp+'</a></div>',
                    bodyBorder : false
                }
                ]
            },{
                xtype : "fieldset",
                title : RM.Translate.Admin.Config.Edit.CartOptions,
                autoHeight: true,
                layout: 'form',
                id : "rm_pages_config_edit_cartoptions_fieldset",
                autoWidth: true,
                bodyBorder : false,
                autoScroll: true,
                labelWidth: 190,
                items : [{
                    xtype : "xcheckbox",
                    id : "rm_config_gotocartsfterselection_enabled",
                    name : "rm_config_gotocartsfterselection_enabled",
                    inputValue : "1",
                    fieldLabel : RM.Translate.Admin.Config.Edit.CartAfterAdd
                }]
            }]
        }]
    }]
});

// help for tab 3
RM.Pages.Config_Edit_Tab3.on('afterlayout', function(){
    RM.Help.Create('rm_config_calendar_confirmed', 'Admin.Config.Site.CalendarConfirmed');
    RM.Help.Create('rm_config_calendar_nonconfirmed', 'Admin.Config.Site.CalendarUnConfirmed');
    RM.Help.Create('rm_config_calendar_startday', 'Admin.Config.Site.CalendarStartDay');
    return true;
});

// Notification Config
RM.Pages.Config_Edit_Tab4 = new Ext.Panel({
    xtype : "panel",
    id: 'rm_pages_config_edit_tab4',
    height: RM.Common.GetPanelHeight(RM.Pages.Config_PageTabOffset),
    title : RM.Translate.Admin.Config.Edit.Notifications,
    bodyStyle : "padding:10px;",
    labelWidth: 190,
    autoScroll: true,
    bodyBorder: false,
    items : [{
        xtype : "fieldset",
        title: RM.Translate.Common.Email,
        autoHeight: true,
        layout: 'form',
        labelWidth: 190,
        id : "rm_pages_config_edit_email_fieldset",
        autoWidth: true,
        bodyBorder : false,
        items : [{
            xtype : "textfield",
            id : "rm_config_administrator_email",
            name : "rm_config_administrator_email",
            fieldLabel : RM.Translate.Admin.Config.Edit.AdministratorEmail,
            width : 208
        },{
            xtype : "button",
            fieldLabel : RM.Translate.Admin.Config.Edit.TestEmailLabel,
            text : RM.Translate.Admin.Config.Edit.Test,
            handler : function(button, action){
                var email = Ext.getCmp('rm_config_administrator_email').getValue();
                if (Ext.form.VTypes.email(email) === false){
                    Ext.Msg.alert(
                        RM.Translate.Admin.Common.Error,
                        RM.Translate.Admin.Config.Edit.WrongEmailFormat
                    );
                    return;
                } else {
                    var myMask = new Ext.LoadMask('rm_pages_config_edit', {msg:RM.Translate.Common.PleaseWait});
                    myMask.show();

                    var conn = new Ext.data.Connection();
                    var request = {
                        url: RM.Common.AssembleURL({
                            controller : 'Config',
                            action: 'testemailjson',
                            parameters : [{
                                name: 'email',
                                value: email
                            }]
                        }),
                        timeout: 60000,
                        method: 'POST',
                        success: function(responseObject) {                            
                            eval('var object='+responseObject.responseText);
                            if (object.success === false) {
                                Ext.MessageBox.alert(RM.Translate.Common.Failed, RM.Translate.Admin.Config.Edit.TestEmailServerResponse + object.error);
                            } else {
                                Ext.MessageBox.alert(RM.Translate.Common.Success, RM.Translate.Admin.Config.Edit.TestEmailSuccess);
                            }
                            myMask.hide();                            
                        },
                        failure: function() {                            
                            Ext.Msg.alert(RM.Translate.Common.Status, RM.Translate.Common.UnableToShow);
                            myMask.hide();
                        }
                    };
                    conn.request(request);
                }
            }
        }]
    },{
        xtype : "fieldset",
        title: RM.Translate.Admin.Config.Edit.Settings,
        autoHeight: true,
        layout: 'form',
        labelWidth: 255,
        id : "rm_pages_config_edit_email_settings_fieldset",
        autoWidth: true,
        bodyBorder : false,
        items : [{
            id : "rm_config_email_settings_mailer",
            name : "rm_config_email_settings_mailer_name",
            hiddenName : "rm_config_email_settings_mailer_hidden",
            xtype : "combo",
            fieldLabel : RM.Translate.Admin.Config.Edit.Mailer,
            store : RM.Pages.Config_Edit_Mailer,
            forceSelection: true,
            mode: "local",
            triggerAction: 'all',
            selectOnFocus: true,
            valueField: 'value',
            displayField: 'text',
            allowBlank: false,
            emptyText: RM.Translate.Common.PleaseSelect,
            listeners:{
                select: function(){
                    if (this.getValue()==="SMTP"){
                        Ext.getCmp("rm_pages_config_edit_email_smtpsettings_fieldset").expand();
                    } else {
                        Ext.getCmp("rm_pages_config_edit_email_smtpsettings_fieldset").collapse();
                    }
                }
            }
        },{
            xtype : "fieldset",
            title: RM.Translate.Admin.Config.Edit.SMTPSettings,
            autoHeight: true,
            layout: 'form',
            labelWidth: 255,
            id : "rm_pages_config_edit_email_smtpsettings_fieldset",
            autoWidth: true,
            bodyBorder : false,
            collapsible: true,
            collapsed: true,
            animCollapse: true,
            items: [
                {
                    xtype : "textfield",
                    id : "rm_config_email_settings_smtphost",
                    name : "rm_config_email_settings_smtphost",
                    fieldLabel : RM.Translate.Admin.Config.Edit.EmailSMTPHost,
                    width : 208
                },{
                    xtype : "textfield",
                    id : "rm_config_email_settings_smtpsecure",
                    name : "rm_config_email_settings_smtpsecure",
                    fieldLabel : RM.Translate.Admin.Config.Edit.EmailSMTPSecure,
                    width : 208
                },{
                    xtype : "numberfield",
                    id : "rm_config_email_settings_smtpport",
                    name : "rm_config_email_settings_smtpport",
                    fieldLabel : RM.Translate.Admin.Config.Edit.EmailSMTPPort,
                    width : 208
                },{
                    xtype : "textfield",
                    id : "rm_config_email_settings_smtpuser",
                    name : "rm_config_email_settings_smtpuser",
                    fieldLabel : RM.Translate.Admin.Config.Edit.EmailSMTPUser,
                    width : 208
                },{
                    xtype : "textfield",
                    id : "rm_config_email_settings_smtppass",
                    name : "rm_config_email_settings_smtppass",
                    fieldLabel : RM.Translate.Admin.Config.Edit.EmailSMTPPass,
                    width : 208,
                    inputType : 'password'
                }
            ],
            listeners: {
                afterlayout: function(){
                    if (Ext.getCmp("rm_config_email_settings_mailer").getValue()==="SMTP"){
                        Ext.getCmp("rm_pages_config_edit_email_smtpsettings_fieldset").expand();
                    } else {
                        Ext.getCmp("rm_pages_config_edit_email_smtpsettings_fieldset").collapse();
                    }
                }
            }
        },{
            xtype : "textfield",
            id : "rm_config_email_settings_mailfrom",
            name : "rm_config_email_settings_mailfrom",
            fieldLabel : RM.Translate.Admin.Config.Edit.EmailFrom,
            width : 208
        },{
            xtype : "textfield",
            id : "rm_config_email_settings_fromname",
            name : "rm_config_email_settings_fromname",
            fieldLabel : RM.Translate.Admin.Config.Edit.FromName,
            width : 208
        }
        ]
    }]
});

RM.Pages.Config_Edit_Tab4.on('afterlayout', function(){
    RM.Help.Create('rm_config_administrator_email', 'Admin.Config.Site.AdminEmail');
    return true;
});

// ful sized image...
RM.Pages.Config_Edit_FullSize_Image_Settings = {
    xtype : "panel",
    id: 'rm_pages_config_edit_tab5_fullsize_image_settings',
    layout: "form",
    width: 350,
    bodyBorder: false,
    items:[{
        xtype : "slider",
        isFormField: true,
        id : "rm_config_image_settings_x_res_slider",
        name : "rm_config_image_settings_x_res_slider",
        fieldLabel : RM.Translate.Admin.Config.Edit.ResolutionX,
        width : 100,
        minValue: 0,
        maxValue: 500,
        value: 250,
        animate: true,
        listeners: {
            'drag': function(slider, event) {
                if (Ext.getCmp('rm_config_image_settings_aspect').getValue()){
                    document.getElementById('rm_pages_config_edit_fullsize_image_preview_resizable').style.height = Ext.getCmp('rm_config_image_settings_x_res_slider').getValue() + "px";
                    document.getElementById('rm_pages_config_edit_fullsize_image_preview_resizable').style.width = Ext.getCmp('rm_config_image_settings_x_res_slider').getValue() + "px";
                    Ext.getCmp('rm_config_image_settings_y_res_slider').setValue(Ext.getCmp('rm_config_image_settings_x_res_slider').getValue());
                    // update the text field
                    Ext.getCmp('rm_config_image_settings_x_res').setValue(Ext.getCmp('rm_config_image_settings_x_res_slider').getValue());
                    Ext.getCmp('rm_config_image_settings_y_res').setValue(Ext.getCmp('rm_config_image_settings_x_res_slider').getValue());
                } else {
                    document.getElementById('rm_pages_config_edit_fullsize_image_preview_resizable').style.height = Ext.getCmp('rm_config_image_settings_x_res_slider').getValue() + "px";
                    // update the text field
                    Ext.getCmp('rm_config_image_settings_x_res').setValue(Ext.getCmp('rm_config_image_settings_x_res_slider').getValue());
                }
            
            }
        }
    },{
        // hidden value to store slider value
        xtype : "textfield",
        id : "rm_config_image_settings_x_res",
        width : 30
    },{
        xtype : "slider",
        isFormField: true,
        name : "rm_config_image_settings_y_res_slider",
        id : "rm_config_image_settings_y_res_slider",
        fieldLabel : RM.Translate.Admin.Config.Edit.ResolutionY,
        width : 100,
        minValue: 0,
        maxValue: 500,
        value: 250,
        listeners: {
            'drag': function(slider, event) {
                if (Ext.getCmp('rm_config_image_settings_aspect').getValue()){
                    document.getElementById('rm_pages_config_edit_fullsize_image_preview_resizable').style.height = Ext.getCmp('rm_config_image_settings_y_res_slider').getValue() + "px";
                    document.getElementById('rm_pages_config_edit_fullsize_image_preview_resizable').style.width = Ext.getCmp('rm_config_image_settings_y_res_slider').getValue() + "px";
                    Ext.getCmp('rm_config_image_settings_x_res_slider').setValue(Ext.getCmp('rm_config_image_settings_y_res_slider').getValue());
                    // update the text field
                    Ext.getCmp('rm_config_image_settings_x_res').setValue(Ext.getCmp('rm_config_image_settings_y_res_slider').getValue());
                    Ext.getCmp('rm_config_image_settings_y_res').setValue(Ext.getCmp('rm_config_image_settings_y_res_slider').getValue());
                } else {
                    document.getElementById('rm_pages_config_edit_fullsize_image_preview_resizable').style.width = Ext.getCmp('rm_config_image_settings_y_res_slider').getValue() + "px";
                    // update the text field
                    Ext.getCmp('rm_config_image_settings_y_res').setValue(Ext.getCmp('rm_config_image_settings_y_res_slider').getValue());
                }
                
            }
        }
    },{
        // hidden value to store slider value
        xtype : "textfield",
        id : "rm_config_image_settings_y_res",
        width : 30
    },{
        xtype : "slider",
        isFormField: true,
        name : "rm_config_image_settings_quality_slider",
        id : "rm_config_image_settings_quality_slider",
        fieldLabel : RM.Translate.Admin.Config.Edit.Quality,
        width : 100,
        minValue: 0,
        maxValue: 100,
        value: 50,
        listeners: {
            'drag': function() {
                //TODO: we need to fix 'quality' slider in IE later
                //document.getElementById('rm_pages_config_edit_fullsize_image_preview_resizable').style.filter = "Blur(Add = 0, Direction = 225, Strength = "+Ext.getCmp('rm_config_image_settings_quality_slider').getValue()+")";

                // update the text field
                Ext.getCmp('rm_config_image_settings_quality').setValue(Ext.getCmp('rm_config_image_settings_quality_slider').getValue());
            }
        }
    },{
        // hidden value to store slider value
        xtype : "textfield",
        id : "rm_config_image_settings_quality",
        width : 30
    },{
        xtype : "xcheckbox",
        id : "rm_config_image_settings_aspect",
        name : "rm_config_image_settings_aspect",
        inputValue : "1",
        fieldLabel : RM.Translate.Admin.Config.Edit.ResolutionKeepAspect
    }]

};

RM.Pages.Config_Edit_FullSize_Image_Preview = {
    xtype:'panel',
    html: "<img id='rm_pages_config_edit_fullsize_image_preview_resizable' src='" + RM.BaseLargeImageURL+"imagesettings_preview.jpg' border='0' height='200' width='200'>",
    listeners: {'afterlayout':function(){
        document.getElementById('rm_pages_config_edit_fullsize_image_preview_resizable').style.height = Ext.getCmp('rm_config_image_settings_y_res_slider').value + "px";
        document.getElementById('rm_pages_config_edit_fullsize_image_preview_resizable').style.width = Ext.getCmp('rm_config_image_settings_y_res_slider').value + "px";
    }}
};

RM.Pages.Config_Edit_ThumbSize_Image_Settings = {
    xtype : "panel",
    id: 'rm_pages_config_edit_tab5_thumbnail_image_settings',
    layout: "form",
    width: 350,
    bodyBorder: false,
    items:[{
        xtype : "slider",
        isFormField: true,
        id : "rm_config_image_thumb_settings_x_res_slider",
        name : "rm_config_image_thumb_settings_x_res_slider",
        fieldLabel : RM.Translate.Admin.Config.Edit.ResolutionX,
        width : 100,
        minValue: 0,
        maxValue: 200,
        value: 100,
        animate: true,
        listeners: {
            'drag': function(slider, event) {
                if (Ext.getCmp('rm_config_image_thumb_settings_aspect').getValue()){
                    document.getElementById('rm_pages_config_edit_thumb_image_preview_resizable').style.height = Ext.getCmp('rm_config_image_thumb_settings_x_res_slider').getValue() + "px";
                    document.getElementById('rm_pages_config_edit_thumb_image_preview_resizable').style.width = Ext.getCmp('rm_config_image_thumb_settings_x_res_slider').getValue() + "px";
                    Ext.getCmp('rm_config_image_thumb_settings_y_res_slider').setValue(Ext.getCmp('rm_config_image_thumb_settings_x_res_slider').getValue());
                    // textfeild settings
                    Ext.getCmp('rm_config_image_thumb_settings_x_res').setValue(Ext.getCmp('rm_config_image_thumb_settings_x_res_slider').getValue());
                    Ext.getCmp('rm_config_image_thumb_settings_y_res').setValue(Ext.getCmp('rm_config_image_thumb_settings_x_res_slider').getValue());
                } else {
                    document.getElementById('rm_pages_config_edit_thumb_image_preview_resizable').style.height = Ext.getCmp('rm_config_image_thumb_settings_x_res_slider').getValue() + "px";
                    // update the text field
                    Ext.getCmp('rm_config_image_thumb_settings_x_res').setValue(Ext.getCmp('rm_config_image_thumb_settings_x_res_slider').getValue());
                }
            }
            }
    },{
        // hidden value to store slider value
        xtype : "textfield",
        id : "rm_config_image_thumb_settings_x_res",
        width : 30
    },{
        xtype : "slider",
        isFormField: true,
        name : "rm_config_image_thumb_settings_y_res_slider",
        id : "rm_config_image_thumb_settings_y_res_slider",
        fieldLabel : RM.Translate.Admin.Config.Edit.ResolutionY,
        width : 100,
        minValue: 0,
        maxValue: 200,
        value: 100,
        listeners: {
            'drag': function(slider, event) {
                if (Ext.getCmp('rm_config_image_thumb_settings_aspect').getValue()){
                    document.getElementById('rm_pages_config_edit_thumb_image_preview_resizable').style.height = Ext.getCmp('rm_config_image_thumb_settings_y_res_slider').getValue() + "px";
                    document.getElementById('rm_pages_config_edit_thumb_image_preview_resizable').style.width = Ext.getCmp('rm_config_image_thumb_settings_y_res_slider').getValue() + "px";
                    Ext.getCmp('rm_config_image_thumb_settings_x_res_slider').setValue(Ext.getCmp('rm_config_image_thumb_settings_y_res_slider').getValue());
                    // textfeild settings
                    Ext.getCmp('rm_config_image_thumb_settings_x_res').setValue(Ext.getCmp('rm_config_image_thumb_settings_y_res_slider').getValue());
                    Ext.getCmp('rm_config_image_thumb_settings_y_res').setValue(Ext.getCmp('rm_config_image_thumb_settings_y_res_slider').getValue());
                } else {
                    document.getElementById('rm_pages_config_edit_thumb_image_preview_resizable').style.width = Ext.getCmp('rm_config_image_thumb_settings_y_res_slider').getValue() + "px";
                    // update the text field
                    Ext.getCmp('rm_config_image_thumb_settings_y_res').setValue(Ext.getCmp('rm_config_image_thumb_settings_y_res_slider').getValue());
                }
            }
            }
    },{
        // hidden value to store slider value
        xtype : "textfield",
        id : "rm_config_image_thumb_settings_y_res",
        width : 30
    },{
        xtype : "slider",
        isFormField: true,
        name : "rm_config_image_thumb_settings_quality_slider",
        id : "rm_config_image_thumb_settings_quality_slider",
        fieldLabel : RM.Translate.Admin.Config.Edit.Quality,
        width : 100,
        minValue: 0,
        maxValue: 100,
        value: 50,
        listeners: {
            'drag': function() {
                //TODO: we need to fix 'quality' slider in IE later
                //document.getElementById('rm_pages_config_edit_thumb_image_preview_resizable').style.filter = "Blur(Add = 0, Direction = 225, Strength = "+Ext.getCmp('rm_config_image_thumb_settings_quality_slider').getValue()+")";
                Ext.getCmp('rm_config_image_thumb_settings_quality').setValue(Ext.getCmp('rm_config_image_thumb_settings_quality_slider').getValue());
            }
            }
    },{
        // hidden value to store slider value
        xtype : "textfield",
        id : "rm_config_image_thumb_settings_quality",
        width : 30
    },{
        xtype : "xcheckbox",
        id : "rm_config_image_thumb_settings_aspect",
        name : "rm_config_image_thumb_settings_aspect",
        inputValue : "1",
        fieldLabel : RM.Translate.Admin.Config.Edit.ResolutionKeepAspect
    },{
        id : "rm_config_carousel_thumbs_to_show",
        name : "rm_config_carousel_thumbs_to_show_name",
        hiddenName : "rm_config_carousel_thumbs_to_show_hidden",
        xtype : "combo",
        fieldLabel : RM.Translate.Admin.Config.Edit.CarouselThumbsToShow,
        store : RM.Pages.Config_Edit_CarouselThumbsSelection,
        mode: "local",
        triggerAction: 'all',
        selectOnFocus: true,
        valueField: 'value',
        displayField: 'text',
        allowBlank: false
    }]

};

RM.Pages.Config_Edit_ThumbSize_Image_Preview = {
    xtype:'panel',
    html: "<img id='rm_pages_config_edit_thumb_image_preview_resizable' src='" + RM.BaseLargeImageURL+"imagesettings_preview.jpg' border='0' height='100' width='100'>",
    listeners: {'afterlayout':function(){
        document.getElementById('rm_pages_config_edit_thumb_image_preview_resizable').style.height = Ext.getCmp('rm_config_image_thumb_settings_y_res_slider').value + "px";
        document.getElementById('rm_pages_config_edit_thumb_image_preview_resizable').style.width = Ext.getCmp('rm_config_image_thumb_settings_y_res_slider').value + "px";
    }}
};

// Image Settings
RM.Pages.Config_Edit_Tab5 = new Ext.Panel({
    xtype : "panel",
    id: 'rm_pages_config_edit_tab5',
    autoScroll: true,
    height: RM.Common.GetPanelHeight(RM.Pages.Config_PageTabOffset),
    title : RM.Translate.Admin.Config.Edit.ImageSettings,
    bodyStyle : "padding-left: 10px; padding-right: 20px;",
    bodyBorder: false,    
    items :[{
        xtype : "fieldset",
        title: RM.Translate.Admin.Config.Edit.LargeImageProperties,
        autoHeight: true,
        layout: 'column',
        id : "rm_pages_config_edit_imagesettings_fieldset",
        bodyBorder : false,
        autoScroll: true,
        collapsible: true,
        items : [
            RM.Pages.Config_Edit_FullSize_Image_Settings,
            RM.Pages.Config_Edit_FullSize_Image_Preview
        ]
    },{
        xtype : "fieldset",
        title: RM.Translate.Admin.Config.Edit.ThumbnailImageProperties,
        autoHeight: true,
        layout: 'column',
        id : "rm_pages_config_edit_thumbimagesettings_fieldset",        
        bodyBorder : false,
        autoScroll: true,
        collapsible: true,
        items : [
            RM.Pages.Config_Edit_ThumbSize_Image_Settings,
            RM.Pages.Config_Edit_ThumbSize_Image_Preview
        ]
    }]
});

RM.Pages.Config_Edit_Tab5.on('afterlayout', function(){
    // load and assign help
    RM.Help.Create('rm_pages_config_edit_imagesettings_fieldset', 'Admin.Config.Site.ImageSettings');
    RM.Help.Create('rm_pages_config_edit_thumbimagesettings_fieldset', 'Admin.Config.Site.ThumbSettings');
    return true;
});

// License key
RM.Pages.Config_Edit_Tab6 = new Ext.Panel({
    xtype : "panel",
    id: 'rm_pages_config_edit_tab6',
    height: RM.Common.GetPanelHeight(RM.Pages.Config_PageTabOffset),
    title : RM.Translate.Admin.Config.Edit.Licensing,
    bodyStyle : "padding:10px;",
    bodyBorder: false,
    autoScroll: true,
    items :[{
        xtype : "fieldset",
        title: RM.Translate.Admin.Config.Edit.License,
        autoHeight: true,
        layout: 'form',
        id : "rm_pages_config_edit_license_fieldset",
        bodyBorder : false,
        items : [{
            xtype: "textarea",
            width : 300,
            fieldLabel: RM.Translate.Admin.Config.Edit.LicenseKey,
            name: 'rm_config_licensekey',
            id: 'rm_config_licensekey'
        }]
    }]
});

RM.Pages.Config_Edit_Tab6.on('afterlayout', function(){
    RM.Help.Create('rm_pages_config_edit_license_fieldset', 'Admin.Config.Site.License');
    return true;
});

// language config
RM.Pages.Config_Edit_Tab7 = new Ext.Panel({
    xtype : "panel",
    height: RM.Common.GetPanelHeight(RM.Pages.Config_PageTabOffset),
    id: 'rm_pages_config_edit_tab7',
    bodyStyle : "padding:10px;",
    layout: 'form',
    labelWidth: 190,
    bodyBorder: false,
    autoScroll: true,
    title: RM.Translate.Admin.Config.Edit.Languages,
    items : [{
        xtype : "fieldset",
        title: RM.Translate.Admin.Config.Edit.LanguageDefault,
        autoHeight: true,
        layout: 'form',
        id : "rm_pages_config_edit_languages_fieldset",
        autoWidth: true,
        bodyBorder : false,
        items : [{
            xtype : "combo",
            id : "rm_config_language_default_back",
            name : "rm_config_language_default_back_name",
            hiddenName : "rm_config_language_default_back_hidden",
            triggerAction: 'all',
            listeners: {
                'beforeselect' : function(combo, record, index){
                    if (record.data.field1 !== RM.Pages.Functions.Config_Language_Default_Back) {
                        Ext.Msg.alert(
                            RM.Translate.Admin.Config.Edit.Languages,
                            RM.Translate.Admin.Config.Edit.LanguageBackAlert
                        );
                    }
                    return true;
                }
            },
            fieldLabel : RM.Translate.Admin.Config.Edit.LanguageBack,
            store : RM.Languages.concat([["cms","CMS"]])
        },{
            xtype : "combo",
            id : "rm_config_language_default_front",
            name : "rm_config_language_default_front_name",
            hiddenName : "rm_config_language_default_front_hidden",
            triggerAction: 'all',
            fieldLabel : RM.Translate.Admin.Config.Edit.LanguageFront,
            store : RM.Languages.concat([["cms","CMS"]])
        }]
    }]
});

RM.Pages.Config_Edit_Form_Tabpanel = new Ext.TabPanel({
    id : "rm_pages_config_edit_main_tabpanel",
    xtype : "tabpanel",
    activeTab : 0,
    height: RM.Common.GetPanelHeight(RM.Pages.Config_PageTabOffset),
    bodyBorder: false,    
    items : [
        RM.Pages.Config_Edit_Tab1,
        RM.Pages.Config_Edit_Tab2,
        RM.Pages.Config_Edit_Tab3,
        RM.Pages.Config_Edit_Tab4,
        RM.Pages.Config_Edit_Tab5,
        RM.Pages.Config_Edit_Tab6,
        RM.Pages.Config_Edit_Tab7
    ]
});

RM.Pages.Config_Edit_Form = new Ext.FormPanel({
    id : "rm_pages_config_edit_main_form",
    xtype : "form",
    title : RM.Translate.Admin.Config.Edit.Title,
    iconCls: "RM_config_root_icon",
    height: RM.Common.GetPanelHeight(RM.Pages.Config_PageOffset),
    url : RM.Common.AssembleURL({
        controller: 'Config',
        action: 'updateJson'
    }),
    items : [
        RM.Pages.Config_Edit_Form_Tabpanel
    ],
    bbar: new Ext.ux.StatusBar({
        id: 'rm_pages_config_edit_statusbar',
        items: []
    })
});

RM.Pages.Config_Edit_StatusBar = Ext.getCmp('rm_pages_config_edit_statusbar');
RM.Pages.Config_Edit_StatusBar.setStatus({
    text: RM.Translate.Admin.Config.Edit.StatusBarEditing,
    iconCls: 'ok-icon'
});

RM.Pages.Config_Edit = new Ext.Panel({
    id : "rm_pages_config_edit",
    iconCls: "RM_config_root_icon",
    items : [RM.Pages.Config_Edit_Form],
    height: RM.Common.GetPanelHeight(RM.Pages.Config_PageOffset)
});


RM.Main.Pages.push(RM.Pages.Config_Edit);