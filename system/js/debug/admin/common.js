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
 * This file contains all common JS and front-end methods.
 * These include: toolbar (renders to the RM_Toolbars div)
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

/* RM.Common.AssembleURL
 * Note: This function is not in this location!
 * this code is CMS specific and so located in:-
 * <root>/<cmsfolder>/classes/RM/CMS/Controller/Router/Route.php
 *
 * JSLint.com Check: 18/03/2011
*/
RM.Common.Date = {};
RM.Common.Date.IsLeap = function(year){
    return (((0 === (year%4)) && ( (0 !== (year%100)) || (0 === (year%400)))));
};
RM.Common.Date.MonthsDays = [31,28,31,30,31,30,31,31,30,31,30,31];
/**
 * Returns number of days in given year
 * @param year
 */
RM.Common.Date.GetYearDays = function(year) {
    if (RM.Common.Date.IsLeap(year)) {return 366;}
    return 365;
};

RM.Common.updateUnreadResevations = function(){
    var conn = new Ext.data.Connection();
    var request = {
        url: RM.Common.AssembleURL({
            controller : 'Reservations',
            action: 'getunreadJson'
        }),
        method: 'POST',
        success: function(responseObject) {
            eval('var jsonObject = '+responseObject.responseText);
            if (jsonObject.unread > 0){
                Ext.getCmp("rm_main_tree_menu")
                    .root
                    .findChild("id", "Reservations_ListJson_NoAjax")
                    .setText(RM.Translate.Common.Reservations+"&nbsp;<span class='RM_Menu_Badge_Count'><div class='RM_Menu_Badge_Count_text'>"+jsonObject.unread+"</div></span><span class='RM_Menu_Badge_Count_right'></span>");
            }
        }
    };
    conn.request(request);
};

RM.Common.getToolbar = function(buttons){
    if (buttons){
        var html = "<table class='RM_ToolBar_Table' align='right'><tr>";
        var i = 0;for (i; i < buttons.length; i++ ){
            if (buttons[i].link===""){
                html = html + "<td class='RM_ToolBar_TD' height='42'><img src='"+buttons[i].image+"' /></td>";
            } else {
                html = html + "<td class='RM_ToolBar_TD' height='42'><a href='javascript:"+buttons[i].link+"'><img src='"+buttons[i].image+"' alt='"+buttons[i].label+"'/></a><br/>"+buttons[i].label+"</td>";
            }
        }
        html = html + "</tr></table>";
        return html;
    }
};

RM.Common.JSON = {};
RM.Common.JSON.decode = function(jsonString, redirect){
    //TODO: we need to remove this line when we will be ready to "beta" and also we need to
    //find a way to create some kind of code for every AJAX depended component of Ext.js
    redirect = false;
    var jsonObject;

    if (redirect) {
        try {
            jsonObject = Ext.util.JSON.decode(jsonString);
        } catch (e) {
            Ext.MessageBox.alert('Error', RM.Translate.Common.SessionExpires, function(){
                window.location = RM.BaseURL;
            });
        }
    } else {
        jsonObject = Ext.util.JSON.decode(jsonString);
    }
    return jsonObject;
};

/*
 * This calculates the main gui height required.
 */
RM.Common.MainHeight = function (){
    return Ext.getBody().getViewSize().height - RM.ViewPortSize.top - RM.ViewPortSize.bottom;
};

/*
 * This function returns the calculated panel size for the placement of the grid bar etc.
 */
RM.Common.GetPanelHeight = function (offset){

    var mainheight = RM.Common.MainHeight();

    var h;

    try {
        // we have to do a try here as this container is not always ready
        // and this stops us getting errors
        if (Ext.getCmp('root_gui_container')) {
            h = Ext.getCmp('root_gui_container').getHeight - offset;
        }

        if (!h){
            h = mainheight - offset;
        }
    } catch(err) {
        h = mainheight - offset;
    }

    return h;
};

