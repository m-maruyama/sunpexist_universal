define([
	"app",
	"backbone.validation"
],function(App) {
	'use strict';
	App.module('Entities.Models', function(Models,App, Backbone, Marionette, $, _){
		Models.lendAbstract = Backbone.Model.extend({
			initialize: function() {
			},
		});
	});
});
