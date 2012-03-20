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
 * Unit New JS
 * This creates the admin GUI Unit New Unit Creation JS
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

RM.Pages.Functions.units_new_change_language = function(combo, record, index){
    var myMask = new Ext.LoadMask('rm_pages_units_new_tab', {msg:RM.Translate.Common.PleaseWait});
    myMask.show();

    var conn = new Ext.data.Connection();
    var request = {
        url: RM.Common.AssembleURL({
            controller : 'Units',
            action: 'newjson',
            parameters : [{
                name : 'iso',
                value : record.data.field1
            }]
        }),
        method: 'POST',
        success: function(responseObject) {
           RM.Pages.Reservations_StatusBar.setStatus({
                text: RM.Translate.Admin.Units.New.LanguageChanged,
                iconCls: 'ok-icon'
            });
            eval("RM.Pages.Functions.Units_newJson("+responseObject.responseText+")");
            myMask.hide();
        },
        failure: function() {
            RM.Pages.Reservations_StatusBar.setStatus({
                text: RM.Translate.Admin.Units.New.CouldNotSwitchLanguage,
                iconCls: 'failed-icon'
            });
           Ext.MessageBox.alert(RM.Translate.Admin.Units.New.ChangeLanguageFailure);
           myMask.hide();
        }
    };
    conn.request(request);
};


RM.Pages.Functions.Units_New_Save = function(){
    RM.Pages.Units_New_Tab1.getForm().submit({
        params: {
            'new_unit[iso]' : Ext.getCmp('new_units_iso').value,
             color:  Ext.getCmp('new_unit[color]').value
        },
        success: function(form, action) {            
            Ext.getCmp('rm_main_tree_menu').root.reload();
            RM.Pages.Reservations_StatusBar.setStatus({
                text: RM.Translate.Admin.Units.New.Saved,
                iconCls: 'ok-icon'
            });
            var newUnitID = action.result.id;
            RM.Pages.Functions.Units_Edit_Unit_Request(newUnitID);            
        },
        waitMsg: RM.Translate.Common.Saving,
        waitTitle: RM.Translate.Common.PleaseWait
    });
};

RM.Pages.Functions.Units_New_Cancel = function(){
   RM.Pages.Functions.Units_ListJson();
};

RM.Pages.Units_New_Toolbar = {
    xtype : "panel",
    id : "rm_pages_units_new_toolbar",
    bodyBorder : false,
    html : RM.Common.getToolbar([
        {image: RM.BaseLargeImageURL+"save.gif", label: RM.Translate.Common.Save, link: "RM.Pages.Functions.Units_New_Save()"},
        {image: RM.BaseLargeImageURL+"cancel.gif", label: RM.Translate.Common.Cancel, link: "RM.Pages.Functions.Units_New_Cancel()"}
    ])
};
RM.Toolbars.push(RM.Pages.Units_New_Toolbar);

RM.Pages.Functions.Units_newJson = function (responseObject) {

    if (responseObject.success === false){
        Ext.Msg.show({
            msg: responseObject.error,
            minWidth: 200,
            maxWidth: 500,
            buttons: Ext.Msg.OK,
            icon: Ext.MessageBox.INFO
        });
        return;
    }
    
    RM.Pages.Units_New.on('rm_pages_units_new_tab', function (page, currentTab){
        currentTab.doLayout();
    });

    Ext.getCmp('toolbar_panel').layout.setActiveItem('rm_pages_units_new_toolbar');

    var languageCombobox = Ext.getCmp('new_units_iso');
    languageCombobox.on('select', RM.Pages.Functions.units_new_change_language);
    languageCombobox.setValue(responseObject.language);

    for (i = 0; i < responseObject.fields.length; i++){
        field = responseObject.fields[i];

        field.view.id = 'new_' + field.view.id;
        field.view.name = 'new_' + field.view.name;
        if (field.view.hiddenName) {
            field.view.hiddenName = 'new_' + field.view.hiddenName;
        }
        field.parent_id = 'units_new_' + field.parent_id;

        RM.Common.RemoveOldFields(field.view.id);

        eval("Ext.getCmp(field.parent_id).add(new "+field.class_name+"(field.view))");
    }

    Ext.getCmp('new_unit[type_id]').setValue('1'); //DEFAULT unit type    

    Ext.getCmp('new_unit_published').store = RM.Pages.Units_Published;
    Ext.getCmp('new_unit_published').setValue(0);

    Ext.getCmp('rm_pages_units_new_main_form').syncSize();
    Ext.getCmp('rm_pages_units_new').doLayout(false,true);
    
    Ext.getCmp('content-panel').layout.setActiveItem('rm_pages_units_new');

    Ext.getCmp("units_new_tab_1").doLayout(false,true); // required to refresh htmleditor
};

