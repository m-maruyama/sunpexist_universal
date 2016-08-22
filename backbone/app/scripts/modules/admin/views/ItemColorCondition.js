define([
	'app',
	'../Templates',
	'backbone.stickit',
], function(App) {
	'use strict';
	App.module('Admin.Views', function(Views, App, Backbone, Marionette, $, _){
		Views.ItemColorCondition = Marionette.ItemView.extend({
			template: App.Admin.Templates.itemColorCondition,
			model: new Backbone.Model(),
			ui: {
				'item_color': '.item_color'
			},
			bindings: {
				'.item_color': 'item_color',
			},
			onShow: function() {
				var that = this;
				var modelForUpdate = this.model;
					modelForUpdate.url = App.api.CM0080;
				var cond = {
					"scr": '色'
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
