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
 * Layout JS
 * This creates the admin GUI Layout
 *
 * JSLint.com Check: 18/03/2011
 *
 * @access      public
 * @author      Rob/Valentin
 * @copyright   2011 ResMania Ltd.
 * @version     1.2
 * @link        http://docs.resmania.com/api/
 * @since       06-2011
 */

RM.Pages.ScreenSizeWarning = false; // this tracks if the screensize is too small

RM.Pages.Default_Help = {
    xtype : "panel",
    id : "rm_pages_default_help",
    bodyBorder : false,
    items:[]
};

// this resizes the gui to fit properly...
window.onresize = function(){

    Ext.getBody().setWidth(Ext.getBody().getViewSize().height);
    Ext.getBody().setWidth(Ext.getBody().getViewSize().width);
    try {
        Ext.getCmp('root_gui_container').doLayout();
    }catch(err){
    // this happens if the gui is not yet rendered and the root_gui_container component is not present.
    // it is not a problem as this could be a resize even during initialisation.
    }
};

// buttons
RM.Pages.Functions.Common_SaveGUI_MaxMinState = function(state){
    var myMask = new Ext.LoadMask('root_gui_container', {
        msg:RM.Translate.Common.PleaseWait
        });
    myMask.show();
    var conn = new Ext.data.Connection();
    var request = {
        url: RM.Common.AssembleURL({
            controller : 'Index',
            action: 'updatemaxminstateJson',
            parameters : [{
                name : 'GUIstate',
                value : state
            }]
        }),
        method: 'POST',
        success: function(responseObject) {
            var data = Ext.util.JSON.decode(responseObject.responseText);
            window.location.href=data.url;
            myMask.hide();
        },
        failure: function() {
            Ext.Msg.alert(RM.Translate.Common.Status, RM.Translate.Common.UnableToShow);
            myMask.hide();
        }
    };
    conn.request(request);
};


RM.Pages.Common_GUI_MaximiseGUI_Toolbar = {
    xtype : "panel",
    id : "rm_pages_common_gui_maximise_toolbar",
    bodyBorder : false,
    html : RM.Common.getToolbar([
    {
        image: RM.BaseLargeImageURL+"maximise.gif",
        label: RM.Translate.Common.Maximise,
        link: "RM.Pages.Functions.Common_SaveGUI_MaxMinState(1)"
    }
    ])
};
RM.Toolbars.push(RM.Pages.Common_GUI_MaximiseGUI_Toolbar);

RM.Pages.Common_GUI_MinimiseGUI_Toolbar = {
    xtype : "panel",
    id : "rm_pages_common_gui_minimise_toolbar",
    bodyBorder : false,
    html : RM.Common.getToolbar([
    {
        image: RM.BaseLargeImageURL+"minimise.gif",
        label: RM.Translate.Common.Minimise,
        link: "RM.Pages.Functions.Common_SaveGUI_MaxMinState(0)"
    }
    ])
};
RM.Toolbars.push(RM.Pages.Common_GUI_MinimiseGUI_Toolbar);

RM.Pages.Functions.HomePage_Toolbars = function(){
    if (RM.Common.GUIMaximised === "0"){
        Ext.getCmp('toolbar_panel').layout.setActiveItem('rm_pages_common_gui_maximise_toolbar');
    } else {
        Ext.getCmp('toolbar_panel').layout.setActiveItem('rm_pages_common_gui_minimise_toolbar');
    }
    Ext.getCmp('toolbar_panel').syncSize();
};

