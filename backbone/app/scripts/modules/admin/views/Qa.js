define([
	'app',
	'../Templates',
	'blockUI',
	'../behaviors/Alerts'
], function(App) {
	'use strict';
	App.module('Admin.Views', function(Views, App, Backbone, Marionette, $, _){
		Views.Qa = Marionette.LayoutView.extend({
			template: App.Admin.Templates.qa,
			model: new Backbone.Model(),
			behaviors: {
				"Alerts": {
					behaviorClass: App.Admin.Behaviors.Alerts
				}
			},
			regions: {
				"condition": ".condition",
				"qa_area": ".qa_area"
			},
			ui: {
				"qa_area": ".qa_area"
			},
			bindings: {
			},
			onRender: function() {
				var that = this;
				$.blockUI({ message: '<p><img src="ajax-loader.gif" style="margin: 0 auto;" /> 読み込み中...</p>' });
				



				$.unblockUI();
			},
			events: {
				'click @ui.updateBtn': function(e){
					e.preventDefault();
					this.triggerMethod('click:updateBtn');
				},
			}
		});
	});
});
