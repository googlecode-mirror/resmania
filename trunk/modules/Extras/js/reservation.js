/*
* Extras Module Reservation Edit JS
* This creates the admin GUI Notifications List Page
*
* JSLint.com Check: 18/03/2011
*/
RM.Pages.Functions.Extras_Reservation_Edit_Extras_Changed = function(id, detail_id, value){
    myMask = new Ext.LoadMask('content-panel', {msg:RM.Translate.Common.PleaseWait});
    myMask.show();
    var request = {
        url : RM.Common.AssembleURL({
            controller: 'Extras',
            action: 'calculateJson'
        }),
        params: {
            extra_id: id,
            detail_id : detail_id,
            value: value
        },
        method: 'POST',
        success: function(responseObject) {
            myMask.hide();
            var jsonObject = RM.Common.JSON.decode(responseObject.responseText, true);
            if (jsonObject.success){
                Ext.getDom('rm_extra_new_price_'+detail_id+'_'+id).innerHTML = jsonObject.value;
            } else {
                Ext.Msg.alert('Status', RM.Translate.Admin.Extras.Reservation.UnableToCalculate);
            }
        },
        failure: function() {
            Ext.Msg.alert('Status', RM.Translate.Common.UnableToShow);
            myMask.hide();
        }
    };

    var conn = new Ext.data.Connection();
    conn.request(request);    
};

RM.Pages.Functions.Extras_Reservation_Edit_Update = function(){    
    var selects = Ext.query('select[name="rm_extras"]');
    var ids = [];
    var i = 0;for (i; i < selects.length; i++){
        var selectedExtra = selects[i];
        var j = 0;for (i; j < RM.Pages.Extras_Reservation_Current.length; j++){
            var detail = RM.Pages.Extras_Reservation_Current[j];
            var newExtras = [];
            var k = 0;for (k; k < detail.extras.length; k++){
                var currentExtra = detail.extras[k];
                if (selectedExtra.id !== 'rm_extras_' + detail.id + '_' + currentExtra.id) {
                    continue;
                }
                if (selectedExtra.value !== currentExtra.value) {
                    newExtras.push({
                        id: currentExtra.id,
                        value: selectedExtra.value
                    });
                }
            }
            if (newExtras.length > 0) {
                ids.push({
                    id: detail.id,
                    extras: newExtras
                });
            }
        }        
    }

    if (ids.length === 0){
        Ext.Msg.alert(RM.Translate.Common.Status, RM.Translate.Admin.Extras.Reservation.NoChanges);
        return;
    }

    myMask = new Ext.LoadMask('content-panel', {msg:RM.Translate.Common.PleaseWait});
    myMask.show();
    var request = {
        url : RM.Common.AssembleURL({
            controller: 'Extras',
            action: 'reservationupdateJson'
        }),
        params: {
            ids: Ext.util.JSON.encode(ids)
        },
        method: 'POST',
        success: function(responseObject) {
            myMask.hide();
            RM.Pages.Functions.Reservations_EditJson_Request(RM.Pages.Reservations_Edit_ReservationID);
        },
        failure: function() {
            Ext.Msg.alert('Status', RM.Translate.Common.UnableToShow);
            myMask.hide();
        }
    };

    var conn = new Ext.data.Connection();
    conn.request(request);    
};

RM.Pages.Extras_Reservation_Edit_Toolbar = {
    xtype : "panel",
    id : "rm_pages_extras_reservation_edit_toolbar",
    bodyBorder : false,
    html : RM.Common.getToolbar([{image: RM.BaseLargeImageURL+"save.gif", label: RM.Translate.Admin.Extras.Reservation.UpdateExtras, link: "RM.Pages.Functions.Extras_Reservation_Edit_Update()"}])
};
RM.Toolbars.push(RM.Pages.Extras_Reservation_Edit_Toolbar);

RM.Pages.Extras_Reservation_Current = [];
RM.Pages.Extras_Reservation_Edit_Load = function(){
    myMask = new Ext.LoadMask('content-panel', {msg:RM.Translate.Common.PleaseWait});
    myMask.show();
    var request = {
        url : RM.Common.AssembleURL({
            controller: 'Extras',
            action: 'listhtmlJson',
            parameters : [{
                name : 'id',
                value : RM.Pages.Reservations_Edit_ReservationID
            }]
        }),
        method: 'POST',
        success: function(responseObject) {
            var response = Ext.util.JSON.decode(responseObject.responseText);
            RM.Pages.Extras_Reservation_Current = response.details;
            Ext.getCmp('rm_extras_reservation_edit_panel').body.update(
                RM.Pages.Extras_Reservation_Edit_Template.applyTemplate(response)
            );
            myMask.hide();
        },
        failure: function() {
            Ext.Msg.alert('Status', RM.Translate.Common.UnableToShow);
            myMask.hide();
        }
    };

    var conn = new Ext.data.Connection();
    conn.request(request);           
};
   
