define([
	'app',
	'../Templates',
	'backbone.stickit',
], function(App) {
	'use strict';
	App.module('Admin.Views', function(Views, App, Backbone, Marionette, $, _){
		Views.ReasonKbnConditionOrder= Marionette.ItemView.extend({
			template: App.Admin.Templates.ReasonKbnConditionOrder,
			model: new Backbone.Model(),
			ui: {
				'reason_kbn': '.reason_kbn'
			},
			bindings: {
				'.reason_kbn': 'reason_kbn'
			},
			onShow: function() {
				var that = this;
				var modelForUpdate = this.model;
				modelForUpdate.url = App.api.WO0011;
				var cond = {
					"scr": '理由区分'
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
