define([
	'app',
	'../Templates',
	'cookie'
], function(App) {
	'use strict';
	App.module('Admin.Views', function(Views, App, Backbone, Marionette, $, _){
		Views.Footer = Marionette.ItemView.extend({
			template: App.Admin.Templates.footer,
			ui: {
				"home": "li.footer_home",
				"history": "li.footer_history",
				"delivery": "li.footer_delivery"
			}
		});
	});
});