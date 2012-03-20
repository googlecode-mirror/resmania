Ext.namespace('Ext.rm', 'Ext.rm.form');

Ext.rm.form.DateField = Ext.extend(Ext.ux.form.DateFieldPlus, {
    disabledDatePeriods : [],
    disabledDatesRE: [],

    initComponent : function(){
        this.setDisabledPeriods(this.disabledDatePeriods);
        //this.disabledDates(this.disabledDatesRE);
        Ext.rm.form.DateField.superclass.initComponent.call(this);                
    },   

    setDisabledPeriods : function(periods){
        //We need to remove already assigned 'disabledPeriods'
        this.clearDisabledPeriods();
        
        for(var i = 0; i < periods.length; i++){            
            this.addDisabledPeriod(periods[i]);
        }
        if (this.disabledDatesRE.length>0){
            this.disabledDates = this.disabledDatesRE;
        }
    },

    clearDisabledPeriods : function(){
        this.disabledDatesRE = [];
    },

    addDisabledPeriod : function(period){
        
        var startDate = RM.Common.ConvertToDate(period.start);
        var endDate = RM.Common.ConvertToDate(period.end);
        while (startDate.getTime() < endDate.getTime()) {
            //this.disabledDatesRE.push(startDate.dateFormat(this.format));
            this.disabledDatesRE.push(startDate.clone());
            startDate.setTime(startDate.getTime() + 1000 * 60 * 60 * 24);
        }        
    }
});

Ext.reg('rmdatefield', Ext.rm.form.DateField);