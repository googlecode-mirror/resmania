RM.Pages.Formdesigner_UnitName = Ext.extend(Ext.Panel, {
    title: "-drag bar-",
    style:'padding:0px 0px 0px 0px',
    closeable: true,
    collapsible: false,
    html: "some more html",
    preventBodyReset: true, // this is important to ensure the html template is not effected by the ExtJS css
    plugins: Ext.ux.PortletPlugin,
    baseCls: 'RM_form_designer_container'
});
Ext.reg('unitname', RM.Pages.Formdesigner_UnitName);

RM.Pages.Formdesigner_UnitDescription = Ext.extend(Ext.Panel, {
    title: "-drag bar-",
    style:'padding:0px 0px 0px 0px',
    width: 680,
    closeable: true,
    collapsible: false,
    html: "some test html",
    preventBodyReset: true, // this is important to ensure the html template is not effected by the ExtJS css
    plugins: Ext.ux.PortletPlugin,
    baseCls: 'RM_form_designer_container'
});
Ext.reg('unitdescription', RM.Pages.Formdesigner_UnitDescription);