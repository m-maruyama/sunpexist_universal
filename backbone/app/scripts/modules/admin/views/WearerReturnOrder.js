define([
	'app',
	'../Templates'
], function(App) {
	'use strict';
	App.module('Admin.Views', function(Views, App, Backbone, Marionette, $, _){
		Views.WearerReturnOrder = Marionette.LayoutView.extend({
			template: App.Admin.Templates.wearerReturnOrder,
			ui: {
			},
			regions: {
				"condition": ".condition",
				"listTable": ".listTable",
				"complete": ".complete",
				"sectionModal": ".section_modal",
				"sectionModal_2": ".section_modal_2",
			},
			model: new Backbone.Model(),
			onShow: function() {
				if (window.sessionStorage.getItem("referrer")=='wearer_return_order_complete') {
					window.sessionStorage.setItem("referrer","wearer_return_order");
					location.href = './wearer_other.html';
				}else{
					window.sessionStorage.setItem("referrer","wearer_return_order");
				}
				var that = this;
				var modelForUpdate = this.model;
				modelForUpdate.url = App.api.CM0040;
				var cond = {
					"scr": '発注入力（不要品返却）'
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
					}
				});
			},
			events: {
			}

		});
	});
});
