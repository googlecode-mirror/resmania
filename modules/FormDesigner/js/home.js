/*
 * Edit Locations Module JS
 * This creates the admin GUI Edit Page for the Unit Locations Module
 *
 * JSLint.com Check:18/03/2011
 */

RM.Pages.Formdesigner_HelpPage_MainPanel = new Ext.Panel({
    xtype : "panel",
    height: RM.Common.GetPanelHeight(120),
    id: 'rm_pages_formdesigner_helppage_mainpanel',
    bodyStyle : "padding:10px;",
    layout: 'form',
    labelWidth: 190,
    bodyBorder: false,
    autoScroll: true,
    title: RM.Translate.Admin.FormDesigner.Main.name
});

RM.Pages.Functions.Formdesigner_HelpPage_MainPanel_Show = function(){
    RM.Pages.Formdesigner_HelpPage_MainPanel.load({
        url: RM.Common.AssembleURL({
            controller : 'FormDesigner',
            action: 'helppage'
        }),
        scripts: true
    });
    Ext.getCmp('content-panel').layout.setActiveItem('rm_pages_formdesigner_helppage_mainpanel');
};

RM.Main.Pages.push(RM.Pages.Formdesigner_HelpPage_MainPanel);