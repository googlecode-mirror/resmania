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
 * Home Page JS
 * This creates the admin GUI Home Page
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

Ext.state.Manager.setProvider(new Ext.state.CookieProvider());

RM.Pages.HomePage = new Ext.Panel({
        id: 'rm_pages_homepage_panel',
        layout:'border',
        items: [
            {
                title: RM.Translate.Admin.Homepage.Dashboard,
                style:'padding:0px 0px 0px 0px',
                autoScroll: true,                
                region:'center',
                layout:'column',
                html: document.getElementById('hidden_dashboard').innerHTML
            },{
                style:'padding:0px 0px 12px 0px;background-color: white;',
                baseCls: "rm-welcome-page-panel",
                //autoScroll: true,
                bodyBorder : false,
                region:'east',
                layout:'column',
                width: 740,
                collapsed: false,
                collapsible: true,
                preventBodyReset: true,
                headerAsText: true,
                html: document.getElementById('hidden_welcome').innerHTML,
                stateful: true,
                stateId: 'welcome_panel_state',
                getState: function() {
                    return {
                        collapsed: this.collapsed
                    };
                },
                stateEvents: ['collapse', 'expand']
            }
        ]
});


// this is just used when the home page is called from the menu
RM.Pages.Functions.Index_HomepageJson = function(response){

    myMask = new Ext.LoadMask('content-panel', {
        msg: RM.Translate.Common.PleaseWait,
        msgCls: "RM_Loading_Mask_Msg_Hidden" // hide the msg
    });
    myMask.show();

    // min, max, back to cms buttons...
    RM.Pages.Functions.HomePage_Toolbars();
    
    Ext.getCmp('content-panel').layout.setActiveItem('rm_pages_homepage_panel');
    myMask.hide();
};

RM.Main.Pages.push(RM.Pages.HomePage);