RM.Pages.Functions.MainTreeMenuHandler = function(controllerValue, actionValue, id, params){                
    var paramsValue = id;

    if (paramsValue === 'NoAjax'){
        var tmpvar = 'RM.Pages.Functions.'+controllerValue+'_'+actionValue+'({});';
        eval(tmpvar);
        return true;
    }

    var myMask = new Ext.LoadMask('content-panel', {
        msg:RM.Translate.Common.PleaseWait
        });
    myMask.show();
       
    Ext.getCmp('toolbar_panel').layout.setActiveItem('rm_pages_default_toolbar');

    var request = {
        url: RM.Common.AssembleURL({
            controller : controllerValue,
            action: actionValue
        }),
        method: 'POST',
        success: function(responseObject) {

            var jsonObject = RM.Common.JSON.decode(responseObject.responseText, true);

            if (paramsValue==="showlicensing"){
                jsonObject.licensing = true;
            } else if (paramsValue==="showlanguage"){
                jsonObject.language = true;
            }

            var tmpvar = 'RM.Pages.Functions.'+controllerValue+'_'+actionValue+'(jsonObject);';
            eval(tmpvar);
            myMask.hide();
        },
        failure: function() {
            Ext.Msg.alert('Status', RM.Translate.Common.UnableToShow);
        }
    };

    var currentParams = {}; // create object

    if ( params !== null && params !== undefined ){
        currentParams = RM.Common.Clone(params);
    }

    if (paramsValue) {
        currentParams.id = paramsValue;
    }

    request.params = currentParams;

    var conn = new Ext.data.Connection();
    conn.request(request);
};



