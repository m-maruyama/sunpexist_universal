define([
	"app",
	"entities/models/AdminPassword"
],function(App) {
	'use strict';
	App.module('Entities.Collections', function(Collections, App, Backbone, Marionette, $, _){
		Collections.AdminPassword = Backbone.Collection.extend({
			model: App.Entities.Models.AdminPassword,
			url: App.api.PA0010,
			parse:function(res, xhr){
				console.log(res);
			}
		});
	});
});
