define([
	'app',
	'../Templates'
], function(App) {
	'use strict';
	App.module('Admin.Views', function(Views, App, Backbone, Marionette, $, _){
		Views.Inquiry = Marionette.LayoutView.extend({
			template: App.Admin.Templates.inquiry,
			ui: {
			},
			regions: {
				"page": ".page",
				"page_2": ".page_2",
				"condition": ".condition",
				"listTable": ".listTable",
				"sectionModal": ".section_modal",
				"sectionModal_2": ".section_modal_2",
				"inquiry_input_modal": '.inquiry_input_modal',
				"inquiry_detail_modal": '.inquiry_detail_modal'
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
			},
			events: {
			}

		});
	});
});
