define([
	'app',
	'../Templates',
	'blockUI',
	'./WearerEditListItem'
], function(App) {
	'use strict';
	App.module('Admin.Views', function(Views, App, Backbone, Marionette, $, _){
		var order = 'asc';
		var sort_key = 'order_req_no';
		Views.WearerEditListList = Marionette.CompositeView.extend({
			template: App.Admin.Templates.wearerEditListList,
			emptyView: Backbone.Marionette.ItemView.extend({
                tagName: "tr",
				template: App.Admin.Templates.wearerEditEmpty,
			}),
			childView: Views.WearerEditListItem,
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
					if(sort_key == e.target.id){
						if(order == 'asc'){
							order = 'desc';
						}else{
							order = 'asc';
						}
					} else {
						//ソートキーが変更された場合は昇順
						order = 'asc';
					}
					sort_key = e.target.id;
					this.triggerMethod('sort', e.target.id,order);
				}
			},
			fetch:function(wearerEditListConditionModel){
				var cond = {
					"scr": '着用者編集検索',
					"page":this.options.pagerModel.getPageRequest(),
					"cond": wearerEditListConditionModel.getReq()
				};
				$.blockUI({ message: '<p><img src="ajax-loader.gif" style="margin: 0 auto;" /> 読み込み中...</p>' });
				this.collection.fetchMx({
					data: cond,
					success: function(model){
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
