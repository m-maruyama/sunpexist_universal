define([
	'app',
	'../Templates'
], function(App) {
	'use strict';
	App.module('Admin.Views', function(Views, App, Backbone, Marionette, $, _){
		Views.Lend = Marionette.LayoutView.extend({
			template: App.Admin.Templates.lend,
			ui: {
			},
			regions: {
				"page": ".page",
				"condition": ".condition",
				"listTable": ".listTable",
				"detailModal": '.detail_modal'
			},
			model: new Backbone.Model(),
			onShow: function() {
				var that = this;
				var modelForUpdate = this.model;
					modelForUpdate.url = App.api.CM0040;
				var cond = {
					"scr": '貸与リスト'
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