RM.Pages.Extras_Reservation_Edit_Template = new Ext.XTemplate(
    '<table width="100%" cellpadding="5" cellspacing="5">',
        '<tpl for="details">',
        '<tr>',            
            '<td colspan="5">'
            +RM.Translate.Common.Unit+':&nbsp;<b>{unit_name}({unit_id})</b>&nbsp;&nbsp;'
            +RM.Translate.Common.StartDatetime+':&nbsp<b>{start}</b>&nbsp;&nbsp;'
            +RM.Translate.Common.EndDatetime+':&nbsp;<b>{end}</b>&nbsp;&nbsp;'
            +RM.Translate.Admin.Reservations.Edit.SubTotal+':&nbsp;<b>{subtotal}</b>',
            '</td>',
        '</tr>',
        '<tr>',
            '<td width="20%">&nbsp;</td>',
            '<td width="25%"><b>'+RM.Translate.Admin.Extras.Reservation.PricePerItem+'</b></td>',
            '<td width="15%"><b>'+RM.Translate.Admin.Extras.Reservation.NumberItemsSelected+'</b></td>',
            '<td width="20%"><b>'+RM.Translate.Admin.Extras.Reservation.NewSelectedSubtotal+'</b></td>',
            '<td width="20%"><b>'+RM.Translate.Admin.Extras.Reservation.SavedSubTotal+'</b></td>',
        '</tr>',
            '<tpl for="extras">',
            '<tr>',
                '<td><b>{name}</b></td>',
                '<td>{price} ({type})</td>',
                '<td>',
                    '{[this.renderSelect(values.id, parent.id, values.min, values.max, values.value)]}',
                '</td>',
                '<td><span id="rm_extra_new_price_{parent.id}_{id}">0</span></td>',
                '<td><span>{saved_price}</span></td>',
            '</tr>',
            '</tpl>',
        '<tr>',
            '<td colspan="5">&nbsp;</td>',
        '</tr>',
        '</tpl>',
    '</table>',
    {
        renderSelect: function(id, detail_id, min, max, value) {
            var html = "<select id='rm_extras_"+detail_id+"_"+id+"' name='rm_extras' onchange='RM.Pages.Functions.Extras_Reservation_Edit_Extras_Changed("+id+", "+detail_id+",this.value)'>";
            html = html + "<option value='0' "+ ((value === 0) ? "selected='selected'" : "") +" >0</option>";

            if (value !== 0 && value < min) {
                html = html + "<option value='" + value + "' selected='selected' >" + value + "</option>";
            }
            
            var i = min;for (min; i <= max; i++ ) {
                if (i === 0) {
                    continue;
                }
                html = html + "<option value='" + i + "' "+ ((value === i) ? "selected='selected'" : "") +" >" + i + "</option>";
            }

            if (value > max) {
                html = html + "<option value='" + value + "' selected='selected' >" + value + "</option>";
            }

            html = html + '</select>';            
            return html;
        }
    }
);

RM.Pages.Extras_Reservation_Edit = new Ext.Panel({
    id: 'rm_extras_reservation_edit_panel',
    title: RM.Translate.Admin.Extras.Reservation.Tabtitle,
    bodyStyle : "padding:10px",
    autoScroll: true,
    border: false,
    listeners: {
        'beforerender' : function(){
            RM.Pages.Extras_Reservation_Edit_Load();
            return true;
        },
        'beforehide' : function(){
            Ext.getCmp('toolbar_panel').layout.setActiveItem('rm_pages_reservations_edit_toolbar');
            return true;
        },
        'beforeshow' : function(){
            Ext.getCmp('toolbar_panel').layout.setActiveItem('rm_pages_extras_reservation_edit_toolbar');
            RM.Pages.Extras_Reservation_Edit_Load();
            return true;
        }
    }
});

RM.Pages.Reservations_Edit_TabPanel.add(RM.Pages.Extras_Reservation_Edit);