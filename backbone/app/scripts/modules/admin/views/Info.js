define([
	'app',
	'../Templates',
	'../behaviors/Alerts'
], function(App) {
	'use strict';
	App.module('Admin.Views', function(Views, App, Backbone, Marionette, $, _){
		Views.Info = Marionette.LayoutView.extend({
			template: App.Admin.Templates.info,
			behaviors: {
				"Alerts": {
					behaviorClass: App.Admin.Behaviors.Alerts
				}
			},
			regions: {
				"page": ".page",
				"infoModal": '.info_modal',
				"listTable": ".listTable"
			},
			ui: {
				'modal': '#myModal',
				'addBtn': '.add',
				'message': '#message',
				'updateBtn': 'button.update'
			},
			bindings: {
			},
			onRender: function() {
				var that = this;

			},
			events: {
				'click @ui.addBtn': function(e){
					e.preventDefault();
				},
			}
		});
	});
});