RM.Common.MainTreePanelHeight = function (offset){
    if (!offset){
        offset = 0.7;
    }
    return (Ext.getBody().getViewSize().height  - RM.ViewPortSize.top - RM.ViewPortSize.bottom - 83) * offset;
};

/* JS InArray Search
 *
 * @return  boolean True if found
 * @todo I think we need to check this constuction: "if (SearchString in ArrayVar)"
 */
RM.Common.InArray = function (SearchString, ArrayVar){
    var i = 0;for(i; i < ArrayVar.length; i++){
        if(ArrayVar[i] === SearchString) {return true;}
    }
    return false;
};

/* JS Remove String from Array
 *
 * @return  Array
 */
RM.Common.RemoveFromArray = function(SearchString, ArrayVar) {
    var j = 0;
    while (j < ArrayVar.length) {
        if (ArrayVar[j] === SearchString) {
            ArrayVar.splice(j, 1);
        } else {
            j++;
        }

    }
    return ArrayVar;
};

/* JS Counts the number of Days between two dates.
 *
 * @return  Numeric
 */
RM.Common.NumberofDays = function(Date1, Date2){

    var ONE_DAY = 1000 * 60 * 60 * 24;
    var date1_ms = Date1.getTime();
    var date2_ms = Date2.getTime();
    var difference_ms = Math.abs(date1_ms - date2_ms);

    return Math.round(difference_ms/ONE_DAY);
};

/* JS JSON Numeric List...
 *
 * @return  JSON
 */
RM.Common.Combo_Number_Data = function(startvalue,endvalue,incrementvalue){
    var data = [];
    for (i=startvalue;i<=endvalue;i=i+incrementvalue){
        data.push([i,i]);
    }

    return data; // returns an an array
};

/**
 * Clone Function
 */
RM.Common.Clone = function(o) {
    if(!o || 'object' !== typeof o) {
        return o;
    }
    var c = '[object Array]' === Object.prototype.toString.call(o) ? [] : {};
    var p, v;
    for(p in o) {
        if(o.hasOwnProperty(p)) {
            v = o[p];
            if(v && 'object' === typeof v) {
                c[p] = RM.Common.Clone(v);
            }
            else {
                c[p] = v;
            }
        }
    }
    return c;
}; // eo function clone

/*
 * inarray searches for a value in an array and returns true/false
 */
RM.Common.InArray = function(value, values){
    var i = 0;for (i; i < values.length; i++){
        if (value === values[i]) {
            return true;
        }
    }
    return false;
};

/*
 * remove old form elements. This is used for form reloads
 */
RM.Common.RemoveOldFields = function(formObjId){
    var fo = Ext.getCmp(formObjId);
    if (fo) {
        if (fo.label) {fo.label.remove();}
        fo.destroy();
    }
}; // eo function RemoveOldFieilds

RM.Common.ConvertToDate = function(MySQLDateString){
    var dateParts = MySQLDateString.split(" ");
    var dateChunks;
    var timeChunks;

    if (dateParts.length === 2) {
        dateChunks = dateParts[0].split("-");
        timeChunks = dateParts[1].split(":");
    } else {
        dateChunks = MySQLDateString.split("-");
        timeChunks = [0, 0];
    }
    return new Date(dateChunks[0], dateChunks[1]-1, dateChunks[2], timeChunks[0], timeChunks[1]);
};

/*
 * replaces a style element by class name
 * this is required for the calendar colors
 *
 * @params  selector        the new style selector
 * @params  color           the new CSS rules/style
 */
RM.Common.addCSSRule = function(selector, rule){
    if (document.styleSheets) {
        if (!document.styleSheets.length) {
            var head = document.getElementsByTagName('head')[0];
            head.appendChild(bc.createEl('style'));
        }

        var i = document.styleSheets.length-1;
        var ss = document.styleSheets[i];

        var l=0;
        if (ss.cssRules) {
            l = ss.cssRules.length;
        } else if (ss.rules) {
            // IE
            l = ss.rules.length;
        }

        if (ss.insertRule) {
            ss.insertRule(selector + ' {' + rule + '}', l);
        } else if (ss.addRule) {
            // IE
            ss.addRule(selector, rule, l);
        }
    }
};

