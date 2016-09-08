define([
	"app",
	"backbone.validation"
],function(App) {
	'use strict';
	App.module('Entities.Models', function(Models,App, Backbone, Marionette, $, _){
		Models.wearerAbstract = Backbone.Model.extend({
			initialize: function() {
				_.extend(this,Backbone.Validation.mixin);
			}
		});
	});
});
