/*
* Form Designer Edit Page JS
*
* JSLint.com Check: 18/03/2011
*/
// Helper class for organizing the buttons
RM_FormDesignerButtonPanel = Ext.extend(Ext.Panel, {
    layout: 'table',
    defaultType: 'button',
    baseCls: 'x-plain',
    cls: 'btn-panel',
    menu: undefined,
    split: false,

    layoutConfig: {
        columns:3 //Default value
    },

    constructor: function(buttons){
        this.layoutConfig.columns = buttons.length;
        // apply test configs
        var i = 0;var b;for(i, b; b = buttons[i]; i++){
            b.menu = this.menu;
            b.enableToggle = this.enableToggle;
            b.split = this.split;
            b.arrowAlign = this.arrowAlign;
        }
        var items = buttons;
        RM_FormDesignerButtonPanel.superclass.constructor.call(this, {
            items: items,
            autoScroll: true
        });
    }
});

RM.Pages.Functions.Formdesigner_ToggleButton = function(panelXType, hide){
    var button_id = RM_Form_Naming_Manager.generateButtonID(panelXType);
    
    Ext.getCmp(button_id).setDisabled(hide);
    return true;
};

RM.Pages.Functions.Formdesigner_PortalChangeColumns = function(columnNumber){
    Ext.getCmp('formdesigner-page').items.each(function(panel){
        panel.items.each(function(item){
            RM.Pages.Functions.Formdesigner_ToggleButton(item.initialConfig.xtype, false);
            return true;
        });
    });

    Ext.getCmp('formdesigner-page').removeAll(true);
        
    var i = 0;for (i; i < columnNumber; i++){
        var JSONConfig = {
            columnWidth: 1/columnNumber,
            items: [],
            listeners: {
                'remove': function(component, panel){
                    return RM.Pages.Functions.Formdesigner_ToggleButton(panel.initialConfig.xtype, false);
                },
                'add': function(component, panel){
                    return RM.Pages.Functions.Formdesigner_ToggleButton(panel.initialConfig.xtype, true);
                }
            }
        };
        if (i === 0) {
            JSONConfig.id = 'formdesigner-page-first';
        }
        Ext.getCmp('formdesigner-page').items.add(new Ext.Panel(JSONConfig));
    }
    
    Ext.getCmp('formdesigner-page').doLayout();
};

RM.Pages.Formdesigner_Home_Menu = new Ext.Panel ({
    region:'north',
    id:'home-menu',
    split:false,
    height: 65,
    header: false,
    layout: "form",
    autoScroll: true,
    items: [{
        xtype: 'panel',
        layout:'table',
        layoutConfig: {columns:5},
        border: false,
        autoScroll: true,
        iconCls:'settings',
        items: [{
            xtype: 'panel',
            border: false,
            autoScroll: true,
            iconCls:'settings',
            width: 200,
            layout: 'form',
            items: [{
                id : "formdesigner_column_number_panel",
                items : [],
                xtype : 'panel',
                border: false,
                layout: 'form'
            }]
            },{
                id: "rm_formdesigner_colwidth1_panel",
                xtype: 'panel',
                border: false,
                width: 200,
                layout: 'form',
                items: [{
                    xtype: "textfield",
                    id: "rm_formdesigner_colwidth1",
                    name: "rm_formdesigner_colwidth1",
                    fieldLabel: RM.Translate.Admin.Formdesigner.Edit.ColumnWidth1,
                    width: 30,
                    labelWidth: 120
                }]
            },{
                id: "rm_formdesigner_colwidth2_panel",
                xtype: 'panel',
                border: false,
                width: 200,
                layout: 'form',
                items: [{
                        xtype: "textfield",
                    id: "rm_formdesigner_colwidth2",
                    name: "rm_formdesigner_colwidth2",
                    fieldLabel: RM.Translate.Admin.Formdesigner.Edit.ColumnWidth2,
                    width: 30,
                    labelWidth: 120
                }]
            },{
                id: "rm_formdesigner_colwidth3_panel",
                xtype: 'panel',
                border: false,
                width: 200,
                layout: 'form',
                items: [{
                    xtype: "textfield",
                    id: "rm_formdesigner_colwidth3",
                    name: "rm_formdesigner_colwidth3",
                    fieldLabel: RM.Translate.Admin.Formdesigner.Edit.ColumnWidth3,
                    width: 30,
                    labelWidth: 120
                }]
            },{
                xtype: 'panel',
                border: false,
                html: RM.Translate.Admin.Formdesigner.Edit.ColumnHelp
            },{
                id: 'rm_formdesigner_buttons',
                colspan: 5,
                xtype: 'panel',
                border: false,
                autoScroll: true,
                iconCls:'settings',
                items: []
            }]
    }]
});

RM.Pages.Formdesigner_Home_Panel = new Ext.ux.Portal({
    id: 'formdesigner-page',
    xtype: 'portal',
    stateful: true,
    region: 'center',
    layout: 'column',
    items: []
});

