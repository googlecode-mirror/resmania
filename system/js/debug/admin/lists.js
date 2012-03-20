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
 * Lists
 * This creates the admin GUI Lists Parameters
 *
 * JSLint.com Check: 27/01/2010
 *
 * @access       public
 * @author       Valentin
 * @copyright    2011 ResMania Ltd.
 * @version      1.2
 * @link         http://docs.resmania.com/api/
 * @since        06-2011
 */

RM.Pages.Reservations_Edit_Json_Store_Fields = [];
RM.Pages.Reservations_Edit_Columns_Rows = [];
RM.Pages.Reservations_Edit_Columns_Editors = [];
RM.Pages.Reservations_Edit_UnitSelectionInfo = [];
RM.Common.Reservations_Edit_Setup = function(info){
    RM.Pages.Reservations_Edit_UnitSelectionInfo = info;
};

RM.Pages.Reservations_List_Json_Store_Fields = [];
RM.Pages.Reservations_List_Filters_Rows = [];
RM.Pages.Reservations_List_Columns_Rows = [];
// the info array is created from /RM/System/applications/controllers/RM/admin/ReservationsController.php function listJsAction
//'info' information is a value in JSON format of 'admin_list_preferences' field from 'rm_reservation_config' table
RM.Common.Reservations_List_Setup = function(info){
    RM.Pages.Reservations_List_Columns_Rows[0] = "";
    var i = 0;for (i; i < info.length; i++) {
        RM.Pages.Reservations_List_Json_Store_Fields[i] = info[i].field;
        RM.Pages.Reservations_List_Filters_Rows[i] = info[i].filter;
        RM.Pages.Reservations_List_Columns_Rows[i+1] = info[i].column;
    }
};

RM.Pages.Units_List_Json_Store_Fields = [];
RM.Pages.Units_List_Filters_Rows = [];
RM.Pages.Units_List_Columns_Rows = [];
// the info array is created from /RM/System/applications/controllers/RM/admin/UnitsController.php function listJsAction
//'info' information is a value in JSON format of 'admin_list_preferences' field from 'rm_unit_config' table
RM.Common.Units_List_Setup = function(info){
    RM.Pages.Units_List_Columns_Rows[0] = "";
    var i = 0;for (i; i < info.length; i++) {
        RM.Pages.Units_List_Json_Store_Fields[i] = info[i].field;
        RM.Pages.Units_List_Filters_Rows[i] = info[i].filter;
        RM.Pages.Units_List_Columns_Rows[i+1] = info[i].column;
    }
};

RM.Pages.Users_List_Json_Store_Fields = [];
RM.Pages.Users_List_Filters_Rows = [];
RM.Pages.Users_List_Columns_Rows = [];
// the info array is created from /RM/System/applications/controllers/RM/admin/UsersController.php function listJsAction
//'info' information is a value in JSON format of 'admin_list_preferences' field from 'rm_users_config' table
RM.Common.Users_List_Setup = function(info){
    RM.Pages.Users_List_Columns_Rows[0] = "";
    var i = 0;for (i; i < info.length; i++) {
        RM.Pages.Users_List_Json_Store_Fields[i] = info[i].field;
        RM.Pages.Users_List_Filters_Rows[i] = info[i].filter;
        RM.Pages.Users_List_Columns_Rows[i+1] = info[i].column;
    }
};