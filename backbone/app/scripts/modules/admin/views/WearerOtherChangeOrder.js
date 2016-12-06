define([
	'app',
	'../Templates'
], function(App) {
	'use strict';
	App.module('Admin.Views', function(Views, App, Backbone, Marionette, $, _){
		Views.WearerOtherChangeOrder = Marionette.LayoutView.extend({
			template: App.Admin.Templates.wearerOtherChangeOrder,
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
				if (window.sessionStorage.getItem("referrer")=='other_change_other_complete') {
					window.sessionStorage.setItem("referrer","other_change_other");
					location.href = './wearer_size_change.html';
				}else{
					window.sessionStorage.setItem("referrer","other_change_other");
				}
				var that = this;
				var modelForUpdate = this.model;
				modelForUpdate.url = App.api.CM0040;
				var cond = {
					"scr": '発注入力（その他交換）'
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
