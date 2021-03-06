define([
	'app',
	'../Templates',
	'backbone.stickit',
], function(App) {
	'use strict';
	App.module('Admin.Views', function(Views, App, Backbone, Marionette, $, _){
		Views.JobTypeConditionInput = Marionette.ItemView.extend({
			template: App.Admin.Templates.jobTypeConditionInput,
			model: new Backbone.Model(),
			ui: {
				'job_type': '.job_type'
			},
			bindings: {
				'.job_type': 'job_type',
			},
			onShow: function() {
				var that = this;
				var modelForUpdate = this.model;
				modelForUpdate.url = App.api.CM0010;
				var cond = {
					"scr": '貸与パターン'
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
