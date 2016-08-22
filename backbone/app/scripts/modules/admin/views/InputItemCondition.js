define([
	'app',
	'../Templates',
	'backbone.stickit',
], function(App) {
	'use strict';
	App.module('Admin.Views', function(Views, App, Backbone, Marionette, $, _){
		Views.InputItemCondition = Marionette.ItemView.extend({
			template: App.Admin.Templates.inputItemCondition,
			model: new Backbone.Model(),
			ui: {
				'input_item': '.input_item'
			},
			bindings: {
				'.input_item': 'input_item',
			},
			onShow: function() {
				var that = this;
				var modelForUpdate = this.model;
					modelForUpdate.url = App.api.CM0070;
				var cond = {
					"scr": '商品'
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
