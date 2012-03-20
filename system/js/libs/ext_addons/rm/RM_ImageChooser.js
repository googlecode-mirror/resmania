///*
// * Ext JS Library 2.2.1
// * Copyright(c) 2006-2009, Ext JS, LLC.
// * licensing@extjs.com
// *
// * http://extjs.com/license
// */
//
///*
// * Ext JS Library 2.0
// * Copyright(c) 2006-2007, Ext JS, LLC.
// * licensing@extjs.com
// *
// * http://extjs.com/license
// */
//
//var RM_ImageChooser = function(config){
//	this.config = config;
//}
//
//RM_ImageChooser.prototype = {
//    // cache data by image name for easy lookup
//    lookup : {},
//
//	show : function(el, callback){
//		if(!this.win){
//			this.initTemplates();
//
//			var store = new Ext.data.JsonStore({
//			    url: this.config.url,
//			    root: 'images',
//			    fields: [
//			        'name', 'url',
//			        {name:'size', type: 'float'},
//			        {name:'lastmod', type:'date', dateFormat:'timestamp'}
//			    ],
//			    listeners: {
//			    	'load': {
//                        fn:function(){
//                            this.view.select(0);
//                        },
//                        scope:this,
//                        single:true
//                    }
//			    }
//			});
//            this.store = store;
//			this.store.load();
//
//			var formatSize = function(data){
//		        if(data.size < 1024) {
//		            return data.size + " bytes";
//		        } else {
//		            return (Math.round(((data.size*10) / 1024))/10) + " KB";
//		        }
//		    };
//
//			var formatData = function(data){
//		    	data.shortName = data.name.ellipse(15);
//		    	data.sizeString = formatSize(data);
//		    	data.dateString = new Date(data.lastmod).format("m/d/Y g:i a");
//		    	this.lookup[data.name] = data;
//		    	return data;
//		    };
//
//		    this.view = new Ext.DataView({
//				tpl: this.thumbTemplate,
//				singleSelect: true,
//				overClass:'x-view-over',
//				itemSelector: 'div.thumb-wrap',
//				emptyText : '<div style="padding:10px;">No images match the specified filter</div>',
//				store: this.store,
//				listeners: {
//					'selectionchange': {fn:this.showDetails, scope:this, buffer:100},
//					'dblclick'       : {fn:this.doCallback, scope:this},
//					'loadexception'  : {fn:this.onLoadException, scope:this},
//					'beforeselect'   : {fn:function(view){
//				        return view.store.getRange().length > 0;
//				    }}
//				},
//				prepareData: formatData.createDelegate(this)
//			});
//
//			var cfg = {
//		    	title: RM.Translate.Admin.System.MediaManager.Title,
//		    	id: 'img-chooser-dlg',
//		    	layout: 'border',
//				minWidth: 500,
//				minHeight: 300,
//				//modal: true,
//				closeAction: 'hide',
//				border: false,
//				items:[{
//					id: 'img-chooser-view',
//					region: 'center',
//					autoScroll: true,
//					items: this.view,
//                    tbar:[{
//                    	text: RM.Translate.Admin.System.MediaManager.Filter
//                    },{
//                    	xtype: 'textfield',
//                    	id: 'filter',
//                    	selectOnFocus: true,
//                    	width: 100,
//                    	listeners: {
//                    		'render': {fn:function(){
//						    	Ext.getCmp('filter').getEl().on('keyup', function(){
//						    		this.filter();
//						    	}, this, {buffer:500});
//                    		}, scope:this}
//                    	}
//                    }, ' ', '-', {
//                    	text: RM.Translate.Admin.System.MediaManager.SortBy
//                    }, {
//                    	id: 'sortSelect',
//                    	xtype: 'combo',
//				        typeAhead: true,
//				        triggerAction: 'all',
//				        width: 100,
//				        editable: false,
//				        mode: 'local',
//				        displayField: 'desc',
//				        valueField: 'name',
//				        lazyInit: false,
//				        value: 'name',
//				        store: new Ext.data.SimpleStore({
//					        fields: ['name', 'desc'],
//					        data : [
//                                ['name', RM.Translate.Admin.System.MediaManager.Name],
//                                ['size', RM.Translate.Admin.System.MediaManager.FileSize],
//                                ['lastmod', RM.Translate.Admin.System.MediaManager.LastModified]
//                            ]
//					    }),
//					    listeners: {
//							'select': {fn:this.sortImages, scope:this}
//					    }
//				    }],
//                    bbar: [{
//                        id : 'img-upload-panel',
//                        fileUpload: true,
//                        xtype: 'form',
//                        url : RM.Common.AssembleURL({
//                            controller: 'system',
//                            action: 'mediamanageruploadjson'
//                        }),
//                        items: [{
//                            xtype : 'fileuploadfield',
//                            id: 'img-upload-panel-file',
//                            width: 300,
//                            height: 20,
//                            emptyText: RM.Translate.Admin.System.MediaManager.ChooseImage,
//                            fieldLabel: RM.Translate.Admin.System.MediaManager.Image,
//                            name: 'media_manager_upload'
//                        }]
//                    },{
//                        xtype: 'button',
//                        text: RM.Translate.Common.Upload,
//                        handler: function(){
//                            if(Ext.getCmp('img-upload-panel').getForm().isValid()){
//                                Ext.getCmp('img-upload-panel').getForm().submit({
//                                    success: function(form, action) {
//                                        store.load();
//                                        Ext.Msg.alert(RM.Translate.Common.Success, RM.Translate.Admin.System.MediaManager.UploadSuccess);
//                                    },
//                                    failure: function(form, action){
//                                        Ext.Msg.alert(RM.Translate.Common.Success, RM.Translate.Admin.System.MediaManager.UploadFailure);
//                                    },
//                                    waitMsg: RM.Translate.Common.Uploading,
//                                    waitTitle: RM.Translate.Common.PleaseWait
//                                });
//                            }
//                        }
//                    }]
//				},{
//					id: 'img-detail-panel',
//					region: 'east',
//					split: true,
//					width: 280,
//					minWidth: 280,
//					maxWidth: 300
//				}],
//				buttons: [{
//					id: 'ok-btn',
//					text: RM.Translate.Admin.System.MediaManager.Ok,
//					handler: this.doCallback,
//					scope: this
//				},{
//					text: RM.Translate.Admin.System.MediaManager.Cancel,
//					handler: function(){ this.win.hide(); },
//					scope: this
//				}],
//				keys: {
//					key: 27, // Esc key
//					handler: function(){ this.win.hide(); },
//					scope: this
//				}
//			};
//			Ext.apply(cfg, this.config);
//		    this.win = new Ext.Window(cfg);
//		}
//
//		this.reset();
//	    this.win.show(el);
//		this.callback = callback;
//		this.animateTarget = el;
//	},
//
//	initTemplates : function(){
//		this.thumbTemplate = new Ext.XTemplate(
//			'<tpl for=".">',
//				'<div class="thumb-wrap" id="{name}">',
//				'<div class="thumb"><img src="{url}" title="{name}"></div>',
//				'<span>{shortName}</span></div>',
//			'</tpl>'
//		);
//		this.thumbTemplate.compile();
//
//		this.detailsTemplate = new Ext.XTemplate(
//			'<div class="details">',
//				'<tpl for=".">',
//					'<img src="{url}"><div class="details-info">',
//					'<b>'+RM.Translate.Admin.System.MediaManager.ImageName+':</b>',
//					'<span>{name}</span>',
//					'<b>'+RM.Translate.Admin.System.MediaManager.Size+':</b>',
//					'<span>{sizeString}</span>',
//					'<b>'+RM.Translate.Admin.System.MediaManager.LastModified+':</b>',
//					'<span>{dateString}</span></div>',
//				'</tpl>',
//			'</div>'
//		);
//		this.detailsTemplate.compile();
//	},
//
//	showDetails : function(){
//	    var selNode = this.view.getSelectedNodes();
//	    var detailEl = Ext.getCmp('img-detail-panel').body;
//		if(selNode && selNode.length > 0){
//			selNode = selNode[0];
//			Ext.getCmp('ok-btn').enable();
//		    var data = this.lookup[selNode.id];
//            detailEl.hide();
//            this.detailsTemplate.overwrite(detailEl, data);
//            detailEl.slideIn('l', {stopFx:true,duration:.2});
//		}else{
//		    Ext.getCmp('ok-btn').disable();
//		    detailEl.update('');
//		}
//	},
//
//	filter : function(){
//		var filter = Ext.getCmp('filter');
//		this.view.store.filter('name', filter.getValue());
//		this.view.select(0);
//	},
//
//	sortImages : function(){
//		var v = Ext.getCmp('sortSelect').getValue();
//    	this.view.store.sort(v, v == 'name' ? 'asc' : 'desc');
//    	this.view.select(0);
//    },
//
//	reset : function(){
//		if(this.win.rendered){
//			Ext.getCmp('filter').reset();
//			this.view.getEl().dom.scrollTop = 0;
//		}
//	    this.view.store.clearFilter();
//		this.view.select(0);
//	},
//
//	doCallback : function(){
//        var selNode = this.view.getSelectedNodes()[0];
//		var callback = this.callback;
//		var lookup = this.lookup;
//		this.win.hide(this.animateTarget, function(){
//            if(selNode && callback){
//				var data = lookup[selNode.id];
//				callback(data);
//			}
//		});
//    },
//
//	onLoadException : function(v,o){
//	    this.view.getEl().update('<div style="padding:10px;">'+RM.Translate.Admin.System.MediaManager.Error+'</div>');
//	}
//};