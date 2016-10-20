define([
	'app',
	'../Templates',
	'backbone.stickit',
], function(App) {
	'use strict';
	App.module('Admin.Views', function(Views, App, Backbone, Marionette, $, _){
		Views.JobTypeConditionOrder = Marionette.ItemView.extend({
			defaults: {
				agreement_no: '',
			},
			initialize: function(options) {
			    this.options = options || {};
			    this.options = _.extend(this.defaults, this.options);
			},
			template: App.Admin.Templates.jobTypeConditionOrder,
			model: new Backbone.Model(),
			ui: {
				'job_type': '.job_type'
			},
			bindings: {
				'.job_type': 'job_type',
			},
			onShow: function() {
				var agreement_no = this.options.agreement_no;
				var that = this;

				var modelForUpdate = this.model;
				modelForUpdate.url = App.api.WC0016;
				var path = location.pathname;
				if(path=='/universal/wearer_end_order.html'){
					modelForUpdate.url = App.api.WN0013;
				}else if(path=='/universal/wearer_order.html'){
					modelForUpdate.url = App.api.WC0016;
				}
				var cond = {
					"scr": '貸与パターン',
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
