/*
* Notification Edit JS
*
* JSLint.com Check: 18/03/2011
*/
RM.Pages.Functions.EmailNotifications_EditJson = function (responseObject) {

    var formValues = Ext.util.JSON.decode(responseObject.responseText);
    /*
     * id = formValues.id
     * message = formValues.message
     * language = formValues.language
     */
    var languageCombobox = Ext.getCmp('emailnotifications_iso');
    languageCombobox.on('select', RM.Pages.Functions.emailnotifications_edit_change_language);
    languageCombobox.setValue(formValues.language);

    Ext.getCmp('rm_pages_emailnotifications_edit_message').setValue(formValues.message);
    Ext.getCmp('rm_pages_emailnotifications_edit_id').setValue(formValues.id);
    
    Ext.getCmp('toolbar_panel').layout.setActiveItem('rm_pages_emailnotifications_edit_toolbar');
    Ext.getCmp('content-panel').layout.setActiveItem('rm_pages_emailnotifications_edit');

    // load help
    RM.Help.Load('Admin.EmailNotifications.Help.Main');
};

RM.Pages.Functions.emailnotifications_edit_change_language = function(combo, record, index){
    var myMask = new Ext.LoadMask('content-panel', {msg:RM.Translate.Common.PleaseWait});
    myMask.show();

    var conn = new Ext.data.Connection();
    var request = {
        url: RM.Common.AssembleURL({
            controller : 'EmailNotifications',
            action: 'editjson',
            parameters : [{
                name : 'iso',
                value : record.data.field1
            },{
                name : 'id',
                value : Ext.getCmp("rm_pages_emailnotifications_edit_id").getValue()
            }]
        }),
        method: 'POST',
        success: function(responseObject) {
           RM.Pages.Functions.EmailNotifications_EditJson(responseObject);
           myMask.hide();
        },
        failure: function() {
           Ext.MessageBox.alert(RM.Translate.Admin.Units.Edit.ChangeLanguageFailure);
        }
    };
    conn.request(request);
};

RM.Pages.Functions.emailnotifications_edit_save = function(){
    RM.Pages.Emailnotifications_Edit_Formpanel.getForm().submit({
        params: {
            'id' : Ext.getCmp("rm_pages_emailnotifications_edit_id").getValue(),
            'iso' : Ext.getCmp('emailnotifications_iso').getValue(),
            'message' : Ext.getDom('rm_pages_emailnotifications_edit_message').value
        },
        waitMsg: RM.Translate.Common.Saving,
        waitTitle: RM.Translate.Common.PleaseWait
    });
};

RM.Pages.Emailnotifications_Edit_Toolbar = {
    xtype : "panel",
    id : "rm_pages_emailnotifications_edit_toolbar",
    bodyBorder : false,
        html : RM.Common.getToolbar([
            {image: RM.BaseLargeImageURL+"back.gif",label: RM.Translate.Common.Back, link: "RM.Pages.Functions.Notifications_ListJson_Request()"},
            {image: RM.BaseLargeImageURL+"save.gif",label: RM.Translate.Common.Save, link: "RM.Pages.Functions.emailnotifications_edit_save()"}
        ])
};
RM.Toolbars.push(RM.Pages.Emailnotifications_Edit_Toolbar);

RM.Pages.Emailnotifications_Edit_Language_Combo = new Ext.form.ComboBox({
    id : "emailnotifications_iso",
    hiddenName : "emailnotifications[iso]",
    xtype : "combo",
    cls: 'RM_language_selection_combo',
    typeAhead: true,
    fieldLabel: RM.Translate.Admin.Emailnotifications.Edit.Language,
    forceSelection: true,
    triggerAction: 'all',
    selectOnFocus: true,
    store: RM.Languages,
    bodyBorder : false,
    width: 180,
    frame : false
});

RM.Pages.Emailnotifications_Edit_Language = new Ext.Panel({
    id : "rm_pages_emailnotification_edit_language",
    layout: 'form',
    frame : false,
    bodyBorder : false,
    items : [
        RM.Pages.Emailnotifications_Edit_Language_Combo
    ]
});

RM.Pages.Emailnotifications_Edit_Title = new Ext.Panel({
    xtype : "panel",
    id : "rm_pages_emailnotifications_edit_title",
    bodyBorder : false,
    frame : false,
    html : "<span class='RM_Title_Language_Bar_img'><img src='"+RM.BaseMenuImageURL+"notification_edit.png'></span>&nbsp;<span class='RM_Title_Language_Bar_text'>"+RM.Translate.Admin.Emailnotifications.Edit.Title+"</span>"
});

RM.Pages.Emailnotifications_Edit_Language_Form = new Ext.FormPanel({
    id : "rm_pages_emailnotifications_edit_language_selection",
    xtype : "form",
    layout: 'column',
    url : RM.Common.AssembleURL({
        controller: 'EmailNotifications',
        action: 'updatejson'
    }),
    frame : false,
    baseCls: "x-panel-header",
    items : [
        RM.Pages.Emailnotifications_Edit_Title,
        RM.Pages.Emailnotifications_Edit_Language
    ]
});

RM.Pages.Emailnotifications_Edit_Formpanel = new Ext.FormPanel({
    id : "rm_pages_emailnotifications_edit_main_form",
    title : RM.Translate.Admin.Emailnotifications.Edit.Messages,
    xtype : "form",
    labelWidth : 200,
    height: RM.Common.GetPanelHeight(146),
    autoScroll: true,
    url : RM.Common.AssembleURL({
        controller: 'EmailNotifications',
        action: 'updatejson'
    }),
    frame : true,
    items : [
        new Ext.rm.HtmlEditor({
            id: "rm_pages_emailnotifications_edit_message",
            name: "rm_pages_emailnotifications_edit_message_name",
            width: 600,
            height: RM.Common.GetPanelHeight(272),
            plugins: [new Ext.rm.HtmlEditor_SpecialCharacters()]
        }),
        new Ext.form.Hidden({
            id: "rm_pages_emailnotifications_edit_id"
        })
    ]
});

RM.Pages.Emailnotifications_Edit = new Ext.Panel({
    id : "rm_pages_emailnotifications_edit",
    items : [
        RM.Pages.Emailnotifications_Edit_Language_Form,
        RM.Pages.Emailnotifications_Edit_Formpanel
    ]
});
RM.Main.Pages.push(RM.Pages.Emailnotifications_Edit);