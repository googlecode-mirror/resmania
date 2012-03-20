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
 * Pages Edit JS
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
RM.Pages.Functions.Pages_Edit_Save = function(){
    var myMask = new Ext.LoadMask('rm_pages_pages_edit', {msg:RM.Translate.Common.PleaseWait});
    myMask.show();

    var conn = new Ext.data.Connection();
    var request = {
        url: RM.Common.AssembleURL({
            controller : 'Pages',
            action: 'updateJson'
        }),
        method: 'POST',
        params: {
            id: RM.Pages.Functions.Pages_Edit_ID,
            content: Ext.getCmp('rm_pages_pages_edit_html').getValue()
        },
        success: function(responseObject) {
            var jsonObject = RM.Common.JSON.decode(responseObject.responseText, true);            
            if (jsonObject.success){
                RM.Pages.Pages_Edit_StatusBar.setStatus({
                    iconCls: 'ok-icon',
                    text: RM.Translate.Admin.Pages.Edit.EditSuccess
                });
            } else {
                Ext.Msg.alert(RM.Translate.Common.Error, jsonObject.error);
                RM.Pages.Pages_Edit_StatusBar.setStatus({
                    iconCls: 'ok-icon',
                    text: jsonObject.error
                });
            }
            myMask.hide();
        },
        failure: function(){
            RM.Pages.Pages_Edit_StatusBar.setStatus({
                iconCls: 'failed-icon',
                text: RM.Translate.Admin.System.Language.EditFailed
            });
            myMask.hide();
        }
    };
    conn.request(request);
};

RM.Pages.Functions.Pages_Edit_ID = '';
RM.Pages.Functions.Pages_EditJson = function (responseObject) {
    RM.Pages.Functions.Pages_Edit_ID = responseObject.id;
    Ext.getCmp('rm_pages_pages_edit_html').setValue(unescape(responseObject.content));
    Ext.getCmp('toolbar_panel').layout.setActiveItem('rm_pages_pages_edit_toolbar');
    Ext.getCmp('content-panel').layout.setActiveItem('rm_pages_pages_edit');

    // for some reason this needs to be called twice to run.
    if (RM.Common.Editor == "html"){
        Ext.getCmp('rm_pages_pages_edit_html').toggleSourceEdit(true);
        Ext.getCmp('rm_pages_pages_edit_html').toggleSourceEdit(true);
    }

    RM.Help.Load('Admin.Pages.Templates.Invoice');
};

RM.Pages.Pages_Edit_Toolbar = {
    xtype : "panel",
    id : "rm_pages_pages_edit_toolbar",
    bodyBorder : false,
    html : RM.Common.getToolbar([{
        image: RM.BaseLargeImageURL+"save.gif", label: RM.Translate.Common.Save, link: "RM.Pages.Functions.Pages_Edit_Save()"
    }])
};
RM.Toolbars.push(RM.Pages.Pages_Edit_Toolbar);

if (RM.Common.Editor == "html"){
    RM.Pages.Pages_Edit_Editor = {
        xtype: 'rm_htmleditor',
        hideLabel: true,
        id: 'rm_pages_pages_edit_html',
        name: 'rm_pages_pages_edit_html',
        height: RM.Common.GetPanelHeight(244),
        anchor:'90%',
        plugins: [new Ext.rm.HtmlEditor_SpecialCharacters()]
    };
} else {
    RM.Pages.Pages_Edit_Editor = {
        xtype: 'textarea',
        hideLabel: true,
        id: 'rm_pages_pages_edit_html',
        name: 'rm_pages_pages_edit_html',
        height: RM.Common.GetPanelHeight(244),
        anchor:'90%'
    };
}

RM.Pages.Pages_Edit = new Ext.FormPanel({
    id : 'rm_pages_pages_edit',
    title : RM.Translate.Common.Edit,
    iconCls: "RM_units_default_root_icon",
    autoScroll: true,
    bodyStyle : "padding:10px;",
    height: RM.Common.GetPanelHeight(104),
    items : [{
        xtype : "fieldset",
        title: RM.Translate.Admin.Pages.Edit.Content,
        autoHeight: true,
        layout: 'form',
        id : "rm_pages_pages_edit_fieldset",
        autoWidth: true,
        bodyBorder : false,        
        items : [
            RM.Pages.Pages_Edit_Editor
        ]
    }],
    bbar:  new Ext.ux.StatusBar({
        id: 'rm_pages_pages_statusbar',
        items: []
    })
});

RM.Pages.Pages_Edit_StatusBar = Ext.getCmp('rm_pages_pages_statusbar');
RM.Pages.Pages_Edit_StatusBar.setStatus({
    text: RM.Translate.Admin.Pages.Edit.StatusBarEditing,
    iconCls: 'ok-icon'
});
RM.Main.Pages.push(RM.Pages.Pages_Edit);