// THIS FILE SHOULD NOT BE COMPRESSED!

/**
* PayPal Edit settings Page
*
* This page will render the GUI and the PayPal settings feilds. This page is
* commented extensively as the paypal code is used to base many payment modules
*
* JSLint: 18/03/2011
*
* @access   public
* @author   Rob, Valentin
* @copyright    ResMania 2009 all rights reserved.
* @version  1.0
* @link http://developer.resmania.com/docs:creating_resmania_plugins
* @since    05-2009
*/

/**
* RM.Pages.Plugin_PayPal_Config_Toolbar
*
* Setup the PayPal Edit Page toolbar Buttons. This function will render the toolbar
* to the upper right of the GUI. For this page we just need one button to save settings.
*
* @return   ExtJS Panel
*/
RM.Pages.Plugin_PayPal_Config_Toolbar = {
    xtype : "panel",
    id : "rm_pages_plugins_paypal_toolbar",
    bodyBorder : false,
    html : RM.Common.getToolbar([{image: RM.BaseLargeImageURL+"save.gif", label: RM.Translate.Admin.PayPal.Main.Save, link: "RM.Pages.Functions.plugins_paypal_save()"}])
    // RM.Common.getToolbar usage: image, label, JS function to link to
};
RM.Toolbars.push(RM.Pages.Plugin_PayPal_Config_Toolbar);

/**
* RM.Pages.Functions.plugins_paypal_save
*
* This is the JS action called on the save button click. It submit's the form
* RM.Pages.Plugin_PayPal_Panel with params. This is handled by the controller:
* /RM/plugins/PayPal/controllers/RM/admin/PayPalController.php method: updateJsonAction
* the controller will return true or false and this will in turn call success or
* failure of this function.
*/
RM.Pages.Functions.plugins_paypal_save = function(){
    RM.Pages.Plugin_PayPal_Panel.getForm().submit({
        params: {
            'id' : Ext.getDom('rm_pages_plugins_paypal_id').value,
            'account' : Ext.getDom('rm_pages_plugins_paypal_account').value,
            'sandbox' : Ext.getDom('rm_pages_plugins[paypal_sandbox]').value,
            'default' : Ext.getDom('rm_pages_plugins[paypal_default]').value
        },
        success: function(form, action) {
            RM.Pages.Units_StatusBar = Ext.getCmp('rm_pages_plugins_paypal_statusbar');
            RM.Pages.Units_StatusBar.setStatus({
                text: RM.Translate.Admin.PayPal.Main.SaveOK,
                iconCls: 'ok-icon'
            });
        },
        failure: function() {
            RM.Pages.Units_StatusBar = Ext.getCmp('rm_pages_plugins_paypal_statusbar');
            RM.Pages.Units_StatusBar.setStatus({
                text: RM.Translate.Admin.PayPal.Main.SaveFailed,
                iconCls: 'failed-icon'
            });
        },
        waitMsg: RM.Translate.Common.Saving,
        waitTitle: RM.Translate.Common.PleaseWait
    });
};

/*
 * The following section defines the form item. For this plugin we need just four
 * items: the fieldset, the hidden ID feild, the account textfield and a checkbox
 * to switch test mode (sandbox) on or off.
 */

// the hidden ID feild use to connect this to the DB record.
RM.Pages.Plugin_PayPal_ID_Field = new Ext.form.Hidden({
    id : "rm_pages_plugins_paypal_id"
});

// the account text input feild
RM.Pages.Plugin_PayPal_Account_Field = new Ext.form.TextField({
    fieldLabel: RM.Translate.Admin.PayPal.Main.Account,
    id : "rm_pages_plugins_paypal_account",
    width : 200
});

RM.Pages.Plugin_PayPal_GetAccountText = new Ext.Panel({
    fieldLabel: '',
    html: '<div id="rm_pages_admin_form_textlink"><a href="https://www.paypal.com/mrb/pal=AAPRE6YT2E6QA" target="_blank">'+RM.Translate.Admin.PayPal.Main.GetAccount+'</a></div>',
    bodyBorder : false
});

