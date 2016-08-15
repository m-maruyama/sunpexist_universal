define([
	'app',
	'../Templates',
	'blockUI',
	'./UnreturnedListItem'
], function(App) {
	'use strict';
	App.module('Admin.Views', function(Views, App, Backbone, Marionette, $, _){
		var order = 'asc';
		var sort_key = 'order_req_no';
		Views.UnreturnedListList = Marionette.CompositeView.extend({
			template: App.Admin.Templates.unreturnedListList,
			emptyView: Backbone.Marionette.ItemView.extend({
                tagName: "tr",
				template: App.Admin.Templates.unreturnedEmpty,
            }),
			childView: Views.UnreturnedListItem,
			childViewContainer: "tbody",
			ui: {
			},
			onRender: function() {
				this.listenTo(this.collection, 'parsed', function(res){
					this.options.pagerModel.set(res.page);
				});
			},
			events: {
				"click .sort": function(e) {
					e.preventDefault();
					var that = this;
					//同じソートキーの場合は昇順降順切り替え
					var order = this.model.get('order');
					if(this.model.get('sort_key') == e.target.id){
						if(order=='asc'){
							order = 'desc';
						}else{
							order = 'asc';
						}
					} else {
						//ソートキーが変更された場合は昇順
						order = 'asc';
					}
					var sort_key = e.target.id;
					
					this.model.set('sort_key',sort_key);
					this.model.set('order',order);
					this.triggerMethod('sort', e.target.id,order);
				}
			},
			fetch:function(unreturnedListConditionModel){
				var cond = {
					"scr": '返却実績照会',
					"page":this.options.pagerModel.getPageRequest(),
					"cond": unreturnedListConditionModel.getReq()
				};
				var that = this;
				$.blockUI({ message: '<p><img src="ajax-loader.gif" style="margin: 0 auto;" /> 読み込み中...</p>' });
				this.collection.fetchMx({
					data: cond,
					success: function(res){
						that.model.set('mode',null);
						$.unblockUI();
					},
					complete:function(res){
						$.unblockUI();
					}
				});
			}


		});
	});
});