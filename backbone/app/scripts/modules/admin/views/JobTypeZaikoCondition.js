define([
	'app',
	'../Templates',
	'backbone.stickit',
], function(App) {
	'use strict';
	App.module('Admin.Views', function(Views, App, Backbone, Marionette, $, _){
		Views.JobTypeZaikoCondition = Marionette.ItemView.extend({
			defaults: {
				agreement_no: '',
			},
			initialize: function(options) {
			    this.options = options || {};
			    this.options = _.extend(this.defaults, this.options);
			},
			template: App.Admin.Templates.jobTypeZaikoCondition,
			model: new Backbone.Model(),
			ui: {
				'job_type_zaiko': '.job_type_zaiko'
			},
			bindings: {
				'.job_type_zaiko': 'job_type_zaiko',
			},
			onShow: function() {
				var agreement_no = this.options.agreement_no;
				var that = this;

				var modelForUpdate = this.model;
				modelForUpdate.url = App.api.CM0050;
				var cond = {
					"scr": '在庫照会専用貸与パターン',
					"agreement_no": agreement_no,
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
