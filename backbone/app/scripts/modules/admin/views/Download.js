define([
	'app',
	'../Templates',
], function(App) {
	'use strict';
	App.module('Admin.Views', function(Views, App, Backbone, Marionette, $, _){
		Views.Download = Marionette.ItemView.extend({
			template: App.Admin.Templates.download,
			ui: {
			}
		});
	});
});
