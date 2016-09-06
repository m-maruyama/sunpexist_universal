define([
	'app',
	'../Templates',
	'backbone.stickit',
], function(App) {
	'use strict';
	App.module('Admin.Views', function(Views, App, Backbone, Marionette, $, _){
		Views.ItemZaikoCondition = Marionette.ItemView.extend({
			defaults: {
				agreement_no: '',
				job_type_zaiko: '',
			},
			initialize: function(options) {
			    this.options = options || {};
			    this.options = _.extend(this.defaults, this.options);
			},
			template: App.Admin.Templates.itemZaikoCondition,
			model: new Backbone.Model(),
			ui: {
				'item': '.item'
			},
			bindings: {
				'.item': 'item',
			},
			onShow: function() {
				var agreement_no = this.options.agreement_no;
				var job_type_zaiko = this.options.job_type_zaiko;
				var that = this;

				var modelForUpdate = this.model;
					modelForUpdate.url = App.api.CM0051;
				var cond = {
					"scr": '商品',
					"agreement_no": agreement_no,
					"job_type_zaiko": job_type_zaiko,
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