/*
 * Create a random number with upper and lower bounds
 */
RM.Common.rand = function (l, u) {
     return Math.floor((Math.random() * (u-l+1))+l);
};

/*
 * JS Equivalent to PHP explode
 *
 * creates an array from a string
 */
RM.Common.explode = function(delimiter,varstring) {
    tempArray=new Array(1);
    var Count=0;
    var tempString=new String(varstring);

    while (tempString.indexOf(delimiter)>0) {
        tempArray[Count]=tempString.substr(0,tempString.indexOf(delimiter));
        tempString=tempString.substr(tempString.indexOf(delimiter)+1,tempString.length-tempString.indexOf(delimiter)+1);
        Count=Count+1;
    }

    tempArray[Count]=tempString;
    return tempArray;
};

/*
 * JS Equivalent to PHP implode
 *
 * Creates a string from an Array
 */
RM.Common.implode = function(seperator, arrayVar){
    return arrayVar.join(seperator);
};

RM.Common.Message = function(){
    var msgCt;

    function createBox(t, s){
        return ['<div class="msg">',
                '<div class="x-box-tl"><div class="x-box-tr"><div class="x-box-tc"></div></div></div>',
                '<div class="x-box-ml"><div class="x-box-mr"><div class="x-box-mc"><h3>', t, '</h3>', s, '</div></div></div>',
                '<div class="x-box-bl"><div class="x-box-br"><div class="x-box-bc"></div></div></div>',
                '</div>'].join('');
    };
    return {
        msg : function(title, format, delay, anchorDiv){
            msgCt = Ext.DomHelper.insertFirst(document.body, {id: anchorDiv}, true);
            msgCt.alignTo(document, 't-t');
            var s = String.format.apply(String, Array.prototype.slice.call(arguments, 1));
            var m = Ext.DomHelper.append(msgCt, {html:createBox(title, s)}, true);
            m.slideIn('t').pause(delay).ghost("t", {remove:true});
        }
    };
}();

/**
 * Returns time values in an array for combo boxes
 */
RM.Common.TimeArray = function(format){

    if (format === "" || format === null){
        format = "12h"; //default format
    }

    var AM = RM.Translate.Common.AM;
    var PM = RM.Translate.Common.PM;

    var hour = "";
    var min = "";
    var h,endh, hstr, mstr, hourcount = 0, resultArray = [], loopcount = 0;

    // hours
    if (format == "12h"){
        // 12 hour format (starts at 1)
        h = 1;
        endh = 24;
    } else {
        // 24 hour format (starts at 0)
        h = 0;
        endh = 23;
    }

    for (h; h <= endh; h++){

        if (format == "12h"){
            if (hourcount == 13){
                hourcount = 1;
            }
        }

        hstr = hourcount + "";
        if (hstr.length < 2){
            hourcount = '0' + hstr;
        } else {
            hourcount = hstr;
        }


        var m = 0;
        for (m; m <= 45; m+=15){
            mstr = m + "";
            if (mstr.length < 2){
                min = '0' + mstr;
            } else {
                min = mstr;
            }

            // add AM/PM marker
            loopcount +=1;

            if (format == "12h"){
                if (h < 12){
                    resultArray.push([hourcount + ":" + min + " AM", hourcount + ":" + min + " " + AM]);
                } else {
                    if (loopcount<93){
                        resultArray.push([hourcount + ":" + min + " PM", hourcount + ":" + min + " " + PM]);
                    } else {
                        resultArray.push([hourcount + ":" + min + " AM", hourcount + ":" + min + " " + AM]);
                    }
                }
            } else {
                resultArray.push([hourcount + ":" + min, hourcount + ":" + min]);
            }
        }

        hourcount++;
        
    }

    //var jsonObject = Ext.util.JSON.encode(resultArray);
    return resultArray;
};