define([
	'app',
	'../Templates',
    '../behaviors/Alerts',
], function(App) {
	'use strict';
	App.module('Admin.Views', function(Views, App, Backbone, Marionette, $, _){
		Views.WearerInputComplete = Marionette.LayoutView.extend({
			template: App.Admin.Templates.wearerInputComplete,
			model: new Backbone.Model(),
			regions: {
				"condition": ".condition",
			},
			onRender: function() {
			},
			events: {

			},

		});
	});
});
