define([
	'app',
	'../Templates'
], function(App) {
	'use strict';
	App.module('Admin.Views', function(Views, App, Backbone, Marionette, $, _){
		Views.InquiryDetail = Marionette.LayoutView.extend({
			template: App.Admin.Templates.inquiryDetail,
			ui: {
			},
			regions: {
				"condition": ".condition",
				"sectionModal": ".section_modal",
				"sectionModal_2": ".section_modal_2"
			},
			model: new Backbone.Model(),
			onShow: function() {
				var that = this;
				var modelForUpdate = this.model;
				modelForUpdate.url = App.api.CM0040;
				var cond = {
					"scr": 'お問い合わせ'
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
			}
		});
	});
});
