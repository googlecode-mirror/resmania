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
 * Templates Page JS
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
RM.Pages.Functions.Templates_Edit_Save = function(){
    var myMask = new Ext.LoadMask('rm_pages_templates_edit', {msg:RM.Translate.Common.PleaseWait});
    myMask.show();

    var conn = new Ext.data.Connection();
    var request = {
        url: RM.Common.AssembleURL({
            controller : 'Templates',
            action: 'updateJson'
        }),
        method: 'POST',
        params: {
            id: RM.Pages.Functions.Templates_Edit_ID,
            content: Ext.getCmp('rm_pages_templates_edit_html').getValue(),
            language: Ext.getCmp('edit_templates_iso').getValue()
        },
        success: function(responseObject) {
            var jsonObject = RM.Common.JSON.decode(responseObject.responseText, true);            
            if (jsonObject.success){
                RM.Pages.Templates_Edit_StatusBar.setStatus({
                    iconCls: 'ok-icon',
                    text: RM.Translate.Admin.Pages.Edit.EditSuccess
                });
            } else {
                Ext.Msg.alert(RM.Translate.Common.Error, jsonObject.error);
                RM.Pages.Templates_Edit_StatusBar.setStatus({
                    iconCls: 'ok-icon',
                    text: jsonObject.error
                });
            }
            myMask.hide();
        },
        failure: function(){
            RM.Pages.Templates_Edit_StatusBar.setStatus({
                iconCls: 'failed-icon',
                text: RM.Translate.Admin.System.Language.EditFailed
            });
            myMask.hide();
        }
    };
    conn.request(request);
};

RM.Pages.Functions.Templates_Edit_Change_Language = function(combo, record, index){
    var myMask = new Ext.LoadMask('rm_pages_templates_edit', {msg:RM.Translate.Common.PleaseWait});
    myMask.show();

    var conn = new Ext.data.Connection();
    var request = {
        url: RM.Common.AssembleURL({
            controller : 'Templates',
            action: 'editjson',
            parameters : [{
                name : 'id',
                value : RM.Pages.Functions.Templates_Edit_ID
            }, {
                name : 'language',
                value : record.data.field1
            }]
        }),
        method: 'POST',
        success: function(responseObject) {
           eval("RM.Pages.Functions.Templates_EditJson("+responseObject.responseText+")");
           myMask.hide();
        },
        failure: function() {
           Ext.MessageBox.alert(RM.Translate.Admin.Templates.Edit.ChangeLanguageFailure);
           myMask.hide();
        }
    };
    conn.request(request);
};

RM.Pages.Functions.Templates_Edit_ID = '';
RM.Pages.Functions.Templates_EditJson = function (responseObject) {
    RM.Pages.Functions.Templates_Edit_ID = responseObject.id;

    var languageCombobox = Ext.getCmp('edit_templates_iso');
    languageCombobox.on('select', RM.Pages.Functions.Templates_Edit_Change_Language);
    languageCombobox.setValue(responseObject.language);

    RM.Pages.Functions.Templates_Edit_ID = responseObject.id;
    Ext.getCmp('rm_pages_templates_edit_html').setValue(responseObject.content);
    Ext.getCmp('toolbar_panel').layout.setActiveItem('rm_pages_templates_edit_toolbar');
    Ext.getCmp('content-panel').layout.setActiveItem('rm_pages_templates_edit');

    RM.Pages.Templates_Edit_StatusBar.setStatus({
        iconCls: 'ok-icon',
        text: RM.Translate.Admin.Templates.Edit.StatusBarEditing
    });

    Ext.getCmp('rm_pages_templates_edit_form').syncSize();
    Ext.getCmp('rm_pages_templates_edit_html').syncSize();
};

RM.Pages.Templates_Edit_Toolbar = {
    xtype : "panel",
    id : "rm_pages_templates_edit_toolbar",
    bodyBorder : false,
    html : RM.Common.getToolbar([{
        image: RM.BaseLargeImageURL+"save.gif", label: RM.Translate.Common.Save, link: "RM.Pages.Functions.Templates_Edit_Save()"
    }])
};
RM.Toolbars.push(RM.Pages.Templates_Edit_Toolbar);

RM.Pages.Templates_Edit_Language_Combo = new Ext.form.ComboBox({
    id : "edit_templates_iso",
    hiddenName : "edit_template[iso]",
    xtype : "combo",
    cls: 'RM_language_selection_combo',
    typeAhead: true,
    fieldLabel: RM.Translate.Admin.Templates.Edit.Language,
    forceSelection: true,
    triggerAction: 'all',
    selectOnFocus: true,
    store: RM.Languages,
    bodyBorder : false,
    frame : false,
    width: 180
});

RM.Pages.Templates_Edit_Language = new Ext.Panel({
    id : "rm_pages_templates_edit_language",
    layout: 'form',
    frame : false,
    bodyBorder : false,
    items : [
        RM.Pages.Templates_Edit_Language_Combo
    ]
});

RM.Pages.Templates_Edit_Title = {
    xtype : "panel",
    id : "rm_pages_templates_edit_title",
    bodyBorder : false,
    frame : false,
    html : "<span class='RM_Title_Language_Bar_img'><img src='"+RM.BaseMenuImageURL+"templates.png'></span>&nbsp;<span class='RM_Title_Language_Bar_text'>"+RM.Translate.Admin.Templates.Edit.Title+"</span>"
};

RM.Pages.Templates_Edit_Language_Form = new Ext.FormPanel({
    id : "rm_pages_templates_edit_language_selection",
    xtype : "form",
    layout: 'column',
    frame : false,
    baseCls: "x-panel-header",
    items : [
        RM.Pages.Templates_Edit_Title,
        RM.Pages.Templates_Edit_Language
    ]
});

if (RM.Common.Editor == "html"){
    RM.Pages.Templates_Edit_Editor = {
        xtype: 'rm_htmleditor',
        hideLabel: true,
        id: 'rm_pages_templates_edit_html',
        name: 'rm_pages_templates_edit_html',
        height: RM.Common.GetPanelHeight(180),
        anchor:'98%',
        plugins: [new Ext.rm.HtmlEditor_SpecialCharacters()]
    };
} else {
    RM.Pages.Templates_Edit_Editor = {
        xtype: 'textarea',
        hideLabel: true,
        id: 'rm_pages_templates_edit_html',
        name: 'rm_pages_templates_edit_html',
        height: RM.Common.GetPanelHeight(180),
        anchor:'98%'
    };
}

RM.Pages.Templates_Edit = new Ext.Panel({
    id : 'rm_pages_templates_edit',    
    iconCls: "RM_templates_default_root_icon",
    height: RM.Common.GetPanelHeight(80),    
    items : [
        RM.Pages.Templates_Edit_Language_Form, 
        new Ext.FormPanel({
            autoHeight: true,
            autoWidth: true,
            autoScroll: true,
            layout: 'fit',
            id : "rm_pages_templates_edit_form",
            bodyBorder : false,
            frame: false,
            bodyStyle : "padding:10px;",
            items : [
                RM.Pages.Templates_Edit_Editor
            ]
        })
    ],
    bbar:  new Ext.ux.StatusBar({
        id: 'rm_pages_templates_statusbar',
        items: []
    })
});

RM.Pages.Templates_Edit_StatusBar = Ext.getCmp('rm_pages_templates_statusbar');
RM.Pages.Templates_Edit_StatusBar.setStatus({
    text: RM.Translate.Admin.Templates.Edit.StatusBarEditing,
    iconCls: 'ok-icon'
});
RM.Main.Pages.push(RM.Pages.Templates_Edit);