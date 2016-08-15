define([
	'app',
	'../Templates'
], function(App) {
	'use strict';
	App.module('Admin.Views', function(Views, App, Backbone, Marionette, $, _){
		Views.ImportCsv = Marionette.LayoutView.extend({
			template: App.Admin.Templates.importCsv,
			ui: {
			},
			regions: {
				"condition": ".condition"
			},
			onRender: function() {
			},

		});
	});
});