define([
	'app',
	'../Templates',
	'blockUI',
	'./InfoListItem'
], function(App) {
	'use strict';
	App.module('Admin.Views', function(Views, App, Backbone, Marionette, $, _){
		Views.InfoListList = Marionette.CompositeView.extend({
			template: App.Admin.Templates.infoListList,
			childView: Views.InfoListItem,
			emptyView: Backbone.Marionette.ItemView.extend({
				tagName: "tr",
				template: App.Admin.Templates.infoEmpty
      }),
			childViewContainer: "tbody",
			ui: {
			},
			onRender: function() {
				this.listenTo(this.collection, 'parsed', function(res){
					this.options.pagerModel.set(res.page);
				});
			},
			events: {
			},
			fetch:function(infoListConditionModel){
				$.blockUI({ message: '<p><img src="ajax-loader.gif" style="margin: 0 auto;" /> 読み込み中...</p>' });
				var cond = {
					"scr": 'お知らせ管理',
					"log_type": '2',
					"page":this.options.pagerModel.getPageRequest(),
					"cond": infoListConditionModel.getReq()
				};
				this.collection.fetchMx({
					data: cond,
					success: function(model){
						$.unblockUI();
					}
				});
			}


		});
	});
});
