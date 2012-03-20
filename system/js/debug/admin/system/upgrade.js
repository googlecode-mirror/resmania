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
 * Upgrade Page JS
 * This creates the admin GUI Configuration Page
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

// toolbar
RM.Pages.System_Upgrade_Toolbar = {
    xtype : "panel",
    id : "rm_system_upgrade_toolbar",
    bodyBorder : false,
    html : RM.Common.getToolbar([
        {image: RM.BaseLargeImageURL+"upgrade.png", label: RM.Translate.Admin.System.Upgrade.PerformUpgrade, link: "RM.Pages.Functions.System_Upgrade_Check_Extensions()"}
    ])
};
RM.Toolbars.push(RM.Pages.System_Upgrade_Toolbar);

// Process function

// start upgrade...

// step 1
RM.Pages.Functions.System_Upgrade_Disable_Extensions = function(){
    myMask = new Ext.LoadMask('content-panel', {msg:RM.Translate.Common.PleaseWait});
    myMask.show();
    var request = {
        url: RM.Common.AssembleURL({
            controller : 'System_Upgrade_Extensions',
            action: 'disableJson'
        }),
        method: 'POST',
        success: function(responseObject) {
            var jsonObject = Ext.util.JSON.decode(responseObject.responseText);
            myMask.hide();
            RM.Pages.Functions.System_Upgrade();
        },
        failure: function() {
            myMask.hide();
            Ext.Msg.alert(RM.Translate.Common.Status, RM.Translate.Common.UnableToShow);
        }
    };

    var conn = new Ext.data.Connection();
    conn.timeout = 900000; //15 minutes
    conn.request(request);
};

RM.Pages.Functions.System_Upgrade_Renew_Extensions = function(){
    myMask = new Ext.LoadMask('content-panel', {msg:RM.Translate.Common.PleaseWait});
    myMask.show();
    var request = {
        url: RM.Common.AssembleURL({
            controller : 'System_Upgrade_Extensions',
            action: 'renewJson'
        }),
        method: 'POST',
        success: function(responseObject) {
            var jsonObject = Ext.util.JSON.decode(responseObject.responseText);
            myMask.hide();
            RM.Pages.Functions.System_Upgrade();
        },
        failure: function() {
            myMask.hide();
            Ext.Msg.alert(RM.Translate.Common.Status, RM.Translate.Common.UnableToShow);
        }
    };

    var conn = new Ext.data.Connection();
    conn.timeout = 900000; //15 minutes
    conn.request(request);
};

RM.Pages.Functions.System_Upgrade_Check_Extensions = function(){
    myMask = new Ext.LoadMask('content-panel', {msg:RM.Translate.Common.PleaseWait});
    myMask.show();
    var request = {
        url: RM.Common.AssembleURL({
            controller : 'System_Upgrade_Extensions',
            action: 'checkJson'
        }),
        method: 'POST',
        success: function(responseObject) {
            var jsonObject = Ext.util.JSON.decode(responseObject.responseText);
            myMask.hide();
            if (jsonObject.upgradeExtensionAvailable) {
                Ext.Msg.show({
                    title: RM.Translate.Admin.System.Upgrade.Title,
                    msg: RM.Translate.Admin.System.Upgrade.ExtensionsUpgradeAvailable+jsonObject.upgradeExtensions.join(', '),
                    buttons: Ext.Msg.YESNOCANCEL,                                        
                    fn: function(buttonID){                        
                        if (buttonID === 'cancel') {
                            return;
                        }
                        if (buttonID === 'yes') {
                            RM.Pages.Functions.System_Upgrade_Renew_Extensions();
                        } else {
                            RM.Pages.Functions.System_Upgrade_Disable_Extensions();
                        }
                    }
                });
            } else {
                RM.Pages.Functions.System_Upgrade();
            }
        },
        failure: function() {
            myMask.hide();
            Ext.Msg.alert(RM.Translate.Common.Status, RM.Translate.Common.UnableToShow);
        }
    };

    var conn = new Ext.data.Connection();
    conn.timeout = 900000; //15 minutes
    conn.request(request);
};

