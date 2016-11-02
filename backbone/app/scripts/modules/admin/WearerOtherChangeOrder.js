define([
	"app",
	"./controllers/WearerOtherChangeOrder"
], function(App) {
	'use strict';

	App.addRegions({
		"nav": "#nav",
		"main": "#main",
		"footer": "#footer"
	});
	App.Router = Marionette.AppRouter.extend({
		initialize: function() {
		},
		appRoutes: {
			'': 'top'
		}
	});
	App.module('Admin', function(Module, App, Backbone, Marionette, $, _){
		Module.addInitializer(function(options){
			var router = new App.Router({
				controller: new App.Admin.Controllers.WearerOtherChangeOrder()
			});
		});

	});
	return App.Router;
});
