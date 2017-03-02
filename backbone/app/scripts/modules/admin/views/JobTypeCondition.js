define([
	'app',
	'../Templates',
	'backbone.stickit',
], function(App) {
	'use strict';
	App.module('Admin.Views', function(Views, App, Backbone, Marionette, $, _){
		Views.JobTypeCondition = Marionette.ItemView.extend({
			defaults: {
				agreement_no: '',
				job_type: '',
			},
			initialize: function(options) {
			    this.options = options || {};
			    this.options = _.extend(this.defaults, this.options);
			},
			template: App.Admin.Templates.jobTypeCondition,
			model: new Backbone.Model(),
			ui: {
				'job_type': '.job_type'
			},
			bindings: {
				'.job_type': 'job_type',
			},
			onShow: function() {
				var that = this;
				var agreement_no = this.options.agreement_no;
				var job_type = this.options.job_type;

				var modelForUpdate = this.model;
				modelForUpdate.url = App.api.CM0010;
				var cond = {
					"scr": '貸与パターン',
					"agreement_no": agreement_no,
					"job_type": job_type
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
						if (res.attributes.job_type_list.length > 1) {
							that.render();
						}else{
							//貸与パターンのリトライ
							modelForUpdate.fetchMx({
								data: cond,
								success: function (res) {
									that.render();
								}
							});

						}
					}
				});
			},
			events: {
			}
		});
	});
});