RM.Pages.Formdesigner_Home_Main = new Ext.Panel ({
    id: 'formdesigner-main',
    layout:'border',    
    title: RM.Translate.Admin.Formdesigner.Edit.FormDesignerTitle,
    items: [
        RM.Pages.Formdesigner_Home_Menu,
        RM.Pages.Formdesigner_Home_Panel
    ]
});

RM.Pages.Functions.Formdesigner_Edit_Save = function(){    
    var state = Ext.getCmp('formdesigner-page').getState();    
    var encodedState = Ext.util.JSON.encode(state);
    
    var myMask = new Ext.LoadMask('content-panel', {msg:RM.Translate.Common.PleaseWait});
    myMask.show();

    var conn = new Ext.data.Connection();
    var request = {
        url: RM.Common.AssembleURL({
            controller : 'FormDesigner',
            action: 'savestatejson'
        }),
        params : {
            formID : RM.Pages.Functions.Formdesigner_FormID,
            unitTypeID : RM.Pages.Functions.Formdesigner_UnitTypeID,
            state : encodedState,
            columns : Ext.getCmp('formdesigner_column_number').getValue(),
            column1width: Ext.getCmp('rm_formdesigner_colwidth1').getValue(),
            column2width: Ext.getCmp('rm_formdesigner_colwidth2').getValue(),
            column3width: Ext.getCmp('rm_formdesigner_colwidth3').getValue()
        },
        method: 'POST',
        success: function(responseObject) {
            Ext.getCmp('formdesigner-page').doLayout();
            myMask.hide();            
        },
        failure: function() {            
            myMask.hide();
        }
    };
    conn.request(request);
};

RM.Pages.Formdesigner_Edit_Toolbar = {
    xtype : "panel",
    id : "rm_pages_formdesigner_edit_toolbar",
    bodyBorder : false,
    html : RM.Common.getToolbar([{image: RM.BaseLargeImageURL+"save.gif", label: RM.Translate.Common.Save, link: "RM.Pages.Functions.Formdesigner_Edit_Save()"}])
};
RM.Toolbars.push(RM.Pages.Formdesigner_Edit_Toolbar);

RM.Pages.Functions.Formdesigner_LoadPanelsState = function(){
    if (document.getElementById('rm_formdesigner_data_iframe').contentWindow.document.getElementById('rm_formdesigner_test') === null){
        RM.Pages.Formdesigner_Mask.hide();

        Ext.MessageBox.confirm(RM.Translate.Admin.Formdesigner.Edit.ErrorTitle, RM.Translate.Admin.Formdesigner.Edit.Error, function(buttonID){
            if (buttonID !== 'yes') {
                alert(RM.Translate.Admin.Formdesigner.Edit.TroubleShooter + " " + document.getElementById('rm_formdesigner_data_iframe').src);
                return;
            }
            //RM.Pages.Functions.Languages_List_Action(selected, 'deletejson', true);
            RM.Pages.Functions.FormDesigner_controlpanelJson();
        });
        Ext.MessageBox.getDialog().setHeight(150);
      
        return;
    }

    RM.Pages.Functions.Formdesigner_InitPanels();

    var responseObject = RM.Pages.Formdesigner_Edit_ResponseObject;
    RM.Pages.Functions.Formdesigner_FormID = responseObject.formID;
    RM.Pages.Functions.Formdesigner_UnitTypeID = responseObject.unitTypeID;
    Ext.getCmp('formdesigner-main').setTitle(RM.Translate.Admin.Formdesigner.Edit.FormDesignerTitle + ' : ' + responseObject.unitTypeName + ' - ' + responseObject.formName);

    RM.Pages.Functions.Formdesigner_PortalChangeColumns(responseObject.columns);

    var records = [];
    var i = 1;for (i; i <= responseObject.max_columns; i++){
        records.push([i, i]);
    }

    var f = 0;for (f; f < Ext.getCmp('formdesigner_column_number_panel').items.items.length; f++){
        Ext.getCmp('formdesigner_column_number_panel').remove(f);
    }
    Ext.getCmp('formdesigner_column_number_panel').items.clear();
    Ext.getCmp('formdesigner_column_number_panel').items.add(0, new Ext.form.ComboBox({
        id : "formdesigner_column_number",
        xtype : "combo",
        region: "west",
        typeAhead: true,
        fieldLabel: RM.Translate.Admin.Formdesigner.Edit.ColumnSelection,
        labelStyle: "padding-left:15px;",
        forceSelection: true,
        triggerAction: 'all',
        selectOnFocus: true,
        labelWidth: 190,
        width : 70,
        store: records,
        allowBlank: false,
        listeners: {
            'select' : function(){
                RM.Pages.Functions.Formdesigner_PortalChangeColumns(this.value);
            }
        }
    }));    
    Ext.getCmp('formdesigner_column_number').setValue(responseObject.columns);    
    Ext.getCmp('formdesigner_column_number_panel').doLayout();

    var formdesignerButtons = Ext.getCmp('rm_formdesigner_buttons');
    var g =0;for (g; g < formdesignerButtons.items.items.length; g++){
        formdesignerButtons.remove(g);
    }

    var disabled_buttons = [];
    var c = 0;for(c; c < responseObject.formState.length; c++){
        var p = 0;for (p; p < responseObject.formState[c].length; p++){
            disabled_buttons.push(responseObject.formState[c][p].xtype);
        }
    }

    var buttons = [];
    var h = 0;for (h; h < responseObject.panels.length; h++){
        buttons.push({
            text: responseObject.panels[h].name,
            id: 'rm_formdesigner_' + responseObject.panels[h].id + '_button',
            panelID: responseObject.panels[h].id,
            cls: 'fullWidth',
            disabled: (disabled_buttons.indexOf(RM_Form_Naming_Manager.generatePanelXType(responseObject.panels[h].id)) === -1) ? false : true,
            handler: function(){
                var portal = Ext.getCmp('formdesigner-page');
                eval('var element = new ' + RM_Form_Naming_Manager.generatePanelClassName(this.panelID) + '({xtype: "' + RM_Form_Naming_Manager.generatePanelXType(this.panelID) + '", resizeable: false})');
                var button_element = Ext.getCmp(
                    RM_Form_Naming_Manager.generateButtonID(
                        RM_Form_Naming_Manager.generatePanelXType(this.panelID)
                    )
                );
                portal.items.items[0].add(element);
                button_element.setDisabled(true);
                portal.doLayout();
            }
        });
    }

    formdesignerButtons.items.add(new RM_FormDesignerButtonPanel(buttons));
    formdesignerButtons.doLayout();

    Ext.state.Manager.setProvider(new Ext.state.Provider());

    Ext.getCmp('formdesigner-page').applyState(responseObject.formState);
    Ext.getCmp('formdesigner-page').doLayout();

    RM.Pages.Formdesigner_Mask.hide();
};

