define([
	"app",
	"./controllers/LoginPassword"
], function(App) {
	'use strict';

	App.addRegions({
		"main": "#main",
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
				controller: new App.Admin.Controllers.LoginPassword()
			});
		});

	});
	return App.Router;
});