RM.Pages.Functions.System_Upgrade = function(){
    
    Ext.MessageBox.confirm(RM.Translate.Admin.System.Upgrade.Title, RM.Translate.Admin.System.Upgrade.Confirm, function(buttonID){
        
        if (buttonID !== 'yes') {
            return;
        }

        // clear the changelog as we don't need it now
        Ext.getCmp('rm_system_upgrade_changelog_fieldset').hide();

        myMask = new Ext.LoadMask('content-panel', {msg:RM.Translate.Common.PleaseWait});
        myMask.show();

        Ext.getCmp('rm_system_upgrade_statusbar').setStatus({
            text: RM.Translate.Admin.System.Upgrade.Progressing,
            iconCls: 'working-icon'
        });

        new Ext.Panel({
            id: "rm_system_upgrade_progress_msg_downloadcomplete",
            bodyBorder : false,
            html: "<table width='400px' border='0'><tr><td width='350px'>"+RM.Translate.Admin.System.Upgrade.ChkDownloadCore+"</td><td width='50px'><img src='"+RM.BaseSmallImageURL+"working.gif'></td></tr></table>"
        });

        Ext.getCmp('rm_system_upgrade_process_fieldset').add(Ext.getCmp('rm_system_upgrade_progress_msg_downloadcomplete'));
        Ext.getCmp('rm_system_upgrade_main_panel').doLayout();

        // set a timer going to update the progress bar
        RM.Pages.System_Upgrade_ProgressBar_Timer = {};
        RM.Pages.System_Upgrade_ProgressBar_Timer.Delay = 5000;
        RM.Pages.System_Upgrade_ProgressBar_Timer.ID = null;
        RM.Pages.System_Upgrade_ProgressBar_Timer.Set = function(){
            RM.Pages.System_Upgrade_ProgressBar_Timer.ID = window.setTimeout('RM.Pages.Functions.System_Upgrade_Download_Progress('+sid+')', RM.Pages.System_Upgrade_ProgressBar_Timer.Delay);
        };
        RM.Pages.System_Upgrade_ProgressBar_Timer.Clear = function(){
            window.clearTimeout(RM.Pages.System_Upgrade_ProgressBar_Timer.ID);
        };

        var sid = RM.Common.rand(10000,999999); // a session ID to track the process.

        var request = {
            url: RM.Common.AssembleURL({
                controller : 'System',
                action: 'downloadcorejson'
            }),
            params: {
                sessionid: sid
            },
            method: 'POST',
            success: function(responseObject) {

                var jsonObject = Ext.util.JSON.decode(responseObject.responseText);

                // stop the timer from updating the progress bar
                try{
                    RM.Pages.System_Upgrade_ProgressBar_Timer.Clear();
                } catch (err){
                    // handler
                }

                if (jsonObject.success){
                    Ext.getCmp('rm_system_upgrade_progress_msg_downloadcomplete').body.update("<table width='400px' border='0'><tr><td width='350px'>"+RM.Translate.Admin.System.Upgrade.ChkDownloadCore+"</td><td width='50px'><img src='"+RM.BaseSmallImageURL+"tick.gif'></td></tr></table>");
                } else {
                    Ext.getCmp('rm_system_upgrade_progress_msg_downloadcomplete').body.update("<table width='400px' border='0'><tr><td width='350px'>"+RM.Translate.Admin.System.Upgrade.ChkDownloadCore+"</td><td width='50px'><img src='"+RM.BaseSmallImageURL+"cross.gif'></td></tr></table>");
                }

                Ext.getCmp('rm_system_upgrade_download_progressbar').hide(); // hide it as we don't need it anymore

                // call the ajax function to unzip the core
                RM.Pages.Functions.System_Upgrade_Unzip_Core();

            },
            failure: function() {
                Ext.Msg.alert(RM.Translate.Common.Status, RM.Translate.Common.UnableToShow);
            }
        };

        var conn = new Ext.data.Connection();
        conn.timeout = 900000; //15 minutes
        conn.request(request);

        myMask.hide();

        // the ajax request to download has been sent, now show the download progress

        // hide all un-used form items
        Ext.getCmp('rm_system_upgrade_options_fieldset').hide();
        Ext.getCmp('toolbar_panel').hide();

        Ext.getCmp('rm_system_upgrade_main_panel').add(RM.Pages.System_Upgrade_Progress_Fieldset);
        Ext.getCmp('rm_system_upgrade_main_panel').doLayout();
        
        RM.Pages.System_Upgrade_ProgressBar_Timer.Set();
    });
};

