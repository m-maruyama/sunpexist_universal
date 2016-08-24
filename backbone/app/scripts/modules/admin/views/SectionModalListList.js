define([
	'app',
	'../Templates',
	'blockUI',
	'./SectionModalListItem'
], function(App) {
	'use strict';
	App.module('Admin.Views', function(Views, App, Backbone, Marionette, $, _){
		Views.SectionModalListList = Marionette.CompositeView.extend({
			template: App.Admin.Templates.sectionModalListList,
			emptyView: Backbone.Marionette.ItemView.extend({
				tagName: "tr",
				template: App.Admin.Templates.sectionEmpty,
			}),
			childView: Views.SectionModalListItem,
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
			fetch:function(sectionModalListConditionModel){
				var cond = {
					"scr": '拠点検索',
					"page":this.options.pagerModel.getPageRequest(),
					"cond": sectionModalListConditionModel.getReq()
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