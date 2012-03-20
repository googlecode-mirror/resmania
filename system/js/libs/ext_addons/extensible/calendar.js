/**
 * Enhanced Calendar Main JS file
 *
 * @access      public
 * @author      Rob Locke
 * @copyright   ResMania 2011 all rights reserved.
 * @version     1.0
 * @link        http://docs.resmania.com/index.php?title=Enhanced_Calendar
 * @since       03-2011
 * @notes       jslint:
 */

Ext.onReady(function(){

    RM.Pages.EnhancedCalendar_SelectedView = 3; // week view as the initial view

    Ext.override(Ext.ensible.cal.EventContextMenu,{
        /**
         * override the calendar event context menu (right click)
         */
        buildMenu: function(){
            Ext.apply(this, {
                items: []
            });
        }
    });

    Ext.override(Ext.ensible.cal.DateRangeField,{
        onRender: function(ct, position){
            if(!this.el){

                // this is passed from the price system, if the price system does
                // allow hourly selection then we disable it here.
                this.showTimes = show_time;

                this.startDate = new Ext.form.DateField({
                    id: this.id+'-start-date',
                    format: this.dateFormat,
                    width:100,
                    listeners: {
                        'change': {
                            fn: function(){
                                this.onFieldChange('date', 'start');
                            },
                            scope: this
                        }
                    }
                });
                this.startTime = new Ext.form.TimeField({
                    id: this.id+'-start-time',
                    hidden: this.showTimes === false,
                    labelWidth: 0,
                    hideLabel:true,
                    width:90,
                    listeners: {
                        'select': {
                            fn: function(){
                                this.onFieldChange('time', 'start');
                            },
                            scope: this
                        }
                    }
                });
                this.endTime = new Ext.form.TimeField({
                    id: this.id+'-end-time',
                    hidden: this.showTimes === false,
                    labelWidth: 0,
                    hideLabel:true,
                    width:90,
                    listeners: {
                        'select': {
                            fn: function(){
                                this.onFieldChange('time', 'end');
                            },
                            scope: this
                        }
                    }
                });
                this.endDate = new Ext.form.DateField({
                    id: this.id+'-end-date',
                    format: this.dateFormat,
                    hideLabel:true,
                    width:100,
                    listeners: {
                        'change': {
                            fn: function(){
                                this.onFieldChange('date', 'end');
                            },
                            scope: this
                        }
                    }
                });
                this.allDay = new Ext.form.Checkbox({
                    id: this.id+'-allday',
                    hidden: this.showTimes === false || this.showAllDay === false,
                    boxLabel: this.allDayText,
                    handler: function(chk, checked){
                        this.startTime.setVisible(!checked);
                        this.endTime.setVisible(!checked);
                    },
                    scope: this
                });
                this.toLabel = new Ext.form.Label({
                    xtype: 'label',
                    id: this.id+'-to-label',
                    text: this.toText
                });

                var singleLine = this.singleLine;
                if(singleLine === 'auto'){
                    var el, w = this.ownerCt.getWidth() - this.ownerCt.getEl().getPadding('lr');
                    if(el = this.ownerCt.getEl().child('.x-panel-body')){
                        w -= el.getPadding('lr');
                    }
                    if(el = this.ownerCt.getEl().child('.x-form-item-label')){
                        w -= el.getWidth() - el.getPadding('lr');
                    }
                    singleLine = w <= this.singleLineMinWidth ? false : true;
                }

                this.fieldCt = new Ext.Container({
                    autoEl: {id:this.id}, //make sure the container el has the field's id
                    cls: 'ext-dt-range',
                    renderTo: ct,
                    layout: 'table',
                    layoutConfig: {
                        columns: singleLine ? 6 : 3
                    },
                    defaults: {
                        hideParent: true
                    },
                    items:[
                        this.startDate,
                        this.startTime,
                        this.toLabel,
                        singleLine ? this.endTime : this.endDate,
                        singleLine ? this.endDate : this.endTime,
                        this.allDay
                    ]
                });

                this.fieldCt.ownerCt = this;
                this.el = this.fieldCt.getEl();
                this.items = new Ext.util.MixedCollection();
                this.items.addAll([this.startDate, this.endDate, this.toLabel, this.startTime, this.endTime, this.allDay]);
            }

            Ext.ensible.cal.DateRangeField.superclass.onRender.call(this, ct, position);

            if(!singleLine){
                this.el.child('tr').addClass('ext-dt-range-row1');
            }
        }
    });

   // override the Extensibe Calendar to provide our own new reservsstion form.
    Ext.override(Ext.ensible.cal.EventEditWindow,{
        onRender : function(ct, position){

            this.titleTextAdd = RM.Translate.User.EnhancedCalendar.NewReservation;

            this.deleteBtn = Ext.getCmp(this.id+'-delete-btn');
                       
            this.titleField = new Ext.form.TextField({
                name: Ext.ensible.cal.EventMappings.Title.name,
                fieldLabel: this.titleLabelText,
                anchor: '100%'
            });

            this.dateRangeField = new Ext.ensible.cal.DateRangeField({
                anchor: '95%',
                fieldLabel: this.datesLabelText,
                dateFormat: RM.Common.GUIDateFormat
            });

            var items = [this.dateRangeField];

            var hidePersonsFeildset = false;

            // persons selection
            if (adults>1){
                // adults

                hidePersonsFeildset = true;

                this.adultsField = new Ext.form.ComboBox ({
                    id : "adultsValue",
                    name : "adultsValue_name",
                    hiddenName : "adultsValue_hidden",
                    width : 50,
                    triggerAction: 'all',
                    fieldLabel : RM.Translate.User.DatePicker.Adults,
                    store : new Ext.data.SimpleStore({
                        fields:[
                        {
                            name:'value',
                            type:'string'
                        },

                        {
                            name:'text',
                            type:'string'
                        }
                        ],
                        data: RM.Common.Combo_Number_Data(1,adults,1)
                    }),
                    value: 1,
                    mode: 'local',
                    typeAhead: true,
                    resizable: false,
                    valueField: 'value',
                    displayField: 'text'
                });

            } else {
                this.adultsField = new Ext.form.Hidden ({
                    id : "adultsValue",
                    value: 1
                });
            }

            // children
            if (children>0){
                this.childrenField = new Ext.form.ComboBox ({
                    id : "childrenValue",
                    name : "childrenValue_name",
                    hiddenName : "childrenValue_hidden",
                    width : 50,
                    triggerAction: 'all',
                    fieldLabel : RM.Translate.User.DatePicker.Children,
                    store : new Ext.data.SimpleStore({
                        fields:[
                        {
                            name:'value',
                            type:'string'
                        },

                        {
                            name:'text',
                            type:'string'
                        }
                        ],
                        data: RM.Common.Combo_Number_Data(1,children,1)
                    }),
                    mode: 'local',
                    typeAhead: true,
                    resizable: false,
                    valueField: 'value',
                    displayField: 'text',
                    value: 0
                });


            } else {
                this.childrenField = new Ext.form.Hidden ({
                    id : "childrenValue",
                    value: 0
                });
            }

            // infants
            if (infants>0){
                this.infantsField = new Ext.form.ComboBox ({
                    id : "infantsValue",
                    name : "infantsValue_name",
                    hiddenName : "infantsValue_hidden",
                    width : 50,
                    triggerAction: 'all',
                    fieldLabel : RM.Translate.User.DatePicker.Infants,
                    store : new Ext.data.SimpleStore({
                        fields:[
                        {
                            name:'value',
                            type:'string'
                        },

                        {
                            name:'text',
                            type:'string'
                        }
                        ],
                        data: RM.Common.Combo_Number_Data(1,infants,1)
                    }),
                    mode: 'local',
                    typeAhead: true,
                    resizable: false,
                    valueField: 'value',
                    displayField: 'text',
                    value: 0
                });

            } else {
                this.infantsField = new Ext.form.Hidden ({
                    id : "infantsValue",
                    value: 0
                });
            }

            if (hidePersonsFeildset){
                this.personsFeildset = new Ext.form.FieldSet({
                    autoHeight: true,
                    layout: 'form',
                    autoWidth: true,
                    bodyBorder : false,
                    labelWidth: 190,
                    items:[
                        this.adultsField,
                        this.childrenField,
                        this.infantsField
                    ]
                });

                items.push(this.personsFeildset);
            }
            // end persons selection

            if(this.calendarStore){
                this.calendarField = new Ext.ensible.cal.CalendarCombo({
                    name: Ext.ensible.cal.EventMappings.CalendarId.name,
                    hidden: true,
                    anchor: '100%',
                    labelStyle: "display: none",
                    store: this.calendarStore
                });
                items.push(this.calendarField);
            }

            this.formPanel = new Ext.FormPanel({
                labelWidth: this.labelWidth,
                frame: false,
                bodyBorder: false,
                border: false,
                items: items
            });

            this.add(this.formPanel);
            Ext.ensible.cal.EventEditWindow.superclass.onRender.call(this, ct, position);
        },
        onSave: function(){

            if(!this.formPanel.form.isValid()){
                return;
            }

            // check the number of persons selection is ok...
            var peopleSelection = parseInt( Ext.getCmp("adultsValue").getValue(), 10) +
                parseInt( Ext.getCmp("childrenValue").getValue(), 10) +
                parseInt( Ext.getCmp("infantsValue").getValue(), 10);

            if (peopleSelection > parseInt( maxOccupancy, 10 )){
                Ext.Msg.alert('', RM.Translate.User.EnhancedCalendar.ExceededMaxOccupancy.replace(/\[XX]/g, maxOccupancy));
                return;
            }

            this.titleTextAdd = RM.Translate.User.EnhancedCalendar.YourSelection;

            this.fireEvent('eventadd', this, this.activeRecord, this.animateTarget);
            this.close();
        }
    });

    

});