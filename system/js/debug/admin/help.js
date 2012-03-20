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
 * Help JS
 * This creates the admin GUI Help
 *
 * JSLint.com Check: 27/01/2010
 *
 * @access       public
 * @author       Rob/Valentin
 * @copyright    2011 ResMania Ltd.
 * @version      1.2
 * @link         http://docs.resmania.com/api/
 * @since        06-2011
 */

RM.Help = {};

if (RM.Config.rm_config_admin_help_panel_enable !== "1") {
    RM.Help.Create = function(targetID, textName){
        return null;
    };
    RM.Help.Load = function(textName){
        return null;
    };
} else {
    RM.Help.Cache = {};
    RM.Help.URL = function(text){
        return RM.Common.AssembleURL({
            controller : 'Index',
            action: 'helpjson',
            parameters: [{
                name: 'text',
                value: text
            }]
        });
    };

    /**
     * Method for creation tooltip of an element
     *
     * @access public
     * @param targetID - id of the extjs element
     * @param textName - name of help constant in this format: <Section Name>:<Constant Name>
     **/
    RM.Help.Create = function(targetID, textName){
        try{
            Ext.get(targetID).on('mouseover', RM.Help.Timer.Set, this, {text: textName});
            Ext.get(targetID).on('mouseout', RM.Help.Timer.Clear, this);
        } catch (e) {
            // handle the error
        }
    };

    RM.Help.Timer = {};
    RM.Help.Text = null;
    RM.Help.Timer.Text = null;
    RM.Help.Timer.Delay = 1500;
    RM.Help.Timer.ID = null;
    RM.Help.Timer.Set = function(event, targer, parameters){
        if (RM.Help.Timer.Text === parameters.text) {
            return true;
        }
        RM.Help.Timer.Clear();        
        RM.Help.Timer.Text = parameters.text;
        RM.Help.Timer.ID = window.setTimeout('RM.Help.Timer.Load()', RM.Help.Timer.Delay);
    };

    /**
     * Load help content immediately (without any mouseover)
     *
     * @access public
     * @param textName - name of help constant in this format: <Section Name>:<Constant Name>
     **/
    RM.Help.Load = function(textName){
        if (!textName) {return;}
        var textIndex = textName.replace(/\./g, '_'); //This is odd on the first sight, but we need to transform this string to make an index and prevent index duplication in the same time.

        try {
            eval("var message = RM.Help.Cache." + textIndex + ";");
            if (typeof(message) !== "undefined") {
                Ext.getCmp('help_panel').body.update(message);
                return true;
            }
        } catch (e) {
            //Doing nothing - we just don't have this message yet in cache
        }

        var helpURL = RM.Help.URL(textName);

        RM.Help.Text = textName;
        Ext.getCmp('help_panel').load({
            url: helpURL,
            text: RM.Translate.Common.LoadingMessage,
            nocache: false,
            callback: function(el, success, response, options){
                eval("RM.Help.Cache." + textIndex + " = response.responseText;");
            }
        });
    };

    RM.Help.Timer.Load = function(){
        RM.Help.Load(RM.Help.Timer.Text);
    };

    RM.Help.Timer.Clear = function(){
        RM.Help.Timer.Text = null;
        window.clearTimeout(RM.Help.Timer.ID);
    };
}