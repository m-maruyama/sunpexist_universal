define([
	'app',
	'../Templates',
	'backbone.stickit',
], function(App) {
	'use strict';
	App.module('Admin.Views', function(Views, App, Backbone, Marionette, $, _){
		Views.InputItemCondition = Marionette.ItemView.extend({
			defaults: {
				agreement_no: '',
				job_type: '',
			},
			initialize: function(options) {
			    this.options = options || {};
			    this.options = _.extend(this.defaults, this.options);
			},
			template: App.Admin.Templates.inputItemCondition,
			model: new Backbone.Model(),
			ui: {
				'input_item': '.input_item'
			},
			bindings: {
				'.input_item': 'input_item',
			},
			onShow: function() {
				var agreement_no = this.options.agreement_no;
				var job_type = this.options.job_type;
				var that = this;

				var modelForUpdate = this.model;
					modelForUpdate.url = App.api.CM0070;
				var cond = {
					"scr": '商品',
					"agreement_no": agreement_no,
					"job_type": job_type,
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
