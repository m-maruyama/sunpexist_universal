define([
	'app',
	'../Templates',
	'./InfoListItem'
], function(App) {
	'use strict';
	App.module('Admin.Views', function(Views, App, Backbone, Marionette, $, _){
		Views.InfoListList = Marionette.CompositeView.extend({
			template: App.Admin.Templates.infoListList,
			childView: Views.InfoListItem,
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
				var cond = {
					"scr": 'お知らせ管理',
					"page":this.options.pagerModel.getPageRequest(),
					"cond": infoListConditionModel.getReq()
				};
				this.collection.fetchMx({
					data: cond,
					success: function(model){
					}
				});
			}


		});
	});
});