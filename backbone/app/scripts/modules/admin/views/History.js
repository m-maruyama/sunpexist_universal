define([
	'app',
	'../Templates'
], function(App) {
	'use strict';
	App.module('Admin.Views', function(Views, App, Backbone, Marionette, $, _){
		Views.History = Marionette.LayoutView.extend({
			template: App.Admin.Templates.history,
			ui: {
			},
			regions: {
				"page": ".page",
				"page_2": ".page_2",
				"condition": ".condition",
				"listTable": ".listTable",
				"download": ".download",
				"sectionModal": ".section_modal",
				"detailModal": '.detail_modal'
			},
			model: new Backbone.Model(),
			onShow: function() {
				var that = this;
				var modelForUpdate = this.model;
					modelForUpdate.url = App.api.CM0040;
				var cond = {
					"scr": '発注状況照会'
				};
					modelForUpdate.fetchMx({
						data:cond,
						success:function(res){
							var errors = res.get('errors');
							if(errors) {
								var errorMessages = errors.map(function(v){
									return v.error_message;
								});
								that.triggerMethod('showAlerts', errorMessages);
							}
						}
					});
			},
			events: {
			}

		});
	});
});