// get the download progress and update the progressbar
RM.Pages.Functions.System_Upgrade_Download_Progress = function(sid){
    var request = {
        url: RM.Common.AssembleURL({
            controller : 'System',
            action: 'downloadprogressjson'
        }),
        params:{
            sessionid: sid
        },
        method: 'POST',
        success: function(responseObject) {

            var jsonObject = Ext.util.JSON.decode(responseObject.responseText);

            Ext.getCmp("rm_system_upgrade_download_progressbar").updateProgress(jsonObject.percentcomplete,RM.Translate.Admin.System.Upgrade.DownloadingWait,true);

            if (jsonObject.percentcomplete<1) {
                RM.Pages.System_Upgrade_ProgressBar_Timer.Set(); // set the time to run this again in 5 seconds.
            } else {
                RM.Pages.System_Upgrade_ProgressBar_Timer.Clear();
            }
        }
    };

    var conn = new Ext.data.Connection();
    conn.request(request);
};


// step 2: download complete now send an ajax request to unzip
RM.Pages.Functions.System_Upgrade_Unzip_Core = function(){

    new Ext.Panel({
        id: "rm_system_upgrade_progress_msg_unzipcomplete",
        bodyBorder : false,
        html: "<table width='400px' border='0'><tr><td width='350px'>"+RM.Translate.Admin.System.Upgrade.ChkUnZip+"</td><td width='50px'><img src='"+RM.BaseSmallImageURL+"working.gif'></td></tr></table>"
    });

    Ext.getCmp('rm_system_upgrade_process_fieldset').add(Ext.getCmp('rm_system_upgrade_progress_msg_unzipcomplete'));
    Ext.getCmp('rm_system_upgrade_main_panel').doLayout();

    var request = {
        url: RM.Common.AssembleURL({
            controller : 'System',
            action: 'unzipcorezipjson'
        }),
        method: 'POST',
        success: function(responseObject) {

            var jsonObject = Ext.util.JSON.decode(responseObject.responseText);

            if (jsonObject.success){
                Ext.getCmp('rm_system_upgrade_progress_msg_unzipcomplete').body.update("<table width='400px' border='0'><tr><td width='350px'>"+RM.Translate.Admin.System.Upgrade.ChkUnZip+"</td><td width='50px'><img src='"+RM.BaseSmallImageURL+"tick.gif'></td></tr></table>");
            } else {
                Ext.getCmp('rm_system_upgrade_progress_msg_unzipcomplete').body.update("<table width='400px' border='0'><tr><td width='350px'>"+RM.Translate.Admin.System.Upgrade.ChkUnZip+"</td><td width='50px'><img src='"+RM.BaseSmallImageURL+"cross.gif'></td></tr></table>");
            }

            // go to the next stage - and copy the upgrade files
            RM.Pages.Functions.System_Upgrade_DataBase();
        }
    };

    var conn = new Ext.data.Connection();
    conn.timeout = 300000; //5 minutes
    conn.request(request);
};

// Step 3: Now upgrade the DB...
RM.Pages.Functions.System_Upgrade_DataBase = function(){

    new Ext.Panel({
        id: "rm_system_upgrade_progress_msg_dbcomplete",
        bodyBorder : false,
        html: "<table width='400px' border='0'><tr><td width='350px'>"+RM.Translate.Admin.System.Upgrade.ChkDataBase+"</td><td width='50px'><img src='"+RM.BaseSmallImageURL+"working.gif'></td></tr></table>"
    });

    Ext.getCmp('rm_system_upgrade_process_fieldset').add(Ext.getCmp('rm_system_upgrade_progress_msg_dbcomplete'));
    Ext.getCmp('rm_system_upgrade_main_panel').doLayout();

    var request = {
        url: RM.Common.AssembleURL({
            controller : 'System',
            action: 'upgradedatabasejson'
        }),
        method: 'POST',
        success: function(responseObject) {

            var jsonObject = Ext.util.JSON.decode(responseObject.responseText);

            if (jsonObject.success){
                Ext.getCmp('rm_system_upgrade_progress_msg_dbcomplete').body.update("<table width='400px' border='0'><tr><td width='350px'>"+RM.Translate.Admin.System.Upgrade.ChkDataBase+"</td><td width='50px'><img src='"+RM.BaseSmallImageURL+"tick.gif'></td></tr></table>");
            } else {
                Ext.getCmp('rm_system_upgrade_progress_msg_dbcomplete').body.update("<table width='400px' border='0'><tr><td width='350px'>"+RM.Translate.Admin.System.Upgrade.ChkDataBase+"</td><td width='50px'><img src='"+RM.BaseSmallImageURL+"cross.gif'></td></tr></table>");
            }

            // go to the next stage and upgrade the DB
            RM.Pages.Functions.System_Upgrade_Copy_Files();
        }
    };

    var conn = new Ext.data.Connection();
    conn.timeout = 120000; //2 minutes
    conn.request(request);
};

