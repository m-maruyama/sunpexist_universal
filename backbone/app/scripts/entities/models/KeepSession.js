define([
	"app"
],function(App) {
	'use strict';
	App.module('Entities.Models', function(Models,App, Backbone, Marionette, $, _){
		Models.KeepSession = Backbone.Model.extend({
			url: App.api.CM9010
		});
	});
});
