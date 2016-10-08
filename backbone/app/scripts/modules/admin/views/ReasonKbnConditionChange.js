define([
	'app',
	'../Templates',
	'backbone.stickit',
], function(App) {
	'use strict';
	App.module('Admin.Views', function(Views, App, Backbone, Marionette, $, _){
		Views.ReasonKbnConditionChange = Marionette.ItemView.extend({
			defaults: {
				job_type: ''
			},
			initialize: function(options) {
			    this.options = options || {};
			    this.options = _.extend(this.defaults, this.options);
			},
			template: App.Admin.Templates.reasonKbnConditionChange,
			model: new Backbone.Model(),
			ui: {
				'reason_kbn': '.reason_kbn'
			},
			bindings: {
				'.reason_kbn': 'reason_kbn'
			},
			onShow: function() {
				var that = this;

				var job_type = this.options.job_type;
				var data = {
					'job_type_cd': job_type
				}
				var modelForUpdate = this.model;
				modelForUpdate.url = App.api.WC0013;
				var cond = {
					"scr": '理由区分',
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