// Step 4: Now send the request to copy all the required files to the correct places.
RM.Pages.Functions.System_Upgrade_Copy_Files = function(){

    new Ext.Panel({
        id: "rm_system_upgrade_progress_msg_copycomplete",
        bodyBorder : false,
        html: "<table width='400px' border='0'><tr><td width='350px'>"+RM.Translate.Admin.System.Upgrade.ChkCopyFiles+"</td><td width='50px'><img src='"+RM.BaseSmallImageURL+"working.gif'></td></tr></table>"
    });

    Ext.getCmp('rm_system_upgrade_process_fieldset').add(Ext.getCmp('rm_system_upgrade_progress_msg_copycomplete'));
    Ext.getCmp('rm_system_upgrade_main_panel').doLayout();

    var request = {
        url: RM.Common.AssembleURL({
            controller : 'System',
            action: 'copyfilesjson'
        }),
        params: {
            core: Ext.getCmp('rm_system_upgrade_selection_core').getValue(),
            modules: Ext.getCmp('rm_system_upgrade_selection_modules').getValue(),
            plugins: Ext.getCmp('rm_system_upgrade_selection_plugins').getValue(),
            userdata: Ext.getCmp('rm_system_upgrade_selection_userdata').getValue()
        },
        method: 'POST',
        success: function(responseObject) {

            var jsonObject = Ext.util.JSON.decode(responseObject.responseText);

            if (jsonObject.success){
                Ext.getCmp('rm_system_upgrade_progress_msg_copycomplete').body.update("<table width='400px' border='0'><tr><td width='350px'>"+RM.Translate.Admin.System.Upgrade.ChkCopyFiles+"</td><td width='50px'><img src='"+RM.BaseSmallImageURL+"tick.gif'></td></tr></table>");
            } else {
                Ext.getCmp('rm_system_upgrade_progress_msg_copycomplete').body.update("<table width='400px' border='0'><tr><td width='350px'>"+RM.Translate.Admin.System.Upgrade.ChkCopyFiles+"</td><td width='50px'><img src='"+RM.BaseSmallImageURL+"cross.gif'></td></tr></table>");
            }

            RM.Pages.Functions.System_Process_Post_Upgrade_Script();
        }
    };

    var conn = new Ext.data.Connection();
    conn.timeout = 600000; //10 minutes
    conn.request(request);
};

// Step 5: process any post upgrade scripts
RM.Pages.Functions.System_Process_Post_Upgrade_Script = function(){

    new Ext.Panel({
        id: "rm_system_upgrade_progress_msg_postupgradescript",
        bodyBorder : false,
        html: "<table width='400px' border='0'><tr><td width='350px'>"+RM.Translate.Admin.System.Upgrade.PostUpgradeScript+"</td><td width='50px'><img src='"+RM.BaseSmallImageURL+"working.gif'></td></tr></table>"
    });

    Ext.getCmp('rm_system_upgrade_process_fieldset').add(Ext.getCmp('rm_system_upgrade_progress_msg_postupgradescript'));
    Ext.getCmp('rm_system_upgrade_main_panel').doLayout();

    var request = {
        url: RM.Common.AssembleURL({
            controller : 'System',
            action: 'postupgradescriptjson'
        }),
        method: 'POST',
        success: function(responseObject) {

            var jsonObject = Ext.util.JSON.decode(responseObject.responseText);

            if (jsonObject.success){
                Ext.getCmp('rm_system_upgrade_progress_msg_postupgradescript').body.update("<table width='400px' border='0'><tr><td width='350px'>"+RM.Translate.Admin.System.Upgrade.PostUpgradeScript+"</td><td width='50px'><img src='"+RM.BaseSmallImageURL+"tick.gif'></td></tr></table>");
            } else {
                Ext.getCmp('rm_system_upgrade_progress_msg_postupgradescript').body.update("<table width='400px' border='0'><tr><td width='350px'>"+RM.Translate.Admin.System.Upgrade.PostUpgradeScript+"</td><td width='50px'><img src='"+RM.BaseSmallImageURL+"cross.gif'></td></tr></table>");
            }

            RM.Pages.Functions.System_Upgrade_CleanUP();
        }
    };

    var conn = new Ext.data.Connection();
    conn.timeout = 600000; //10 minutes
    conn.request(request);
};

