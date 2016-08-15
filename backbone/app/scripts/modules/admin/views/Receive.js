define([
	'app',
	'../Templates'
], function(App) {
	'use strict';
	App.module('Admin.Views', function(Views, App, Backbone, Marionette, $, _){
		Views.Receive = Marionette.LayoutView.extend({
			template: App.Admin.Templates.receive,
			ui: {
                'updateBtn': 'button.update'
			},
			regions: {
				"page": ".page",
				"condition": ".condition",
				"listTable": ".listTable",
				"receive_button": ".receive_button",
				"detailModal": '.detail_modal'
			},
			model: new Backbone.Model(),
			onShow: function() {
				var that = this;
				var modelForUpdate = this.model;
					modelForUpdate.url = App.api.CM0040;
				var cond = {
					"scr": '受領確認'
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
		});
	});
});