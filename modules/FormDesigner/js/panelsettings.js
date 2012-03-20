/*
* Form Designer Panel settings Page JS
*
* JSLint.com Check: 18/03/2011
*/
RM.Pages.Functions.Formdesigner_SettingsWindow = function(panelId, panelName, panelSettings){


    // combo values
    var combodata = new Ext.data.JsonStore({
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
        data: RM.Translate.Common.JSON.AlignmentSettings
    });

    // alignment selection combo
    var alignmentSelection = new Ext.form.ComboBox({
        id : "rm_formdesigner_panelsettings_alignment_id",
        name : "rm_formdesigner_panelsettings_alignment_name",
        hiddenName : "rm_formdesigner_panelsettings_alignment_hidden",
        fieldLabel : RM.Translate.Admin.Formdesigner.Settings.PanelAlignment,
        store : combodata,
        forceSelection: true,
        mode: "local",
        triggerAction: 'all',
        selectOnFocus: true,
        valueField: 'value',
        displayField: 'text',
        allowBlank: false,
        emptyText: RM.Translate.Common.PleaseSelect,
        width : 70
    });

    // form panel
    var form = new Ext.FormPanel({
        id : "rm_formdesigner_panelsettings_main_form",
        bodyStyle : "padding:10px;",
        url : RM.Common.AssembleURL({
            controller: 'FormDesigner',
            action: 'updatepanelsettingsJson'
        }),
        items : [
            alignmentSelection
        ]
    });

    // window
    var settingsWindow = new Ext.Window({
        title: panelName + " " + RM.Translate.Admin.Formdesigner.Settings.PanelSettings,
        buttons: [{
            text: RM.Translate.Common.Cancel,
            handler: function(){
                settingsWindow.close();
            }
        },{
            text: RM.Translate.Common.Save,
            handler: function(){
                 form.getForm().submit({
                    params: {
                        'form_id' : panelId
                    },
                    success: function(form, action) {
                        settingsWindow.close();
                    },
                    waitMsg: RM.Translate.Common.Saving,
                    waitTitle: RM.Translate.Common.PleaseWait
                });
            }
        }],
        renderTo: "content-panel",
        layout: 'fit',
        width: 300,
        height: 200,
        plain: true,
        autoDestroy: true,
        items: [
            form
        ]
    });

    // load data...
    if (panelSettings!==""){
        var settings = Ext.util.JSON.decode(panelSettings);
        alignmentSelection.setValue(settings.align);
        //widthSelection.setValue(settings.width);
    }


    settingsWindow.show();
    
};

