define([
	'app',
	'../Templates',
    '../behaviors/Alerts',
], function(App) {
	'use strict';
	App.module('Admin.Views', function(Views, App, Backbone, Marionette, $, _){
		Views.WearerOrderComplete = Marionette.LayoutView.extend({
			template: App.Admin.Templates.wearerOrderComplete,
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
