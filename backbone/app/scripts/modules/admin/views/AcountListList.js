define([
	'app',
	'../Templates',
	'./AcountListItem'
], function(App) {
	'use strict';
	App.module('Admin.Views', function(Views, App, Backbone, Marionette, $, _){
		Views.AcountListList = Marionette.CompositeView.extend({
			template: App.Admin.Templates.acountListList,
			childView: Views.AcountListItem,
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
			fetch:function(acountListConditionModel){
				var cond = {
					"scr": 'アカウント管理',
					"page":this.options.pagerModel.getPageRequest(),
					"cond": acountListConditionModel.getReq()
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