define([
	'app',
	'../Templates',
	'backbone.stickit',
], function(App) {
	'use strict';
	App.module('Admin.Views', function(Views, App, Backbone, Marionette, $, _){
		Views.CorporateIdCondition = Marionette.ItemView.extend({
			template: App.Admin.Templates.corporateIdCondition,
			model: new Backbone.Model(),
			ui: {
				'corporate_id': '.corporate_id'
			},
			bindings: {
				'.corporate_id': 'corporate_id'
			},
			onShow: function() {
				var that = this;
				var modelForUpdate = this.model;
				modelForUpdate.url = App.api.CM0062;
				var cond = {
					"scr": '企業ID'
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
							that.render();
						}
					});
			},
			events: {
			}
		});
	});
});