RM.Pages.Functions.Formdesigner_FormID = '';
RM.Pages.Functions.Formdesigner_UnitTypeID = '';
RM.Pages.Functions.FormDesigner_EditJson = function(responseObject) {

    if (responseObject.error === true){
        Ext.Msg.alert(RM.Translate.Common.Status, RM.Translate.Admin.Formdesigner.Edit.NoUnit);
        return;
    }

    Ext.getCmp('rm_formdesigner_colwidth1').setValue(responseObject.column1width);

    // set initial value (panel 1 is always shown)
    Ext.getCmp('rm_formdesigner_colwidth2_panel').hide();
    Ext.getCmp('rm_formdesigner_colwidth3_panel').hide();

    var columns = responseObject.columns;
    if (columns===2){
        // show column selection panel 2
        Ext.getCmp('rm_formdesigner_colwidth2').setValue(responseObject.column2width);
        Ext.getCmp('rm_formdesigner_colwidth2_panel').show();
    } else if (columns===3) {
        // show all column selection panels
        Ext.getCmp('rm_formdesigner_colwidth2').setValue(responseObject.column2width);
        Ext.getCmp('rm_formdesigner_colwidth3').setValue(responseObject.column3width);
        Ext.getCmp('rm_formdesigner_colwidth3_panel').show();
        Ext.getCmp('rm_formdesigner_colwidth2_panel').show();
    }

    Ext.getCmp('toolbar_panel').layout.setActiveItem('rm_pages_formdesigner_edit_toolbar');
    Ext.getCmp('content-panel').layout.setActiveItem('formdesigner-main');    

    RM.Pages.Formdesigner_Mask = new Ext.LoadMask('formdesigner-main', {msg:RM.Translate.Common.PleaseWait});
    RM.Pages.Formdesigner_Mask.show();

    RM.Pages.Formdesigner_Edit_ResponseObject = responseObject;

    var iframeSrc = RM.UserRootURL + RM.Common.AssembleURL({
        controller : 'FormDesigner',
        action: 'allpanels',
        parameters: [{
            'name': 'unitTypeID',
            'value': responseObject.unitTypeID
        }],
        forcefrontend: true
    });
    if (document.getElementById('rm_formdesigner_data_iframe')) {
	    document.getElementById('rm_formdesigner_data_iframe').src = iframeSrc;
    } else {
        var iframe;
        iframe = document.createElement('iframe');
        if (document.createElement && iframe) {
            iframe.setAttribute('id', 'rm_formdesigner_data_iframe');
            iframe.setAttribute('name', 'rm_formdesigner_data_iframe');
            iframe.setAttribute('style', 'display: none');
            iframe.setAttribute('src', iframeSrc);
            iframe.setAttribute('onload', 'RM.Pages.Functions.Formdesigner_LoadPanelsState();');
            document.body.appendChild(iframe);
        }
    }
};

RM.Main.Pages.push(RM.Pages.Formdesigner_Home_Main);