// Step 5: Clean up the old upgrade files...
RM.Pages.Functions.System_Upgrade_CleanUP = function(){

    new Ext.Panel({
        id: "rm_system_upgrade_progress_msg_cleanup",
        bodyBorder : false,
        html: "<table width='400px' border='0'><tr><td width='350px'>"+RM.Translate.Admin.System.Upgrade.CleanUP+"</td><td width='50px'><img src='"+RM.BaseSmallImageURL+"working.gif'></td></tr></table>"
    });

    Ext.getCmp('rm_system_upgrade_process_fieldset').add(Ext.getCmp('rm_system_upgrade_progress_msg_cleanup'));
    Ext.getCmp('rm_system_upgrade_main_panel').doLayout();

    var request = {
        url: RM.Common.AssembleURL({
            controller : 'System',
            action: 'cleanupjson'
        }),
        method: 'POST',
        success: function(responseObject) {

            var jsonObject = Ext.util.JSON.decode(responseObject.responseText);

            if (jsonObject.success){
                Ext.getCmp('rm_system_upgrade_progress_msg_cleanup').body.update("<table width='400px' border='0'><tr><td width='350px'>"+RM.Translate.Admin.System.Upgrade.CleanUP+"</td><td width='50px'><img src='"+RM.BaseSmallImageURL+"tick.gif'></td></tr></table>");
            } else {
                Ext.getCmp('rm_system_upgrade_progress_msg_cleanup').body.update("<table width='400px' border='0'><tr><td width='350px'>"+RM.Translate.Admin.System.Upgrade.CleanUP+"</td><td width='50px'><img src='"+RM.BaseSmallImageURL+"cross.gif'></td></tr></table>");
            }

            Ext.getCmp('rm_system_upgrade_statusbar').setStatus({
                text: RM.Translate.Admin.System.Upgrade.Complete,
                iconCls: 'ok-icon'
            });

            Ext.Msg.alert(RM.Translate.Admin.System.Upgrade.UpgradeComplete, RM.Translate.Admin.System.Upgrade.Complete);
        }
    };

    var conn = new Ext.data.Connection();
    conn.timeout = 600000; //10 minutes
    conn.request(request);
};


// panels items

// create a progressbar object here
RM.Pages.System_Upgrade_Progress_Bar = new Ext.ProgressBar({
    id: "rm_system_upgrade_download_progressbar",
    text:'Initializing...',
    width: 400
});

RM.Pages.System_Upgrade_Progress_Fieldset = new Ext.form.FieldSet({
    id: "rm_system_upgrade_process_fieldset",
    title: RM.Translate.Admin.System.Upgrade.UpgradeInProgress,
    layout: 'form',
    bodyBorder : false,
    height: RM.Common.GetPanelHeight(174),
    items: [
        RM.Pages.System_Upgrade_Progress_Bar
    ]
});

RM.Pages.System_Upgrade_Core = {
    xtype : "xcheckbox",
    id : "rm_system_upgrade_selection_core",
    name : "rm_system_upgrade_selection_core",
    inputValue : "1",
    labelStyle: "width: 225px;",
    fieldLabel : RM.Translate.Admin.System.Upgrade.Core,
    checked: true
};
RM.Pages.System_Upgrade_Modules = {
    xtype : "xcheckbox",
    id : "rm_system_upgrade_selection_modules",
    name : "rm_system_upgrade_selection_modules",
    labelStyle: "width: 225px;",
    inputValue : "1",
    fieldLabel : RM.Translate.Admin.System.Upgrade.Modules,
    checked: true
};
RM.Pages.System_Upgrade_Plugins = {
    xtype : "xcheckbox",
    id : "rm_system_upgrade_selection_plugins",
    name : "rm_system_upgrade_selection_plugins",
    labelStyle: "width: 225px;",
    inputValue : "1",
    fieldLabel : RM.Translate.Admin.System.Upgrade.Plugins,
    checked: true
};
RM.Pages.System_Upgrade_UserData = {
    xtype : "xcheckbox",
    id : "rm_system_upgrade_selection_userdata",
    name : "rm_system_upgrade_selection_userdata",
    labelStyle: "width: 225px;",
    inputValue : "1",
    fieldLabel : RM.Translate.Admin.System.Upgrade.UserData,
    checked: true
};
RM.Pages.System_Upgrade_Note = {
    xtype: "panel",
    bodyBorder : false,
    html: RM.Translate.Admin.System.Upgrade.Note
};

