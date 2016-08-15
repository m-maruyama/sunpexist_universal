define(["app"],function(App) {
	'use strict';
	App.module('Entities.Models', function(Models,App, Backbone, Marionette, $, _){
		Models.Calendar = Backbone.Model.extend({
			url: App.api.CM0080
		});
	});
});