RM.Pages.Units_New_Tab1_Panel = {
    xtype : "panel",
    id : "units_new_tab_1",
    bodyStyle : "padding:10px",
    layout: 'form',
    items : []
};

RM.Pages.Units_New_Tab1 = new Ext.FormPanel({
    id: "rm_pages_units_new_main_form",
    title: RM.Translate.Admin.Units.New.General,
    xtype: "form",
    url: RM.Common.AssembleURL({
        controller: 'Units',
        action: 'insertjson'
    }),
    frame: true,
    items : [
        RM.Pages.Units_New_Tab1_Panel
    ],
    autoScroll: true
});


RM.Pages.Units_New_TabPanel = new Ext.TabPanel({
    id: "rm_pages_units_new_tab",
    activeTab: 0,
    height: RM.Common.GetPanelHeight(112),
    items: [
        RM.Pages.Units_New_Tab1
    ],
    bbar: new Ext.ux.StatusBar({
        id: 'rm_pages_units_new_statusbar',
        items: []
    })
});

RM.Pages.Reservations_StatusBar = Ext.getCmp('rm_pages_units_new_statusbar');
RM.Pages.Reservations_StatusBar.setStatus({
    //text: RM.Translate.Admin.Units.New.Status,
    iconCls: 'ok-icon'
});


RM.Pages.Units_New_Language_Combo = new Ext.form.ComboBox({
    id : "new_units_iso",
    hiddenName : "new_unit[iso]",
    xtype : "combo",
    cls: 'RM_language_selection_combo',
    typeAhead: true,
    fieldLabel: RM.Translate.Admin.Units.New.UnitLanguage,
    forceSelection: true,
    triggerAction: 'all',
    selectOnFocus: true,
    store: RM.Languages,
    bodyBorder : false,
    frame : false,
    width: 180
});


RM.Pages.Units_New_Language = new Ext.Panel({
    id : "rm_pages_units_new_language",
    layout: 'form',
    bodyBorder : false,
    frame : false,
    items : [
        RM.Pages.Units_New_Language_Combo
    ]
});

RM.Pages.Units_New_Title = {
    xtype : "panel",
    id : "rm_pages_units_new_title",
    bodyBorder : false,
    frame : false,
    html : "<span class='RM_Title_Language_Bar_img'><img src='"+RM.BaseMenuImageURL+"unit.png'></span>&nbsp;<span class='RM_Title_Language_Bar_text'>"+RM.Translate.Admin.Units.New.Title+"</span>"
};

RM.Pages.Units_New_Language_Form = new Ext.FormPanel({
    id : "rm_pages_units_new_language_selection",
    xtype : "form",
    layout: 'column',
    frame : false,
    baseCls: "x-panel-header",
    url : RM.Common.AssembleURL({
        controller: 'Units',
        action: 'insertjson'
    }),
    items : [
        RM.Pages.Units_New_Title,
        RM.Pages.Units_New_Language
    ]
});

RM.Pages.Units_New = new Ext.Panel({
    id : "rm_pages_units_new",
    items : [
        RM.Pages.Units_New_Language_Form,
        RM.Pages.Units_New_TabPanel
    ]
});

RM.Main.Pages.push(RM.Pages.Units_New);