//Ext.onReady(function(){
RM.OnReady = function(){
    //Ext.QuickTips.init(); // moved to end of routine based on extjs example
    Ext.Ajax.on('requestcomplete', function(conn, response, options){
        if (typeof(response.getResponseHeader) !== 'undefined') {
            if (response.getResponseHeader('RMError') === 1){
                alert(response.responseText);
            }
        }
    }, this);

    var myTreeLoader = new Ext.tree.TreeLoader({
        requestMethod : 'POST',
        dataUrl : RM.Common.AssembleURL({
            controller : 'Menu',
            action: 'treeJson'
        })
    });

    // Assign the changeLayout function to be called on tree node click.
    var treePanel = new Ext.tree.TreePanel( {
        id: "rm_main_tree_menu",
        xtype : "treepanel",
        animate : true,
        autoScroll : true,
        containerScroll : true,
        rootVisible: false,
        lines: true,
        singleExpand: false,
        useArrows: false,
        collapsed: false,

        // TODO: we can only do Ext.getCmp('rm_main_tree_menu').root.reload()
        // because there is only one AyncTreeNode, need to define one for units
        // then we can also reload the units.
        root: new Ext.tree.AsyncTreeNode({
            text: RM.Translate.Admin.Layout.Home
            }),

        loader : myTreeLoader ,

        dropConfig : {
            appendOnly : true
        }        
    });

    treePanel.on('click', function(n){        
        if (n.attributes.external){
            window.open(n.attributes.url, '_blank');
            return true;
        }        
        // send the the click handler function...
        var chunks = n.id.split('_');
        RM.Pages.Functions.MainTreeMenuHandler(chunks[0], chunks[1], chunks[2], n.attributes);        
    });   

    var contentPanel = new Ext.Panel({
        id: 'content-panel',
        region : "center",
        border: false,
        activeItem: 0,
        layout: 'card',
        items : RM.Main.Pages,
        height: RM.Common.GetPanelHeight()
    });


    var mainTreePanel = new Ext.Panel({
        id : 'main_tree_panel',
        layout : "card",
        activeItem : 0,       
        items : [ treePanel ],
        title : RM.Translate.Admin.Layout.TreeMenu
    });

    var LogoandButtonBar = {
        region : "north",
        height : 50,
        layout: "column",
        border : false,
        items: [{
            region: 'west',
            width : 250,
            bodyBorder : false,
            html : "<div class='RM_Logo'><img src='"+RM.BaseLargeImageURL+"logo.jpg' alt='"+RM.Translate.Common.Logo+"'/></div>"
        } , {
            region: 'center',
            id : "toolbar_panel",
            width: 500,
            activeItem: 0,
            cls: 'RM_Toolbar_position',
            bodyBorder : false,
            layout: 'card',
            items : RM.Toolbars
        }]
    };

    var mainHelp = {
        layout : "card",
        activeItem : 0,
        id : "help_panel",        
        autoScroll : true,
        bodyStyle : "padding:5px;",
        bodyCssClass: "RM_Help_Panel",
        title : RM.Translate.Common.Help
    };

    var MenuandHelp = {
        id: "rm_main_menuhelp_area",
        region : "west",
        width : 200
    };

    // enable or disable the help panel...
    if (RM.Config.rm_config_admin_help_panel_enable==="0"){
        MenuandHelp.items = [ mainTreePanel ];
        mainTreePanel.height = RM.Common.MainTreePanelHeight(1.0);
    } else {        
        MenuandHelp.items = [ mainTreePanel , mainHelp ];
        mainHelp.height = RM.Common.MainTreePanelHeight(0.3);
        mainTreePanel.height = RM.Common.MainTreePanelHeight(0.7);
    }

    var mainPanel = new Ext.Panel({
        id: "root_gui_container",
        layout : "border",
        items : [ contentPanel, LogoandButtonBar , MenuandHelp],

        width : '100%',
        height: RM.Common.MainHeight(),
        renderTo: 'main_form',
        listeners: {
            'afterrender' : function() {
                Ext.get('loading').remove();
                Ext.fly('loading-mask').hide({
                    duration: 3
                });
            }
        },
        bbar: new Ext.ux.StatusBar({
            id: 'rm_pages_main_admin_statusbar',
            cls: "RM_Dashboard_notification_bar",
            items: []
        })
    });

    RM.Pages.Functions.AdminConsole_MainToolBar_Update = function(textVar){
        
        var sb = Ext.getCmp('rm_pages_main_admin_statusbar');
        
        if (textVar!==""){
            sb.setStatus({
                iconCls: 'failed-icon',
                text: textVar
            });
        }

        sb.add(new Ext.Toolbar.TextItem(document.getElementById("rm-versioninfo").innerHTML));
    };

    RM.Pages.Functions.AdminConsole_MainToolBar_Update();

    
    // min, max back to cms buttons
    RM.Pages.Functions.HomePage_Toolbars();

    RM.Help.Create('main_tree_panel', 'Common.UnitTree');    

    RM.Help.Load('Common.MainHelp'); // load the help

    Ext.QuickTips.init();

    if (RM.CacheError) {
        RM.Common.Message.msg(RM.Translate.Common.CacheErrorTitle, RM.Translate.Common.CacheError, 15,  "RM_MessagePopup" );
    }

    var rmWidth = Ext.getBody().getViewSize().width;
    var rmHeight = Ext.getBody().getViewSize().height;    
    if (rmHeight < 700 || rmWidth < 975) {
        //I can't use Ext message alert cause we have too big message and it shows it badly.
        //ExtJS message alert don't have any option to increase height of the alert message. ExtJS is so ExtJS.
        RM.Common.Message.msg(RM.Translate.Common.Error, RM.Translate.Common.WindowSizeError, 15,  "RM_MessagePopup" );
        RM.Pages.ScreenSizeWarning = true;
    }

    var RM_LOADING_TIME = new Date().getTime() - RM_START;
    var request = {
        url: RM.Common.AssembleURL({
            controller : 'Index',
            action: 'logloadingtimeJson'
        }),
        params: {
            'time' : RM_LOADING_TIME
        },
        method: 'POST',
        success: function(responseObject) {
            var data = Ext.util.JSON.decode(responseObject.responseText);
            var updatetext = "";
            if (data.moduleupdates){
                updatetext = RM.Translate.Common.NewModulesAvailable + data.moduleupdates + " ";
                RM.Pages.Functions.AdminConsole_MainToolBar_Update(updatetext);
            }
            if (data.pluginupdates){
                updatetext = updatetext + RM.Translate.Common.NewModulesPlugins + data.moduleupdates + " ";
                RM.Pages.Functions.AdminConsole_MainToolBar_Update(updatetext);
            }

            if (!data.licensekey){
                updatetext = updatetext + RM.Translate.Common.NotLicensed + " ";
                RM.Pages.Functions.AdminConsole_MainToolBar_Update(updatetext);
            }
        }
    };
    new Ext.data.Connection().request(request);

    // warn the user if the console is MSIE 7 or 8
    if(Ext.isIE){
        if(Ext.isIE6){
            RM.Common.Message.msg(RM.Translate.Common.Information, RM.Translate.Common.MSIE6, 30,  "RM_MessagePopup" );
        }
        if(Ext.isIE7 || Ext.isIE8){
            RM.Common.Message.msg(RM.Translate.Common.Information, RM.Translate.Common.MSIE78, 25,  "RM_MessagePopup" );
        }
    }
};

