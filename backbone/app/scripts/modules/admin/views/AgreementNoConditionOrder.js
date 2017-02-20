define([
	'app',
	'../Templates',
	'backbone.stickit',
], function(App) {
	'use strict';
	App.module('Admin.Views', function(Views, App, Backbone, Marionette, $, _){
		Views.AgreementNoConditionOrder = Marionette.ItemView.extend({
			template: App.Admin.Templates.agreementNoConditionOrder,
			model: new Backbone.Model(),
			ui: {
				'agreement_no': '.agreement_no'
			},
			bindings: {
				'.agreement_no': 'agreement_no'
			},
			onShow: function() {
				var that = this;
				var modelForUpdate = this.model;
				modelForUpdate.url = App.api.CM0065;
				var cond = {
					"scr": '契約No'
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