// upgrade options feildset
RM.Pages.System_Upgrade_Options_Fieldset = new Ext.form.FieldSet({
    id: "rm_system_upgrade_options_fieldset",
    title: RM.Translate.Admin.System.Upgrade.Options,
    autoHeight: true,
    layout: 'form',
    bodyBorder : false,
    collapsible: true,
    collapsed: true,
    hidden: true,
    items: [
        RM.Pages.System_Upgrade_Core,
        RM.Pages.System_Upgrade_Modules,
        RM.Pages.System_Upgrade_Plugins,
        RM.Pages.System_Upgrade_UserData,
        RM.Pages.System_Upgrade_Note
    ]
});

RM.Pages.System_Upgrade_Changelog_Fieldset = new Ext.form.FieldSet({
    title: RM.Translate.Admin.System.Upgrade.ChangelogTitle,
    id: "rm_system_upgrade_changelog_fieldset",    
    bodyBorder: false,
    autoScroll: true
});

RM.Pages.Functions.System_UpgradeJson_Changelog = function(){
    changelogFieldset = Ext.getCmp('rm_system_upgrade_changelog_fieldset');
    changelogFieldset.body.scale(null, 0, {duration: 0.3});

    var request = {
        url: RM.Common.AssembleURL({
            controller : 'System',
            action: 'upgradechangelogJson'
        }),
        method: 'POST',
        success: function(responseObject) {
            var response = Ext.util.JSON.decode(responseObject.responseText);
            if (response.success === false){
                //no connection to resmania server
                changelogFieldset.body.update(response.message);
                changelogFieldset.body.scale(null, 20, {duration: 0.6});
                if (response.license == true){
                    Ext.getCmp('toolbar_panel').show();
                }
                return true;
            }            

            //we have an old version and we need to chow a changelog from resmania server
            changelogFieldset.body.update(response.changelog);
            var changelogHeight = RM.Common.GetPanelHeight(209) - Ext.getCmp("rm_system_upgrade_options_fieldset").getHeight();
            if (changelogHeight < 0) {
                changelogHeight = 100;
            }

            changelogFieldset.body.scale(null, changelogHeight, {duration: 0.6});
            return false;
        }
    };
    var conn = new Ext.data.Connection();
    conn.request(request);
};

RM.Pages.Functions.System_UpgradeJson = function(){    
    RM.Pages.Functions.System_UpgradeJson_Changelog();

    RM.Help.Load('Admin.Config.Site.Main'); // load the help

    Ext.getCmp('content-panel').layout.setActiveItem('rm_system_upgrade_main_panel');
    Ext.getCmp('toolbar_panel').layout.setActiveItem('rm_system_upgrade_toolbar');

    //Ext.getCmp('rm_system_upgrade_options_fieldset').show();
    Ext.getCmp('rm_system_upgrade_changelog_fieldset').show();
};

RM.Pages.System_Upgrade_MainPanel = new Ext.Panel({
    id : "rm_system_upgrade_main_panel",
    layout: 'form',
    bodyStyle : "padding:10px",
    title: RM.Translate.Admin.System.Upgrade.Title,
    iconCls: "RM_config_upgrade_icon",
    autoScroll: true,
    items : [
        RM.Pages.System_Upgrade_Options_Fieldset,
        RM.Pages.System_Upgrade_Changelog_Fieldset        
    ],
    height: RM.Common.GetPanelHeight(134),
    bbar: new Ext.ux.StatusBar({
        id: 'rm_system_upgrade_statusbar',
        items: []
    })
});

RM.Main.Pages.push(RM.Pages.System_Upgrade_MainPanel);