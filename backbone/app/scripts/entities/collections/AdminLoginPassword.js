define([
	"app",
	"entities/models/AdminLoginPassword"
],function(App) {
	'use strict';
	App.module('Entities.Collections', function(Collections, App, Backbone, Marionette, $, _){
		Collections.AdminLoginPassword = Backbone.Collection.extend({
			model: App.Entities.Models.AdminLoginPassword,
			url: App.api.LP0010,
			parse:function(res, xhr){
			}
		});
	});
});
