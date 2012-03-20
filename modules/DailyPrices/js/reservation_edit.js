/*
* Daily Prices Reservation Include Edit JS
*
* JSLint.com Check: 18/03/2011
*/
RM.Pages.Functions.DailyPrices_ReservationEdit = function(UnitID, FormID, data){

    return new Ext.Panel({
        hidden: true,
        items : [{
            id : FormID+"persons_adults",
            xtype : "hidden",
            value: 1
        },{
            id : FormID+"persons_children",
            xtype : "hidden",
            value: 0
        },{
            id : FormID+"persons_infants",
            xtype : "hidden",
            value: 0
        }]
    });
};