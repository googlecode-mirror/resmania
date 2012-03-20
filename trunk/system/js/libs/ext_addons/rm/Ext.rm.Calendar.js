Ext.namespace('Ext.rm');

Ext.rm.Calendar = Ext.extend(Ext.ux.DatePickerPlus, {
    disabledDatePeriods : [],    
    eventDatePeriods : [],    
    disabledDatesRE : [],
    eventDatesArray: [],

    initComponent : function(){
        this.setEventPeriods(this.eventDatePeriods);
        this.setDisabledPeriods(this.disabledDatePeriods);
        Ext.rm.Calendar.superclass.initComponent.call(this);
    },

    setEventPeriods : function(periods, comment){
        for(var i = 0; i < periods.length; i++){
            this.addEventPeriod(periods[i], comment);
        }
    },

    markEventPeriods : function(periods, comment){
        this.eventDatesArray = [];
        for(var i = 0; i < periods.length; i++){
            this.markPeriod(periods[i], comment);
        }
        this.setEventDates(this.eventDatesArray);
    },

    clearView : function() {
        this.disabledDatesRE = [];
        this.disabledDays = [];
        this.eventDatesArray = [];
        this.clearSelectedDates();
    },

    _convertDate : function(MySQLDateString){
        // MySQLDateString should only be passed here in SQL format ie: YYYY-mm-dd
        if (!MySQLDateString) return;
        var dateParts = MySQLDateString.split(" ");
        if (dateParts.length == 2) {
            var dateChunks = dateParts[0].split("-");
        } else {
            var dateChunks = MySQLDateString.split("-");
        }        
        return new Date(dateChunks[0], dateChunks[1]-1, dateChunks[2]);
    },

    addEventPeriod : function(period){        
        var startDate = this._convertDate(period.start_date);
        var endDate = this._convertDate(period.end_date);

        if (!startDate || !endDate) return;

        while (startDate.getTime() <= endDate.getTime()) {
            var currentDate = new Date();
            currentDate.setTime(startDate.getTime());
            this.setSelectedDates(currentDate);
            startDate.setDate(startDate.getDate() + 1);
        }       
    },

    /**
     * Mark a period on the calendar with special text
     *
     * period: {start_date: <MySQLDateString>, end_date: <MySQLDateString>}
     * comment: text
     */
    markPeriod : function(period, comment){
        var startDate = this._convertDate(period.start_date);
        var endDate = this._convertDate(period.end_date);
        var color = period.color;

        var newCSScls = "RM_calendar_event_"+RM.Common.rand(1000,9999);

        // this dynamically creates a new css style rule and overrides the existing style. Very good for changing a style dynamically.
        RM.Common.addCSSRule("."+newCSScls+" a",'background-color: #'+color+'; border:1px solid #000088; padding:1px 4px;');

        while (startDate.getTime() < endDate.getTime()) {
            this.eventDatesArray.push({
                date: startDate.clone(),
                text: comment,
                cls: newCSScls
            });
            startDate.setDate(startDate.getDate() + 1);
        }
    },    

    setDisabledPeriods : function(periods){
        //We need to remove already assigned 'disabledPeriods'
        this.disabledDatesRE = [];
        this.disabledDays = [];
        for(var i = 0; i < periods.length; i++){
            this.addDisabledPeriod(periods[i]);
        }
    },

    addDisabledPeriod : function(period){
        var startDate = this._convertDate(period.start_date);
        var endDate = this._convertDate(period.end_date);
        
        while (startDate.getTime() < endDate.getTime()) {
            var currentDate = new Date();
            currentDate.setTime(startDate.getTime());
            this.disabledDatesRE.push(currentDate);            
            startDate.setDate(startDate.getDate() + 1);
        }
    }
});

Ext.reg('rmcalendar', Ext.rm.Calendar);