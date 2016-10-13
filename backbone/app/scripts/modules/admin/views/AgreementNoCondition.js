define([
	'app',
	'../Templates',
	'backbone.stickit',
], function(App) {
	'use strict';
	App.module('Admin.Views', function(Views, App, Backbone, Marionette, $, _){
		Views.AgreementNoCondition = Marionette.ItemView.extend({
			defaults: {
				data: "",
			},
			initialize: function(options) {
				this.options = options || {};
				this.options = _.extend(this.defaults, this.options);
			},
			template: App.Admin.Templates.agreementNoCondition,
			model: new Backbone.Model(),
			ui: {
				'agreement_no': '.agreement_no'
			},
			bindings: {
				'.agreement_no': 'agreement_no'
			},
			onShow: function() {
				var that = this;
				var data = this.options.data;

				var modelForUpdate = this.model;
				modelForUpdate.url = App.api.CM0060;
				var cond = {
					"scr": '契約No',
					"data": data
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
