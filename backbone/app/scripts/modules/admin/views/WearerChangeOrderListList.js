define([
	'app',
	'handlebars',
	'../Templates',
	'backbone.stickit',
	'../behaviors/Alerts',
	'typeahead',
	'bloodhound',
	'../controllers/WearerChangeOrder',
], function(App) {
	'use strict';
	App.module('Admin.Views', function(Views, App, Backbone, Marionette, $, _){
		Views.WearerChangeOrderListList = Marionette.LayoutView.extend({
			model: new Backbone.Model(),
			template: App.Admin.Templates.wearerChangeOrderList,
			behaviors: {
				"Alerts": {
					behaviorClass: App.Admin.Behaviors.Alerts
				}
			},
			ui: {
				'order_count': '#order_count',
				'return_count': '#return_count',
				'target_flg': '#target_flg',
			},
			bindings: {
				'#order_count': 'order_count',
				'#return_count': 'return_count',
				'#target_flg': 'target_flg',
			},
			onShow: function() {
				var that = this;

				// 現在貸与中のアイテム
				var modelForUpdate = this.model;
				modelForUpdate.url = App.api.WC0019;
				var cond = {
					"scr": '現在貸与中のアイテム',
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
						var res_list = res.attributes;
						//console.log(res_list['wearer_info']);
					}
				});
			},
			events: {
			},
		});
	});
});