// the sandbox/test account checkbox
RM.Pages.Plugin_PayPal_SandBox_Selection = {
    id: "rm_pages_plugins[paypal_sandbox]",
    name: "rm_pages_plugins_paypal_sandbox",
    inputValue: "1",
    fieldLabel: RM.Translate.Admin.PayPal.Main.SandBox,
    xtype: "xcheckbox"
};

RM.Pages.Plugin_PayPal_Default_Selection = {
    id: "rm_pages_plugins[paypal_default]",
    name: "rm_pages_plugins_paypal_default",
    inputValue: "1",
    fieldLabel: RM.Translate.Admin.PayPal.Main.Default,
    xtype: "xcheckbox"
};

// the feildset to make it look pretty
// notice 'items' contains all the above form items
RM.Pages.Plugin_PayPal_Fieldset = new Ext.form.FieldSet({
    id: "rm_pages_plugins_paypal_fieldset",
    title: RM.Translate.Admin.PayPal.Main.Settings,
    autoHeight: true,
    layout: 'form',
    bodyBorder : false,
    items: [
        RM.Pages.Plugin_PayPal_ID_Field,
        RM.Pages.Plugin_PayPal_Account_Field,
        RM.Pages.Plugin_PayPal_GetAccountText,
        RM.Pages.Plugin_PayPal_SandBox_Selection,
        RM.Pages.Plugin_PayPal_Default_Selection
    ]
});

/*
 * This function loads the settings that are passed from the controller (PayPalController.php)
 * and set's the values on the form items. It then renders the GUI and set's this
 * page as the active page.
 */
RM.Pages.Functions.PayPal_ConfigJson = function (responseObject) {

    // create a mask assigned to rm_pages_plugins_paypal_main_form
    var myMask = new Ext.LoadMask('rm_pages_plugins_paypal_main_form', {
        msg: RM.Translate.Common.PleaseWait,
        msgCls: "RM_Loading_Mask_Msg_Hidden" // hide the msg
    });
    // enable the mask while the data and form are loaded
    myMask.show();

    // the data is contained in the responseObject
    var settings = responseObject.settings[0];

    // set the values of the form items
    Ext.getCmp("rm_pages_plugins_paypal_id").setValue(settings.id);
    Ext.getCmp("rm_pages_plugins_paypal_account").setValue(settings.account);
    Ext.getCmp("rm_pages_plugins[paypal_sandbox]").setValue(settings.sandbox);
    Ext.getCmp("rm_pages_plugins[paypal_default]").setValue(settings.defaultplugin);

    // just do so Extjs stuff to ensure the GUI is rendered and visable
    Ext.getCmp('rm_pages_plugins_paypal_main_form').render();
    Ext.getCmp('rm_pages_plugins_paypal_main_form').doLayout();

    // make the toolbar visable
    Ext.getCmp('toolbar_panel').layout.setActiveItem('rm_pages_plugins_paypal_toolbar');
    // make the main form visable
    Ext.getCmp('content-panel').layout.setActiveItem('rm_pages_plugins_paypal_main_form');

    // hide the mask
    myMask.hide();

    RM.Help.Load('Admin.PayPal.Edit.Main');

};

/*
 * This is the main form panel. The ExtJS type is FormPanel this defines this
 * as a form and can be submitted. The url is the destination where this will be
 * submitted to. This form will be submitted to:
 * /RM/plugins/PayPal/controllers/RM/admin/PayPalController.php method: updateJsonAction
 * based ont he controller = paypal and action = updateJson
 */
RM.Pages.Plugin_PayPal_Panel = new Ext.FormPanel({
    id : 'rm_pages_plugins_paypal_main_form',
    title: RM.Translate.Admin.PayPal.Main.Title,
    bodyStyle : "padding:10px;",
    items : [
        RM.Pages.Plugin_PayPal_Fieldset
    ],
    url : RM.Common.AssembleURL({
        controller: 'PayPal',
        action: 'updateJson'
    }),
    bbar: new Ext.ux.StatusBar({
        id: 'rm_pages_plugins_paypal_statusbar',
        items: []
    })
});

// required although I can't remember why right now :-)
RM.Main.Pages.push(RM.Pages.Plugin_PayPal_Panel);